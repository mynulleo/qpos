<?php

/**
 * @Quill Information Technology
 */

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Discount;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Http\Resources\Resource;
use App\Http\Controllers\Base\BaseController;
use Illuminate\Support\Facades\DB;

class DiscountController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Discount::with([
            'category:id,title',
            'item:id,title,barcode',
            'creator:id,name'
        ])->latest('id');

        if ($request->filled('value') && $request->value !== 'null' && $request->value !== 'undefined') {
            $val = trim($request->value);
            $query->where(function ($q) use ($val) {
                $q->where('name', 'like', "%{$val}%")
                  ->orWhereHas('category', function ($cq) use ($val) {
                      $cq->where('title', 'like', "%{$val}%");
                  })
                  ->orWhereHas('item', function ($iq) use ($val) {
                      $iq->where('title', 'like', "%{$val}%")
                         ->orWhere('barcode', 'like', "%{$val}%");
                  });
            });
        }

        if ($request->filled('discount_type') && $request->discount_type !== 'null' && $request->discount_type !== '') {
            $query->where('discount_type', $request->discount_type);
        }

        if ($request->filled('applicable_on') && $request->applicable_on !== 'null' && $request->applicable_on !== '') {
            $query->where('applicable_on', $request->applicable_on);
        }

        if ($request->filled('status') && $request->status !== 'null' && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->filled('valid_date')) {
            $date = date('Y-m-d', strtotime($request->valid_date));
            $query->whereDate('valid_from', '<=', $date)->whereDate('valid_to', '>=', $date);
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
                DB::beginTransaction();
                $data = $request->all();

                if (!empty($data['valid_from'])) {
                    $data['valid_from'] = date('Y-m-d', strtotime($data['valid_from']));
                }
                if (!empty($data['valid_to'])) {
                    $data['valid_to'] = date('Y-m-d', strtotime($data['valid_to']));
                }

                if (($data['discount_type'] ?? '') === 'category') {
                    $data['item_id'] = null;
                } elseif (($data['discount_type'] ?? '') === 'item') {
                    $data['category_id'] = null;
                }

                $authAdmin = auth()->guard('admin')->user() ?? auth()->user();
                $data['created_by'] = $authAdmin ? $authAdmin->id : 1;

                $res = Discount::create($data);

                DB::commit();
                return $this->responseReturn("create", $res);
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

        $discount = Discount::with([
            'category:id,title',
            'item:id,title,barcode,category_id',
            'creator:id,name'
        ])->find($id);

        if (!$discount) {
            return response()->json(['message' => 'Discount not found'], 404);
        }

        return $discount;
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
        $discount = Discount::find($id);
        if (!$discount) {
            return response()->json(['type' => 'error', 'message' => 'Discount not found!'], 404);
        }

        if ($this->validateCheck($request, $discount->id)) {
            try {
                DB::beginTransaction();
                $data = $request->all();

                if (!empty($data['valid_from'])) {
                    $data['valid_from'] = date('Y-m-d', strtotime($data['valid_from']));
                }
                if (!empty($data['valid_to'])) {
                    $data['valid_to'] = date('Y-m-d', strtotime($data['valid_to']));
                }

                if (($data['discount_type'] ?? '') === 'category') {
                    $data['item_id'] = null;
                } elseif (($data['discount_type'] ?? '') === 'item') {
                    $data['category_id'] = null;
                }

                $authAdmin = auth()->guard('admin')->user() ?? auth()->user();
                $data['updated_by'] = $authAdmin ? $authAdmin->id : 1;

                $discount->fill($data)->save();

                DB::commit();
                return $this->responseReturn("update", $discount);
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
        $discount = Discount::find($id);
        if (!$discount) {
            return response()->json(['type' => 'error', 'message' => 'Discount not found!'], 404);
        }

        $res = $discount->delete();
        return $this->responseReturn("delete", $res);
    }

    /**
     * Toggle status between active and inactive.
     */
    public function toggleStatus($id)
    {
        $discount = Discount::find($id);
        if (!$discount) {
            return response()->json(['type' => 'error', 'message' => 'Discount not found!'], 404);
        }

        $discount->status = ($discount->status === 'active') ? 'inactive' : 'active';
        $discount->save();

        return response()->json([
            'type'    => 'success',
            'message' => 'Status updated successfully',
            'status'  => $discount->status,
        ]);
    }

    /**
     * Get active discounts for POS terminal.
     */
    public function getActiveDiscounts(Request $request)
    {
        $today = date('Y-m-d');
        $discounts = Discount::activeValid($today)
            ->with(['category:id,title', 'item:id,title,barcode'])
            ->get();

        return response()->json($discounts);
    }

    /**
     * Validate form fields.
     */
    public function validateCheck($request, $id = null)
    {
        return true;
    }
}
