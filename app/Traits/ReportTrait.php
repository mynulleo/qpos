<?php

namespace App\Traits;

use DateTime;
use App\Models\Client;
use App\Models\Account;
use App\Models\Payment;
use App\Models\Employee;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Supplier;
use App\Models\Expense;
use App\Models\ExpenseDetail;
use App\Models\PaymentDetail;
use App\Models\VoucherDetail;
use App\Models\Commission;
use App\Models\SalarySheet;
use App\Models\SalarySheetDetail;
use App\Models\LoanInfo;
use App\Models\Invoice;
use App\Models\InvoiceDetails;
use App\Models\Item;
use App\Models\ItemPrice;
use App\Models\Agent;
use App\Models\SalesReturn;
use App\Models\SalesReturnDetail;
use App\Models\Wastage;
use App\Models\WastageDetail;
use Illuminate\Support\Facades\DB;

trait ReportTrait
{

    public function getOpeningBalance($account_id, $from_date)
    {
        $opening_balance = 0;

        // get account (ignore soft delete)
        $account = Account::whereNull('deleted_at')->find($account_id);

        if (!$account) {
            return 0;
        }

        // base opening balance from account table
        $opening_balance = 0;

        // total debit before date (ignore soft delete)
        $total_debit = VoucherDetail::join('vouchers', function ($join) use ($from_date) {
            $join->on('vouchers.id', '=', 'voucher_details.voucher_id')
                ->whereNull('vouchers.deleted_at')
                ->whereDate('vouchers.voucher_date', '<', $from_date);
        })
            ->where('voucher_details.account_id', $account_id)
            ->whereNull('voucher_details.deleted_at')
            ->sum('voucher_details.dr_amount');

        // total credit before date (ignore soft delete)
        $total_credit = VoucherDetail::join('vouchers', function ($join) use ($from_date) {
            $join->on('vouchers.id', '=', 'voucher_details.voucher_id')
                ->whereNull('vouchers.deleted_at')
                ->whereDate('vouchers.voucher_date', '<', $from_date);
        })
            ->where('voucher_details.account_id', $account_id)
            ->whereNull('voucher_details.deleted_at')
            ->sum('voucher_details.cr_amount');

        // apply accounting rule based on account type
        if (in_array($account->account_type, ['Liability', 'Capital', 'Income'])) {

            // Liability type formula
            $opening_balance = $opening_balance + $total_credit - $total_debit;
        } else {

            // Asset / Expense type formula
            $opening_balance = $opening_balance + $total_debit - $total_credit;
        }

        return (float) $opening_balance;
    }

