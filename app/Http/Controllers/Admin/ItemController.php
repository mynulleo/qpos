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
        $query  = Item::with('category:id,title', 'unit:id,title')->latest();

        if ($request->field_name && $request->value) {
            $query->whereLike($request->field_name, $request->value);
        }

        if (!empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
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
            'unit:id,title',
            'itemPrices.color:id,title',
            'itemPrices.size:id,title',
            'stockSummaries.color:id,title',
            'stockSummaries.size:id,title'
        ])->find($id);

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

    public function getItemStock($item_id)
    {
        $stock = 0;
        $res = ItemStockSummary::select([DB::raw('SUM(current_stock) as current_stock')])->where('item_id', $item_id)->first();
        if ($res) {
            $stock = $res->current_stock;
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
