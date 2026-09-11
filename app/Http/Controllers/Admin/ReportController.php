<?php

namespace App\Http\Controllers\Admin;

use Storage;
use App\Traits\ReportTrait;
use Illuminate\Http\Request;
use App\Http\Controllers\Base\BaseController;
use App\Models\FundAccountBalance;
use App\Models\ItemStockSummary;
use App\Models\PaymentDetail;
use App\Models\Account;
use App\Models\Payment;
use App\Models\Client;
use App\Models\InvoiceDetails;
use App\Models\PurchaseDetail;
use App\Models\WarrantyClaim;
use App\Models\Item;
use App\Models\ItemPrice;
use App\Models\Category;
use App\Models\ClientPointTransaction;
use App\Models\System\SiteSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends BaseController
{
    use ReportTrait;


    public function itemLadger(Request $request)
    {
        if ($request->format() == 'html') {
            return view('admin.layouts.admin_app');
        }
        $from_date = null;
        $to_date = null;

        $itemid = null;
        $searchdata = $request->all();
        if ($searchdata && array_key_exists('item_id', $searchdata)) {
            $itemid = $searchdata['item_id'];
            $from_date = vue_to_server_date($searchdata['start_date']);
            $to_date = vue_to_server_date($searchdata['end_date']);
        }

        $stockledger = $this->getItemLadger($itemid, $from_date, $to_date);
        return $stockledger;
    }

    public function availablestock(Request $request)
    {
        if ($request->format() == 'html') {
            return view('admin.layouts.admin_app');
        }
        $searchdata = $request->all();

        $lowThreshold = isset($searchdata['low_threshold']) && is_numeric($searchdata['low_threshold'])
            ? (int) $searchdata['low_threshold']
            : 5;

        // 1. Fast Global Stock Counts in single query
        $counts = DB::table('item_stock_summaries')
            ->selectRaw("
                COUNT(*) as all_count,
                SUM(CASE WHEN current_stock > 0 AND current_stock <= {$lowThreshold} THEN 1 ELSE 0 END) as low_stock_count,
                SUM(CASE WHEN current_stock <= 0 THEN 1 ELSE 0 END) as out_of_stock_count,
                SUM(CASE WHEN current_stock < 0 THEN 1 ELSE 0 END) as negative_stock_count,
                SUM(CASE WHEN current_stock > 0 THEN 1 ELSE 0 END) as in_stock_count,
                SUM(total_qty_in) as total_in,
                SUM(total_qty_out) as total_out,
                SUM(CASE WHEN current_stock > 0 THEN current_stock ELSE 0 END) as total_current_stock
            ")
            ->first();

        // 2. High-Performance Query with Single-Pass Pricing Resolution
        $latestPurchaseSub = DB::table('purchase_details')
            ->select('item_id', 'color_id', 'size_id', 'price', 'selling_price')
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')
                  ->from('purchase_details')
                  ->whereNull('deleted_at')
                  ->groupBy('item_id', 'color_id', 'size_id');
            });

        $query = DB::table('item_stock_summaries as iss')
            ->join('items as i', 'i.id', '=', 'iss.item_id')
            ->leftJoin('categories as c', 'c.id', '=', 'i.category_id')
            ->leftJoin('units as u', 'u.id', '=', 'i.unit_id')
            ->leftJoin('colors as col', 'col.id', '=', 'iss.color_id')
            ->leftJoin('sizes as sz', 'sz.id', '=', 'iss.size_id')
            ->leftJoin('item_prices as ip', function ($join) {
                $join->on('ip.item_id', '=', 'iss.item_id')
                     ->where('ip.status', '=', 'active')
                     ->where(function ($q) {
                         $q->whereColumn('ip.color_id', 'iss.color_id')
                           ->orWhere(function ($sq) {
                               $sq->whereNull('ip.color_id')->whereNull('iss.color_id');
                           });
                     })
                     ->where(function ($q) {
                         $q->whereColumn('ip.size_id', 'iss.size_id')
                           ->orWhere(function ($sq) {
                               $sq->whereNull('ip.size_id')->whereNull('iss.size_id');
                           });
                     });
            })
            ->leftJoinSub($latestPurchaseSub, 'pd', function ($join) {
                $join->on('pd.item_id', '=', 'iss.item_id')
                     ->where(function ($q) {
                         $q->whereColumn('pd.color_id', 'iss.color_id')
                           ->orWhere(function ($sq) {
                               $sq->whereNull('pd.color_id')->whereNull('iss.color_id');
                           });
                     })
                     ->where(function ($q) {
                         $q->whereColumn('pd.size_id', 'iss.size_id')
                           ->orWhere(function ($sq) {
                               $sq->whereNull('pd.size_id')->whereNull('iss.size_id');
                           });
                     });
            })
            ->select([
                'iss.item_id',
                'iss.color_id',
                'iss.size_id',
                'iss.total_qty_in',
                'iss.total_qty_out',
                'iss.current_stock',
                'i.title as item_title',
                'i.barcode',
                'i.category_id',
                'c.title as category_title',
                'u.title as unit_title',
                'col.title as color_title',
                'sz.title as size_title',
                DB::raw("COALESCE(NULLIF(ip.purchase_price, 0), NULLIF(pd.price, 0), NULLIF(i.opening_rate, 0), 0) as purchase_price"),
                DB::raw("COALESCE(NULLIF(ip.selling_price, 0), NULLIF(pd.selling_price, 0), 0) as selling_price"),
            ]);

        // Stock Status Filter
        $stockStatus = $searchdata['stock_status'] ?? 'low_stock';
        if ($stockStatus === 'low_stock') {
            $query->where('iss.current_stock', '>', 0)->where('iss.current_stock', '<=', $lowThreshold);
        } elseif ($stockStatus === 'out_of_stock') {
            $query->where('iss.current_stock', '<=', 0);
        } elseif ($stockStatus === 'negative_stock') {
            $query->where('iss.current_stock', '<', 0);
        } elseif ($stockStatus === 'in_stock') {
            $query->where('iss.current_stock', '>', 0);
        }

        if (!empty($searchdata['category_id'])) {
            $query->where('i.category_id', $searchdata['category_id']);
        }
        if (!empty($searchdata['item_id'])) {
            $query->where('iss.item_id', $searchdata['item_id']);
        }
        if (!empty($searchdata['color_id'])) {
            $query->where('iss.color_id', $searchdata['color_id']);
        }
        if (!empty($searchdata['size_id'])) {
            $query->where('iss.size_id', $searchdata['size_id']);
        }
        if (isset($searchdata['is_zero']) && ($searchdata['is_zero'] === '1' || $searchdata['is_zero'] === 1 || $searchdata['is_zero'] === true)) {
            $query->where('iss.current_stock', 0);
        }
        if (!empty($searchdata['keyword'])) {
            $kw = trim($searchdata['keyword']);
            $query->where(function ($q) use ($kw) {
                $q->where('i.title', 'like', "%{$kw}%")
                  ->orWhere('i.barcode', 'like', "%{$kw}%");
            });
        }
        if (isset($searchdata['from_qty']) && is_numeric($searchdata['from_qty'])) {
            $query->where('iss.current_stock', '>=', (int) $searchdata['from_qty']);
        }
        if (isset($searchdata['to_qty']) && is_numeric($searchdata['to_qty'])) {
            $query->where('iss.current_stock', '<=', (int) $searchdata['to_qty']);
        }

        // Sorting
        $sortBy = $searchdata['sort_by'] ?? ($stockStatus === 'low_stock' ? 'stock_asc' : 'stock_desc');
        if ($sortBy === 'stock_asc') {
            $query->orderBy('iss.current_stock', 'asc');
        } elseif ($sortBy === 'stock_desc') {
            $query->orderBy('iss.current_stock', 'desc');
        } elseif ($sortBy === 'item_id') {
            $query->orderBy('iss.item_id', 'asc');
        } else {
            $query->orderBy('iss.current_stock', 'desc');
        }

        // Fetch dataset (support pagination or full dataset)
        $limit = $searchdata['limit'] ?? null;
        if (is_numeric($limit) && (int) $limit > 0) {
            $rawList = $query->limit((int) $limit)->get();
        } else {
            $rawList = $query->get();
        }

        // 3. Process Records and Compute Non-Negative Valuations
        $processedList = [];
        $filteredQty = 0;
        $filteredPurchaseValue = 0;
        $filteredSellingValue = 0;

        foreach ($rawList as $row) {
            $qty = floatval($row->current_stock);
            $cost = floatval($row->purchase_price);
            $sell = floatval($row->selling_price);

            // Stock value and displayed stock (-) asbe na, (-)value holeo 0 takbe
            $displayQty = max(0, $qty);

            if ($qty > 0) {
                $rowPurchaseValue = round($qty * $cost, 2);
                $rowSellingValue  = round($qty * $sell, 2);
                $rowProfit        = max(0, round($rowSellingValue - $rowPurchaseValue, 2));
                $rowMargin        = $rowSellingValue > 0 ? round(($rowProfit / $rowSellingValue) * 100, 2) : 0;

                $filteredQty           += $qty;
                $filteredPurchaseValue += $rowPurchaseValue;
                $filteredSellingValue  += $rowSellingValue;
            } else {
                // Negative or 0 stock => 0 valuation
                $rowPurchaseValue = 0.00;
                $rowSellingValue  = 0.00;
                $rowProfit        = 0.00;
                $rowMargin        = 0.00;
            }

            $row->current_stock        = $displayQty;
            $row->purchase_price       = round($cost, 2);
            $row->selling_price        = round($sell, 2);
            $row->total_purchase_value = $rowPurchaseValue;
            $row->total_selling_value  = $rowSellingValue;
            $row->potential_profit     = $rowProfit;
            $row->margin_percent       = $rowMargin;

            // Structured object for Vue template & export
            $row->item = [
                'id'          => $row->item_id,
                'title'       => $row->item_title,
                'barcode'     => $row->barcode,
                'category_id' => $row->category_id,
                'category'    => $row->category_title ? ['title' => $row->category_title] : null,
                'unit'        => $row->unit_title ? ['title' => $row->unit_title] : null,
            ];
            $row->color = $row->color_title ? ['title' => $row->color_title] : null;
            $row->size  = $row->size_title ? ['title' => $row->size_title] : null;

            $processedList[] = $row;
        }

        $filteredProfit = max(0, $filteredSellingValue - $filteredPurchaseValue);
        $filteredMargin = $filteredSellingValue > 0 ? round(($filteredProfit / $filteredSellingValue) * 100, 2) : 0;

        return response()->json([
            'datas' => $processedList,
            'counts' => [
                'low_stock'      => (int) ($counts->low_stock_count ?? 0),
                'out_of_stock'   => (int) ($counts->out_of_stock_count ?? 0),
                'in_stock'       => (int) ($counts->in_stock_count ?? 0),
                'negative_stock' => (int) ($counts->negative_stock_count ?? 0),
                'all'            => (int) ($counts->all_count ?? 0),
            ],
            'summary' => [
                'total_in'            => (float) ($counts->total_in ?? 0),
                'total_out'           => (float) ($counts->total_out ?? 0),
                'total_current_stock' => (float) ($counts->total_current_stock ?? 0),

                // Global Shop Inventory Valuation (Total mal in shop)
                'global_total_stock_qty'      => round($filteredQty, 2),
                'global_total_purchase_value' => round($filteredPurchaseValue, 2),
                'global_total_selling_value'  => round($filteredSellingValue, 2),
                'global_potential_profit'     => round($filteredProfit, 2),
                'global_potential_margin'     => $filteredMargin,

                // Filtered List Valuation
                'filtered_count'                => count($processedList),
                'filtered_total_qty'            => round($filteredQty, 2),
                'filtered_total_purchase_value' => round($filteredPurchaseValue, 2),
                'filtered_total_selling_value'  => round($filteredSellingValue, 2),
                'filtered_potential_profit'     => round($filteredProfit, 2),
                'filtered_potential_margin'     => $filteredMargin,
            ]
        ]);
    }

    public function incomestatement(Request $request)
    {
        if ($request->format() == 'html') {
            return view('admin.layouts.admin_app');
        }
        $searchdata = $request->all();
        $datas = $this->getIncomeStatement($searchdata);
        return $datas;
    }

    public function expensestatement(Request $request)
    {
        if ($request->format() == 'html') {
            return view('admin.layouts.admin_app');
        }

        $searchdata = $request->all();
        return $this->getExpenseStatement($searchdata);
    }

    public function cashbook(Request $request)
    {
        if ($request->format() == 'html') {
            return view('admin.layouts.admin_app');
        }

        $searchdata = $request->all();
        return $this->getCashBook($searchdata);
    }

    public function ledger(Request $request)
    {
        if ($request->format() == 'html') {
            return view('admin.layouts.admin_app');
        }

        $searchdata = $request->all();
        return $this->getLedger($searchdata);
    }

    public function trialbalance(Request $request)
    {
        if ($request->format() == 'html') {
            return view('admin.layouts.admin_app');
        }

        $searchdata = $request->all();
        return $this->getTrialBalance($searchdata);
    }

    public function receivable(Request $request)
    {
        if ($request->format() == 'html') {
            return view('admin.layouts.admin_app');
        }

        $searchdata = $request->all();
        return $this->getReceivable($searchdata);
    }

    public function payable(Request $request)
    {
        if ($request->format() == 'html') {
            return view('admin.layouts.admin_app');
        }

        $searchdata = $request->all();
        return $this->getPayable($searchdata);
    }

    public function balancesheet(Request $request)
    {
        if ($request->format() == 'html') {
            return view('admin.layouts.admin_app');
        }

        $searchdata = $request->all();
        return $this->getBalancesheet($searchdata);
    }

    public function clientLedger(Request $request)
    {
        if ($request->format() == 'html') {
            return view('admin.layouts.admin_app');
        }

        $searchdata = $request->all();
        return $this->getClientLedger($searchdata);
    }

    public function supplierledger(Request $request)
    {
        if ($request->format() == 'html') {
            return view('admin.layouts.admin_app');
        }

        $data = $request->all();
        $supplierledger = $this->getSupplierLedger($data);
        return $supplierledger;
    }

    public function agentledger(Request $request)
    {
        if ($request->format() == 'html') {
            return view('admin.layouts.admin_app');
        }

        $data = $request->all();
        $agentledger = $this->getAgentLedger($data);
        return $agentledger;
    }

    public function employeeledger(Request $request)
    {
        if ($request->format() == 'html') {
            return view('admin.layouts.admin_app');
        }

        $data = $request->all();
        $employeeledger = $this->getEmployeeLedger($data);
        return $employeeledger;
    }

    public function funds(Request $request)
    {
        if ($request->format() == 'html') {
            return view('admin.layouts.admin_app');
        }

        $searchdata = $request->all();
        $fromDate = !empty($searchdata['from_date']) ? vue_to_server_date($searchdata['from_date']) : null;
        $toDate = !empty($searchdata['to_date']) ? vue_to_server_date($searchdata['to_date']) : null;

        if ($fromDate && $toDate) {
            $fundAccounts = Account::where('is_fund_account', 1)
                ->where('status', 'active')
                ->whereNull('deleted_at')
                ->get();

            $funds = [];
            $totalOpening = 0;
            $totalIncome = 0;
            $totalExpense = 0;

            foreach ($fundAccounts as $acc) {
                // Base opening balance from accounts table
                $baseOpening = ($acc->opening_balance_type == 'Credit') ? -(float)$acc->opening_balance : (float)$acc->opening_balance;

                // Income before from_date
                $priorIncome = Payment::where('fund_account_id', $acc->id)
                    ->where('status', 'active')
                    ->whereNull('deleted_at')
                    ->where('payment_type', 'Receive')
                    ->whereDate('payment_date', '<', $fromDate)
                    ->selectRaw('SUM(amount + IFNULL(discount, 0)) as total')
                    ->value('total') ?? 0;

                // Expense before from_date
                $priorExpense = Payment::where('fund_account_id', $acc->id)
                    ->where('status', 'active')
                    ->whereNull('deleted_at')
                    ->where('payment_type', 'Pay')
                    ->whereDate('payment_date', '<', $fromDate)
                    ->sum('amount');

                $periodOpening = (float)$baseOpening + (float)$priorIncome - (float)$priorExpense;

                // Period income
                $periodIncome = (float)(Payment::where('fund_account_id', $acc->id)
                    ->where('status', 'active')
                    ->whereNull('deleted_at')
                    ->where('payment_type', 'Receive')
                    ->whereDate('payment_date', '>=', $fromDate)
                    ->whereDate('payment_date', '<=', $toDate)
                    ->selectRaw('SUM(amount + IFNULL(discount, 0)) as total')
                    ->value('total') ?? 0);

                // Period expense
                $periodExpense = (float)(Payment::where('fund_account_id', $acc->id)
                    ->where('status', 'active')
                    ->whereNull('deleted_at')
                    ->where('payment_type', 'Pay')
                    ->whereDate('payment_date', '>=', $fromDate)
                    ->whereDate('payment_date', '<=', $toDate)
                    ->sum('amount'));

                $currentBalance = $periodOpening + $periodIncome - $periodExpense;

                $totalOpening += $periodOpening;
                $totalIncome += $periodIncome;
                $totalExpense += $periodExpense;

                $funds[] = [
                    'account_id' => $acc->id,
                    'account_name' => $acc->account_name,
                    'opening_balance' => number_format($periodOpening, 2, '.', ''),
                    'opening_balance_type' => $periodOpening >= 0 ? 'Debit' : 'Credit',
                    'total_income' => number_format($periodIncome, 2, '.', ''),
                    'total_expense' => number_format($periodExpense, 2, '.', ''),
                    'current_balance' => number_format($currentBalance, 2, '.', ''),
                ];
            }

            $total_fund = array_sum(array_column($funds, 'current_balance'));

            return [
                'funds' => $funds,
                'total_fund' => $total_fund,
                'summary' => [
                    'total_opening' => $totalOpening,
                    'total_income' => $totalIncome,
                    'total_expense' => $totalExpense,
                    'total_fund' => $total_fund,
                    'total_accounts' => count($funds),
                ]
            ];
        }

        // All-time snapshot from FundAccountBalance view
        $funds = FundAccountBalance::get();
        $total_fund = (float)$funds->sum('current_balance');
        $totalOpening = (float)$funds->sum('opening_balance');
        $totalIncome = (float)$funds->sum('total_income');
        $totalExpense = (float)$funds->sum('total_expense');

        return [
            'funds' => $funds,
            'total_fund' => $total_fund,
            'summary' => [
                'total_opening' => $totalOpening,
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'total_fund' => $total_fund,
                'total_accounts' => count($funds),
            ]
        ];
    }

    public function sales(Request $request)
    {
        if ($request->format() == 'html') {
            return view('admin.layouts.admin_app');
        }

        $searchdata = $request->all();
        return $this->getSalesReport($searchdata);
    }

    public function salesReturn(Request $request)
    {
        if ($request->format() == 'html') {
            return view('admin.layouts.admin_app');
        }

        $searchdata = $request->all();
        return $this->getSalesReturnReport($searchdata);
    }

    public function coupon(Request $request)
    {
        if ($request->format() == 'html') {
            return view('admin.layouts.admin_app');
        }

        $searchdata = $request->all();
        return $this->getCouponReport($searchdata);
    }

    public function getCouponReport($searchdata)
    {
        $fromDate = !empty($searchdata['from_date']) ? vue_to_server_date($searchdata['from_date']) : null;
        $toDate = !empty($searchdata['to_date']) ? vue_to_server_date($searchdata['to_date']) : null;
        $clientId = !empty($searchdata['client_id']) ? $searchdata['client_id'] : null;
        $type = !empty($searchdata['type']) ? $searchdata['type'] : null;

        $siteSetting = SiteSetting::first();
        $redeemRate = floatval($siteSetting->point_redeem_rate ?: 10);
        $earnRate = floatval($siteSetting->point_earn_rate ?: 1);

        $query = ClientPointTransaction::with([
            'client:id,clientid,name,mobile,points_balance',
            'invoice:id,invoice_no,amount'
        ])
        ->where('status', 'active');

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($fromDate && $toDate) {
            $query->whereDate('transaction_date', '>=', $fromDate)
                  ->whereDate('transaction_date', '<=', $toDate);
        } elseif ($fromDate) {
            $query->whereDate('transaction_date', '>=', $fromDate);
        } elseif ($toDate) {
            $query->whereDate('transaction_date', '<=', $toDate);
        }

        $transactions = $query->latest('id')->get();

        // Summary metrics
        $totalEarned = (float)ClientPointTransaction::where('status', 'active')->sum('points_in');
        $totalRedeemed = (float)ClientPointTransaction::where('status', 'active')->whereIn('type', ['Redeem', 'Convert_To_Cash'])->sum('points_out');
        $totalActivePoints = (float)Client::where('status', 'active')->sum('points_balance');
        $totalLiabilityTk = $redeemRate > 0 ? round($totalActivePoints / $redeemRate, 2) : 0;

        // Filtered period metrics
        $periodEarned = (float)$transactions->sum('points_in');
        $periodRedeemed = (float)$transactions->sum('points_out');
        $periodEquivalentTk = (float)$transactions->sum('amount_equivalent');

        // Client info if single client selected
        $selectedClient = $clientId ? Client::find($clientId) : null;

        return [
            'records' => $transactions,
            'client' => $selectedClient,
            'summary' => [
                'total_earned_all_time' => $totalEarned,
                'total_redeemed_all_time' => $totalRedeemed,
                'total_active_points' => $totalActivePoints,
                'total_liability_tk' => $totalLiabilityTk,
                'period_earned' => $periodEarned,
                'period_redeemed' => $periodRedeemed,
                'period_equivalent_tk' => $periodEquivalentTk,
                'total_transactions' => $transactions->count(),
            ],
            'settings' => [
                'coupon_enabled' => boolval($siteSetting->coupon_enabled ?? 0),
                'point_earn_rate' => $earnRate,
                'point_redeem_rate' => $redeemRate,
                'min_points_to_redeem' => intval($siteSetting->min_points_to_redeem ?? 10),
            ]
        ];
    }

    /**
     * Serial Number & Warranty Lifecycle Audit Report
     */
    public function serial(Request $request)
    {
        if ($request->format() == 'html') {
            return view('admin.layouts.admin_app');
        }

        $params = $request->all();
        $keyword = !empty($params['keyword']) ? trim($params['keyword']) : null;
        $salesStatus = !empty($params['sales_status']) ? $params['sales_status'] : 'all'; // all, sold, unsold
        $warrantyStatus = !empty($params['warranty_status']) ? $params['warranty_status'] : 'all'; // all, active, expired, no_warranty
        $claimStatus = !empty($params['claim_status']) ? $params['claim_status'] : 'all'; // all, claimed, sold_unclaimed_active, sold_unclaimed_expired, unsold
        $itemId = !empty($params['item_id']) ? $params['item_id'] : null;
        $categoryId = !empty($params['category_id']) ? $params['category_id'] : null;
        $fromDate = !empty($params['from_date']) ? vue_to_server_date($params['from_date']) : null;
        $toDate = !empty($params['to_date']) ? vue_to_server_date($params['to_date']) : null;

        // 1. Fetch all sold serial numbers from InvoiceDetails
        $soldRecords = InvoiceDetails::with([
            'invoice.client',
            'item.category:id,title',
            'color:id,title',
            'size:id,title',
        ])
        ->whereNotNull('serial_no')
        ->where('serial_no', '!=', '')
        ->where('status', 'active')
        ->latest('id')
        ->get();

        // 2. Fetch all purchased serial numbers from PurchaseDetail
        $purchaseRecords = PurchaseDetail::with([
            'purchase.supplier:id,org_name,name,mobile',
            'item.category:id,title',
            'color:id,title',
            'size:id,title',
        ])
        ->whereNotNull('serial_no')
        ->where('serial_no', '!=', '')
        ->latest('id')
        ->get();

        // 3. Fetch all warranty claims grouped by serial_no
        $claims = WarrantyClaim::with('creator:id,name')
            ->latest('id')
            ->get()
            ->groupBy('serial_no');

        // Map sold serials by trimmed serial number
        $soldMap = [];
        foreach ($soldRecords as $sr) {
            $serials = preg_split('/[\r\n,]+/', $sr->serial_no, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($serials as $s) {
                $clean = trim($s);
                if (!empty($clean) && !isset($soldMap[$clean])) {
                    $soldMap[$clean] = $sr;
                }
            }
        }

        // Map purchase serials
        $purchaseMap = [];
        foreach ($purchaseRecords as $pr) {
            $serials = preg_split('/[\r\n,]+/', $pr->serial_no, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($serials as $s) {
                $clean = trim($s);
                if (!empty($clean) && !isset($purchaseMap[$clean])) {
                    $purchaseMap[$clean] = $pr;
                }
            }
        }

        // Collect all distinct serials
        $allSerials = array_unique(array_merge(array_keys($purchaseMap), array_keys($soldMap), array_keys($claims->toArray())));
        $today = Carbon::today();

        $rows = [];
        $totalSold = 0;
        $totalUnsold = 0;
        $totalClaimed = 0;
        $totalUnclaimedActive = 0;
        $totalUnclaimedExpired = 0;

        foreach ($allSerials as $serialNo) {
            $isSold = isset($soldMap[$serialNo]);
            $soldDetail = $isSold ? $soldMap[$serialNo] : null;
            $purchaseDetail = isset($purchaseMap[$serialNo]) ? $purchaseMap[$serialNo] : null;
            $serialClaims = $claims->get($serialNo);

            // Determine item and policy
            $item = null;
            $colorTitle = null;
            $sizeTitle = null;

            if ($soldDetail) {
                $item = $soldDetail->item;
                $colorTitle = $soldDetail->color ? $soldDetail->color->title : null;
                $sizeTitle = $soldDetail->size ? $soldDetail->size->title : null;
            } elseif ($purchaseDetail) {
                $item = $purchaseDetail->item;
                $colorTitle = $purchaseDetail->color ? $purchaseDetail->color->title : null;
                $sizeTitle = $purchaseDetail->size ? $purchaseDetail->size->title : null;
            }

            $warrantyType = $item && $item->warranty_type ? $item->warranty_type : 'none';
            $warrantyPeriod = $item && $item->warranty_period ? $item->warranty_period : '';

            // Sales details
            $invoice = $soldDetail ? $soldDetail->invoice : null;
            $client = $invoice ? $invoice->client : null;
            $saleDate = null;
            if ($isSold) {
                $saleDateRaw = $invoice && $invoice->getRawOriginal('invoice_date') ? $invoice->getRawOriginal('invoice_date') : ($soldDetail ? $soldDetail->created_at : null);
                try {
                    $saleDate = $saleDateRaw ? Carbon::parse($saleDateRaw)->format('Y-m-d') : null;
                } catch (\Exception $e) {
                    $saleDate = null;
                }
            }

            // Expiry & Warranty Status
            $expiryDate = null;
            $remainingDays = 0;
            $expiredDays = 0;
            $wStatus = 'unsold';

            if ($isSold) {
                if ($warrantyType === 'none' || empty($warrantyPeriod)) {
                    $wStatus = 'no_warranty';
                } else {
                    $expiryDate = $this->calculateExpiryDateHelper($saleDate, $warrantyPeriod);
                    if ($expiryDate) {
                        $expCarbon = Carbon::parse($expiryDate);
                        if ($today->lte($expCarbon)) {
                            $wStatus = 'active';
                            $remainingDays = $today->diffInDays($expCarbon, false);
                        } else {
                            $wStatus = 'expired';
                            $expiredDays = $expCarbon->diffInDays($today, false);
                        }
                    } else {
                        $wStatus = 'active';
                    }
                }
            }

            // Claim Lifecycle Status
            $hasClaims = ($serialClaims && count($serialClaims) > 0);
            $cStatus = 'unsold';

            if ($hasClaims) {
                $cStatus = 'claimed';
                $totalClaimed++;
            } elseif ($isSold) {
                if ($wStatus === 'active') {
                    $cStatus = 'sold_unclaimed_active';
                    $totalUnclaimedActive++;
                } elseif ($wStatus === 'expired') {
                    $cStatus = 'sold_unclaimed_expired';
                    $totalUnclaimedExpired++;
                } else {
                    $cStatus = 'sold_no_policy';
                }
            } else {
                $cStatus = 'unsold';
            }

            if ($isSold) {
                $totalSold++;
            } else {
                $totalUnsold++;
            }

            // Construct unified row
            $row = [
                'serial_no' => $serialNo,
                'item_id' => $item ? $item->id : null,
                'item_title' => $item ? $item->title : 'Unknown Item',
                'barcode' => $item ? $item->barcode : '',
                'category_id' => $item ? $item->category_id : null,
                'category_title' => $item && $item->category ? $item->category->title : 'N/A',
                'color_title' => $colorTitle,
                'size_title' => $sizeTitle,
                'warranty_type' => $warrantyType,
                'warranty_period' => $warrantyPeriod,
                'is_sold' => $isSold,
                'invoice_id' => $invoice ? $invoice->id : null,
                'invoice_no' => $invoice ? ($invoice->invoice_no ?? 'N/A') : 'N/A',
                'sale_date' => $saleDate,
                'customer_name' => $isSold ? ($client ? $client->name : 'Walk-in Customer') : 'In Stock (Unsold)',
                'customer_mobile' => $client ? $client->mobile : '',
                'customer_address' => $client ? $client->address : '',
                'supplier_name' => $purchaseDetail && $purchaseDetail->purchase && $purchaseDetail->purchase->supplier ? $purchaseDetail->purchase->supplier->org_name : null,
                'purchase_date' => $purchaseDetail && $purchaseDetail->purchase ? $purchaseDetail->purchase->purchase_date : null,
                'warranty_expiry_date' => $expiryDate,
                'warranty_status' => $wStatus,
                'remaining_days' => $remainingDays,
                'expired_days' => $expiredDays,
                'claim_status' => $cStatus,
                'claims_count' => $hasClaims ? count($serialClaims) : 0,
                'claims' => $hasClaims ? $serialClaims->values() : [],
            ];

            // Apply Filters
            if ($salesStatus === 'sold' && !$isSold) continue;
            if ($salesStatus === 'unsold' && $isSold) continue;

            if ($warrantyStatus !== 'all' && $wStatus !== $warrantyStatus) continue;

            if ($claimStatus !== 'all' && $cStatus !== $claimStatus) continue;

            if ($itemId && (!$item || $item->id != $itemId)) continue;
            if ($categoryId && (!$item || $item->category_id != $categoryId)) continue;

            if ($fromDate && $saleDate && $saleDate < $fromDate) continue;
            if ($toDate && $saleDate && $saleDate > $toDate) continue;

            if ($keyword) {
                $kw = strtolower($keyword);
                $match = str_contains(strtolower($serialNo), $kw)
                    || str_contains(strtolower($row['item_title']), $kw)
                    || str_contains(strtolower($row['barcode']), $kw)
                    || str_contains(strtolower($row['customer_name']), $kw)
                    || str_contains(strtolower($row['customer_mobile']), $kw)
                    || str_contains(strtolower($row['invoice_no']), $kw);
                if (!$match) continue;
            }

            $rows[] = $row;
        }

        // Summary metrics
        $summary = [
            'total_serials' => count($allSerials),
            'total_sold' => $totalSold,
            'total_unsold' => $totalUnsold,
            'total_claimed' => $totalClaimed,
            'total_unclaimed_active' => $totalUnclaimedActive,
            'total_unclaimed_expired' => $totalUnclaimedExpired,
            'filtered_count' => count($rows),
        ];

        // Pagination
        $perPage = intval($params['pagination'] ?? 20);
        $page = intval($params['page'] ?? 1);
        $totalRows = count($rows);
        $lastPage = max(1, (int)ceil($totalRows / $perPage));
        $offset = ($page - 1) * $perPage;
        $paginatedRows = array_slice($rows, $offset, $perPage);

        return response()->json([
            'records' => $paginatedRows,
            'summary' => $summary,
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalRows,
                'last_page' => $lastPage,
                'from' => $totalRows > 0 ? $offset + 1 : 0,
                'to' => min($totalRows, $offset + $perPage),
            ]
        ]);
    }

    /**
     * Helper to calculate expiry date string.
     */
    private function calculateExpiryDateHelper($saleDateStr, $warrantyPeriodStr)
    {
        if (empty($saleDateStr) || empty($warrantyPeriodStr)) return null;
        try {
            $saleDate = Carbon::parse($saleDateStr);
            $period = strtolower(trim($warrantyPeriodStr));

            if (str_contains($period, 'lifetime')) {
                return $saleDate->copy()->addYears(50)->format('Y-m-d');
            }

            preg_match('/(\d+)\s*(year|yr|month|mon|day|d)/i', $period, $matches);

            if (!empty($matches)) {
                $amount = intval($matches[1]);
                $unit = strtolower($matches[2]);

                if (str_starts_with($unit, 'y')) {
                    return $saleDate->copy()->addYears($amount)->format('Y-m-d');
                } elseif (str_starts_with($unit, 'm')) {
                    return $saleDate->copy()->addMonths($amount)->format('Y-m-d');
                } elseif (str_starts_with($unit, 'd')) {
                    return $saleDate->copy()->addDays($amount)->format('Y-m-d');
                }
            }

            if (str_contains($period, 'year')) {
                return $saleDate->copy()->addYear()->format('Y-m-d');
            } elseif (str_contains($period, 'month')) {
                return $saleDate->copy()->addMonth()->format('Y-m-d');
            } elseif (str_contains($period, 'week')) {
                return $saleDate->copy()->addWeek()->format('Y-m-d');
            }

            return $saleDate->copy()->addYear()->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Warranty & Guarantee Claims Audit Report
     */
    public function warrantyclaim(Request $request)
    {
        if ($request->format() == 'html') {
            return view('admin.layouts.admin_app');
        }

        $params = $request->all();
        $keyword = !empty($params['keyword']) ? trim($params['keyword']) : null;
        $status = !empty($params['current_status']) ? $params['current_status'] : null;
        $warrantyType = !empty($params['warranty_type']) ? $params['warranty_type'] : null;
        $itemId = !empty($params['item_id']) ? $params['item_id'] : null;
        $categoryId = !empty($params['category_id']) ? $params['category_id'] : null;
        $fromDate = !empty($params['from_date']) ? vue_to_server_date($params['from_date']) : null;
        $toDate = !empty($params['to_date']) ? vue_to_server_date($params['to_date']) : null;

        $query = WarrantyClaim::with([
            'item:id,title,barcode,category_id',
            'item.category:id,title',
            'color:id,title',
            'size:id,title',
            'invoice:id,invoice_no,invoice_date,amount',
            'invoice.client:id,name,mobile,address',
            'creator:id,name',
            'logs' => function ($q) {
                $q->with('creator:id,name')->latest('id');
            }
        ]);

        // Status Filter
        if ($status && $status !== 'all') {
            $query->where('current_status', $status);
        }

        // Coverage Type Filter
        if ($warrantyType && $warrantyType !== 'all') {
            $query->where('warranty_type', $warrantyType);
        }

        // Item / Category Filter
        if ($itemId) {
            $query->where('item_id', $itemId);
        }
        if ($categoryId) {
            $query->whereHas('item', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        // Date Range Filter (by claim_date)
        if ($fromDate && $toDate) {
            $query->whereDate('claim_date', '>=', $fromDate)
                  ->whereDate('claim_date', '<=', $toDate);
        } elseif ($fromDate) {
            $query->whereDate('claim_date', '>=', $fromDate);
        } elseif ($toDate) {
            $query->whereDate('claim_date', '<=', $toDate);
        }

        // Global Keyword Search
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('claim_no', 'like', "%{$keyword}%")
                  ->orWhere('serial_no', 'like', "%{$keyword}%")
                  ->orWhere('customer_name', 'like', "%{$keyword}%")
                  ->orWhere('customer_mobile', 'like', "%{$keyword}%")
                  ->orWhere('problem_description', 'like', "%{$keyword}%")
                  ->orWhereHas('invoice', function ($iq) use ($keyword) {
                      $iq->where('invoice_no', 'like', "%{$keyword}%");
                  })
                  ->orWhereHas('item', function ($iq) use ($keyword) {
                      $iq->where('title', 'like', "%{$keyword}%")
                         ->orWhere('barcode', 'like', "%{$keyword}%");
                  });
            });
        }

        // All matched records for summary
        $allMatched = (clone $query)->get();

        $totalServiceCost = (float)$allMatched->sum('service_cost');
        $totalCustomerCharge = (float)$allMatched->sum('customer_charge');
        $netBalance = $totalCustomerCharge - $totalServiceCost;

        $pendingServiceCount = $allMatched->whereIn('current_status', ['received', 'sent_to_vendor', 'in_service'])->count();
        $repairedReadyCount = $allMatched->whereIn('current_status', ['repaired', 'replaced', 'ready_for_delivery'])->count();
        $deliveredCount = $allMatched->where('current_status', 'delivered')->count();
        $rejectedCount = $allMatched->where('current_status', 'rejected')->count();

        $warrantyCount = $allMatched->where('warranty_type', 'warranty')->count();
        $guaranteeCount = $allMatched->where('warranty_type', 'guarantee')->count();

        $statusBreakdown = [
            'received' => $allMatched->where('current_status', 'received')->count(),
            'sent_to_vendor' => $allMatched->where('current_status', 'sent_to_vendor')->count(),
            'in_service' => $allMatched->where('current_status', 'in_service')->count(),
            'repaired' => $allMatched->where('current_status', 'repaired')->count(),
            'replaced' => $allMatched->where('current_status', 'replaced')->count(),
            'ready_for_delivery' => $allMatched->where('current_status', 'ready_for_delivery')->count(),
            'delivered' => $deliveredCount,
            'rejected' => $rejectedCount,
        ];

        $summary = [
            'total_claims' => $allMatched->count(),
            'total_service_cost' => $totalServiceCost,
            'total_customer_charge' => $totalCustomerCharge,
            'net_balance' => $netBalance,
            'pending_service' => $pendingServiceCount,
            'repaired_ready' => $repairedReadyCount,
            'delivered' => $deliveredCount,
            'rejected' => $rejectedCount,
            'warranty_count' => $warrantyCount,
            'guarantee_count' => $guaranteeCount,
            'status_breakdown' => $statusBreakdown,
        ];

        // Paginated results
        $perPage = intval($params['pagination'] ?? 20);
        $paginated = $query->latest('id')->paginate($perPage);

        return response()->json([
            'records' => $paginated->items(),
            'summary' => $summary,
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'from' => $paginated->firstItem() ?: 0,
                'to' => $paginated->lastItem() ?: 0,
            ]
        ]);
    }
}
