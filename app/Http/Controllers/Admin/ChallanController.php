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
use App\Models\Payment;
use App\Models\PaymentDetail;
use App\Models\Account;
use App\Models\System\SiteSetting;
use App\Traits\VoucherTrait;
use Illuminate\Http\Request;
use App\Http\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Base\BaseController;
use Storage;

class ChallanController extends BaseController
{
    use VoucherTrait;
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

                    $details = $request->challan_details;
                    if (is_string($details)) {
                        $details = json_decode($details, true) ?: [];
                    }

                    // 1. Stock Pre-dispatch Verification
                    // Check warehouse inventory before issuing challan
                    if (!empty($details)) {
                        foreach ($details as $row) {
                            $itemId = !empty($row['item_id']) ? intval($row['item_id']) : null;
                            $qty = floatval($row['qty'] ?? 0);
                            if ($itemId && $qty > 0) {
                                $stockIn = StockTransaction::where('item_id', $itemId)
                                    ->where('status', 'active')
                                    ->sum('qty_in');
                                $stockOut = StockTransaction::where('item_id', $itemId)
                                    ->where('status', 'active')
                                    ->sum('qty_out');
                                $availableStock = floatval($stockIn - $stockOut);

                                if ($qty > $availableStock) {
                                    $itemObj = Item::find($itemId);
                                    $title = $itemObj ? $itemObj->title : "Item #{$itemId}";
                                    $availDisp = $availableStock > 0 ? $availableStock : 0;
                                    $deficit = $qty - $availableStock;
                                    throw new Exception("স্টকে পর্যাপ্ত পণ্য নেই! '{$title}' পণ্যটির বর্তমান স্টক {$availDisp}, কিন্তু চালানে প্রেরণের জন্য চাওয়া হয়েছে {$qty} (ঘাটতি: {$deficit})। পর্যাপ্ত স্টক না থাকলে চালান ইস্যু করা যাবে না।");
                                }
                            }
                        }
                    }

                    // 2. Master insert
                    $challan = Challan::create($data);

                    // 3. Details insert & Immediate Stock Issue Transaction
                    if ($challan && !empty($details)) {
                        foreach ($details as $row) {
                            $colorId = !empty($row['color_id']) ? intval($row['color_id']) : null;
                            $sizeId = !empty($row['size_id']) ? intval($row['size_id']) : null;
                            $serialNo = !empty($row['serial_no']) ? trim($row['serial_no']) : null;

                            ChallanDetail::create([
                                'challan_id' => $challan->id,
                                'category_id' => !empty($row['category_id']) ? $row['category_id'] : null,
                                'item_id' => !empty($row['item_id']) ? $row['item_id'] : null,
                                'color_id' => $colorId,
                                'size_id' => $sizeId,
                                'serial_no' => $serialNo,
                                'description' => $row['description'] ?? '',
                                'qty' => floatval($row['qty'] ?? 0),
                                'receive_qty' => floatval($row['receive_qty'] ?? 0),
                                'remark' => $row['remark'] ?? '',
                            ]);

                            // Immediately record stock out on dispatch so physical POS/sales cannot double-sell
                            if (!empty($row['item_id']) && floatval($row['qty'] ?? 0) > 0) {
                                StockTransaction::create([
                                    'item_id' => $row['item_id'],
                                    'color_id' => $colorId,
                                    'size_id' => $sizeId,
                                    'transaction_date' => $challan->getRawOriginal('challan_date') ?: date('Y-m-d'),
                                    'transaction_type' => 'Challan',
                                    'reference_type' => 'Challan',
                                    'reference_id' => (string) $challan->id,
                                    'qty_in' => 0,
                                    'qty_out' => floatval($row['qty']),
                                    'status' => 'active',
                                    'created_by' => Auth::id() ?? auth('admin')->id() ?? auth()->id() ?? 1,
                                ]);
                            }
                        }
                    }

