<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Item;
use App\Models\Color;
use App\Models\Size;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceDetails;
use App\Models\Payment;
use App\Models\PaymentDetail;
use App\Models\StockTransaction;
use App\Models\ItemPrice;
use App\Models\ItemStockSummary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Base\BaseController;

class PosController extends BaseController
{
    public function index(Request $request)
    {
        return view('layouts.backend_app');
    }

    public function searchCustomer(Request $request)
    {
        $mobile = trim($request->input('mobile'));
        if (empty($mobile)) {
            return response()->json(null);
        }

        $client = Client::where('mobile', $mobile)->first();
        if ($client) {
            // Calculate current balance/due
            $totalInvoiced = Invoice::where('client_id', $client->id)->where('status', 'active')->sum('amount');
            $totalPaid = Payment::where('client_id', $client->id)->where('status', 'active')->sum('amount');
            $currentDue = floatval($client->previous_due ?? 0) + ($totalInvoiced - $totalPaid);
            $client->current_due = max(0, $currentDue);
        }

        return response()->json($client);
    }

    public function quickCustomer(Request $request)
    {
        $request->validate([
            'mobile' => 'required|string',
            'name' => 'required|string',
        ]);

        $mobile = trim($request->mobile);
        $client = Client::where('mobile', $mobile)->first();

        if (!$client) {
            $lastClient = Client::latest('id')->first();
            $clientid = $lastClient ? intval($lastClient->clientid) + 1 : 1001;

            $client = Client::create([
                'clientid' => $clientid,
                'reg_date' => date('Y-m-d'),
                'name' => $request->name,
                'mobile' => $mobile,
                'address' => $request->address ?? 'N/A',
                'status' => 'active',
            ]);
        }

        return response()->json($client);
    }

    public function searchItems(Request $request)
    {
        $term = trim($request->input('term'));
        if (empty($term)) {
            return response()->json([]);
        }

        $items = Item::where('status', 'active')
            ->where(function ($q) use ($term) {
                $q->where('barcode', 'like', "%{$term}%")
                  ->orWhere('title', 'like', "%{$term}%");
            })
            ->with([
                'category:id,title',
                'unit:id,title',
                'itemPrices' => function($q) {
                    $q->with('color:id,title', 'size:id,title');
                },
                'stockSummaries' => function($q) {
                    $q->with('color:id,title', 'size:id,title');
                }
            ])
            ->limit(20)
            ->get();

        // Also fetch list of all colors & sizes for fallback selection
        $allColors = Color::where('status', 'active')->oldest('sort')->get(['id', 'title']);
        $allSizes = Size::where('status', 'active')->oldest('sort')->get(['id', 'title']);

        return response()->json([
            'items' => $items,
            'colors' => $allColors,
            'sizes' => $allSizes,
        ]);
    }

