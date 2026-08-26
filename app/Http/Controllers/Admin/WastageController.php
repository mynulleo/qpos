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
        $query = Wastage::with(['auditor:id,full_name'])->latest('id');
        $query->whereLike($request->field_name, $request->value);
        $query->whereAny('status', $request->status);
        $query->whereLike('audit_number', $request->audit_number);
        $query->whereAny('auditor_id', $request->auditor_id);

        if (!empty($request->from_date) && !empty($request->to_date)) {
            $query->whereDates('audit_date', $request->from_date, $request->to_date);
        }

        if (!empty($request->item_id)) {
            $query->whereSub('wastage_details', 'item_id', $request->item_id);
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

            // Validate that no serials are currently sold in active invoices
            foreach ($wastage_details as $detail) {
                if (!empty($detail['serial_no'])) {
                    $sList = preg_split('/[\r\n,]+/', $detail['serial_no'], -1, PREG_SPLIT_NO_EMPTY);
                    foreach ($sList as $s) {
                        $sn = trim($s);
                        if (empty($sn)) continue;

                        $isSold = \App\Models\InvoiceDetails::where('item_id', $detail['item_id'])
                            ->whereNotNull('serial_no')
                            ->where('serial_no', '!=', '')
                            ->where('status', 'active')
                            ->get()
                            ->contains(function ($invd) use ($sn) {
                                $serials = preg_split('/[\r\n,]+/', $invd->serial_no, -1, PREG_SPLIT_NO_EMPTY);
                                foreach ($serials as $soldSn) {
                                    if (strcasecmp(trim($soldSn), $sn) === 0) return true;
                                }
                                return false;
                            });

                        if ($isSold) {
                            return response()->json([
                                'message' => "Serial '{$sn}' is currently sold in an active invoice and has not been returned. It cannot be added to wastage."
                            ], 422);
                        }
                    }
                }
            }

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
            'wastage_details.item',
            'wastage_details.item.unit',
            'wastage_details.category',
            'wastage_details.color',
            'wastage_details.size',
            'wastage_details.unit',
            'auditor',
            'approved_admin',
            'creator',
            'branch',
        ])->find($id);

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

            // Validate that no serials are currently sold in active invoices
            foreach ($wastage_details as $detail) {
                if (!empty($detail['serial_no'])) {
                    $sList = preg_split('/[\r\n,]+/', $detail['serial_no'], -1, PREG_SPLIT_NO_EMPTY);
                    foreach ($sList as $s) {
                        $sn = trim($s);
                        if (empty($sn)) continue;

                        $isSold = \App\Models\InvoiceDetails::where('item_id', $detail['item_id'])
                            ->whereNotNull('serial_no')
                            ->where('serial_no', '!=', '')
                            ->where('status', 'active')
                            ->get()
                            ->contains(function ($invd) use ($sn) {
                                $serials = preg_split('/[\r\n,]+/', $invd->serial_no, -1, PREG_SPLIT_NO_EMPTY);
                                foreach ($serials as $soldSn) {
                                    if (strcasecmp(trim($soldSn), $sn) === 0) return true;
                                }
                                return false;
                            });

                        if ($isSold) {
                            return response()->json([
                                'message' => "Serial '{$sn}' is currently sold in an active invoice and has not been returned. It cannot be added to wastage."
                            ], 422);
                        }
                    }
                }
            }

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
     * Check if a serial number is valid for wastage.
     * Must be purchased, and NOT currently sold (invoice_details active).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkSerial(Request $request)
    {
        $itemId = $request->input('item_id');
        $serialNo = trim($request->input('serial_no'));

        if (empty($itemId) || empty($serialNo)) {
            return response()->json(['valid' => false, 'message' => 'Item ID and Serial number are required.'], 422);
        }

        // 1. Check if serial number exists in Purchase records for this item
        $purchaseExists = \App\Models\PurchaseDetail::where('item_id', $itemId)
            ->whereNotNull('serial_no')
            ->where('serial_no', '!=', '')
            ->get()
            ->contains(function ($pd) use ($serialNo) {
                $serials = preg_split('/[\r\n,]+/', $pd->serial_no, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($serials as $s) {
                    if (strcasecmp(trim($s), $serialNo) === 0) return true;
                }
                return false;
            });

        if (!$purchaseExists) {
            return response()->json([
                'valid' => false,
                'status' => 'not_purchased',
                'message' => "Serial '{$serialNo}' was not found in purchase records for this item!"
            ]);
        }

        // 2. Check if serial number is currently SOLD in Invoice Details (active sales)
        // If status == 'active', it's sold and NOT returned.
        // If the item was returned / deactivated, status is not 'active'.
        $soldDetail = \App\Models\InvoiceDetails::where('item_id', $itemId)
            ->whereNotNull('serial_no')
            ->where('serial_no', '!=', '')
            ->where('status', 'active')
            ->get()
            ->first(function ($invd) use ($serialNo) {
                $serials = preg_split('/[\r\n,]+/', $invd->serial_no, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($serials as $s) {
                    if (strcasecmp(trim($s), $serialNo) === 0) return true;
                }
                return false;
            });

        if ($soldDetail) {
            return response()->json([
                'valid' => false,
                'status' => 'sold',
                'message' => "Serial '{$serialNo}' is currently SOLD in Invoice #{$soldDetail->invoice_id} (not returned)! Sold items cannot be added to wastage."
            ]);
        }

        // 3. Check if serial is already in another wastage record
        $currentWastageId = $request->input('wastage_id');
        $wastedQuery = \App\Models\WastageDetail::where('item_id', $itemId)
            ->whereNotNull('serial_no')
            ->where('serial_no', '!=', '');

        if ($currentWastageId) {
            $wastedQuery->where('wastage_id', '!=', $currentWastageId);
        }

        $wastedDetail = $wastedQuery->get()->first(function ($wd) use ($serialNo) {
            $serials = preg_split('/[\r\n,]+/', $wd->serial_no, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($serials as $s) {
                if (strcasecmp(trim($s), $serialNo) === 0) return true;
            }
            return false;
        });

        if ($wastedDetail) {
            return response()->json([
                'valid' => false,
                'status' => 'wasted',
                'message' => "Serial '{$serialNo}' is already recorded in Wastage Audit #{$wastedDetail->wastage_id}!"
            ]);
        }

        return response()->json([
            'valid' => true,
            'message' => "Serial '{$serialNo}' is valid and available in inventory."
        ]);
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
