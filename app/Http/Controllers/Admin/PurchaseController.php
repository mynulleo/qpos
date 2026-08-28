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
        $query  = Purchase::with('supplier:id,org_name')->latest();
        if ($request->field_name && $request->value) {
            $query->whereLike($request->field_name, $request->value);
        }

        if ($request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->allData) {
            return $query->get();
        } else {
            $datas = $query->paginate($request->pagination);
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
        $purchase = Purchase::with(
            'supplier',
            'purchase_details.item',
            'purchase_details.category',
            'purchase_details.unit',
            'purchase_details.color',
            'purchase_details.size'
        )->find($id);

        // 🔥 each row তে category wise items attach
        $purchase->purchase_details->transform(function ($detail) {
            $detail->items = Item::where('category_id', $detail->category_id)
                ->select('id', 'title')
                ->get();

            return $detail;
        });

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
        $purchase = Purchase::find($id);
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
            $purchase = Purchase::find($id);

            // =========================
            // 1️⃣ Check if GRN exists
            // =========================
            $hasGrn = \App\Models\Grn::where('purchase_id', $purchase->id)->exists();
            if ($hasGrn) {
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
