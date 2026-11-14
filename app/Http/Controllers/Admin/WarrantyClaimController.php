<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Carbon\Carbon;
use App\Models\Item;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceDetails;
use App\Models\PurchaseDetail;
use App\Models\WarrantyClaim;
use App\Models\WarrantyClaimLog;
use App\Models\Expense;
use App\Models\ExpenseDetail;
use App\Models\Payment;
use App\Models\PaymentDetail;
use App\Models\Account;
use App\Traits\VoucherTrait;
use App\Http\Resources\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Base\BaseController;

class WarrantyClaimController extends BaseController
{
    use VoucherTrait;
    /**
     * Display a listing of warranty claims.
     */
    public function index(Request $request)
    {
        $query = WarrantyClaim::with([
            'item:id,title,barcode',
            'color:id,title',
            'size:id,title',
            'client:id,name,mobile',
            'supplier:id,org_name,name,mobile',
            'invoice:id,invoice_no,invoice_date',
            'invoiceDetail:id,invoice_id,warranty_type,warranty_period,warranty_expires_at,serial_no',
            'creator:id,full_name,email',
        ])->latest();

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function ($q) use ($keyword) {
                $q->where('claim_no', 'like', "%{$keyword}%")
                  ->orWhere('serial_no', 'like', "%{$keyword}%")
                  ->orWhere('customer_mobile', 'like', "%{$keyword}%")
                  ->orWhere('customer_name', 'like', "%{$keyword}%")
                  ->orWhere('supplier_name', 'like', "%{$keyword}%")
                  ->orWhereHas('supplier', function ($sq) use ($keyword) {
                      $sq->where('org_name', 'like', "%{$keyword}%")
                         ->orWhere('name', 'like', "%{$keyword}%")
                         ->orWhere('mobile', 'like', "%{$keyword}%");
                  })
                  ->orWhereHas('item', function ($iq) use ($keyword) {
                      $iq->where('title', 'like', "%{$keyword}%")
                         ->orWhere('barcode', 'like', "%{$keyword}%");
                  })
                  ->orWhereHas('invoice', function ($nq) use ($keyword) {
                      $nq->where('invoice_no', 'like', "%{$keyword}%");
                  });
            });
        }

        if ($request->filled('claim_no')) {
            $query->where('claim_no', 'like', '%' . trim($request->claim_no) . '%');
        }

        if ($request->filled('serial_no')) {
            $query->where('serial_no', 'like', '%' . trim($request->serial_no) . '%');
        }

        if ($request->filled('customer_mobile')) {
            $query->where('customer_mobile', 'like', '%' . trim($request->customer_mobile) . '%');
        }

        if ($request->filled('customer_name')) {
            $query->where('customer_name', 'like', '%' . trim($request->customer_name) . '%');
        }

        if ($request->filled('current_status')) {
            $query->where('current_status', $request->current_status);
        }

        if ($request->filled('warranty_type')) {
            $query->where('warranty_type', $request->warranty_type);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('claim_date', [$request->from_date, $request->to_date]);
        } elseif ($request->filled('from_date')) {
            $query->where('claim_date', '>=', $request->from_date);
        } elseif ($request->filled('to_date')) {
            $query->where('claim_date', '<=', $request->to_date);
        }

        if ($request->allData) {
            return $query->get();
        } else {
            $datas = $query->paginate($request->pagination ?? 15);
            return new Resource($datas);
        }
    }

    /**
     * Show form view.
     */
    public function create()
    {
        return view('layouts.backend_app');
    }

    /**
     * Check serial number eligibility for warranty/guarantee claim.
     */
    public function checkSerial(Request $request)
    {
        $serialNo = trim($request->input('serial_no'));

        if (empty($serialNo)) {
            return response()->json([
                'found' => false,
                'message' => 'দয়া করে একটি সিরিয়াল নম্বর প্রদান করুন।',
            ], 422);
        }

        $invoiceDetail = null;

        // 0. If a specific invoice_detail_id is requested, load it directly
        if ($request->filled('invoice_detail_id')) {
            $invoiceDetail = InvoiceDetails::with([
                'invoice.client',
                'item.category:id,title',
                'color:id,title',
                'size:id,title',
            ])
            ->where('id', $request->input('invoice_detail_id'))
            ->where('status', 'active')
            ->first();
        }

        // 1. Search for serial number in sold items (InvoiceDetails)
        if (!$invoiceDetail) {
            $cleanInvNo = ltrim($serialNo, '#');

            $invoiceDetail = InvoiceDetails::with([
                'invoice.client',
                'item.category:id,title',
                'color:id,title',
                'size:id,title',
            ])
            ->where('serial_no', 'like', "%{$serialNo}%")
            ->where('status', 'active')
            ->latest('id')
            ->first();
        }

        // 1.1 If not found directly by serial, check if user provided an Invoice No / ID
        if (!$invoiceDetail) {
            $cleanInvNo = ltrim($serialNo, '#');
            $invoiceDetail = InvoiceDetails::with([
                'invoice.client',
                'item.category:id,title',
                'color:id,title',
                'size:id,title',
            ])
            ->whereHas('invoice', function ($iq) use ($serialNo, $cleanInvNo) {
                $iq->where('invoice_no', $serialNo)
                   ->orWhere('invoice_no', $cleanInvNo)
                   ->orWhere('id', $cleanInvNo);
            })
            ->where(function ($wq) {
                $wq->whereNotNull('warranty_period')
                   ->where('warranty_period', '!=', '')
                   ->orWhere(function ($wtq) {
                       $wtq->whereNotNull('warranty_type')
                           ->where('warranty_type', '!=', 'none');
                   });
            })
            ->where('status', 'active')
            ->latest('id')
            ->first();

            if (!$invoiceDetail) {
                $invoiceDetail = InvoiceDetails::with([
                    'invoice.client',
                    'item.category:id,title',
                    'color:id,title',
                    'size:id,title',
                ])
                ->whereHas('invoice', function ($iq) use ($serialNo, $cleanInvNo) {
                    $iq->where('invoice_no', $serialNo)
                       ->orWhere('invoice_no', $cleanInvNo)
                       ->orWhere('id', $cleanInvNo);
                })
                ->where('status', 'active')
                ->latest('id')
                ->first();
            }
        }

        // 2. If not sold, check if it exists in Purchase / GRN (unsold inventory)
        if (!$invoiceDetail) {
            $sourcing = $this->findSourcingRecord($serialNo);

            if ($sourcing && $sourcing['found']) {
                return response()->json([
                    'found' => true,
                    'sold' => false,
                    'eligible' => false,
                    'status_type' => 'unsold',
                    'message' => 'এই সিরিয়াল নম্বরটি ক্রয় করা হয়েছে তবে এখনও কোনো কাস্টমারের কাছে বিক্রয় (Sold) করা হয়নি!',
                    'serial_no' => $serialNo,
                    'invoice_id' => null,
                    'invoice_no' => 'N/A',
                    'item_id' => $sourcing['item_id'],
                    'item_title' => $sourcing['item_title'],
                    'item_barcode' => $sourcing['item_barcode'],
                    'color_id' => $sourcing['color_id'],
                    'color_title' => $sourcing['color_title'],
                    'size_id' => $sourcing['size_id'],
                    'size_title' => $sourcing['size_title'],
                    'warranty_type' => $sourcing['supplier_warranty_type'] ?: 'warranty',
                    'warranty_period' => $sourcing['supplier_warranty_period'] ?: '',
                    'supplier_info' => $sourcing,
                    'supplier_id' => $sourcing['supplier_id'],
                    'supplier_name' => $sourcing['supplier_name'],
                    'purchase_id' => $sourcing['purchase_id'],
                    'purchase_no' => $sourcing['purchase_no'],
                    'purchase_date' => $sourcing['purchase_date'],
                    'supplier_warranty_period' => $sourcing['supplier_warranty_period'],
                    'supplier_warranty_expiry_date' => $sourcing['supplier_warranty_expiry_date'],
                    'supplier_remaining_days' => $sourcing['supplier_remaining_days'],
                    'supplier_expired_days' => $sourcing['supplier_expired_days'],
                    'supplier_warranty_status' => $sourcing['supplier_warranty_status'],
                    'purchase_info' => [
                        'purchase_no' => $sourcing['purchase_no'],
                        'purchase_date' => $sourcing['purchase_date_formatted'],
                        'supplier' => $sourcing['supplier_name'],
                        'supplier_mobile' => $sourcing['supplier_mobile'],
                        'item_title' => $sourcing['item_title'],
                        'color' => $sourcing['color_title'],
                        'size' => $sourcing['size_title'],
                    ]
                ]);
            }

            return response()->json([
                'found' => false,
                'sold' => false,
                'eligible' => false,
                'status_type' => 'not_found',
                'message' => 'সিস্টেমে এই সিরিয়াল নম্বরের কোনো তথ্য পাওয়া যায়নি!',
            ]);
        }

        // 3. Serial is sold. Now analyze customer warranty policy & calculate expiration
        $item = $invoiceDetail->item;
        $invoice = $invoiceDetail->invoice;
        $client = $invoice ? $invoice->client : null;

        $saleDateRaw = $invoice && $invoice->getRawOriginal('invoice_date') 
            ? $invoice->getRawOriginal('invoice_date') 
            : ($invoice && $invoice->invoice_date ? $invoice->invoice_date : $invoiceDetail->created_at);
        try {
            $saleDate = Carbon::parse($saleDateRaw)->format('Y-m-d');
        } catch (Exception $e) {
            $saleDate = date('Y-m-d');
        }

        // ⭐️ Priority 1: Check warranty directly from invoice_details (sold terms), then fallback to item
        $warrantyPeriod = '';
        if (!empty($invoiceDetail->warranty_period) && trim($invoiceDetail->warranty_period) !== '') {
            $warrantyPeriod = trim($invoiceDetail->warranty_period);
        } elseif (!empty($invoiceDetail->warranty_duration) && trim($invoiceDetail->warranty_duration) !== '') {
            $warrantyPeriod = trim($invoiceDetail->warranty_duration);
        } elseif ($item && !empty($item->warranty_period) && trim($item->warranty_period) !== '') {
            $warrantyPeriod = trim($item->warranty_period);
        }

        $warrantyType = 'none';
        if (!empty($invoiceDetail->warranty_type) && trim($invoiceDetail->warranty_type) !== '' && $invoiceDetail->warranty_type !== 'none') {
            $warrantyType = trim($invoiceDetail->warranty_type);
        } elseif ($item && !empty($item->warranty_type) && trim($item->warranty_type) !== '' && $item->warranty_type !== 'none') {
            $warrantyType = trim($item->warranty_type);
        } elseif (!empty($warrantyPeriod)) {
            $warrantyType = (stripos($warrantyPeriod, 'guarantee') !== false) ? 'guarantee' : 'warranty';
        }

        $expiryDate = null;
        if (!empty($invoiceDetail->warranty_expires_at)) {
            try {
                $expiryDate = Carbon::parse($invoiceDetail->warranty_expires_at);
            } catch (Exception $e) {
                $expiryDate = null;
            }
        }
        if (!$expiryDate && !empty($warrantyPeriod)) {
            $expiryDate = $this->calculateExpiryDate($saleDate, $warrantyPeriod);
        }

        $isEligible = false;
        $remainingDays = 0;
        $expiredDays = 0;
        $statusType = 'eligible';
        $message = '';

        if ($warrantyType === 'none' || empty($warrantyPeriod)) {
            $statusType = 'no_warranty';
            $isEligible = false;
            $message = 'এই পণ্যটিতে কোনো কাস্টমার ওয়ারেন্টি বা গ্যারান্টি সুবিধা নেই।';
        } else {
            $today = Carbon::today();

            if ($expiryDate) {
                if ($today->lte($expiryDate)) {
                    $isEligible = true;
                    $remainingDays = $today->diffInDays($expiryDate, false);
                    $statusType = 'eligible';
                    $coverageName = ($warrantyType === 'guarantee') ? 'গ্যারান্টি' : 'ওয়ারেন্টি';
                    $message = "পণ্যটি {$coverageName}র আওতাভুক্ত! আরও {$remainingDays} দিন মেয়াদ বাকি আছে।";
                } else {
                    $isEligible = false;
                    $expiredDays = $expiryDate->diffInDays($today, false);
                    $statusType = 'expired';
                    $coverageName = ($warrantyType === 'guarantee') ? 'গ্যারান্টি' : 'ওয়ারেন্টি';
                    $message = "পণ্যটির {$coverageName}র মেয়াদ {$expiredDays} দিন আগে শেষ হয়ে গেছে!";
                }
            } else {
                $isEligible = true;
                $statusType = 'eligible';
                $message = "পণ্যটির ওয়ারেন্টি সক্রিয় রয়েছে।";
            }
        }

        // Check if there are previous claims on this serial
        $matchedSerial = !empty($invoiceDetail->serial_no) ? $invoiceDetail->serial_no : $serialNo;
        $existingClaims = WarrantyClaim::where(function ($cq) use ($matchedSerial, $serialNo) {
            $cq->where('serial_no', $matchedSerial)
              ->orWhere('serial_no', $serialNo);
        })
        ->latest('id')
        ->get(['id', 'claim_no', 'claim_date', 'current_status', 'problem_description']);

        $invoiceItems = [];
        if ($invoice) {
            $invoiceItems = InvoiceDetails::with(['item:id,title,barcode', 'color:id,title', 'size:id,title'])
                ->where('invoice_id', $invoice->id)
                ->where('status', 'active')
                ->get()
                ->map(function ($d) {
                    $wPeriod = !empty($d->warranty_period) ? $d->warranty_period : (!empty($d->warranty_duration) ? $d->warranty_duration : ($d->item ? $d->item->warranty_period : ''));
                    $wType = !empty($d->warranty_type) && $d->warranty_type !== 'none' ? $d->warranty_type : ($d->item && $d->item->warranty_type !== 'none' ? $d->item->warranty_type : 'none');
                    return [
                        'id' => $d->id,
                        'item_id' => $d->item_id,
                        'title' => $d->item ? $d->item->title : 'Item',
                        'serial_no' => $d->serial_no,
                        'color_title' => $d->color ? $d->color->title : null,
                        'size_title' => $d->size ? $d->size->title : null,
                        'warranty_type' => $wType,
                        'warranty_period' => $wPeriod,
                        'warranty_expires_at' => $d->warranty_expires_at,
                    ];
                });
        }

        // ⭐️ Priority 4: Look up Supplier Sourcing & Purchase Warranty Details for this sold item
        $sourcing = $this->findSourcingRecord($matchedSerial, $invoiceDetail->item_id, $invoiceDetail->color_id, $invoiceDetail->size_id);

        return response()->json([
            'found' => true,
            'sold' => true,
            'eligible' => $isEligible,
            'status_type' => $statusType,
            'message' => $message,
            'serial_no' => $matchedSerial,
            'invoice_id' => $invoice ? $invoice->id : null,
            'invoice_no' => $invoice ? ($invoice->invoice_no ?? $invoice->invoiceno ?? 'N/A') : 'N/A',
            'invoice_detail_id' => $invoiceDetail->id,
            'item_id' => $item ? $item->id : null,
            'item_title' => $item ? $item->title : 'Item',
            'item_barcode' => $item ? $item->barcode : '',
            'color_id' => $invoiceDetail->color_id,
            'color_title' => $invoiceDetail->color ? $invoiceDetail->color->title : null,
            'size_id' => $invoiceDetail->size_id,
            'size_title' => $invoiceDetail->size ? $invoiceDetail->size->title : null,
            'client_id' => $client ? $client->id : null,
            'customer_name' => $client ? $client->name : 'Walk-in Customer',
            'customer_mobile' => $client ? $client->mobile : '',
            'customer_address' => $client ? $client->address : '',
            'sale_date' => $saleDate,
            'warranty_type' => $warrantyType,
            'warranty_period' => $warrantyPeriod,
            'warranty_expiry_date' => $expiryDate ? $expiryDate->format('Y-m-d') : null,
            'remaining_days' => $remainingDays,
            'expired_days' => $expiredDays,
            'existing_claims' => $existingClaims,
            'invoice_items' => $invoiceItems,
            'supplier_info' => $sourcing,
            'supplier_id' => $sourcing ? $sourcing['supplier_id'] : null,
            'supplier_name' => $sourcing ? $sourcing['supplier_name'] : null,
            'purchase_id' => $sourcing ? $sourcing['purchase_id'] : null,
            'purchase_no' => $sourcing ? $sourcing['purchase_no'] : null,
            'purchase_date' => $sourcing ? $sourcing['purchase_date'] : null,
            'supplier_warranty_period' => $sourcing ? $sourcing['supplier_warranty_period'] : null,
            'supplier_warranty_expiry_date' => $sourcing ? $sourcing['supplier_warranty_expiry_date'] : null,
            'supplier_remaining_days' => $sourcing ? $sourcing['supplier_remaining_days'] : 0,
            'supplier_expired_days' => $sourcing ? $sourcing['supplier_expired_days'] : 0,
            'supplier_warranty_status' => $sourcing ? $sourcing['supplier_warranty_status'] : 'no_warranty',
        ]);
    }

    /**
     * Find purchase sourcing and supplier warranty record for a serial or item.
     */
    private function findSourcingRecord($serialNo, $itemId = null, $colorId = null, $sizeId = null)
    {
        $cleanSerial = trim((string)$serialNo);
        $pdRecord = null;
        $grnRecord = null;

        // 1. Check PurchaseDetail by serial
        if (!empty($cleanSerial)) {
            $pdCandidates = PurchaseDetail::with(['purchase.supplier', 'item', 'color:id,title', 'size:id,title'])
                ->whereNotNull('serial_no')
                ->where('serial_no', '!=', '')
                ->where(function ($q) use ($cleanSerial) {
                    $q->where('serial_no', $cleanSerial)
                      ->orWhere('serial_no', 'like', "%{$cleanSerial}%");
                })
                ->latest('id')
                ->get();

            foreach ($pdCandidates as $cand) {
                $serials = preg_split('/[\r\n,]+/', $cand->serial_no, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($serials as $s) {
                    if (trim($s) === $cleanSerial) {
                        $pdRecord = $cand;
                        break 2;
                    }
                }
            }

            if (!$pdRecord && $pdCandidates->isNotEmpty()) {
                $pdRecord = $pdCandidates->first();
            }
        }

        // 2. Check GrnDetail by serial
        if (!empty($cleanSerial) && \Illuminate\Support\Facades\Schema::hasTable('grn_details')) {
            $grnCandidates = \App\Models\GrnDetail::with(['grn.supplier', 'purchase_detail.purchase.supplier', 'item', 'color:id,title', 'size:id,title'])
                ->whereNotNull('serial_no')
                ->where('serial_no', '!=', '')
                ->where(function ($q) use ($cleanSerial) {
                    $q->where('serial_no', $cleanSerial)
                      ->orWhere('serial_no', 'like', "%{$cleanSerial}%");
                })
                ->latest('id')
                ->get();

            foreach ($grnCandidates as $gcand) {
                $serials = preg_split('/[\r\n,]+/', $gcand->serial_no, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($serials as $s) {
                    if (trim($s) === $cleanSerial) {
                        $grnRecord = $gcand;
                        break 2;
                    }
                }
            }

            if (!$grnRecord && $grnCandidates->isNotEmpty()) {
                $grnRecord = $grnCandidates->first();
            }
        }

        // 3. Fallback: Search by item_id (and color_id, size_id) if not found by serial
        if (!$pdRecord && !$grnRecord && $itemId) {
            $pQuery = PurchaseDetail::with(['purchase.supplier', 'item', 'color:id,title', 'size:id,title'])
                ->where('item_id', $itemId);
            if ($colorId) $pQuery->where('color_id', $colorId);
            if ($sizeId) $pQuery->where('size_id', $sizeId);
            $pdRecord = $pQuery->latest('id')->first();

            if (!$pdRecord && \Illuminate\Support\Facades\Schema::hasTable('grn_details')) {
                $gQuery = \App\Models\GrnDetail::with(['grn.supplier', 'purchase_detail.purchase.supplier', 'item', 'color:id,title', 'size:id,title'])
                    ->where('item_id', $itemId);
                if ($colorId) $gQuery->where('color_id', $colorId);
                if ($sizeId) $gQuery->where('size_id', $sizeId);
                $grnRecord = $gQuery->latest('id')->first();
            }
        }

        if (!$pdRecord && !$grnRecord) {
            return null;
        }

        // Extract Supplier model
        $supplier = null;
        if ($pdRecord && $pdRecord->purchase && $pdRecord->purchase->supplier) {
            $supplier = $pdRecord->purchase->supplier;
        } elseif ($grnRecord && $grnRecord->grn && $grnRecord->grn->supplier) {
            $supplier = $grnRecord->grn->supplier;
        } elseif ($grnRecord && $grnRecord->purchase_detail && $grnRecord->purchase_detail->purchase && $grnRecord->purchase_detail->purchase->supplier) {
            $supplier = $grnRecord->purchase_detail->purchase->supplier;
        }

        // Extract Purchase & GRN models and Dates
        $purchase = $pdRecord ? $pdRecord->purchase : ($grnRecord && $grnRecord->purchase_detail ? $grnRecord->purchase_detail->purchase : null);
        $grn = $grnRecord ? $grnRecord->grn : null;

        $purchaseDateRaw = null;
        if ($purchase) {
            $purchaseDateRaw = $purchase->getRawOriginal('purchase_date') ?: $purchase->purchase_date;
        } elseif ($grn) {
            $purchaseDateRaw = $grn->getRawOriginal('grn_date') ?: $grn->grn_date;
        }

        $purchaseDate = null;
        $purchaseDateFormatted = 'N/A';
        if ($purchaseDateRaw) {
            try {
                $pCarbon = Carbon::parse($purchaseDateRaw);
                $purchaseDate = $pCarbon->format('Y-m-d');
                $purchaseDateFormatted = $pCarbon->format('d M, Y');
            } catch (\Exception $e) {
                $purchaseDate = null;
            }
        }

        // Determine Item reference
        $item = ($pdRecord && $pdRecord->item) ? $pdRecord->item : (($grnRecord && $grnRecord->item) ? $grnRecord->item : null);

        // Supplier Warranty Terms:
        // Priority: GrnDetail warranty columns, then PurchaseDetail warranty columns, then Item default warranty
        $supplierWarrantyType = 'none';
        if ($grnRecord && !empty($grnRecord->warranty_type) && $grnRecord->warranty_type !== 'none') {
            $supplierWarrantyType = $grnRecord->warranty_type;
        } elseif ($pdRecord && !empty($pdRecord->warranty_type) && $pdRecord->warranty_type !== 'none') {
            $supplierWarrantyType = $pdRecord->warranty_type;
        } elseif ($item && !empty($item->warranty_type) && $item->warranty_type !== 'none') {
            $supplierWarrantyType = $item->warranty_type;
        }

        $supplierWarrantyPeriod = '';
        if ($grnRecord && !empty($grnRecord->warranty_period)) {
            $supplierWarrantyPeriod = trim($grnRecord->warranty_period);
        } elseif ($grnRecord && !empty($grnRecord->warranty_duration)) {
            $supplierWarrantyPeriod = trim($grnRecord->warranty_duration) . ' Months';
        } elseif ($pdRecord && !empty($pdRecord->warranty_duration)) {
            $supplierWarrantyPeriod = trim($pdRecord->warranty_duration) . ' Months';
        } elseif ($item && !empty($item->warranty_period)) {
            $supplierWarrantyPeriod = trim($item->warranty_period);
        }

        if ($supplierWarrantyType === 'none' && !empty($supplierWarrantyPeriod)) {
            $supplierWarrantyType = (stripos($supplierWarrantyPeriod, 'guarantee') !== false) ? 'guarantee' : 'warranty';
        }

        // Calculate Supplier Warranty Expiry Date
        $supplierExpiryDate = null;
        if ($grnRecord && !empty($grnRecord->warranty_expires_at)) {
            try {
                $supplierExpiryDate = Carbon::parse($grnRecord->warranty_expires_at);
            } catch (\Exception $e) {
                $supplierExpiryDate = null;
            }
        } elseif ($pdRecord && !empty($pdRecord->warranty_expires_at)) {
            try {
                $supplierExpiryDate = Carbon::parse($pdRecord->warranty_expires_at);
            } catch (\Exception $e) {
                $supplierExpiryDate = null;
            }
        }

        if (!$supplierExpiryDate && !empty($supplierWarrantyPeriod) && $purchaseDate) {
            $supplierExpiryDate = $this->calculateExpiryDate($purchaseDate, $supplierWarrantyPeriod);
        }

        // Expiry status and remaining / expired days calculation
        $today = Carbon::today();
        $supplierRemainingDays = 0;
        $supplierExpiredDays = 0;
        $supplierWarrantyStatus = 'no_warranty';
        $supplierWarrantyStatusText = 'ওয়ারেন্টি পলিসি নেই';

        if (empty($supplierWarrantyPeriod) || $supplierWarrantyType === 'none') {
            $supplierWarrantyStatus = 'no_warranty';
            $supplierWarrantyStatusText = 'ওয়ারেন্টি পলিসি নেই';
        } elseif ($supplierExpiryDate) {
            if ($today->lte($supplierExpiryDate)) {
                $supplierRemainingDays = $today->diffInDays($supplierExpiryDate, false);
                $supplierWarrantyStatus = 'active';
                $covLabel = ($supplierWarrantyType === 'guarantee') ? 'গ্যারান্টি' : 'ওয়ারেন্টি';
                $supplierWarrantyStatusText = "সাপ্লায়ার {$covLabel} সক্রিয় ({$supplierRemainingDays} দিন বাকি)";
            } else {
                $supplierExpiredDays = $supplierExpiryDate->diffInDays($today, false);
                $supplierWarrantyStatus = 'expired';
                $covLabel = ($supplierWarrantyType === 'guarantee') ? 'গ্যারান্টি' : 'ওয়ারেন্টি';
                $supplierWarrantyStatusText = "সাপ্লায়ার {$covLabel}র মেয়াদ উত্তীর্ণ ({$supplierExpiredDays} দিন আগে)";
            }
        } else {
            $supplierWarrantyStatus = 'active';
            $supplierWarrantyStatusText = 'সাপ্লায়ার ওয়ারেন্টি সক্রিয়';
        }

        $purchaseNo = $purchase ? ($purchase->invoiceno ?? ('PUR-' . $purchase->id)) : ($grn ? ($grn->grn_no ?? ('GRN-' . $grn->id)) : 'N/A');
        $grnNo = $grn ? $grn->grn_no : null;
        $challanNo = ($grn && $grn->challan_no) ? $grn->challan_no : null;
        $purchasePrice = $pdRecord ? $pdRecord->price : ($grnRecord ? $grnRecord->unit_price : 0);

        return [
            'found' => true,
            'item_id' => $item ? $item->id : null,
            'item_title' => $item ? $item->title : 'Item',
            'item_barcode' => $item ? $item->barcode : '',
            'color_id' => $pdRecord ? $pdRecord->color_id : ($grnRecord ? $grnRecord->color_id : null),
            'color_title' => ($pdRecord && $pdRecord->color) ? $pdRecord->color->title : (($grnRecord && $grnRecord->color) ? $grnRecord->color->title : null),
            'size_id' => $pdRecord ? $pdRecord->size_id : ($grnRecord ? $grnRecord->size_id : null),
            'size_title' => ($pdRecord && $pdRecord->size) ? $pdRecord->size->title : (($grnRecord && $grnRecord->size) ? $grnRecord->size->title : null),
            'supplier_id' => $supplier ? $supplier->id : null,
            'supplier_name' => $supplier ? ($supplier->org_name ?: $supplier->name) : 'N/A',
            'supplier_code' => $supplier ? $supplier->supid : null,
            'supplier_contact_person' => $supplier ? $supplier->name : null,
            'supplier_mobile' => $supplier ? $supplier->mobile : null,
            'supplier_email' => $supplier ? $supplier->email : null,
            'supplier_address' => $supplier ? $supplier->address : null,
            'purchase_id' => $purchase ? $purchase->id : null,
            'purchase_no' => $purchaseNo,
            'purchase_date' => $purchaseDate,
            'purchase_date_formatted' => $purchaseDateFormatted,
            'grn_id' => $grn ? $grn->id : null,
            'grn_no' => $grnNo,
            'challan_no' => $challanNo,
            'purchase_price' => $purchasePrice,
            'supplier_warranty_type' => $supplierWarrantyType,
            'supplier_warranty_period' => $supplierWarrantyPeriod,
            'supplier_warranty_expiry_date' => $supplierExpiryDate ? $supplierExpiryDate->format('Y-m-d') : null,
            'supplier_remaining_days' => $supplierRemainingDays,
            'supplier_expired_days' => $supplierExpiredDays,
            'supplier_warranty_status' => $supplierWarrantyStatus,
            'supplier_warranty_status_text' => $supplierWarrantyStatusText,
            'can_claim_from_vendor' => ($supplierWarrantyStatus === 'active'),
        ];
    }

    /**
     * Calculate expiry date from sale date and warranty period string.
     */
    private function calculateExpiryDate($saleDateStr, $warrantyPeriodStr)
    {
        try {
            $saleDate = Carbon::parse($saleDateStr);
            $period = strtolower(trim($warrantyPeriodStr));

            $bnDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
            $enDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            $period = str_replace($bnDigits, $enDigits, $period);
            $period = str_replace(['বছর', 'মাস', 'দিন', 'সপ্তাহ'], ['year', 'month', 'day', 'week'], $period);

            if (str_contains($period, 'lifetime')) {
                return $saleDate->copy()->addYears(50);
            }

            preg_match('/(\d+)\s*(year|yr|month|mon|day|d|week|wk)/i', $period, $matches);

            if (!empty($matches)) {
                $amount = intval($matches[1]);
                $unit = strtolower($matches[2]);

                if (str_starts_with($unit, 'y')) {
                    return $saleDate->copy()->addYears($amount);
                } elseif (str_starts_with($unit, 'm')) {
                    return $saleDate->copy()->addMonths($amount);
                } elseif (str_starts_with($unit, 'w')) {
                    return $saleDate->copy()->addWeeks($amount);
                } elseif (str_starts_with($unit, 'd')) {
                    return $saleDate->copy()->addDays($amount);
                }
            }

            // Fallback: check plain numbers or common words
            if (str_contains($period, 'year')) {
                return $saleDate->copy()->addYear();
            } elseif (str_contains($period, 'month')) {
                return $saleDate->copy()->addMonth();
            } elseif (str_contains($period, 'week')) {
                return $saleDate->copy()->addWeek();
            }

            return $saleDate->copy()->addYear();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Store a newly created warranty claim.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->all();
            if (empty($data['claim_no'])) {
                $data['claim_no'] = WarrantyClaim::generateClaimNo();
            }
            if (empty($data['claim_date'])) {
                $data['claim_date'] = date('Y-m-d');
            }

            // Verify / backfill warranty information from invoice_details if linked
            if (!empty($data['invoice_detail_id'])) {
                $soldInvDetail = InvoiceDetails::find($data['invoice_detail_id']);
                if ($soldInvDetail) {
                    if (empty($data['warranty_period']) && !empty($soldInvDetail->warranty_period)) {
                        $data['warranty_period'] = $soldInvDetail->warranty_period;
                    } elseif (empty($data['warranty_period']) && !empty($soldInvDetail->warranty_duration)) {
                        $data['warranty_period'] = $soldInvDetail->warranty_duration;
                    }
                    if (empty($data['warranty_type']) && !empty($soldInvDetail->warranty_type) && $soldInvDetail->warranty_type !== 'none') {
                        $data['warranty_type'] = $soldInvDetail->warranty_type;
                    }
                    if (empty($data['warranty_expiry_date']) && !empty($soldInvDetail->warranty_expires_at)) {
                        $data['warranty_expiry_date'] = $soldInvDetail->warranty_expires_at;
                    }
                    if (empty($data['serial_no']) && !empty($soldInvDetail->serial_no)) {
                        $data['serial_no'] = $soldInvDetail->serial_no;
                    }
                }
            }

            // Sanitize all date fields to Y-m-d or null
            $dateFields = ['sale_date', 'claim_date', 'warranty_expiry_date', 'expected_delivery_date', 'purchase_date', 'supplier_warranty_expiry_date'];
            foreach ($dateFields as $field) {
                if (!empty($data[$field])) {
                    try {
                        $data[$field] = Carbon::parse($data[$field])->format('Y-m-d');
                    } catch (Exception $e) {
                        $data[$field] = null;
                    }
                } else {
                    $data[$field] = null;
                }
            }

            // Auto-backfill supplier sourcing if not provided
            if (empty($data['supplier_id']) && (!empty($data['serial_no']) || !empty($data['item_id']))) {
                $src = $this->findSourcingRecord($data['serial_no'] ?? '', $data['item_id'] ?? null, $data['color_id'] ?? null, $data['size_id'] ?? null);
                if ($src && $src['found']) {
                    if (empty($data['supplier_id'])) $data['supplier_id'] = $src['supplier_id'];
                    if (empty($data['supplier_name'])) $data['supplier_name'] = $src['supplier_name'];
                    if (empty($data['purchase_id'])) $data['purchase_id'] = $src['purchase_id'];
                    if (empty($data['purchase_no'])) $data['purchase_no'] = $src['purchase_no'];
                    if (empty($data['purchase_date'])) $data['purchase_date'] = $src['purchase_date'];
                    if (empty($data['supplier_warranty_period'])) $data['supplier_warranty_period'] = $src['supplier_warranty_period'];
                    if (empty($data['supplier_warranty_expiry_date'])) $data['supplier_warranty_expiry_date'] = $src['supplier_warranty_expiry_date'];
                }
            }

            $data['created_by'] = auth('admin')->id();
            $data['created_ip'] = $request->ip();

            $claim = WarrantyClaim::create($data);

            // Synchronize financial transactions (Expense & Payment vouchers)
            $this->syncFinancialTransactions($claim);

            // Log initial tracking status
            WarrantyClaimLog::create([
                'warranty_claim_id' => $claim->id,
                'status' => $claim->current_status ?? 'received',
                'remarks' => $request->input('initial_remarks') ?? 'Claim created with status: ' . ($claim->current_status ?? 'received'),
                'reminder_date' => $claim->expected_delivery_date,
                'created_by' => auth('admin')->id(),
                'created_ip' => $request->ip(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Warranty Claim created successfully',
                'data' => $claim,
            ]);
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json(['exception' => $ex->getMessage()], 422);
        }
    }

    /**
     * Display full warranty claim details and tracking logs.
     */
    public function show(Request $request, $id)
    {
        $claim = WarrantyClaim::with([
            'item.category',
            'color',
            'size',
            'client',
            'supplier',
            'purchase.supplier',
            'invoice.client',
            'invoiceDetail',
            'expense.expense_details.account',
            'payment.payment_details.account',
            'logs.creator:id,full_name,email',
            'creator:id,full_name,email',
        ])->findOrFail($id);

        return response()->json($claim);
    }

    /**
     * Update claim details.
     */
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $claim = WarrantyClaim::findOrFail($id);
            $data = $request->all();

            // If payment was already received for customer charge, protect customer_charge from being altered
            if ($claim->payment_id) {
                unset($data['customer_charge']);
            }

            // Sanitize all date fields to Y-m-d or null
            $dateFields = ['sale_date', 'claim_date', 'warranty_expiry_date', 'expected_delivery_date', 'purchase_date', 'supplier_warranty_expiry_date'];
            foreach ($dateFields as $field) {
                if (!empty($data[$field])) {
                    try {
                        $data[$field] = Carbon::parse($data[$field])->format('Y-m-d');
                    } catch (Exception $e) {
                        $data[$field] = null;
                    }
                } else {
                    $data[$field] = null;
                }
            }

            $data['updated_by'] = auth('admin')->id();
            $data['updated_ip'] = $request->ip();

            $oldStatus = $claim->current_status;
            $claim->fill($data)->save();

            // Synchronize financial transactions (Expense & Payment vouchers)
            $this->syncFinancialTransactions($claim);

            // If status was changed during edit, add a log entry automatically
            if (!empty($data['current_status']) && $data['current_status'] !== $oldStatus) {
                WarrantyClaimLog::create([
                    'warranty_claim_id' => $claim->id,
                    'status' => $data['current_status'],
                    'remarks' => $request->input('log_remarks') ?? "Status updated from {$oldStatus} to {$data['current_status']}",
                    'reminder_date' => $claim->expected_delivery_date,
                    'created_by' => auth('admin')->id(),
                    'created_ip' => $request->ip(),
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Warranty Claim updated successfully',
                'data' => $claim,
            ]);
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json(['exception' => $ex->getMessage()], 422);
        }
    }

    /**
     * Add a new tracking status log / reminder / remark to the claim.
     */
    public function addTrackingLog(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $claim = WarrantyClaim::findOrFail($id);

            $log = WarrantyClaimLog::create([
                'warranty_claim_id' => $claim->id,
                'status' => $request->input('status'),
                'remarks' => $request->input('remarks'),
                'reminder_date' => $request->input('reminder_date'),
                'created_by' => auth('admin')->id(),
                'created_ip' => $request->ip(),
            ]);

            // Update parent claim status
            $claim->current_status = $request->input('status');
            if ($request->input('status') === 'delivered') {
                $claim->delivered_date = date('Y-m-d');
            }
            if ($request->filled('service_cost')) {
                $claim->service_cost = $request->input('service_cost');
            }
            // Only update customer_charge if payment has not yet been processed
            if ($request->filled('customer_charge') && !$claim->payment_id) {
                $claim->customer_charge = $request->input('customer_charge');
            }
            $claim->updated_by = auth('admin')->id();
            $claim->updated_ip = $request->ip();
            $claim->save();

            // Synchronize financial transactions (Expense & Payment vouchers)
            $this->syncFinancialTransactions($claim);

            DB::commit();

            // Return refreshed claim with logs and financial relations
            $claim->load([
                'item.category',
                'color',
                'size',
                'client',
                'invoice.client',
                'expense.expense_details.account',
                'payment.payment_details.account',
                'logs.creator:id,full_name,email',
                'creator:id,full_name,email',
            ]);

            return response()->json([
                'message' => 'Tracking log added successfully',
                'data' => $claim,
            ]);
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json(['exception' => $ex->getMessage()], 422);
        }
    }

    /**
     * Synchronize Office Expense and Payment Receive records for warranty claim financial amounts.
     */
    protected function syncFinancialTransactions(WarrantyClaim $claim)
    {
        // 1. Internal Service Cost (Office Expense & Expense Voucher)
        if ($claim->service_cost > 0 && !$claim->expense_id) {
            $expenseAccount = Account::where('account_type', 'Expense')
                ->where(function ($q) {
                    $q->where('system_key_name', 'Expense')
                      ->orWhere('system_key_name', 'office-supplies-expense')
                      ->orWhere('account_name', 'like', '%Office Expense%');
                })->first() ?? Account::where('account_type', 'Expense')->first();

            $expenseAccountId = $expenseAccount ? $expenseAccount->id : null;

            $expense = Expense::create([
                'expenseid'     => Expense::generateExpenseID(),
                'expense_date'  => $claim->claim_date ? Carbon::parse($claim->claim_date)->format('Y-m-d') : date('Y-m-d'),
                'total_amount'  => $claim->service_cost,
                'approved_by'   => auth('admin')->id() ?? $claim->created_by,
                'approved_date' => now(),
                'status'        => 'active',
                'created_by'    => auth('admin')->id() ?? $claim->created_by,
                'created_ip'    => request()->ip() ?? '127.0.0.1',
            ]);

            $expenseDetail = $expense->expense_details()->create([
                'account_id' => $expenseAccountId,
                'amount'     => $claim->service_cost,
                'narration'  => "Internal Service Cost for Warranty Claim #{$claim->claim_no} (Serial: {$claim->serial_no})",
                'status'     => 'active',
                'created_by' => auth('admin')->id() ?? $claim->created_by,
                'created_ip' => request()->ip() ?? '127.0.0.1',
            ]);

            $vdata = [
                'module'     => 'ExpenseDetail',
                'date'       => $expense->expense_date,
                'amount'     => $expenseDetail->amount,
                'source_id'  => $expenseDetail->id,
                'ref_id'     => $expense->employee_id ?? null,
                'account_id' => $expenseDetail->account_id,
            ];
            $this->createPayableVoucher($vdata);

            $claim->expense_id = $expense->id;
            $claim->saveQuietly();
        }

        // 2. Customer Charge (Payment Receive & Voucher)
        if ($claim->customer_charge > 0 && !$claim->payment_id) {
            $fundAccount = Account::where('is_fund_account', 1)->where('system_key_name', 'Cash')->first()
                ?? Account::where('is_fund_account', 1)->first()
                ?? Account::where('system_key_name', 'Cash')->first();
            $fundAccountId = $fundAccount ? $fundAccount->id : null;

            $revenueAccount = Account::where('system_key_name', 'accounts-receivable')->first()
                ?? Account::where('account_type', 'Income')->first()
                ?? Account::where('account_name', 'like', '%Service%')->first();
            $revenueAccountId = $revenueAccount ? $revenueAccount->id : null;

            $clientId = $claim->client_id;
            if (!$clientId && !empty($claim->customer_mobile)) {
                $matchedClient = Client::where('mobile', $claim->customer_mobile)->first();
                $clientId = $matchedClient ? $matchedClient->id : null;
            }
            if (!$clientId) {
                $clientId = Client::first()?->id;
            }

            $detailAccountId = $clientId ? ($this->getClientAccount($clientId) ?? $revenueAccountId) : $revenueAccountId;

            $payment = Payment::create([
                'payslipno'       => Payment::getPaySlipNo(),
                'payment_type'    => 'Receive',
                'client_id'       => $clientId,
                'payment_date'    => $claim->claim_date ? Carbon::parse($claim->claim_date)->format('Y-m-d') : date('Y-m-d'),
                'discount'        => 0,
                'amount'          => $claim->customer_charge,
                'fund_account_id' => $fundAccountId,
                'payment_method'  => 'Cash',
                'status'          => 'active',
                'created_by'      => auth('admin')->id() ?? $claim->created_by,
                'created_ip'      => request()->ip() ?? '127.0.0.1',
            ]);

            $payment->payment_details()->create([
                'payment_id'     => $payment->id,
                'reference_type' => 'WarrantyClaim',
                'reference_id'   => $claim->id,
                'account_id'     => $detailAccountId,
                'amount'         => $claim->customer_charge,
                'is_closed'      => 1,
                'status'         => 'active',
                'created_by'     => auth('admin')->id() ?? $claim->created_by,
                'created_ip'     => request()->ip() ?? '127.0.0.1',
            ]);

            $payment->load('payment_details');
            $this->createVoucherFromPayment($payment, $payment->id);

            $claim->payment_id = $payment->id;
            $claim->saveQuietly();
        }
    }

    /**
     * Remove the specified claim.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $claim = WarrantyClaim::findOrFail($id);
            $claim->delete();
            DB::commit();

            return response()->json(['message' => 'Warranty Claim deleted successfully']);
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json(['exception' => $ex->getMessage()], 422);
        }
    }
}
