<?php

/**
 * @Quill Information Technology
 */

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentDetail;
use App\Models\StockTransaction;
use App\Models\Item;
use App\Http\Resources\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Base\BaseController;

class StockAdjustmentController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = StockAdjustment::with(['conductedBy:id,full_name,name', 'warehouse:id,name'])->latest('id');
        $query->whereLike($request->field_name, $request->value);
        $query->whereAny('status', $request->status);
        $query->whereLike('adjustment_no', $request->adjustment_no);
        $query->whereAny('adjustment_type', $request->adjustment_type);
        $query->whereAny('conducted_by', $request->conducted_by);

        if (!empty($request->from_date) && !empty($request->to_date)) {
            $query->whereDates('adjustment_date', $request->from_date, $request->to_date);
        }

        if (!empty($request->item_id)) {
            $query->whereHas('details', function ($q) use ($request) {
                $q->where('item_id', $request->item_id);
            });
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validateCheck($request);

        DB::beginTransaction();

        try {
            $data = $request->all();

            if (empty($data['adjustment_no'])) {
                $data['adjustment_no'] = StockAdjustment::generateAdjustmentNo();
            }

            if (!empty($data['adjustment_date'])) {
                $data['adjustment_date'] = function_exists('vue_to_server_date') ? vue_to_server_date($data['adjustment_date']) : $data['adjustment_date'];
            } else {
                $data['adjustment_date'] = date('Y-m-d');
            }

            $details = $data['adjustment_details'] ?? $data['details'] ?? [];
            unset($data['adjustment_details'], $data['details']);

            if (empty($details) || !is_array($details)) {
                return response()->json([
                    'type' => 'error',
                    'message' => 'কমপক্ষে একটি আইটেম যোগ করুন!'
                ], 422);
            }

            $totalItems = count($details);
            $totalAdjustedQty = 0;
            $totalAmount = 0;

            foreach ($details as $d) {
                $diff = isset($d['difference_qty']) ? floatval($d['difference_qty']) : 0;
                $cost = isset($d['unit_cost']) ? floatval($d['unit_cost']) : 0;
                $totalAdjustedQty += abs($diff);
                $totalAmount += abs($diff) * $cost;
            }

            $data['total_items'] = $totalItems;
            $data['total_adjusted_qty'] = $totalAdjustedQty;
            $data['total_amount'] = $totalAmount;
            $data['status'] = $data['status'] ?? 'active';

            $adjustment = StockAdjustment::create($data);

            foreach ($details as $detail) {
                $itemId = $detail['item_id'] ?? null;
                if (!$itemId) continue;

                $colorId = !empty($detail['color_id']) ? $detail['color_id'] : null;
                $sizeId = !empty($detail['size_id']) ? $detail['size_id'] : null;
                $warehouseId = !empty($detail['warehouse_id']) ? $detail['warehouse_id'] : ($adjustment->warehouse_id ?? null);
                $systemQty = isset($detail['system_qty']) ? floatval($detail['system_qty']) : 0;
                $physicalQty = isset($detail['physical_qty']) ? floatval($detail['physical_qty']) : 0;
                $differenceQty = isset($detail['difference_qty']) ? floatval($detail['difference_qty']) : ($physicalQty - $systemQty);
                $unitCost = isset($detail['unit_cost']) ? floatval($detail['unit_cost']) : 0;
                $lineTotal = round(abs($differenceQty) * $unitCost, 2);

                StockAdjustmentDetail::create([
                    'stock_adjustment_id' => $adjustment->id,
                    'item_id'             => $itemId,
                    'color_id'            => $colorId,
                    'size_id'             => $sizeId,
                    'warehouse_id'        => $warehouseId,
                    'system_qty'          => $systemQty,
                    'physical_qty'        => $physicalQty,
                    'difference_qty'      => $differenceQty,
                    'unit_cost'           => $unitCost,
                    'total_amount'        => $lineTotal,
                    'remarks'             => $detail['remarks'] ?? null,
                    'status'              => 'active',
                ]);

                // Create StockTransaction based on Difference
                if ($differenceQty > 0) {
                    // Positive Difference: Physical count is higher -> Stock In
                    StockTransaction::create([
                        'item_id'          => $itemId,
                        'color_id'         => $colorId,
                        'size_id'          => $sizeId,
                        'warehouse_id'     => $warehouseId,
                        'transaction_date' => $adjustment->getRawOriginal('adjustment_date') ?: date('Y-m-d'),
                        'transaction_type' => 'Adjustment',
                        'reference_type'   => 'StockAdjustment',
                        'reference_id'     => $adjustment->id,
                        'qty_in'           => $differenceQty,
                        'qty_out'          => 0,
                        'status'           => 'active',
                    ]);
                } elseif ($differenceQty < 0) {
                    // Negative Difference: Physical count is lower -> Stock Out
                    StockTransaction::create([
                        'item_id'          => $itemId,
                        'color_id'         => $colorId,
                        'size_id'          => $sizeId,
                        'warehouse_id'     => $warehouseId,
                        'transaction_date' => $adjustment->getRawOriginal('adjustment_date') ?: date('Y-m-d'),
                        'transaction_type' => 'Adjustment',
                        'reference_type'   => 'StockAdjustment',
                        'reference_id'     => $adjustment->id,
                        'qty_in'           => 0,
                        'qty_out'          => abs($differenceQty),
                        'status'           => 'active',
                    ]);
                }
            }

            DB::commit();
            return $this->responseReturn('create', $adjustment);
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json(['exception' => $ex->getMessage()], 422);
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

        $adjustment = StockAdjustment::with([
            'conductedBy:id,full_name,name,phone',
            'warehouse:id,name',
            'details' => function ($q) {
                $q->with([
                    'item:id,title,barcode,unit_id,category_id,brand_id',
                    'item.unit:id,title',
                    'item.category:id,title',
                    'item.brand:id,title',
                    'color:id,title',
                    'size:id,title',
                    'warehouse:id,name',
                ]);
            }
        ])->find($id);

        if (!$adjustment) {
            return response()->json(['message' => 'Stock Adjustment not found!'], 404);
        }

        return $adjustment;
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $adjustment = StockAdjustment::find($id);
        if (!$adjustment) {
            return response()->json(['type' => 'error', 'message' => 'Adjustment not found!'], 404);
        }

        try {
            DB::beginTransaction();

            // Revert / remove stock transactions
            StockTransaction::where('reference_type', 'StockAdjustment')
                ->where('reference_id', $id)
                ->delete();

            StockAdjustmentDetail::where('stock_adjustment_id', $id)->delete();
            $res = $adjustment->delete();

            DB::commit();
            return $this->responseReturn('delete', $res);
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json(['type' => 'error', 'message' => $ex->getMessage()], 422);
        }
    }

    /**
     * Validate form request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int|null  $id
     * @return array
     */
    public function validateCheck(Request $request, $id = null)
    {
        return $request->validate([
            'adjustment_type' => 'required|string',
            'adjustment_date' => 'required',
        ]);
    }
}
