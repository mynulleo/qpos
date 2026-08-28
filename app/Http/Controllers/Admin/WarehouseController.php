<?php

/**
 * @Quill Information Technology
 */

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Warehouse;
use App\Models\Grn;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use App\Http\Resources\Resource;
use App\Http\Controllers\Base\BaseController;

class WarehouseController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Warehouse::with('branch:id,title')->orderBy('sorting', 'asc')->latest('id');

        if ($request->field_name && $request->value) {
            $query->whereLike($request->field_name, $request->value);
        }

        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
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
                $res = Warehouse::create($data);
                return $this->responseReturn("create", $res);
            } catch (Exception $ex) {
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
        $warehouse = Warehouse::with('branch:id,title')->find($id);
        return $warehouse;
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
        $warehouse = Warehouse::find($id);
        if ($this->validateCheck($request, $warehouse->id)) {
            try {
                $data = $request->all();
                $warehouse->fill($data)->save();

                return $this->responseReturn("update", $warehouse);
            } catch (Exception $ex) {
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
        $warehouse = Warehouse::find($id);
        if (!$warehouse) {
            return response()->json(['message' => 'Warehouse not found.'], 404);
        }

        // Safety check: is this warehouse used in GRN or Stock Transactions?
        $hasGrn = Grn::where('warehouse_id', $warehouse->id)->exists();
        if ($hasGrn) {
            return response()->json([
                'message' => 'Cannot delete warehouse. Goods Receive Notes (GRN) are associated with this warehouse.'
            ], 422);
        }

        $hasStock = StockTransaction::where('warehouse_id', $warehouse->id)->exists();
        if ($hasStock) {
            return response()->json([
                'message' => 'Cannot delete warehouse. Inventory transactions exist in this warehouse.'
            ], 422);
        }

        $res = $warehouse->delete();
        return $this->responseReturn("delete", $res);
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
