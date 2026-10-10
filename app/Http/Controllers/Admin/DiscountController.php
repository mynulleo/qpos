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
            'creator:id,full_name,email'
        ])->latest('id');

        if ($request->filled('value') && $request->value !== 'null' && $request->value !== 'undefined') {
            $val = trim($request->value);
            $matchedItemIds = Item::where('title', 'like', "%{$val}%")
                ->orWhere('barcode', 'like', "%{$val}%")
                ->pluck('id')
                ->toArray();

            $query->where(function ($q) use ($val, $matchedItemIds) {
                $q->where('title', 'like', "%{$val}%")
                  ->orWhereHas('category', function ($cq) use ($val) {
                      $cq->where('title', 'like', "%{$val}%");
                  });

                if (!empty($matchedItemIds)) {
                    foreach ($matchedItemIds as $mId) {
                        $q->orWhereJsonContains('items', (int)$mId);
                    }
                }
            });
        }

        if ($request->filled('discount_type') && $request->discount_type !== 'null' && $request->discount_type !== '') {
            $scopeVal = $request->discount_type;
            $query->where(function ($q) use ($scopeVal) {
                $q->where('scope', $scopeVal)
                  ->orWhere('discount_type', $scopeVal);
            });
        }

        if ($request->filled('applicable_on') && $request->applicable_on !== 'null' && $request->applicable_on !== '') {
            $query->where('applicable_on', $request->applicable_on);
        }

        if ($request->filled('status') && $request->status !== 'null' && $request->status !== '') {
            $statusVal = in_array(strtolower($request->status), ['active', '1', 1, true], true) ? 'active' : 'deactive';
            $query->where('status', $statusVal);
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
        return view('admin.layouts.admin_app');
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
                $baseData = $this->prepareDiscountData($request);

                $authAdmin = auth()->guard('admin')->user() ?? auth()->user();
                $baseData['created_by'] = $authAdmin ? $authAdmin->id : 1;

                $discount = Discount::create($baseData);

                DB::commit();
                return $this->responseReturn("create", $discount);
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
            return view('admin.layouts.admin_app');
        }

        $discount = Discount::with([
            'category:id,title',
            'creator:id,full_name,email'
        ])->find($id);

        if (!$discount) {
            return response()->json(['message' => 'Discount not found'], 404);
        }

        // Attach details based on scope
        if ($discount->scope === 'category' && $discount->category_id) {
            $categoryItems = Item::where('category_id', $discount->category_id)
                ->select('id', 'title', 'barcode', 'retail_price', 'wholesale_price', 'category_id', 'status')
                ->orderBy('title')
                ->get();

            $totalItems = $categoryItems->count();
            $activeItems = $categoryItems->where('status', 'active')->count();
            $inactiveItems = $totalItems - $activeItems;
            $minPrice = $categoryItems->where('retail_price', '>', 0)->min('retail_price') ?? 0;
            $maxPrice = $categoryItems->max('retail_price') ?? 0;
            $avgPrice = $totalItems > 0 ? round($categoryItems->avg('retail_price'), 2) : 0;

            $discount->category_summary = [
                'total_items'    => $totalItems,
                'active_items'   => $activeItems,
                'inactive_items' => $inactiveItems,
                'min_price'      => (float) $minPrice,
                'max_price'      => (float) $maxPrice,
                'avg_price'      => (float) $avgPrice,
            ];
            $discount->items_details = $categoryItems;
        } elseif ($discount->scope === 'item' && !empty($discount->items) && is_array($discount->items)) {
            $discount->items_details = Item::whereIn('id', $discount->items)
                ->with('category:id,title')
                ->select('id', 'title', 'barcode', 'retail_price', 'wholesale_price', 'category_id', 'status')
                ->orderBy('title')
                ->get();
            $discount->category_summary = null;
        } else {
            $discount->items_details = [];
            $discount->category_summary = null;
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
        return view('admin.layouts.admin_app');
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
                $data = $this->prepareDiscountData($request);

                $authAdmin = auth()->guard('admin')->user() ?? auth()->user();
                $data['updated_by'] = $authAdmin ? $authAdmin->id : 1;

                $discount->update($data);

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

        $discount->status = ($discount->status === 'active') ? 'deactive' : 'active';
        $discount->save();

        return response()->json([
            'type'    => 'success',
            'message' => 'Status updated successfully',
            'status'  => $discount->status,
        ]);
    }

    /**
     * Prepare & normalize discount data from incoming request.
     */
    private function prepareDiscountData(Request $request): array
    {
        $raw = $request->all();

        // 1. Title / Campaign Name
        $title = $raw['title'] ?? $raw['name'] ?? 'Discount Campaign';

        // 2. Scope ('category' or 'item')
        $scope = $raw['scope'] ?? $raw['discount_type'] ?? 'category';
        if (!in_array($scope, ['category', 'item'])) {
            $scope = 'category';
        }

        // 3. Discount calculation unit / type ('percentage' or 'fixed')
        $discountType = $raw['unit'] ?? $raw['type'] ?? 'percentage';
        if (!in_array($discountType, ['percentage', 'fixed'])) {
            $discountType = (isset($raw['discount_type']) && in_array($raw['discount_type'], ['percentage', 'fixed']))
                ? $raw['discount_type']
                : 'percentage';
        }

        // 4. Status ('active' or 'deactive')
        $status = 'active';
        if (isset($raw['status'])) {
            $st = $raw['status'];
            if ($st === 'deactive' || $st === 'inactive' || $st === 0 || $st === '0' || $st === false) {
                $status = 'deactive';
            } else {
                $status = 'active';
            }
        }

        // 5. Validity dates
        $validFrom = !empty($raw['valid_from']) ? date('Y-m-d', strtotime($raw['valid_from'])) : null;
        $validTo   = !empty($raw['valid_to'])   ? date('Y-m-d', strtotime($raw['valid_to']))   : null;

        // 6. Target IDs based on scope
        $categoryId = ($scope === 'category') ? ($raw['category_id'] ?? null) : null;
        
        $items = null;
        if ($scope === 'item') {
            $rawItems = $raw['items'] ?? $raw['item_ids'] ?? $raw['item_id'] ?? [];
            if (is_string($rawItems)) {
                $decoded = json_decode($rawItems, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $rawItems = $decoded;
                } else {
                    $rawItems = explode(',', $rawItems);
                }
            }
            if (is_array($rawItems)) {
                $items = array_values(array_unique(array_filter(array_map('intval', $rawItems))));
            } elseif (is_numeric($rawItems)) {
                $items = [(int)$rawItems];
            }
        }

        // 7. Notes / Remarks
        $notes = $raw['notes'] ?? $raw['description'] ?? null;

        return [
            'title'          => $title,
            'scope'          => $scope,
            'category_id'    => $categoryId,
            'items'          => $items,
            'discount_type'  => $discountType,
            'discount_value' => floatval($raw['discount_value'] ?? 0),
            'applicable_on'  => $raw['applicable_on'] ?? 'both',
            'valid_from'     => $validFrom,
            'valid_to'       => $validTo,
            'status'         => $status,
            'notes'          => $notes,
        ];
    }

    /**
     * Get active discounts for POS terminal.
     */
    public function getActiveDiscounts(Request $request)
    {
        $today = date('Y-m-d');
        $discounts = Discount::activeValid($today)
            ->with(['category:id,title'])
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
