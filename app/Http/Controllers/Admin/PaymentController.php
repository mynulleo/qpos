<?php

/**
 * @Quill Information Technology
 */

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Payment;
use App\Models\Voucher;
use App\Models\PaymentDetail;
use App\Models\VoucherDetail;
use App\Traits\PaymentTrait;
use App\Traits\VoucherTrait;
use Illuminate\Http\Request;
use App\Http\Resources\Resource;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Base\BaseController;
use App\Models\FundAccountBalance;

class PaymentController extends BaseController
{
    use PaymentTrait, VoucherTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Payment::with([
            'client:id,name,mobile,clientid',
            'supplier:id,org_name,mobile,supid',
            'employee:id,full_name,mobile',
            'agent:id,full_name,mobile',
            'fund_account:id,account_name,account_code',
            'bank:id,bank_name'
        ])->latest();

        // 1. Text / Keyword Search (Global or Specific Field)
        if ($request->filled('value')) {
            $val = trim($request->value);
            if ($request->filled('field_name')) {
                $field = $request->field_name;
                if (in_array($field, ['payslipno', 'trxid', 'chequeno', 'branch', 'account_name', 'accountno', 'status', 'payment_type', 'payment_method', 'mbanking_type'])) {
                    $query->where($field, 'like', "%{$val}%");
                } elseif ($field === 'client') {
                    $query->whereHas('client', function ($q) use ($val) {
                        $q->where('name', 'like', "%{$val}%")->orWhere('clientid', 'like', "%{$val}%")->orWhere('mobile', 'like', "%{$val}%");
                    });
                } elseif ($field === 'supplier') {
                    $query->whereHas('supplier', function ($q) use ($val) {
                        $q->where('org_name', 'like', "%{$val}%")->orWhere('supid', 'like', "%{$val}%")->orWhere('mobile', 'like', "%{$val}%");
                    });
                } elseif ($field === 'employee') {
                    $query->whereHas('employee', function ($q) use ($val) {
                        $q->where('full_name', 'like', "%{$val}%")->orWhere('mobile', 'like', "%{$val}%");
                    });
                } elseif ($field === 'agent') {
                    $query->whereHas('agent', function ($q) use ($val) {
                        $q->where('full_name', 'like', "%{$val}%")->orWhere('mobile', 'like', "%{$val}%");
                    });
                }
            } else {
                // Global Multi-field Search
                $query->where(function ($q) use ($val) {
                    $q->where('payslipno', 'like', "%{$val}%")
                        ->orWhere('trxid', 'like', "%{$val}%")
                        ->orWhere('chequeno', 'like', "%{$val}%")
                        ->orWhere('branch', 'like', "%{$val}%")
                        ->orWhere('account_name', 'like', "%{$val}%")
                        ->orWhere('accountno', 'like', "%{$val}%")
                        ->orWhere('amount', 'like', "%{$val}%")
                        ->orWhereHas('client', function ($cq) use ($val) {
                            $cq->where('name', 'like', "%{$val}%")
                                ->orWhere('clientid', 'like', "%{$val}%")
                                ->orWhere('mobile', 'like', "%{$val}%");
                        })
                        ->orWhereHas('supplier', function ($sq) use ($val) {
                            $sq->where('org_name', 'like', "%{$val}%")
                                ->orWhere('supid', 'like', "%{$val}%")
                                ->orWhere('mobile', 'like', "%{$val}%");
                        })
                        ->orWhereHas('employee', function ($eq) use ($val) {
                            $eq->where('full_name', 'like', "%{$val}%")
                                ->orWhere('mobile', 'like', "%{$val}%");
                        })
                        ->orWhereHas('agent', function ($aq) use ($val) {
                            $aq->where('full_name', 'like', "%{$val}%")
                                ->orWhere('mobile', 'like', "%{$val}%");
                        });
                });
            }
        }

