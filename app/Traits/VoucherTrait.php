<?php

namespace App\Traits;

use Log;
use App\Models\Client;
use App\Models\Account;
use App\Models\Voucher;
use App\Models\Employee;
use App\Models\Supplier;
use App\Models\FinancialYear;
use App\Models\VoucherDetail;
use App\Models\SalarySheetDetail;
use Illuminate\Support\Facades\DB;

trait VoucherTrait
{


    public function removeVoucherByPaymentID($payment_id)
    {
        DB::transaction(function () use ($payment_id) {

            // 1️⃣ Get vouchers created from this payment
            $vouchers = Voucher::where('payment_id', $payment_id)->get();

            foreach ($vouchers as $voucher) {

                // 2️⃣ Delete voucher details first
                VoucherDetail::where('voucher_id', $voucher->id)->delete();

                // 3️⃣ Then delete main voucher
                return $voucher->delete();
            }
        });
    }

    // Source info
    // $source = [
    //     'source' => 'Purchase',
    //     'source_id' => 12
    // ];
    public function removeVoucherBySourceInfo($source)
    {
        DB::transaction(function () use ($source) {

            $vouchers = Voucher::where('source', $source['source'])
                ->where('source_id', $source['source_id'])
                ->get();
            // dd($vouchers);

            if ($vouchers) {
                foreach ($vouchers as $voucher) {
                    // delete voucher details via relation
                    $voucher->voucher_details()->delete();
                    // then delete voucher
                    $voucher->delete();
                }
            }
        });
    }

    public function createVoucherFromPayment($payment, $payment_id)
    {
        DB::transaction(function () use ($payment, $payment_id) {
            $ref_type = null;
            $ref_id = null;

            if (!empty($payment->client_id)) {
                $ref_type = 'Client';
                $ref_id = $payment->client_id;
            } elseif (!empty($payment->supplier_id)) {
                $ref_type = 'Supplier';
                $ref_id = $payment->supplier_id;
            } elseif (!empty($payment->employee_id)) {
                $ref_type = 'Employee';
                $ref_id = $payment->employee_id;
            } elseif (!empty($payment->agent_id)) {
                $ref_type = 'Agent';
                $ref_id = $payment->agent_id;
            }

            $voucher = Voucher::create([
                'voucherno'            => Voucher::generateVoucherNo(),
                'voucher_date'         => vue_to_server_date($payment->payment_date),
                'voucher_type'         => $payment->payment_type,
                'narration'            => 'Payment processed via Payment Module',
                'financial_year_id'    => $this->getFinancialYearId(),
                'payment_id'           => $payment_id,
                'source'               => $ref_type ?: $payment->reference_type,
                'source_id'            => $ref_id ?: $payment->reference_id,
                'status'               => 'active'
            ]);

            $cash_bank_account = !empty($payment->fund_account_id)
                ? $payment->fund_account_id
                : $this->getSystemAccount($payment->payment_method);

            if ($payment->payment_type == 'Receive') {
                $discount_account = $this->getExpenseAccount('Discount');

                // 1️⃣ Debit Cash/Bank
                VoucherDetail::create([
                    'voucher_id'      => $voucher->id,
                    'account_id'      => $cash_bank_account,
                    'dr_amount'       => $payment->amount,
                    'cr_amount'       => 0,
                    'reference_type'  => $ref_type ?: $payment->reference_type,
                    'reference_id'    => $ref_id ?: $payment->reference_id,
                ]);

                if ($payment->discount > 0) {
                    VoucherDetail::create([
                        'voucher_id'      => $voucher->id,
                        'account_id'      => $discount_account,
                        'dr_amount'       => $payment->discount,
                        'cr_amount'       => 0,
                        'reference_type'  => 'Client',
                        'reference_id'    => $payment->client_id,
                    ]);
                }
            }

            if ($payment->payment_type == 'Pay') {
                // 1️⃣ Credit Cash/Bank
                VoucherDetail::create([
                    'voucher_id'      => $voucher->id,
                    'account_id'      => $cash_bank_account,
                    'dr_amount'       => 0,
                    'cr_amount'       => $payment->amount,
                    'reference_type'  => $ref_type ?: $payment->reference_type,
                    'reference_id'    => $ref_id ?: $payment->reference_id,
                ]);
            }

            foreach ($payment->payment_details as $detail) {
                if ($payment->payment_type == 'Receive') {
                    $received_amount = $detail['amount'];

                    // Credit Client Receivable
                    VoucherDetail::create([
                        'voucher_id'      => $voucher->id,
                        'account_id'      => $detail['account_id'],
                        'dr_amount'       => 0,
                        'cr_amount'       => $received_amount,
                        'reference_type'  => 'Client',
                        'reference_id'    => $payment->client_id,
                    ]);
                }
                elseif ($payment->payment_type == 'Pay') {
                    $module = null;
                    $module_id = null;
                    $payable_account_id = $this->getAccountID('accounts-payable');

                    if ($detail['reference_type'] == 'Purchase') {
                        $payable_account_id = $this->getAccountID('accounts-payable');
                        $module = 'Supplier';
                        $module_id = $payment->supplier_id;
                    }

                    if ($detail['reference_type'] == 'SalarySheet' || $detail['reference_type'] == 'SalarySheetDetail') {
                        $payable_account_id = $this->getAccountID('salary-payable');
                        $module = 'Employee';
                        $module_id = $payment->employee_id;
                    }

                    if ($detail['reference_type'] == 'ExpenseDetail') {
                        $payable_account_id = $this->getAccountID('expense-payable');
                        $module = 'Office Expense';
                        $module_id = $detail['reference_id'];
                    }

                    if ($detail['reference_type'] == 'Commission') {
                        $payable_account_id = $this->getAccountID('commission-payable');
                        if (!empty($payment->agent_id)) {
                            $module = 'Agent';
                            $module_id = $payment->agent_id;
                        } elseif (!empty($payment->employee_id)) {
                            $module = 'Employee';
                            $module_id = $payment->employee_id;
                        } else {
                            $module = 'Others';
                            $module_id = null;
                        }
                    }

                    // Debit side (Payable A/C)
                    VoucherDetail::create([
                        'voucher_id'     => $voucher->id,
                        'account_id'     => $payable_account_id,
                        'dr_amount'      => $detail['amount'],
                        'cr_amount'      => 0,
                        'reference_type' => $module,
                        'reference_id'   => $module_id,
                    ]);
                }
            }
        });
    }

