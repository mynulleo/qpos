<?php

namespace App\Traits;

use App\Models\Account;
use App\Models\Commission;
use App\Models\Expense;
use App\Models\ExpenseDetail;
use App\Models\Invoice;
use App\Models\LoanInfo;
use App\Models\LoanInstallment;
use App\Models\Payment;
use App\Models\PaymentDetail;
use App\Models\Purchase;
use App\Models\Grn;
use App\Models\SalarySheetDetail;

trait PaymentTrait
{

    /**
     * Retrieve all due invoices and bandwidth charges for a client.
     *
     * Fetches unpaid bandwidth history records and invoices with pending amounts
     * for the specified client, formatting them into a standardized array structure
     * containing reference type, ID, descriptive info, and amount due.
     *
     * @param int $clientid The ID of the client
     * @return array An array of due items with keys: reference_type, reference_id, reference_info, amount
     */
    public function clientDueInvoices($clientid)
    {
        $datas = [];

        $invoices = Invoice::where('client_id', $clientid)->where('is_closed', 0)->get();

        if ($invoices) {
            foreach ($invoices as $idata) {

                $paid_amount = $this->getRefPaidAmount('Invoice', $idata->id);

                $due_amount = $idata->amount - $paid_amount;

                $data = [
                    'reference_type'    => 'Invoice',
                    'reference_id'      => $idata->id,
                    'account_id'        => $this->getAccountID('accounts-receivable'),
                    'reference_info'    => $idata->invoice_no . '-' . date('d M, Y', strtotime($idata->invoice_date)),
                    'amount'            => $idata->amount,
                    'paid_amount'       => $paid_amount,
                    'due_amount'        => $due_amount,
                    'ref_module'        => 'Invoice',
                    'ref_module_id'     => $idata->id,
                ];
                $datas[] = $data;
            }
        }
        return $datas;
    }

    public function EmployeeLoanInstallment($empid)
    {
        $datas = [];
        $today = date('Y-m-d');
        $currentMonth = date('Y-m'); // যেমন: 2026-02
        $currentYear  = date('Y');
        $currentMonthNo = date('m');

        $loans = LoanInstallment::with('loaninfo')->where('employee_id', $empid)
            ->where('deduct_from_salary', 0)
            ->whereDate('instalment_date', '<=', $today)
            ->where('is_closed', 0)
            ->whereHas('loaninfo', function ($q) {
                $q->whereNotNull('approved_by');
            })
            ->get();

        foreach ($loans as $loan) {

            // All conditions passed → datas এ ঢুকবে
            $employeeLoanAccountId = $this->getAccountID('employee-loan-receivable');

            $datas[] = [
                'reference_type' => 'LoanInstallment',
                'reference_id'   => $loan->id,
                'account_id'     => $employeeLoanAccountId,
                'reference_info' => $loan->type . ' TrnsID-' . $loan->loaninfo?->trnsid . ' | Amount: ' . $loan->loninfo?->amount
                    . ' | Approved: ' . $loan->loaninfo?->approved_date,
                'amount'         => (float) $loan->amount,
                'paid_amount'    => 0,
                'due_amount'     => (float) $loan->amount,
            ];
        }

        return $datas;
    }

    public function supplierPurchaseInvoices($supid)
    {
        $datas = [];

        // 1. Primary: Fetch unclosed GRNs for this supplier (pay for received goods)
        $grns = Grn::with('purchase:id,invoiceno')
            ->where('supplier_id', $supid)
            ->where('is_closed', 0)
            ->where('status', 'active')
            ->get();

        if ($grns && count($grns) > 0) {
            foreach ($grns as $grn) {
                $paid_amount = (float) $this->getRefPaidAmount('GRN', $grn->id);
                $due_amount = (float) ($grn->total_amount - $paid_amount);

                if ($due_amount > 0) {
                    $poInfo = $grn->purchase ? ' (PO: ' . $grn->purchase->invoiceno . ')' : '';
                    $datas[] = [
                        'reference_type'    => 'GRN',
                        'reference_id'      => $grn->id,
                        'account_id'        => $this->getExpenseAccount('Purchase'),
                        'reference_info'    => 'GRN: ' . $grn->grn_no . $poInfo . ' - ' . $grn->grn_date,
                        'amount'            => (float) $grn->total_amount,
                        'paid_amount'       => $paid_amount,
                        'due_amount'        => $due_amount,
                    ];
                }
            }
        }

        // 2. Fallback: Legacy purchases without GRN
        $pinvoices = Purchase::where('supplier_id', $supid)
            ->where('is_closed', 0)
            ->whereDoesntHave('grns')
            ->get();

        if ($pinvoices && count($pinvoices) > 0) {
            foreach ($pinvoices as $idata) {
                $paid_amount = (float) $this->getRefPaidAmount('Purchase', $idata->id);
                $due_amount = (float) ($idata->total_amount - $paid_amount);

                if ($due_amount > 0) {
                    $datas[] = [
                        'reference_type'    => 'Purchase',
                        'reference_id'      => $idata->id,
                        'account_id'        => $this->getExpenseAccount('Purchase'),
                        'reference_info'    => 'PO: ' . $idata->invoiceno . ' - ' . $idata->purchase_date,
                        'amount'            => (float) $idata->total_amount,
                        'paid_amount'       => $paid_amount,
                        'due_amount'        => $due_amount,
                    ];
                }
            }
        }

        return $datas;
    }