        // 2. Transaction Type Filter (Receive / Pay)
        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        // 3. Entity Filters
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        // 4. Payment Method & Channels
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->filled('mbanking_type')) {
            $query->where('mbanking_type', $request->mbanking_type);
        }
        if ($request->filled('bank_id')) {
            $query->where('bank_id', $request->bank_id);
        }

        // 5. Fund Account
        if ($request->filled('fund_account_id')) {
            $query->where('fund_account_id', $request->fund_account_id);
        }

        // 6. Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 7. Office expense yes/no switch
        if ($request->filled('office_expense')) {
            $query->where('office_expense', $request->office_expense);
        }

        // 8. Amount Range Filters
        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', (float) $request->min_amount);
        }
        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', (float) $request->max_amount);
        }

        // 9. Date Range Filter with vue_to_server_date conversion
        if ($request->filled('from_payment_date') && !$request->filled('to_payment_date')) {
            $from = vue_to_server_date($request->from_payment_date);
            $query->whereDate('payment_date', '>=', $from);
        } elseif (!$request->filled('from_payment_date') && $request->filled('to_payment_date')) {
            $to = vue_to_server_date($request->to_payment_date);
            $query->whereDate('payment_date', '<=', $to);
        } elseif ($request->filled('from_payment_date') && $request->filled('to_payment_date')) {
            $from = vue_to_server_date($request->from_payment_date);
            $to = vue_to_server_date($request->to_payment_date);
            $query->whereBetween('payment_date', [$from, $to]);
        }

        if ($request->allData) {
            return $query->get();
        } else {
            $datas = $query->paginate($request->pagination ?? 20);
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
    public function store(Request $request)
    {
        if ($this->validateCheck($request)) {
            try {
                $data = $request->all();
                $data['payslipno'] = Payment::getPaySlipNo();
                $data['payment_date'] = vue_to_server_date($data['payment_date']);
                $data['module'] = $data['ref_module'] ?? null;
                $data['module_id'] = $data['ref_module_id'] ?? null;
                $data['bank_id'] = $data['bank_id'] ?? null;

                $payment_details = $data['payment_details'];
                unset($data['payment_details']);
                // push the insert text
                $res = Payment::create($data);
                if ($res && !empty($payment_details)) {
                    foreach ($payment_details as $detail) {
                        $res->payment_details()->create([
                            'payment_id' => $res->id,
                            'reference_type' => $detail['reference_type'],
                            'reference_id' => $detail['reference_id'],
                            'account_id' => $detail['account_id'],
                            'amount' => $detail['amount'],
                            'is_closed' => $detail['is_closed'],
                        ]);
                        $this->updateRefValue($detail, $data['payment_type']);
                    }
                }

                if (!empty($request->employee_id)) {
                    $this->createEmployeeVoucher($request, $res->id);
                } else {
                    $this->createVoucherFromPayment($request, $res->id);
                }

                return $this->responseReturn("create", $res);
            } catch (Exception $ex) {
                return response()->json(['exception' => $ex->errorInfo ?? $ex->getMessage()], 422);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        if ($request->format() == 'html') {
            return view('layouts.backend_app');
        }

        $payment = Payment::with([
            'payment_details',
            'payment_details.account',
            'client:id,name,mobile,address',
            'supplier:id,org_name,mobile,address',
            'employee:id,full_name,mobile,address',
            'agent:id,full_name,mobile,address'
        ])->find($id);

        // calculate totals
        $total_amount      = $payment->payment_details->sum('amount');
        $total_paid        = $payment->payment_details->sum('paid_amount');
        $total_due         = $payment->payment_details->sum('due_amount');

        return [
            'id'                    => $payment->id,
            'payment_date'          => $payment->payment_date,
            'payment_type'          => $payment->payment_type,
            'client_id'             => $payment->client_id,
            'supplier_id'           => $payment->supplier_id,
            'employee_id'           => $payment->employee_id,
            'agent_id'              => $payment->agent_id,
            'payment_method'        => $payment->payment_method,
            'status'                => $payment->status,
            'discount'              => $payment->discount ?? 0,
            'amount'                => $payment->amount,
            'office_expense'        => $payment->office_expense,
            'mbanking_type'         => $payment->mbanking_type,
            'chequeno'              => $payment->chequeno,
            'bank_id'               => $payment->bank_id,
            'branch'                => $payment->branch,
            'account_name'          => $payment->account_name,
            'accountno'             => $payment->accountno,
            'trxid'                 => $payment->trxid,

            // payto section
            'payto' => [
                'name'    => optional($payment->supplier)->org_name ?? optional($payment->client)->name ?? optional($payment->employee)->full_name ?? optional($payment->agent)->full_name,
                'mobile'  => optional($payment->supplier)->mobile ?? optional($payment->client)->mobile ?? optional($payment->employee)->mobile ?? optional($payment->agent)->mobile,
                'address' => optional($payment->supplier)->address ?? optional($payment->client)->address ?? optional($payment->employee)->address ?? optional($payment->agent)->address,
                'info'    => optional($payment->supplier)->info ?? optional($payment->client)->info
            ],

            // payment details list formatted like your Vue table
            'payment_details' => $payment->payment_details->map(function ($d) {
                return [
                    'reference_type' => $d->reference_type,
                    'reference_id'   => $d->reference_id,
                    'account_id'     => $d->account_id,
                    'reference_info' => $d->reference_info,
                    'amount'         => (float) $d->amount,
                    'paid_amount'    => (float) $d->paid_amount,
                    'due_amount'     => (float) $d->due_amount,
                    'adjust_amount'  => (float) ($d->amount ?? 0),
                    'is_closed'      => (bool) $d->is_closed,
                    'checked'        => true,
                    'account'        => $d->account
                ];
            }),

            'invoices' => $payment->payment_details->map(function ($d) {
                return [
                    'checked'        => true,
                    'reference_type' => $d->reference_type,
                    'reference_id'   => $d->reference_id,
                    'account_id'     => $d->account_id,
                    'reference_info' => $d->reference_info,
                    'amount'         => (float) $d->amount,
                    'paid_amount'    => (float) $d->paid_amount,
                    'due_amount'     => (float) $d->due_amount,
                    'adjust_amount'  => (float) ($d->amount ?? 0),
                    'is_closed'      => (bool) $d->is_closed,
                    'checked'        => true,
                ];
            }),

            // totals block like your Vue reactive data
            'totals' => [
                'total_receive' => $total_amount,
                'total_due'     => $total_due,
            ],
        ];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('layouts.backend_app');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $payment  = Payment::find($id);
        if ($this->validateCheck($request, $payment->id)) {

            DB::beginTransaction();

            try {

                $data = $request->all();
                // convert date
                $data['payment_date'] = vue_to_server_date($data['payment_date']);
                $data['module'] = $data['ref_module'] ?? null;
                $data['module_id'] = $data['ref_module_id'] ?? null;
                $data['bank_id'] = $data['bank_id'] ?? null;

                // store details separately
                $payment_details = $data['payment_details'] ?? [];

                // remove unwanted fields
                unset(
                    $data['payment_details'],
                    $data['payto'],
                    $data['invoices'],
                    $data['totals'],
                    $data['id'],             // IMPORTANT
                    $data['payment_id']      // IMPORTANT
                );

                // reload model from DB
                $payment = Payment::find($payment->id);
                // update payment master
                $payment->update($data);

                // delete old details
                $payment->payment_details()->delete();

                // insert new details
                foreach ($payment_details as $detail) {

                    $payment->payment_details()->create([
                        'payment_id'     => $payment->id,
                        'reference_type' => $detail['reference_type'],
                        'reference_id'   => $detail['reference_id'],
                        'account_id'     => $detail['account_id'],
                        'amount'         => $detail['amount'],
                        'is_closed'      => $detail['is_closed'],
                    ]);

                    $this->updateRefValue($detail);
                }

                // remove previous vouchers
                $this->removeVoucherByPaymentID($payment->id);

                // recreate voucher
                if (!empty($payment->employee_id)) {
                    $this->createEmployeeVoucher($payment, $payment->id);
                } else {
                    $this->createVoucherFromPayment($payment, $payment->id);
                }

                DB::commit();

                return $this->responseReturn("update", $payment);
            } catch (\Exception $ex) {

                DB::rollBack();

                return response()->json([
                    'exception' => $ex->getMessage()
                ], 422);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $payment = Payment::find($id);
        DB::beginTransaction();

        try {

            // 🔁 all related vouchers (just in case multiple exists)
            $vouchers = Voucher::where('payment_id', $payment->id)
                ->orWhere(function ($q) use ($payment) {
                    $q->where('source', 'Payment')
                        ->where('source_id', $payment->id);
                })
                ->get();


            foreach ($vouchers as $voucher) {
                VoucherDetail::where('voucher_id', $voucher->id)->delete(); // soft delete
                $voucher->delete(); // soft delete
            }

            // Payment details (soft delete)
            $payment->payment_details()->delete();

            // Payment (soft delete)
            $payment->delete();

            DB::commit();
            return $this->responseReturn("delete", true);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['exception' => $e->getMessage()], 422);
        }
    }

    public function getFundData($account_id)
    {
        $balance = 0;

        if ($account_id) {
            $fund =  FundAccountBalance::where('account_id', $account_id)->first();
            if ($fund) {
                $balance = $fund->current_balance;
            }
        }
        return $balance;
    }

    public function agentPayableDatas($agentId)
    {
        return $this->getAgentPayableDatas($agentId);
    }

    /**
     * Validate form field.
     *
     * @return \Illuminate\Http\Response
     */
    public function validateCheck($request, $id = null)
    {
        return true;
        return $request->validate([
            //ex: 'name' => 'required|email|nullable|date|string|min:0|max:191',
        ], [
            //ex: 'name' => "This name is required" (custom message)
        ]);
    }
}
