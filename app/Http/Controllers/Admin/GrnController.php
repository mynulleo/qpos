<?php

/**
 * @Quill Information Technology
 */

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Grn;
use App\Models\GrnDetail;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Payment;
use App\Models\PaymentDetail;
use App\Models\Voucher;
use App\Models\VoucherDetail;
use App\Models\Account;
use App\Models\StockTransaction;
use App\Models\ItemPrice;
use App\Traits\VoucherTrait;
use Illuminate\Http\Request;
use App\Http\Resources\Resource;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Base\BaseController;

class GrnController extends BaseController
{
    use VoucherTrait;

    /**
     * Apply common search and query filters to Grn query
     */
    private function applyFilters($query, Request $request)
    {
        if ($request->filled('value') && $request->value !== 'null' && $request->value !== 'undefined') {
            $val = trim($request->value);
            if ($request->filled('field_name') && $request->field_name !== 'null' && $request->field_name !== 'undefined' && $request->field_name !== 'default' && $request->field_name !== '') {
                if ($request->field_name === 'invoiceno') {
                    $query->whereHas('purchase', function ($q) use ($val) {
                        $q->where('invoiceno', 'like', "%{$val}%");
                    });
                } elseif ($request->field_name === 'supplier') {
                    $query->whereHas('supplier', function ($q) use ($val) {
                        $q->where('org_name', 'like', "%{$val}%")
                          ->orWhere('name', 'like', "%{$val}%")
                          ->orWhere('mobile', 'like', "%{$val}%");
                    });
                } elseif ($request->field_name === 'warehouse') {
                    $query->whereHas('warehouse', function ($q) use ($val) {
                        $q->where('name', 'like', "%{$val}%")
                          ->orWhere('code', 'like', "%{$val}%");
                    });
                } elseif ($request->field_name === 'note') {
                    $query->where('note', 'like', "%{$val}%");
                } else {
                    $query->where($request->field_name, 'like', "%{$val}%");
                }
            } else {
                $query->where(function ($q) use ($val) {
                    $q->where('grn_no', 'like', "%{$val}%")
                      ->orWhere('challan_no', 'like', "%{$val}%")
                      ->orWhere('note', 'like', "%{$val}%")
                      ->orWhereHas('purchase', function ($pq) use ($val) {
                          $pq->where('invoiceno', 'like', "%{$val}%");
                      })
                      ->orWhereHas('supplier', function ($sq) use ($val) {
                          $sq->where('org_name', 'like', "%{$val}%")
                             ->orWhere('name', 'like', "%{$val}%")
                             ->orWhere('mobile', 'like', "%{$val}%");
                      })
                      ->orWhereHas('warehouse', function ($wq) use ($val) {
                          $wq->where('name', 'like', "%{$val}%")
                             ->orWhere('code', 'like', "%{$val}%");
                      });
                });
            }
        }

        if ($request->filled('grn_type') && $request->grn_type !== 'null' && $request->grn_type !== 'undefined' && $request->grn_type !== '') {
            $query->where('grn_type', $request->grn_type);
        }

        if ($request->filled('supplier_id') && $request->supplier_id !== 'null' && $request->supplier_id !== 'undefined' && $request->supplier_id !== '') {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('warehouse_id') && $request->warehouse_id !== 'null' && $request->warehouse_id !== 'undefined' && $request->warehouse_id !== '') {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('purchase_id') && $request->purchase_id !== 'null' && $request->purchase_id !== 'undefined' && $request->purchase_id !== '') {
            $query->where('purchase_id', $request->purchase_id);
        }

        if ($request->filled('from_date') && $request->from_date !== 'null' && $request->from_date !== 'undefined' && $request->from_date !== '') {
            $from = date('Y-m-d', strtotime($request->from_date));
            if ($from && $from !== '1970-01-01') {
                $query->whereDate('grn_date', '>=', $from);
            }
        }

        if ($request->filled('to_date') && $request->to_date !== 'null' && $request->to_date !== 'undefined' && $request->to_date !== '') {
            $to = date('Y-m-d', strtotime($request->to_date));
            if ($to && $to !== '1970-01-01') {
                $query->whereDate('grn_date', '<=', $to);
            }
        }

        if ($request->filled('status') && $request->status !== 'null' && $request->status !== 'undefined' && $request->status !== '') {
            $query->where('status', $request->status);
        }

        return $query;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Grn::with([
            'purchase:id,invoiceno,purchase_date',
            'supplier:id,org_name,mobile',
            'warehouse:id,name,code',
            'fund_account:id,account_name,account_code'
        ])->latest('id');

        $this->applyFilters($query, $request);

        if ($request->allData) {
            return $query->get();
        } else {
            // Calculate KPI summary directly on the filtered dataset
            $summaryQuery = clone $query;
            $total_grns = (clone $summaryQuery)->count();
            $total_received_qty = (clone $summaryQuery)->sum('total_qty');
            $total_amount = (clone $summaryQuery)->sum('total_amount');
            $total_paid = (clone $summaryQuery)->sum('paid_amount');

            $po_count = (clone $summaryQuery)->where('grn_type', 'po')->count();
            $supplier_count = (clone $summaryQuery)->where('grn_type', 'supplier')->count();
            $direct_count = (clone $summaryQuery)->where('grn_type', 'direct')->count();

            $summary = [
                'total_grns' => $total_grns,
                'total_received_qty' => (float)$total_received_qty,
                'total_amount' => (float)$total_amount,
                'total_paid' => (float)$total_paid,
                'po_count' => $po_count,
                'supplier_count' => $supplier_count,
                'direct_count' => $direct_count,
            ];

            $datas = $query->paginate($request->pagination ?? 10);
            $resource = new Resource($datas);
            $resource->additional(['summary' => $summary]);

            return $resource;
        }
    }

