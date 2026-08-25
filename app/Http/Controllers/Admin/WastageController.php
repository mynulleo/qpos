<?php

/**
 * @Quill Information Technology
 */

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Wastage;
use App\Models\WastageDetail;
use App\Models\StockTransaction;
use App\Http\Resources\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Base\BaseController;

class WastageController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Wastage::with([
            'auditor:id,full_name',
            'approved_admin:id,full_name',
            'branch:id,title',
            'creator:id,name'
        ])
        ->withCount('wastage_details')
        ->latest('id');

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function ($q) use ($keyword) {
                $q->where('audit_number', 'like', "%{$keyword}%")
                  ->orWhere('audited_by', 'like', "%{$keyword}%")
                  ->orWhere('note', 'like', "%{$keyword}%");
            });
        }

        if ($request->has('field_name') && $request->has('value') && !empty($request->value)) {
            $query->whereLike($request->field_name, $request->value);
        }

        if ($request->filled('audit_number')) {
            $query->where('audit_number', 'like', '%' . trim($request->audit_number) . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('auditor_id')) {
            $query->where('auditor_id', $request->auditor_id);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('item_id')) {
            $itemId = $request->item_id;
            $query->whereHas('wastage_details', function ($q) use ($itemId) {
                $q->where('item_id', $itemId);
            });
        }

        $fromDate = $request->from_date ?: $request->from_audit_date;
        $toDate   = $request->to_date ?: $request->to_audit_date;

        if (!empty($fromDate) && !empty($toDate)) {
            $date_from = function_exists('vue_to_server_date') ? vue_to_server_date($fromDate) : $fromDate;
            $date_to   = function_exists('vue_to_server_date') ? vue_to_server_date($toDate) : $toDate;
            $query->whereBetween('audit_date', [$date_from, $date_to]);
        } elseif (!empty($fromDate)) {
            $date_from = function_exists('vue_to_server_date') ? vue_to_server_date($fromDate) : $fromDate;
            $query->where('audit_date', '>=', $date_from);
        } elseif (!empty($toDate)) {
            $date_to = function_exists('vue_to_server_date') ? vue_to_server_date($toDate) : $toDate;
            $query->where('audit_date', '<=', $date_to);
        }

        if ($request->allData) {
            return $query->get();
        } else {
            $datas = $query->paginate($request->pagination ?? 15);
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

            if (empty($data['audit_number'])) {
                $data['audit_number'] = Wastage::generateAuditNumber();
            }

            if (!empty($data['audit_date'])) {
                $data['audit_date'] = function_exists('vue_to_server_date') ? vue_to_server_date($data['audit_date']) : $data['audit_date'];
            } else {
                $data['audit_date'] = date('Y-m-d');
            }

            $wastage_details = $data['wastage_details'] ?? [];
            unset($data['wastage_details']);

            $totalQty = 0;
            $totalLoss = 0;

            foreach ($wastage_details as $detail) {
                $qty = (float)($detail['quantity'] ?? 0);
                $price = (float)($detail['unit_price'] ?? 0);
                $totalQty += $qty;
                $totalLoss += ($qty * $price);
            }

            $data['total_qty'] = $totalQty;
            $data['total_loss_amount'] = $totalLoss;
            $data['status'] = 'pending';
            $data['created_by'] = Auth::guard('admin')->id();
            $data['created_ip'] = $request->ip();

            $wastage = Wastage::create($data);

            if (!empty($wastage_details)) {
                foreach ($wastage_details as $detail) {
                    $rowQty = (float)($detail['quantity'] ?? 0);
                    $rowPrice = (float)($detail['unit_price'] ?? 0);
                    $rowTotal = $rowQty * $rowPrice;

                    $expDate = null;
                    if (!empty($detail['expired_date'])) {
                        $expDate = function_exists('vue_to_server_date') ? vue_to_server_date($detail['expired_date']) : $detail['expired_date'];
                    }

                    $wastage->wastage_details()->create([
                        'category_id'  => $detail['category_id'] ?? null,
                        'item_id'      => $detail['item_id'],
                        'color_id'     => $detail['color_id'] ?? null,
                        'size_id'      => $detail['size_id'] ?? null,
                        'unit_id'      => $detail['unit_id'] ?? null,
                        'quantity'     => $rowQty,
                        'unit_price'   => $rowPrice,
                        'total_amount' => $rowTotal,
                        'reason'       => $detail['reason'] ?? null,
                        'expired_date' => $expDate,
                        'serial_no'    => $detail['serial_no'] ?? null,
                        'remarks'      => $detail['remarks'] ?? null,
                        'status'       => 'active',
                    ]);
                }
            }

            DB::commit();

            return $this->responseReturn("create", $wastage);
        } catch (\Throwable $ex) {
            DB::rollBack();

            return response()->json([
                'message' => 'Wastage creation failed',
                'error'   => $ex->getMessage()
            ], 422);
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

        $wastage = Wastage::with([
            'wastage_details.item:id,title,barcode,category_id,unit_id',
            'wastage_details.item.unit:id,title',
            'wastage_details.category:id,title',
            'wastage_details.color:id,title',
            'wastage_details.size:id,title',
            'wastage_details.unit:id,title',
            'auditor:id,full_name,mobile',
            'approved_admin:id,full_name,email',
            'creator:id,name',
            'branch:id,title',
        ])->find($id);

        if (!$wastage) {
            return response()->json(['message' => 'Wastage record not found'], 404);
        }

        return $wastage;
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
        $wastage = Wastage::find($id);
        if (!$wastage) {
            return response()->json(['message' => 'Wastage record not found'], 404);
        }

        if ($wastage->status === 'approved' || !empty($wastage->approved_by)) {
            return response()->json([
                'message' => 'This wastage audit is already approved. Editing is not allowed unless approval is cancelled.'
            ], 422);
        }

        $this->validateCheck($request, $wastage->id);

        DB::beginTransaction();

        try {
            $data = $request->all();

            if (!empty($data['audit_date'])) {
                $data['audit_date'] = function_exists('vue_to_server_date') ? vue_to_server_date($data['audit_date']) : $data['audit_date'];
            }

            $wastage_details = $data['wastage_details'] ?? [];
            unset($data['wastage_details']);

            $totalQty = 0;
            $totalLoss = 0;

            foreach ($wastage_details as $detail) {
                $qty = (float)($detail['quantity'] ?? 0);
                $price = (float)($detail['unit_price'] ?? 0);
                $totalQty += $qty;
                $totalLoss += ($qty * $price);
            }

            $data['total_qty'] = $totalQty;
            $data['total_loss_amount'] = $totalLoss;
            $data['updated_by'] = Auth::guard('admin')->id();
            $data['updated_ip'] = $request->ip();

            $wastage->update($data);

            // Replace details
            $wastage->wastage_details()->forceDelete();

            if (!empty($wastage_details)) {
                foreach ($wastage_details as $detail) {
                    $rowQty = (float)($detail['quantity'] ?? 0);
                    $rowPrice = (float)($detail['unit_price'] ?? 0);
                    $rowTotal = $rowQty * $rowPrice;

                    $expDate = null;
                    if (!empty($detail['expired_date'])) {
                        $expDate = function_exists('vue_to_server_date') ? vue_to_server_date($detail['expired_date']) : $detail['expired_date'];
                    }

                    $wastage->wastage_details()->create([
                        'category_id'  => $detail['category_id'] ?? null,
                        'item_id'      => $detail['item_id'],
                        'color_id'     => $detail['color_id'] ?? null,
                        'size_id'      => $detail['size_id'] ?? null,
                        'unit_id'      => $detail['unit_id'] ?? null,
                        'quantity'     => $rowQty,
                        'unit_price'   => $rowPrice,
                        'total_amount' => $rowTotal,
                        'reason'       => $detail['reason'] ?? null,
                        'expired_date' => $expDate,
                        'serial_no'    => $detail['serial_no'] ?? null,
                        'remarks'      => $detail['remarks'] ?? null,
                        'status'       => 'active',
                    ]);
                }
            }

            DB::commit();

            return $this->responseReturn("update", $wastage);
        } catch (\Throwable $ex) {
            DB::rollBack();

            return response()->json([
                'message' => 'Wastage update failed',
                'error'   => $ex->getMessage()
            ], 422);
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
        $wastage = Wastage::find($id);
        if (!$wastage) {
            return response()->json(['message' => 'Wastage not found'], 404);
        }

        if ($wastage->status === 'approved' || !empty($wastage->approved_by)) {
            return response()->json([
                'message' => 'This wastage is approved. Please cancel approval first before deleting.'
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Delete stock transactions if any
            StockTransaction::where('reference_type', 'Wastage')
                ->where('reference_id', (string)$wastage->id)
                ->delete();

            $wastage->wastage_details()->delete();
            $wastage->delete();

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
     * Approve Wastage Audit & deduct stock automatically.
     * Role-based permission: wastage.approved
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function approved(Request $request)
    {
        $wastageId = $request->wastage_id ?? $request->id;

        if (!$wastageId) {
            return $this->responseReturn('error', null, null, false, 'Wastage ID missing');
        }

        $wastage = Wastage::with('wastage_details')->find($wastageId);

        if (!$wastage) {
            return $this->responseReturn('error', null, null, false, 'Wastage record not found');
        }

        if ($wastage->approved_by || $wastage->status === 'approved') {
            return $this->responseReturn('warning', null, null, false, 'This wastage record is already approved');
        }

        if ($wastage->wastage_details->isEmpty()) {
            return response()->json([
                'message' => 'No items found in this wastage record to approve.'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $user = Auth::guard('admin')->user();

            $wastage->approved_by   = $user->id;
            $wastage->approved_date = now();
            $wastage->status        = 'approved';
            $wastage->save();

            // Clear any prior stock transaction for this reference to prevent duplicate deductions
            StockTransaction::where('reference_type', 'Wastage')
                ->where('reference_id', (string)$wastage->id)
                ->delete();

            // Deduct stock for each line item (Qty Out)
            $auditDate = $wastage->getRawOriginal('audit_date') ?? date('Y-m-d');

            foreach ($wastage->wastage_details as $detail) {
                if ($detail->quantity > 0) {
                    StockTransaction::create([
                        'item_id'          => $detail->item_id,
                        'color_id'         => $detail->color_id,
                        'size_id'          => $detail->size_id,
                        'transaction_date' => $auditDate,
                        'transaction_type' => 'Wastage',
                        'reference_type'   => 'Wastage',
                        'reference_id'     => (string)$wastage->id,
                        'qty_in'           => 0,
                        'qty_out'          => $detail->quantity,
                        'status'           => 'active',
                        'created_by'       => $user->id,
                        'created_ip'       => $request->ip(),
                    ]);
                }
            }

            DB::commit();

            return $this->responseReturn('success', null, null, false, 'Wastage approved and stock deducted successfully');
        } catch (\Throwable $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Wastage approval failed',
                'error'   => $ex->getMessage()
            ], 422);
        }
    }

    /**
     * Cancel Approval of Wastage Audit & restore stock.
     * Role-based permission: wastage.approvalcancel
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function approvalcancel(Request $request)
    {
        $wastageId = $request->wastage_id ?? $request->id;

        if (!$wastageId) {
            return $this->responseReturn('error', null, null, false, 'Wastage ID missing');
        }

        $wastage = Wastage::with('wastage_details')->find($wastageId);

        if (!$wastage) {
            return $this->responseReturn('error', null, null, false, 'Wastage record not found');
        }

        if (!$wastage->approved_by && $wastage->status !== 'approved') {
            return $this->responseReturn('warning', null, null, false, 'Wastage is not approved yet');
        }

        DB::beginTransaction();

        try {
            $wastage->approved_by   = null;
            $wastage->approved_date = null;
            $wastage->status        = 'pending';
            $wastage->save();

            // Restore Stock by removing the StockTransaction records
            StockTransaction::where('reference_type', 'Wastage')
                ->where('reference_id', (string)$wastage->id)
                ->delete();

            DB::commit();

            return $this->responseReturn('success', null, null, false, 'Wastage approval cancelled and stock restored successfully');
        } catch (\Throwable $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Cancelling wastage approval failed',
                'error'   => $ex->getMessage()
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
        return $request->validate([
            'audit_date' => 'required',
            'wastage_details' => 'required|array|min:1',
            'wastage_details.*.item_id' => 'required|integer|exists:items,id',
            'wastage_details.*.quantity' => 'required|numeric|min:0.01',
        ], [
            'audit_date.required' => 'Audit date is required',
            'wastage_details.required' => 'At least one wastage item is required',
            'wastage_details.*.item_id.required' => 'Item is required for all rows',
            'wastage_details.*.quantity.required' => 'Quantity is required for all rows',
            'wastage_details.*.quantity.min' => 'Quantity must be greater than 0',
        ]);
    }
}
