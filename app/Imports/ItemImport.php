<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Item;
use App\Models\Unit;
use App\Models\ItemStockSummary;
use App\Models\StockTransaction;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ItemImport implements ToCollection, WithHeadingRow
{
    public $failures = [];
    public $importedCount = 0;
    public $skippedCount = 0;

    /**
     * Process collection of rows
     */
    public function collection(Collection $rows)
    {
        $categories = Category::all()->keyBy(function ($item) {
            return strtolower(trim($item->title));
        });

        $units = Unit::all()->keyBy(function ($item) {
            return strtolower(trim($item->title));
        });

        $defaultUnit = Unit::first();

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // Accounting for 1-indexed Excel and 1 header row
            $rowData = $row->toArray();

            // Skip entirely blank rows
            if (empty(array_filter($rowData))) {
                continue;
            }

            // Normalise keys
            $title = isset($rowData['title']) ? trim($rowData['title']) : (isset($rowData['name']) ? trim($rowData['name']) : (isset($rowData['item_name']) ? trim($rowData['item_name']) : ''));
            $categoryVal = isset($rowData['category']) ? trim($rowData['category']) : (isset($rowData['category_id']) ? trim($rowData['category_id']) : '');
            $barcode = isset($rowData['barcode']) ? trim($rowData['barcode']) : '';
            $unitVal = isset($rowData['unit']) ? trim($rowData['unit']) : (isset($rowData['unit_id']) ? trim($rowData['unit_id']) : '');
            $openingRate = isset($rowData['opening_rate']) ? floatval($rowData['opening_rate']) : (isset($rowData['rate']) ? floatval($rowData['rate']) : (isset($rowData['purchase_price']) ? floatval($rowData['purchase_price']) : 0));
            $openingQty = isset($rowData['opening_qty']) ? intval($rowData['opening_qty']) : (isset($rowData['qty']) ? intval($rowData['qty']) : 0));
            $warrantyType = isset($rowData['warranty_type']) ? strtolower(trim($rowData['warranty_type'])) : 'none';
            $warrantyPeriod = isset($rowData['warranty_period']) ? trim($rowData['warranty_period']) : '';
            $description = isset($rowData['description']) ? trim($rowData['description']) : '';

            // 1. Validate Required Title
            if (empty($title)) {
                $this->failures[] = [
                    'row' => $rowNumber,
                    'column' => 'title',
                    'value' => 'EMPTY',
                    'error' => 'Product title is required and cannot be empty.',
                ];
                $this->skippedCount++;
                continue;
            }

            // 2. Validate / Resolve Category
            $categoryId = null;
            if (is_numeric($categoryVal)) {
                $cat = Category::find(intval($categoryVal));
                $categoryId = $cat ? $cat->id : null;
            } elseif (!empty($categoryVal)) {
                $cleanCat = strtolower($categoryVal);
                if (isset($categories[$cleanCat])) {
                    $categoryId = $categories[$cleanCat]->id;
                } else {
                    // Auto-create category if missing
                    $newCat = Category::create([
                        'title' => ucwords($categoryVal),
                        'status' => 'active',
                    ]);
                    $categories[$cleanCat] = $newCat;
                    $categoryId = $newCat->id;
                }
            }

            if (!$categoryId) {
                // If still no category, pick first category or report error
                $firstCat = Category::first();
                if ($firstCat) {
                    $categoryId = $firstCat->id;
                } else {
                    $newCat = Category::create([
                        'title' => 'General',
                        'status' => 'active',
                    ]);
                    $categoryId = $newCat->id;
                }
            }

            // 3. Validate / Resolve Unit
            $unitId = null;
            if (is_numeric($unitVal)) {
                $u = Unit::find(intval($unitVal));
                $unitId = $u ? $u->id : null;
            } elseif (!empty($unitVal)) {
                $cleanUnit = strtolower($unitVal);
                if (isset($units[$cleanUnit])) {
                    $unitId = $units[$cleanUnit]->id;
                } else {
                    $newUnit = Unit::create([
                        'title' => strtoupper($unitVal),
                        'status' => 'active',
                    ]);
                    $units[$cleanUnit] = $newUnit;
                    $unitId = $newUnit->id;
                }
            }

            if (!$unitId) {
                $unitId = $defaultUnit ? $defaultUnit->id : 1;
            }

            // 4. Validate / Handle Barcode
            if (!empty($barcode)) {
                $exists = Item::where('barcode', $barcode)->exists();
                if ($exists) {
                    $this->failures[] = [
                        'row' => $rowNumber,
                        'column' => 'barcode',
                        'value' => $barcode,
                        'error' => "Barcode '{$barcode}' already exists in database. Must be unique.",
                    ];
                    $this->skippedCount++;
                    continue;
                }
            } else {
                $barcode = Item::generateBarcode();
            }

            // 5. Validate Warranty Type
            if (!in_array($warrantyType, ['none', 'warranty', 'guarantee'])) {
                if (str_contains($warrantyType, 'guar')) {
                    $warrantyType = 'guarantee';
                } elseif (str_contains($warrantyType, 'warr')) {
                    $warrantyType = 'warranty';
                } else {
                    $warrantyType = 'none';
                }
            }

            // 6. Create Item
            try {
                DB::beginTransaction();

                $item = Item::create([
                    'title' => $title,
                    'category_id' => $categoryId,
                    'unit_id' => $unitId,
                    'barcode' => $barcode,
                    'opening_rate' => $openingRate,
                    'opening_qty' => $openingQty,
                    'warranty_type' => $warrantyType,
                    'warranty_period' => $warrantyPeriod,
                    'description' => $description,
                    'status' => 'active',
                ]);

                // Create initial stock summary if opening stock provided
                if ($openingQty > 0) {
                    ItemStockSummary::create([
                        'item_id' => $item->id,
                        'color_id' => null,
                        'size_id' => null,
                        'current_stock' => $openingQty,
                        'available_stock' => $openingQty,
                        'purchase_price' => $openingRate,
                    ]);
                }

                DB::commit();
                $this->importedCount++;
            } catch (\Exception $e) {
                DB::rollBack();
                $this->failures[] = [
                    'row' => $rowNumber,
                    'column' => 'database',
                    'value' => $title,
                    'error' => 'Database error: ' . $e->getMessage(),
                ];
                $this->skippedCount++;
            }
        }
    }
}