    /**
     * Get aggregate KPI statistics for GRN list
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics(Request $request)
    {
        $query = Grn::query();
        $this->applyFilters($query, $request);

        $total_grns = (clone $query)->count();
        $total_received_qty = (clone $query)->sum('total_qty');
        $total_amount = (clone $query)->sum('total_amount');
        $total_paid = (clone $query)->sum('paid_amount');

        $po_count = (clone $query)->where('grn_type', 'po')->count();
        $supplier_count = (clone $query)->where('grn_type', 'supplier')->count();
        $direct_count = (clone $query)->where('grn_type', 'direct')->count();

        return response()->json([
            'total_grns' => $total_grns,
            'total_received_qty' => (float)$total_received_qty,
            'total_amount' => (float)$total_amount,
            'total_paid' => (float)$total_paid,
            'po_count' => $po_count,
            'supplier_count' => $supplier_count,
            'direct_count' => $direct_count,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('layouts.backend_app');
    }

    /**
     * Get pending / partially received purchases eligible for GRN.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function pendingpurchases(Request $request)
    {
        $query = Purchase::with('supplier:id,org_name,mobile')
            ->where(function ($q) {
                $q->where('receive_status', '!=', 'Received')
                  ->orWhereNull('receive_status');
            })
            ->where('status', 'active')
            ->latest('id');

        if ($request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $purchases = $query->get(['id', 'invoiceno', 'purchase_date', 'supplier_id', 'total_amount', 'receive_status'])
            ->map(function ($p) {
                $supplierName = $p->supplier ? $p->supplier->org_name : 'No Supplier';
                $dateFormatted = $p->purchase_date ? date('d-M-Y', strtotime($p->purchase_date)) : '';
                $amountFormatted = number_format((float)$p->total_amount, 2);
                $status = $p->receive_status ?: 'Pending';

                $p->display_label = "{$p->invoiceno} | {$dateFormatted} | {$supplierName} | ৳ {$amountFormatted} ({$status})";
                return $p;
            });

        return response()->json($purchases);
    }

    /**
     * Get purchase line items with received & remaining quantities.
     *
     * @param  int  $purchase_id
     * @return \Illuminate\Http\Response
     */
    public function purchaseitems($purchase_id)
    {
        $purchase = Purchase::with([
            'supplier:id,org_name,mobile,address',
            'purchase_details' => function ($q) {
                $q->with([
                    'item:id,title,barcode',
                    'category:id,title',
                    'unit:id,title',
                    'color:id,title',
                    'size:id,title',
                ]);
            }
        ])->find($purchase_id);

        if (!$purchase) {
            return response()->json(['message' => 'Purchase not found.'], 404);
        }

        $items = [];
        foreach ($purchase->purchase_details as $detail) {
            $prevReceived = (float) GrnDetail::where('purchase_detail_id', $detail->id)->sum('received_qty');
            $orderedQty = (float) $detail->qty;
            $remainingQty = max(0, $orderedQty - $prevReceived);

            $items[] = [
                'purchase_detail_id'      => $detail->id,
                'item_id'                 => $detail->item_id,
                'item'                    => $detail->item,
                'category_id'             => $detail->category_id,
                'category'                => $detail->category,
                'unit_id'                 => $detail->unit_id,
                'unit'                    => $detail->unit,
                'color_id'                => $detail->color_id,
                'color'                   => $detail->color,
                'size_id'                 => $detail->size_id,
                'size'                    => $detail->size,
                'ordered_qty'             => $orderedQty,
                'previously_received_qty' => $prevReceived,
                'remaining_qty'           => $remainingQty,
                'received_qty'            => $remainingQty, // default to receive rest
                'unit_price'              => (float) $detail->price,
                'selling_price'           => (float) ($detail->selling_price ?? 0),
                'total_amount'            => (float) ($remainingQty * $detail->price),
                'serial_no'               => '',
                'note'                    => '',
            ];
        }

        return response()->json([
            'purchase' => [
                'id'            => $purchase->id,
                'invoiceno'     => $purchase->invoiceno,
                'purchase_date' => $purchase->purchase_date,
                'supplier_id'   => $purchase->supplier_id,
                'supplier'      => $purchase->supplier,
                'total_amount'  => $purchase->total_amount,
            ],
            'items' => $items,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($this->validateCheck($request)) {
            DB::beginTransaction();

            try {
                $data = $request->all();
                $details = $data['grn_details'] ?? [];
                $grnType = $data['grn_type'] ?? 'po';

                if (empty($details)) {
                    return response()->json(['message' => 'At least one item must be received.'], 422);
                }

                // Auto-generate GRN number
                $data['grn_no'] = Grn::generateGrnNo();
                $data['grn_date'] = date('Y-m-d', strtotime($data['grn_date']));
                $data['challan_date'] = !empty($data['challan_date']) ? date('Y-m-d', strtotime($data['challan_date'])) : null;
                $data['grn_type'] = $grnType;

                unset($data['grn_details'], $data['purchase'], $data['supplier'], $data['warehouse'], $data['fund_account']);

                $totalQty = 0;
                $subTotal = 0;

                foreach ($details as $item) {
                    $qty = floatval($item['received_qty'] ?? 0);
                    if ($qty > 0) {
                        $price = floatval($item['unit_price'] ?? 0);
                        $totalQty += $qty;
                        $subTotal += ($qty * $price);
                    }
                }

                if ($totalQty <= 0) {
                    return response()->json(['message' => 'Received quantity must be greater than 0.'], 422);
                }

                $discount = floatval($data['discount'] ?? 0);
                $totalAmount = max(0, $subTotal - $discount);

                $data['total_qty'] = $totalQty;
                $data['sub_total'] = $subTotal;
                $data['discount'] = $discount;
                $data['total_amount'] = $totalAmount;

                if ($grnType === 'direct') {
                    $data['purchase_id'] = null;
                    $data['supplier_id'] = null;
                    $data['paid_amount'] = $totalAmount;
                    $data['is_closed'] = 1;
                } elseif ($grnType === 'supplier') {
                    $data['purchase_id'] = null;
                    $data['paid_amount'] = floatval($data['paid_amount'] ?? 0);
                    $data['is_closed'] = ($data['paid_amount'] >= $totalAmount && $totalAmount > 0) ? 1 : 0;
                } else { // po
                    $data['paid_amount'] = floatval($data['paid_amount'] ?? 0);
                    $data['is_closed'] = ($data['paid_amount'] >= $totalAmount && $totalAmount > 0) ? 1 : 0;
                }

                $grn = Grn::create($data);

                // Insert details and stock transactions
                foreach ($details as $item) {
                    $receivedQty = floatval($item['received_qty'] ?? 0);
                    if ($receivedQty <= 0) {
                        continue;
                    }

                    $unitPrice = floatval($item['unit_price'] ?? 0);
                    $sellingPrice = floatval($item['selling_price'] ?? 0);
                    $lineTotal = $receivedQty * $unitPrice;
                    $colorId = !empty($item['color_id']) ? $item['color_id'] : null;
                    $sizeId = !empty($item['size_id']) ? $item['size_id'] : null;
                    $serialNo = !empty($item['serial_no']) ? $item['serial_no'] : null;

                    // 1. Create GrnDetail
                    GrnDetail::create([
                        'grn_id'                  => $grn->id,
                        'purchase_detail_id'      => !empty($item['purchase_detail_id']) ? $item['purchase_detail_id'] : null,
                        'category_id'             => $item['category_id'] ?? null,
                        'item_id'                 => $item['item_id'],
                        'color_id'                => $colorId,
                        'size_id'                 => $sizeId,
                        'unit_id'                 => $item['unit_id'] ?? null,
                        'ordered_qty'             => floatval($item['ordered_qty'] ?? $receivedQty),
                        'previously_received_qty' => floatval($item['previously_received_qty'] ?? 0),
                        'received_qty'            => $receivedQty,
                        'unit_price'              => $unitPrice,
                        'selling_price'           => $sellingPrice,
                        'total_amount'            => $lineTotal,
                        'serial_no'               => $serialNo,
                        'note'                    => $item['note'] ?? null,
                        'status'                  => 'active',
                    ]);

                    // 2. Insert Stock Transaction (Stock In to selected Warehouse)
                    StockTransaction::create([
                        'item_id'          => $item['item_id'],
                        'warehouse_id'     => $grn->warehouse_id,
                        'color_id'         => $colorId,
                        'size_id'          => $sizeId,
                        'transaction_date' => $grn->getRawOriginal('grn_date') ?? $data['grn_date'],
                        'transaction_type' => 'GRN',
                        'reference_type'   => 'GRN',
                        'reference_id'     => $grn->id,
                        'qty_in'           => $receivedQty,
                        'qty_out'          => 0,
                        'status'           => 'active',
                    ]);

                    // 3. Update/Create Item Price if variant/price info is given
                    if ($colorId || $sizeId || $unitPrice > 0 || $sellingPrice > 0) {
                        ItemPrice::updateOrCreate(
                            [
                                'item_id'  => $item['item_id'],
                                'color_id' => $colorId,
                                'size_id'  => $sizeId,
                            ],
                            [
                                'purchase_price' => $unitPrice,
                                'selling_price'  => $sellingPrice,
                                'status'         => 'active',
                            ]
                        );
                    }
                }

                // 4. Mode-specific Accounting & Status updates
                if ($grnType === 'po') {
                    // Update Purchase receive_status
                    $this->updatePurchaseReceiveStatus($grn->purchase_id);

                    // Create Payable Voucher for received amount
                    $vdata = [
                        'module'    => 'GRN',
                        'date'      => $grn->getRawOriginal('grn_date') ?? $data['grn_date'],
                        'amount'    => $grn->total_amount,
                        'source_id' => $grn->id,
                        'ref_id'    => $grn->supplier_id
                    ];
                    $this->createPayableVoucher($vdata);
                } elseif ($grnType === 'supplier') {
                    // Create Payable Voucher for Supplier
                    if ($grn->supplier_id) {
                        $vdata = [
                            'module'    => 'GRN',
                            'date'      => $grn->getRawOriginal('grn_date') ?? $data['grn_date'],
                            'amount'    => $grn->total_amount,
                            'source_id' => $grn->id,
                            'ref_id'    => $grn->supplier_id
                        ];
                        $this->createPayableVoucher($vdata);
                    }
                } elseif ($grnType === 'direct') {
                    // DIRECT PURCHASE: Record Payment & Voucher
                    $fundAccountId = $grn->fund_account_id;
                    $purchaseExpenseAccountId = $this->getExpenseAccount('Purchase');

                    // Determine payment method label
                    $fundAccount = Account::find($fundAccountId);
                    $paymentMethod = 'Cash';
                    if ($fundAccount) {
                        if (stripos($fundAccount->account_name, 'bank') !== false) {
                            $paymentMethod = 'Bank';
                        } elseif (stripos($fundAccount->account_name, 'bkash') !== false || stripos($fundAccount->account_name, 'nagad') !== false) {
                            $paymentMethod = 'MobileBanking';
                        }
                    }

                    // 1. Create Payment
                    $payment = Payment::create([
                        'payslipno'       => Payment::getPaySlipNo(),
                        'payment_type'    => 'Pay',
                        'supplier_id'     => null,
                        'client_id'       => null,
                        'employee_id'     => null,
                        'payment_date'    => $grn->getRawOriginal('grn_date') ?? $data['grn_date'],
                        'discount'        => $discount,
                        'amount'          => $totalAmount,
                        'fund_account_id' => $fundAccountId,
                        'payment_method'  => $paymentMethod,
                        'status'          => 'active',
                    ]);

                    // 2. Create PaymentDetail
                    PaymentDetail::create([
                        'payment_id'     => $payment->id,
                        'reference_type' => 'GRN',
                        'reference_id'   => $grn->id,
                        'account_id'     => $purchaseExpenseAccountId,
                        'amount'         => $totalAmount,
                        'is_closed'      => 1,
                        'status'         => 'active',
                    ]);

                    // 3. Create Balanced Double-Entry Voucher
                    $voucher = Voucher::create([
                        'voucherno'         => Voucher::generateVoucherNo(),
                        'voucher_date'      => $grn->getRawOriginal('grn_date') ?? $data['grn_date'],
                        'voucher_type'      => 'Pay',
                        'narration'         => 'Direct Purchase GRN #' . ($grn->grn_no ?? $grn->id) . ' cash/bank payment',
                        'financial_year_id' => $this->getFinancialYearId(),
                        'payment_id'        => $payment->id,
                        'source'            => 'GRN',
                        'source_id'         => $grn->id,
                        'status'            => 'active',
                    ]);

                    // Debit Purchase Expense
                    VoucherDetail::create([
                        'voucher_id'     => $voucher->id,
                        'account_id'     => $purchaseExpenseAccountId,
                        'dr_amount'      => $totalAmount,
                        'cr_amount'      => 0,
                        'reference_type' => 'GRN',
                        'reference_id'   => $grn->id,
                    ]);

                    // Credit Fund Account (Cash in Hand / Bank)
                    VoucherDetail::create([
                        'voucher_id'     => $voucher->id,
                        'account_id'     => $fundAccountId ?: $this->getSystemAccount('Cash'),
                        'dr_amount'      => 0,
                        'cr_amount'      => $totalAmount,
                        'reference_type' => 'GRN',
                        'reference_id'   => $grn->id,
                    ]);
                }

                DB::commit();

                return $this->responseReturn("create", $grn);
            } catch (Exception $ex) {
                DB::rollBack();
                return response()->json(['exception' => $ex->errorInfo ?? $ex->getMessage()], 422);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        if ($request->format() == 'html') {
            return view('layouts.backend_app');
        }

        $grn = Grn::with([
            'purchase:id,invoiceno,purchase_date,total_amount',
            'supplier:id,org_name,name,mobile,address,email',
            'warehouse:id,name,code,address,phone,contact_person',
            'fund_account:id,account_name,account_code',
            'payments',
            'grn_details.item:id,title,barcode',
            'grn_details.category:id,title',
            'grn_details.unit:id,title',
            'grn_details.color:id,title',
            'grn_details.size:id,title',
        ])->find($id);

        return $grn;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('layouts.backend_app');
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
        $grn = Grn::find($id);
        if (!$grn) {
            return response()->json(['message' => 'GRN not found.'], 404);
        }

        // Check if any payment was recorded against this GRN (for PO / Supplier mode)
        if ($grn->grn_type !== 'direct') {
            $hasPayment = PaymentDetail::where('reference_type', 'GRN')
                ->where('reference_id', $grn->id)
                ->whereNull('deleted_at')
                ->exists();

            if ($hasPayment) {
                return response()->json([
                    'message' => 'This GRN has recorded supplier payments. Edit is restricted to maintain accounting integrity.'
                ], 422);
            }
        }

        if ($this->validateCheck($request, $grn->id)) {
            DB::beginTransaction();

            try {
                $data = $request->all();
                $details = $data['grn_details'] ?? [];
                $grnType = $data['grn_type'] ?? $grn->grn_type ?? 'po';

                $data['grn_date'] = date('Y-m-d', strtotime($data['grn_date']));
                $data['challan_date'] = !empty($data['challan_date']) ? date('Y-m-d', strtotime($data['challan_date'])) : null;
                $data['grn_type'] = $grnType;

                unset($data['grn_details'], $data['purchase'], $data['supplier'], $data['warehouse'], $data['fund_account'], $data['payments']);

                $totalQty = 0;
                $subTotal = 0;

                foreach ($details as $item) {
                    $qty = floatval($item['received_qty'] ?? 0);
                    if ($qty > 0) {
                        $price = floatval($item['unit_price'] ?? 0);
                        $totalQty += $qty;
                        $subTotal += ($qty * $price);
                    }
                }

                $discount = floatval($data['discount'] ?? 0);
                $totalAmount = max(0, $subTotal - $discount);

                $data['total_qty'] = $totalQty;
                $data['sub_total'] = $subTotal;
                $data['discount'] = $discount;
                $data['total_amount'] = $totalAmount;

                if ($grnType === 'direct') {
                    $data['purchase_id'] = null;
                    $data['supplier_id'] = null;
                    $data['paid_amount'] = $totalAmount;
                    $data['is_closed'] = 1;
                }

                $grn->fill($data)->save();

                // 1. Delete previous details and stock transactions
                GrnDetail::where('grn_id', $grn->id)->delete();
                StockTransaction::where('reference_type', 'GRN')
                    ->where('reference_id', $grn->id)
                    ->delete();

                // 2. Insert new details and stock transactions
                foreach ($details as $item) {
                    $receivedQty = floatval($item['received_qty'] ?? 0);
                    if ($receivedQty <= 0) {
                        continue;
                    }

                    $unitPrice = floatval($item['unit_price'] ?? 0);
                    $sellingPrice = floatval($item['selling_price'] ?? 0);
                    $lineTotal = $receivedQty * $unitPrice;
                    $colorId = !empty($item['color_id']) ? $item['color_id'] : null;
                    $sizeId = !empty($item['size_id']) ? $item['size_id'] : null;
                    $serialNo = !empty($item['serial_no']) ? $item['serial_no'] : null;

                    GrnDetail::create([
                        'grn_id'                  => $grn->id,
                        'purchase_detail_id'      => !empty($item['purchase_detail_id']) ? $item['purchase_detail_id'] : null,
                        'category_id'             => $item['category_id'] ?? null,
                        'item_id'                 => $item['item_id'],
                        'color_id'                => $colorId,
                        'size_id'                 => $sizeId,
                        'unit_id'                 => $item['unit_id'] ?? null,
                        'ordered_qty'             => floatval($item['ordered_qty'] ?? $receivedQty),
                        'previously_received_qty' => floatval($item['previously_received_qty'] ?? 0),
                        'received_qty'            => $receivedQty,
                        'unit_price'              => $unitPrice,
                        'selling_price'           => $sellingPrice,
                        'total_amount'            => $lineTotal,
                        'serial_no'               => $serialNo,
                        'note'                    => $item['note'] ?? null,
                        'status'                  => 'active',
                    ]);

                    StockTransaction::create([
                        'item_id'          => $item['item_id'],
                        'warehouse_id'     => $grn->warehouse_id,
                        'color_id'         => $colorId,
                        'size_id'          => $sizeId,
                        'transaction_date' => $grn->getRawOriginal('grn_date') ?? $data['grn_date'],
                        'transaction_type' => 'GRN',
                        'reference_type'   => 'GRN',
                        'reference_id'     => $grn->id,
                        'qty_in'           => $receivedQty,
                        'qty_out'          => 0,
                        'status'           => 'active',
                    ]);
                }

                // 3. Remove previous vouchers
                $this->removeVoucherBySourceInfo(['source' => 'GRN', 'source_id' => $grn->id]);

                if ($grnType === 'po' || $grnType === 'supplier') {
                    if ($grn->supplier_id) {
                        $vdata = [
                            'module'    => 'GRN',
                            'date'      => $grn->getRawOriginal('grn_date') ?? $data['grn_date'],
                            'amount'    => $grn->total_amount,
                            'source_id' => $grn->id,
                            'ref_id'    => $grn->supplier_id
                        ];
                        $this->createPayableVoucher($vdata);
                    }
                    if ($grnType === 'po') {
                        $this->updatePurchaseReceiveStatus($grn->purchase_id);
                    }
                } elseif ($grnType === 'direct') {
                    // Update direct payment & voucher
                    $fundAccountId = $grn->fund_account_id;
                    $purchaseExpenseAccountId = $this->getExpenseAccount('Purchase');

                    $paymentDetail = PaymentDetail::where('reference_type', 'GRN')->where('reference_id', $grn->id)->first();
                    $payment = $paymentDetail ? Payment::find($paymentDetail->payment_id) : null;
                    if ($payment) {
                        $payment->update([
                            'amount'          => $totalAmount,
                            'discount'        => $discount,
                            'fund_account_id' => $fundAccountId,
                            'payment_date'    => $grn->getRawOriginal('grn_date') ?? $data['grn_date'],
                        ]);
                        $paymentDetail->update([
                            'amount' => $totalAmount
                        ]);
                    }

                    // Create new voucher
                    $voucher = Voucher::create([
                        'voucherno'         => Voucher::generateVoucherNo(),
                        'voucher_date'      => $grn->getRawOriginal('grn_date') ?? $data['grn_date'],
                        'voucher_type'      => 'Pay',
                        'narration'         => 'Direct Purchase GRN #' . ($grn->grn_no ?? $grn->id) . ' cash/bank payment',
                        'financial_year_id' => $this->getFinancialYearId(),
                        'payment_id'        => $payment ? $payment->id : null,
                        'source'            => 'GRN',
                        'source_id'         => $grn->id,
                        'status'            => 'active',
                    ]);

                    VoucherDetail::create([
                        'voucher_id'     => $voucher->id,
                        'account_id'     => $purchaseExpenseAccountId,
                        'dr_amount'      => $totalAmount,
                        'cr_amount'      => 0,
                        'reference_type' => 'GRN',
                        'reference_id'   => $grn->id,
                    ]);

                    VoucherDetail::create([
                        'voucher_id'     => $voucher->id,
                        'account_id'     => $fundAccountId ?: $this->getSystemAccount('Cash'),
                        'dr_amount'      => 0,
                        'cr_amount'      => $totalAmount,
                        'reference_type' => 'GRN',
                        'reference_id'   => $grn->id,
                    ]);
                }

                DB::commit();

                return $this->responseReturn("update", $grn);
            } catch (Exception $ex) {
                DB::rollBack();
                return response()->json(['exception' => $ex->errorInfo ?? $ex->getMessage()], 422);
            }
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
        DB::beginTransaction();

        try {
            $grn = Grn::find($id);
            if (!$grn) {
                return response()->json(['message' => 'GRN not found.'], 404);
            }

            // Check if any external payment exists (for non-direct GRN)
            if ($grn->grn_type !== 'direct') {
                $hasPayment = PaymentDetail::where('reference_type', 'GRN')
                    ->where('reference_id', $grn->id)
                    ->whereNull('deleted_at')
                    ->exists();

                if ($hasPayment) {
                    return response()->json([
                        'message' => 'This GRN has recorded payments. Delete is not allowed.'
                    ], 422);
                }
            }

            $purchaseId = $grn->purchase_id;

            // Delete stock transactions
            StockTransaction::where('reference_type', 'GRN')
                ->where('reference_id', $grn->id)
                ->delete();

            // Remove vouchers
            $this->removeVoucherBySourceInfo(['source' => 'GRN', 'source_id' => $grn->id]);

            // If direct GRN, clean up payment
            if ($grn->grn_type === 'direct') {
                $paymentDetails = PaymentDetail::where('reference_type', 'GRN')->where('reference_id', $grn->id)->get();
                foreach ($paymentDetails as $pd) {
                    $pmtId = $pd->payment_id;
                    $pd->delete();
                    if ($pmtId) {
                        Payment::where('id', $pmtId)->delete();
                    }
                }
            }

            // Soft-delete details and GRN
            $grn->grn_details()->delete();
            $grn->delete();

            // Re-calculate Purchase receive_status
            if ($purchaseId) {
                $this->updatePurchaseReceiveStatus($purchaseId);
            }

            DB::commit();

            return $this->responseReturn("delete", true);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['exception' => $e->getMessage()], 422);
        }
    }

    /**
     * Recalculate and update receive_status of a Purchase.
     *
     * @param  int  $purchase_id
     * @return void
     */
    private function updatePurchaseReceiveStatus($purchase_id)
    {
        if (!$purchase_id) return;

        $purchase = Purchase::with('purchase_details')->find($purchase_id);
        if (!$purchase) return;

        $totalOrdered = (float) $purchase->purchase_details->sum('qty');
        $totalReceived = (float) GrnDetail::whereHas('grn', function ($q) use ($purchase_id) {
            $q->where('purchase_id', $purchase_id);
        })->sum('received_qty');

        if ($totalReceived <= 0) {
            $status = 'Pending';
        } elseif ($totalReceived >= $totalOrdered) {
            $status = 'Received';
        } else {
            $status = 'Partial';
        }

        $purchase->update(['receive_status' => $status]);
    }

    /**
     * Validate form field.
     *
     * @return \Illuminate\Http\Response
     */
    public function validateCheck($request, $id = null)
    {
        return true;
    }
}
