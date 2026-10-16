<?php

/**
 * @Quill Information Technology
 */

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Challan;
use App\Models\ChallanDetail;
use App\Models\Invoice;
use App\Models\InvoiceDetails;
use App\Models\Item;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use App\Http\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Base\BaseController;
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
            'client:id,org_name,name,mobile,email',
            'invoice:id,invoice_no,invoice_date,amount,paid_amount,status',
            'challan_details:id,challan_id,item_id,qty,receive_qty'
        ])->withCount('challan_details')->latest();

        // Client filter
        if ($request->client_id) {
            $query->where('client_id', $request->client_id);
        }

        // Status filter
        if ($request->has('status') && $request->status !== '' && $request->status !== null) {
            $query->where('status', $request->status);
        }

        // Receive Status filter ('pending', 'partial', 'received')
        if ($request->has('receive_status') && !empty($request->receive_status)) {
            $query->where('receive_status', $request->receive_status);
        }

        // Invoiced filter (1 or 0)
        if ($request->has('is_invoiced') && $request->is_invoiced !== '' && $request->is_invoiced !== null) {
            $query->where('is_invoiced', $request->is_invoiced);
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

            $totalChallans = Challan::count();
            $activeChallans = Challan::where(function($q) {
                $q->where('status', 1)->orWhere('status', 'active');
            })->count();
            $thisMonthChallans = Challan::whereMonth('challan_date', now()->month)
                                        ->whereYear('challan_date', now()->year)
                                        ->count();
            $totalItemsDispatched = (int) ChallanDetail::sum('qty');

            return (new Resource($datas))->additional([
                'summary' => [
                    'total_challans' => $totalChallans,
                    'active_challans' => $activeChallans,
                    'this_month' => $thisMonthChallans,
                    'total_items' => $totalItemsDispatched,
                ]
            ]);
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

                    $data = $request->except('challan_details', 'workorder', 'invoice', 'client');
                    $data['challan_no'] = Challan::generateChallanNumber();
                    $data['challan_date'] = vue_to_server_date($data['challan_date'] ?? date('Y-m-d'));
                    $data['receive_status'] = 'pending';
                    $data['is_invoiced'] = 0;
                    $data['delivery_location'] = $data['delivery_location'] ?? '';
                    $data['receive_by'] = $data['receive_by'] ?? '';
                    $data['receive_by_designation'] = $data['receive_by_designation'] ?? '';
                    $data['receive_by_mobile'] = $data['receive_by_mobile'] ?? '';
                    $data['delivery_by'] = $data['delivery_by'] ?? '';
                    $data['delivery_by_mobile'] = $data['delivery_by_mobile'] ?? '';

                    // Master insert
                    $challan = Challan::create($data);

                    // Details insert
                    if ($challan && !empty($request->challan_details)) {
                        $details = is_string($request->challan_details) ? json_decode($request->challan_details, true) : $request->challan_details;

                        foreach ($details as $row) {
                            ChallanDetail::create([
                                'challan_id' => $challan->id,
                                'category_id' => !empty($row['category_id']) ? $row['category_id'] : null,
                                'item_id' => !empty($row['item_id']) ? $row['item_id'] : null,
                                'description' => $row['description'] ?? '',
                                'qty' => floatval($row['qty'] ?? 0),
                                'receive_qty' => floatval($row['receive_qty'] ?? 0),
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
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        if ($request->format() == 'html') {
            return view('layouts.backend_app');
        }
        $challan = Challan::with([
            'client:id,org_name,name,mobile,email,address,customer_type',
            'invoice:id,invoice_no,invoice_date,amount,paid_amount,status',
            'challan_details' => function($q) {
                $q->with([
                    'category:id,title',
                    'item:id,title,category_id,barcode,model_no,retail_price,wholesale_price,unit_id',
                    'item.category:id,title',
                    'item.unit:id,title'
                ])->orderBy('id', 'asc');
            }
        ])->find($id);

        if (!$challan) {
            return response()->json(['message' => 'Challan not found'], 404);
        }

        return $challan;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $challan = Challan::find($id);
        if ($challan && ($challan->is_invoiced || $challan->invoice_id)) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['message' => 'This challan has already been invoiced and cannot be edited.'], 422);
            }
        }
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
        $challan = Challan::findOrFail($id);

        // Lock check: Requirement 5: "kono chllan er invoice create hoye gele sei challan r edit kora jabe na ..."
        if ($challan->is_invoiced || $challan->invoice_id) {
            return response()->json([
                'message' => 'This challan has already been invoiced and is locked against modifications.'
            ], 422);
        }

        if ($this->validateCheck($request, $id)) {
            try {

                $res = DB::transaction(function () use ($request, $challan) {

                    // Master update
                    $data = $request->except('challan_details', 'workorder', 'invoice', 'client');
                    if (!empty($data['challan_date'])) {
                        $data['challan_date'] = vue_to_server_date($data['challan_date']);
                    }
                    $challan->update($data);

                    $existingIds = ChallanDetail::where('challan_id', $challan->id)
                        ->pluck('id')
                        ->toArray();

                    $requestIds = [];

                    $details = $request->challan_details;
                    if (is_string($details)) {
                        $details = json_decode($details, true);
                    }

                    if (!empty($details)) {
                        foreach ($details as $row) {

                            // existing row update
                            if (array_key_exists('id', $row) && !empty($row['id'])) {

                                $requestIds[] = $row['id'];
                                $detail = ChallanDetail::find($row['id']);

                                if ($detail) {
                                    $detail->update([
                                        'category_id' => !empty($row['category_id']) ? $row['category_id'] : null,
                                        'item_id'     => !empty($row['item_id']) ? $row['item_id'] : null,
                                        'description' => $row['description'] ?? '',
                                        'qty'         => floatval($row['qty'] ?? 0),
                                        'receive_qty' => floatval($row['receive_qty'] ?? 0),
                                        'remark'      => $row['remark'] ?? '',
                                    ]);
                                }
                            }
                            // new row insert
                            else {

                                $new = ChallanDetail::create([
                                    'challan_id'  => $challan->id,
                                    'category_id' => !empty($row['category_id']) ? $row['category_id'] : null,
                                    'item_id'     => !empty($row['item_id']) ? $row['item_id'] : null,
                                    'description' => $row['description'] ?? '',
                                    'qty'         => floatval($row['qty'] ?? 0),
                                    'receive_qty' => floatval($row['receive_qty'] ?? 0),
                                    'remark'      => $row['remark'] ?? '',
                                ]);

                                $requestIds[] = $new->id;
                            }
                        }
                    }

                    // removed row delete
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $challan = Challan::with('challan_details')->findOrFail($id);

        if ($challan->is_invoiced || $challan->invoice_id) {
            return response()->json([
                'message' => 'This challan has already been invoiced and cannot be deleted.'
            ], 422);
        }

        DB::transaction(function () use ($challan) {
            foreach ($challan->challan_details as $detail) {
                $detail->delete();
            }
            $challan->delete();
        });

        return $this->responseReturn("delete", true);
    }

    /**
     * Accept Challan (record full or partial item receipts)
     * Requirement 3: "challan er view page teke challan accepte er ekta button takbe. jekhan teke partial item receive dekhano jabe"
     */
    public function accept(Request $request, $id)
    {
        $challan = Challan::with('challan_details')->findOrFail($id);

        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.receive_qty' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($request, $challan) {
                foreach ($request->items as $item) {
                    $detail = ChallanDetail::where('challan_id', $challan->id)
                        ->where('id', $item['id'])
                        ->first();

                    if ($detail) {
                        $detail->receive_qty = floatval($item['receive_qty']);
                        if (isset($item['remark'])) {
                            $detail->remark = $item['remark'];
                        }
                        $detail->save();
                    }
                }

                if ($request->filled('receive_by')) {
                    $challan->receive_by = $request->receive_by;
                }
                if ($request->filled('receive_by_designation')) {
                    $challan->receive_by_designation = $request->receive_by_designation;
                }
                if ($request->filled('receive_by_mobile')) {
                    $challan->receive_by_mobile = $request->receive_by_mobile;
                }

                // Calculate receive status
                $allDetails = ChallanDetail::where('challan_id', $challan->id)->get();
                $totalQty = $allDetails->sum('qty');
                $totalReceived = $allDetails->sum('receive_qty');

                if ($totalReceived <= 0) {
                    $status = 'pending';
                } elseif ($totalReceived >= $totalQty) {
                    $status = 'received';
                } else {
                    $status = 'partial';
                }

                $challan->receive_status = $status;
                $challan->accepted_at = now();
                $challan->accepted_by = Auth::id() ?? auth('admin')->id();
                $challan->save();
            });

            return response()->json([
                'success' => true,
                'message' => 'Challan accepted successfully.',
                'challan' => Challan::with([
                    'client:id,org_name,name,mobile,email,address',
                    'invoice:id,invoice_no,invoice_date,amount,paid_amount,status',
                    'challan_details.item.unit'
                ])->find($challan->id)
            ]);
        } catch (Exception $ex) {
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage()
            ], 422);
        }
    }

    /**
     * Create Invoice from Accepted Challan Items
     * Requirement 4: "challan accept korar por ei accepted item gulo invoice korar system korte hobe"
     */
    public function createInvoice(Request $request, $id)
    {
        $challan = Challan::with(['client', 'challan_details.item'])->findOrFail($id);

        if ($challan->is_invoiced || $challan->invoice_id) {
            return response()->json([
                'message' => 'An invoice has already been generated for this challan.'
            ], 422);
        }

        if (empty($challan->client_id)) {
            return response()->json([
                'message' => 'Please assign a client to this challan before generating an invoice.'
            ], 422);
        }

        $acceptedDetails = $challan->challan_details->filter(function ($d) {
            return floatval($d->receive_qty) > 0;
        });

        if ($acceptedDetails->isEmpty()) {
            return response()->json([
                'message' => 'No accepted items found to invoice. Please accept items first.'
            ], 422);
        }

        try {
            $invoice = DB::transaction(function () use ($request, $challan, $acceptedDetails) {
                $calculatedOriginalAmount = 0;
                $lines = [];

                foreach ($acceptedDetails as $detail) {
                    $reqItem = collect($request->items)->firstWhere('id', $detail->id);
                    $qty = floatval($reqItem['qty'] ?? $detail->receive_qty);
                    $rate = floatval($reqItem['rate'] ?? ($detail->item ? ($detail->item->retail_price ?? $detail->item->wholesale_price ?? 0) : 0));
                    $lineTotal = $qty * $rate;
                    $calculatedOriginalAmount += $lineTotal;

                    $lines[] = [
                        'detail' => $detail,
                        'qty' => $qty,
                        'rate' => $rate,
                        'total' => $lineTotal,
                    ];
                }

                $discount = floatval($request->discount ?? 0);
                $vat = floatval($request->vat ?? 0);
                $totalAmount = max(0, $calculatedOriginalAmount - $discount + $vat);

                $invoiceDate = !empty($request->invoice_date)
                    ? vue_to_server_date($request->invoice_date)
                    : date('Y-m-d');

                $invoiceData = [
                    'invoice_no' => Invoice::generateInvoiceNumber(),
                    'client_id' => $challan->client_id,
                    'challan_id' => $challan->id,
                    'invoice_date' => $invoiceDate,
                    'original_amount' => $calculatedOriginalAmount,
                    'discount' => $discount,
                    'vat' => $vat,
                    'amount' => $totalAmount,
                    'paid_amount' => 0,
                    'is_previous_due' => 0,
                    'is_closed' => 0,
                    'status' => 'active',
                    'created_by' => Auth::id() ?? auth('admin')->id(),
                ];

                if (Schema::hasColumn('invoices', 'terms_conditions') && !empty($request->terms_conditions)) {
                    $invoiceData['terms_conditions'] = $request->terms_conditions;
                }

                $invoice = Invoice::create($invoiceData);

                // Create invoice details
                foreach ($lines as $line) {
                    $det = $line['detail'];
                    $desc = $det->item ? $det->item->title : ($det->description ?: 'Challan Item');
                    if (!empty($det->description) && $det->item && $det->item->title !== $det->description) {
                        $desc .= ' - ' . $det->description;
                    }

                    $detailData = [
                        'invoice_id' => (string) $invoice->id,
                        'reference' => 'Challan #' . ($challan->challan_no ?: $challan->id),
                        'challan_detail_id' => $det->id,
                        'item_id' => $det->item_id ?? null,
                        'workorder_id' => null,
                        'account_id' => 0,
                        'description' => $desc,
                        'qty' => $line['qty'],
                        'amount' => $line['rate'],
                        'total_amount' => $line['total'],
                        'status' => 'active',
                    ];

                    InvoiceDetails::create($detailData);

                    // If item is from inventory and StockTransaction exists, record stock out
                    if (!empty($det->item_id)) {
                        StockTransaction::create([
                            'item_id' => $det->item_id,
                            'transaction_date' => $invoiceDate,
                            'transaction_type' => 'Issue',
                            'reference_type' => 'Invoice',
                            'reference_id' => (string) $invoice->id,
                            'qty_in' => 0,
                            'qty_out' => $line['qty'],
                            'status' => 'active',
                        ]);
                    }
                }

                // Lock the challan and link invoice
                $challan->update([
                    'invoice_id' => $invoice->id,
                    'is_invoiced' => 1,
                ]);

                return $invoice;
            });

            return response()->json([
                'success' => true,
                'message' => 'Invoice generated successfully.',
                'invoice_id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'challan' => Challan::with([
                    'client:id,org_name,name,mobile,email,address,customer_type',
                    'invoice:id,invoice_no,invoice_date,amount,paid_amount,status',
                    'challan_details.item.unit'
                ])->find($challan->id),
            ]);
        } catch (Exception $ex) {
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage()
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
            'client_id' => 'required',
            'challan_date' => 'required',
        ], [
            'client_id.required' => 'Client is required',
            'challan_date.required' => 'Challan Date is required',
        ]);
    }
}
