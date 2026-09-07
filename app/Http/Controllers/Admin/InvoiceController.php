<?php

/**
 * @Quill Information Technology
 */

namespace App\Http\Controllers\Admin;

use Storage;
use Exception;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\InvoiceDetails;
use App\Models\Voucher;
use App\Models\InvoiceMonth;
use Illuminate\Http\Request;
use App\Models\PaymentDetail;
use App\Models\VoucherDetail;
use App\Http\Resources\Resource;
use App\Traits\InvoiceTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Base\BaseController;
use Illuminate\Pagination\LengthAwarePaginator;

class InvoiceController extends BaseController
{
    use InvoiceTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Invoice::with([
            'client:id,clientid,name,org_name,mobile',
            'details' => function ($q) {
                $q->with('item:id,title,barcode', 'color:id,title', 'size:id,title');
            },
            'payment_details:id,payment_id,reference_type,reference_id,amount,is_closed'
        ]);

        // ✅ 1. Keyword Filter (Invoice No, Customer Name, Mobile, Org Name, Product Title, Barcode, Serial)
        if (!empty($request->keyword)) {
            $kw = trim($request->keyword);
            $query->where(function ($q) use ($kw) {
                $q->where('invoice_no', 'like', "%{$kw}%")
                  ->orWhereHas('client', function ($cq) use ($kw) {
                      $cq->where('mobile', 'like', "%{$kw}%")
                         ->orWhere('name', 'like', "%{$kw}%")
                         ->orWhere('org_name', 'like', "%{$kw}%")
                         ->orWhere('clientid', 'like', "%{$kw}%");
                  })
                  ->orWhereHas('details.item', function ($iq) use ($kw) {
                      $iq->where('title', 'like', "%{$kw}%")
                         ->orWhere('barcode', 'like', "%{$kw}%");
                  });
            });
        }

        // ✅ 2. Client Filter
        if (!empty($request->client_id)) {
            $query->where('client_id', $request->client_id);
        }

        // ✅ 3. Walk-in Customer Filter
        if (isset($request->customer_type) && $request->customer_type !== '') {
            if ($request->customer_type === 'walkin') {
                $query->whereNull('client_id');
            } elseif ($request->customer_type === 'registered') {
                $query->whereNotNull('client_id');
            }
        }

        // ✅ 4. Invoice Date Range Filter
        if (!empty($request->from_invoice_date) && !empty($request->to_invoice_date)) {
            $query->whereBetween('invoice_date', [
                date('Y-m-d', strtotime($request->from_invoice_date)),
                date('Y-m-d', strtotime($request->to_invoice_date))
            ]);
        } elseif (!empty($request->from_invoice_date)) {
            $query->whereDate('invoice_date', '>=', date('Y-m-d', strtotime($request->from_invoice_date)));
        } elseif (!empty($request->to_invoice_date)) {
            $query->whereDate('invoice_date', '<=', date('Y-m-d', strtotime($request->to_invoice_date)));
        }

        // ✅ 5. Payment Status Filter (Paid, Partial, Due, Unpaid)
        if (!empty($request->payment_status)) {
            if ($request->payment_status === 'paid') {
                $query->whereRaw('paid_amount >= amount');
            } elseif ($request->payment_status === 'partial') {
                $query->whereRaw('paid_amount > 0 AND paid_amount < amount');
            } elseif ($request->payment_status === 'due') {
                $query->whereRaw('(paid_amount < amount OR paid_amount IS NULL)');
            } elseif ($request->payment_status === 'unpaid') {
                $query->whereRaw('(paid_amount = 0 OR paid_amount IS NULL)');
            }
        }

        // ✅ 6. Amount Range Filter
        if (!empty($request->min_amount)) {
            $query->where('amount', '>=', (float)$request->min_amount);
        }
        if (!empty($request->max_amount)) {
            $query->where('amount', '<=', (float)$request->max_amount);
        }

        // ✅ 7. Due Amount Range Filter
        if (!empty($request->min_due)) {
            $query->whereRaw('(amount - COALESCE(paid_amount, 0)) >= ?', [(float)$request->min_due]);
        }
        if (!empty($request->max_due)) {
            $query->whereRaw('(amount - COALESCE(paid_amount, 0)) <= ?', [(float)$request->max_due]);
        }
        if (!empty($request->has_due) && $request->has_due == '1') {
            $query->whereRaw('(amount - COALESCE(paid_amount, 0)) > 0');
        }

