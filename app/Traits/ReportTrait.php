<?php

namespace App\Traits;

use DateTime;
use App\Models\Client;
use App\Models\Account;
use App\Models\Payment;
use App\Models\Employee;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\ExpenseDetail;
use App\Models\PaymentDetail;
use App\Models\VoucherDetail;
use App\Models\Commission;
use App\Models\SalarySheetDetail;
use App\Models\Agent;
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

    public function getItemLadger($itemid, $fromDate = null, $toDate = null)
    {

        if (!$itemid) {
            return response()->json([
                'status'  => false,
                'message' => 'Item ID is required'
            ], 422);
        }

        // ----------------------------
        // CASE 1: Date Range Provided
        // ----------------------------
        if ($fromDate && $toDate) {

            /** 1️⃣ Opening Balance (before from_date) */
            $openingBalance = DB::table('stock_transactions')
                ->where('item_id', $itemid)
                ->where('status', 'active')
                ->where('transaction_date', '<', $fromDate)
                ->selectRaw('
                    COALESCE(SUM(qty_in), 0) - COALESCE(SUM(qty_out), 0)
                    AS opening_balance
                ')
                ->value('opening_balance');

            /** 2️⃣ Start running balance from opening */
            DB::statement("SET @balance := {$openingBalance}");

            /** 3️⃣ Ledger rows (from → to) */
            $ledger = DB::table('stock_transactions as st')
                ->leftJoin('purchases as p', function ($join) {
                    $join->on('p.id', '=', 'st.reference_id')
                        ->where('st.reference_type', 'Purchase');
                })
                ->leftJoin('issues as i', function ($join) {
                    $join->on('i.id', '=', 'st.reference_id')
                        ->where('st.reference_type', 'Issue');
                })
                ->select(
                    'st.transaction_date',
                    'st.transaction_type',
                    'st.reference_type',
                    DB::raw("
                        CASE
                            WHEN st.reference_type = 'Purchase' THEN p.invoiceno
                            WHEN st.reference_type = 'Issue' THEN i.issueno
                            ELSE NULL
                        END AS reference_no
                    "),
                    'st.qty_in',
                    'st.qty_out',
                    DB::raw('(@balance := @balance + st.qty_in - st.qty_out) AS balance')
                )
                ->where('st.item_id', $itemid)
                ->whereBetween('st.transaction_date', [$fromDate, $toDate])
                ->where('st.status', 'active')
                ->orderBy('st.transaction_date')
                ->orderBy('st.id')
                ->get();

            /** 4️⃣ Opening row add (structure unchanged) */
            $openingRow = [
                'transaction_date' => $fromDate,
                'transaction_type' => 'Opening Balance',
                'reference_type'   => null,
                'reference_no'     => null,
                'qty_in'           => 0,
                'qty_out'          => 0,
                'balance'          => $openingBalance
            ];

            $ledger->prepend($openingRow);

            return $ledger;
        }

        // ----------------------------
        // CASE 2: Only Item Ledger
        // ----------------------------
        DB::statement('SET @balance := 0');

        $ledger = DB::table('stock_transactions as st')
            ->leftJoin('purchases as p', function ($join) {
                $join->on('p.id', '=', 'st.reference_id')
                    ->where('st.reference_type', 'Purchase');
            })
            ->leftJoin('issues as i', function ($join) {
                $join->on('i.id', '=', 'st.reference_id')
                    ->where('st.reference_type', 'Issue');
            })
            ->select(
                'st.transaction_date',
                'st.transaction_type',
                'st.reference_type',
                DB::raw("
                    CASE
                        WHEN st.reference_type = 'Purchase' THEN p.invoiceno
                        WHEN st.reference_type = 'Issue' THEN i.issueno
                        ELSE NULL
                    END AS reference_no
                "),
                'st.qty_in',
                'st.qty_out',
                DB::raw('(@balance := @balance + st.qty_in - st.qty_out) AS balance')
            )
            ->where('st.item_id', $itemid)
            ->where('st.status', 'active')
            ->orderBy('st.transaction_date')
            ->orderBy('st.id')
            ->get();

        return $ledger;
    }

    public function getIncomeStatement($searchdata)
    {
        $from = array_key_exists('from_date', $searchdata)
            ? vue_to_server_date($searchdata['from_date'])
            : date('Y-m-01');

        $to = array_key_exists('to_date', $searchdata)
            ? vue_to_server_date($searchdata['to_date'])
            : date('Y-m-t');


        // =========================
        // TOTAL INCOME
        // =========================
        $total_income = VoucherDetail::whereNull('voucher_details.deleted_at')
            ->where('voucher_details.status', 'active')
            ->whereHas('account', function ($q) {
                $q->where('accounts.account_type', 'Income')
                    ->where('accounts.status', 'active')
                    ->whereNull('accounts.deleted_at');
            })
            ->whereHas('voucher', function ($q) use ($from, $to) {
                $q->whereBetween('vouchers.voucher_date', [$from, $to])
                    ->whereNull('vouchers.deleted_at')
                    ->where('vouchers.status', 'active');
            })
            ->sum('voucher_details.cr_amount');


        // =========================
        // TOTAL EXPENSE
        // =========================
        $total_expense = VoucherDetail::whereNull('voucher_details.deleted_at')
            ->where('voucher_details.status', 'active')
            ->whereHas('account', function ($q) {
                $q->where('accounts.account_type', 'Expense')
                    ->where('accounts.status', 'active')
                    ->whereNull('accounts.deleted_at');
            })
            ->whereHas('voucher', function ($q) use ($from, $to) {
                $q->whereBetween('vouchers.voucher_date', [$from, $to])
                    ->whereNull('vouchers.deleted_at')
                    ->where('vouchers.status', 'active');
            })
            ->sum('voucher_details.dr_amount');


        // =========================
        // NET PROFIT
        // =========================
        $net_profit = $total_income - $total_expense;


        // =========================
        // DETAILS
        // =========================
        $details = VoucherDetail::selectRaw(
            'account_id,
            SUM(dr_amount) as total_debit,
            SUM(cr_amount) as total_credit'
        )
            ->with([
                'account:id,account_code,account_name,account_type'
            ])
            ->whereNull('voucher_details.deleted_at')
            ->where('voucher_details.status', 'active')
            ->whereHas('account', function ($q) {
                $q->whereIn('accounts.account_type', ['Income', 'Expense'])
                    ->where('accounts.status', 'active')
                    ->whereNull('accounts.deleted_at');
            })
            ->whereHas('voucher', function ($q) use ($from, $to) {
                $q->whereBetween('vouchers.voucher_date', [$from, $to])
                    ->whereNull('vouchers.deleted_at')
                    ->where('vouchers.status', 'active');
            })
            ->groupBy('account_id')
            ->get();


        return response()->json([
            'from' => $from,
            'to' => $to,
            'total_income' => (float) $total_income,
            'total_expense' => (float) $total_expense,
            'net_profit' => (float) $net_profit,
            'details' => $details,
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
                'status'  => false,
                'message' => 'Client ID is required'
            ], 422);
        }

        $client = Client::find($client_id);
        if (!$client) {
            return response()->json([
                'status'  => false,
                'message' => 'Client not found'
            ], 404);
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
                'type'    => 'error',
                'message' => 'Supplier ID is required'
            ], 422);
        }

        $supplier = Supplier::find($supplier_id);
        if (!$supplier) {
            return response()->json([
                'status'  => false,
                'message' => 'Supplier not found'
            ], 404);
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
}