    public function getEmpPayableDatas($empid)
    {
        $datas = [];

        // Salary Sheet data start
        $details = SalarySheetDetail::with('salary_sheet')
            ->where('employee_id', $empid)
            ->where('is_paid', 0)
            ->whereHas('salary_sheet', function ($q) {
                $q->whereNotNull('approved_by');
            })
            ->get();

        $datas = $details->map(function ($d) {
            return [
                'reference_type' => 'SalarySheetDetail',
                'reference_id'   => $d->id,
                'account_id'     => $this->getExpenseAccount('SalarySheetDetail'),
                'reference_info' => $d->salary_sheet->month . ' ' . $d->salary_sheet->year,
                'amount'         => $d->total,
                'paid_amount'    => 0,
                'due_amount'     => $d->total,
            ];
        })->toArray();

        // Salary Sheet data end

        // Loan data start
        $loanhistories = LoanInfo::where('employee_id', $empid)
            ->where('is_paid', 0)
            ->whereNotNull('approved_by')
            ->get();

        foreach ($loanhistories as $loan) {
            $employee_loan_account_id = $this->getAccountID('employee-loan-receivable');
            $data = [
                'reference_type' => 'LoanInfo',
                'reference_id'   => $loan->id,
                'account_id'     => $employee_loan_account_id,
                'reference_info' => $loan->trns_type . 'TrnsID -' . $loan->trnsid . '<br> Amount - ' . $loan->amount . '<br> Approved Date -' . $loan->approved_date,
                'amount'         => $loan->amount,
                'paid_amount'    => 0,
                'due_amount'     => $loan->amount,
            ];
            $datas[] = $data;
        }
        // Loan data end

        // Loan data start
        $expense_details = ExpenseDetail::with('expense')
            ->where('is_closed', 0)
            ->whereHas('expense', function ($q) use ($empid) {
                $q->where('employee_id', $empid)
                    ->whereNotNull('approved_by');
            })
            ->get();

        foreach ($expense_details as $detail) {

            $expense = $detail->expense;

            $data = [
                'reference_type' => 'ExpenseDetail',
                'reference_id'   => $detail->id,
                'account_id'     => $detail->account_id,
                'reference_info' => 'Exp Date -' . $expense->expense_date . '<br>' . $detail->narration,
                'amount'         => $detail->amount,
                'paid_amount'    => 0,
                'due_amount'     => $detail->amount,
            ];

            $datas[] = $data;
        }

        $commissions = Commission::with('client:id,name')
            ->where('status', 'active')
            ->where('is_closed', 0)
            ->where('approved_by', '!=', null)
            ->get();

        if ($commissions) {
            $commission_account_id = Account::where('system_key_name', 'commission-expense')->first()->id;
            foreach ($commissions as $commission) {
                $data = [
                    'reference_type' => 'Commission',
                    'reference_id'   => $commission->id,
                    'account_id'     => $commission_account_id,
                    'reference_info' => 'Commission for the client -' .  $commission->client?->name,
                    'amount'         => $commission->amount,
                    'paid_amount'    => 0,
                    'due_amount'     => $commission->amount,
                ];

                $datas[] = $data;
            }
        }

        return $datas;
    }

    public function getAgentPayableDatas($agentId)
    {
        $datas = [];

        $commissions = Commission::with('client:id,name')
            ->where('agent_id', $agentId)
            ->where('status', 'active')
            ->where('is_closed', 0)
            ->whereNotNull('approved_by')
            ->get();

        if ($commissions) {
            $commission_account = Account::where('system_key_name', 'commission-expense')->first();
            $commission_account_id = $commission_account ? $commission_account->id : null;
            foreach ($commissions as $commission) {
                $paid_amount = $this->getRefPaidAmount('Commission', $commission->id);
                $due_amount = (float) ($commission->amount - $paid_amount);

                if ($due_amount > 0) {
                    $datas[] = [
                        'reference_type' => 'Commission',
                        'reference_id'   => $commission->id,
                        'account_id'     => $commission_account_id,
                        'reference_info' => 'Commission for Client - ' . ($commission->client?->name ?? 'N/A'),
                        'amount'         => (float) $commission->amount,
                        'paid_amount'    => (float) $paid_amount,
                        'due_amount'     => $due_amount,
                    ];
                }
            }
        }

        return $datas;
    }

