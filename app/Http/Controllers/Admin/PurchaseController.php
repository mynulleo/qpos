<?php

/**
 * @Quill Information Technology
 */

namespace App\Http\Controllers\Admin;

use Storage;
use Exception;
use App\Models\Item;
use App\Models\Voucher;
use App\Models\Purchase;
use App\Traits\VoucherTrait;
use Illuminate\Http\Request;
use App\Models\PaymentDetail;
use App\Models\VoucherDetail;
use App\Models\PurchaseDetail;
use App\Http\Resources\Resource;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Base\BaseController;

class PurchaseController extends BaseController
{
    use VoucherTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Purchase::with([
            'supplier:id,org_name,mobile',
            'grns:id,purchase_id,grn_no,grn_date,total_qty,total_amount,status'
        ])
        ->withCount('grns')
        ->latest('id');

        if ($request->filled('value') && $request->value !== 'null' && $request->value !== 'undefined') {
            $val = trim($request->value);
            if ($request->filled('field_name') && !in_array($request->field_name, ['default', 'null', 'undefined', ''])) {
                if ($request->field_name === 'invoiceno') {
                    $query->where('invoiceno', 'like', "%{$val}%");
                } elseif ($request->field_name === 'supplier') {
                    $query->whereHas('supplier', function ($sq) use ($val) {
                        $sq->where('org_name', 'like', "%{$val}%")
                           ->orWhere('name', 'like', "%{$val}%")
                           ->orWhere('mobile', 'like', "%{$val}%");
                    });
                } else {
                    $query->where($request->field_name, 'like', "%{$val}%");
                }
            } else {
                $query->where(function ($q) use ($val) {
                    $q->where('invoiceno', 'like', "%{$val}%")
                      ->orWhereHas('supplier', function ($sq) use ($val) {
                          $sq->where('org_name', 'like', "%{$val}%")
                             ->orWhere('name', 'like', "%{$val}%")
                             ->orWhere('mobile', 'like', "%{$val}%");
                      });
                });
            }
        }