    public function createEmployeeVoucher($payment, $payment_id)
    {

        DB::transaction(
            function () use ($payment, $payment_id) {
                $voucher = Voucher::create([
                    'voucherno'            => Voucher::generateVoucherNo(),
                    'voucher_date'         => vue_to_server_date($payment->payment_date),
                    'voucher_type'         => $payment->payment_type,
                    'narration'            => 'Payment processed via Payment Module',
                    'financial_year_id'    => $this->getFinancialYearId(),
                    'payment_id'           => $payment_id,
                    'source'               => 'Payment',
                    'source_id'            => $payment_id,
                    'status'               => 'active'
                ]);

                //voucher detail total cash credit
                $cash_bank_account = $this->getSystemAccount($payment->payment_method);
                // 1️⃣ Credit Cash/Bank
                VoucherDetail::create([
                    'voucher_id'      => $voucher->id,
                    'account_id'      => $cash_bank_account,
                    'dr_amount'       => 0,
                    'cr_amount'       => $payment->amount,
                    'reference_type'  => null,
                    'reference_id'    => null,
                ]);

                foreach ($payment->payment_details as $detail) {
                    // Salary Sheet Detail Case
                    if ($detail['reference_type'] == 'SalarySheetDetail') {

                        $ssd = SalarySheetDetail::find($detail['reference_id']);

                        if (!$ssd) {
                            continue; // skip if not found
                        }
                        // ===============================
                        // 1. map components → account ids
                        // ===============================
                        $payableAccountId     = Self::getAccountID('salary-payable');
                        // ===============================
                        // 2. create voucher rows component wise
                        // ===============================

                        // Basic Salary
                        if ($ssd->salary > 0) {
                            $netsalary = $ssd->salary - ($ssd->installment + $ssd->deduct);
                            VoucherDetail::create([
                                'voucher_id'     => $voucher->id,
                                'account_id'     => $payableAccountId,
                                'dr_amount'      => $netsalary,
                                'cr_amount'      => 0,
                                'reference_type' => 'Employee',
                                'reference_id'   => $payment->employee_id,
                            ]);
                        }

                        // Bonus
                        if ($ssd->bonus > 0) {
                            $bonuspayableAccountId     = Self::getAccountID('bonus-payable');
                            VoucherDetail::create([
                                'voucher_id'     => $voucher->id,
                                'account_id'     => $bonuspayableAccountId,
                                'dr_amount'      => $ssd->bonus,
                                'cr_amount'      => 0,
                                'reference_type' => 'Employee',
                                'reference_id'   => $payment->employee_id,
                            ]);
                        }

                        // Installment recovered from salary (CR) Loan receive
                        if ($ssd->installment > 0) {
                            $employee_loan_account_id = $this->getAccountID('employee-loan-receivable');
                            VoucherDetail::create([
                                'voucher_id'     => $voucher->id,
                                'account_id'     => $employee_loan_account_id,
                                'dr_amount'      => 0,
                                'cr_amount'      => $ssd->installment,
                                'reference_type' => 'Employee',
                                'reference_id'   => $payment->employee_id,
                            ]);
                        }

                        // Other deduction (CR)
                        if ($ssd->deduct > 0) {
                            $employee_loan_account_id = $this->getAccountID('employee-salary-deduction');
                            VoucherDetail::create([
                                'voucher_id'     => $voucher->id,
                                'account_id'     => $payableAccountId,
                                'dr_amount'      => 0,
                                'cr_amount'      => $ssd->deduct,
                                'reference_type' => 'Employee',
                                'reference_id'   => $payment->employee_id,
                            ]);
                        }

                        // finally mark paid
                        $ssd->is_paid = 1;
                        $ssd->save();
                    } elseif ($detail['reference_type'] == 'LoanInfo') {
                        $employee_loan_account_id = $this->getAccountID('employee-loan-receivable');
                        if ($payment->payment_type == 'Receive') {
                            VoucherDetail::create([
                                'voucher_id'     => $voucher->id,
                                'account_id'     => $employee_loan_account_id, // Sales Revenue
                                'reference_type' => 'Employee', //
                                'reference_id'   => $payment->employee_id,
                                'dr_amount'      => 0,
                                'cr_amount'      => $detail['amount'],
                            ]);
                        } else {
                            VoucherDetail::create([
                                'voucher_id'     => $voucher->id,
                                'account_id'     => $employee_loan_account_id, // Sales Revenue
                                'reference_type' => 'Employee', //
                                'reference_id'   => $payment->employee_id,
                                'dr_amount'      => $detail['amount'],
                                'cr_amount'      => 0,
                            ]);
                        }
                    }

                    // Commission
                    elseif ($detail['reference_type'] == 'Commission') {
                        $payableAccountId = $this->getAccountID('commission-payable');
                        VoucherDetail::create([
                            'voucher_id'     => $voucher->id,
                            'account_id'     => $payableAccountId,
                            'dr_amount'      => $detail['amount'],
                            'cr_amount'      => 0,
                            'reference_type' => 'Employee',
                            'reference_id'   => $payment->employee_id,
                        ]);
                    }
                    //Expense
                    elseif ($detail['reference_type'] == 'ExpenseDetail') {
                        $payableAccountId = $this->getAccountID('expense-payable');
                        VoucherDetail::create([
                            'voucher_id'     => $voucher->id,
                            'account_id'     => $payableAccountId,
                            'dr_amount'      => $detail['amount'],
                            'cr_amount'      => 0,
                            'reference_type' => 'Employee',
                            'reference_id'   => $payment->employee_id,
                        ]);
                    }

                    // ======================================
                    // Other reference types keep same logic
                    // ======================================
                    else {
                        VoucherDetail::create([
                            'voucher_id'     => $voucher->id,
                            'account_id'     => $detail['account_id'],
                            'dr_amount'      => $detail['amount'],
                            'cr_amount'      => 0,
                            'reference_type' => $detail['reference_type'],
                            'reference_id'   => $detail['reference_id'],
                        ]);
                    }
                }
            }
        );
    }