    public function getofficeexpenses()
    {
        $datas = [];
        $records = ExpenseDetail::with('expense')
            ->whereHas('expense', function ($q) {
                $q->whereNull('employee_id');
                $q->whereNotNull('approved_by');
            })
            ->where('is_closed', 0)
            ->get();

        foreach ($records as $record) {
            $paid_amount = $this->getRefPaidAmount('ExpenseDetail', $record->id);
            $data = [
                'reference_type' => 'ExpenseDetail',
                'reference_id'   => $record->id,  //Expense Detail ID
                'reference_info' => 'exp ID -' . $record->expense?->expenseid . ', <br> Exp Date -' . $record->expense?->expense_date,
                'account_id'     => $record->account_id,
                'amount'         => $record->amount,
                'paid_amount'    => $paid_amount,
                'due_amount'     => (float) ($record->amount - $paid_amount),
            ];
            $datas[] = $data;
        }

        //Commission expenses
        $commissions = Commission::with('client:id,name')
            ->where('status', 'active')
            ->where('is_closed', 0)
            ->whereNull('employee_id')
            ->whereNotNull('approved_by')
            ->get();

        if ($commissions) {
            $commission_account_id = Account::where('system_key_name', 'commission-expense')->first()->id;
            foreach ($commissions as $commission) {
                $paid_amount = $this->getRefPaidAmount('Commission', $commission->id);
                $data = [
                    'reference_type' => 'Commission',
                    'reference_id'   => $commission->id,
                    'account_id'     => $commission_account_id,
                    'reference_info' => 'Commission for the client -' .  $commission->client?->name,
                    'amount'         => $commission->amount,
                    'paid_amount'    => $paid_amount,
                    'due_amount'     => (float) ($commission->amount - $paid_amount),
                ];

                $datas[] = $data;
            }
        }
        return $datas;
    }

    public function getRefPaidAmount($ref_type, $ref_id)
    {
        $paid_amt = PaymentDetail::where('reference_type', $ref_type)
            ->where('reference_id', $ref_id)
            ->whereNull('deleted_at')
            ->sum('amount');
        return (float) $paid_amt;
    }

    /**
     * Update the reference value based on reference type.
     *
     * Routes the update operation to the appropriate handler method based on
     * the reference_type field.
     *
     * @param array $ref An associative array with at least 'reference_type' key
     * @return bool True if update succeeded or was ignored, false if reference_type is missing
     */
    public function updateRefValue(array $ref, string $payment_type = ''): bool
    {
        if (empty($ref['reference_type']) || empty($ref['reference_id'])) {
            return false;
        }

        return $this->recalculateReference($ref['reference_type'], $ref['reference_id'], $ref['is_closed'] ?? false);
    }

