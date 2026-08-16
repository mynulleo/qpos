<?php

/**
 * @Quill Information Technology
 */

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Challan;
use Illuminate\Http\Request;
use App\Http\Resources\Resource;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Base\BaseController;
use App\Models\ChallanDetail;
use Storage;

class ChallanController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Challan::with([
            'workorder:id,order_no,order_date,uno_no',
            'client:id,org_name'
        ])->latest();

        // Client filter
        if ($request->client_id) {
            $query->where('client_id', $request->client_id);
        }

        // Workorder filter
        if ($request->workorder_id) {
            $query->where('workorder_id', $request->workorder_id);
        }

        // Workorder সম্পর্কিত filter
        if (
            $request->order_no ||
            $request->uno_no ||
            $request->order_date
        ) {
            $query->whereHas('workorder', function ($q) use ($request) {

                if ($request->order_no) {
                    $q->where('order_no', 'like', '%' . $request->order_no . '%');
                }

                if ($request->uno_no) {
                    $q->where('uno_no', 'like', '%' . $request->uno_no . '%');
                }

                if ($request->order_date) {
                    $q->whereDate('order_date', '>=', $request->order_date);
                }
            });
        }

        // Challan Date range
        if ($request->from_challan_date) {
            $query->whereDate('challan_date', '>=', $request->from_challan_date);
        }

        if ($request->to_challan_date) {
            $query->whereDate('challan_date', '<=', $request->to_challan_date);
        }

        // Generic search (optional)
        if ($request->field_name && $request->value) {
            $query->whereLike($request->field_name, $request->value);
        }

        // 📦 Response
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

                $res = DB::transaction(function () use ($request) {

                    $data = $request->except('challan_details', 'workorder');
                    $data['challan_no'] = Challan::generateChallanNumber();
                    $data['challan_date'] = vue_to_server_date($data['challan_date']);
                    // Master insert
                    // dd($data, $request->challan_details);
                    $challan = Challan::create($data);

                    // Details insert
                    if ($challan && !empty($request->challan_details)) {

                        foreach ($request->challan_details as $row) {

                            ChallanDetail::create([
                                'challan_id' => $challan->id,
                                'workorder_detail_id' => $row['id'],
                                'item_id' => $row['item_id'],
                                'description' => $row['description'] ?? '',
                                'qty' => $row['qty'] ?? 0,
                                'receive_qty' => $row['receive_qty'] ?? 0,
                                'remark' => $row['remark'] ?? '',
                            ]);
                        }
                    }

                    return $challan;
                });

                return $this->responseReturn("create", $res);
            } catch (Exception $ex) {
                return response()->json(['exception' => $ex->errorInfo ?? $ex->getMessage()], 422);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Challan  $challan
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        if ($request->format() == 'html') {
            return view('layouts.backend_app');
        }
        $challan = Challan::with(['client', 'challan_details.item', 'workorder'])->find($id);
        return $challan;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Challan  $challan
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
     * @param  \App\Models\Challan  $challan
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if ($this->validateCheck($request, $id)) {
            try {

                $res = DB::transaction(function () use ($request, $id) {

                    $challan = Challan::findOrFail($id);

                    // Master update
                    $data = $request->except('challan_details', 'workorder');
                    $data['challan_date'] = vue_to_server_date($data['challan_date']);
                    $challan->update($data);

                    $existingIds = ChallanDetail::where('challan_id', $challan->id)
                        ->pluck('id')
                        ->toArray();

                    $requestIds = [];

                    if (!empty($request->challan_details)) {

                        foreach ($request->challan_details as $row) {

                            // existing row update
                            if (array_key_exists('id', $row) && !empty($row['id'])) {

                                $requestIds[] = $row['id'];
                                $detail = ChallanDetail::find($row['id']);

                                if ($detail) {
                                    $detail->update([
                                        'workorder_detail_id'   => $row['workorder_detail_id'],
                                        'item_id'               => $row['item_id'],
                                        'description'           => $row['description'] ?? '',
                                        'qty'                   => $row['qty'] ?? 0,
                                        'receive_qty'           => $row['receive_qty'] ?? 0,
                                        'remark'                => $row['remark'] ?? '',
                                    ]);
                                }
                            }
                            // new row insert
                            else {

                                $new = ChallanDetail::create([
                                    'challan_id'          => $challan->id,
                                    'workorder_detail_id' => $row['workorder_detail_id'],
                                    'description'         => $row['description'] ?? '',
                                    'qty'                 => $row['qty'] ?? 0,
                                    'receive_qty'         => $row['receive_qty'] ?? 0,
                                    'remark'              => $row['remark'] ?? '',
                                ]);

                                $requestIds[] = $new->id;
                            }
                        }
                    }

                    // removed row delete (যেগুলো request এ নাই)
                    $deleteIds = array_diff($existingIds, $requestIds);

                    if (!empty($deleteIds)) {
                        ChallanDetail::whereIn('id', $deleteIds)->delete();
                    }

                    return $challan;
                });

                return $this->responseReturn("update", $res);
            } catch (Exception $ex) {
                return response()->json([
                    'exception' => $ex->errorInfo ?? $ex->getMessage()
                ], 422);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Challan  $challan
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $challan = Challan::with('challan_details')->findOrFail($id);

        DB::transaction(function () use ($challan) {

            // 1. First delete details (model event trigger hobe)
            foreach ($challan->challan_details as $detail) {
                $detail->delete(); // ✅ boot deleted() trigger hobe
            }

            // 2. Then delete challan
            $challan->delete();
        });

        return $this->responseReturn("delete", true);
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