        // ✅ 8. Created By / Cashier Filter
        if (!empty($request->created_by)) {
            $query->where('created_by', $request->created_by);
        }

        // ✅ 9. Product / Item Filter
        if (!empty($request->item_id)) {
            $query->whereHas('details', function ($q) use ($request) {
                $q->where('item_id', $request->item_id);
            });
        }

        // ✅ 10. Barcode Filter
        if (!empty($request->barcode)) {
            $barcode = trim($request->barcode);
            $query->whereHas('details.item', function ($q) use ($barcode) {
                $q->where('barcode', 'like', "%{$barcode}%");
            });
        }

        // ✅ 11. Closed / Active Status
        if (isset($request->is_closed) && $request->is_closed !== '') {
            $query->where('is_closed', $request->is_closed);
        }
        if (!empty($request->status)) {
            $query->where('status', $request->status);
        }

        // ✅ 12. Generic field search
        if (!empty($request->field_name) && !empty($request->value)) {
            $query->whereLike($request->field_name, $request->value);
        }

        // Calculate KPI Metrics for current filtered dataset
        $totalInvoicesCount = (clone $query)->count();
        $totalSalesAmount = (clone $query)->sum('amount');
        $totalPaidAmount = (clone $query)->sum('paid_amount');
        $totalDueAmount = max(0, $totalSalesAmount - $totalPaidAmount);

        // ✅ 13. Dynamic Sorting
        $sortBy = $request->sort_by ?? 'id';
        $sortOrder = strtolower($request->sort_order ?? 'desc') === 'asc' ? 'asc' : 'desc';

        if ($sortBy === 'invoice_date') {
            $query->orderBy('invoice_date', $sortOrder)->orderBy('id', $sortOrder);
        } elseif ($sortBy === 'amount') {
            $query->orderBy('amount', $sortOrder);
        } elseif ($sortBy === 'paid_amount') {
            $query->orderBy('paid_amount', $sortOrder);
        } elseif ($sortBy === 'due_amount') {
            $query->orderByRaw("(amount - COALESCE(paid_amount, 0)) {$sortOrder}");
        } elseif ($sortBy === 'invoice_no') {
            $query->orderBy('invoice_no', $sortOrder);
        } else {
            $query->orderBy('id', $sortOrder);
        }

        if ($request->allData) {
            return $query->get();
        } else {
            $perPage = (int)($request->pagination ?? 15);
            $currentPage = (int)($request->page ?? 1);
            $datas = $query->paginate($perPage, ['*'], 'page', $currentPage);
            $resource = new Resource($datas);
            $resource->additional([
                'kpi' => [
                    'total_invoices' => $totalInvoicesCount,
                    'total_sales' => $totalSalesAmount,
                    'total_paid' => $totalPaidAmount,
                    'total_due' => $totalDueAmount,
                ]
            ]);
            return $resource;
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return redirect('/pos');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return response()->json(['exception' => 'ইনভয়েস তৈরির জন্য দয়া করে POS টার্মিনাল ব্যবহার করুন।'], 422);
    }

