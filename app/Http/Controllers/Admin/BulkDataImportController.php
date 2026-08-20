<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Base\BaseController;
use App\Imports\ClientImport;
use App\Imports\EmployeesImport;
use App\Imports\ItemImport;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class BulkDataImportController extends BaseController
{

    public function employee()
    {
        return view('layouts.backend_app');
    }


    public function client()
    {
        return view('layouts.backend_app');
    }

    public function item()
    {
        return view('layouts.backend_app');
    }

    public function itemimport(Request $request)
    {
        $request->validate([
            'itemfile' => 'required|file|mimes:xls,xlsx,csv,txt',
        ]);

        $file = $request->file('itemfile');

        try {
            $importer = new ItemImport();
            Excel::import($importer, $file);

            $hasFailures = count($importer->failures) > 0;

            return response()->json([
                'success' => !$hasFailures || $importer->importedCount > 0,
                'message' => "{$importer->importedCount} items imported successfully." . ($hasFailures ? " {$importer->skippedCount} rows had errors." : ""),
                'imported_count' => $importer->importedCount,
                'skipped_count' => $importer->skippedCount,
                'failures' => $importer->failures,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process import file: ' . $e->getMessage(),
                'failures' => [
                    [
                        'row' => 'General',
                        'column' => 'File',
                        'value' => $file->getClientOriginalName(),
                        'error' => $e->getMessage()
                    ]
                ]
            ], 422);
        }
    }

    /**
     * Download Sample CSV for Item Import
     */
    public function downloadItemSampleCsv()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="item_import_sample.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = [
            'title',
            'category',
            'barcode',
            'unit',
            'opening_rate',
            'opening_qty',
            'warranty_type',
            'warranty_period',
            'description',
        ];

        $firstCat = Category::first();
        $catName = $firstCat ? $firstCat->title : 'Electronics';

        $firstUnit = Unit::first();
        $unitName = $firstUnit ? $firstUnit->title : 'Pcs';

        $sampleData = [
            [
                'Dell Inspiron 15 Core i5 Laptop',
                $catName,
                '8801001001',
                $unitName,
                '62000',
                '10',
                'warranty',
                '2 Years',
                '15.6 inch FHD, 8GB RAM, 512GB SSD',
            ],
            [
                'Samsung 24 Inch IPS Borderless Monitor',
                $catName,
                '8801001002',
                $unitName,
                '14500',
                '15',
                'warranty',
                '3 Years',
                '75Hz Refresh Rate, HDMI/VGA',
            ],
            [
                'Logitech Wireless Keyboard & Mouse Combo MK270',
                $catName,
                '8801001003',
                $unitName,
                '2200',
                '25',
                'guarantee',
                '1 Year',
                'USB Nano Receiver 2.4GHz',
            ],
        ];

        $callback = function () use ($columns, $sampleData) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($sampleData as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function empimport(Request $request)
    {
        $request->validate([
            'empexcel' => 'required|file|mimes:xls,xlsx,csv',
        ]);

        $file = $request->file('empexcel');

        try {
            Excel::import(new EmployeesImport, $file);

            return response()->json([
                'success' => true,
                'message' => 'Employees imported successfully!'
            ]);
        } catch (ValidationException $e) {

            $errors = [];

            foreach ($e->failures() as $failure) {
                $row = $failure->row(); // row number
                foreach ($failure->errors() as $error) {
                    $errors[] = "Row {$row}: {$error}";
                }
            }

            return response()->json([
                'success' => false,
                'exception_errors' => $errors
            ], 422);
        }
    }

    public function clientimport(Request $request)
    {
        $request->validate([
            'clientexcel' => 'required|file|mimes:xls,xlsx,csv',
        ]);

        try {
            Excel::import(new ClientImport, $request->file('clientexcel'));

            return response()->json([
                'success' => true,
                'message' => 'Clients imported successfully!'
            ]);
        } catch (ValidationException $e) {

            $errors = [];

            foreach ($e->failures() as $failure) {
                $row = $failure->row(); // row number
                foreach ($failure->errors() as $error) {
                    $errors[] = "Row {$row}: {$error}";
                }
            }

            return response()->json([
                'success' => false,
                'exception_errors' => $errors
            ], 422);
        }
    }
}