    private function getFinancialYearId()
    {
        $fyearid = null;
        $fyear = FinancialYear::where('is_current', 1)->first();
        if ($fyear) {
            $fyearid = $fyear->id;
        }
        return $fyearid;
    }

    // Client specific account
    private function getClientAccount($client_id)
    {
        $accountid = null;
        $paccount = Account::where('reference_type', 'Client')
            ->where('reference_id', $client_id)->first();
        if ($paccount) {
            $accountid = $paccount->id;
        } else {
            $client = Client::where('id', $client_id)->first();
            if ($client) {
                $pdata = [
                    'name'           => $client->name,
                    'entityid'       => $client->clientid,
                    'reference_type' => 'Client',
                    'reference_id'   => $client->id,
                    'account_type'   => 'Asset',
                    'parent_id'      => 26
                ];
                $paccount = Account::createPersonAccount($pdata);
                $accountid = $paccount->id;
            }
        }
        return $accountid;
    }

    // Supplier account
    private function getSupplierAccount($supplier_id)
    {
        $accountid = null;
        $paccount = Account::where('reference_type', 'Supplier')
            ->where('reference_id', $supplier_id)->first();
        if ($paccount) {
            $accountid = $paccount->id;
        } else {
            $supplier = Supplier::where('id', $supplier_id)->first();
            if ($supplier) {
                $pdata = [
                    'name'           => $supplier->org_name,
                    'entityid'       => $supplier->supid,
                    'reference_type' => 'Supplier',
                    'reference_id'   => $supplier->id,
                    'account_type'   => 'Liability',
                    'parent_id'      => 31
                ];
                $paccount = Account::createPersonAccount($pdata);
                $accountid = $paccount->id;
            }
        }
        return $accountid;
    }

