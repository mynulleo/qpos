<?php

/**
 * @Quill Information Technology
 */

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Http\Resources\Resource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Base\BaseController;
use App\Models\ItemStockSummary;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;

class ItemController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query  = Item::with('category:id,title', 'brand:id,title', 'unit:id,title')->latest();

        if ($request->field_name && $request->value) {
            $query->whereLike($request->field_name, $request->value);
        }

        if (!empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }

        if (!empty($request->brand_id)) {
            $query->where('brand_id', $request->brand_id);
        }

        if (!empty($request->color_id)) {
            $query->whereHas('itemPrices', function ($q) use ($request) {
                $q->where('color_id', $request->color_id);
            });
        }

        if (!empty($request->size_id)) {
            $query->whereHas('itemPrices', function ($q) use ($request) {
                $q->where('size_id', $request->size_id);
            });
        }


        if ($request->allData) {
            $results = $query->get();
            if (!$request->has('with_barcode_image')) {
                $results->makeHidden(['barcode_image', 'original_image']);
            }
            return $results;
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
                if (empty($data['barcode'])) {
                    $data['barcode'] = Item::generateBarcode();
                }
                // push the insert text
                if (!empty($request->image_base64)) {
                    $data["image"] = $this->upload($request->image_base64, "item", null, true);
                } else if (!empty($request->file("image"))) {
                    $data["image"] = $this->upload($request->image, "item");
                }
                $res = Item::create($data);

                // Handle variants matrix if provided
                $variants = $request->input('variants');
                if (is_string($variants)) {
                    $variants = json_decode($variants, true);
                }

                if (is_array($variants) && count($variants) > 0) {
                    foreach ($variants as $variant) {
                        $colorId = !empty($variant['color_id']) ? $variant['color_id'] : null;
                        $sizeId = !empty($variant['size_id']) ? $variant['size_id'] : null;
                        $purchasePrice = isset($variant['purchase_price']) ? floatval($variant['purchase_price']) : 0;
                        $sellingPrice = isset($variant['selling_price']) ? floatval($variant['selling_price']) : 0;
                        $qty = isset($variant['qty']) ? intval($variant['qty']) : 0;

                        if ($colorId || $sizeId || $purchasePrice > 0 || $sellingPrice > 0) {
                            \App\Models\ItemPrice::updateOrCreate(
                                [
                                    'item_id' => $res->id,
                                    'color_id' => $colorId,
                                    'size_id' => $sizeId,
                                ],
                                [
                                    'purchase_price' => $purchasePrice,
                                    'selling_price' => $sellingPrice,
                                    'status' => 'active',
                                ]
                            );

                            if ($qty > 0) {
                                StockTransaction::create([
                                    'item_id' => $res->id,
                                    'color_id' => $colorId,
                                    'size_id' => $sizeId,
                                    'transaction_date' => date('Y-m-d'),
                                    'transaction_type' => 'Opening',
                                    'reference_type' => 'Opening',
                                    'qty_in' => $qty,
                                    'qty_out' => 0,
                                    'status' => 'active',
                                ]);
                            }
                        }
                    }
                } else if ($res->opening_qty > 0) {
                    $stockitemdata = [
                        'item_id' => $res->id,
                        'transaction_date' => date('Y-m-d'),
                        'transaction_type' => 'Opening',
                        'qty_in' => $res->opening_qty,
                        'qty_out' => 0,
                        'status' => 'active',
                    ];
                    // create stock transaction for opening stock
                    StockTransaction::create($stockitemdata);
                }

                DB::commit();
                return $this->responseReturn("create", $res);
            } catch (Exception $ex) {
                DB::rollBack();
                return response()->json(['exception' => $ex->errorInfo ?? $ex->getMessage()], 422);
            }
        }
    }

    public function getGeneratedBarcode()
    {
        $barcode = Item::generateBarcode();
        $dns1d = new \Milon\Barcode\DNS1D();
        $barcode_image = 'data:image/png;base64,' . $dns1d->getBarcodePNG((string)$barcode, 'C128', 2, 45);
        return response()->json([
            'barcode' => $barcode,
            'barcode_image' => $barcode_image,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        if ($request->format() == 'html') {
            return view('layouts.backend_app');
        }

        $item = Item::with([
            'category:id,title',
            'brand:id,title',
            'unit:id,title',
            'itemPrices.color:id,title',
            'itemPrices.size:id,title',
        ])->find($id);

        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        // 1. Overall Stock & Sales Metrics
        $totalQtyIn = (float) DB::table('stock_transactions')
            ->where('item_id', $id)
            ->where('status', 'active')
            ->sum('qty_in');

        $totalSoldQty = (float) DB::table('stock_transactions')
            ->where('item_id', $id)
            ->where('status', 'active')
            ->where(function($q) {
                $q->where('transaction_type', 'Issue')->orWhere('reference_type', 'Issue');
            })
            ->sum('qty_out');

        $totalWastageQty = (float) DB::table('stock_transactions')
            ->where('item_id', $id)
            ->where('status', 'active')
            ->where(function($q) {
                $q->where('transaction_type', 'Wastage')->orWhere('reference_type', 'Wastage');
            })
            ->sum('qty_out');

        $totalStockOut = (float) DB::table('stock_transactions')
            ->where('item_id', $id)
            ->where('status', 'active')
            ->sum('qty_out');

        $currentStock = $totalQtyIn - $totalStockOut;

        $totalSalesAmount = (float) DB::table('invoice_details')
            ->where('item_id', $id)
            ->where('status', 'active')
            ->sum('total_amount');

        $totalPurchaseAmount = (float) DB::table('purchase_details')
            ->where('item_id', $id)
            ->where('status', 'active')
            ->sum('total_amount');

        // 2. Variant-wise Stock, Price & Sales Matrix
        $colors = DB::table('colors')->pluck('title', 'id')->toArray();
        $sizes = DB::table('sizes')->pluck('title', 'id')->toArray();

        $variantKeys = [];
        $itemPrices = DB::table('item_prices')
            ->where('item_id', $id)
            ->get();

        foreach ($itemPrices as $ip) {
            $key = ($ip->color_id ?? 0) . '_' . ($ip->size_id ?? 0);
            $variantKeys[$key] = [
                'color_id'       => $ip->color_id,
                'size_id'        => $ip->size_id,
                'purchase_price' => (float)$ip->purchase_price,
                'selling_price'  => (float)$ip->selling_price,
                'updated_at'     => $ip->updated_at,
                'created_at'     => $ip->created_at,
            ];
        }

        $stockTrxVariants = DB::table('stock_transactions')
            ->where('item_id', $id)
            ->where('status', 'active')
            ->select('color_id', 'size_id')
            ->distinct()
            ->get();

        foreach ($stockTrxVariants as $stv) {
            $key = ($stv->color_id ?? 0) . '_' . ($stv->size_id ?? 0);
            if (!isset($variantKeys[$key])) {
                $variantKeys[$key] = [
                    'color_id'       => $stv->color_id,
                    'size_id'        => $stv->size_id,
                    'purchase_price' => 0,
                    'selling_price'  => 0,
                    'updated_at'     => null,
                    'created_at'     => null,
                ];
            }
        }

        if (empty($variantKeys)) {
            $variantKeys['0_0'] = [
                'color_id'       => null,
                'size_id'        => null,
                'purchase_price' => 0,
                'selling_price'  => 0,
                'updated_at'     => null,
                'created_at'     => null,
            ];
        }

        $variantsBreakdown = [];
        foreach ($variantKeys as $v) {
            $cId = $v['color_id'];
            $sId = $v['size_id'];

            $vQtyIn = (float) DB::table('stock_transactions')
                ->where('item_id', $id)
                ->where('status', 'active')
                ->where(function($q) use ($cId) {
                    if ($cId) $q->where('color_id', $cId);
                    else $q->whereNull('color_id');
                })
                ->where(function($q) use ($sId) {
                    if ($sId) $q->where('size_id', $sId);
                    else $q->whereNull('size_id');
                })
                ->sum('qty_in');

            $vSoldQty = (float) DB::table('stock_transactions')
                ->where('item_id', $id)
                ->where('status', 'active')
                ->where(function($q) use ($cId) {
                    if ($cId) $q->where('color_id', $cId);
                    else $q->whereNull('color_id');
                })
                ->where(function($q) use ($sId) {
                    if ($sId) $q->where('size_id', $sId);
                    else $q->whereNull('size_id');
                })
                ->where(function($q) {
                    $q->where('transaction_type', 'Issue')->orWhere('reference_type', 'Issue');
                })
                ->sum('qty_out');

            $vWastageQty = (float) DB::table('stock_transactions')
                ->where('item_id', $id)
                ->where('status', 'active')
                ->where(function($q) use ($cId) {
                    if ($cId) $q->where('color_id', $cId);
                    else $q->whereNull('color_id');
                })
                ->where(function($q) use ($sId) {
                    if ($sId) $q->where('size_id', $sId);
                    else $q->whereNull('size_id');
                })
                ->where(function($q) {
                    $q->where('transaction_type', 'Wastage')->orWhere('reference_type', 'Wastage');
                })
                ->sum('qty_out');

            $vTotalOut = (float) DB::table('stock_transactions')
                ->where('item_id', $id)
                ->where('status', 'active')
                ->where(function($q) use ($cId) {
                    if ($cId) $q->where('color_id', $cId);
                    else $q->whereNull('color_id');
                })
                ->where(function($q) use ($sId) {
                    if ($sId) $q->where('size_id', $sId);
                    else $q->whereNull('size_id');
                })
                ->sum('qty_out');

            $vStock = $vQtyIn - $vTotalOut;
            $margin = $v['selling_price'] - $v['purchase_price'];
            $marginPercent = $v['purchase_price'] > 0 ? round(($margin / $v['purchase_price']) * 100, 2) : 0;

            $variantsBreakdown[] = [
                'color_id'        => $cId,
                'color_title'     => $cId && isset($colors[$cId]) ? $colors[$cId] : null,
                'size_id'         => $sId,
                'size_title'      => $sId && isset($sizes[$sId]) ? $sizes[$sId] : null,
                'purchase_price'  => $v['purchase_price'],
                'selling_price'   => $v['selling_price'],
                'profit_margin'   => $margin,
                'margin_percent'  => $marginPercent,
                'total_qty_in'    => $vQtyIn,
                'total_sold'      => $vSoldQty,
                'total_wastage'   => $vWastageQty,
                'current_stock'   => $vStock,
                'last_updated'    => $v['updated_at'] ? date('d M, Y h:i A', strtotime($v['updated_at'])) : ($v['created_at'] ? date('d M, Y h:i A', strtotime($v['created_at'])) : null),
            ];
        }

        // 3. Price History / Timeline
        $priceHistory = [];
        foreach ($itemPrices as $ip) {
            $priceHistory[] = [
                'type'           => 'Standard Price Setting',
                'color_title'    => $ip->color_id && isset($colors[$ip->color_id]) ? $colors[$ip->color_id] : 'Standard',
                'size_title'     => $ip->size_id && isset($sizes[$ip->size_id]) ? $sizes[$ip->size_id] : 'Standard',
                'purchase_price' => (float)$ip->purchase_price,
                'selling_price'  => (float)$ip->selling_price,
                'date'           => date('d M, Y h:i A', strtotime($ip->updated_at ?: $ip->created_at)),
                'created_at'     => $ip->updated_at ?: $ip->created_at,
            ];
        }

        $recentPurchases = DB::table('purchase_details as pd')
            ->join('purchases as p', 'p.id', '=', 'pd.purchase_id')
            ->leftJoin('suppliers as s', 's.id', '=', 'p.supplier_id')
            ->leftJoin('colors as c', 'c.id', '=', 'pd.color_id')
            ->leftJoin('sizes as sz', 'sz.id', '=', 'pd.size_id')
            ->where('pd.item_id', $id)
            ->select(
                'pd.id',
                'pd.purchase_id',
                'p.invoiceno',
                'p.purchase_date',
                's.name as supplier_name',
                'c.title as color_title',
                'sz.title as size_title',
                'pd.qty',
                'pd.price as rate',
                'pd.total_amount',
                'pd.created_at'
            )
            ->orderBy('pd.id', 'desc')
            ->limit(10)
            ->get();

        foreach ($recentPurchases as $rp) {
            $priceHistory[] = [
                'type'           => 'Purchase (Challan: ' . ($rp->invoiceno ?: 'N/A') . ')',
                'color_title'    => $rp->color_title ?: 'Standard',
                'size_title'     => $rp->size_title ?: 'Standard',
                'purchase_price' => (float)$rp->rate,
                'selling_price'  => null,
                'date'           => date('d M, Y', strtotime($rp->purchase_date ?: $rp->created_at)),
                'created_at'     => $rp->created_at,
            ];
        }

        usort($priceHistory, function ($a, $b) {
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });

        // 4. Recent Sales History (Last 10)
        $recentSales = DB::table('invoice_details as ind')
            ->join('invoices as inv', 'inv.id', '=', 'ind.invoice_id')
            ->leftJoin('clients as cust', 'cust.id', '=', 'inv.client_id')
            ->leftJoin('colors as c', 'c.id', '=', 'ind.color_id')
            ->leftJoin('sizes as sz', 'sz.id', '=', 'ind.size_id')
            ->where('ind.item_id', $id)
            ->where('ind.status', 'active')
            ->select(
                'ind.id',
                'inv.invoice_no',
                'inv.invoice_date',
                'cust.name as customer_name',
                'c.title as color_title',
                'sz.title as size_title',
                'ind.serial_no',
                'ind.qty',
                'ind.amount as price',
                'ind.total_amount',
                'ind.created_at'
            )
            ->orderBy('ind.id', 'desc')
            ->limit(10)
            ->get();

        $item->metrics = [
            'total_stock_in'        => $totalQtyIn,
            'total_sold_qty'        => $totalSoldQty,
            'total_wastage_qty'     => $totalWastageQty,
            'total_stock_out'       => $totalStockOut,
            'current_stock'         => $currentStock,
            'total_sales_amount'    => $totalSalesAmount,
            'total_purchase_amount' => $totalPurchaseAmount,
        ];

        $item->variants_breakdown = $variantsBreakdown;
        $item->price_history = $priceHistory;
        $item->recent_sales = $recentSales;
        $item->recent_purchases = $recentPurchases;

        return $item;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function edit(Item $item)
    {
        return view('layouts.backend_app');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $item = Item::find($id);
        if ($this->validateCheck($request, $item->id)) {
            try {
                DB::beginTransaction();
                $data = $request->all();
                if (empty($data['barcode'])) {
                    $data['barcode'] = $item->barcode ?: Item::generateBarcode();
                }
                // push the update text
                if (!empty($request->image_base64)) {
                    $oldFile = $this->oldFile($item->image);
                    Storage::delete($oldFile);
                    $data["image"] = $this->upload($request->image_base64, "item", null, true);
                } else if (!empty($request->file("image"))) {
                    $oldFile = $this->oldFile($item->image);
                    Storage::delete($oldFile);
                    $data["image"] = $this->upload($request->image, "item");
                } else {
                    unset($data['image']);
                }
                $item->fill($data)->save();

                // Check for Price Modification or New Purchase
                $isPriceModification = filter_var($request->input('is_price_modification'), FILTER_VALIDATE_BOOLEAN);
                $variants = $request->input('variants');
                if (is_string($variants)) {
                    $variants = json_decode($variants, true);
                }

                if ($isPriceModification && is_array($variants)) {
                    foreach ($variants as $variant) {
                        $colorId = !empty($variant['color_id']) ? $variant['color_id'] : null;
                        $sizeId = !empty($variant['size_id']) ? $variant['size_id'] : null;
                        $purchasePrice = isset($variant['purchase_price']) ? floatval($variant['purchase_price']) : 0;
                        $sellingPrice = isset($variant['selling_price']) ? floatval($variant['selling_price']) : 0;
                        $qty = isset($variant['qty']) ? intval($variant['qty']) : 0;

                        if ($colorId || $sizeId || $purchasePrice > 0 || $sellingPrice > 0) {
                            \App\Models\ItemPrice::updateOrCreate(
                                [
                                    'item_id' => $item->id,
                                    'color_id' => $colorId,
                                    'size_id' => $sizeId,
                                ],
                                [
                                    'purchase_price' => $purchasePrice,
                                    'selling_price' => $sellingPrice,
                                    'status' => 'active',
                                ]
                            );

                            if ($qty > 0) {
                                StockTransaction::create([
                                    'item_id' => $item->id,
                                    'color_id' => $colorId,
                                    'size_id' => $sizeId,
                                    'transaction_date' => date('Y-m-d'),
                                    'transaction_type' => 'Purchase',
                                    'reference_type' => 'Purchase',
                                    'qty_in' => $qty,
                                    'qty_out' => 0,
                                    'status' => 'active',
                                ]);
                            }
                        }
                    }
                }

                DB::commit();
                return $this->responseReturn("update", $item);
            } catch (Exception $ex) {
                DB::rollBack();
                return response()->json(['exception' => $ex->errorInfo ?? $ex->getMessage()], 422);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = Item::find($id);
        // delete
        app("deleteAction")->arrayImages($item->image);
        $old = $this->oldFile($item->image);
        if (Storage::disk("public")->exists($old)) {
            Storage::delete($old);
        }


        $res = $item->delete();
        return $this->responseReturn("delete", $res);
    }

    public function getItemStock(Request $request, $item_id)
    {
        $allSummaries = ItemStockSummary::with('color:id,title', 'size:id,title')
            ->where('item_id', $item_id)
            ->get();

        $totalStock = (float)$allSummaries->sum('current_stock');

        // Identify variant with highest stock
        $bestVariant = $allSummaries->where('current_stock', '>', 0)->sortByDesc('current_stock')->first();
        if (!$bestVariant && $allSummaries->isNotEmpty()) {
            $bestVariant = $allSummaries->sortByDesc('current_stock')->first();
        }

        $defaultColorId = $bestVariant ? $bestVariant->color_id : null;
        $defaultSizeId  = $bestVariant ? $bestVariant->size_id : null;
        $defaultStock   = $bestVariant ? (float)$bestVariant->current_stock : $totalStock;

        // Build list of variants with stock
        $variants = $allSummaries->map(function ($row) {
            return [
                'color_id'    => $row->color_id,
                'color_title' => $row->color ? $row->color->title : 'Standard',
                'size_id'     => $row->size_id,
                'size_title'  => $row->size ? $row->size->title : 'Standard',
                'stock'       => (float)$row->current_stock,
            ];
        })->values();

        // Calculate specific variant stock if color or size is queried
        if ($request->has('color_id') || $request->has('size_id') || $request->has('strict_variant')) {
            $query = ItemStockSummary::where('item_id', $item_id);

            if ($request->filled('color_id')) {
                $query->where('color_id', $request->color_id);
            } elseif ($request->has('color_id') && ($request->color_id === null || $request->color_id === '' || $request->color_id === 'null')) {
                if ($allSummaries->whereNotNull('color_id')->isNotEmpty()) {
                    $query->whereNull('color_id');
                }
            }

            if ($request->filled('size_id')) {
                $query->where('size_id', $request->size_id);
            } elseif ($request->has('size_id') && ($request->size_id === null || $request->size_id === '' || $request->size_id === 'null')) {
                if ($allSummaries->whereNotNull('size_id')->isNotEmpty()) {
                    $query->whereNull('size_id');
                }
            }

            $stock = (float)$query->sum('current_stock');
        } else {
            // Initial load without specific variant filter: use best variant stock or total stock
            $stock = $defaultStock;
        }

        // Fetch item details and purchase price
        $item = Item::with('category:id,title', 'unit:id,title')->find($item_id);
        $cost = $item ? (float)($item->purchase_price ?? $item->price ?? 0) : 0;

        // Fetch variant-specific purchase price from ItemPrice
        $targetColorId = $request->filled('color_id') ? $request->color_id : ($request->has('color_id') ? null : $defaultColorId);
        $targetSizeId  = $request->filled('size_id') ? $request->size_id : ($request->has('size_id') ? null : $defaultSizeId);

        if ($targetColorId || $targetSizeId) {
            $ipQuery = \App\Models\ItemPrice::where('item_id', $item_id);
            if ($targetColorId) {
                $ipQuery->where('color_id', $targetColorId);
            }
            if ($targetSizeId) {
                $ipQuery->where('size_id', $targetSizeId);
            }
            $varPrice = $ipQuery->first();
            if ($varPrice && !empty($varPrice->purchase_price)) {
                $cost = (float)$varPrice->purchase_price;
            }
        }

        // Available serials (purchased serials not yet sold or wasted)
        $purchasedSerials = \App\Models\PurchaseDetail::where('item_id', $item_id)
            ->whereNotNull('serial_no')
            ->where('serial_no', '!=', '')
            ->pluck('serial_no');

        $soldSerials = \App\Models\InvoiceDetails::where('item_id', $item_id)
            ->whereNotNull('serial_no')
            ->where('serial_no', '!=', '')
            ->where('status', 'active')
            ->pluck('serial_no');

        $wastedSerials = \App\Models\WastageDetail::where('item_id', $item_id)
            ->whereNotNull('serial_no')
            ->where('serial_no', '!=', '')
            ->pluck('serial_no');

        $purchasedList = [];
        foreach ($purchasedSerials as $s) {
            $split = preg_split('/[\r\n,]+/', $s, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($split as $sn) {
                $t = trim($sn);
                if (!empty($t)) $purchasedList[$t] = true;
            }
        }

        $usedList = [];
        $soldList = [];
        foreach ($soldSerials as $s) {
            $split = preg_split('/[\r\n,]+/', $s, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($split as $sn) {
                $t = trim($sn);
                if (!empty($t)) {
                    $usedList[$t] = true;
                    $soldList[$t] = true;
                }
            }
        }
        foreach ($wastedSerials as $s) {
            $split = preg_split('/[\r\n,]+/', $s, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($split as $sn) {
                $t = trim($sn);
                if (!empty($t)) $usedList[$t] = true;
            }
        }

        $hasPurchasedSerials = count($purchasedList) > 0;
        $availableSerials = array_values(array_diff(array_keys($purchasedList), array_keys($usedList)));

        if ($request->has('details') || $request->has('color_id') || $request->has('size_id')) {
            return response()->json([
                'stock'                 => $stock,
                'total_stock'           => $totalStock,
                'default_color_id'      => $defaultColorId,
                'default_size_id'       => $defaultSizeId,
                'default_stock'         => $defaultStock,
                'variants'              => $variants,
                'unit_cost'             => $cost,
                'item'                  => $item,
                'has_purchased_serials' => $hasPurchasedSerials,
                'available_serials'     => $availableSerials,
                'sold_serials'          => array_keys($soldList),
                'all_purchased_serials' => array_keys($purchasedList),
            ]);
        }

        return $stock;
    }

    public function getSalesItems(Request $request)
    {
        $query = Item::where('status', 'active');

        if ($request->has('term') && !empty($request->term)) {
            $term = $request->term;
            $query->where(function ($q) use ($term) {
                $q->where('barcode', 'like', "%{$term}%")
                  ->orWhere('title', 'like', "%{$term}%");
            });
        }

        $items = $query->with([
            'category:id,title',
            'unit:id,title',
            'itemPrices.color:id,title',
            'itemPrices.size:id,title',
            'stockSummaries.color:id,title',
            'stockSummaries.size:id,title',
        ])->get();

        return response()->json($items);
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