    public function validateSerial(Request $request)
    {
        $itemId = $request->input('item_id');
        $serialNo = trim($request->input('serial_no'));

        if (empty($itemId) || empty($serialNo)) {
            return response()->json(['valid' => true]);
        }

        // 1. Check if serial number exists in Purchase records (purchase_details)
        $purchaseExists = \App\Models\PurchaseDetail::where('item_id', $itemId)
            ->where('serial_no', 'like', "%{$serialNo}%")
            ->exists();

        if (!$purchaseExists) {
            return response()->json([
                'valid' => false,
                'message' => 'এই সিরিয়াল নম্বরটি ক্রয়কৃত রেকর্ডে (Purchase) পাওয়া যায়নি!'
            ]);
        }

        // 2. Check if serial number has already been sold in Invoice Details (invoice_details)
        $alreadySold = InvoiceDetails::where('item_id', $itemId)
            ->where('serial_no', 'like', "%{$serialNo}%")
            ->where('status', 'active')
            ->exists();

        if ($alreadySold) {
            return response()->json([
                'valid' => false,
                'message' => 'এই সিরিয়াল নম্বরটি ইতিপূর্বে বিক্রয় (Sold) হয়ে গেছে!'
            ]);
        }

        return response()->json(['valid' => true]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'cart' => 'required|array|min:1',
            'paid_amount' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Client handle
            $clientId = $request->input('client_id');
            if (empty($clientId) && !empty($request->input('client_mobile'))) {
                $mobile = trim($request->input('client_mobile'));
                $clientName = trim($request->input('client_name')) ?: 'Walk-in Customer';
                $client = Client::firstOrCreate(
                    ['mobile' => $mobile],
                    [
                        'clientid' => rand(10000, 99999),
                        'reg_date' => date('Y-m-d'),
                        'name' => $clientName,
                        'address' => $request->input('client_address') ?? 'N/A',
                        'status' => 'active'
                    ]
                );
                $clientId = $client->id;
            }

            if (empty($clientId)) {
                $walkin = Client::firstOrCreate(
                    ['mobile' => '00000000000'],
                    [
                        'clientid' => 1000,
                        'reg_date' => date('Y-m-d'),
                        'name' => 'Walk-in Customer',
                        'address' => 'N/A',
                        'status' => 'active'
                    ]
                );
                $clientId = $walkin->id;
            }

            // Generate Invoice No
            $lastInvoice = Invoice::latest('id')->first();
            $nextId = $lastInvoice ? $lastInvoice->id + 1 : 1;
            $invoiceNo = 'POS-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            $cart = $request->input('cart');
            $originalAmount = 0;

            foreach ($cart as $item) {
                $originalAmount += floatval($item['rate']) * floatval($item['qty']);
            }

            $discount = floatval($request->input('discount', 0));
            $vat = floatval($request->input('vat', 0));
            $totalAmount = max(0, ($originalAmount - $discount) + $vat);
            $paidAmount = floatval($request->input('paid_amount', 0));

            // Create Invoice
            $invoice = Invoice::create([
                'client_id' => $clientId,
                'invoice_no' => $invoiceNo,
                'invoice_date' => date('Y-m-d'),
                'original_amount' => $originalAmount,
                'discount' => $discount,
                'vat' => $vat,
                'amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'is_previous_due' => 0,
                'is_closed' => ($paidAmount >= $totalAmount) ? 1 : 0,
                'status' => 'active',
            ]);

            // Create Invoice Details & Stock Transactions
            foreach ($cart as $cartItem) {
                $qty = floatval($cartItem['qty']);
                $rate = floatval($cartItem['rate']);
                $itemTotal = $qty * $rate;
                $colorId = !empty($cartItem['color_id']) ? $cartItem['color_id'] : null;
                $sizeId = !empty($cartItem['size_id']) ? $cartItem['size_id'] : null;
                $serialNo = !empty($cartItem['serial_no']) ? $cartItem['serial_no'] : null;

                InvoiceDetails::create([
                    'invoice_id' => (string)$invoice->id,
                    'reference' => 'POS Sale',
                    'workorder_id' => 0,
                    'item_id' => $cartItem['item_id'],
                    'color_id' => $colorId,
                    'size_id' => $sizeId,
                    'serial_no' => $serialNo,
                    'account_id' => 0,
                    'description' => 'POS Item Sale' . ($serialNo ? " (Serial: {$serialNo})" : ''),
                    'qty' => $qty,
                    'amount' => $rate,
                    'total_amount' => $itemTotal,
                    'status' => 'active',
                ]);

                // Record stock out
                StockTransaction::create([
                    'item_id' => $cartItem['item_id'],
                    'color_id' => $colorId,
                    'size_id' => $sizeId,
                    'transaction_date' => date('Y-m-d'),
                    'transaction_type' => 'Issue',
                    'reference_type' => 'Issue',
                    'reference_id' => (string)$invoice->id,
                    'qty_in' => 0,
                    'qty_out' => $qty,
                    'status' => 'active',
                ]);
            }

            // Record Payment if paid > 0
            if ($paidAmount > 0) {
                $paymentMethod = $request->input('payment_method', 'Cash');
                $mBankingType = $request->input('mbanking_type', null);
                $trxId = $request->input('trxid', null);

                $payment = Payment::create([
                    'payslipno' => $invoiceNo,
                    'payment_type' => 'Receive',
                    'client_id' => $clientId,
                    'payment_date' => date('Y-m-d'),
                    'discount' => 0,
                    'amount' => $paidAmount,
                    'fund_account_id' => 0,
                    'payment_method' => $paymentMethod,
                    'mbanking_type' => $mBankingType,
                    'trxid' => $trxId,
                    'status' => 'active',
                ]);

                PaymentDetail::create([
                    'payment_id' => $payment->id,
                    'reference_type' => 'Invoice',
                    'reference_id' => $invoice->id,
                    'amount' => $paidAmount,
                    'is_closed' => ($paidAmount >= $totalAmount) ? 1 : 0,
                    'status' => 'active',
                ]);
            }

            DB::commit();

            // Load full payload for printing
            $fullInvoice = Invoice::with([
                'client',
                'details' => function($q) {
                    $q->with('item:id,title,barcode', 'color:id,title', 'size:id,title');
                }
            ])->find($invoice->id);

            return response()->json([
                'success' => true,
                'message' => 'Sale completed successfully!',
                'invoice' => $fullInvoice,
            ]);

        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json(['exception' => $ex->getMessage()], 422);
        }
    }

    public function returnIndex(Request $request)
    {
        return view('layouts.backend_app');
    }

    public function searchInvoicesForReturn(Request $request)
    {
        $term = trim($request->input('term'));
        if (empty($term)) {
            return response()->json([]);
        }

        $invoices = Invoice::where('status', 'active')
            ->where(function ($q) use ($term) {
                $q->where('invoice_no', 'like', "%{$term}%")
                  ->orWhereHas('client', function($cq) use ($term) {
                      $cq->where('mobile', 'like', "%{$term}%")
                         ->orWhere('name', 'like', "%{$term}%");
                  });
            })
            ->with([
                'client',
                'details' => function($q) {
                    $q->with('item:id,title,barcode', 'color:id,title', 'size:id,title');
                }
            ])
            ->latest()
            ->limit(10)
            ->get();

        return response()->json($invoices);
    }

    public function processReturn(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required',
            'return_items' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            $invoice = Invoice::findOrFail($request->invoice_id);
            $returnItems = $request->return_items;
            $totalRefund = 0;

            foreach ($returnItems as $item) {
                $qty = floatval($item['qty']);
                if ($qty <= 0) continue;

                $rate = floatval($item['rate']);
                $itemRefund = $qty * $rate;
                $totalRefund += $itemRefund;

                $colorId = !empty($item['color_id']) ? $item['color_id'] : null;
                $sizeId = !empty($item['size_id']) ? $item['size_id'] : null;

                // Add stock back in
                StockTransaction::create([
                    'item_id' => $item['item_id'],
                    'color_id' => $colorId,
                    'size_id' => $sizeId,
                    'transaction_date' => date('Y-m-d'),
                    'transaction_type' => 'Adjustment',
                    'reference_type' => 'SalesReturn',
                    'reference_id' => (string)$invoice->id,
                    'qty_in' => $qty,
                    'qty_out' => 0,
                    'status' => 'active',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sales return processed and stock updated successfully!',
                'refund_amount' => $totalRefund,
            ]);

        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json(['exception' => $ex->getMessage()], 422);
        }
    }
}