                    return $challan;
                });

                return $this->responseReturn("create", $res);
            } catch (Exception $ex) {
                return response()->json([
                    'message' => $ex->getMessage(),
                    'exception' => $ex->errorInfo ?? $ex->getMessage()
                ], 422);
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
                    'color:id,title',
                    'size:id,title',
                    'item:id,title,category_id,barcode,model_no,retail_price,wholesale_price,unit_id',
                    'item.category:id,title',
                    'item.unit:id,title'
                ])->orderBy('id', 'asc');
            }
        ])->find($id);

        if (!$challan) {
            return response()->json(['message' => 'Challan not found'], 404);
        }

        if ($challan->challan_details) {
            $itemIds = $challan->challan_details->pluck('item_id')->filter()->unique();
            if ($itemIds->isNotEmpty()) {
                $stockInMap = StockTransaction::whereIn('item_id', $itemIds)
                    ->where('status', 'active')
                    ->groupBy('item_id')
                    ->selectRaw('item_id, SUM(qty_in) as total_in')
                    ->pluck('total_in', 'item_id');

                $stockOutMap = StockTransaction::whereIn('item_id', $itemIds)
                    ->where('status', 'active')
                    ->groupBy('item_id')
                    ->selectRaw('item_id, SUM(qty_out) as total_out')
                    ->pluck('total_out', 'item_id');

                foreach ($challan->challan_details as $detail) {
                    if (!empty($detail->item_id)) {
                        $in = floatval($stockInMap[$detail->item_id] ?? 0);
                        $out = floatval($stockOutMap[$detail->item_id] ?? 0);
                        $currStock = $in - $out;
                        $detail->current_stock = $currStock;
                        if ($detail->item) {
                            $detail->item->current_stock = $currStock;
                        }
                    } else {
                        $detail->current_stock = 999999;
                    }
                }
            }
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

                    // Temporarily release previous Challan stock reservation to verify new stock accurately
                    StockTransaction::whereIn('reference_type', ['Challan', 'Challan Return'])
                        ->where('reference_id', (string) $challan->id)
                        ->delete();

                    $details = $request->challan_details;
                    if (is_string($details)) {
                        $details = json_decode($details, true) ?: [];
                    }

                    // Stock verification for updated quantities
                    if (!empty($details)) {
                        foreach ($details as $row) {
                            $itemId = !empty($row['item_id']) ? intval($row['item_id']) : null;
                            $qty = floatval($row['qty'] ?? 0);
                            if ($itemId && $qty > 0) {
                                $stockIn = StockTransaction::where('item_id', $itemId)
                                    ->where('status', 'active')
                                    ->sum('qty_in');
                                $stockOut = StockTransaction::where('item_id', $itemId)
                                    ->where('status', 'active')
                                    ->sum('qty_out');
                                $availableStock = floatval($stockIn - $stockOut);

                                if ($qty > $availableStock) {
                                    $itemObj = Item::find($itemId);
                                    $title = $itemObj ? $itemObj->title : "Item #{$itemId}";
                                    $availDisp = $availableStock > 0 ? $availableStock : 0;
                                    $deficit = $qty - $availableStock;
                                    throw new Exception("স্টকে পর্যাপ্ত পণ্য নেই! '{$title}' পণ্যটির বর্তমান স্টক {$availDisp}, কিন্তু চালানে প্রেরণের জন্য চাওয়া হয়েছে {$qty} (ঘাটতি: {$deficit})। পর্যাপ্ত স্টক না থাকলে চালান আপডেট করা যাবে না।");
                                }
                            }
                        }
                    }

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
                                        'color_id'    => !empty($row['color_id']) ? intval($row['color_id']) : null,
                                        'size_id'     => !empty($row['size_id']) ? intval($row['size_id']) : null,
                                        'serial_no'   => !empty($row['serial_no']) ? trim($row['serial_no']) : null,
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
                                    'color_id'    => !empty($row['color_id']) ? intval($row['color_id']) : null,
                                    'size_id'     => !empty($row['size_id']) ? intval($row['size_id']) : null,
                                    'serial_no'   => !empty($row['serial_no']) ? trim($row['serial_no']) : null,
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

                    // Re-record StockTransaction for current details
                    $savedDetails = ChallanDetail::where('challan_id', $challan->id)->get();
                    foreach ($savedDetails as $d) {
                        if (!empty($d->item_id) && floatval($d->qty) > 0) {
                            StockTransaction::create([
                                'item_id' => $d->item_id,
                                'color_id' => $d->color_id ?? null,
                                'size_id' => $d->size_id ?? null,
                                'transaction_date' => $challan->getRawOriginal('challan_date') ?: date('Y-m-d'),
                                'transaction_type' => 'Challan',
                                'reference_type' => 'Challan',
                                'reference_id' => (string) $challan->id,
                                'qty_in' => 0,
                                'qty_out' => floatval($d->qty),
                                'status' => 'active',
                                'created_by' => Auth::id() ?? auth('admin')->id() ?? auth()->id() ?? 1,
                            ]);

                            // If already accepted/returned, record stock return for unreceived difference
                            $returnQty = max(0, floatval($d->qty) - floatval($d->receive_qty));
                            if ($challan->receive_status !== 'pending' && $returnQty > 0) {
                                StockTransaction::create([
                                    'item_id' => $d->item_id,
                                    'color_id' => $d->color_id ?? null,
                                    'size_id' => $d->size_id ?? null,
                                    'transaction_date' => date('Y-m-d'),
                                    'transaction_type' => 'Challan Return',
                                    'reference_type' => 'Challan Return',
                                    'reference_id' => (string) $challan->id,
                                    'qty_in' => $returnQty,
                                    'qty_out' => 0,
                                    'status' => 'active',
                                    'created_by' => Auth::id() ?? auth('admin')->id() ?? auth()->id() ?? 1,
                                ]);
                            }
                        }
                    }

                    return $challan;
                });

                return $this->responseReturn("update", $res);
            } catch (Exception $ex) {
                return response()->json([
                    'message' => $ex->getMessage(),
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
            // Restore inventory by removing all stock transactions for this challan
            StockTransaction::whereIn('reference_type', ['Challan', 'Challan Return'])
                ->where('reference_id', (string) $challan->id)
                ->delete();

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

                // Clean up previous Challan Return transactions for this challan
                StockTransaction::where('reference_type', 'Challan Return')
                    ->where('reference_id', (string) $challan->id)
                    ->delete();

                // Ensure base Challan dispatch StockTransaction exists
                $hasChallanTx = StockTransaction::where('reference_type', 'Challan')
                    ->where('reference_id', (string) $challan->id)
                    ->exists();

                if (!$hasChallanTx) {
                    foreach ($allDetails as $detail) {
                        if (!empty($detail->item_id) && floatval($detail->qty) > 0) {
                            StockTransaction::create([
                                'item_id' => $detail->item_id,
                                'color_id' => $detail->color_id ?? null,
                                'size_id' => $detail->size_id ?? null,
                                'transaction_date' => $challan->getRawOriginal('challan_date') ?: date('Y-m-d'),
                                'transaction_type' => 'Challan',
                                'reference_type' => 'Challan',
                                'reference_id' => (string) $challan->id,
                                'qty_in' => 0,
                                'qty_out' => floatval($detail->qty),
                                'status' => 'active',
                                'created_by' => Auth::id() ?? auth('admin')->id() ?? auth()->id() ?? 1,
                            ]);
                        }
                    }
                }

                // Automatic Restock on Return / Partial Delivery
                // Any unreceived quantity (qty - receive_qty) is physically returned to stock
                foreach ($allDetails as $detail) {
                    if (!empty($detail->item_id)) {
                        $dispatchedQty = floatval($detail->qty);
                        $receivedQty = floatval($detail->receive_qty);
                        $returnQty = max(0, $dispatchedQty - $receivedQty);

                        if ($returnQty > 0) {
                            StockTransaction::create([
                                'item_id' => $detail->item_id,
                                'color_id' => $detail->color_id ?? null,
                                'size_id' => $detail->size_id ?? null,
                                'transaction_date' => date('Y-m-d'),
                                'transaction_type' => 'Challan Return',
                                'reference_type' => 'Challan Return',
                                'reference_id' => (string) $challan->id,
                                'qty_in' => $returnQty,
                                'qty_out' => 0,
                                'status' => 'active',
                                'created_by' => Auth::id() ?? auth('admin')->id() ?? auth()->id() ?? 1,
                            ]);
                        }
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Challan accepted successfully. Returned/unreceived items (if any) have been automatically credited back to stock.',
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

        // Requirement 1: Challan accept na hole invoice generate kora jabe na
        if (empty($challan->accepted_at) || $challan->receive_status === 'pending') {
            return response()->json([
                'message' => 'Challan must be accepted before generating an invoice. (চালান গ্রহণ না করা পর্যন্ত ইনভয়েস তৈরি করা যাবে না)'
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

        // NOTE: In standard ERP supply chain architecture, goods are physically deducted from warehouse stock
        // upon Challan dispatch. Therefore, generating the invoice does NOT deduct stock again,
        // and is never blocked by current warehouse stock availability.

        try {
            $result = DB::transaction(function () use ($request, $challan, $acceptedDetails) {
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

                // Requirement 2: Terms & Conditions handling
                $siteSetting = SiteSetting::first();
                $isTermsEnabled = boolval($siteSetting->show_invoice_terms ?? $siteSetting->show_pos_terms ?? 1);

                $termsConditions = [];
                if ($isTermsEnabled && !empty($request->terms_conditions)) {
                    if (is_array($request->terms_conditions)) {
                        $termsConditions = array_values(array_filter(array_map('trim', $request->terms_conditions)));
                    } elseif (is_string($request->terms_conditions)) {
                        $decoded = json_decode($request->terms_conditions, true);
                        $termsConditions = is_array($decoded) ? $decoded : [$request->terms_conditions];
                    }
                }

                // Requirement 3: Payment Switch handling (Default OFF, when ON records paid in Payment module)
                $isPaid = $request->boolean('is_paid')
                    || $request->input('is_paid') == 1
                    || $request->input('is_paid') === '1'
                    || $request->input('is_paid') === true
                    || $request->input('is_paid') === 'true';

                $paidAmount = $isPaid ? floatval($request->paid_amount ?: $totalAmount) : 0;
                $paymentMethod = $isPaid ? ($request->payment_method ?: 'Cash') : null;
                $isClosed = ($isPaid && $paidAmount >= $totalAmount) ? 1 : 0;

                $invoiceData = [
                    'invoice_no' => Invoice::generateInvoiceNumber(),
                    'client_id' => $challan->client_id,
                    'challan_id' => $challan->id,
                    'invoice_date' => $invoiceDate,
                    'original_amount' => $calculatedOriginalAmount,
                    'discount' => $discount,
                    'vat' => $vat,
                    'amount' => $totalAmount,
                    'paid_amount' => $paidAmount,
                    'is_previous_due' => 0,
                    'is_closed' => $isClosed,
                    'payment_method' => $paymentMethod,
                    'status' => 'active',
                    'terms_conditions' => $termsConditions,
                    'created_by' => Auth::id() ?? auth('admin')->id() ?? auth()->id() ?? 1,
                ];

                $invoice = Invoice::create($invoiceData);

                // Create invoice details (Stock already deducted at Challan dispatch - no duplicate stock out)
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
                        'color_id' => $det->color_id ?? null,
                        'size_id' => $det->size_id ?? null,
                        'serial_no' => $det->serial_no ?? null,
                        'workorder_id' => null,
                        'account_id' => 0,
                        'description' => $desc,
                        'qty' => $line['qty'],
                        'amount' => $line['rate'],
                        'total_amount' => $line['total'],
                        'status' => 'active',
                    ];

                    InvoiceDetails::create($detailData);
                }

                // Lock the challan and link invoice
                $challan->update([
                    'invoice_id' => $invoice->id,
                    'is_invoiced' => 1,
                ]);

                // Create Payment record if Paid
                $payment = null;
                if ($isPaid && $totalAmount > 0 && $paidAmount > 0) {
                    $fundAccountId = $request->fund_account_id ?: null;
                    if (!$fundAccountId) {
                        $cashAccount = Account::where('system_key_name', 'Cash')
                            ->where('account_type', 'Asset')
                            ->first()
                            ?? Account::where('system_key_name', 'Cash')->first();
                        $fundAccountId = $cashAccount ? $cashAccount->id : 1;
                    }

                    $receivableAccount = Account::where('system_key_name', 'accounts-receivable')->first();
                    $receivableAccountId = $receivableAccount ? $receivableAccount->id : 26;

                    $payslipNo = Payment::getPaySlipNo();

                    $payment = Payment::create([
                        'payslipno' => $payslipNo,
                        'payment_type' => 'Receive',
                        'client_id' => $challan->client_id,
                        'payment_date' => $invoiceDate,
                        'discount' => 0,
                        'amount' => $paidAmount,
                        'fund_account_id' => $fundAccountId,
                        'payment_method' => $paymentMethod,
                        'mbanking_type' => $request->mbanking_type ?? null,
                        'trxid' => $request->trxid ?? null,
                        'chequeno' => $request->chequeno ?? null,
                        'bank_id' => $request->bank_id ?? null,
                        'status' => 'active',
                        'created_by' => Auth::id() ?? auth('admin')->id() ?? auth()->id() ?? 1,
                    ]);

                    PaymentDetail::create([
                        'payment_id' => $payment->id,
                        'reference_type' => 'Invoice',
                        'reference_id' => $invoice->id,
                        'account_id' => $receivableAccountId,
                        'amount' => $paidAmount,
                        'is_closed' => $isClosed,
                        'status' => 'active',
                    ]);

                    $invoice->update([
                        'payment_id' => $payment->id,
                        'paid_amount' => $paidAmount,
                        'is_closed' => $isClosed,
                        'payment_method' => $paymentMethod,
                    ]);

                    try {
                        $this->createVoucherFromPayment($payment, $payment->id);
                    } catch (\Throwable $ve) {
                        Log::error('Voucher creation failed in ChallanController: ' . $ve->getMessage());
                    }
                }

                return [
                    'invoice' => $invoice,
                    'payment' => $payment,
                ];
            });

            $invoice = $result['invoice'];
            $payment = $result['payment'];

            $message = 'Invoice generated successfully.';
            if ($payment) {
                $message .= ' Payment of ৳' . number_format($payment->amount, 2) . ' recorded as Paid in Payment Module.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'invoice_id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'payment_id' => $payment ? $payment->id : null,
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