    /**
     * Recalculate and update the referenced record based on remaining active payments.
     *
     * @param string $reference_type
     * @param int|string $reference_id
     * @param bool $force_closed
     * @return bool
     */
    public function recalculateReference($reference_type, $reference_id, bool $force_closed = false): bool
    {
        if (empty($reference_type) || empty($reference_id)) {
            return false;
        }

        $paidAmount = $this->getRefPaidAmount($reference_type, $reference_id);

        switch ($reference_type) {
            case 'Invoice':
                $invoice = Invoice::find($reference_id);
                if ($invoice) {
                    $invAmount = (float) $invoice->amount;
                    $isClosed = ($invAmount > 0 && $paidAmount >= ($invAmount - 0.01)) || $force_closed;
                    $invoice->update([
                        'paid_amount' => $paidAmount,
                        'is_closed'   => $isClosed ? 1 : 0,
                    ]);
                }
                break;

            case 'GRN':
                $grn = Grn::find($reference_id);
                if ($grn) {
                    $totalAmount = (float) $grn->total_amount;
                    $isClosed = ($totalAmount > 0 && $paidAmount >= ($totalAmount - 0.01)) || $force_closed;
                    $grn->update([
                        'paid_amount' => $paidAmount,
                        'is_closed'   => $isClosed ? 1 : 0,
                    ]);
                }
                break;

            case 'Purchase':
                $purchase = Purchase::find($reference_id);
                if ($purchase) {
                    $totalAmount = (float) $purchase->total_amount;
                    $isClosed = ($totalAmount > 0 && $paidAmount >= ($totalAmount - 0.01)) || $force_closed;
                    $purchase->update([
                        'is_closed' => $isClosed ? 1 : 0,
                    ]);
                }
                break;

            case 'SalarySheetDetail':
                $ssd = SalarySheetDetail::find($reference_id);
                if ($ssd) {
                    $total = (float) $ssd->total;
                    $isPaid = ($total > 0 && $paidAmount >= ($total - 0.01)) || $force_closed;
                    $ssd->update([
                        'is_paid' => $isPaid ? 1 : 0,
                    ]);
                    if ($ssd->installment > 0 && $ssd->installment_history) {
                        $installmenthistories = json_decode($ssd->installment_history, 1);
                        if (is_array($installmenthistories)) {
                            foreach ($installmenthistories as $hdata) {
                                if (!empty($hdata['id'])) {
                                    $loanInstallment = LoanInstallment::find($hdata['id']);
                                    if ($loanInstallment) {
                                        $loanInstallment->update([
                                            'is_closed' => $isPaid ? 1 : 0,
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
                break;

            case 'Commission':
                $commission = Commission::find($reference_id);
                if ($commission) {
                    $commAmount = (float) $commission->amount;
                    $isClosed = ($commAmount > 0 && $paidAmount >= ($commAmount - 0.01)) || $force_closed;
                    $commission->update([
                        'is_closed' => $isClosed ? 1 : 0,
                    ]);
                }
                break;

            case 'ExpenseDetail':
                $expense = ExpenseDetail::find($reference_id);
                if ($expense) {
                    $expAmount = (float) $expense->amount;
                    $isClosed = ($expAmount > 0 && $paidAmount >= ($expAmount - 0.01)) || $force_closed;
                    $expense->update([
                        'is_closed' => $isClosed ? 1 : 0,
                    ]);
                }
                break;

            case 'LoanInstallment':
                $loanInst = LoanInstallment::find($reference_id);
                if ($loanInst) {
                    $instAmount = (float) $loanInst->amount;
                    $isClosed = ($instAmount > 0 && $paidAmount >= ($instAmount - 0.01)) || $force_closed;
                    $loanInst->update([
                        'is_closed' => $isClosed ? 1 : 0,
                    ]);
                }
                break;

            case 'LoanInfo':
                $loan = LoanInfo::find($reference_id);
                if ($loan) {
                    $disbursed = (float) PaymentDetail::where('reference_type', 'LoanInfo')
                        ->where('reference_id', $reference_id)
                        ->whereNull('deleted_at')
                        ->whereHas('payment', function ($q) {
                            $q->where('payment_type', 'Pay');
                        })->sum('amount');

                    $received = (float) PaymentDetail::where('reference_type', 'LoanInfo')
                        ->where('reference_id', $reference_id)
                        ->whereNull('deleted_at')
                        ->whereHas('payment', function ($q) {
                            $q->where('payment_type', 'Receive');
                        })->sum('amount');

                    $loanAmount = (float) $loan->amount;
                    $due = max(0, $loanAmount - $received);

                    $loan->update([
                        'is_paid'    => ($disbursed > 0) ? 1 : 0,
                        'due_amount' => $due,
                        'is_closed'  => ($due <= 0.01 && $received > 0) ? 1 : 0,
                    ]);
                }
                break;

            default:
                break;
        }

        return true;
    }

    public function updateGrnInfo(array $ref): bool
    {
        return $this->recalculateReference('GRN', $ref['reference_id'], !empty($ref['is_closed']));
    }

    public function updatePurchaseInfo(array $ref): bool
    {
        return $this->recalculateReference('Purchase', $ref['reference_id'], !empty($ref['is_closed']));
    }

    public function updateExpenseDetail(array $ref): bool
    {
        return $this->recalculateReference('ExpenseDetail', $ref['reference_id'], !empty($ref['is_closed']));
    }

    public function updateInvoiceInfo(array $inv): bool
    {
        return $this->recalculateReference('Invoice', $inv['reference_id'], !empty($inv['is_closed']));
    }

    public function updateSalarySheet($ref)
    {
        return $this->recalculateReference('SalarySheetDetail', $ref['reference_id'], !empty($ref['is_closed']));
    }

    public function updateLoanInstallment(array $refdata): bool
    {
        return $this->recalculateReference('LoanInstallment', $refdata['reference_id'], !empty($refdata['is_closed']));
    }

    public function updateCommission(array $refdata): bool
    {
        return $this->recalculateReference('Commission', $refdata['reference_id'], !empty($refdata['is_closed']));
    }

    public function updateLoanInfo(array $refdata, string $payment_type): bool
    {
        return $this->recalculateReference('LoanInfo', $refdata['reference_id'], !empty($refdata['is_closed']));
    }
}