    // Employee account
    private function getEmployeeAccount($employee_id)
    {
        $accountid = null;
        $paccount = Account::where('reference_type', 'Employee')
            ->where('reference_id', $employee_id)->first();
        if ($paccount) {
            $accountid = $paccount->id;
        } else {
            $employee = Employee::where('id', $employee_id)->first();
            if ($employee) {
                $pdata = [
                    'name'           => $employee->full_name,
                    'entityid'       => $employee->empid,
                    'reference_type' => 'Employee',
                    'reference_id'   => $employee->id,
                    'account_type'   => 'Liability',
                    'parent_id'      => 32
                ];
                $paccount = Account::createPersonAccount($pdata);
                $accountid = $paccount->id;
            }
        }
        return $accountid;
    }



    // create payable voucher
    // $vdata = [
    //     'module'    => 'Purchase',
    //     'date'      => 23 Jan, 2026,
    //     'amount'    => 5000.00,
    //     'source_id' => 23, //purchase id
    //     'ref_id'    => 2 //supplier id
    // ];
    public function createPayableVoucher($vdata)
    {
        DB::transaction(function () use ($vdata) {
            $module = '';
            $amount = '';
            $source_id = '';
            $ref_id = '';
            $ref_type = '';
            $account_id = null;
            $vdate = vue_to_server_date($vdata['date']);

            // liability account
            $payable_account_id = $this->getAccountID('accounts-payable');
            if ($vdata) {
                $module = $vdata['module'];
                $amount = $vdata['amount'];
                $source_id = $vdata['source_id'] ?? null;
                $ref_id = $vdata['ref_id'] ?? null;
            }

            if ($module == 'Purchase') {
                $account_id = $this->getExpenseAccount('Purchase');
                $payable_account_id = $this->getAccountID('accounts-payable');
                $ref_type = 'Supplier';
            }

            if ($module == 'SalarySheetDetail') {
                $account_id = $this->getAccountID('SalaryExpense');
                $payable_account_id = $this->getAccountID('salary-payable');
                $ref_type = 'Employee';
            }

            if ($module == 'ExpenseDetail') {
                $account_id = $vdata['account_id'] ?? $this->getAccountID('office-supplies-expense');
                $payable_account_id = $this->getAccountID('expense-payable');
                $ref_type = 'Office Expense';
                if (!empty($ref_id)) {
                    $ref_type = 'Employee';
                }
            }

            if ($module == 'Commission') {
                $account_id = $this->getAccountID('commission-expense');
                $payable_account_id = $this->getAccountID('commission-payable');
                if (!empty($vdata['ref_name'])) {
                    $ref_type = $vdata['ref_name'];
                } else if (!empty($ref_id)) {
                    $ref_type = 'Employee';
                } else {
                    $ref_type = 'Others';
                }

                $vdate = $vdata['date'];
            }

            // Create Voucher master
            $voucher = Voucher::create([
                'voucherno'         => Voucher::generateVoucherNo(),
                'voucher_date'      => $vdate,
                'financial_year_id' => $this->getFinancialYearId(),
                'source'            => $module,
                'source_id'         => $source_id,
                'voucher_type'      => 'Journal',
                'narration'         => 'Payable voucher auto generated',
            ]);

            // 1. Debit Expense / Purchase
            VoucherDetail::create([
                'voucher_id' => $voucher->id,
                'account_id' => $account_id,
                'reference_type' => $ref_type, //Purchase
                'reference_id' => $ref_id, //purchase_id
                'dr_amount'  => $amount,
                'cr_amount'  => 0,
            ]);

            // 2. Credit Accounts Payable (or individual Supplier ledger)
            VoucherDetail::create([
                'voucher_id' => $voucher->id,
                'account_id' => $payable_account_id, //Account payable
                'reference_type' => $ref_type, // Supplier
                'reference_id' => $ref_id, //Supplier_id
                'dr_amount'  => 0,
                'cr_amount'  => $amount,
            ]);

            return $voucher->id;
        });
    }

