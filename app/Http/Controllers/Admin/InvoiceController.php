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
use App\Models\System\SiteSetting;
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
                        $iq->with('category:id,title', 'brand:id,title', 'series:id,title', 'unit:id,title');
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

            $brandTitle = $d->item && $d->item->brand ? $d->item->brand->title : null;
            $modelNo = $d->item ? $d->item->model_no : null;
            $seriesTitle = $d->item && $d->item->series ? $d->item->series->title : null;
            $colorTitle = $d->color ? ($d->color->title ?? $d->color->name) : ($d->item && $d->item->color ? ($d->item->color->title ?? $d->item->color->name) : null);
            $sizeTitle = $d->size ? ($d->size->title ?? $d->size->name) : ($d->item && $d->item->size ? ($d->item->size->title ?? $d->item->size->name) : null);
            $warrantyType = $d->warranty_type && $d->warranty_type !== 'none' ? $d->warranty_type : 'none';
            $warrantyPeriod = $d->warranty_type && $d->warranty_type !== 'none' ? $d->warranty_period : null;

            return [
                'id' => $d->id,
                'invoice_id' => $d->invoice_id,
                'item_id' => $itemId,
                'title' => $d->item ? $d->item->title : 'Product',
                'barcode' => $d->item ? $d->item->barcode : '',
                'brand_title' => $brandTitle,
                'model_no' => $modelNo,
                'series_title' => $seriesTitle,
                'category_title' => $d->item && $d->item->category ? $d->item->category->title : 'N/A',
                'unit_title' => $d->item && $d->item->unit ? $d->item->unit->title : 'Pcs',
                'warranty_type' => $warrantyType,
                'warranty_period' => $warrantyPeriod,
                'color_id' => $colorId,
                'color_title' => $colorTitle,
                'size_id' => $sizeId,
                'size_title' => $sizeTitle,
                'serial_no' => $d->serial_no,
                'description' => $d->description ?: ($d->item ? $d->item->description : null),
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

        // 5. Creator / Salesperson resolution
        $creator = null;
        if (!empty($invoice->created_by)) {
            $creator = \App\Models\Admin::find($invoice->created_by);
        }
        if (!$creator && !empty($invoice->admin_id)) {
            $creator = \App\Models\Admin::find($invoice->admin_id);
        }
        if (!$creator && !empty($invoice->employee_id)) {
            $creator = \App\Models\Admin::find($invoice->employee_id);
        }
        if (!$creator) {
            $activity = \Spatie\Activitylog\Models\Activity::where('subject_type', \App\Models\Invoice::class)
                ->where('subject_id', $invoice->id)
                ->where('event', 'created')
                ->first();
            if ($activity && $activity->causer_id) {
                $creator = \App\Models\Admin::find($activity->causer_id);
            }
        }
        if (!$creator) {
            $creator = auth()->guard('admin')->user() ?? auth()->user() ?? \App\Models\Admin::first();
        }

        return response()->json([
            'id' => $invoice->id,
            'invoice_no' => $invoice->invoice_no,
            'invoice_date' => $invoice->invoice_date,
            'raw_invoice_date' => $invoice->getRawOriginal('invoice_date'),
            'created_at' => $invoice->created_at ? $invoice->created_at->format('d M, Y h:i A') : '',
            'original_amount' => floatval($invoice->original_amount),
            'discount' => floatval($invoice->discount),
            'vat' => floatval($invoice->vat ?? 0),
            'vat_percent' => floatval($invoice->vat_percent ?? 0),
            'amount' => floatval($invoice->amount),
            'paid_amount' => floatval($invoice->paid_amount),
            'due_amount' => $dueAmount,
            'payment_status' => $paymentStatus,
            'is_closed' => $invoice->is_closed,
            'status' => $invoice->status,
            'client_id' => $invoice->client_id,
            'client' => $invoice->client,
            'creator' => $creator ? [
                'id' => $creator->id,
                'name' => $creator->name ?? ($creator->full_name ?? 'Cashier'),
                'email' => $creator->email ?? '',
                'mobile' => $creator->mobile ?? '',
            ] : null,
            'client_history' => $clientHistory,
            'loyalty_points' => $loyaltyPoints,
            'details' => $details,
            'invoice_details' => $details,
            'payment_details' => $invoice->payment_details,
            'returns' => $returns,
            'terms_conditions' => $invoice->terms_conditions ?? [],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        if ($request->format() == 'html' || !$request->wantsJson()) {
            return view('admin.layouts.admin_app');
        }
        return $this->show($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $invoice = Invoice::with(['details', 'payment_details'])->findOrFail($id);

        $dueAmount = max(0, floatval($invoice->amount) - floatval($invoice->paid_amount));
        $isPaid = ($dueAmount <= 0.01) || (floatval($invoice->paid_amount) >= floatval($invoice->amount));

        if ($isPaid) {
            return response()->json([
                'exception' => 'পরিশোধিত (Paid) ইনভয়েস এডিট করা সম্ভব নয়।'
            ], 422);
        }

        // Check for sales returns
        $hasReturns = \App\Models\StockTransaction::where('reference_type', 'SalesReturn')
            ->where('reference_id', (string)$invoice->id)
            ->exists();
        if ($hasReturns) {
            return response()->json([
                'exception' => 'এই ইনভয়েসের বিপরীতে রিটার্ন রেকর্ড থাকায় এটি এডিট করা সম্ভব নয়।'
            ], 422);
        }

        $items = $request->input('items', []);
        if (empty($items) || !is_array($items)) {
            return response()->json([
                'exception' => 'ইনভয়েসে কমপক্ষে একটি পণ্য থাকা আবশ্যক।'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // 1. Remove previous Issue stock transactions for this invoice
            \App\Models\StockTransaction::where('reference_type', 'Issue')
                ->where('reference_id', (string)$invoice->id)
                ->delete();

            // 2. Remove previous invoice details
            \App\Models\InvoiceDetails::where('invoice_id', $invoice->id)->delete();

            // 3. Re-calculate totals and create new details and stock transactions
            $originalAmount = 0;
            foreach ($items as $item) {
                $itemId = $item['item_id'] ?? null;
                $qty = floatval($item['qty'] ?? 1);
                $rate = floatval($item['amount'] ?? ($item['rate'] ?? 0));
                $total = round($qty * $rate, 2);
                $originalAmount += $total;

                $colorId = !empty($item['color_id']) ? $item['color_id'] : null;
                $sizeId = !empty($item['size_id']) ? $item['size_id'] : null;
                $serialNo = !empty($item['serial_no']) ? $item['serial_no'] : null;
                $warrantyType = !empty($item['warranty_type']) ? $item['warranty_type'] : 'none';
                $warrantyPeriod = !empty($item['warranty_period']) ? trim($item['warranty_period']) : null;
                $warrantyDuration = !empty($item['warranty_duration']) ? intval($item['warranty_duration']) : null;
                $warrantyExpiresAt = null;
                if ($warrantyType !== 'none' && !empty($warrantyPeriod)) {
                    $warrantyExpiresAt = app(\App\Http\Controllers\Admin\PosController::class)->calculateExpiryDate(date('Y-m-d'), $warrantyPeriod);
                }

                $detailDescription = !empty($item['description'])
                    ? $item['description']
                    : (($item['title'] ?? 'POS Item Sale') . ($serialNo ? " (Serial: {$serialNo})" : ''));

                $detailData = [
                    'invoice_id' => (string)$invoice->id,
                    'reference' => 'POS Sale',
                    'workorder_id' => 0,
                    'item_id' => $itemId,
                    'color_id' => $colorId,
                    'size_id' => $sizeId,
                    'serial_no' => $serialNo,
                    'account_id' => 0,
                    'description' => $detailDescription,
                    'qty' => $qty,
                    'amount' => $rate,
                    'total_amount' => $total,
                    'status' => 'active',
                ];

                if (\Illuminate\Support\Facades\Schema::hasColumn('invoice_details', 'warranty_type')) {
                    $detailData['warranty_type'] = $warrantyType;
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('invoice_details', 'warranty_duration')) {
                    $detailData['warranty_duration'] = $warrantyDuration;
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('invoice_details', 'warranty_period')) {
                    $detailData['warranty_period'] = $warrantyPeriod;
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('invoice_details', 'warranty_expires_at')) {
                    $detailData['warranty_expires_at'] = $warrantyExpiresAt ? ($warrantyExpiresAt instanceof \Carbon\Carbon ? $warrantyExpiresAt->format('Y-m-d') : (string)$warrantyExpiresAt) : null;
                }

                \App\Models\InvoiceDetails::create($detailData);

                // Create StockTransaction (Issue)
                if ($itemId) {
                    \App\Models\StockTransaction::create([
                        'item_id' => $itemId,
                        'color_id' => $colorId,
                        'size_id' => $sizeId,
                        'transaction_date' => vue_to_server_date($request->input('invoice_date', date('Y-m-d'))),
                        'transaction_type' => 'Issue',
                        'reference_type' => 'Issue',
                        'reference_id' => (string)$invoice->id,
                        'qty_in' => 0,
                        'qty_out' => $qty,
                        'status' => 'active',
                    ]);
                }
            }

            // 4. Update Invoice fields
            $discount = floatval($request->input('discount', 0));
            $vatPercent = floatval($request->input('vat_percent', 0));
            $vat = floatval($request->input('vat', 0));
            if ($vatPercent > 0 && $vat <= 0) {
                $vat = round((max(0, $originalAmount - $discount) * $vatPercent) / 100, 2);
            }
            $netAmount = max(0, round($originalAmount - $discount + $vat, 2));

            $invoiceDate = $request->input('invoice_date') ? vue_to_server_date($request->input('invoice_date')) : $invoice->getRawOriginal('invoice_date');

            $invoice->client_id = $request->filled('client_id') ? $request->input('client_id') : null;
            $invoice->invoice_date = $invoiceDate;
            $invoice->original_amount = $originalAmount;
            $invoice->discount = $discount;
            if (\Illuminate\Support\Facades\Schema::hasColumn('invoices', 'vat_percent')) {
                $invoice->vat_percent = $vatPercent;
            }
            $invoice->vat = $vat;
            $invoice->amount = $netAmount;

            if ($request->has('paid_amount')) {
                $newPaid = min($netAmount, floatval($request->input('paid_amount')));
                $invoice->paid_amount = $newPaid;
            }
            $invoice->is_closed = (floatval($invoice->paid_amount) >= $netAmount) ? 1 : 0;

            if ($request->has('terms_conditions')) {
                $invoice->terms_conditions = $request->input('terms_conditions');
            }

            $invoice->save();

            // 5. Update payment details if paid_amount changed
            if ($request->has('paid_amount')) {
                $pDetails = \App\Models\PaymentDetail::where('reference_type', 'Invoice')->where('reference_id', $invoice->id)->get();
                if ($pDetails->isNotEmpty()) {
                    $firstPd = $pDetails->first();
                    $firstPd->amount = $invoice->paid_amount;
                    $firstPd->is_closed = $invoice->is_closed;
                    $firstPd->save();

                    $payment = \App\Models\Payment::find($firstPd->payment_id);
                    if ($payment) {
                        $payment->amount = \App\Models\PaymentDetail::where('payment_id', $payment->id)->sum('amount');
                        $payment->client_id = $invoice->client_id;
                        $payment->save();
                    }
                }
            }

            activity('Invoice')
                ->performedOn($invoice)
                ->causedBy(auth('admin')->user() ?? auth()->user())
                ->log("Invoice #{$invoice->invoice_no} was updated");

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'ইনভয়েস সফলভাবে আপডেট করা হয়েছে।',
                'invoice' => $invoice
            ]);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json(['exception' => $ex->getMessage()], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);

        $dueAmount = max(0, floatval($invoice->amount) - floatval($invoice->paid_amount));
        $isPaid = ($dueAmount <= 0.01) || (floatval($invoice->paid_amount) >= floatval($invoice->amount));

        if ($isPaid) {
            return response()->json([
                'exception' => 'পরিশোধিত (Paid) ইনভয়েস ডিলিট করা সম্ভব নয়।'
            ], 422);
        }

        // Check for sales returns
        $hasReturns = \App\Models\StockTransaction::where('reference_type', 'SalesReturn')
            ->where('reference_id', (string)$invoice->id)
            ->exists();
        if ($hasReturns) {
            return response()->json([
                'exception' => 'এই ইনভয়েসের বিপরীতে রিটার্ন রেকর্ড থাকায় এটি ডিলিট করা সম্ভব নয়।'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // 1. Restore stock by removing Issue stock transactions
            \App\Models\StockTransaction::where('reference_type', 'Issue')
                ->where('reference_id', (string)$invoice->id)
                ->delete();

            // 2. If this invoice was converted from a challan, reset challan state
            if (!empty($invoice->challan_id)) {
                $challan = \App\Models\Challan::find($invoice->challan_id);
                if ($challan) {
                    $challan->is_invoiced = 0;
                    $challan->invoice_id = null;
                    $challan->save();
                }
            }

            // 3. Remove payment details and payments if any (e.g. partial payment)
            $pDetails = \App\Models\PaymentDetail::where('reference_type', 'Invoice')
                ->where('reference_id', $invoice->id)
                ->get();
            foreach ($pDetails as $pd) {
                $payment = \App\Models\Payment::find($pd->payment_id);
                $pd->delete();
                if ($payment) {
                    $otherDetailsCount = \App\Models\PaymentDetail::where('payment_id', $payment->id)->count();
                    if ($otherDetailsCount === 0) {
                        $payment->delete();
                    } else {
                        $payment->amount = \App\Models\PaymentDetail::where('payment_id', $payment->id)->sum('amount');
                        $payment->save();
                    }
                }
            }

            // 4. Revert Loyalty Points if any
            $pointTrxs = \App\Models\ClientPointTransaction::where('invoice_id', $invoice->id)->get();
            foreach ($pointTrxs as $pt) {
                $client = \App\Models\Client::find($pt->client_id);
                if ($client) {
                    if ($pt->type === 'Redeem') {
                        $client->points_balance = floatval($client->points_balance ?? 0) + floatval($pt->points_out);
                    } elseif ($pt->type === 'Earn') {
                        $client->points_balance = max(0, floatval($client->points_balance ?? 0) - floatval($pt->points_in));
                    }
                    $client->save();
                }
                $pt->delete();
            }

            // 5. Remove vouchers
            $invoice->removeVoucherBySourceInfo([
                'source'    => 'Invoice',
                'source_id' => $invoice->id,
            ]);

            // 6. Delete details & months
            \App\Models\InvoiceDetails::where('invoice_id', $invoice->id)->delete();
            if (\Illuminate\Support\Facades\Schema::hasTable('invoice_months')) {
                \App\Models\InvoiceMonth::where('invoice_id', $invoice->id)->delete();
            }

            // 7. Delete the invoice
            $invoice->delete();

            activity('Invoice')
                ->performedOn($invoice)
                ->causedBy(auth('admin')->user() ?? auth()->user())
                ->log("Invoice #{$invoice->invoice_no} was deleted");

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'ইনভয়েস সফলভাবে ডিলিট করা হয়েছে এবং আইটেম স্টক পূর্বাবস্থায় ফিরিয়ে দেওয়া হয়েছে।'
            ]);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json(['exception' => $ex->getMessage()], 422);
        }
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
                'creator',
                'invoice_details' => function($q) {
                    $q->with([
                        'item' => function($iq) {
                            $iq->with('category:id,title', 'brand:id,title', 'series:id,title', 'unit:id,title');
                        },
                        'color:id,title',
                        'size:id,title',
                        'currency:id,title,short_name'
                    ]);
                }
            ]
        )->where('id', $invoiceid)->first();

        if ($invoice) {
            $client_id = $invoice->client_id;
            $previous_due = $this->previousDue($client_id, $invoice->id);
            $invoice->previous_due = $previous_due;
            $invoice->total_amount  = $invoice->amount + $previous_due;

            $site = SiteSetting::first();
            if ($site) {
                $siteData = $site->toArray();
                if (is_string($site->organization_memberships)) {
                    $siteData['memberships'] = json_decode($site->organization_memberships, true);
                    $siteData['organization_memberships'] = $siteData['memberships'];
                } elseif (is_array($site->organization_memberships)) {
                    $siteData['memberships'] = $site->organization_memberships;
                }
                $invoice->site_setting = $siteData;
            }
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
