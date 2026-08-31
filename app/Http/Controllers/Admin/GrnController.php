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
use App\Models\PaymentDetail;
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Grn::with([
            'purchase:id,invoiceno,purchase_date',
            'supplier:id,org_name,mobile',
            'warehouse:id,name,code'
        ])->latest('id');

        if ($request->field_name && $request->value) {
            $query->whereLike($request->field_name, $request->value);
        }

        if ($request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->purchase_id) {
            $query->where('purchase_id', $request->purchase_id);
        }

        if ($request->from_date) {
            $query->whereDate('grn_date', '>=', date('Y-m-d', strtotime($request->from_date)));
        }

        if ($request->to_date) {
            $query->whereDate('grn_date', '<=', date('Y-m-d', strtotime($request->to_date)));
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->allData) {
            return $query->get();
        } else {
            $datas = $query->paginate($request->pagination ?? 10);
            return new Resource($datas);
        }
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
    public function getPendingPurchases(Request $request)
    {
        $query = Purchase::with('supplier:id,org_name')
            ->where(function ($q) {
                $q->where('receive_status', '!=', 'Received')
                  ->orWhereNull('receive_status');
            })
            ->where('status', 'active')
            ->latest('id');

        if ($request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $purchases = $query->get(['id', 'invoiceno', 'purchase_date', 'supplier_id', 'total_amount', 'receive_status']);
        return response()->json($purchases);
    }

    /**
     * Get purchase line items with received & remaining quantities.
     *
     * @param  int  $purchase_id
     * @return \Illuminate\Http\Response
     */
    public function getPurchaseItems($purchase_id)
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

                if (empty($details)) {
                    return response()->json(['message' => 'At least one item must be received.'], 422);
                }

                // Auto-generate GRN number
                $data['grn_no'] = Grn::generateGrnNo();
                $data['grn_date'] = date('Y-m-d', strtotime($data['grn_date']));
                $data['challan_date'] = !empty($data['challan_date']) ? date('Y-m-d', strtotime($data['challan_date'])) : null;

                unset($data['grn_details'], $data['purchase'], $data['supplier'], $data['warehouse']);

                $totalQty = 0;
                $totalAmount = 0;

                foreach ($details as $item) {
                    $qty = floatval($item['received_qty'] ?? 0);
                    if ($qty > 0) {
                        $price = floatval($item['unit_price'] ?? 0);
                        $totalQty += $qty;
                        $totalAmount += ($qty * $price);
                    }
                }

                if ($totalQty <= 0) {
                    return response()->json(['message' => 'Received quantity must be greater than 0.'], 422);
                }

                $data['total_qty'] = $totalQty;
                $data['total_amount'] = $totalAmount;

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
                        'purchase_detail_id'      => $item['purchase_detail_id'] ?? null,
                        'category_id'             => $item['category_id'] ?? null,
                        'item_id'                 => $item['item_id'],
                        'color_id'                => $colorId,
                        'size_id'                 => $sizeId,
                        'unit_id'                 => $item['unit_id'] ?? null,
                        'ordered_qty'             => floatval($item['ordered_qty'] ?? 0),
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

                // 4. Update Purchase receive_status
                $this->updatePurchaseReceiveStatus($grn->purchase_id);

                // 5. Create Payable Voucher for received amount
                $vdata = [
                    'module'    => 'GRN',
                    'date'      => $grn->getRawOriginal('grn_date') ?? $data['grn_date'],
                    'amount'    => $grn->total_amount,
                    'source_id' => $grn->id,
                    'ref_id'    => $grn->supplier_id
                ];
                $this->createPayableVoucher($vdata);

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

        // Check if any payment was recorded against this GRN
        $hasPayment = PaymentDetail::where('reference_type', 'GRN')
            ->where('reference_id', $grn->id)
            ->whereNull('deleted_at')
            ->exists();

        if ($hasPayment) {
            return response()->json([
                'message' => 'This GRN has recorded payments. Edit is restricted to maintain accounting integrity.'
            ], 422);
        }

        if ($this->validateCheck($request, $grn->id)) {
            DB::beginTransaction();

            try {
                $data = $request->all();
                $details = $data['grn_details'] ?? [];

                $data['grn_date'] = date('Y-m-d', strtotime($data['grn_date']));
                $data['challan_date'] = !empty($data['challan_date']) ? date('Y-m-d', strtotime($data['challan_date'])) : null;

                unset($data['grn_details'], $data['purchase'], $data['supplier'], $data['warehouse']);

                $totalQty = 0;
                $totalAmount = 0;

                foreach ($details as $item) {
                    $qty = floatval($item['received_qty'] ?? 0);
                    if ($qty > 0) {
                        $price = floatval($item['unit_price'] ?? 0);
                        $totalQty += $qty;
                        $totalAmount += ($qty * $price);
                    }
                }

                $data['total_qty'] = $totalQty;
                $data['total_amount'] = $totalAmount;

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
                        'purchase_detail_id'      => $item['purchase_detail_id'] ?? null,
                        'category_id'             => $item['category_id'] ?? null,
                        'item_id'                 => $item['item_id'],
                        'color_id'                => $colorId,
                        'size_id'                 => $sizeId,
                        'unit_id'                 => $item['unit_id'] ?? null,
                        'ordered_qty'             => floatval($item['ordered_qty'] ?? 0),
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

                // 3. Remove previous payable voucher and create updated voucher
                $this->removeVoucherBySourceInfo(['source' => 'GRN', 'source_id' => $grn->id]);

                $vdata = [
                    'module'    => 'GRN',
                    'date'      => $grn->getRawOriginal('grn_date') ?? $data['grn_date'],
                    'amount'    => $grn->total_amount,
                    'source_id' => $grn->id,
                    'ref_id'    => $grn->supplier_id
                ];
                $this->createPayableVoucher($vdata);

                // 4. Re-calculate Purchase receive_status
                $this->updatePurchaseReceiveStatus($grn->purchase_id);

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

            // Check if any payment exists
            $hasPayment = PaymentDetail::where('reference_type', 'GRN')
                ->where('reference_id', $grn->id)
                ->whereNull('deleted_at')
                ->exists();

            if ($hasPayment) {
                return response()->json([
                    'message' => 'This GRN has recorded payments. Delete is not allowed.'
                ], 422);
            }

            $purchaseId = $grn->purchase_id;

            // Delete stock transactions
            StockTransaction::where('reference_type', 'GRN')
                ->where('reference_id', $grn->id)
                ->delete();

            // Remove payable vouchers
            $this->removeVoucherBySourceInfo(['source' => 'GRN', 'source_id' => $grn->id]);

            // Soft-delete details and GRN
            $grn->grn_details()->delete();
            $grn->delete();

            // Re-calculate Purchase receive_status
            $this->updatePurchaseReceiveStatus($purchaseId);

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
