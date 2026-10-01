<?php

/**
 * @Quill Information Technology
 */

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Quotation;
use App\Models\QuotationDetail;
use App\Models\Client;
use App\Models\Item;
use App\Http\Resources\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Base\BaseController;

class QuotationController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Quotation::with([
            'client:id,org_name,name,mobile,email,address',
            'branch:id,title',
            'preparedBy:id,name,full_name'
        ])->latest('id');

        // Global search
        $query->whereLike($request->field_name, $request->value);

        // Specific filters
        if (!empty($request->quotation_no)) {
            $query->where('quotation_no', 'like', "%{$request->quotation_no}%");
        }

        if (!empty($request->client_id)) {
            $query->where('client_id', $request->client_id);
        }

        if (!empty($request->status)) {
            $query->where('status', $request->status);
        }

        if (!empty($request->from_date) && !empty($request->to_date)) {
            $query->whereDates('quotation_date', $request->from_date, $request->to_date);
        } elseif (!empty($request->from_date)) {
            $query->whereDate('quotation_date', '>=', $request->from_date);
        } elseif (!empty($request->to_date)) {
            $query->whereDate('quotation_date', '<=', $request->to_date);
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

            // Auto generate quotation number if blank
            if (empty($data['quotation_no'])) {
                $data['quotation_no'] = Quotation::generateQuotationNo();
            }

            // Dates conversion
            if (!empty($data['quotation_date'])) {
                $data['quotation_date'] = function_exists('vue_to_server_date') ? vue_to_server_date($data['quotation_date']) : $data['quotation_date'];
            } else {
                $data['quotation_date'] = date('Y-m-d');
            }

            if (!empty($data['validity_date'])) {
                $data['validity_date'] = function_exists('vue_to_server_date') ? vue_to_server_date($data['validity_date']) : $data['validity_date'];
            }

            // Sync client information if client_id is given
            if (!empty($data['client_id'])) {
                $client = Client::find($data['client_id']);
                if ($client) {
                    if (empty($data['client_name'])) {
                        $data['client_name'] = $client->org_name ?: $client->name;
                    }
                    if (empty($data['client_phone'])) {
                        $data['client_phone'] = $client->mobile ?: $client->phone;
                    }
                    if (empty($data['client_email'])) {
                        $data['client_email'] = $client->email;
                    }
                    if (empty($data['client_address'])) {
                        $data['client_address'] = $client->address;
                    }
                }
            }

            $details = $data['quotation_details'] ?? $data['details'] ?? [];
            if (is_string($details)) {
                $details = json_decode($details, true) ?? [];
            }
            unset($data['quotation_details'], $data['details']);

            if (empty($details) || !is_array($details)) {
                return response()->json([
                    'type' => 'error',
                    'message' => 'কমপক্ষে একটি আইটেম অথবা সার্ভিস কোটেশনে যোগ করুন!'
                ], 422);
            }

            // Calculate totals
            $subTotal = 0;
            $totalQty = 0;

            foreach ($details as $row) {
                $qty = isset($row['qty']) ? floatval($row['qty']) : 1;
                $unitPrice = isset($row['unit_price']) ? floatval($row['unit_price']) : 0;
                $discountAmount = isset($row['discount_amount']) ? floatval($row['discount_amount']) : 0;
                $lineTotal = ($qty * $unitPrice) - $discountAmount;

                $subTotal += max(0, $lineTotal);
                $totalQty += $qty;
            }

            $discountType = $data['discount_type'] ?? 'fixed';
            $discount = isset($data['discount']) ? floatval($data['discount']) : 0;
            if ($discountType === 'percentage') {
                $discountAmount = ($subTotal * $discount) / 100;
            } else {
                $discountAmount = $discount;
            }

            $taxPercent = isset($data['tax_percent']) ? floatval($data['tax_percent']) : 0;
            $afterDiscount = max(0, $subTotal - $discountAmount);
            $taxAmount = ($afterDiscount * $taxPercent) / 100;

            $shippingCost = isset($data['shipping_cost']) ? floatval($data['shipping_cost']) : 0;
            $totalAmount = round($afterDiscount + $taxAmount + $shippingCost, 2);

            $data['sub_total'] = round($subTotal, 2);
            $data['discount_type'] = $discountType;
            $data['discount'] = $discount;
            $data['discount_amount'] = round($discountAmount, 2);
            $data['tax_percent'] = $taxPercent;
            $data['tax_amount'] = round($taxAmount, 2);
            $data['shipping_cost'] = round($shippingCost, 2);
            $data['total_amount'] = $totalAmount;
            $data['total_items'] = count($details);
            $data['total_qty'] = $totalQty;
            $data['status'] = $data['status'] ?? 'draft';
            $data['created_by'] = Auth::id();

            $quotation = Quotation::create($data);

            // Insert Quotation Details
            foreach ($details as $index => $row) {
                $itemName = !empty($row['item_name']) ? trim($row['item_name']) : '';
                $itemId = !empty($row['item_id']) ? $row['item_id'] : null;

                if (empty($itemName) && $itemId) {
                    $itemObj = Item::find($itemId);
                    $itemName = $itemObj ? $itemObj->title : 'Item #' . $itemId;
                }

                if (empty($itemName)) continue;

                $qty = isset($row['qty']) ? floatval($row['qty']) : 1;
                $unitPrice = isset($row['unit_price']) ? floatval($row['unit_price']) : 0;
                $discPercent = isset($row['discount_percent']) ? floatval($row['discount_percent']) : 0;
                $discAmt = isset($row['discount_amount']) ? floatval($row['discount_amount']) : 0;
                if ($discPercent > 0 && $discAmt == 0) {
                    $discAmt = ($qty * $unitPrice * $discPercent) / 100;
                }
                $lineTotal = round(($qty * $unitPrice) - $discAmt, 2);

                QuotationDetail::create([
                    'quotation_id'     => $quotation->id,
                    'item_type'        => $row['item_type'] ?? ($itemId ? 'product' : 'custom'),
                    'category_id'      => $row['category_id'] ?? null,
                    'item_id'          => $itemId,
                    'brand_id'         => $row['brand_id'] ?? null,
                    'color_id'         => $row['color_id'] ?? null,
                    'size_id'          => $row['size_id'] ?? null,
                    'item_name'        => $itemName,
                    'description'      => $row['description'] ?? null,
                    'unit_id'          => $row['unit_id'] ?? null,
                    'unit_name'        => $row['unit_name'] ?? 'Pcs',
                    'qty'              => $qty,
                    'unit_price'       => $unitPrice,
                    'discount_percent' => $discPercent,
                    'discount_amount'  => $discAmt,
                    'total_price'      => $lineTotal,
                    'sorting'          => $index + 1,
                ]);
            }

            DB::commit();
            return $this->responseReturn('create', $quotation);
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

        $quotation = Quotation::with([
            'client:id,org_name,name,mobile,email,address',
            'branch:id,title,address',
            'currency:id,title,short_name,symbol',
            'preparedBy:id,name,full_name,phone',
            'createdBy:id,name,full_name',
            'quotation_details' => function ($q) {
                $q->with([
                    'item:id,title,barcode,model_no,category_id,brand_id,unit_id',
                    'item.unit:id,title',
                    'item.brand:id,title',
                    'category:id,title',
                    'brand:id,title',
                    'color:id,title',
                    'size:id,title',
                    'unit:id,title'
                ])->orderBy('sorting', 'asc')->orderBy('id', 'asc');
            }
        ])->find($id);

        if (!$quotation) {
            return response()->json(['message' => 'Quotation not found'], 404);
        }

        return $quotation;
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
        $this->validateCheck($request);

        DB::beginTransaction();

        try {
            $quotation = Quotation::findOrFail($id);
            $data = $request->all();

            // Dates conversion
            if (!empty($data['quotation_date'])) {
                $data['quotation_date'] = function_exists('vue_to_server_date') ? vue_to_server_date($data['quotation_date']) : $data['quotation_date'];
            }

            if (!empty($data['validity_date'])) {
                $data['validity_date'] = function_exists('vue_to_server_date') ? vue_to_server_date($data['validity_date']) : $data['validity_date'];
            }

            // Sync client information if client_id is given
            if (!empty($data['client_id'])) {
                $client = Client::find($data['client_id']);
                if ($client) {
                    if (empty($data['client_name'])) {
                        $data['client_name'] = $client->org_name ?: $client->name;
                    }
                    if (empty($data['client_phone'])) {
                        $data['client_phone'] = $client->mobile ?: $client->phone;
                    }
                    if (empty($data['client_email'])) {
                        $data['client_email'] = $client->email;
                    }
                    if (empty($data['client_address'])) {
                        $data['client_address'] = $client->address;
                    }
                }
            }

            $details = $data['quotation_details'] ?? $data['details'] ?? [];
            if (is_string($details)) {
                $details = json_decode($details, true) ?? [];
            }
            unset($data['quotation_details'], $data['details']);

            if (empty($details) || !is_array($details)) {
                return response()->json([
                    'type' => 'error',
                    'message' => 'কমপক্ষে একটি আইটেম অথবা সার্ভিস কোটেশনে যোগ করুন!'
                ], 422);
            }

            // Calculate totals
            $subTotal = 0;
            $totalQty = 0;

            foreach ($details as $row) {
                $qty = isset($row['qty']) ? floatval($row['qty']) : 1;
                $unitPrice = isset($row['unit_price']) ? floatval($row['unit_price']) : 0;
                $discountAmount = isset($row['discount_amount']) ? floatval($row['discount_amount']) : 0;
                $lineTotal = ($qty * $unitPrice) - $discountAmount;

                $subTotal += max(0, $lineTotal);
                $totalQty += $qty;
            }

            $discountType = $data['discount_type'] ?? ($quotation->discount_type ?? 'fixed');
            $discount = isset($data['discount']) ? floatval($data['discount']) : 0;
            if ($discountType === 'percentage') {
                $discountAmount = ($subTotal * $discount) / 100;
            } else {
                $discountAmount = $discount;
            }

            $taxPercent = isset($data['tax_percent']) ? floatval($data['tax_percent']) : 0;
            $afterDiscount = max(0, $subTotal - $discountAmount);
            $taxAmount = ($afterDiscount * $taxPercent) / 100;

            $shippingCost = isset($data['shipping_cost']) ? floatval($data['shipping_cost']) : 0;
            $totalAmount = round($afterDiscount + $taxAmount + $shippingCost, 2);

            $data['sub_total'] = round($subTotal, 2);
            $data['discount_type'] = $discountType;
            $data['discount'] = $discount;
            $data['discount_amount'] = round($discountAmount, 2);
            $data['tax_percent'] = $taxPercent;
            $data['tax_amount'] = round($taxAmount, 2);
            $data['shipping_cost'] = round($shippingCost, 2);
            $data['total_amount'] = $totalAmount;
            $data['total_items'] = count($details);
            $data['total_qty'] = $totalQty;
            $data['updated_by'] = Auth::id();

            $quotation->update($data);

            // Delete old details and recreate
            QuotationDetail::where('quotation_id', $quotation->id)->delete();

            foreach ($details as $index => $row) {
                $itemName = !empty($row['item_name']) ? trim($row['item_name']) : '';
                $itemId = !empty($row['item_id']) ? $row['item_id'] : null;

                if (empty($itemName) && $itemId) {
                    $itemObj = Item::find($itemId);
                    $itemName = $itemObj ? $itemObj->title : 'Item #' . $itemId;
                }

                if (empty($itemName)) continue;

                $qty = isset($row['qty']) ? floatval($row['qty']) : 1;
                $unitPrice = isset($row['unit_price']) ? floatval($row['unit_price']) : 0;
                $discPercent = isset($row['discount_percent']) ? floatval($row['discount_percent']) : 0;
                $discAmt = isset($row['discount_amount']) ? floatval($row['discount_amount']) : 0;
                if ($discPercent > 0 && $discAmt == 0) {
                    $discAmt = ($qty * $unitPrice * $discPercent) / 100;
                }
                $lineTotal = round(($qty * $unitPrice) - $discAmt, 2);

                QuotationDetail::create([
                    'quotation_id'     => $quotation->id,
                    'item_type'        => $row['item_type'] ?? ($itemId ? 'product' : 'custom'),
                    'category_id'      => $row['category_id'] ?? null,
                    'item_id'          => $itemId,
                    'brand_id'         => $row['brand_id'] ?? null,
                    'color_id'         => $row['color_id'] ?? null,
                    'size_id'          => $row['size_id'] ?? null,
                    'item_name'        => $itemName,
                    'description'      => $row['description'] ?? null,
                    'unit_id'          => $row['unit_id'] ?? null,
                    'unit_name'        => $row['unit_name'] ?? 'Pcs',
                    'qty'              => $qty,
                    'unit_price'       => $unitPrice,
                    'discount_percent' => $discPercent,
                    'discount_amount'  => $discAmt,
                    'total_price'      => $lineTotal,
                    'sorting'          => $index + 1,
                ]);
            }

            DB::commit();
            return $this->responseReturn('update', $quotation);
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json(['exception' => $ex->getMessage()], 422);
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
        try {
            $quotation = Quotation::findOrFail($id);
            QuotationDetail::where('quotation_id', $quotation->id)->delete();
            $quotation->delete();

            return $this->responseReturn('delete', $quotation);
        } catch (Exception $ex) {
            return response()->json(['exception' => $ex->getMessage()], 422);
        }
    }

    /**
     * Quick status update for quotation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus(Request $request, $id)
    {
        try {
            $quotation = Quotation::findOrFail($id);
            $status = $request->status;

            if (!in_array($status, ['draft', 'sent', 'accepted', 'declined', 'converted', 'expired'])) {
                return response()->json(['message' => 'Invalid status value.'], 422);
            }

            $quotation->update([
                'status'     => $status,
                'updated_by' => Auth::id()
            ]);

            return response()->json([
                'message' => "Quotation status successfully updated to {$status}.",
                'data'    => $quotation
            ]);
        } catch (Exception $ex) {
            return response()->json(['exception' => $ex->getMessage()], 422);
        }
    }

    /**
     * Validate form fields.
     */
    public function validateCheck($request, $id = null)
    {
        return $request->validate([
            'quotation_date' => 'required',
        ]);
    }
}
