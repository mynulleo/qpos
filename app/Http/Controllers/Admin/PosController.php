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
use App\Models\Account;
use App\Models\System\SiteSetting;
use App\Models\ClientPointTransaction;
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

            // Coupon / Loyalty Points
            $siteSetting = SiteSetting::first();
            $couponEnabled = boolval($siteSetting->coupon_enabled ?? 0);
            $client->coupon_enabled = $couponEnabled;
            $client->points_balance = floatval($client->points_balance ?? 0);
            $pointRedeemRate = floatval($siteSetting->point_redeem_rate ?? 10);
            $pointEarnRate = floatval($siteSetting->point_earn_rate ?? 1);
            $minPointsToRedeem = intval($siteSetting->min_points_to_redeem ?? 10);

            $client->point_redeem_rate = $pointRedeemRate;
            $client->point_earn_rate = $pointEarnRate;
            $client->min_points_to_redeem = $minPointsToRedeem;
            $client->points_value_in_tk = ($couponEnabled && $pointRedeemRate > 0)
                ? round($client->points_balance / $pointRedeemRate, 2)
                : 0.00;
        }

        return response()->json($client);
    }

    public function quickCustomer(Request $request)
    {
        $request->validate([
            'mobile' => 'required|string|size:11',
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

                // Find Cash Account (system_key_name == 'Cash' and account_type == 'Asset')
                $cashAccount = Account::where('system_key_name', 'Cash')
                    ->where('account_type', 'Asset')
                    ->first()
                    ?? Account::where('system_key_name', 'Cash')->first();
                $fundAccountId = $cashAccount ? $cashAccount->id : 0;

                $payment = Payment::create([
                    'payslipno' => $invoiceNo,
                    'payment_type' => 'Receive',
                    'client_id' => $clientId,
                    'payment_date' => date('Y-m-d'),
                    'discount' => 0,
                    'amount' => $paidAmount,
                    'fund_account_id' => $fundAccountId,
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

            // Coupon & Loyalty Points Calculation
            $siteSetting = SiteSetting::first();
            $couponEnabled = boolval($siteSetting->coupon_enabled ?? 0);
            $pointsRedeemed = floatval($request->input('points_redeemed', 0));
            $pointsEarned = 0;
            $clientObj = Client::find($clientId);

            // 1. Redeem points if requested
            if ($couponEnabled && $pointsRedeemed > 0 && $clientObj) {
                $pointRedeemRate = floatval($siteSetting->point_redeem_rate ?: 10);
                $redeemAmount = round($pointsRedeemed / $pointRedeemRate, 2);

                $currentBal = floatval($clientObj->points_balance ?? 0);
                $newBalAfterRedeem = max(0, $currentBal - $pointsRedeemed);

                ClientPointTransaction::create([
                    'client_id' => $clientId,
                    'invoice_id' => $invoice->id,
                    'type' => 'Redeem',
                    'points_in' => 0,
                    'points_out' => $pointsRedeemed,
                    'balance' => $newBalAfterRedeem,
                    'rate' => $pointRedeemRate,
                    'amount_equivalent' => $redeemAmount,
                    'description' => "Redeemed {$pointsRedeemed} points for Tk. {$redeemAmount} discount on Invoice #{$invoiceNo}",
                    'transaction_date' => date('Y-m-d'),
                    'status' => 'active',
                    'created_by' => auth()->id() ?? 1,
                    'created_ip' => $request->ip(),
                ]);

                $clientObj->points_balance = $newBalAfterRedeem;
                $clientObj->save();
            }

            // 2. Earn loyalty points on net purchase amount
            if ($couponEnabled && $clientObj && $totalAmount > 0 && $clientObj->mobile !== '00000000000') {
                $pointEarnRate = floatval($siteSetting->point_earn_rate ?: 1);
                $pointsEarned = round($totalAmount * $pointEarnRate);

                if ($pointsEarned > 0) {
                    $clientObj->refresh();
                    $currentBal = floatval($clientObj->points_balance ?? 0);
                    $newBalAfterEarn = $currentBal + $pointsEarned;
                    $equivEarnTk = round($pointsEarned / floatval($siteSetting->point_redeem_rate ?: 10), 2);

                    ClientPointTransaction::create([
                        'client_id' => $clientId,
                        'invoice_id' => $invoice->id,
                        'type' => 'Earn',
                        'points_in' => $pointsEarned,
                        'points_out' => 0,
                        'balance' => $newBalAfterEarn,
                        'rate' => $pointEarnRate,
                        'amount_equivalent' => $equivEarnTk,
                        'description' => "Earned {$pointsEarned} loyalty points from Invoice #{$invoiceNo}",
                        'transaction_date' => date('Y-m-d'),
                        'status' => 'active',
                        'created_by' => auth()->id() ?? 1,
                        'created_ip' => $request->ip(),
                    ]);

                    $clientObj->points_balance = $newBalAfterEarn;
                    $clientObj->save();
                }
            }

            DB::commit();

            // Load full payload for printing
            $fullInvoice = Invoice::with([
                'client',
                'details' => function($q) {
                    $q->with('item:id,title,barcode', 'color:id,title', 'size:id,title');
                }
            ])->find($invoice->id);

            // Attach points info
            $fullInvoice->coupon_enabled = $couponEnabled;
            $fullInvoice->points_earned = $pointsEarned;
            $fullInvoice->points_redeemed = $pointsRedeemed;
            $fullInvoice->points_balance = $clientObj ? floatval($clientObj->points_balance) : 0;

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

    public function return(Request $request)
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

        // Calculate already returned quantities and remaining returnable for each item
        foreach ($invoices as $invoice) {
            foreach ($invoice->details as $detail) {
                $returnedQty = StockTransaction::where('reference_type', 'SalesReturn')
                    ->where('reference_id', (string)$invoice->id)
                    ->where('item_id', $detail->item_id)
                    ->where(function($q) use ($detail) {
                        if ($detail->color_id) {
                            $q->where('color_id', $detail->color_id);
                        } else {
                            $q->whereNull('color_id');
                        }
                    })
                    ->where(function($q) use ($detail) {
                        if ($detail->size_id) {
                            $q->where('size_id', $detail->size_id);
                        } else {
                            $q->whereNull('size_id');
                        }
                    })
                    ->where('status', 'active')
                    ->sum('qty_in');

                $detail->already_returned_qty = floatval($returnedQty);
                $detail->remaining_returnable_qty = max(0, floatval($detail->qty) - floatval($returnedQty));
            }
        }

        return response()->json($invoices);
    }

    public function processReturn(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'return_items' => 'required|array|min:1',
            'return_items.*.item_id' => 'required|exists:items,id',
            'return_items.*.qty' => 'required|numeric|min:0.01',
            'return_items.*.rate' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $invoice = Invoice::with('details')->findOrFail($request->invoice_id);
            $returnItems = $request->return_items;
            $totalRefund = 0;

            foreach ($returnItems as $item) {
                $qty = floatval($item['qty']);
                if ($qty <= 0) continue;

                $itemId = $item['item_id'];
                $colorId = !empty($item['color_id']) ? $item['color_id'] : null;
                $sizeId = !empty($item['size_id']) ? $item['size_id'] : null;
                $rate = floatval($item['rate']);

                // Find matching line in invoice details
                $matchingDetail = $invoice->details->first(function ($d) use ($itemId, $colorId, $sizeId) {
                    return $d->item_id == $itemId &&
                        (($colorId === null && empty($d->color_id)) || $d->color_id == $colorId) &&
                        (($sizeId === null && empty($d->size_id)) || $d->size_id == $sizeId);
                });

                if (!$matchingDetail) {
                    throw new Exception("পণ্যটি (Item ID: {$itemId}) এই ইনভয়েসের অন্তর্ভুক্ত নয়!");
                }

                // Check previously returned quantity in stock_transactions for this invoice
                $alreadyReturned = StockTransaction::where('reference_type', 'SalesReturn')
                    ->where('reference_id', (string)$invoice->id)
                    ->where('item_id', $itemId)
                    ->where(function($q) use ($colorId) {
                        if ($colorId) {
                            $q->where('color_id', $colorId);
                        } else {
                            $q->whereNull('color_id');
                        }
                    })
                    ->where(function($q) use ($sizeId) {
                        if ($sizeId) {
                            $q->where('size_id', $sizeId);
                        } else {
                            $q->whereNull('size_id');
                        }
                    })
                    ->where('status', 'active')
                    ->sum('qty_in');

                $maxReturnable = max(0, floatval($matchingDetail->qty) - floatval($alreadyReturned));

                // 🛑 STRICT VALIDATION: Return qty cannot exceed purchased or remaining qty
                if ($qty > $maxReturnable) {
                    $itemTitle = $matchingDetail->item ? $matchingDetail->item->title : "Item #{$itemId}";
                    throw new Exception("ভুল পরিমাণ! '{$itemTitle}' পণ্যের ক্ষেত্রে ফেরতযোগ্য সর্বোচ্চ পরিমাণ হলো {$maxReturnable} (ইনভয়েসে বিক্রয়: {$matchingDetail->qty}, পূর্বের ফেরত: {$alreadyReturned})। আপনি দিয়েছেন: {$qty}।");
                }

                $itemRefund = $qty * $rate;
                $totalRefund += $itemRefund;

                // Add stock back in
                StockTransaction::create([
                    'item_id' => $itemId,
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

            if ($totalRefund <= 0) {
                throw new Exception("ফেরত দেওয়ার জন্য কোনো বৈধ পণ্য বা পরিমাণ পাওয়া যায়নি।");
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

    public function convertPoints(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'points' => 'required|numeric|min:1',
        ]);

        $siteSetting = SiteSetting::first();
        if (!$siteSetting || !$siteSetting->coupon_enabled) {
            return response()->json(['message' => 'Coupon & Points System is not enabled in settings.'], 422);
        }

        $client = Client::findOrFail($request->client_id);
        $points = floatval($request->points);

        if ($client->points_balance < $points) {
            return response()->json(['message' => 'Insufficient points balance.'], 422);
        }

        $minPoints = intval($siteSetting->min_points_to_redeem ?? 10);
        if ($points < $minPoints) {
            return response()->json(['message' => "Minimum {$minPoints} points required to convert."], 422);
        }

        $rate = floatval($siteSetting->point_redeem_rate ?: 10);
        $amountTk = round($points / $rate, 2);

        DB::beginTransaction();
        try {
            $newBal = max(0, $client->points_balance - $points);
            $client->points_balance = $newBal;
            $client->save();

            ClientPointTransaction::create([
                'client_id' => $client->id,
                'type' => 'Convert_To_Cash',
                'points_in' => 0,
                'points_out' => $points,
                'balance' => $newBal,
                'rate' => $rate,
                'amount_equivalent' => $amountTk,
                'description' => "Converted {$points} loyalty points to Tk. {$amountTk} cash",
                'transaction_date' => date('Y-m-d'),
                'status' => 'active',
                'created_by' => auth()->id() ?? 1,
                'created_ip' => $request->ip(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully converted {$points} points to Tk. {$amountTk}!",
                'points_balance' => $newBal,
                'amount_tk' => $amountTk,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Handle Label Printing view and AJAX items search for barcodes
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function labelprint(Request $request)
    {
        if ($request->format() == 'html' || (!$request->ajax() && !$request->wantsJson() && !$request->has('term') && !$request->has('allData') && !$request->has('from_barcode') && !$request->has('date_filter'))) {
            return view('admin.layouts.admin_app');
        }

        $query = Item::where('status', 'active');

        // Keyword Search (Barcode or Title)
        if ($request->filled('term')) {
            $term = trim($request->term);
            $query->where(function ($q) use ($term) {
                $q->where('barcode', 'like', "%{$term}%")
                  ->orWhere('title', 'like', "%{$term}%");
            });
        }

        // Category Filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Barcode Range Filter (From Barcode - To Barcode)
        if ($request->filled('from_barcode') && $request->filled('to_barcode')) {
            $fromBarcode = trim($request->from_barcode);
            $toBarcode = trim($request->to_barcode);
            if (is_numeric($fromBarcode) && is_numeric($toBarcode)) {
                $fromNum = (int)$fromBarcode;
                $toNum = (int)$toBarcode;
                if ($fromNum > $toNum) {
                    $tmp = $fromNum;
                    $fromNum = $toNum;
                    $toNum = $tmp;
                }
                $query->whereRaw("CAST(barcode AS UNSIGNED) BETWEEN ? AND ?", [$fromNum, $toNum]);
            } else {
                $query->whereBetween('barcode', [$fromBarcode, $toBarcode]);
            }
        } elseif ($request->filled('from_barcode')) {
            $fromBarcode = trim($request->from_barcode);
            if (is_numeric($fromBarcode)) {
                $query->whereRaw("CAST(barcode AS UNSIGNED) >= ?", [(int)$fromBarcode]);
            } else {
                $query->where('barcode', '>=', $fromBarcode);
            }
        } elseif ($request->filled('to_barcode')) {
            $toBarcode = trim($request->to_barcode);
            if (is_numeric($toBarcode)) {
                $query->whereRaw("CAST(barcode AS UNSIGNED) <= ?", [(int)$toBarcode]);
            } else {
                $query->where('barcode', '<=', $toBarcode);
            }
        }

        // New Items / Date Filter Preset
        if ($request->filled('date_filter')) {
            $dateFilter = $request->date_filter;
            if ($dateFilter === 'today') {
                $query->whereDate('created_at', now()->toDateString());
            } elseif ($dateFilter === 'yesterday') {
                $query->whereDate('created_at', now()->subDay()->toDateString());
            } elseif ($dateFilter === 'this_week' || $dateFilter === 'last_7_days') {
                $query->whereDate('created_at', '>=', now()->subDays(7)->toDateString());
            } elseif ($dateFilter === 'this_month' || $dateFilter === 'last_30_days') {
                $query->whereDate('created_at', '>=', now()->subDays(30)->toDateString());
            }
        }

        // Custom Date Range
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $fromDate = vue_to_server_date($request->from_date);
            $toDate = vue_to_server_date($request->to_date);
            if ($fromDate && $toDate) {
                $query->whereDate('created_at', '>=', $fromDate)
                      ->whereDate('created_at', '<=', $toDate);
            }
        } elseif ($request->filled('from_date')) {
            $fromDate = vue_to_server_date($request->from_date);
            if ($fromDate) {
                $query->whereDate('created_at', '>=', $fromDate);
            }
        } elseif ($request->filled('to_date')) {
            $toDate = vue_to_server_date($request->to_date);
            if ($toDate) {
                $query->whereDate('created_at', '<=', $toDate);
            }
        }

        // Has Barcode Filter
        if ($request->filled('has_barcode')) {
            if ($request->has_barcode === 'yes') {
                $query->whereNotNull('barcode')->where('barcode', '!=', '');
            } elseif ($request->has_barcode === 'no') {
                $query->where(function ($q) {
                    $q->whereNull('barcode')->orWhere('barcode', '');
                });
            }
        }

        // Price Filter
        if ($request->filled('min_price') && is_numeric($request->min_price)) {
            $query->where('opening_rate', '>=', $request->min_price);
        }
        if ($request->filled('max_price') && is_numeric($request->max_price)) {
            $query->where('opening_rate', '<=', $request->max_price);
        }

        if ($request->filled('limit') && is_numeric($request->limit)) {
            $query->limit((int)$request->limit);
        }

        $items = $query->with([
            'category:id,title',
            'unit:id,title',
            'itemPrices.color:id,title',
            'itemPrices.size:id,title',
            'stockSummaries.color:id,title',
            'stockSummaries.size:id,title',
        ])->latest('created_at')->get();

        return response()->json($items);
    }
}