    /**
     * Display the specified resource with rich details, client history, loyalty points, and item stock analytics.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $invoice = Invoice::with([
            'client',
            'details' => function ($q) {
                $q->with([
                    'item' => function ($iq) {
                        $iq->with('category:id,title', 'unit:id,title');
                    },
                    'color:id,title',
                    'size:id,title'
                ]);
            },
            'payment_details.payment'
        ])->findOrFail($id);

        // 1. Client History Summary
        $clientHistory = null;
        $loyaltyPoints = null;

        $siteSetting = \App\Models\System\SiteSetting::first();
        $couponEnabled = boolval($siteSetting->coupon_enabled ?? 0);
        $pointRedeemRate = floatval($siteSetting->point_redeem_rate ?: 10);

        if ($invoice->client) {
            $clientId = $invoice->client->id;
            $allClientInvoices = Invoice::where('client_id', $clientId)->get();
            $lifetimeSales = $allClientInvoices->sum('amount');
            $lifetimePaid = $allClientInvoices->sum('paid_amount');

            $clientHistory = [
                'total_orders' => $allClientInvoices->count(),
                'lifetime_sales' => $lifetimeSales,
                'lifetime_paid' => $lifetimePaid,
                'lifetime_due' => max(0, $lifetimeSales - $lifetimePaid),
                'current_due' => $invoice->client->current_due ?? 0,
            ];

            // Points earned & redeemed in this invoice
            $pointsEarnedThis = \App\Models\ClientPointTransaction::where('invoice_id', $invoice->id)
                ->where('type', 'Earn')
                ->sum('points_in');

            $pointsRedeemedThis = \App\Models\ClientPointTransaction::where('invoice_id', $invoice->id)
                ->where('type', 'Redeem')
                ->sum('points_out');

            $loyaltyPoints = [
                'coupon_enabled' => $couponEnabled,
                'points_balance' => floatval($invoice->client->points_balance ?? 0),
                'points_value_in_tk' => round(floatval($invoice->client->points_balance ?? 0) / $pointRedeemRate, 2),
                'points_earned_this_invoice' => floatval($pointsEarnedThis),
                'points_redeemed_this_invoice' => floatval($pointsRedeemedThis),
                'point_redeem_rate' => $pointRedeemRate,
            ];
        }

        // 2. Enrich Details with Live Stock and Lifetime Sold Quantities
        $details = $invoice->details->map(function ($d) {
            $itemId = $d->item_id;
            $colorId = $d->color_id;
            $sizeId = $d->size_id;

            // Overall present stock of the product across all variants
            $overallStock = \App\Models\ItemStockSummary::where('item_id', $itemId)->sum('current_stock');

            // Variant specific stock
            $variantStockQuery = \App\Models\ItemStockSummary::where('item_id', $itemId);
            if ($colorId) {
                $variantStockQuery->where('color_id', $colorId);
            }
            if ($sizeId) {
                $variantStockQuery->where('size_id', $sizeId);
            }
            $variantStock = $variantStockQuery->sum('current_stock');

            // Total sold quantity across all invoices in system
            $totalSoldQty = \App\Models\InvoiceDetails::where('item_id', $itemId)->sum('qty');

            // Stock status
            $stockStatus = 'In Stock';
            $stockBadge = 'success';
            if ($variantStock <= 0) {
                $stockStatus = 'Out of Stock';
                $stockBadge = 'danger';
            } elseif ($variantStock <= 5) {
                $stockStatus = 'Low Stock';
                $stockBadge = 'warning';
            }

            return [
                'id' => $d->id,
                'invoice_id' => $d->invoice_id,
                'item_id' => $itemId,
                'title' => $d->item ? $d->item->title : 'Product',
                'barcode' => $d->item ? $d->item->barcode : '',
                'category_title' => $d->item && $d->item->category ? $d->item->category->title : 'N/A',
                'unit_title' => $d->item && $d->item->unit ? $d->item->unit->title : 'Pcs',
                'warranty_type' => $d->item ? $d->item->warranty_type : 'none',
                'warranty_period' => $d->item ? $d->item->warranty_period : null,
                'color_id' => $colorId,
                'color_title' => $d->color ? $d->color->title : null,
                'size_id' => $sizeId,
                'size_title' => $d->size ? $d->size->title : null,
                'serial_no' => $d->serial_no,
                'qty' => floatval($d->qty),
                'amount' => floatval($d->amount),
                'total_amount' => floatval($d->total_amount),
                'status' => $d->status,
                // ⭐️ Requested Item Insights
                'present_stock' => floatval($variantStock),
                'overall_stock' => floatval($overallStock),
                'total_sold_qty' => floatval($totalSoldQty),
                'stock_status' => $stockStatus,
                'stock_badge' => $stockBadge,
            ];
        });

        // 3. Sales Returns for this Invoice
        $returns = \App\Models\StockTransaction::where('reference_type', 'SalesReturn')
            ->where('reference_id', (string)$invoice->id)
            ->with(['item:id,title,barcode', 'color:id,title', 'size:id,title'])
            ->get();

        // 4. Calculations
        $dueAmount = max(0, floatval($invoice->amount) - floatval($invoice->paid_amount));
        $paymentStatus = 'Paid';
        if ($dueAmount > 0 && floatval($invoice->paid_amount) > 0) {
            $paymentStatus = 'Partial';
        } elseif ($dueAmount > 0 && floatval($invoice->paid_amount) <= 0) {
            $paymentStatus = 'Due';
        }

        return response()->json([
            'id' => $invoice->id,
            'invoice_no' => $invoice->invoice_no,
            'invoice_date' => $invoice->invoice_date,
            'created_at' => $invoice->created_at ? $invoice->created_at->format('d M, Y h:i A') : '',
            'original_amount' => floatval($invoice->original_amount),
            'discount' => floatval($invoice->discount),
            'vat' => floatval($invoice->vat ?? 0),
            'amount' => floatval($invoice->amount),
            'paid_amount' => floatval($invoice->paid_amount),
            'due_amount' => $dueAmount,
            'payment_status' => $paymentStatus,
            'is_closed' => $invoice->is_closed,
            'status' => $invoice->status,
            'client' => $invoice->client,
            'client_history' => $clientHistory,
            'loyalty_points' => $loyaltyPoints,
            'details' => $details,
            'payment_details' => $invoice->payment_details,
            'returns' => $returns,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return redirect()->route('invoice.show', $id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        return response()->json([
            'exception' => 'ইনভয়েস এডিট করা সম্ভব নয়। বিক্রয় ও স্টক অখণ্ডতা বজায় রাখতে ইনভয়েস অপরিবর্তনীয়।'
        ], 422);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return response()->json([
            'exception' => 'ইনভয়েস ডিলিট করা সম্ভব নয়। এটি সংরক্ষিত বিক্রয় রেকর্ড।'
        ], 422);
    }

    public function paynow($invoiceid)
    {
        if (!$invoiceid) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice ID not provided'
            ], 400);
        }

        $invoice = Invoice::find($invoiceid);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        try {
            $invoice->paid_amount = $invoice->amount;
            $invoice->payment_date = date('Y-m-d');
            $invoice->card_type = 'cash';
            $invoice->payment_status = 'paid';
            $invoice->save();

            return response()->json([
                'success' => true,
                'message' => 'Cash Payment completed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    public function months(Invoice $invoice)
    {
        return InvoiceMonth::where('invoice_id', $invoice->id)
            ->pluck('invoice_month')
            ->map(fn($m) => substr($m, 0, 7))
            ->toArray();
    }


    public function bill(Request $request, $invoiceid)
    {
        if ($request->format() == 'html') {
            return view('admin.layouts.admin_app');
        }
        $invoice = Invoice::with(
            [
                'client',
                'invoice_details' => function($q) {
                    $q->with('item:id,title,barcode,warranty_type,warranty_period', 'color:id,title', 'size:id,title', 'currency:id,title,short_name');
                }
            ]
        )->where('id', $invoiceid)->first();

        if ($invoice) {
            $client_id = $invoice->client_id;
            $previous_due = $this->previousDue($client_id, $invoice->id);
            $invoice->previous_due = $previous_due;
            $invoice->total_amount  = $invoice->amount + $previous_due;
        }

        return $invoice ? $invoice->toArray() : [];
    }

    public function moneyreceipt(Request $request, $invoiceid)
    {
        if ($request->format() == 'html') {
            return view('admin.layouts.admin_app');
        }

        $invoice = Invoice::with(
            [
                'client:id,name,org_name,mobile,email,address',
                'invoice_details.account:id,account_name',
                'payment_details.payment:payslipno,payment_date'
            ]
        )->where('id', $invoiceid)->first();
        return $invoice;
    }

    public function generateInvoice()
    {
        return true;
    }



    /**
     * Validate form field.
     *
     * @return \Illuminate\Http\Response
     */
    public function validateCheck($request, $id = null)
    {
        return true;
        return $request->validate([
            //ex: 'name' => 'required|email|nullable|date|string|min:0|max:191',
        ], [
            //ex: 'name' => "This name is required" (custom message)
        ]);
    }
}