    public function getItemLadger($searchdata)
    {
        if (is_numeric($searchdata) || is_string($searchdata)) {
            $itemid = $searchdata;
            $searchdata = ['item_id' => $itemid];
        } else {
            $itemid = $searchdata['item_id'] ?? null;
        }

        if (!$itemid) {
            return [
                'item' => null,
                'metrics' => null,
                'variants_breakdown' => [],
                'datas' => []
            ];
        }

        $fromDate = !empty($searchdata['start_date']) ? vue_to_server_date($searchdata['start_date']) : null;
        $toDate = !empty($searchdata['end_date']) ? vue_to_server_date($searchdata['end_date']) : null;
        $colorId = !empty($searchdata['color_id']) ? $searchdata['color_id'] : null;
        $sizeId = !empty($searchdata['size_id']) ? $searchdata['size_id'] : null;
        $warehouseId = !empty($searchdata['warehouse_id']) ? $searchdata['warehouse_id'] : null;
        $trxType = !empty($searchdata['transaction_type']) ? $searchdata['transaction_type'] : null;

        // 1. Fetch Item Profile
        $item = Item::with('category:id,title', 'brand:id,title', 'unit:id,title')->find($itemid);
        if (!$item) {
            return [
                'item' => null,
                'metrics' => null,
                'variants_breakdown' => [],
                'datas' => []
            ];
        }

        // 2. Lifetime Metrics
        $lifetimeIn = (float) DB::table('stock_transactions')
            ->where('item_id', $itemid)
            ->where('status', 'active')
            ->sum('qty_in');

        $lifetimeOut = (float) DB::table('stock_transactions')
            ->where('item_id', $itemid)
            ->where('status', 'active')
            ->sum('qty_out');

        $lifetimeSold = (float) DB::table('stock_transactions')
            ->where('item_id', $itemid)
            ->where('status', 'active')
            ->where(function($q) {
                $q->where('transaction_type', 'Issue')->orWhere('reference_type', 'Issue');
            })
            ->sum('qty_out');

        $lifetimeWastage = (float) DB::table('stock_transactions')
            ->where('item_id', $itemid)
            ->where('status', 'active')
            ->where(function($q) {
                $q->where('transaction_type', 'Wastage')->orWhere('reference_type', 'Wastage');
            })
            ->sum('qty_out');

        $lifetimeReturned = (float) DB::table('stock_transactions')
            ->where('item_id', $itemid)
            ->where('status', 'active')
            ->where(function($q) {
                $q->whereIn('transaction_type', ['SalesReturn', 'Return'])->orWhereIn('reference_type', ['SalesReturn', 'Return']);
            })
            ->sum('qty_in');

        $currentStock = $lifetimeIn - $lifetimeOut;
        $costPrice = floatval($item->purchase_price ?? 0);
        $sellPrice = floatval($item->selling_price ?? 0);

        // 3. Variant Breakdown & Pricing
        $colors = DB::table('colors')->pluck('title', 'id')->toArray();
        $sizes = DB::table('sizes')->pluck('title', 'id')->toArray();

        $variantSummaries = DB::table('item_stock_summaries')
            ->where('item_id', $itemid)
            ->get();

        $itemPrices = DB::table('item_prices')
            ->where('item_id', $itemid)
            ->where('status', 'active')
            ->get();

        if ($costPrice == 0 && $itemPrices->isNotEmpty()) {
            $costPrice = floatval($itemPrices->first()->purchase_price ?? 0);
        }
        if ($sellPrice == 0 && $itemPrices->isNotEmpty()) {
            $sellPrice = floatval($itemPrices->first()->selling_price ?? 0);
        }

        $variantPriceMap = [];
        foreach ($itemPrices as $ip) {
            $key = ($ip->color_id ?? 0) . '_' . ($ip->size_id ?? 0);
            $variantPriceMap[$key] = [
                'purchase_price' => floatval($ip->purchase_price),
                'selling_price' => floatval($ip->selling_price),
            ];
        }

        $variantsBreakdown = [];
        $totalValuationCost = 0;
        $totalValuationSell = 0;
        foreach ($variantSummaries as $vs) {
            $k = ($vs->color_id ?? 0) . '_' . ($vs->size_id ?? 0);
            $vp = $variantPriceMap[$k] ?? ['purchase_price' => $costPrice, 'selling_price' => $sellPrice];
            $stk = floatval($vs->current_stock);
            $pPrice = $vp['purchase_price'] ?: $costPrice;
            $sPrice = $vp['selling_price'] ?: $sellPrice;

            if ($stk > 0) {
                $totalValuationCost += $stk * $pPrice;
                $totalValuationSell += $stk * $sPrice;
            }

            $variantsBreakdown[] = [
                'color_id' => $vs->color_id,
                'color_title' => $vs->color_id && isset($colors[$vs->color_id]) ? $colors[$vs->color_id] : null,
                'size_id' => $vs->size_id,
                'size_title' => $vs->size_id && isset($sizes[$vs->size_id]) ? $sizes[$vs->size_id] : null,
                'purchase_price' => $pPrice,
                'selling_price' => $sPrice,
                'total_qty_in' => floatval($vs->total_qty_in),
                'total_qty_out' => floatval($vs->total_qty_out),
                'current_stock' => $stk,
            ];
        }

        if ($totalValuationCost == 0 && $currentStock > 0 && $costPrice > 0) {
            $totalValuationCost = $currentStock * $costPrice;
        }
        if ($totalValuationSell == 0 && $currentStock > 0 && $sellPrice > 0) {
            $totalValuationSell = $currentStock * $sellPrice;
        }

        $stockPurchaseValue = round($totalValuationCost, 2);
        $stockSellingValue = round($totalValuationSell, 2);
        $potentialProfit = round(max(0, $stockSellingValue - $stockPurchaseValue), 2);

        // 4. Period Opening Balance (before fromDate)
        $openingBalanceQuery = DB::table('stock_transactions')
            ->where('item_id', $itemid)
            ->where('status', 'active');

        if ($colorId) {
            $openingBalanceQuery->where('color_id', $colorId);
        }
        if ($sizeId) {
            $openingBalanceQuery->where('size_id', $sizeId);
        }
        if ($warehouseId) {
            $openingBalanceQuery->where('warehouse_id', $warehouseId);
        }

        $periodOpeningBalance = 0;
        if ($fromDate) {
            $periodOpeningBalance = (float) (clone $openingBalanceQuery)
                ->where('transaction_date', '<', $fromDate)
                ->selectRaw('COALESCE(SUM(qty_in), 0) - COALESCE(SUM(qty_out), 0) AS opening_balance')
                ->value('opening_balance');
        }

        // 5. Query Ledger Rows
        $query = DB::table('stock_transactions as st')
            ->leftJoin('purchases as p', function ($join) {
                $join->on('p.id', '=', 'st.reference_id')
                    ->where('st.reference_type', 'Purchase');
            })
            ->leftJoin('suppliers as p_sup', 'p_sup.id', '=', 'p.supplier_id')
            ->leftJoin('invoices as inv', function ($join) {
                $join->on('inv.id', '=', 'st.reference_id')
                    ->where('st.reference_type', 'Issue');
            })
            ->leftJoin('clients as cust', 'cust.id', '=', 'inv.client_id')
            ->leftJoin('grns as g', function ($join) {
                $join->on('g.id', '=', 'st.reference_id')
                    ->where('st.reference_type', 'GRN');
            })
            ->leftJoin('suppliers as g_sup', 'g_sup.id', '=', 'g.supplier_id')
            ->leftJoin('sales_returns as sr', function ($join) {
                $join->on('sr.id', '=', 'st.reference_id')
                    ->where(function($sq) {
                        $sq->where('st.reference_type', 'SalesReturn')
                           ->orWhere('st.reference_type', 'Return');
                    });
            })
            ->leftJoin('clients as sr_cust', 'sr_cust.id', '=', 'sr.client_id')
            ->leftJoin('wastages as w', function ($join) {
                $join->on('w.id', '=', 'st.reference_id')
                    ->where('st.reference_type', 'Wastage');
            })
            ->leftJoin('employees as w_emp', 'w_emp.id', '=', 'w.auditor_id')
            ->leftJoin('stock_adjustments as sa', function ($join) {
                $join->on('sa.id', '=', 'st.reference_id')
                    ->where(function($sq) {
                        $sq->where('st.reference_type', 'StockAdjustment')
                           ->orWhere('st.reference_type', 'Adjustment');
                    });
            })
            ->leftJoin('employees as sa_emp', 'sa_emp.id', '=', 'sa.conducted_by')
            ->leftJoin('colors as col', 'col.id', '=', 'st.color_id')
            ->leftJoin('sizes as sz', 'sz.id', '=', 'st.size_id')
            ->leftJoin('warehouses as wh', 'wh.id', '=', 'st.warehouse_id')
            ->select(
                'st.id',
                'st.transaction_date',
                'st.transaction_type',
                'st.reference_type',
                'st.reference_id',
                'st.color_id',
                'st.size_id',
                'st.warehouse_id',
                'st.created_at',
                'col.title as color_title',
                'sz.title as size_title',
                'wh.name as warehouse_name',
                DB::raw("
                    CASE
                        WHEN st.reference_type = 'Purchase' THEN p.invoiceno
                        WHEN st.reference_type = 'Issue' THEN inv.invoice_no
                        WHEN st.reference_type = 'GRN' THEN g.grn_no
                        WHEN st.reference_type = 'SalesReturn' OR st.reference_type = 'Return' THEN sr.return_no
                        WHEN st.reference_type = 'Wastage' THEN w.audit_number
                        WHEN st.reference_type = 'StockAdjustment' OR st.reference_type = 'Adjustment' OR st.transaction_type = 'Adjustment' THEN sa.adjustment_no
                        WHEN st.reference_type = 'Production' OR st.transaction_type = 'Production' THEN CONCAT('PRD-', st.id)
                        ELSE NULL
                    END AS reference_no
                "),
                DB::raw("
                    CASE
                        WHEN st.reference_type = 'Purchase' THEN COALESCE(p_sup.org_name, p_sup.name)
                        WHEN st.reference_type = 'Issue' THEN cust.name
                        WHEN st.reference_type = 'GRN' THEN COALESCE(g_sup.org_name, g_sup.name)
                        WHEN st.reference_type = 'SalesReturn' OR st.reference_type = 'Return' THEN sr_cust.name
                        WHEN st.reference_type = 'Wastage' THEN w_emp.full_name
                        WHEN st.reference_type = 'StockAdjustment' OR st.reference_type = 'Adjustment' OR st.transaction_type = 'Adjustment' THEN CONCAT('Conducted: ', COALESCE(sa_emp.full_name, sa_emp.name, 'N/A'))
                        WHEN st.reference_type = 'Production' OR st.transaction_type = 'Production' THEN 'In-House Production'
                        ELSE NULL
                    END AS party_name
                "),
                'st.qty_in',
                'st.qty_out'
            )
            ->where('st.item_id', $itemid)
            ->where('st.status', 'active');

        if ($fromDate && $toDate) {
            $query->whereBetween('st.transaction_date', [$fromDate, $toDate]);
        } elseif ($fromDate) {
            $query->where('st.transaction_date', '>=', $fromDate);
        } elseif ($toDate) {
            $query->where('st.transaction_date', '<=', $toDate);
        }

        if ($colorId) {
            $query->where('st.color_id', $colorId);
        }
        if ($sizeId) {
            $query->where('st.size_id', $sizeId);
        }
        if ($warehouseId) {
            $query->where('st.warehouse_id', $warehouseId);
        }
        if ($trxType) {
            if ($trxType === 'Sale') {
                $query->where(function($q) {
                    $q->where('st.transaction_type', 'Issue')->orWhere('st.reference_type', 'Issue');
                });
            } else {
                $query->where(function($q) use ($trxType) {
                    $q->where('st.transaction_type', $trxType)->orWhere('st.reference_type', $trxType);
                });
            }
        }

        $rows = $query->orderBy('st.transaction_date', 'asc')
            ->orderBy('st.id', 'asc')
            ->get();

        // 6. Fetch details in batch
        $grnIds = $rows->where('reference_type', 'GRN')->pluck('reference_id')->filter()->unique()->toArray();
        $purchaseIds = $rows->where('reference_type', 'Purchase')->pluck('reference_id')->filter()->unique()->toArray();
        $invoiceIds = $rows->where('reference_type', 'Issue')->pluck('reference_id')->filter()->unique()->toArray();
        $returnIds = $rows->whereIn('reference_type', ['SalesReturn', 'Return'])->pluck('reference_id')->filter()->unique()->toArray();
        $wastageIds = $rows->where('reference_type', 'Wastage')->pluck('reference_id')->filter()->unique()->toArray();

        $grnDetailsMap = [];
        if (!empty($grnIds)) {
            $gds = DB::table('grn_details')->whereIn('grn_id', $grnIds)->where('item_id', $itemid)->get();
            foreach ($gds as $gd) {
                $k = $gd->grn_id . '_' . ($gd->color_id ?? 0) . '_' . ($gd->size_id ?? 0);
                $grnDetailsMap[$k] = ['serial_no' => $gd->serial_no, 'rate' => floatval($gd->unit_cost), 'total' => floatval($gd->total_cost)];
            }
        }

        $purchaseDetailsMap = [];
        if (!empty($purchaseIds)) {
            $pds = DB::table('purchase_details')->whereIn('purchase_id', $purchaseIds)->where('item_id', $itemid)->get();
            foreach ($pds as $pd) {
                $k = $pd->purchase_id . '_' . ($pd->color_id ?? 0) . '_' . ($pd->size_id ?? 0);
                $purchaseDetailsMap[$k] = ['serial_no' => $pd->serial_no, 'rate' => floatval($pd->price), 'total' => floatval($pd->total_amount)];
            }
        }

        $invoiceDetailsMap = [];
        if (!empty($invoiceIds)) {
            $inds = DB::table('invoice_details')->whereIn('invoice_id', $invoiceIds)->where('item_id', $itemid)->get();
            foreach ($inds as $ind) {
                $k = $ind->invoice_id . '_' . ($ind->color_id ?? 0) . '_' . ($ind->size_id ?? 0);
                $invoiceDetailsMap[$k] = ['serial_no' => $ind->serial_no, 'rate' => floatval($ind->amount), 'total' => floatval($ind->total_amount)];
            }
        }

        $returnDetailsMap = [];
        if (!empty($returnIds)) {
            $srds = DB::table('sales_return_details')->whereIn('sales_return_id', $returnIds)->where('item_id', $itemid)->get();
            foreach ($srds as $srd) {
                $k = $srd->sales_return_id . '_' . ($srd->color_id ?? 0) . '_' . ($srd->size_id ?? 0);
                $returnDetailsMap[$k] = ['serial_no' => $srd->serial_no, 'rate' => floatval($srd->unit_price), 'total' => floatval($srd->total_amount)];
            }
        }

        $wastageDetailsMap = [];
        if (!empty($wastageIds)) {
            $wds = DB::table('wastage_details')->whereIn('wastage_id', $wastageIds)->where('item_id', $itemid)->get();
            foreach ($wds as $wd) {
                $k = $wd->wastage_id . '_' . ($wd->color_id ?? 0) . '_' . ($wd->size_id ?? 0);
                $wastageDetailsMap[$k] = ['serial_no' => $wd->serial_no, 'rate' => floatval($wd->unit_price), 'total' => floatval($wd->total_amount)];
            }
        }

        $adjustmentIds = $rows->whereIn('reference_type', ['StockAdjustment', 'Adjustment'])->pluck('reference_id')->filter()->unique()->toArray();
        $adjustmentDetailsMap = [];
        if (!empty($adjustmentIds)) {
            $sads = DB::table('stock_adjustment_details')->whereIn('stock_adjustment_id', $adjustmentIds)->where('item_id', $itemid)->get();
            foreach ($sads as $sad) {
                $k = $sad->stock_adjustment_id . '_' . ($sad->color_id ?? 0) . '_' . ($sad->size_id ?? 0);
                $adjustmentDetailsMap[$k] = ['rate' => floatval($sad->unit_cost), 'total' => floatval($sad->total_amount)];
            }
        }

        $periodQtyIn = 0;
        $periodQtyOut = 0;
        $runningBalance = $periodOpeningBalance;

        $processedRows = [];
        foreach ($rows as $row) {
            $runningBalance += (float)$row->qty_in - (float)$row->qty_out;
            $row->balance = $runningBalance;
            $periodQtyIn += (float)$row->qty_in;
            $periodQtyOut += (float)$row->qty_out;

            // Resolve detail
            $key = $row->reference_id . '_' . ($row->color_id ?? 0) . '_' . ($row->size_id ?? 0);
            $detail = null;
            if ($row->reference_type === 'GRN') $detail = $grnDetailsMap[$key] ?? null;
            elseif ($row->reference_type === 'Purchase') $detail = $purchaseDetailsMap[$key] ?? null;
            elseif ($row->reference_type === 'Issue') $detail = $invoiceDetailsMap[$key] ?? null;
            elseif (in_array($row->reference_type, ['SalesReturn', 'Return'])) $detail = $returnDetailsMap[$key] ?? null;
            elseif ($row->reference_type === 'Wastage') $detail = $wastageDetailsMap[$key] ?? null;
            elseif (in_array($row->reference_type, ['StockAdjustment', 'Adjustment'])) $detail = $adjustmentDetailsMap[$key] ?? null;

            $rawSerial = $detail['serial_no'] ?? null;
            $serials = [];
            if (!empty($rawSerial)) {
                $parts = preg_split('/[\r\n,]+/', $rawSerial, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($parts as $p) {
                    $t = trim($p);
                    if (!empty($t)) $serials[] = $t;
                }
            }

            $count = count($serials);
            $vPrice = $variantPriceMap[($row->color_id ?? 0) . '_' . ($row->size_id ?? 0)] ?? ['purchase_price' => $costPrice, 'selling_price' => $sellPrice];
            $rowCostPrice = $vPrice['purchase_price'] ?: $costPrice;
            $rowSellPrice = $vPrice['selling_price'] ?: $sellPrice;

            $row->rate = $detail && !empty($detail['rate']) ? $detail['rate'] : ($row->qty_in > 0 ? $rowCostPrice : $rowSellPrice);
            $row->total_amount = $detail && !empty($detail['total']) ? $detail['total'] : round(($row->qty_in > 0 ? $row->qty_in : $row->qty_out) * $row->rate, 2);
            $row->has_serial = $count > 0;
            $row->serial_count = $count;
            $row->serials = $serials;
            $row->serial_preview = $count > 0 ? ($count > 1 ? $serials[0] . ' +' . ($count - 1) . ' more' : $serials[0]) : '';
            $row->transaction_date_formatted = date('d M, Y', strtotime($row->transaction_date));
            $row->created_time = $row->created_at ? date('h:i A', strtotime($row->created_at)) : '';

            $processedRows[] = $row;
        }

        // Prepend opening row if date filter applies
        if ($fromDate) {
            $openingRow = (object)[
                'id'                         => 0,
                'transaction_date'           => $fromDate,
                'transaction_date_formatted' => date('d M, Y', strtotime($fromDate)),
                'created_time'               => '',
                'transaction_type'           => 'Opening Balance',
                'reference_type'             => 'Opening',
                'reference_no'               => '-',
                'party_name'                 => '-',
                'warehouse_name'             => '-',
                'color_title'                => null,
                'size_title'                 => null,
                'has_serial'                 => false,
                'serial_count'               => 0,
                'serials'                    => [],
                'serial_preview'             => '',
                'rate'                       => $costPrice,
                'total_amount'               => round($periodOpeningBalance * $costPrice, 2),
                'qty_in'                     => 0,
                'qty_out'                    => 0,
                'balance'                    => $periodOpeningBalance
            ];
            array_unshift($processedRows, $openingRow);
        }

        return [
            'item' => $item,
            'metrics' => [
                'total_qty_in'           => $lifetimeIn,
                'total_sold'             => $lifetimeSold,
                'total_wastage'          => $lifetimeWastage,
                'total_returned'         => $lifetimeReturned,
                'current_stock'          => $currentStock,
                'stock_purchase_value'   => $stockPurchaseValue,
                'stock_selling_value'    => $stockSellingValue,
                'potential_profit'       => $potentialProfit,
                'period_opening_balance' => $periodOpeningBalance,
                'period_qty_in'          => $periodQtyIn,
                'period_qty_out'         => $periodQtyOut,
                'period_closing_balance' => $runningBalance,
            ],
            'variants_breakdown' => $variantsBreakdown,
            'datas' => $processedRows
        ];
    }

    public function getIncomeStatement($searchdata)
    {
        // 1. Date Range Handling (Default: Current Month)
        $from = !empty($searchdata['from_date'])
            ? vue_to_server_date($searchdata['from_date'])
            : date('Y-m-01');

        $to = !empty($searchdata['to_date'])
            ? vue_to_server_date($searchdata['to_date'])
            : date('Y-m-t');

        // Optional filters
        $clientId   = $searchdata['client_id'] ?? null;
        $categoryId = $searchdata['category_id'] ?? null;
        $itemId     = $searchdata['item_id'] ?? null;
        $saleType   = $searchdata['sale_type'] ?? 'all';
        $invoiceNo  = !empty($searchdata['invoice_no']) ? trim($searchdata['invoice_no']) : null;

        // 2. Pre-cache Item Cost Mappings to avoid N+1 queries
        // a) Variant-level item prices: (item_id_color_id_size_id) -> purchase_price
        $itemPrices = ItemPrice::where('status', 'active')
            ->select('item_id', 'color_id', 'size_id', 'purchase_price', 'selling_price')
            ->get();

        $variantCostMap = [];
        $itemDefaultCostFromVariants = [];
        foreach ($itemPrices as $ip) {
            $key = ($ip->item_id ?? 0) . '_' . ($ip->color_id ?? 0) . '_' . ($ip->size_id ?? 0);
            $cost = floatval($ip->purchase_price);
            $variantCostMap[$key] = $cost;
            if (!isset($itemDefaultCostFromVariants[$ip->item_id]) && $cost > 0) {
                $itemDefaultCostFromVariants[$ip->item_id] = $cost;
            }
        }

        // b) Latest purchase details cost
        $latestPurchases = PurchaseDetail::whereNull('deleted_at')
            ->select('item_id', 'color_id', 'size_id', 'price', 'id')
            ->orderBy('id', 'desc')
            ->get();

        $purchaseDetailCostMap = [];
        $itemDefaultCostFromPurchase = [];
        foreach ($latestPurchases as $pd) {
            $key = ($pd->item_id ?? 0) . '_' . ($pd->color_id ?? 0) . '_' . ($pd->size_id ?? 0);
            if (!isset($purchaseDetailCostMap[$key])) {
                $purchaseDetailCostMap[$key] = floatval($pd->price);
            }
            if (!isset($itemDefaultCostFromPurchase[$pd->item_id])) {
                $itemDefaultCostFromPurchase[$pd->item_id] = floatval($pd->price);
            }
        }

        // c) Base Items cost (opening_rate)
        $baseItems = Item::select('id', 'title', 'barcode', 'category_id', 'unit_id', 'opening_rate')
            ->with(['category:id,title', 'unit:id,title'])
            ->get()
            ->keyBy('id');

        // Helper closure to resolve unit purchase price
        $resolvePurchaseCost = function ($itemId, $colorId, $sizeId) use (
            $variantCostMap,
            $purchaseDetailCostMap,
            $itemDefaultCostFromVariants,
            $itemDefaultCostFromPurchase,
            $baseItems
        ) {
            $key = ($itemId ?: 0) . '_' . ($colorId ?: 0) . '_' . ($sizeId ?: 0);

            // 1. Check exact variant cost in ItemPrice
            if (isset($variantCostMap[$key]) && $variantCostMap[$key] > 0) {
                return $variantCostMap[$key];
            }

            // 2. Check exact variant cost in PurchaseDetail
            if (isset($purchaseDetailCostMap[$key]) && $purchaseDetailCostMap[$key] > 0) {
                return $purchaseDetailCostMap[$key];
            }

            // 3. Check item-level variant cost
            if (isset($itemDefaultCostFromVariants[$itemId]) && $itemDefaultCostFromVariants[$itemId] > 0) {
                return $itemDefaultCostFromVariants[$itemId];
            }

            // 4. Check item-level purchase detail cost
            if (isset($itemDefaultCostFromPurchase[$itemId]) && $itemDefaultCostFromPurchase[$itemId] > 0) {
                return $itemDefaultCostFromPurchase[$itemId];
            }

            // 5. Check base item model opening_rate
            if ($itemId && isset($baseItems[$itemId])) {
                $item = $baseItems[$itemId];
                if (!empty($item->opening_rate) && floatval($item->opening_rate) > 0) {
                    return floatval($item->opening_rate);
                }
            }

            return 0.0;
        };

        // 3. Query Invoices for the Period
        $invQuery = Invoice::query()
            ->whereNull('invoices.deleted_at')
            ->where('invoices.status', 'active')
            ->whereBetween('invoices.invoice_date', [$from, $to]);

        if ($clientId) {
            $invQuery->where('invoices.client_id', $clientId);
        }
        if ($invoiceNo) {
            $invQuery->where('invoices.invoice_no', 'like', "%{$invoiceNo}%");
        }
        if ($categoryId) {
            $invQuery->whereHas('invoice_details.item', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }
        if ($itemId) {
            $invQuery->whereHas('invoice_details', function ($q) use ($itemId) {
                $q->where('item_id', $itemId);
            });
        }
        if ($saleType === 'pos') {
            $invQuery->whereHas('invoice_details', function ($q) {
                $q->where('reference', 'POS Sale');
            });
        } elseif ($saleType === 'general') {
            $invQuery->whereDoesntHave('invoice_details', function ($q) {
                $q->where('reference', 'POS Sale');
            });
        }

        $invoices = $invQuery->with([
            'client:id,clientid,name,mobile,address',
            'invoice_details' => function ($q) use ($categoryId, $itemId) {
                $q->with([
                    'item:id,title,barcode,category_id,unit_id,opening_rate',
                    'item.category:id,title',
                    'item.unit:id,title',
                    'color:id,title',
                    'size:id,title',
                ]);
                if ($itemId) {
                    $q->where('item_id', $itemId);
                }
                if ($categoryId) {
                    $q->whereHas('item', function ($iq) use ($categoryId) {
                        $iq->where('category_id', $categoryId);
                    });
                }
            }
        ])
        ->orderBy('invoice_date', 'desc')
        ->orderBy('id', 'desc')
        ->get();

        // 4. Calculate Sales, COGS & Profit per Invoice & Item
        $totalGrossSales  = 0;
        $totalDiscount    = 0;
        $totalVat         = 0;
        $totalNetSales    = 0;
        $totalPaid        = 0;
        $totalDue         = 0;
        $totalCogs        = 0;
        $totalGrossProfit = 0;
        $totalQtySold     = 0;

        $itemBreakdownMap = [];
        $processedInvoices = [];

        foreach ($invoices as $inv) {
            $invAmount   = floatval($inv->amount);
            $invPaid     = floatval($inv->paid_amount ?? 0);
            $invDue      = max(0, $invAmount - $invPaid);
            $invDiscount = floatval($inv->discount ?? 0);
            $invVat      = floatval($inv->vat ?? 0);

            $invLineSalesTotal = 0;
            $invLineCogsTotal  = 0;
            $invLineQtyTotal   = 0;
            $processedDetails  = [];

            foreach ($inv->invoice_details as $detail) {
                $qty = floatval($detail->qty ?? 1);
                $unitSalePrice = floatval($detail->amount ?? 0);
                $lineSaleAmount = floatval($detail->total_amount ?? ($qty * $unitSalePrice));

                $unitPurchaseCost = $resolvePurchaseCost($detail->item_id, $detail->color_id, $detail->size_id);
                $linePurchaseCost = $qty * $unitPurchaseCost;
                $lineProfit = $lineSaleAmount - $linePurchaseCost;
                $lineMarginPercent = $lineSaleAmount > 0 ? round(($lineProfit / $lineSaleAmount) * 100, 2) : 0;

                $invLineSalesTotal += $lineSaleAmount;
                $invLineCogsTotal  += $linePurchaseCost;
                $invLineQtyTotal   += $qty;

                // Detail item record
                $processedDetails[] = [
                    'id'                  => $detail->id,
                    'item_id'             => $detail->item_id,
                    'item_title'          => $detail->item ? $detail->item->title : ($detail->description ?: 'Item Sale'),
                    'barcode'             => $detail->item ? $detail->item->barcode : 'N/A',
                    'category_title'      => $detail->item && $detail->item->category ? $detail->item->category->title : 'N/A',
                    'unit_title'          => $detail->item && $detail->item->unit ? $detail->item->unit->title : 'Pcs',
                    'color_title'         => $detail->color ? $detail->color->title : null,
                    'size_title'          => $detail->size ? $detail->size->title : null,
                    'serial_no'           => $detail->serial_no,
                    'qty'                 => $qty,
                    'unit_purchase_cost'  => round($unitPurchaseCost, 2),
                    'unit_sale_price'     => round($unitSalePrice, 2),
                    'line_sale_amount'    => round($lineSaleAmount, 2),
                    'line_purchase_cost'  => round($linePurchaseCost, 2),
                    'line_profit'         => round($lineProfit, 2),
                    'line_margin_percent' => $lineMarginPercent,
                ];

                // Item Breakdown Accumulator
                $itemKey = ($detail->item_id ?? 0) . '_' . ($detail->color_id ?? 0) . '_' . ($detail->size_id ?? 0);
                if (!isset($itemBreakdownMap[$itemKey])) {
                    $itemBreakdownMap[$itemKey] = [
                        'item_id'            => $detail->item_id,
                        'item_title'         => $detail->item ? $detail->item->title : ($detail->description ?: 'Other Item'),
                        'barcode'            => $detail->item ? $detail->item->barcode : 'N/A',
                        'category_title'     => $detail->item && $detail->item->category ? $detail->item->category->title : 'N/A',
                        'unit_title'         => $detail->item && $detail->item->unit ? $detail->item->unit->title : 'Pcs',
                        'color_title'        => $detail->color ? $detail->color->title : null,
                        'size_title'         => $detail->size ? $detail->size->title : null,
                        'total_qty'          => 0,
                        'unit_purchase_cost' => round($unitPurchaseCost, 2),
                        'total_sales_amount' => 0,
                        'total_cost_amount'  => 0,
                        'total_profit'       => 0,
                        'margin_percent'     => 0,
                        'orders_count'       => 0,
                    ];
                }
                $itemBreakdownMap[$itemKey]['total_qty']          += $qty;
                $itemBreakdownMap[$itemKey]['total_sales_amount'] += $lineSaleAmount;
                $itemBreakdownMap[$itemKey]['total_cost_amount']  += $linePurchaseCost;
                $itemBreakdownMap[$itemKey]['total_profit']       += $lineProfit;
                $itemBreakdownMap[$itemKey]['orders_count']       += 1;
            }

            $invGrossSales = floatval($inv->original_amount ?? $invLineSalesTotal);
            if ($invGrossSales <= 0) {
                $invGrossSales = $invLineSalesTotal;
            }

            // Invoice Gross Profit = (Gross Sales - Discount) - COGS
            $invNetRevenue = max(0, $invGrossSales - $invDiscount + $invVat);
            $invProfit     = ($invGrossSales - $invDiscount) - $invLineCogsTotal;
            $invMarginPct  = $invNetRevenue > 0 ? round(($invProfit / $invNetRevenue) * 100, 2) : 0;

            $totalGrossSales += $invGrossSales;
            $totalDiscount   += $invDiscount;
            $totalVat        += $invVat;
            $totalNetSales   += $invAmount;
            $totalPaid       += $invPaid;
            $totalDue        += $invDue;
            $totalCogs       += $invLineCogsTotal;
            $totalGrossProfit+= $invProfit;
            $totalQtySold    += $invLineQtyTotal;

            $processedInvoices[] = [
                'id'             => $inv->id,
                'invoice_no'     => $inv->invoice_no,
                'invoice_date'   => $inv->invoice_date,
                'client_id'      => $inv->client_id,
                'client_name'    => $inv->client ? $inv->client->name : 'Walk-in Customer',
                'client_mobile'  => $inv->client ? $inv->client->mobile : 'N/A',
                'total_qty'      => $invLineQtyTotal,
                'gross_amount'   => round($invGrossSales, 2),
                'discount'       => round($invDiscount, 2),
                'vat'            => round($invVat, 2),
                'net_amount'     => round($invAmount, 2),
                'paid_amount'    => round($invPaid, 2),
                'due_amount'     => round($invDue, 2),
                'total_cogs'     => round($invLineCogsTotal, 2),
                'profit'         => round($invProfit, 2),
                'margin_percent' => $invMarginPct,
                'is_closed'      => $inv->is_closed,
                'details'        => $processedDetails,
            ];
        }

        // Finalize Item Breakdown list
        $itemBreakdownList = [];
        foreach ($itemBreakdownMap as $item) {
            $item['total_sales_amount'] = round($item['total_sales_amount'], 2);
            $item['total_cost_amount']  = round($item['total_cost_amount'], 2);
            $item['total_profit']       = round($item['total_profit'], 2);
            $item['avg_sale_price']     = $item['total_qty'] > 0 ? round($item['total_sales_amount'] / $item['total_qty'], 2) : 0;
            $item['margin_percent']     = $item['total_sales_amount'] > 0 ? round(($item['total_profit'] / $item['total_sales_amount']) * 100, 2) : 0;
            $itemBreakdownList[] = $item;
        }

        // Sort items by highest profit
        usort($itemBreakdownList, function ($a, $b) {
            return $b['total_profit'] <=> $a['total_profit'];
        });

        // 5. Operating Expenses & Deductions
        // a) Office & General Expenses
        $expenseQuery = ExpenseDetail::with([
            'expense:id,expenseid,expense_date,employee_id,approved_by,approved_date',
            'expense.employee:id,full_name',
            'account:id,account_code,account_name'
        ])
        ->whereNull('expense_details.deleted_at')
        ->whereHas('expense', function ($q) use ($from, $to) {
            $q->whereNull('deleted_at')
              ->whereBetween('expense_date', [$from, $to]);
        });

        $expenses = $expenseQuery->get()->map(function ($row) {
            return [
                'id'            => $row->id,
                'expense_id'    => $row->expense_id,
                'expense_no'    => $row->expense ? $row->expense->expenseid : 'EXP-' . $row->expense_id,
                'date'          => $row->expense ? $row->expense->expense_date : null,
                'account_name'  => $row->account ? ($row->account->account_code . ' - ' . $row->account->account_name) : 'General Expense',
                'employee_name' => $row->expense && $row->expense->employee ? $row->expense->employee->full_name : 'Office',
                'narration'     => $row->narration ?? 'General Expense',
                'amount'        => floatval($row->amount ?? 0),
            ];
        });
        $totalExpensesAmount = floatval($expenses->sum('amount'));

        // b) Employee Salary Sheets
        $salaryQuery = SalarySheetDetail::with([
            'salary_sheet:id,title,month,year,generated_date,approved_by',
            'employee:id,full_name,empid,designation_id',
            'employee.designation:id,title'
        ])
        ->whereNull('salary_sheet_details.deleted_at')
        ->whereHas('salary_sheet', function ($q) use ($from, $to) {
            $q->whereNull('deleted_at')
              ->whereBetween('generated_date', [$from, $to]);
        });

        $salaries = $salaryQuery->get()->map(function ($row) {
            return [
                'id'              => $row->id,
                'salary_sheet_id' => $row->salary_sheet_id,
                'sheet_title'     => $row->salary_sheet ? $row->salary_sheet->title : 'Salary Sheet',
                'month'           => $row->salary_sheet ? $row->salary_sheet->month : '',
                'year'            => $row->salary_sheet ? $row->salary_sheet->year : '',
                'date'            => $row->salary_sheet ? $row->salary_sheet->generated_date : null,
                'employee_id'     => $row->employee_id,
                'employee_name'   => $row->employee ? $row->employee->full_name : 'N/A',
                'designation'     => $row->employee && $row->employee->designation ? $row->employee->designation->title : 'N/A',
                'basic_salary'    => floatval($row->salary ?? 0),
                'additions'       => floatval(($row->commission ?? 0) + ($row->bonus ?? 0)),
                'deductions'      => floatval(($row->installment ?? 0) + ($row->deduct ?? 0)),
                'amount'          => floatval($row->total ?? 0),
                'is_paid'         => (int)($row->is_paid ?? 0),
            ];
        });
        $totalSalariesAmount = floatval($salaries->sum('amount'));

        // c) Employee Loan & Advance Information
        $loanQuery = LoanInfo::with(['employee:id,full_name,empid'])
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->whereBetween('trns_date', [$from, $to]);

        $loans = $loanQuery->get()->map(function ($row) {
            return [
                'id'                 => $row->id,
                'trnsid'             => $row->trnsid,
                'date'               => $row->trns_date,
                'return_date'        => $row->return_date,
                'employee_name'      => $row->employee ? $row->employee->full_name : 'N/A',
                'trns_type'          => $row->trns_type ?: 'Loan',
                'amount'             => floatval($row->amount ?? 0),
                'total_installment'  => $row->total_installment ?? 1,
                'installment_amount' => floatval($row->installment_amount ?? 0),
                'due_amount'         => floatval($row->due_amount ?? 0),
            ];
        });
        $totalLoansAmount = floatval($loans->sum('amount'));

        // d) Commissions (Agents & Employees)
        $commissionQuery = Commission::with([
            'employee:id,full_name',
            'agent:id,full_name,mobile',
            'client:id,name',
            'workorder:id,order_no'
        ])
        ->whereNull('deleted_at')
        ->where('status', 'active')
        ->where(function ($q) use ($from, $to) {
            $q->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
              ->orWhereBetween('approved_date', [$from, $to]);
        });

        $commissions = $commissionQuery->get()->map(function ($row) {
            $beneficiary = $row->agent ? ($row->agent->full_name . ' (Agent)') : ($row->employee ? ($row->employee->full_name . ' (Staff)') : ($row->reference_name ?: 'Reference'));
            return [
                'id'            => $row->id,
                'beneficiary'   => $beneficiary,
                'client_name'   => $row->client ? $row->client->name : 'N/A',
                'workorder_no'  => $row->workorder ? $row->workorder->order_no : 'N/A',
                'percentage'    => $row->percentage,
                'date'          => $row->approved_date ?: date('d M, Y', strtotime($row->created_at)),
                'amount'        => floatval($row->amount ?? 0),
            ];
        });
        $totalCommissionsAmount = floatval($commissions->sum('amount'));

        // 6. Final Summary Aggregations
        $totalDeductions  = $totalExpensesAmount + $totalSalariesAmount + $totalLoansAmount + $totalCommissionsAmount;
        $netProfit        = $totalGrossProfit - $totalDeductions;
        $netProfitMargin  = $totalNetSales > 0 ? round(($netProfit / $totalNetSales) * 100, 2) : 0;
        $grossProfitMargin= $totalNetSales > 0 ? round(($totalGrossProfit / $totalNetSales) * 100, 2) : 0;

        return response()->json([
            'from'              => $from,
            'to'                => $to,
            'summary'           => [
                'total_invoices'     => count($processedInvoices),
                'total_qty_sold'     => round($totalQtySold, 2),
                'gross_sales'        => round($totalGrossSales, 2),
                'total_discount'     => round($totalDiscount, 2),
                'total_vat'          => round($totalVat, 2),
                'net_sales'          => round($totalNetSales, 2),
                'total_paid'         => round($totalPaid, 2),
                'total_due'          => round($totalDue, 2),
                'total_cogs'         => round($totalCogs, 2),
                'gross_profit'       => round($totalGrossProfit, 2),
                'gross_profit_margin'=> $grossProfitMargin,

                // Deductions breakdown
                'total_expenses'     => round($totalExpensesAmount, 2),
                'total_salaries'     => round($totalSalariesAmount, 2),
                'total_loans'        => round($totalLoansAmount, 2),
                'total_commissions'  => round($totalCommissionsAmount, 2),
                'total_deductions'   => round($totalDeductions, 2),

                // Final Net Profit / Loss
                'net_profit'         => round($netProfit, 2),
                'net_profit_margin'  => $netProfitMargin,
                'is_profitable'      => $netProfit >= 0,
            ],
            'invoices'          => $processedInvoices,
            'item_breakdown'    => $itemBreakdownList,
            'expenses'          => $expenses,
            'salaries'          => $salaries,
            'loans'             => $loans,
            'commissions'       => $commissions,
        ]);
    }

    public function getExpenseStatement($searchdata)
    {
        $query = ExpenseDetail::with([
            'expense:id,expenseid,expense_date,employee_id,approved_by,approved_date',
            'expense.approved_admin:id,full_name',
            'expense.employee:id,full_name',
            'account:id,account_code,account_name'
        ])
            ->whereNull('expense_details.deleted_at')

            // 🔹 Paid Amount per Expense Detail
            ->addSelect([
                'paid_amount' => DB::table('payment_details as pd')
                    ->whereColumn('pd.reference_id', 'expense_details.id')
                    ->where('pd.reference_type', 'ExpenseDetail')
                    ->whereNull('pd.deleted_at')
                    ->selectRaw('COALESCE(SUM(pd.amount),0)')
            ]);

        // 🔹 Only Approved Expense
        $query->whereHas('expense', function ($q) {
            $q->whereNotNull('approved_by')
                ->whereNull('deleted_at');
        });

        // 🔹 Account Filter
        if (!empty($searchdata['account_id'])) {
            $query->where('account_id', $searchdata['account_id']);
        }

        // 🔹 Employee Filter
        if (!empty($searchdata['employee_id'])) {
            $query->whereHas('expense', function ($q) use ($searchdata) {
                $q->where('employee_id', $searchdata['employee_id']);
            });
        }

        // 🔹 Branch Filter
        if (!empty($searchdata['branch_id'])) {
            $query->whereHas('expense', function ($q) use ($searchdata) {
                $q->where('branch_id', $searchdata['branch_id']);
            });
        }

        // 🔹 Date Range Filter (Optional)
        if (!empty($searchdata['from_date']) && !empty($searchdata['to_date'])) {

            $from = vue_to_server_date($searchdata['from_date']);
            $to   = vue_to_server_date($searchdata['to_date']);

            $query->whereHas('expense', function ($q) use ($from, $to) {
                $q->whereBetween('expense_date', [$from, $to]);
            });
        }

        $details = $query->get();

        // 🔹 Add Due Amount Manually
        $details->transform(function ($item) {
            $item->due_amount = $item->amount - $item->paid_amount;
            return $item;
        });

        // 🔹 Totals
        $total_expense = $details->sum('amount');
        $total_paid    = $details->sum('paid_amount');
        $total_due     = $details->sum('due_amount');

        return response()->json([
            'details'        => $details,
            'total_expense'  => $total_expense,
            'total_paid'     => $total_paid,
            'total_due'      => $total_due,
        ]);
    }

    public function getCashBook($searchdata)
    {
        // 1️⃣ Date range
        $from = $searchdata['from_date'] ?? date('Y-m-01');
        $from = vue_to_server_date($from);

        $to = $searchdata['to_date'] ?? date('Y-m-t');
        $to = vue_to_server_date($to);

        // 2️⃣ Cash account ID
        $cashAccountId = Account::where('system_key_name', 'Cash')
            ->whereNull('deleted_at')
            ->value('id');

        if (!$cashAccountId) {
            return response()->json(['message' => 'Cash account not found'], 400);
        }

        // 3️⃣ Opening balance (soft delete safe)
        $opening_balance = $this->getOpeningBalance($cashAccountId, $from);

        // 4️⃣ Records within date range (soft delete safe)
        $records = VoucherDetail::with([
            'voucher' => function ($q) use ($from, $to) {
                $q->whereNull('vouchers.deleted_at')      // ✅ table name explicit
                    ->where('vouchers.status', 'active')
                    ->whereBetween('vouchers.voucher_date', [$from, $to]);
            }
        ])
            ->where('voucher_details.account_id', $cashAccountId)
            ->whereNull('voucher_details.deleted_at')      // ✅ table name explicit
            ->where('voucher_details.status', 'active')    // ✅ table name explicit
            ->join('vouchers', function ($join) use ($from, $to) {
                $join->on('vouchers.id', '=', 'voucher_details.voucher_id')
                    ->whereNull('vouchers.deleted_at')        // ✅ table name explicit
                    ->where('vouchers.status', 'active')
                    ->whereBetween('vouchers.voucher_date', [$from, $to]);
            })
            ->orderBy('vouchers.voucher_date', 'asc')
            ->orderBy('voucher_details.id', 'asc')
            ->get();

        // 5️⃣ Totals inside period
        $total_in = $records->sum('dr_amount');
        $total_out = $records->sum('cr_amount');

        // 6️⃣ Running balance calculation
        $running_balance = $opening_balance;

        $records = $records->map(function ($row) use (&$running_balance) {

            $dr = (float) $row->dr_amount;
            $cr = (float) $row->cr_amount;

            $running_balance = $running_balance + $dr - $cr;

            return [
                'date' => $row->voucher->voucher_date ?? null,
                'description' => $row->line_narration ?? ($row->voucher->narration ?? ''),
                'cash_in' => $dr,
                'cash_out' => $cr,
                'balance' => round($running_balance, 2),
            ];
        });

        $closing_balance = $running_balance;

        return response()->json([
            'opening_date' => date('01 M, Y', strtotime($from)),
            'from' => $from,
            'to' => $to,
            'opening_balance' => round($opening_balance, 2),
            'closing_balance' => round($closing_balance, 2),
            'total_in' => round($total_in, 2),
            'total_out' => round($total_out, 2),
            'records' => $records,
        ]);
    }

    public function getLedger($searchdata)
    {
        // current month first & last date if not provided
        $from = array_key_exists('from_date', $searchdata)
            ? vue_to_server_date($searchdata['from_date'])
            : date('Y-m-01');

        $to   = array_key_exists('to_date', $searchdata)
            ? vue_to_server_date($searchdata['to_date'])
            : date('Y-m-t');

        $account_id = $searchdata['account_id'] ?? null;

        if (!$account_id) {
            return response()->json([
                'message' => 'Account not selected'
            ], 400);
        }

        $account = Account::whereNull('deleted_at')->find($account_id);

        // Opening balance before period (must ignore soft deleted)
        $opening_balance = $this->getOpeningBalance($account_id, $from);

        // Period transactions (ignore soft deleted voucher_details and vouchers)
        $entries = VoucherDetail::with([
            'voucher' => function ($q) {
                $q->whereNull('deleted_at');
            }
        ])
            ->where('voucher_details.account_id', $account_id)
            ->whereNull('voucher_details.deleted_at')
            ->join('vouchers', function ($join) use ($from, $to) {
                $join->on('vouchers.id', '=', 'voucher_details.voucher_id')
                    ->whereNull('vouchers.deleted_at')
                    ->whereBetween('vouchers.voucher_date', [$from, $to]);
            })
            ->orderBy('vouchers.voucher_date', 'asc')
            ->orderBy('voucher_details.id', 'asc')
            ->select('voucher_details.*')
            ->get();

        // Running balance calculation
        $runningBalance = (float) $opening_balance;

        $entries = $entries->map(function ($row) use (&$runningBalance, $account) {

            $debit  = (float) $row->dr_amount;
            $credit = (float) $row->cr_amount;

            // Account type wise balance rule
            if (in_array($account->account_type, ['Liability', 'Capital', 'Income'])) {
                $runningBalance = $runningBalance + $credit - $debit;
            } else {
                $runningBalance = $runningBalance + $debit - $credit;
            }

            $row->running_balance = $runningBalance;

            return $row;
        });

        return response()->json([

            // summary
            'opening_date'     => date('01 M, Y', strtotime($from)),
            'opening_balance'  => (float) $opening_balance,
            'total_debit'      => (float) $entries->sum('dr_amount'),
            'total_credit'     => (float) $entries->sum('cr_amount'),
            'closing_balance'  => (float) $runningBalance,

            // entries
            'entries'          => $entries,

            // account info
            'account_name'     => $account->account_name ?? '',
            'account_type'     => $account->account_type ?? ''
        ]);
    }

    public function getTrialBalance($searchdata)
    {
        $from = $searchdata['from_date'] ?? null;
        $from = $from ? vue_to_server_date($from) : null;

        $to   = $searchdata['to_date'] ?? null;
        $to   = $to ? vue_to_server_date($to) : null;

        // Get all active accounts (ignore soft deleted)
        $accounts = Account::whereNull('deleted_at')
            ->orderBy('account_type')
            ->get()
            ->map(function ($account) use ($from, $to) {

                // Base query for VoucherDetail (soft delete safe)
                $vdQuery = VoucherDetail::where('account_id', $account->id)
                    ->whereNull('voucher_details.deleted_at')
                    ->where('voucher_details.status', 'active')
                    ->whereHas('voucher', function ($q) use ($from, $to) {
                        $q->whereNull('vouchers.deleted_at')
                            ->where('vouchers.status', 'active');

                        if ($from && $to) {
                            $q->whereBetween('voucher_date', [$from, $to]);
                        }
                    });

                // Sum debit & credit
                $debit  = (float) $vdQuery->sum('dr_amount');
                $credit = (float) $vdQuery->sum('cr_amount');

                return [
                    'parent_id'    => $account->parent_id,
                    'account_id'   => $account->id,
                    'account_type' => $account->account_type,
                    'account_name' => $account->account_name,
                    'debit'        => $debit,
                    'credit'       => $credit,
                ];
            });

        return response()->json([
            'accounts'     => $accounts,
            'total_debit'  => (float) $accounts->sum('debit'),
            'total_credit' => (float) $accounts->sum('credit'),
        ]);
    }

    private function getClientReceived($clientId, $from, $to)
    {
        $result = Payment::where('client_id', $clientId)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->whereBetween('payment_date', [$from, $to])
            ->selectRaw("
            COALESCE(SUM(amount), 0) as receive_amount,
            COALESCE(SUM(discount), 0) as discount_amount
        ")
            ->first();

        return [
            'receive_amount'  => (float) $result->receive_amount,
            'discount_amount' => (float) $result->discount_amount,
        ];
    }

    public function getReceivable($searchdata)
    {
        // current month first & last date if not provided
        $from = array_key_exists('from_date', $searchdata) ? vue_to_server_date($searchdata['from_date']) : date('Y-m-01');
        $to   = array_key_exists('to_date', $searchdata) ? vue_to_server_date($searchdata['to_date']) : date('Y-m-t');
        $receivables = [];

        // ---------- Invoice Receivable ----------
        $iquery = DB::table('invoices')
            ->leftJoin('clients', 'invoices.client_id', '=', 'clients.id')
            ->select(
                'invoices.client_id',
                'clients.clientid as clientid',
                'clients.mobile as client_mobile',
                'clients.name as client_name',
                DB::raw('SUM(invoices.amount) as billed_amount')
            )
            ->where('invoices.is_closed', 0)
            ->whereNull('invoices.deleted_at')
            ->whereBetween('invoices.invoice_date', [$from, $to])
            ->groupBy('invoices.client_id', 'clients.name');

        if (!empty($from) && !empty($to)) {
            $iquery->whereBetween('invoices.invoice_date', [$from, $to]);
        }

        if (!empty($searchdata['client_id'])) {
            $iquery->where('invoices.client_id', $searchdata['client_id']);
        }

        $invoiceReceivable = $iquery->get();

        // ---------- Merge ----------
        foreach ($invoiceReceivable as $row) {
            $received_amount = $this->getClientReceived($row->client_id, $from, $to);
            $due_amount = ($row->billed_amount - ($received_amount['receive_amount'] + $received_amount['discount_amount']));

            if ($due_amount <= 0) {
                continue;
            }

            if (!isset($receivables[$row->client_id])) {
                $receivables[$row->client_id] = [
                    'clientid'          => $row->clientid,
                    'client_name'       => $row->client_name,
                    'client_mobile'     => $row->client_mobile,
                    'bill_amount'     => (float) $row->billed_amount,
                    'discount_amount'   => (float) $received_amount['discount_amount'],
                    'received_amount'   => (float) $received_amount['receive_amount'],
                    'invoice_due'       => (float) $due_amount,
                ];
            }
        }

        // ---------- Calculate totals ----------
        $collection = collect($receivables)->values();

        $total_bill_amount = $collection->sum('bill_amount');
        $total_received_amount = $collection->sum('received_amount');
        $total_discount_amount = $collection->sum('discount_amount');
        $total_invoice_due   = $collection->sum('invoice_due');
        $grand_total         = $total_bill_amount - $total_received_amount;

        // ---------- Final formatting ----------
        $data = $collection->map(function ($item) {
            return [
                'clientid'          => $item['clientid'],
                'client_name'       => $item['client_name'] ?? 'Unknown',
                'client_mobile'     => $item['client_mobile'] ?? '',
                'bill_amount'       => (float) $item['bill_amount'],
                'received_amount'   => (float) $item['received_amount'],
                'discount_amount'   => (float) $item['discount_amount'],
                'total_due'         => (float) $item['invoice_due'],
            ];
        });

        return response()->json([
            'from'                 => $from,
            'to'                   => $to,
            'total_bill_amount'    => $total_bill_amount,
            'total_received_amount' => $total_received_amount,
            'total_discount_amount' => $total_discount_amount,
            'grand_total'          => $grand_total,
            'receivables'          => $data
        ]);
    }

    private function getPaidAmount($referenceType, $referenceId)
    {
        return PaymentDetail::where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->sum('amount');
    }

    public function getPayable($searchdata)
    {
        $from = $searchdata['from_date'] ?? date('Y-m-01');
        $from = vue_to_server_date($from);

        $to = $searchdata['to_date'] ?? date('Y-m-t');
        $to = vue_to_server_date($to);

        $supplier_id        = $searchdata['supplier_id'] ?? null;
        $employee_id        = $searchdata['employee_id'] ?? null;
        $agent_id           = $searchdata['agent_id'] ?? null;

        $payables = collect([]);

        //   Purchase Payable (Supplier)
        $purchaseQuery = Purchase::with('supplier')
            ->where('is_closed', 0)
            ->whereNull('deleted_at')
            ->whereBetween('purchase_date', [$from, $to]);

        if ($supplier_id) {
            $purchaseQuery->where('supplier_id', $supplier_id);
        }

        $purchase = $purchaseQuery->get()->map(function ($row) {
            $paid = $this->getPaidAmount('Purchase', $row->id);

            return [
                'reference_type' => 'Purchase',
                'reference_id'   => $row->id,
                'name'           => $row->supplier->org_name ?? 'Unknown Supplier',
                'source'         => 'Purchase',
                'date'           => $row->purchase_date,
                'payable'        => (float) $row->total_amount,
                'paid'           => (float) $paid,
                'outstanding'    => round($row->total_amount - $paid, 2),
            ];
        })->filter(fn($r) => $r['outstanding'] > 0);

        $payables = $payables->merge($purchase);

        // Expense Payable (Employee / Office)
        $expenseDetailQuery = ExpenseDetail::with([
            'expense.employee'
        ])
            ->where('is_closed', 0)
            ->whereNull('deleted_at')
            ->whereHas('expense', function ($q) use ($from, $to, $employee_id) {
                $q->whereNotNull('approved_by')
                    ->whereNull('deleted_at')
                    ->whereBetween('expense_date', [$from, $to]);

                if ($employee_id) {
                    $q->where('employee_id', $employee_id);
                }
            });

        $expenseDetails = $expenseDetailQuery->get()->map(function ($row) {

            $paid = $this->getPaidAmount('ExpenseDetail', $row->id);
            $payable = (float) $row->amount;

            return [
                'reference_type' => 'ExpenseDetail',
                'reference_id'   => $row->id,
                'name'           => $row->expense->employee_id
                    ? ($row->expense->employee->full_name ?? '')
                    : 'Office Expense',
                'source'         => $row->expense->employee_id
                    ? 'Employee Expense'
                    : 'Office Expense',
                'date'           => $row->expense->expense_date,
                'payable'        => $payable,
                'paid'           => (float) $paid,
                'outstanding'    => round($payable - $paid, 2),
            ];
        })
            ->filter(fn($r) => $r['outstanding'] > 0);

        $payables = $payables->merge($expenseDetails);

        // salary sheet
        $salaryQuery = SalarySheetDetail::with(['employee', 'salary_sheet'])
            ->whereNull('salary_sheet_details.deleted_at')
            ->where('is_paid', 0)
            ->whereHas('salary_sheet', function ($q) use ($from, $to) {
                $q->whereNotNull('approved_by')
                    ->whereNull('deleted_at')
                    ->whereBetween('generated_date', [$from, $to]);
            });

        if ($employee_id) {
            $salaryQuery->where('employee_id', $employee_id);
        }

        $salary = $salaryQuery->get()->map(function ($row) {

            $amount = (float) ($row->total ?? 0);

            return [
                'reference_type' => 'SalarySheetDetail',
                'reference_id'   => $row->id,
                'name'           => $row->employee->full_name ?? '',
                'source'         => 'Salary Sheet',
                'date'           => $row->salary_sheet->generated_date ?? null,
                'payable'        => $amount,
                'paid'           => 0,
                'outstanding'    => $amount,
            ];
        })
            ->filter(fn($r) => $r['outstanding'] > 0);

        $payables = $payables->merge($salary);

        // Commission Payable
        $commissionQuery = Commission::with(['employee', 'agent'])
            ->whereNull('deleted_at')
            ->where('is_closed', 0)
            ->whereNotNull('approved_by');

        if ($employee_id) {
            $commissionQuery->where('employee_id', $employee_id);
        }

        if ($agent_id) {
            $commissionQuery->where('agent_id', $agent_id);
        }

        // Date filter (created_at)
        $commissionQuery->whereBetween('created_at', [$from, $to]);

        $commissions = $commissionQuery->get()->map(function ($row) {
            $paid = $this->getPaidAmount('Commission', $row->id);
            $amount = (float) ($row->amount ?? 0);
            $name = $row->agent ? $row->agent->full_name : ($row->employee ? $row->employee->full_name : 'Reference Commission');

            return [
                'reference_type' => 'Commission',
                'reference_id'   => $row->id,
                'name'           => $name,
                'source'         => $row->agent ? 'Agent Commission' : 'Employee Commission',
                'date'           => date('d M, Y', strtotime($row->created_at)),
                'payable'        => $amount,
                'paid'           => (float) $paid,
                'outstanding'    => round($amount - $paid, 2),
            ];
        })
            ->filter(fn($r) => $r['outstanding'] > 0);

        $payables = $payables->merge($commissions);

        return response()->json([
            'from'              => $from,
            'to'                => $to,
            'total_payable'     => round($payables->sum('payable'), 2),
            'total_paid'        => round($payables->sum('paid'), 2),
            'total_outstanding' => round($payables->sum('outstanding'), 2),
            'payables'          => $payables,
        ]);
    }

    public function getBalancesheet($searchdata)
    {
        // date handling
        $from = !empty($searchdata['from_date'])
            ? vue_to_server_date($searchdata['from_date'])
            : null;

        $to = !empty($searchdata['to_date'])
            ? vue_to_server_date($searchdata['to_date'])
            : date('Y-m-d');

        // ASSETS (DR - CR) up to TO DATE
        $assets = Account::where('account_type', 'Asset')
            ->whereNull('deleted_at') // soft delete check
            ->get()
            ->map(function ($a) use ($to) {
                $query = VoucherDetail::where('account_id', $a->id)
                    ->whereNull('deleted_at') // soft delete check
                    ->whereHas('voucher', function ($q) use ($to) {
                        $q->where('voucher_date', '<=', $to)
                            ->whereNull('deleted_at'); // soft delete check
                    });

                $totalDr = (float) $query->clone()->sum('dr_amount');
                $totalCr = (float) $query->clone()->sum('cr_amount');

                return [
                    'code'      => $a->account_code,
                    'name'      => $a->account_name,
                    'dr_amount' => $totalDr,
                    'cr_amount' => $totalCr,
                    'balance'   => $totalDr - $totalCr,
                ];
            });

        //LIABILITIES (CR - DR) up to TO DATE
        $liabilities = Account::where('account_type', 'Liability')
            ->whereNull('deleted_at')
            ->get()
            ->map(function ($a) use ($to) {

                $query = VoucherDetail::where('account_id', $a->id)
                    ->whereNull('deleted_at')
                    ->whereHas(
                        'voucher',
                        fn($q) => $q->where('voucher_date', '<=', $to)
                            ->whereNull('deleted_at')
                    );

                $totalDr = (float) $query->clone()->sum('dr_amount');
                $totalCr = (float) $query->clone()->sum('cr_amount');
                $balance = $totalCr - $totalDr;

                return [
                    'code'      => $a->account_code,
                    'name'      => $a->account_name,
                    'dr_amount' => $totalDr,
                    'cr_amount' => $totalCr,
                    'balance'   => (float) $balance,
                ];
            });
        // dd($liabilities);

        // EQUITY (CR - DR) up to TO DATE
        $equity = Account::where('account_type', 'Equity')
            ->whereNull('deleted_at')
            ->get()
            ->map(function ($a) use ($to) {

                $query = VoucherDetail::where('account_id', $a->id)
                    ->whereNull('deleted_at')
                    ->whereHas('voucher', fn($q) => $q->where('voucher_date', '<=', $to)
                        ->whereNull('deleted_at'));

                $totalDr = (float) $query->clone()->sum('dr_amount');
                $totalCr = (float) $query->clone()->sum('cr_amount');
                $balance = $totalCr - $totalDr;

                return [
                    'code'      => $a->account_code,
                    'name'      => $a->account_name,
                    'dr_amount' => $totalDr,
                    'cr_amount' => $totalCr,
                    'balance'   => (float) $balance,
                ];
            });

        return response()->json([
            'from_date'                 => $from,
            'to_date'                   => $to,
            'assets'                     => $assets,
            'liabilities'                => $liabilities,
            'equity'                     => $equity,
            'total_assets'               => round($assets->sum('balance'), 2),
            'total_liabilities_equity'   => round($liabilities->sum('balance') + $equity->sum('balance'), 2),
        ]);
    }

    public function getClientLedger($searchdata)
    {
        $from = !empty($searchdata['from_date'])
            ? vue_to_server_date($searchdata['from_date'])
            : date('Y-m-01');

        $to = !empty($searchdata['to_date'])
            ? vue_to_server_date($searchdata['to_date'])
            : date('Y-m-t');

        $client_id = $searchdata['client_id'] ?? null;

        if (!$client_id) {
            return response()->json([
                'client'          => null,
                'opening_balance' => 0,
                'records'         => []
            ], 200);
        }

        $client = Client::find($client_id);
        if (!$client) {
            return response()->json([
                'client'          => null,
                'opening_balance' => 0,
                'records'         => []
            ], 200);
        }

        $receivableAccountId = Account::where('system_key_name', 'accounts-receivable')->first()?->id; // Accounts Receivable

        //Opening Balance (Before from_date)
        $openingBalance = VoucherDetail::query()
            ->join('vouchers', 'voucher_details.voucher_id', '=', 'vouchers.id')
            ->where('voucher_details.reference_type', 'Client')
            ->where('voucher_details.reference_id', $client_id)
            ->where('voucher_details.account_id', $receivableAccountId)
            ->whereDate('vouchers.voucher_date', '<', $from)
            ->sum(DB::raw('voucher_details.dr_amount - voucher_details.cr_amount'));

        // Ledger rows (from_date to to_date)
        $ledger = VoucherDetail::query()
            ->select([
                'voucher_details.id',
                'voucher_details.voucher_id',
                'voucher_details.account_id',
                'voucher_details.dr_amount',
                'voucher_details.cr_amount',
                'voucher_details.created_at',
                'vouchers.voucherno',
                'vouchers.voucher_date',
                'accounts.account_name',
                'accounts.account_type',
            ])
            ->join('vouchers', 'voucher_details.voucher_id', '=', 'vouchers.id')
            ->join('accounts', 'voucher_details.account_id', '=', 'accounts.id')
            ->where('voucher_details.reference_type', 'Client')
            ->where('voucher_details.reference_id', $client_id)
            ->whereBetween('vouchers.voucher_date', [$from, $to])
            ->orderBy('vouchers.voucher_date')
            ->orderBy('voucher_details.id')
            ->get();

        //Running Balance with Opening Balance
        $balance = (float) $openingBalance;

        $ledger = $ledger->map(function ($row) use (&$balance, $receivableAccountId) {

            if ((int)$row->account_id === $receivableAccountId) {
                $balance += (float)$row->dr_amount - (float)$row->cr_amount;
            }

            $row->running_balance = $balance;

            return $row;
        });

        // Prepend Opening Balance row
        $openingRow = (object) [
            'id'             => null,
            'voucher_id'     => null,
            'account_id'     => null,
            'dr_amount'      => 0,
            'cr_amount'      => 0,
            'created_at'     => null,
            'voucherno'      => null,
            'voucher_date'   => $from,
            'account_name'   => 'Previous Due',
            'account_type'   => null,
            'running_balance' => $openingBalance,
        ];

        $ledger->prepend($openingRow);

        return response()->json([
            'client'          => $client,
            'opening_balance' => $openingBalance,
            'records'         => $ledger
        ]);
    }

    public function getSupplierLedger($searchdata)
    {
        $from = !empty($searchdata['from_date'])
            ? vue_to_server_date($searchdata['from_date'])
            : date('Y-m-01');

        $to = !empty($searchdata['to_date'])
            ? vue_to_server_date($searchdata['to_date'])
            : date('Y-m-t');

        $supplier_id = $searchdata['supplier_id'] ?? null;

        if (!$supplier_id) {
            return response()->json([
                'supplier'        => null,
                'opening_balance' => 0,
                'records'         => []
            ], 200);
        }

        $supplier = Supplier::find($supplier_id);
        if (!$supplier) {
            return response()->json([
                'supplier'        => null,
                'opening_balance' => 0,
                'records'         => []
            ], 200);
        }

        $payableAccountId = Account::where('system_key_name', 'accounts-payable')->first()?->id; // Accounts Receivable

        /**
         * 🔹 Opening Balance (Before from_date)
         */
        $openingBalance = VoucherDetail::query()
            ->join('vouchers', 'voucher_details.voucher_id', '=', 'vouchers.id')
            ->where('voucher_details.reference_type', 'Supplier')
            ->where('voucher_details.reference_id', $supplier_id)
            ->where('voucher_details.account_id', $payableAccountId)
            ->whereDate('vouchers.voucher_date', '<', $from)
            ->sum(DB::raw('voucher_details.cr_amount - voucher_details.dr_amount'));

        /**
         * 🔹 Ledger rows (from_date to to_date)
         */
        $ledger = VoucherDetail::query()
            ->select([
                'voucher_details.id',
                'voucher_details.voucher_id',
                'voucher_details.account_id',
                'voucher_details.dr_amount',
                'voucher_details.cr_amount',
                'voucher_details.created_at',
                'vouchers.voucherno',
                'vouchers.voucher_date',
                'accounts.account_name',
                'accounts.account_type',
            ])
            ->join('vouchers', 'voucher_details.voucher_id', '=', 'vouchers.id')
            ->join('accounts', 'voucher_details.account_id', '=', 'accounts.id')
            ->where('voucher_details.reference_type', 'Supplier')
            ->where('voucher_details.reference_id', $supplier_id)
            ->whereBetween('vouchers.voucher_date', [$from, $to])
            ->orderBy('vouchers.voucher_date')
            ->orderBy('voucher_details.id')
            ->get();

        /**
         * 🔹 Running Balance with Opening Balance
         */
        $balance = (float) $openingBalance;

        $ledger = $ledger->map(function ($row) use (&$balance, $payableAccountId) {

            if ((int)$row->account_id === $payableAccountId) {
                $balance +=  (float)$row->cr_amount - (float)$row->dr_amount;
            }

            $row->running_balance = $balance;
            return $row;
        });

        /**
         * 🔹 Prepend Opening Balance row
         */
        $openingRow = (object) [
            'id'             => null,
            'voucher_id'     => null,
            'account_id'     => null,
            'dr_amount'      => 0,
            'cr_amount'      => 0,
            'created_at'     => null,
            'voucherno'      => null,
            'voucher_date'   => $from,
            'account_name'   => 'Previous Due',
            'account_type'   => null,
            'running_balance' => $openingBalance,
        ];

        $ledger->prepend($openingRow);

        return response()->json([
            'supplier'  => $supplier,
            'opening_balance' => $openingBalance,
            'records'         => $ledger
        ]);
    }

    public function getAgentLedger($searchdata)
    {
        $from = !empty($searchdata['from_date'])
            ? vue_to_server_date($searchdata['from_date'])
            : date('Y-m-01');

        $to = !empty($searchdata['to_date'])
            ? vue_to_server_date($searchdata['to_date'])
            : date('Y-m-t');

        $agent_id = $searchdata['agent_id'] ?? null;

        if (!$agent_id) {
            return response()->json([
                'type'    => 'error',
                'message' => 'Agent ID is required'
            ], 422);
        }

        $agent = Agent::find($agent_id);
        if (!$agent) {
            return response()->json([
                'status'  => false,
                'message' => 'Agent not found'
            ], 404);
        }

        $payableAccountId = Account::where('system_key_name', 'commission-payable')->first()?->id
            ?? Account::where('system_key_name', 'accounts-payable')->first()?->id;

        /**
         * 🔹 Opening Balance (Before from_date)
         */
        $openingBalance = VoucherDetail::query()
            ->join('vouchers', 'voucher_details.voucher_id', '=', 'vouchers.id')
            ->where('voucher_details.reference_type', 'Agent')
            ->where('voucher_details.reference_id', $agent_id)
            ->where('voucher_details.account_id', $payableAccountId)
            ->whereDate('vouchers.voucher_date', '<', $from)
            ->sum(DB::raw('voucher_details.cr_amount - voucher_details.dr_amount'));

        /**
         * 🔹 Ledger rows (from_date to to_date)
         */
        $ledger = VoucherDetail::query()
            ->select([
                'voucher_details.id',
                'voucher_details.voucher_id',
                'voucher_details.account_id',
                'voucher_details.dr_amount',
                'voucher_details.cr_amount',
                'voucher_details.created_at',
                'vouchers.voucherno',
                'vouchers.voucher_date',
                'accounts.account_name',
                'accounts.account_type',
            ])
            ->join('vouchers', 'voucher_details.voucher_id', '=', 'vouchers.id')
            ->join('accounts', 'voucher_details.account_id', '=', 'accounts.id')
            ->where('voucher_details.reference_type', 'Agent')
            ->where('voucher_details.reference_id', $agent_id)
            ->whereBetween('vouchers.voucher_date', [$from, $to])
            ->orderBy('vouchers.voucher_date')
            ->orderBy('voucher_details.id')
            ->get();

        /**
         * 🔹 Running Balance with Opening Balance
         */
        $balance = (float) $openingBalance;

        $ledger = $ledger->map(function ($row) use (&$balance, $payableAccountId) {

            if ((int)$row->account_id === (int)$payableAccountId) {
                $balance += (float)$row->cr_amount - (float)$row->dr_amount;
            }

            $row->running_balance = $balance;
            return $row;
        });

        /**
         * 🔹 Prepend Opening Balance row
         */
        $openingRow = (object) [
            'id'              => null,
            'voucher_id'      => null,
            'account_id'      => null,
            'dr_amount'       => 0,
            'cr_amount'       => 0,
            'created_at'      => null,
            'voucherno'       => null,
            'voucher_date'    => $from,
            'account_name'    => 'Previous Due',
            'account_type'    => null,
            'running_balance' => $openingBalance,
        ];

        $ledger->prepend($openingRow);

        return response()->json([
            'agent'           => $agent,
            'opening_balance' => $openingBalance,
            'records'         => $ledger
        ]);
    }

    public function getEmployeeLedger($searchdata)
    {
        $from = !empty($searchdata['from_date'])
            ? vue_to_server_date($searchdata['from_date'])
            : date('Y-m-01');

        $to = !empty($searchdata['to_date'])
            ? vue_to_server_date($searchdata['to_date'])
            : date('Y-m-t');

        $employee_id = $searchdata['employee_id'] ?? null;

        if (!$employee_id) {
            return response()->json([
                'type'    => 'error',
                'message' => 'Employee ID is required'
            ], 422);
        }

        $employee = Employee::find($employee_id);
        if (!$employee) {
            return response()->json([
                'status'  => false,
                'message' => 'Employee not found'
            ], 404);
        }

        $accountPayableId = Account::where('system_key_name', 'accounts-payable')->value('id');
        $payableAccountId = Account::where('system_key_name', 'salary-payable')->value('id');
        $bonuspayableAccountId = Account::where('system_key_name', 'bonus-payable')->value('id');
        $commissionPaableAccountID = Account::where('system_key_name', 'commission-payable')->value('id');
        $salaryDeductionAccountId = Account::where('system_key_name', 'employee-salary-deduction')->value('id');
        $loanAccountId    = Account::where('system_key_name', 'employee-loan-receivable')->value('id');

        if (!$payableAccountId || !$loanAccountId) {
            return response()->json([
                'status'  => false,
                'message' => 'Required accounts not configured (salary-payable / employee-loan-receivable)'
            ], 500);
        }

        /**
         * 🔹 Opening Balance (Before from_date)
         */
        $openingPayable = VoucherDetail::query()
            ->join('vouchers', 'voucher_details.voucher_id', '=', 'vouchers.id')
            ->where('voucher_details.reference_type', 'Employee')
            ->where('voucher_details.reference_id', $employee_id)
            ->whereIn('voucher_details.account_id', [$accountPayableId, $payableAccountId, $bonuspayableAccountId, $commissionPaableAccountID])
            ->whereDate('vouchers.voucher_date', '<', $from)
            ->sum(DB::raw('voucher_details.cr_amount - voucher_details.dr_amount'));

        $openingLoan = VoucherDetail::query()
            ->join('vouchers', 'voucher_details.voucher_id', '=', 'vouchers.id')
            ->where('voucher_details.reference_type', 'Employee')
            ->where('voucher_details.reference_id', $employee_id)
            ->whereIn('voucher_details.account_id', [$loanAccountId, $salaryDeductionAccountId])
            ->whereDate('vouchers.voucher_date', '<', $from)
            ->sum(DB::raw('voucher_details.dr_amount - voucher_details.cr_amount'));

        // Net Opening Balance:
        // (+) means company owes employee
        // (-) means employee owes company
        $openingBalance = (float)$openingPayable - (float)$openingLoan;

        /**
         * 🔹 Ledger rows (from_date to to_date)
         */
        $ledger = VoucherDetail::query()
            ->select([
                'voucher_details.id',
                'voucher_details.voucher_id',
                'voucher_details.account_id',
                'voucher_details.dr_amount',
                'voucher_details.cr_amount',
                'voucher_details.created_at',
                'vouchers.voucherno',
                'vouchers.voucher_date',
                'accounts.account_name',
                'accounts.account_type',
            ])
            ->join('vouchers', 'voucher_details.voucher_id', '=', 'vouchers.id')
            ->join('accounts', 'voucher_details.account_id', '=', 'accounts.id')
            ->where('voucher_details.reference_type', 'Employee')
            ->where('voucher_details.reference_id', $employee_id)
            ->whereIn('voucher_details.account_id', [$accountPayableId, $payableAccountId, $loanAccountId, $bonuspayableAccountId, $salaryDeductionAccountId, $commissionPaableAccountID])
            ->whereBetween('vouchers.voucher_date', [$from, $to])
            ->orderBy('vouchers.voucher_date')
            ->orderBy('voucher_details.id')
            ->get();

        /**
         * 🔹 Running Balance
         */
        $balance = (float) $openingBalance;

        $ledger = $ledger->map(function ($row) use (&$balance, $accountPayableId, $payableAccountId, $loanAccountId, $bonuspayableAccountId, $salaryDeductionAccountId, $commissionPaableAccountID) {

            if (
                (int)$row->account_id === (int)$accountPayableId ||
                (int)$row->account_id === (int)$payableAccountId ||
                (int)$row->account_id === (int) $bonuspayableAccountId ||
                (int)$row->account_id === (int) $commissionPaableAccountID
            ) {
                // Employee Payable (Liability): CR increases payable, DR decreases
                $balance += (float)$row->cr_amount - (float)$row->dr_amount;
            }

            if ((int)$row->account_id === (int)$loanAccountId || (int)$row->account_id === (int)$salaryDeductionAccountId) {
                // Employee Loan Receivable (Asset): DR increases loan (employee owes), CR decreases
                $balance -= (float)$row->dr_amount - (float)$row->cr_amount;
            }

            $row->running_balance = $balance;
            return $row;
        });

        /**
         * 🔹 Opening Row
         */
        $openingRow = (object) [
            'id'               => null,
            'voucher_id'       => null,
            'account_id'       => null,
            'dr_amount'        => 0,
            'cr_amount'        => 0,
            'created_at'       => null,
            'voucherno'        => null,
            'voucher_date'     => $from,
            'account_name'     => 'Opening Balance',
            'account_type'     => null,
            'running_balance'  => $openingBalance,
        ];

        $ledger->prepend($openingRow);

        return response()->json([
            'employee'        => $employee,
            'opening_balance' => $openingBalance,
            'records'         => $ledger
        ]);
    }

    public function getSalesReport($searchdata)
    {
        $from = !empty($searchdata['from_date'])
            ? vue_to_server_date($searchdata['from_date'])
            : date('Y-m-01');

        $to = !empty($searchdata['to_date'])
            ? vue_to_server_date($searchdata['to_date'])
            : date('Y-m-t');

        $clientId      = $searchdata['client_id'] ?? null;
        $categoryId    = $searchdata['category_id'] ?? null;
        $itemId        = $searchdata['item_id'] ?? null;
        $invoiceNo     = !empty($searchdata['invoice_no']) ? trim($searchdata['invoice_no']) : null;
        $paymentStatus = $searchdata['payment_status'] ?? null; // 'all', 'paid', 'due'
        $saleType      = $searchdata['sale_type'] ?? null; // 'all', 'pos', 'general'

        // 1. Base Query
        $query = \App\Models\Invoice::query()
            ->whereNull('deleted_at')
            ->whereBetween('invoice_date', [$from, $to]);

        // Filter: Client
        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        // Filter: Invoice No
        if ($invoiceNo) {
            $query->where('invoice_no', 'like', "%{$invoiceNo}%");
        }

        // Filter: Payment Status
        if ($paymentStatus === 'paid') {
            $query->where(function ($q) {
                $q->where('is_closed', 1)
                  ->orWhereRaw('COALESCE(paid_amount, 0) >= amount');
            });
        } elseif ($paymentStatus === 'due') {
            $query->where('is_closed', 0)
                  ->whereRaw('COALESCE(paid_amount, 0) < amount');
        }

        // Filter: Category
        if ($categoryId) {
            $query->whereHas('invoice_details.item', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        // Filter: Item
        if ($itemId) {
            $query->whereHas('invoice_details', function ($q) use ($itemId) {
                $q->where('item_id', $itemId);
            });
        }

        // Filter: Sale Type
        if ($saleType === 'pos') {
            $query->whereHas('invoice_details', function ($q) {
                $q->where('reference', 'POS Sale');
            });
        } elseif ($saleType === 'general') {
            $query->whereDoesntHave('invoice_details', function ($q) {
                $q->where('reference', 'POS Sale');
            });
        }

        // Fetch Invoices with Relations
        $invoices = $query->with([
            'client:id,clientid,name,mobile,address',
            'invoice_details' => function ($q) use ($categoryId, $itemId) {
                $q->with([
                    'item:id,title,barcode,category_id,unit_id',
                    'item.category:id,title',
                    'item.unit:id,title',
                    'color:id,title',
                    'size:id,title',
                ]);
                if ($itemId) {
                    $q->where('item_id', $itemId);
                }
                if ($categoryId) {
                    $q->whereHas('item', function ($iq) use ($categoryId) {
                        $iq->where('category_id', $categoryId);
                    });
                }
            }
        ])
        ->orderBy('invoice_date', 'desc')
        ->orderBy('id', 'desc')
        ->get();

        // 2. Calculations for Invoices & Summaries
        $totalInvoices   = $invoices->count();
        $totalGross      = 0;
        $totalDiscount   = 0;
        $totalVat        = 0;
        $totalNetSales   = 0;
        $totalPaid       = 0;
        $totalDue        = 0;
        $totalQtySold    = 0;

        $itemBreakdownMap = [];
        $customerBreakdownMap = [];
        $dailyBreakdownMap = [];

        foreach ($invoices as $inv) {
            $invAmount     = floatval($inv->amount);
            $invPaid       = floatval($inv->paid_amount ?? 0);
            $invDue        = max(0, $invAmount - $invPaid);
            $invDiscount   = floatval($inv->discount ?? 0);
            $invVat        = floatval($inv->vat ?? 0);
            $invGross      = floatval($inv->original_amount ?? ($invAmount + $invDiscount - $invVat));

            $inv->computed_due = $invDue;
            $inv->computed_status = ($invDue <= 0 || $inv->is_closed) ? 'Paid' : ($invPaid > 0 ? 'Partial' : 'Due');

            $totalGross    += $invGross;
            $totalDiscount += $invDiscount;
            $totalVat      += $invVat;
            $totalNetSales += $invAmount;
            $totalPaid     += $invPaid;
            $totalDue      += $invDue;

            // Date-wise breakdown
            $invDate = $inv->getRawOriginal('invoice_date') ?: date('Y-m-d', strtotime($inv->invoice_date));
            if (!isset($dailyBreakdownMap[$invDate])) {
                $dailyBreakdownMap[$invDate] = [
                    'date'         => $invDate,
                    'formatted_date' => date('d M, Y', strtotime($invDate)),
                    'invoices_count' => 0,
                    'total_qty'    => 0,
                    'gross_amount' => 0,
                    'discount'     => 0,
                    'vat'          => 0,
                    'net_sales'    => 0,
                    'paid_amount'  => 0,
                    'due_amount'   => 0,
                ];
            }
            $dailyBreakdownMap[$invDate]['invoices_count'] += 1;
            $dailyBreakdownMap[$invDate]['gross_amount']   += $invGross;
            $dailyBreakdownMap[$invDate]['discount']       += $invDiscount;
            $dailyBreakdownMap[$invDate]['vat']            += $invVat;
            $dailyBreakdownMap[$invDate]['net_sales']      += $invAmount;
            $dailyBreakdownMap[$invDate]['paid_amount']    += $invPaid;
            $dailyBreakdownMap[$invDate]['due_amount']     += $invDue;

            // Customer-wise breakdown
            $cId = $inv->client_id ?: 0;
            $cName = $inv->client ? $inv->client->name : 'Walk-in Customer';
            $cMobile = $inv->client ? $inv->client->mobile : 'N/A';
            $cClientId = $inv->client ? $inv->client->clientid : 'WALK-IN';

            if (!isset($customerBreakdownMap[$cId])) {
                $customerBreakdownMap[$cId] = [
                    'client_id'      => $cId,
                    'clientid'       => $cClientId,
                    'name'           => $cName,
                    'mobile'         => $cMobile,
                    'invoices_count' => 0,
                    'total_purchased'=> 0,
                    'total_paid'     => 0,
                    'total_due'      => 0,
                ];
            }
            $customerBreakdownMap[$cId]['invoices_count'] += 1;
            $customerBreakdownMap[$cId]['total_purchased']+= $invAmount;
            $customerBreakdownMap[$cId]['total_paid']     += $invPaid;
            $customerBreakdownMap[$cId]['total_due']      += $invDue;

            // Line items breakdown
            foreach ($inv->invoice_details as $detail) {
                $qty = floatval($detail->qty ?? 1);
                $rate = floatval($detail->amount ?? 0);
                $lineTotal = floatval($detail->total_amount ?? ($qty * $rate));

                $totalQtySold += $qty;
                $dailyBreakdownMap[$invDate]['total_qty'] += $qty;

                $itemKey = ($detail->item_id ?? 0) . '_' . ($detail->color_id ?? 0) . '_' . ($detail->size_id ?? 0);
                if (!isset($itemBreakdownMap[$itemKey])) {
                    $itemBreakdownMap[$itemKey] = [
                        'item_id'      => $detail->item_id,
                        'item_title'   => $detail->item ? $detail->item->title : ($detail->description ?: 'Other Item'),
                        'barcode'      => $detail->item ? $detail->item->barcode : 'N/A',
                        'category'     => $detail->item && $detail->item->category ? $detail->item->category->title : 'N/A',
                        'unit'         => $detail->item && $detail->item->unit ? $detail->item->unit->title : 'Pcs',
                        'color'        => $detail->color ? $detail->color->title : null,
                        'size'         => $detail->size ? $detail->size->title : null,
                        'total_qty'    => 0,
                        'total_amount' => 0,
                        'avg_rate'     => 0,
                        'orders_count' => 0,
                    ];
                }
                $itemBreakdownMap[$itemKey]['total_qty']    += $qty;
                $itemBreakdownMap[$itemKey]['total_amount'] += $lineTotal;
                $itemBreakdownMap[$itemKey]['orders_count'] += 1;
            }
        }

        // Format item breakdowns avg rate
        foreach ($itemBreakdownMap as &$ib) {
            $ib['avg_rate'] = $ib['total_qty'] > 0 ? round($ib['total_amount'] / $ib['total_qty'], 2) : 0;
            $ib['total_amount'] = round($ib['total_amount'], 2);
            $ib['total_qty'] = round($ib['total_qty'], 2);
        }
        unset($ib);

        // Sort breakdowns
        $itemBreakdowns = collect(array_values($itemBreakdownMap))->sortByDesc('total_amount')->values();
        $customerBreakdowns = collect(array_values($customerBreakdownMap))->sortByDesc('total_purchased')->values();
        $dailyBreakdowns = collect(array_values($dailyBreakdownMap))->sortByDesc('date')->values();

        return response()->json([
            'from'                 => $from,
            'to'                   => $to,
            'summary'              => [
                'total_invoices'   => $totalInvoices,
                'total_qty'        => round($totalQtySold, 2),
                'gross_amount'     => round($totalGross, 2),
                'total_discount'   => round($totalDiscount, 2),
                'total_vat'        => round($totalVat, 2),
                'net_sales'        => round($totalNetSales, 2),
                'total_paid'       => round($totalPaid, 2),
                'total_due'        => round($totalDue, 2),
            ],
            'invoices'             => $invoices,
            'item_breakdown'       => $itemBreakdowns,
            'customer_breakdown'   => $customerBreakdowns,
            'daily_breakdown'      => $dailyBreakdowns,
        ]);
    }

    /**
     * Professional Sales Return Audit Report with Reasons, Notes & Breakdown
     */
    public function getSalesReturnReport($searchdata)
    {
        // 0. Self-healing sync for any un-synced return payment or wastage
        $this->syncHistoricalReturnsIfNeeded();

        // 1. Date Range Handling (Default to Current Month if parameters not supplied)
        $from = !empty($searchdata['from_date']) && $searchdata['from_date'] !== 'null' && $searchdata['from_date'] !== 'undefined'
            ? vue_to_server_date($searchdata['from_date'])
            : null;

        $to = !empty($searchdata['to_date']) && $searchdata['to_date'] !== 'null' && $searchdata['to_date'] !== 'undefined'
            ? vue_to_server_date($searchdata['to_date'])
            : null;

        // If neither from_date nor to_date is provided at all, default to current month
        if (!array_key_exists('from_date', $searchdata) && !array_key_exists('to_date', $searchdata)) {
            $from = date('Y-m-01');
            $to = date('Y-m-t');
        }

        $clientId      = !empty($searchdata['client_id']) && $searchdata['client_id'] !== 'all' && $searchdata['client_id'] !== 'null' ? $searchdata['client_id'] : null;
        $returnReason  = !empty($searchdata['return_reason']) && $searchdata['return_reason'] !== 'all' && $searchdata['return_reason'] !== 'null' ? $searchdata['return_reason'] : null;
        $paymentMethod = !empty($searchdata['payment_method']) && $searchdata['payment_method'] !== 'all' && $searchdata['payment_method'] !== 'null' ? $searchdata['payment_method'] : null;
        $categoryId    = !empty($searchdata['category_id']) && $searchdata['category_id'] !== 'all' && $searchdata['category_id'] !== 'null' ? $searchdata['category_id'] : null;
        $itemId        = !empty($searchdata['item_id']) && $searchdata['item_id'] !== 'all' && $searchdata['item_id'] !== 'null' ? $searchdata['item_id'] : null;
        $keyword       = !empty($searchdata['keyword']) ? trim($searchdata['keyword']) : (!empty($searchdata['value']) ? trim($searchdata['value']) : null);

        // 2. Query SalesReturn model
        $query = SalesReturn::with([
            'invoice:id,invoice_no,invoice_date,amount,paid_amount,created_at',
            'client:id,clientid,name,mobile,email,address',
            'creator:id,full_name,email',
            'payment:id,payslipno,payment_method,mbanking_type,trxid,amount,payment_date',
            'wastage:id,audit_number,audit_date,status',
            'details.item:id,title,barcode,category_id,unit_id',
            'details.category:id,title',
            'details.color:id,title',
            'details.size:id,title',
            'details.unit:id,title',
        ])
        ->whereNull('deleted_at');

        // Apply safe date filters (never whereBetween with NULL)
        if ($from && $to) {
            $query->whereBetween('return_date', [$from, $to]);
        } elseif ($from) {
            $query->where('return_date', '>=', $from);
        } elseif ($to) {
            $query->where('return_date', '<=', $to);
        }

        // Filter: Customer
        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        // Filter: Return Reason
        if ($returnReason && $returnReason !== 'all') {
            $query->where('return_reason', $returnReason);
        }

        // Filter: Payment Method
        if ($paymentMethod && $paymentMethod !== 'all') {
            $query->where('payment_method', $paymentMethod);
        }

        // Filter: Category (via details)
        if ($categoryId) {
            $query->whereHas('details', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId)
                  ->orWhereHas('item', function ($iq) use ($categoryId) {
                      $iq->where('category_id', $categoryId);
                  });
            });
        }

        // Filter: Item (via details)
        if ($itemId) {
            $query->whereHas('details', function ($q) use ($itemId) {
                $q->where('item_id', $itemId);
            });
        }

        // Filter: Keyword (Search return_no, invoice_no, client name/mobile, note, trxid, item title, barcode, serial_no)
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('return_no', 'like', "%{$keyword}%")
                  ->orWhere('note', 'like', "%{$keyword}%")
                  ->orWhere('trxid', 'like', "%{$keyword}%")
                  ->orWhereHas('invoice', function ($iq) use ($keyword) {
                      $iq->where('invoice_no', 'like', "%{$keyword}%");
                  })
                  ->orWhereHas('client', function ($cq) use ($keyword) {
                      $cq->where('name', 'like', "%{$keyword}%")
                         ->orWhere('mobile', 'like', "%{$keyword}%")
                         ->orWhere('clientid', 'like', "%{$keyword}%");
                  })
                  ->orWhereHas('details', function ($dq) use ($keyword) {
                      $dq->where('serial_no', 'like', "%{$keyword}%")
                         ->orWhere('note', 'like', "%{$keyword}%")
                         ->orWhereHas('item', function ($itq) use ($keyword) {
                             $itq->where('title', 'like', "%{$keyword}%")
                                 ->orWhere('barcode', 'like', "%{$keyword}%");
                         });
                  });
            });
        }

        $returns = $query->orderBy('return_date', 'desc')->orderBy('id', 'desc')->get();

        // 3. Compute Metrics & Breakdowns
        $totalReturnsCount = $returns->count();
        $totalReturnedQty = 0;
        $totalRefundAmount = 0;

        $reasonCounts = [
            'Client request' => ['count' => 0, 'qty' => 0, 'amount' => 0, 'label' => 'Client request (স্টকে যুক্ত)', 'badge' => 'badge bg-success'],
            'Wastage'        => ['count' => 0, 'qty' => 0, 'amount' => 0, 'label' => 'Wastage (নষ্ট/ওয়েস্টেজ)', 'badge' => 'badge bg-danger'],
            'Date Expaired'  => ['count' => 0, 'qty' => 0, 'amount' => 0, 'label' => 'Date Expaired (মেয়াদোত্তীর্ণ)', 'badge' => 'badge bg-warning text-dark'],
            'Other'          => ['count' => 0, 'qty' => 0, 'amount' => 0, 'label' => 'Other Reasons', 'badge' => 'badge bg-secondary'],
        ];

        $paymentMethodCounts = [];
        $itemBreakdownMap = [];
        $customerBreakdownMap = [];
        $dailyBreakdownMap = [];

        foreach ($returns as $ret) {
            $retQty = floatval($ret->total_qty);
            $retAmount = floatval($ret->total_refund_amount);

            $totalReturnedQty += $retQty;
            $totalRefundAmount += $retAmount;

            // Reason aggregation
            $rKey = in_array($ret->return_reason, ['Client request', 'Wastage', 'Date Expaired']) ? $ret->return_reason : 'Other';
            $reasonCounts[$rKey]['count'] += 1;
            $reasonCounts[$rKey]['qty'] += $retQty;
            $reasonCounts[$rKey]['amount'] += $retAmount;

            // Payment method aggregation
            $pmKey = $ret->payment_method ?: 'Cash';
            if (!isset($paymentMethodCounts[$pmKey])) {
                $paymentMethodCounts[$pmKey] = ['method' => $pmKey, 'count' => 0, 'amount' => 0];
            }
            $paymentMethodCounts[$pmKey]['count'] += 1;
            $paymentMethodCounts[$pmKey]['amount'] += $retAmount;

            // Daily breakdown
            $retDate = $ret->return_date ? date('Y-m-d', strtotime($ret->return_date)) : date('Y-m-d');
            if (!isset($dailyBreakdownMap[$retDate])) {
                $dailyBreakdownMap[$retDate] = [
                    'date'          => $retDate,
                    'returns_count' => 0,
                    'total_qty'     => 0,
                    'refund_amount' => 0,
                ];
            }
            $dailyBreakdownMap[$retDate]['returns_count'] += 1;
            $dailyBreakdownMap[$retDate]['total_qty'] += $retQty;
            $dailyBreakdownMap[$retDate]['refund_amount'] += $retAmount;

            // Customer breakdown
            $custKey = $ret->client_id ?: 0;
            if (!isset($customerBreakdownMap[$custKey])) {
                $customerBreakdownMap[$custKey] = [
                    'client_id'     => $ret->client_id,
                    'clientid'      => $ret->client ? $ret->client->clientid : 'WALK-IN',
                    'name'          => $ret->client ? $ret->client->name : 'Walk-in Customer (সাধারণ ক্রেতা)',
                    'mobile'        => $ret->client ? $ret->client->mobile : 'N/A',
                    'returns_count' => 0,
                    'total_qty'     => 0,
                    'refund_amount' => 0,
                ];
            }
            $customerBreakdownMap[$custKey]['returns_count'] += 1;
            $customerBreakdownMap[$custKey]['total_qty'] += $retQty;
            $customerBreakdownMap[$custKey]['refund_amount'] += $retAmount;

            // Item-level breakdown
            if ($ret->details) {
                foreach ($ret->details as $d) {
                    $dQty = floatval($d->qty);
                    $dAmount = floatval($d->refund_amount);
                    $itemKey = ($d->item_id ?? 0) . '_' . ($d->color_id ?? 0) . '_' . ($d->size_id ?? 0);

                    if (!isset($itemBreakdownMap[$itemKey])) {
                        $itemBreakdownMap[$itemKey] = [
                            'item_id'           => $d->item_id,
                            'item_title'        => $d->item ? $d->item->title : 'Item #' . $d->item_id,
                            'barcode'           => $d->item ? $d->item->barcode : 'N/A',
                            'category'          => $d->category ? $d->category->title : ($d->item && $d->item->category ? $d->item->category->title : 'N/A'),
                            'unit'              => $d->unit ? $d->unit->title : ($d->item && $d->item->unit ? $d->item->unit->title : 'Pcs'),
                            'color'             => $d->color ? $d->color->title : null,
                            'size'              => $d->size ? $d->size->title : null,
                            'total_qty'         => 0,
                            'total_refund'      => 0,
                            'restocked_qty'     => 0,
                            'wastage_qty'       => 0,
                            'expired_qty'       => 0,
                            'reasons_summary'   => [],
                            'returns_count'     => 0,
                        ];
                    }

                    $itemBreakdownMap[$itemKey]['total_qty'] += $dQty;
                    $itemBreakdownMap[$itemKey]['total_refund'] += $dAmount;
                    $itemBreakdownMap[$itemKey]['returns_count'] += 1;

                    $reasonName = $d->return_reason ?: $ret->return_reason;
                    if ($reasonName === 'Client request') {
                        $itemBreakdownMap[$itemKey]['restocked_qty'] += $dQty;
                    } elseif ($reasonName === 'Wastage') {
                        $itemBreakdownMap[$itemKey]['wastage_qty'] += $dQty;
                    } elseif ($reasonName === 'Date Expaired') {
                        $itemBreakdownMap[$itemKey]['expired_qty'] += $dQty;
                    }

                    if (!in_array($reasonName, $itemBreakdownMap[$itemKey]['reasons_summary'])) {
                        $itemBreakdownMap[$itemKey]['reasons_summary'][] = $reasonName;
                    }
                }
            }
        }

        // Format items avg refund rate
        foreach ($itemBreakdownMap as &$ib) {
            $ib['avg_rate'] = $ib['total_qty'] > 0 ? round($ib['total_refund'] / $ib['total_qty'], 2) : 0;
            $ib['total_refund'] = round($ib['total_refund'], 2);
            $ib['total_qty'] = round($ib['total_qty'], 2);
            $ib['reasons_text'] = implode(', ', $ib['reasons_summary']);
        }
        unset($ib);

        $itemBreakdowns = collect(array_values($itemBreakdownMap))->sortByDesc('total_refund')->values();
        $customerBreakdowns = collect(array_values($customerBreakdownMap))->sortByDesc('refund_amount')->values();
        $dailyBreakdowns = collect(array_values($dailyBreakdownMap))->sortByDesc('date')->values();
        $paymentMethodsList = collect(array_values($paymentMethodCounts))->sortByDesc('amount')->values();

        return response()->json([
            'from'                 => $from,
            'to'                   => $to,
            'summary'              => [
                'total_returns'    => $totalReturnsCount,
                'total_qty'        => round($totalReturnedQty, 2),
                'total_refund'     => round($totalRefundAmount, 2),
                'restocked_qty'    => round($reasonCounts['Client request']['qty'], 2),
                'restocked_amount' => round($reasonCounts['Client request']['amount'], 2),
                'wastage_qty'      => round($reasonCounts['Wastage']['qty'] + $reasonCounts['Date Expaired']['qty'], 2),
                'wastage_amount'   => round($reasonCounts['Wastage']['amount'] + $reasonCounts['Date Expaired']['amount'], 2),
                'reasons'          => $reasonCounts,
                'payment_methods'  => $paymentMethodsList,
            ],
            'returns'              => $returns,
            'item_breakdown'       => $itemBreakdowns,
            'customer_breakdown'   => $customerBreakdowns,
            'daily_breakdown'      => $dailyBreakdowns,
        ]);
    }

    /**
     * Self-healing sync for any returns in payments/stock_transactions/wastages not yet in sales_returns
     */
    protected function syncHistoricalReturnsIfNeeded()
    {
        try {
            $unSyncedPaymentDetails = PaymentDetail::where('reference_type', 'SalesReturn')
                ->whereNotIn('payment_id', function ($q) {
                    $q->select('payment_id')->from('sales_returns')->whereNotNull('payment_id');
                })
                ->with('payment')
                ->get();

            if ($unSyncedPaymentDetails->isEmpty()) {
                return;
            }

            foreach ($unSyncedPaymentDetails as $pd) {
                $payment = $pd->payment;
                $invoiceId = $pd->reference_id;
                $invoice = Invoice::with(['details.item', 'client'])->find($invoiceId);

                if (!$invoice) continue;

                $wastage = \App\Models\Wastage::where('note', 'like', "%POS Return%[ID:{$invoiceId}]%")
                    ->orWhere('note', 'like', "%Invoice #{$invoice->invoice_no}%")
                    ->with('wastage_details')
                    ->first();

                $returnReason = 'Client request';
                $returnNote = null;
                $wastageId = null;

                if ($wastage) {
                    $wastageId = $wastage->id;
                    if (preg_match('/Reason:\s*([^\|]+)/i', $wastage->note, $rMatches)) {
                        $returnReason = trim($rMatches[1]);
                    }
                    if (preg_match('/Note:\s*(.+)$/i', $wastage->note, $nMatches)) {
                        $returnNote = trim($nMatches[1]);
                    }
                }

                $rawDate = $payment ? $payment->getRawOriginal('payment_date') : $invoice->getRawOriginal('invoice_date');
                if (!$rawDate) $rawDate = date('Y-m-d');

                $returnNo = SalesReturn::generateReturnNo();
                $totalRefund = floatval($pd->amount);
                $totalQty = 0;
                $itemsToInsert = [];

                if ($wastage && $wastage->wastage_details->isNotEmpty()) {
                    foreach ($wastage->wastage_details as $wd) {
                        $qty = floatval($wd->quantity);
                        $rate = floatval($wd->unit_price);
                        $amount = floatval($wd->total_amount);
                        $totalQty += $qty;

                        $itemsToInsert[] = [
                            'item_id'       => $wd->item_id,
                            'category_id'   => $wd->category_id,
                            'color_id'      => $wd->color_id,
                            'size_id'       => $wd->size_id,
                            'unit_id'       => $wd->unit_id,
                            'qty'           => $qty,
                            'rate'          => $rate,
                            'refund_amount' => $amount,
                            'return_reason' => $wd->reason ?: $returnReason,
                            'serial_no'     => $wd->serial_no,
                            'note'          => $returnNote,
                        ];
                    }
                } else {
                    $stockTrans = \App\Models\StockTransaction::where('reference_type', 'SalesReturn')
                        ->where('reference_id', (string)$invoiceId)
                        ->get();

                    if ($stockTrans->isNotEmpty()) {
                        foreach ($stockTrans as $st) {
                            $qty = floatval($st->qty_in);
                            $totalQty += $qty;

                            $invDet = $invoice->details->first(function ($d) use ($st) {
                                return $d->item_id == $st->item_id &&
                                    (($st->color_id === null && empty($d->color_id)) || $d->color_id == $st->color_id) &&
                                    (($st->size_id === null && empty($d->size_id)) || $d->size_id == $st->size_id);
                            }) ?? $invoice->details->firstWhere('item_id', $st->item_id);

                            $rate = $invDet ? floatval($invDet->rate) : ($totalQty > 0 ? round($totalRefund / $totalQty, 2) : 0);
                            $lineRefund = $qty * $rate;

                            $itemsToInsert[] = [
                                'item_id'           => $st->item_id,
                                'invoice_detail_id' => $invDet ? $invDet->id : null,
                                'category_id'       => $invDet && $invDet->item ? $invDet->item->category_id : null,
                                'color_id'          => $st->color_id,
                                'size_id'           => $st->size_id,
                                'unit_id'           => $invDet && $invDet->item ? $invDet->item->unit_id : null,
                                'qty'               => $qty,
                                'rate'              => $rate,
                                'refund_amount'     => $lineRefund > 0 ? $lineRefund : $totalRefund,
                                'return_reason'     => 'Client request',
                                'serial_no'         => $invDet ? $invDet->serial_no : null,
                                'note'              => $returnNote,
                            ];
                        }
                    } else {
                        if ($invoice->details->isNotEmpty()) {
                            $firstDetail = $invoice->details->first();
                            $totalQty = 1;
                            $itemsToInsert[] = [
                                'item_id'           => $firstDetail->item_id,
                                'invoice_detail_id' => $firstDetail->id,
                                'category_id'       => $firstDetail->item ? $firstDetail->item->category_id : null,
                                'color_id'          => $firstDetail->color_id,
                                'size_id'           => $firstDetail->size_id,
                                'unit_id'           => $firstDetail->item ? $firstDetail->item->unit_id : null,
                                'qty'               => 1,
                                'rate'              => $totalRefund,
                                'refund_amount'     => $totalRefund,
                                'return_reason'     => $returnReason,
                                'serial_no'         => $firstDetail->serial_no,
                                'note'              => $returnNote,
                            ];
                        }
                    }
                }

                if ($totalQty <= 0) $totalQty = 1;

                $salesReturn = SalesReturn::create([
                    'return_no'           => $returnNo,
                    'invoice_id'          => $invoice->id,
                    'client_id'           => $payment ? $payment->client_id : $invoice->client_id,
                    'branch_id'           => $invoice->branch_id ?? 1,
                    'return_date'         => $rawDate,
                    'return_reason'       => $returnReason,
                    'note'                => $returnNote,
                    'payment_method'      => $payment ? $payment->payment_method : 'Cash',
                    'mbanking_type'       => $payment ? $payment->mbanking_type : null,
                    'trxid'               => $payment ? $payment->trxid : null,
                    'total_qty'           => $totalQty,
                    'total_refund_amount' => $totalRefund,
                    'payment_id'          => $payment ? $payment->id : null,
                    'wastage_id'          => $wastageId,
                    'created_by'          => $payment ? $payment->created_by : 1,
                    'status'              => 'active',
                ]);

                foreach ($itemsToInsert as $item) {
                    $item['sales_return_id'] = $salesReturn->id;
                    $item['status'] = 'active';
                    SalesReturnDetail::create($item);
                }
            }
        } catch (\Throwable $e) {
            // Log or ignore silently
        }
    }

    /**
     * Professional Wastage & Disposal Audit Report
     */
    public function getWastageReport($searchdata)
    {
        // 1. Date Range Handling (Default to Current Month)
        $from = !empty($searchdata['from_date']) && $searchdata['from_date'] !== 'null' && $searchdata['from_date'] !== 'undefined'
            ? vue_to_server_date($searchdata['from_date'])
            : null;

        $to = !empty($searchdata['to_date']) && $searchdata['to_date'] !== 'null' && $searchdata['to_date'] !== 'undefined'
            ? vue_to_server_date($searchdata['to_date'])
            : null;

        // If neither from_date nor to_date is provided at all, default to current month
        if (!array_key_exists('from_date', $searchdata) && !array_key_exists('to_date', $searchdata)) {
            $from = date('Y-m-01');
            $to = date('Y-m-t');
        }

        $categoryId = !empty($searchdata['category_id']) && $searchdata['category_id'] !== 'all' && $searchdata['category_id'] !== 'null' ? $searchdata['category_id'] : null;
        $itemId     = !empty($searchdata['item_id']) && $searchdata['item_id'] !== 'all' && $searchdata['item_id'] !== 'null' ? $searchdata['item_id'] : null;
        $auditorId  = !empty($searchdata['auditor_id']) && $searchdata['auditor_id'] !== 'all' && $searchdata['auditor_id'] !== 'null' ? $searchdata['auditor_id'] : null;
        $status     = !empty($searchdata['status']) && $searchdata['status'] !== 'all' && $searchdata['status'] !== 'null' ? $searchdata['status'] : null;
        $reason     = !empty($searchdata['reason']) && $searchdata['reason'] !== 'all' && $searchdata['reason'] !== 'null' ? $searchdata['reason'] : null;
        $branchId   = !empty($searchdata['branch_id']) && $searchdata['branch_id'] !== 'all' && $searchdata['branch_id'] !== 'null' ? $searchdata['branch_id'] : null;
        $keyword    = !empty($searchdata['keyword']) ? trim($searchdata['keyword']) : (!empty($searchdata['value']) ? trim($searchdata['value']) : null);

        // 2. Query Wastage model
        $query = Wastage::with([
            'auditor:id,empid,full_name,mobile',
            'branch:id,branch_name',
            'approved_admin:id,full_name,email',
            'creator:id,full_name,email',
            'wastage_details.item:id,title,barcode,category_id,unit_id',
            'wastage_details.category:id,title',
            'wastage_details.color:id,title',
            'wastage_details.size:id,title',
            'wastage_details.unit:id,title',
        ])
        ->whereNull('deleted_at');

        // Apply safe date filters (never whereBetween with NULL)
        if ($from && $to) {
            $query->whereBetween('audit_date', [$from, $to]);
        } elseif ($from) {
            $query->where('audit_date', '>=', $from);
        } elseif ($to) {
            $query->where('audit_date', '<=', $to);
        }

        // Filter: Status
        if ($status) {
            $query->where('status', $status);
        }

        // Filter: Branch
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        // Filter: Auditor
        if ($auditorId) {
            $query->where('auditor_id', $auditorId);
        }

        // Filter: Category (via wastage_details)
        if ($categoryId) {
            $query->whereHas('wastage_details', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId)
                  ->orWhereHas('item', function ($iq) use ($categoryId) {
                      $iq->where('category_id', $categoryId);
                  });
            });
        }

        // Filter: Item (via wastage_details)
        if ($itemId) {
            $query->whereHas('wastage_details', function ($q) use ($itemId) {
                $q->where('item_id', $itemId);
            });
        }

        // Filter: Reason (via wastage_details or note)
        if ($reason) {
            $query->where(function ($q) use ($reason) {
                $q->whereHas('wastage_details', function ($dq) use ($reason) {
                    $dq->where('reason', $reason);
                })->orWhere('note', 'like', "%{$reason}%");
            });
        }

        // Filter: Keyword (Search audit_number, audited_by, note, serial_no, remarks, item title, item barcode)
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('audit_number', 'like', "%{$keyword}%")
                  ->orWhere('audited_by', 'like', "%{$keyword}%")
                  ->orWhere('note', 'like', "%{$keyword}%")
                  ->orWhereHas('auditor', function ($aq) use ($keyword) {
                      $aq->where('full_name', 'like', "%{$keyword}%")
                         ->orWhere('employee_id', 'like', "%{$keyword}%");
                  })
                  ->orWhereHas('wastage_details', function ($dq) use ($keyword) {
                      $dq->where('serial_no', 'like', "%{$keyword}%")
                         ->orWhere('remarks', 'like', "%{$keyword}%")
                         ->orWhere('reason', 'like', "%{$keyword}%")
                         ->orWhereHas('item', function ($itq) use ($keyword) {
                             $itq->where('title', 'like', "%{$keyword}%")
                                 ->orWhere('barcode', 'like', "%{$keyword}%");
                         });
                  });
            });
        }

        $wastages = $query->orderBy('audit_date', 'desc')->orderBy('id', 'desc')->get();

        // 3. Compute Metrics & Breakdowns
        $totalAuditsCount = $wastages->count();
        $totalDisposedQty = 0;
        $totalLossAmount = 0;
        $approvedQty = 0;
        $approvedLoss = 0;
        $pendingQty = 0;
        $pendingLoss = 0;

        $reasonCounts = [];
        $statusCounts = [
            'approved' => ['count' => 0, 'qty' => 0, 'amount' => 0],
            'pending'  => ['count' => 0, 'qty' => 0, 'amount' => 0],
            'rejected' => ['count' => 0, 'qty' => 0, 'amount' => 0],
        ];

        $itemBreakdownMap = [];
        $categoryBreakdownMap = [];
        $dailyBreakdownMap = [];

        foreach ($wastages as $wst) {
            $wstQty = floatval($wst->total_qty);
            $wstAmount = floatval($wst->total_loss_amount);

            $totalDisposedQty += $wstQty;
            $totalLossAmount += $wstAmount;

            $stKey = strtolower($wst->status ?: 'pending');
            if (!isset($statusCounts[$stKey])) {
                $statusCounts[$stKey] = ['count' => 0, 'qty' => 0, 'amount' => 0];
            }
            $statusCounts[$stKey]['count']++;
            $statusCounts[$stKey]['qty'] += $wstQty;
            $statusCounts[$stKey]['amount'] += $wstAmount;

            if ($stKey === 'approved') {
                $approvedQty += $wstQty;
                $approvedLoss += $wstAmount;
            } elseif ($stKey === 'pending') {
                $pendingQty += $wstQty;
                $pendingLoss += $wstAmount;
            }

            // Daily Map
            $dateKey = $wst->getRawOriginal('audit_date') ?: date('Y-m-d', strtotime($wst->audit_date));
            if (!isset($dailyBreakdownMap[$dateKey])) {
                $dailyBreakdownMap[$dateKey] = [
                    'date'           => $dateKey,
                    'formatted_date' => date('d M, Y', strtotime($dateKey)),
                    'audits_count'   => 0,
                    'total_qty'      => 0,
                    'loss_amount'    => 0,
                ];
            }
            $dailyBreakdownMap[$dateKey]['audits_count'] += 1;
            $dailyBreakdownMap[$dateKey]['total_qty'] += $wstQty;
            $dailyBreakdownMap[$dateKey]['loss_amount'] += $wstAmount;

            // Details Breakdown (Items, Categories, Reasons)
            if ($wst->wastage_details) {
                foreach ($wst->wastage_details as $d) {
                    $dQty = floatval($d->quantity);
                    $dRate = floatval($d->unit_price);
                    $dAmount = floatval($d->total_amount);
                    if ($dAmount <= 0 && $dRate > 0) $dAmount = $dQty * $dRate;

                    $dReason = $d->reason ?: 'Damaged / Defective';
                    if (!isset($reasonCounts[$dReason])) {
                        $reasonCounts[$dReason] = ['count' => 0, 'qty' => 0, 'amount' => 0];
                    }
                    $reasonCounts[$dReason]['count']++;
                    $reasonCounts[$dReason]['qty'] += $dQty;
                    $reasonCounts[$dReason]['amount'] += $dAmount;

                    // Item Breakdown
                    $itemKey = $d->item_id . '_' . ($d->color_id ?? '0') . '_' . ($d->size_id ?? '0');
                    if (!isset($itemBreakdownMap[$itemKey])) {
                        $itemBreakdownMap[$itemKey] = [
                            'item_id'        => $d->item_id,
                            'item_title'     => $d->item ? $d->item->title : 'Item #' . $d->item_id,
                            'barcode'        => $d->item ? $d->item->barcode : 'N/A',
                            'category'       => $d->category ? $d->category->title : ($d->item && $d->item->category ? $d->item->category->title : 'N/A'),
                            'unit'           => $d->unit ? $d->unit->title : ($d->item && $d->item->unit ? $d->item->unit->title : 'Pcs'),
                            'color'          => $d->color ? $d->color->title : null,
                            'size'           => $d->size ? $d->size->title : null,
                            'total_qty'      => 0,
                            'total_loss'     => 0,
                            'reasons_summary'=> [],
                            'audits_count'   => 0,
                        ];
                    }
                    $itemBreakdownMap[$itemKey]['total_qty'] += $dQty;
                    $itemBreakdownMap[$itemKey]['total_loss'] += $dAmount;
                    $itemBreakdownMap[$itemKey]['audits_count'] += 1;
                    if (!in_array($dReason, $itemBreakdownMap[$itemKey]['reasons_summary'])) {
                        $itemBreakdownMap[$itemKey]['reasons_summary'][] = $dReason;
                    }

                    // Category Breakdown
                    $catId = $d->category_id ?: ($d->item ? $d->item->category_id : 0);
                    $catTitle = $d->category ? $d->category->title : ($d->item && $d->item->category ? $d->item->category->title : 'General / Uncategorized');
                    if (!isset($categoryBreakdownMap[$catId])) {
                        $categoryBreakdownMap[$catId] = [
                            'category_id'    => $catId,
                            'category_title' => $catTitle,
                            'total_qty'      => 0,
                            'total_loss'     => 0,
                            'audits_count'   => 0,
                        ];
                    }
                    $categoryBreakdownMap[$catId]['total_qty'] += $dQty;
                    $categoryBreakdownMap[$catId]['total_loss'] += $dAmount;
                    $categoryBreakdownMap[$catId]['audits_count'] += 1;
                }
            }
        }

        // Format items avg loss rate and reasons string
        foreach ($itemBreakdownMap as &$ib) {
            $ib['avg_rate'] = $ib['total_qty'] > 0 ? round($ib['total_loss'] / $ib['total_qty'], 2) : 0;
            $ib['total_loss'] = round($ib['total_loss'], 2);
            $ib['total_qty'] = round($ib['total_qty'], 2);
            $ib['reasons_text'] = implode(', ', $ib['reasons_summary']);
        }
        unset($ib);

        // Calculate Category % Share
        foreach ($categoryBreakdownMap as &$cb) {
            $cb['loss_percentage'] = $totalLossAmount > 0 ? round(($cb['total_loss'] / $totalLossAmount) * 100, 1) : 0;
            $cb['total_loss'] = round($cb['total_loss'], 2);
            $cb['total_qty'] = round($cb['total_qty'], 2);
        }
        unset($cb);

        $itemBreakdowns = collect(array_values($itemBreakdownMap))->sortByDesc('total_loss')->values();
        $categoryBreakdowns = collect(array_values($categoryBreakdownMap))->sortByDesc('total_loss')->values();
        $dailyBreakdowns = collect(array_values($dailyBreakdownMap))->sortByDesc('date')->values();
        $reasonsList = collect(array_map(function($key, $val) {
            return [
                'reason' => $key,
                'count'  => $val['count'],
                'qty'    => round($val['qty'], 2),
                'amount' => round($val['amount'], 2),
            ];
        }, array_keys($reasonCounts), array_values($reasonCounts)))->sortByDesc('amount')->values();

        return response()->json([
            'from'                 => $from,
            'to'                   => $to,
            'summary'              => [
                'total_audits'     => $totalAuditsCount,
                'total_qty'        => round($totalDisposedQty, 2),
                'total_loss'       => round($totalLossAmount, 2),
                'approved_qty'     => round($approvedQty, 2),
                'approved_loss'    => round($approvedLoss, 2),
                'pending_qty'      => round($pendingQty, 2),
                'pending_loss'     => round($pendingLoss, 2),
                'reasons'          => $reasonsList,
                'status_summary'   => $statusCounts,
            ],
            'wastages'             => $wastages,
            'item_breakdown'       => $itemBreakdowns,
            'category_breakdown'   => $categoryBreakdowns,
            'daily_breakdown'      => $dailyBreakdowns,
        ]);
    }
}
