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
use App\Http\Resources\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Base\BaseController;

class WarrantyClaimController extends BaseController
{
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
            'invoice:id,invoice_no,invoice_date',
            'creator:id,name',
        ])->latest();

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function ($q) use ($keyword) {
                $q->where('claim_no', 'like', "%{$keyword}%")
                  ->orWhere('serial_no', 'like', "%{$keyword}%")
                  ->orWhere('customer_mobile', 'like', "%{$keyword}%")
                  ->orWhere('customer_name', 'like', "%{$keyword}%")
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

        // 1. Search for serial number in sold items (InvoiceDetails)
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

        // 2. If not sold, check if it exists in Purchase (unsold inventory)
        if (!$invoiceDetail) {
            $purchaseDetail = PurchaseDetail::with([
                'purchase.supplier',
                'item.category:id,title',
                'color:id,title',
                'size:id,title',
            ])
            ->where('serial_no', 'like', "%{$serialNo}%")
            ->latest('id')
            ->first();

            if ($purchaseDetail) {
                $pItem = $purchaseDetail->item;
                return response()->json([
                    'found' => true,
                    'sold' => false,
                    'eligible' => false,
                    'status_type' => 'unsold',
                    'message' => 'এই সিরিয়াল নম্বরটি ক্রয় করা হয়েছে তবে এখনও বিক্রয় (Sold) করা হয়নি!',
                    'serial_no' => $serialNo,
                    'invoice_id' => null,
                    'invoice_no' => 'N/A',
                    'item_id' => $pItem ? $pItem->id : null,
                    'item_title' => $pItem ? $pItem->title : 'Item',
                    'item_barcode' => $pItem ? $pItem->barcode : '',
                    'color_id' => $purchaseDetail->color_id,
                    'color_title' => $purchaseDetail->color ? $purchaseDetail->color->title : null,
                    'size_id' => $purchaseDetail->size_id,
                    'size_title' => $purchaseDetail->size ? $purchaseDetail->size->title : null,
                    'warranty_type' => $pItem && $pItem->warranty_type ? $pItem->warranty_type : 'warranty',
                    'warranty_period' => $pItem && $pItem->warranty_period ? $pItem->warranty_period : '',
                    'purchase_info' => [
                        'purchase_no' => $purchaseDetail->purchase ? $purchaseDetail->purchase->id : 'N/A',
                        'purchase_date' => $purchaseDetail->purchase ? $purchaseDetail->purchase->purchase_date : 'N/A',
                        'supplier' => $purchaseDetail->purchase && $purchaseDetail->purchase->supplier ? $purchaseDetail->purchase->supplier->org_name : 'N/A',
                        'item_title' => $purchaseDetail->item ? $purchaseDetail->item->title : 'Item',
                        'color' => $purchaseDetail->color ? $purchaseDetail->color->title : null,
                        'size' => $purchaseDetail->size ? $purchaseDetail->size->title : null,
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

        // 3. Serial is sold. Now analyze warranty policy & calculate expiration
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
        $warrantyType = $item && $item->warranty_type ? $item->warranty_type : 'none';
        $warrantyPeriod = $item && $item->warranty_period ? $item->warranty_period : '';

        $expiryDate = null;
        $isEligible = false;
        $remainingDays = 0;
        $expiredDays = 0;
        $statusType = 'eligible';
        $message = '';

        if ($warrantyType === 'none' || empty($warrantyPeriod)) {
            $statusType = 'no_warranty';
            $isEligible = false;
            $message = 'এই পণ্যটিতে কোনো ওয়ারেন্টি বা গ্যারান্টি সুবিধা নেই।';
        } else {
            $expiryDate = $this->calculateExpiryDate($saleDate, $warrantyPeriod);
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
        $existingClaims = WarrantyClaim::where('serial_no', $serialNo)
            ->latest('id')
            ->get(['id', 'claim_no', 'claim_date', 'current_status', 'problem_description']);

        return response()->json([
            'found' => true,
            'sold' => true,
            'eligible' => $isEligible,
            'status_type' => $statusType,
            'message' => $message,
            'serial_no' => $serialNo,
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
        ]);
    }

    /**
     * Calculate expiry date from sale date and warranty period string.
     */
    private function calculateExpiryDate($saleDateStr, $warrantyPeriodStr)
    {
        try {
            $saleDate = Carbon::parse($saleDateStr);
            $period = strtolower(trim($warrantyPeriodStr));

            if (str_contains($period, 'lifetime')) {
                return $saleDate->copy()->addYears(50);
            }

            preg_match('/(\d+)\s*(year|yr|month|mon|day|d)/i', $period, $matches);

            if (!empty($matches)) {
                $amount = intval($matches[1]);
                $unit = strtolower($matches[2]);

                if (str_starts_with($unit, 'y')) {
                    return $saleDate->copy()->addYears($amount);
                } elseif (str_starts_with($unit, 'm')) {
                    return $saleDate->copy()->addMonths($amount);
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

            // Sanitize all date fields to Y-m-d or null
            $dateFields = ['sale_date', 'claim_date', 'warranty_expiry_date', 'expected_delivery_date'];
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

            $data['created_by'] = auth('admin')->id();
            $data['created_ip'] = $request->ip();

            $claim = WarrantyClaim::create($data);

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
            'invoice.client',
            'logs.creator:id,name',
            'creator:id,name',
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

            // Sanitize all date fields to Y-m-d or null
            $dateFields = ['sale_date', 'claim_date', 'warranty_expiry_date', 'expected_delivery_date'];
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
            if ($request->filled('customer_charge')) {
                $claim->customer_charge = $request->input('customer_charge');
            }
            $claim->updated_by = auth('admin')->id();
            $claim->updated_ip = $request->ip();
            $claim->save();

            DB::commit();

            // Return refreshed claim with logs
            $claim->load(['logs.creator:id,name']);

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