    // create receivable voucher
    // $vdata = [
    //     'module'    => 'Invoice',
    //     'date'      => 23 Jan, 2026,
    //     'amount'    => 8000.00,
    //     'source_id' => 55, // sales / invoice id
    //     'ref_id'    => 7   // customer id
    // ];

    public function createReceivableVoucher($vdata)
    {
        DB::transaction(function () use ($vdata) {

            $module     = '';
            $amount     = 0;
            $source_id  = null;
            $ref_id     = null;
            $ref_type   = '';
            $account_id = null;
            $vdate = vue_to_server_date($vdata['date']);

            // control account
            $receivable_account_id = $this->getAccountID('accounts-receivable');

            if ($vdata) {
                $module    = $vdata['module'];
                $amount    = $vdata['amount'];
                $source_id = $vdata['source_id'];
                $ref_id    = $vdata['ref_id'];
            }

            // Decide income account & reference type
            if ($module == 'Invoice') {
                $account_id = $this->getAccountID('sales-revenue');
                $ref_type   = 'Client';
                $vdate = $vdata['date'];
            }

            // Create voucher master
            $voucher = Voucher::create([
                'voucherno'    => Voucher::generateVoucherNo(),
                'voucher_date' => $vdate,
                'financial_year_id' => $this->getFinancialYearId(),
                'source'       => $module,
                'source_id'    => $source_id,
                'voucher_type' => 'Journal',
                'narration'    => 'Receivable voucher auto generated',
            ]);

            // 1. Debit Accounts Receivable (Control A/C)
            VoucherDetail::create([
                'voucher_id'     => $voucher->id,
                'account_id'     => $receivable_account_id, //Account Receivable
                'reference_type' => $ref_type, //Client
                'reference_id'   => $ref_id, //client_id
                'dr_amount'      => $amount,
                'cr_amount'      => 0,
            ]);

            // 2. Credit Income / Sales Account
            VoucherDetail::create([
                'voucher_id'     => $voucher->id,
                'account_id'     => $account_id, // Sales Revenue
                'reference_type' => $ref_type, //
                'reference_id'   => $ref_id,
                'dr_amount'      => 0,
                'cr_amount'      => $amount,
            ]);
        });
    }

    public function createLoanVoucher($data)
    {
        if ($data) {
            $vdate = vue_to_server_date($data->approved_date);
            $employee_loan_account_id = $this->getAccountID('employee-loan-receivable');
            $employee_id = $data->employee_id;
            $loan_amount = $data->amount;
            $voucher = Voucher::create([
                'voucherno'    => Voucher::generateVoucherNo(),
                'voucher_date' => $vdate,
                'financial_year_id' => $this->getFinancialYearId(),
                'source'       => 'LoanInfo',
                'source_id'    => $data->id,
                'voucher_type' => 'Journal',
                'narration'    => 'Employee Loan voucher auto generated',
            ]);

            VoucherDetail::create([
                'voucher_id'     => $voucher->id,
                'account_id'     => $employee_loan_account_id, // Sales Revenue
                'reference_type' => 'Employee', //
                'reference_id'   => $employee_id,
                'dr_amount'      => $loan_amount,
                'cr_amount'      => 0,
            ]);
        }
    }