        if ($request->filled('supplier_id') && !in_array($request->supplier_id, ['null', 'undefined', ''])) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('category_id') && !in_array($request->category_id, ['null', 'undefined', ''])) {
            $query->whereHas('purchase_details', function ($q) use ($request) {
                $q->where('category_id', $request->category_id)
                  ->orWhereHas('item', function ($iq) use ($request) {
                      $iq->where('category_id', $request->category_id);
                  });
            });
        }

        if ($request->filled('item_id') && !in_array($request->item_id, ['null', 'undefined', ''])) {
            $query->whereHas('purchase_details', function ($q) use ($request) {
                $q->where('item_id', $request->item_id);
            });
        }

        if ($request->filled('status') && !in_array($request->status, ['null', 'undefined', ''])) {
            $query->where('status', $request->status);
        }

        if ($request->filled('receive_status') && !in_array($request->receive_status, ['null', 'undefined', ''])) {
            if ($request->receive_status === 'Pending') {
                $query->where(function ($q) {
                    $q->where('receive_status', 'Pending')
                      ->orWhereNull('receive_status');
                });
            } else {
                $query->where('receive_status', $request->receive_status);
            }
        }

        if ($request->filled('from_date') && !in_array($request->from_date, ['null', 'undefined', ''])) {
            $from = date('Y-m-d', strtotime($request->from_date));
            if ($from && $from !== '1970-01-01') {
                $query->whereDate('purchase_date', '>=', $from);
            }
        }

        if ($request->filled('to_date') && !in_array($request->to_date, ['null', 'undefined', ''])) {
            $to = date('Y-m-d', strtotime($request->to_date));
            if ($to && $to !== '1970-01-01') {
                $query->whereDate('purchase_date', '<=', $to);
            }
        }

        if ($request->allData) {
            return $query->get();
        } else {
            // Compute KPI summary on the filtered dataset
            $summaryQuery = clone $query;
            $total_pos = (clone $summaryQuery)->count();
            $total_amount = (clone $summaryQuery)->sum('total_amount');
            $total_discount = (clone $summaryQuery)->sum('discount');
            $total_tax = (clone $summaryQuery)->sum('tax');

            $pending_count = (clone $summaryQuery)->where(function ($q) {
                $q->where('receive_status', 'Pending')->orWhereNull('receive_status');
            })->count();
            $partial_count = (clone $summaryQuery)->where('receive_status', 'Partial')->count();
            $received_count = (clone $summaryQuery)->where('receive_status', 'Received')->count();

            $summary = [
                'total_pos' => $total_pos,
                'total_amount' => (float)$total_amount,
                'total_discount' => (float)$total_discount,
                'total_tax' => (float)$total_tax,
                'pending_count' => $pending_count,
                'partial_count' => $partial_count,
                'received_count' => $received_count,
            ];

            $datas = $query->paginate($request->pagination ?? 10);
            $resource = new Resource($datas);
            $resource->additional(['summary' => $summary]);

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
        return view('layouts.backend_app');
    }

    public function generateInvoiceNo()
    {
        $invoiceNo = Purchase::generateInvoiceNo();
        return response()->json($invoiceNo);
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
            try {
                $data = $request->all();
                if (empty($data['invoiceno'])) {
                    $data['invoiceno'] = Purchase::generateInvoiceNo();
                }
                $purchasedetails = $data['purchase_details'];
                $data['purchase_date'] = date('Y-m-d', strtotime($data['purchase_date']));
                unset($data['purchase_details']);
                $data['receive_status'] = 'Pending';
                // push the insert text
                $res = Purchase::create($data);

                if ($res && !empty($purchasedetails)) {
                    foreach ($purchasedetails as $detail) {
                        $detail['purchase_id'] = $res->id;
                        $colorId = !empty($detail['color_id']) ? $detail['color_id'] : null;
                        $sizeId = !empty($detail['size_id']) ? $detail['size_id'] : null;
                        $purchasePrice = isset($detail['price']) ? floatval($detail['price']) : 0;
                        $sellingPrice = isset($detail['selling_price']) ? floatval($detail['selling_price']) : 0;
                        $serialNo = !empty($detail['serial_no']) ? $detail['serial_no'] : null;

                        $detail['color_id'] = $colorId;
                        $detail['size_id'] = $sizeId;
                        $detail['serial_no'] = $serialNo;
                        $detail['selling_price'] = $sellingPrice;

                        // remove item object from detail array
                        unset($detail['item'], $detail['items']);
                        // insert purchase details
                        PurchaseDetail::create($detail);

                        // update/create item price for color & size variant
                        if ($colorId || $sizeId || $purchasePrice > 0 || $sellingPrice > 0) {
                            \App\Models\ItemPrice::updateOrCreate(
                                [
                                    'item_id' => $detail['item_id'],
                                    'color_id' => $colorId,
                                    'size_id' => $sizeId,
                                ],
                                [
                                    'purchase_price' => $purchasePrice,
                                    'selling_price' => $sellingPrice,
                                    'status' => 'active',
                                ]
                            );
                        }
                    }
                }

                return $this->responseReturn("create", $res);
            } catch (Exception $ex) {
                return response()->json(['exception' => $ex->errorInfo ?? $ex->getMessage()], 422);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        if ($request->format() == 'html') {
            return view('layouts.backend_app');
        }
        $purchase = Purchase::with([
            'supplier',
            'grns.warehouse',
            'purchase_details.item',
            'purchase_details.category',
            'purchase_details.unit',
            'purchase_details.color',
            'purchase_details.size'
        ])->withCount('grns')->find($id);

        if (!$purchase) {
            return response()->json(['message' => 'Purchase not found'], 404);
        }

        // each row category wise items attach
        if ($purchase->purchase_details) {
            $purchase->purchase_details->transform(function ($detail) {
                $detail->items = Item::where('category_id', $detail->category_id)
                    ->select('id', 'title')
                    ->get();

                return $detail;
            });
        }

        return $purchase;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Purchase  $purchase
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
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $purchase = Purchase::withCount('grns')->find($id);
        if (!$purchase) {
            return response()->json(['message' => 'Purchase not found'], 404);
        }

        // ========================================================
        // 🔒 Check if GRN received (Edit is strictly locked if GRN exists)
        // ========================================================
        $hasGrn = \App\Models\Grn::where('purchase_id', $purchase->id)->exists();
        if ($hasGrn || $purchase->grns_count > 0 || in_array($purchase->receive_status, ['Partial', 'Received'])) {
            return response()->json([
                'message' => 'This Purchase Order has already been received via GRN and cannot be edited.'
            ], 422);
        }

        if ($this->validateCheck($request, $purchase->id)) {
            try {
                $data = $request->all();
                $data['purchase_date'] = date('Y-m-d', strtotime($data['purchase_date']));
                $purchasedetails = $data['purchase_details'];
                unset($data['purchase_details']);
                // push the update text
                $purchase->fill($data)->save();

                // delete existing purchase details
                PurchaseDetail::where('purchase_id', $purchase->id)->delete();

                if ($purchase && !empty($purchasedetails)) {
                    foreach ($purchasedetails as $detail) {
                        $detail['purchase_id'] = $purchase->id;
                        $colorId = !empty($detail['color_id']) ? $detail['color_id'] : null;
                        $sizeId = !empty($detail['size_id']) ? $detail['size_id'] : null;
                        $purchasePrice = isset($detail['price']) ? floatval($detail['price']) : 0;
                        $sellingPrice = isset($detail['selling_price']) ? floatval($detail['selling_price']) : 0;
                        $serialNo = !empty($detail['serial_no']) ? $detail['serial_no'] : null;

                        $detail['color_id'] = $colorId;
                        $detail['size_id'] = $sizeId;
                        $detail['serial_no'] = $serialNo;
                        $detail['selling_price'] = $sellingPrice;

                        // remove item object from detail array
                        unset($detail['item'], $detail['items']);
                        // insert purchase details
                        PurchaseDetail::create($detail);

                        // update/create item price for color & size variant
                        if ($colorId || $sizeId || $purchasePrice > 0 || $sellingPrice > 0) {
                            \App\Models\ItemPrice::updateOrCreate(
                                [
                                    'item_id' => $detail['item_id'],
                                    'color_id' => $colorId,
                                    'size_id' => $sizeId,
                                ],
                                [
                                    'purchase_price' => $purchasePrice,
                                    'selling_price' => $sellingPrice,
                                    'status' => 'active',
                                ]
                            );
                        }
                    }
                }

                return $this->responseReturn("update", $purchase);
            } catch (Exception $ex) {
                return response()->json(['exception' => $ex->errorInfo ?? $ex->getMessage()], 422);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $purchase = Purchase::withCount('grns')->find($id);
            if (!$purchase) {
                return response()->json(['message' => 'Purchase not found'], 404);
            }

            // =========================
            // 1️⃣ Check if GRN exists
            // =========================
            $hasGrn = \App\Models\Grn::where('purchase_id', $purchase->id)->exists();
            if ($hasGrn || $purchase->grns_count > 0 || in_array($purchase->receive_status, ['Partial', 'Received'])) {
                return response()->json([
                    'message' => 'This Purchase has Goods Receive Notes (GRN). Delete is not allowed.'
                ], 422);
            }

            // =========================
            // 2️⃣ Eligibility check (payment/receive হয়েছে কিনা)
            // =========================
            $hasPayment = PaymentDetail::where('reference_type', 'Purchase')
                ->where('reference_id', $purchase->id)
                ->whereNull('deleted_at')   // soft delete aware
                ->exists();

            if ($hasPayment) {
                return response()->json([
                    'message' => 'This Purchase has payment/receive entries. Delete is not allowed.'
                ], 422);
            }

            // =========================
            // 3️⃣ Delete related vouchers (if legacy purchase had vouchers)
            // =========================
            $vouchers = Voucher::where('source', 'Purchase')
                ->where('source_id', $purchase->id)
                ->get();

            foreach ($vouchers as $voucher) {
                VoucherDetail::where('voucher_id', $voucher->id)->delete(); // soft delete
                $voucher->delete(); // soft delete
            }

            // =========================
            // 4️⃣ Delete purchase details (if exists)
            // =========================
            if (method_exists($purchase, 'purchase_details')) {
                $purchase->purchase_details()->delete(); // soft delete
            } else {
                // fallback if relation not defined
                PurchaseDetail::where('purchase_id', $purchase->id)->delete();
            }

            // =========================
            // 5️⃣ Delete purchase (soft)
            // =========================
            $purchase->delete(); // soft delete

            DB::commit();

            return $this->responseReturn("delete", true);
        } catch (Exception $e) {

            DB::rollBack();

            return response()->json([
                'exception' => $e->getMessage()
            ], 422);
        }
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
