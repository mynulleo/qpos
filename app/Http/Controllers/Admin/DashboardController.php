<?php

namespace App\Http\Controllers\Admin;

use App\Models\Invoice;
use App\Models\Package;
use App\Models\Service;
use App\Models\Residence;
use App\Models\System\Menu;
use Illuminate\Http\Request;
use App\Models\UserLoginHistory;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ItemStockSummary;
use App\Models\FundAccountBalance;
use App\Models\Account;
use App\Models\Payment;
use App\Models\Item;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\Models\Activity;


class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = auth('admin')->user();

        if ($request->ajax()) {
            $role = $user ? $user->role_id : null;

            switch ($role) {
                case 1:
                case 2:
                case 8:
                    $dashboardData = $this->getAdminDashboard();
                    break;
                default:
                    $dashboardData = $this->getCommonDashboard();
                    break;
            }

            return response()->json([
                'role_id' => $role,
                'dashboard' => $dashboardData,
            ]);
        }

        return view('admin.layouts.admin_app');
    }

    public function getAdminDashboard()
    {
        $menus = Menu::get();
        $dashboardMenus = Menu::query()
            ->where('show_dasboard', true)
            ->where(function ($q) {
                $q->where('status', 'active')->orWhereNull('status');
            })
            ->get()
            ->each(function ($menu) use ($menus) {
                $getParentById = function ($id, $parents) {
                    $parent = $parents->firstWhere('id', $id);

                    return $parent;
                };

                $parent = $getParentById($menu->parent_id, $menus);

                if (! is_null($parent)) {
                    $menu->parent_title = $parent->menu_name;
                } else {
                    $menu->parent_title = $menu->menu_name;
                }

                // Resolve the model class and count the total data
                if ($menu->module_name && class_exists($menu->module_name)) {
                    try {
                        $model = $menu->module_name;
                        $menu->total_data_count = $model::count();
                    } catch (\Throwable $e) {
                        $menu->total_data_count = 0;
                    }
                } else {
                    $menu->total_data_count = 0;
                }
            });

        return [
            'dashboardMenus' => $dashboardMenus,
            'pieData' => $this->pieData(),
            'fundData' => $this->fundData(),
            'barData' => $this->barData(),
            'stockData' => $this->stockData(),
            'zeroStockData' => $this->zeroStockData(),
            'lowStockData' => $this->lowStockData(),
            'recentInvoices' => $this->recentInvoices(),
            'clientSummary' => $this->clientSummary(),
            'statisticsData' => $this->statisticsData(),
        ];
    }

    public function getCommonDashboard()
    {
        $menus = Menu::get();
        $dashboardMenus = Menu::query()
            ->where('show_dasboard', true)
            ->where(function ($q) {
                $q->where('status', 'active')->orWhereNull('status');
            })
            ->get()
            ->each(function ($menu) use ($menus) {
                $getParentById = function ($id, $parents) {
                    $parent = $parents->firstWhere('id', $id);

                    return $parent;
                };

                $parent = $getParentById($menu->parent_id, $menus);

                if (! is_null($parent)) {
                    $menu->parent_title = $parent->menu_name;
                } else {
                    $menu->parent_title = $menu->menu_name;
                }

                // Resolve the model class and count the total data
                if ($menu->module_name && class_exists($menu->module_name)) {
                    try {
                        $model = $menu->module_name;
                        $menu->total_data_count = $model::count();
                    } catch (\Throwable $e) {
                        $menu->total_data_count = 0;
                    }
                } else {
                    $menu->total_data_count = 0;
                }
            });

        return [
            'dashboardMenus' => $dashboardMenus,
            'barData' => $this->barData(),
            'stockData' => $this->stockData(),
            'zeroStockData' => $this->zeroStockData(),
            'lowStockData' => $this->lowStockData(),
            'recentInvoices' => $this->recentInvoices(),
            'clientSummary' => $this->clientSummary(),
            'statisticsData' => $this->statisticsData(),
        ];
    }

    public function statisticsData()
    {
        $month = date('m');
        $year  = date('Y');
        $today = date('Y-m-d');

        $totalItems = Schema::hasTable('items') ? DB::table('items')
            ->where('status', 'active')
            ->count() : 0;

        $totalSuppliers = Schema::hasTable('suppliers') ? DB::table('suppliers')
            ->where('status', 'active')
            ->count() : 0;

        $totalClients = Schema::hasTable('clients') ? DB::table('clients')
            ->where('status', 'active')
            ->count() : 0;

        // Sales statistics
        $todaySales = 0;
        $todaySalesCount = 0;
        $todayReceived = 0;
        $monthSales = 0;
        $monthSalesCount = 0;
        $monthReceived = 0;

        if (Schema::hasTable('invoices')) {
            $todayInvoices = DB::table('invoices')
                ->whereDate('invoice_date', $today)
                ->where('status', 'active')
                ->whereNull('deleted_at');
            $todaySales = (float) $todayInvoices->sum('amount');
            $todaySalesCount = (int) $todayInvoices->count();
            $todayReceived = (float) $todayInvoices->sum('paid_amount');

            $monthInvoices = DB::table('invoices')
                ->whereYear('invoice_date', $year)
                ->whereMonth('invoice_date', $month)
                ->where('status', 'active')
                ->whereNull('deleted_at');
            $monthSales = (float) $monthInvoices->sum('amount');
            $monthSalesCount = (int) $monthInvoices->count();
            $monthReceived = (float) $monthInvoices->sum('paid_amount');
        }

        $todayExpense = Schema::hasTable('expenses') ? DB::table('expenses')
            ->whereDate('expense_date', $today)
            ->where('status', 'active')
            ->sum('total_amount') : 0;

        $currentMonthExpense = Schema::hasTable('expenses') ? DB::table('expenses')
            ->whereMonth('expense_date', $month)
            ->whereYear('expense_date', $year)
            ->where('status', 'active')
            ->sum('total_amount') : 0;

        $currentMonthPurchase = Schema::hasTable('purchases') ? DB::table('purchases')
            ->whereMonth('purchase_date', $month)
            ->whereYear('purchase_date', $year)
            ->where('status', 'active')
            ->sum('total_amount') : 0;

        $todayPurchase = Schema::hasTable('purchases') ? DB::table('purchases')
            ->whereDate('purchase_date', $today)
            ->where('status', 'active')
            ->sum('total_amount') : 0;

        $lowStockCount = 0;
        if (Schema::hasTable('item_stock_summaries')) {
            $lowStockCount = DB::table('item_stock_summaries')
                ->where('current_stock', '<=', 5)
                ->count();
        }

        $pendingWarrantyCount = 0;
        if (Schema::hasTable('warranty_claims')) {
            $pendingWarrantyCount = DB::table('warranty_claims')
                ->where('status', 'pending')
                ->count();
        }

        return [
            'today_sales'                   => (float) $todaySales,
            'today_sales_count'             => (int) $todaySalesCount,
            'today_received'                => (float) $todayReceived,
            'month_sales'                   => (float) $monthSales,
            'month_sales_count'             => (int) $monthSalesCount,
            'month_received'                => (float) $monthReceived,
            'today_expense'                 => (float) $todayExpense,
            'current_month_expense'         => (float) $currentMonthExpense,
            'today_purchase'                => (float) $todayPurchase,
            'current_month_purchase'        => (float) $currentMonthPurchase,
            'total_items'                   => (int) $totalItems,
            'total_suppliers'               => (int) $totalSuppliers,
            'total_clients'                 => (int) $totalClients,
            'low_stock_count'               => (int) $lowStockCount,
            'pending_warranty_count'        => (int) $pendingWarrantyCount,
        ];
    }

    public function fundData()
    {
        try {
            $funds = collect([]);
            if (class_exists(FundAccountBalance::class) && Schema::hasTable('fund_account_balance')) {
                $funds = FundAccountBalance::get();
            }

            if ($funds->isEmpty() && Schema::hasTable('accounts')) {
                $fundAccounts = Account::where('is_fund_account', 1)
                    ->where('status', 'active')
                    ->whereNull('deleted_at')
                    ->get();
                $calculatedFunds = [];
                foreach ($fundAccounts as $acc) {
                    $baseOpening = ($acc->opening_balance_type == 'Credit') ? -(float)$acc->opening_balance : (float)$acc->opening_balance;
                    $totIncome = 0;
                    $totExpense = 0;
                    if (Schema::hasTable('payments')) {
                        $totIncome = Payment::where('fund_account_id', $acc->id)
                            ->where('status', 'active')
                            ->whereNull('deleted_at')
                            ->where('payment_type', 'Receive')
                            ->selectRaw('SUM(amount + IFNULL(discount, 0)) as total')
                            ->value('total') ?? 0;
                        $totExpense = Payment::where('fund_account_id', $acc->id)
                            ->where('status', 'active')
                            ->whereNull('deleted_at')
                            ->where('payment_type', 'Pay')
                            ->sum('amount') ?? 0;
                    }
                    $currBal = (float)$baseOpening + (float)$totIncome - (float)$totExpense;

                    $calculatedFunds[] = (object)[
                        'account_id' => $acc->id,
                        'account_name' => $acc->account_name,
                        'opening_balance' => $baseOpening,
                        'total_income' => (float)$totIncome,
                        'total_expense' => (float)$totExpense,
                        'current_balance' => (float)$currBal,
                    ];
                }
                $funds = collect($calculatedFunds);
            }

            $totalLiquidity = (float) $funds->sum('current_balance');

            return [
                'accounts' => $funds,
                'total_liquidity' => $totalLiquidity,
            ];
        } catch (\Throwable $e) {
            return [
                'accounts' => [],
                'total_liquidity' => 0,
            ];
        }
    }

    public function zeroStockData()
    {
        try {
            // 1. Get sold quantities per item from invoice_details
            $soldCounts = [];
            if (Schema::hasTable('invoice_details')) {
                $soldCounts = DB::table('invoice_details')
                    ->where('status', 'active')
                    ->select('item_id', DB::raw('SUM(qty) as total_sold'))
                    ->groupBy('item_id')
                    ->pluck('total_sold', 'item_id')
                    ->toArray();
            }

            // 2. Get current stock sum per item from item_stock_summaries
            $itemStocks = [];
            if (Schema::hasTable('item_stock_summaries')) {
                $itemStocks = DB::table('item_stock_summaries')
                    ->select('item_id', DB::raw('SUM(current_stock) as total_stock'))
                    ->groupBy('item_id')
                    ->pluck('total_stock', 'item_id')
                    ->toArray();
            }

            // 3. Get all active items
            $allItems = Item::where('status', 'active')
                ->with('category:id,title', 'unit:id,title')
                ->get();

            $zeroStockItems = [];

            foreach ($allItems as $item) {
                $stock = isset($itemStocks[$item->id]) ? (float)$itemStocks[$item->id] : 0;
                if ($stock <= 0) {
                    $sold = isset($soldCounts[$item->id]) ? (float)$soldCounts[$item->id] : 0;
                    $zeroStockItems[] = (object)[
                        'item_id' => $item->id,
                        'title' => $item->title,
                        'barcode' => $item->barcode,
                        'category_title' => $item->category ? $item->category->title : 'General',
                        'unit_title' => $item->unit ? $item->unit->title : 'Pcs',
                        'current_stock' => $stock,
                        'total_sold_qty' => $sold,
                    ];
                }
            }

            // Sort descending by total_sold_qty (most sold zero-stock items first)
            usort($zeroStockItems, function ($a, $b) {
                return $b->total_sold_qty <=> $a->total_sold_qty;
            });

            return array_slice($zeroStockItems, 0, 5);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function recentInvoices()
    {
        try {
            if (!Schema::hasTable('invoices')) {
                return [];
            }

            return DB::table('invoices')
                ->leftJoin('clients', 'invoices.client_id', '=', 'clients.id')
                ->select(
                    'invoices.id',
                    'invoices.invoice_no',
                    'invoices.invoice_date',
                    'invoices.amount',
                    'invoices.paid_amount',
                    'invoices.is_closed',
                    'invoices.created_at',
                    'clients.name as client_name',
                    'clients.mobile as client_mobile'
                )
                ->where('invoices.status', 'active')
                ->whereNull('invoices.deleted_at')
                ->latest('invoices.id')
                ->take(6)
                ->get();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function lowStockData()
    {
        try {
            if (!Schema::hasTable('item_stock_summaries')) {
                return [];
            }

            return ItemStockSummary::with('item:id,title,barcode,category_id', 'item.category:id,title')
                ->where('current_stock', '<=', 5)
                ->orderBy('current_stock', 'asc')
                ->take(8)
                ->get();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function stockData()
    {
        try {
            if (!Schema::hasTable('item_stock_summaries')) {
                return [];
            }

            return ItemStockSummary::with('item:id,title,barcode,category_id,unit_id', 'item.category:id,title', 'item.unit:id,title', 'color:id,title', 'size:id,title')
                ->orderBy('item_id', 'asc')
                ->take(10)
                ->get();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function clientSummary()
    {
        try {
            $today = date('Y-m-d');
            $pm_first_date = date('Y-m-01', strtotime('first day of last month'));
            $pm_last_date  = date('Y-m-t', strtotime('last day of last month'));
            $cm_first_date = date('Y-m-01');

            $totalClients = 0;
            $previous_month_clients = 0;
            $current_month_clients = 0;
            if (Schema::hasTable('clients')) {
                $totalClients = DB::table('clients')->where('status', 'active')->count();
                $previous_month_clients = DB::table('clients')->where('status', 'active')->whereBetween('reg_date', [$pm_first_date, $pm_last_date])->count();
                $current_month_clients = DB::table('clients')->where('status', 'active')->whereBetween('reg_date', [$cm_first_date, $today])->count();
            }

            $total_invoice_due = 0;
            $pm_invoice_due = 0;
            $cm_invoice_due = 0;
            if (Schema::hasTable('invoices')) {
                $total_invoice_due = DB::table('invoices')
                    ->where('status', 'active')
                    ->select(DB::raw('SUM(amount - IFNULL(paid_amount,0)) as due'))
                    ->value('due');

                $pm_invoice_due = DB::table('invoices')
                    ->where('status', 'active')
                    ->whereBetween('invoice_date', [$pm_first_date, $pm_last_date])
                    ->select(DB::raw('SUM(amount - IFNULL(paid_amount,0)) as due'))
                    ->value('due');

                $cm_invoice_due = DB::table('invoices')
                    ->where('status', 'active')
                    ->whereBetween('invoice_date', [$cm_first_date, $today])
                    ->select(DB::raw('SUM(amount - IFNULL(paid_amount,0)) as due'))
                    ->value('due');
            }

            return [
                'total_clients'     => $totalClients,
                'pm_clients'        => $previous_month_clients,
                'cm_clients'        => $current_month_clients,
                'total_outstanding' => round($total_invoice_due ?? 0, 2),
                'pm_due'            => round($pm_invoice_due ?? 0, 2),
                'cm_due'            => round($cm_invoice_due ?? 0, 2),
                'invoice_due_total' => round($total_invoice_due ?? 0, 2)
            ];
        } catch (\Throwable $e) {
            return [
                'total_clients'     => 0,
                'pm_clients'        => 0,
                'cm_clients'        => 0,
                'total_outstanding' => 0,
                'pm_due'            => 0,
                'cm_due'            => 0,
                'invoice_due_total' => 0
            ];
        }
    }

    /**
     * Current Month Sales Bar chart
     *
     * @return array
     */
    public function barData()
    {
        try {
            $year = date('Y');
            $month = date('m');
            $daysInMonth = (int) date('t');
            $monthName = date('F Y');

            $invoiceMap = [];
            $receiveMap = [];

            if (Schema::hasTable('invoices')) {
                $invoices = DB::table('invoices')
                    ->selectRaw("
                        DAY(invoice_date) as day,
                        SUM(amount) as total_invoice,
                        SUM(COALESCE(paid_amount, 0)) as total_receive
                    ")
                    ->whereYear('invoice_date', $year)
                    ->whereMonth('invoice_date', $month)
                    ->where('status', 'active')
                    ->whereNull('deleted_at')
                    ->groupBy(DB::raw('DAY(invoice_date)'))
                    ->orderBy(DB::raw('DAY(invoice_date)'))
                    ->get();

                foreach ($invoices as $row) {
                    $invoiceMap[(int) $row->day] = (float) $row->total_invoice;
                    $receiveMap[(int) $row->day] = (float) $row->total_receive;
                }
            }

            $labels = [];
            $invoice = [];
            $receive = [];
            $due = [];

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $labels[] = str_pad($d, 2, '0', STR_PAD_LEFT) . ' ' . date('M');
                $invAmt = $invoiceMap[$d] ?? 0;
                $recAmt = $receiveMap[$d] ?? 0;
                $dueAmt = max(0, $invAmt - $recAmt);

                $invoice[] = round($invAmt, 2);
                $receive[] = round($recAmt, 2);
                $due[] = round($dueAmt, 2);
            }

            return [
                'month_name' => $monthName,
                'labels' => $labels,
                'data' => [
                    'invoice' => $invoice,
                    'receive' => $receive,
                    'due' => $due,
                ],
                'total_sales' => array_sum($invoice),
                'total_receive' => array_sum($receive),
                'total_due' => array_sum($due),
            ];
        } catch (\Throwable $e) {
            return [
                'month_name' => date('F Y'),
                'labels' => [],
                'data' => ['invoice' => [], 'receive' => [], 'due' => []],
                'total_sales' => 0,
                'total_receive' => 0,
                'total_due' => 0,
            ];
        }
    }

    /**
     * Pie chart
     *
     * @return array
     */
    public function pieData()
    {
        try {
            $fundData = $this->fundData();
            $accounts = $fundData['accounts'] ?? [];

            $labels = [];
            $data   = [];

            foreach ($accounts as $account) {
                $bal = (float) ($account->current_balance ?? 0);
                if ($bal > 0) {
                    $labels[] = $account->account_name ?? 'Account';
                    $data[]   = $bal;
                }
            }

            return [
                'labels' => $labels,
                'data'   => $data,
            ];
        } catch (\Throwable $e) {
            return [
                'labels' => [],
                'data'   => [],
            ];
        }
    }
}