    public function syncOpeningBalanceVoucherForAccount(Account $account)
    {
        DB::transaction(function () use ($account) {

            // Income & Expense account ignore
            if (in_array($account->account_type, ['Income', 'Expense'])) {
                return;
            }

            if ($account->opening_balance <= 0) {
                return;
            }

            // Opening Balance Equity Account
            $openingAccount = Account::where('system_key_name', 'opening-balance-equity')
                ->where('status', 'active')
                ->first();

            if (!$openingAccount) {
                throw new \Exception('Opening Balance Equity account not found.');
            }
            // Check if voucher already exists for this account's opening balance
            $voucher = Voucher::where('voucher_type', 'Opening')
                ->whereHas('voucher_details', function ($q) use ($account) {
                    $q->where('reference_type', 'OpeningBalance')
                        ->where('reference_id', $account->id);
                })
                ->first();

            // If not, create a new opening balance voucher
            if (!$voucher) {

                $voucher = Voucher::create([
                    'voucherno'         => Voucher::generateVoucherNo(),
                    'voucher_type'      => 'Opening',
                    'voucher_date'      => now()->toDateString(),
                    'financial_year_id' => $this->getFinancialYearId(),
                    'narration'         => 'Opening Balance : ' . $account->account_name,
                    'status'            => 'active',
                ]);
            }

            $amount = abs($account->opening_balance);

            // Determine the debit and credit accounts based on the account type
            if ($account->account_type == 'Asset') {

                $first = [
                    'account_id' => $account->id,
                    'dr_amount'  => $amount,
                    'cr_amount'  => 0,
                ];

                $second = [
                    'account_id' => $openingAccount->id,
                    'dr_amount'  => 0,
                    'cr_amount'  => $amount,
                ];
            } elseif (in_array($account->account_type, ['Liability', 'Equity'])) {

                $first = [
                    'account_id' => $openingAccount->id,
                    'dr_amount'  => $amount,
                    'cr_amount'  => 0,
                ];

                $second = [
                    'account_id' => $account->id,
                    'dr_amount'  => 0,
                    'cr_amount'  => $amount,
                ];
            } else {
                return;
            }
            // Create or update voucher details for the opening balance
            VoucherDetail::updateOrCreate(
                [
                    'voucher_id'     => $voucher->id,
                    'reference_type' => 'OpeningBalance',
                    'reference_id'   => $account->id,
                    'account_id'     => $first['account_id'],
                ],
                [
                    'dr_amount'      => $first['dr_amount'],
                    'cr_amount'      => $first['cr_amount'],
                    'line_narration' => 'Opening Balance',
                    'status'         => 'active',
                ]
            );
            // Create or update voucher details for the opening balance
            VoucherDetail::updateOrCreate(
                [
                    'voucher_id'     => $voucher->id,
                    'reference_type' => 'OpeningBalance',
                    'reference_id'   => $account->id,
                    'account_id'     => $second['account_id'],
                ],
                [
                    'dr_amount'      => $second['dr_amount'],
                    'cr_amount'      => $second['cr_amount'],
                    'line_narration' => 'Opening Balance Offset',
                    'status'         => 'active',
                ]
            );


            // Validate that the voucher is balanced
            $totalDr = VoucherDetail::where('voucher_id', $voucher->id)->sum('dr_amount');
            $totalCr = VoucherDetail::where('voucher_id', $voucher->id)->sum('cr_amount');

            if (round($totalDr, 2) != round($totalCr, 2)) {
                throw new \Exception(
                    "Opening voucher not balanced. DR={$totalDr} CR={$totalCr}"
                );
            }
        });
    }

    public function voucherForFundTransfer($data)
    {
        DB::beginTransaction();

        try {
            $from_account_id = null;
            $to_account_id = null;
            if ($data) {
                $from_account_id = $data['from_account_id'];
                $to_account_id = $data['to_account_id'];
                $amount = $data['amount'];

                $voucher_date = vue_to_server_date($data['transfer_date']);

                // ✅ Create Voucher
                $voucher = Voucher::create([
                    'voucherno' => Voucher::generateVoucherNo(),
                    'voucher_type' => 'Journal',
                    'voucher_date' => $voucher_date,
                    'narration' => 'Fund Transfer',
                    'financial_year_id' => $this->getFinancialYearId(),
                    'source' => 'FundTransfer',
                    'source_id' => $data['id'],
                    'status' => 'active'
                ]);

                // ✅ Debit Entry (To Account)
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $to_account_id,
                    'dr_amount' => $amount,
                    'cr_amount' => 0,
                    'line_narration' => 'Fund received',
                    'status' => 'active'
                ]);

                // ✅ Credit Entry (From Account)
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $from_account_id,
                    'dr_amount' => 0,
                    'cr_amount' => $amount,
                    'line_narration' => 'Fund transferred',
                    'status' => 'active'
                ]);

                DB::commit();
            }
        } catch (\Exception $e) {

            DB::rollBack();

            throw $e;
            // or
            // return response()->json(['error' => $e->getMessage()], 500);
        }

        return true;
    }

    public function voucherForWithdraw($data)
    {
        DB::beginTransaction();

        try {
            if ($data) {

                $cash_account_id = $data['account_id']; // Cash / Bank account
                $owner_account_id = $this->getAccountID('owner-withdraw'); // Owner Drawings / Personal account
                $amount = $data['amount'];

                $voucher_date = vue_to_server_date($data['withdrwal_date']);
                // ✅ Create Voucher
                $voucher = Voucher::create([
                    'voucherno' => Voucher::generateVoucherNo(),
                    'voucher_type' => 'Pay', // Cash withdrawal is Payment type
                    'voucher_date' => $voucher_date,
                    'narration' => $data['narration'] ?? 'Cash Withdrawal',
                    'financial_year_id' => $this->getFinancialYearId(),
                    'source' => 'Withdrawl',
                    'source_id' => $data['id'] ?? null,
                    'status' => 'active'
                ]);

                // ✅ Debit Entry (Owner Drawings / Personal Account)
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $owner_account_id,
                    'dr_amount' => $amount,
                    'cr_amount' => 0,
                    'line_narration' => 'Cash withdrawn by owner',
                    'status' => 'active'
                ]);

                // ✅ Credit Entry (Cash / Bank Account)
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $cash_account_id,
                    'dr_amount' => 0,
                    'cr_amount' => $amount,
                    'line_narration' => 'Cash decrease due to withdrawal',
                    'status' => 'active'
                ]);

                DB::commit();
            }
        } catch (\Exception $e) {

            DB::rollBack();
            throw $e;
            // বা return response()->json(['error' => $e->getMessage()], 500);
        }

        return true;
    }

    private function getAccountID($keyword)
    {
        return Account::where('system_key_name', $keyword)->firstOrFail()->id;
    }

    // Cash/Bank system account
    private function getSystemAccount($type)
    {
        if ($type == 'Cheque') {
            return Account::where('account_name', 'Bank')->firstOrFail()->id;
        }

        if ($type == 'BankTransfer') {
            return Account::where('account_name', 'Bank')->firstOrFail()->id;
        }

        if ($type == 'MobileBanking') {
            return Account::where('account_name', 'bKash/Nagad/Rocket')->firstOrFail()->id;
        }

        return Account::where('account_name', $type)->firstOrFail()->id;
    }

    // Expense account mapping
    private function getExpenseAccount($reference_type)
    {
        switch ($reference_type) {
            case 'SalarySheetDetail':
                return Account::where('account_name', 'Salary Expense')->first()->id;
            case 'Bonus':
                return Account::where('account_name', 'Bonus Expense')->first()->id;
            case 'Loan':
                return Account::where('account_name', 'Office Loan')->first()->id;
            case 'Advance':
                return Account::where('account_name', 'Advance Salary')->first()->id;
            case 'Invoice':
                return Account::where('account_name', 'Sales Revenue')->first()->id;
            case 'Purchase':
                return Account::where('account_name', 'Purchase')->first()->id;
            case 'Commission':
                return Account::where('account_name', 'Commission Expense')->first()->id;
            case 'Expense':
                return Account::where('account_name', 'Office Supplies')->first()->id;
            case 'Discount':
                return Account::where('account_name', 'Discount')->first()->id;
            default:
                return Account::where('account_name', 'Other Expense')->first()->id;
        }
    }
}
