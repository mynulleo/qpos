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
use App\Models\SalesReturn;
use App\Models\WarrantyClaim;
use App\Traits\PaymentTrait;
use App\Traits\VoucherTrait;
use Illuminate\Http\Request;
use App\Http\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
        $hasInvoicePaymentId = Schema::hasColumn('invoices', 'payment_id');

        $withRelations = [
            'client:id,name,mobile,clientid',
            'supplier:id,org_name,mobile,supid',
            'employee:id,full_name,mobile',
            'agent:id,full_name,mobile',
            'fund_account:id,account_name,account_code',
            'bank:id,bank_name',
            'payment_details:id,payment_id,reference_type,reference_id',
            'payment_details.invoice:id,invoice_no',
            'payment_details.purchase:id,invoiceno',
            'sales_return:id,payment_id,return_no',
            'warranty_claim:id,payment_id,claim_no'
        ];

        if ($hasInvoicePaymentId) {
            $withRelations[] = 'invoices:id,payment_id,invoice_no';
        }

        $query = Payment::with($withRelations)->latest();

        // 1. Text / Keyword Search (Global or Specific Field)
        if ($request->filled('value')) {
            $val = trim($request->value);
            if ($request->filled('field_name')) {
                $field = $request->field_name;
                if ($field === 'invoice_no' || $field === 'invoice' || $field === 'invoiceno') {
                    $query->where(function ($q) use ($val, $hasInvoicePaymentId) {
                        $q->whereHas('payment_details', function ($dq) use ($val) {
                            $dq->where(function ($sub) use ($val) {
                                $sub->where(function ($iSub) use ($val) {
                                    $iSub->where('reference_type', 'Invoice')
                                         ->whereHas('invoice', function ($iq) use ($val) {
                                             $iq->where('invoice_no', 'like', "%{$val}%");
                                         });
                                })->orWhere(function ($pSub) use ($val) {
                                    $pSub->where('reference_type', 'Purchase')
                                         ->whereHas('purchase', function ($pq) use ($val) {
                                             $pq->where('invoiceno', 'like', "%{$val}%");
                                         });
                                });
                            });
                        });
                        if ($hasInvoicePaymentId) {
                            $q->orWhereHas('invoices', function ($iq) use ($val) {
                                $iq->where('invoice_no', 'like', "%{$val}%");
                            });
                        }
                        $q->orWhere('payslipno', 'like', "%{$val}%");
                    });
                } elseif (in_array($field, ['payslipno', 'trxid', 'chequeno', 'branch', 'account_name', 'accountno', 'status', 'payment_type', 'payment_method', 'mbanking_type'])) {
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
                $query->where(function ($q) use ($val, $hasInvoicePaymentId) {
                    $q->where('payslipno', 'like', "%{$val}%")
                        ->orWhere('trxid', 'like', "%{$val}%")
                        ->orWhere('chequeno', 'like', "%{$val}%")
                        ->orWhere('branch', 'like', "%{$val}%")
                        ->orWhere('account_name', 'like', "%{$val}%")
                        ->orWhere('accountno', 'like', "%{$val}%")
                        ->orWhere('amount', 'like', "%{$val}%")
                        ->orWhereHas('payment_details', function ($dq) use ($val) {
                            $dq->where(function ($sub) use ($val) {
                                $sub->where(function ($iSub) use ($val) {
                                    $iSub->where('reference_type', 'Invoice')
                                         ->whereHas('invoice', function ($iq) use ($val) {
                                             $iq->where('invoice_no', 'like', "%{$val}%");
                                         });
                                })->orWhere(function ($pSub) use ($val) {
                                    $pSub->where('reference_type', 'Purchase')
                                         ->whereHas('purchase', function ($pq) use ($val) {
                                             $pq->where('invoiceno', 'like', "%{$val}%");
                                         });
                                });
                            });
                        });
                    if ($hasInvoicePaymentId) {
                        $q->orWhereHas('invoices', function ($iq) use ($val) {
                            $iq->where('invoice_no', 'like', "%{$val}%");
                        });
                    }
                    $q->orWhereHas('client', function ($cq) use ($val) {
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

        // 1.1 Dedicated Invoice No Filter (from Advanced Search or query parameter)
        if ($request->filled('invoice_no')) {
            $invNo = trim($request->invoice_no);
            $query->where(function ($q) use ($invNo, $hasInvoicePaymentId) {
                $q->whereHas('payment_details', function ($dq) use ($invNo) {
                    $dq->where(function ($sub) use ($invNo) {
                        $sub->where(function ($iSub) use ($invNo) {
                            $iSub->where('reference_type', 'Invoice')
                                 ->whereHas('invoice', function ($iq) use ($invNo) {
                                     $iq->where('invoice_no', 'like', "%{$invNo}%");
                                 });
                        })->orWhere(function ($pSub) use ($invNo) {
                            $pSub->where('reference_type', 'Purchase')
                                 ->whereHas('purchase', function ($pq) use ($invNo) {
                                     $pq->where('invoiceno', 'like', "%{$invNo}%");
                                 });
                        });
                    });
                });
                if ($hasInvoicePaymentId) {
                    $q->orWhereHas('invoices', function ($iq) use ($invNo) {
                        $iq->where('invoice_no', 'like', "%{$invNo}%");
                    });
                }
                $q->orWhere('payslipno', 'like', "%{$invNo}%");
            });
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
                            'payment_id'     => $res->id,
                            'reference_type' => $detail['reference_type'],
                            'reference_id'   => $detail['reference_id'],
                            'account_id'     => $detail['account_id'] ?? 0,
                            'amount'         => $detail['amount'],
                            'is_closed'      => !empty($detail['is_closed']) ? 1 : 0,
                        ]);
                        $this->recalculateReference($detail['reference_type'], $detail['reference_id'], !empty($detail['is_closed']));
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
            'payment_details.invoice:id,invoice_no,invoice_date,amount',
            'payment_details.purchase:id,invoiceno,purchase_date,total_amount',
            'payment_details.grn:id,grn_no,grn_date,total_amount',
            'payment_details.warranty_claim:id,claim_no,serial_no',
            'payment_details.sales_return:id,return_no,total_refund_amount',
            'payment_details.expense:id,expenseid,expense_date,total_amount',
            'payment_details.loan_info:id,trnsid,trns_date,amount',
            'payment_details.salary_sheet:id,title,month,year',
            'client:id,name,mobile,address',
            'supplier:id,org_name,mobile,address',
            'employee:id,full_name,mobile,address',
            'agent:id,full_name,mobile,address',
            'fund_account:id,account_name,account_code',
            'bank:id,bank_name'
        ])->find($id);

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        // calculate totals
        $total_amount      = $payment->payment_details->sum('amount');
        $total_paid        = $payment->payment_details->sum('paid_amount');
        $total_due         = $payment->payment_details->sum('due_amount');

        return [
            'id'                    => $payment->id,
            'payslipno'             => $payment->payslipno,
            'payment_date'          => $payment->payment_date,
            'payment_type'          => $payment->payment_type,
            'client_id'             => $payment->client_id,
            'supplier_id'           => $payment->supplier_id,
            'employee_id'           => $payment->employee_id,
            'agent_id'              => $payment->agent_id,
            'fund_account_id'       => $payment->fund_account_id,
            'fund_account'          => $payment->fund_account,
            'bank'                  => $payment->bank,
            'client'                => $payment->client,
            'supplier'              => $payment->supplier,
            'employee'              => $payment->employee,
            'agent'                 => $payment->agent,
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
            'can_edit'              => $payment->can_edit,
            'can_delete'            => $payment->can_delete,
            'lock_reason'           => $payment->lock_reason,
            'invoice_numbers'       => $payment->invoice_numbers,

            // payto section
            'payto' => [
                'name'    => optional($payment->supplier)->org_name ?? optional($payment->client)->name ?? optional($payment->employee)->full_name ?? optional($payment->agent)->full_name,
                'mobile'  => optional($payment->supplier)->mobile ?? optional($payment->client)->mobile ?? optional($payment->employee)->mobile ?? optional($payment->agent)->mobile,
                'address' => optional($payment->supplier)->address ?? optional($payment->client)->address ?? optional($payment->employee)->address ?? optional($payment->agent)->address,
                'info'    => optional($payment->supplier)->info ?? optional($payment->client)->info
            ],

            // payment details list formatted like your Vue table
            'payment_details' => $payment->payment_details->map(function ($d) {
                $refDocNo = null;
                $refUrl = null;
                $refDate = null;
                $refTotal = null;

                switch ($d->reference_type) {
                    case 'Invoice':
                        $refDocNo = optional($d->invoice)->invoice_no;
                        $refUrl = $d->reference_id ? "/invoice/{$d->reference_id}" : null;
                        $refDate = optional($d->invoice)->invoice_date;
                        $refTotal = (float)optional($d->invoice)->amount;
                        break;
                    case 'Purchase':
                        $refDocNo = optional($d->purchase)->invoiceno;
                        $refUrl = $d->reference_id ? "/purchase/{$d->reference_id}" : null;
                        $refDate = optional($d->purchase)->purchase_date;
                        $refTotal = (float)optional($d->purchase)->total_amount;
                        break;
                    case 'GRN':
                        $refDocNo = optional($d->grn)->grn_no;
                        $refUrl = $d->reference_id ? "/grn/{$d->reference_id}" : null;
                        $refDate = optional($d->grn)->grn_date;
                        $refTotal = (float)optional($d->grn)->total_amount;
                        break;
                    case 'WarrantyClaim':
                        $refDocNo = optional($d->warranty_claim)->claim_no;
                        $refUrl = $d->reference_id ? "/warrantyClaim/{$d->reference_id}" : null;
                        break;
                    case 'SalesReturn':
                        $refDocNo = optional($d->sales_return)->return_no;
                        $refUrl = "/report/salesreturn";
                        $refTotal = (float)optional($d->sales_return)->total_refund_amount;
                        break;
                    case 'Expense':
                    case 'OfficeExpense':
                        $refDocNo = optional($d->expense)->expenseid;
                        $refUrl = $d->reference_id ? "/expense/{$d->reference_id}" : null;
                        $refDate = optional($d->expense)->expense_date;
                        $refTotal = (float)optional($d->expense)->total_amount;
                        break;
                    case 'Loan':
                    case 'LoanInfo':
                        $refDocNo = optional($d->loan_info)->trnsid;
                        $refUrl = $d->reference_id ? "/loanInfo/{$d->reference_id}" : null;
                        $refDate = optional($d->loan_info)->trns_date;
                        $refTotal = (float)optional($d->loan_info)->amount;
                        break;
                    case 'Salary':
                    case 'SalarySheet':
                        $refDocNo = optional($d->salary_sheet)->title ?: (optional($d->salary_sheet)->month ? optional($d->salary_sheet)->month . ' ' . optional($d->salary_sheet)->year : null);
                        $refUrl = $d->reference_id ? "/salarySheet/{$d->reference_id}" : null;
                        break;
                }

                return [
                    'id'             => $d->id,
                    'reference_type' => $d->reference_type,
                    'reference_id'   => $d->reference_id,
                    'account_id'     => $d->account_id,
                    'reference_info' => $d->reference_info,
                    'ref_doc_no'     => $refDocNo,
                    'ref_url'        => $refUrl,
                    'ref_date'       => $refDate,
                    'ref_total'      => $refTotal,
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
     * Get details of a referenced document (Invoice, GRN, Purchase, WarrantyClaim, Expense, Loan, Salary)
     *
     * @param string $type
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReferenceDetail($type, $id)
    {
        $result = [
            'type'         => $type,
            'id'           => $id,
            'doc_no'       => null,
            'date'         => null,
            'party_name'   => null,
            'party_mobile' => null,
            'total_amount' => 0,
            'paid_amount'  => 0,
            'due_amount'   => 0,
            'status'       => null,
            'note'         => null,
            'url'          => null,
            'items'        => []
        ];

        switch ($type) {
            case 'Invoice':
                $inv = \App\Models\Invoice::with(['client', 'invoice_details.item.unit'])->find($id);
                if ($inv) {
                    $result['doc_no']       = $inv->invoice_no;
                    $result['date']         = $inv->invoice_date;
                    $result['party_name']   = optional($inv->client)->name;
                    $result['party_mobile'] = optional($inv->client)->mobile;
                    $result['total_amount'] = (float)$inv->amount;
                    $result['paid_amount']  = (float)$inv->paid_amount;
                    $result['due_amount']   = max(0, (float)$inv->amount - (float)$inv->paid_amount);
                    $result['status']       = $result['due_amount'] <= 0 ? 'Fully Paid' : 'Payment Due';
                    $result['note']         = optional($inv->client)->address ? "Client Address: " . $inv->client->address : null;
                    $result['url']          = "/invoice/{$inv->id}";
                    foreach ($inv->invoice_details as $d) {
                        $result['items'][] = [
                            'title'       => optional($d->item)->title ?: 'Product',
                            'code'        => optional($d->item)->barcode ?: $d->serial_no,
                            'qty'         => (float)$d->qty,
                            'unit'        => optional(optional($d->item)->unit)->title ?: 'Pcs',
                            'unit_price'  => (float)$d->amount,
                            'total_price' => (float)$d->total_amount,
                        ];
                    }
                }
                break;

            case 'GRN':
                $grn = \App\Models\Grn::with(['supplier', 'warehouse', 'purchase', 'grn_details.item.unit'])->find($id);
                if ($grn) {
                    $result['doc_no']       = $grn->grn_no;
                    $result['date']         = $grn->grn_date;
                    $result['party_name']   = optional($grn->supplier)->org_name ?: optional($grn->supplier)->name;
                    $result['party_mobile'] = optional($grn->supplier)->mobile;
                    $result['total_amount'] = (float)$grn->total_amount;
                    $result['paid_amount']  = (float)$grn->paid_amount;
                    $result['due_amount']   = max(0, (float)$grn->total_amount - (float)$grn->paid_amount);
                    $result['status']       = $grn->is_closed ? 'Closed / Settled' : 'Payment Due';
                    $result['note']         = $grn->note ?: ($grn->purchase ? "Linked to PO #" . $grn->purchase->invoiceno : "Direct GRN");
                    $result['url']          = "/grn/{$grn->id}";
                    foreach ($grn->grn_details as $d) {
                        $result['items'][] = [
                            'title'       => optional($d->item)->title ?: 'Item',
                            'code'        => optional($d->item)->barcode,
                            'qty'         => (float)$d->qty,
                            'unit'        => optional(optional($d->item)->unit)->title ?: 'Pcs',
                            'unit_price'  => (float)$d->unit_price,
                            'total_price' => (float)$d->total_amount,
                        ];
                    }
                }
                break;

            case 'Purchase':
                $p = \App\Models\Purchase::with(['supplier', 'purchase_details.item.unit'])->find($id);
                if ($p) {
                    $result['doc_no']       = $p->invoiceno;
                    $result['date']         = $p->purchase_date;
                    $result['party_name']   = optional($p->supplier)->org_name ?: optional($p->supplier)->name;
                    $result['party_mobile'] = optional($p->supplier)->mobile;
                    $result['total_amount'] = (float)$p->total_amount;
                    $result['paid_amount']  = (float)($p->total_amount - $p->due_amount);
                    $result['due_amount']   = (float)$p->due_amount;
                    $result['status']       = $p->is_closed ? 'Settled' : 'Pending Receive';
                    $result['note']         = $p->note;
                    $result['url']          = "/purchase/{$p->id}";
                    foreach ($p->purchase_details as $d) {
                        $result['items'][] = [
                            'title'       => optional($d->item)->title ?: 'Item',
                            'code'        => optional($d->item)->barcode,
                            'qty'         => (float)$d->qty,
                            'unit'        => optional(optional($d->item)->unit)->title ?: 'Pcs',
                            'unit_price'  => (float)$d->price,
                            'total_price' => (float)$d->total_amount,
                        ];
                    }
                }
                break;

            case 'WarrantyClaim':
                $wc = \App\Models\WarrantyClaim::with(['client', 'supplier', 'item'])->find($id);
                if ($wc) {
                    $result['doc_no']       = $wc->claim_no;
                    $result['date']         = $wc->created_at ? $wc->created_at->format('d M, Y') : null;
                    $result['party_name']   = optional($wc->client)->name ?: optional($wc->supplier)->org_name;
                    $result['party_mobile'] = optional($wc->client)->mobile ?: optional($wc->supplier)->mobile;
                    $result['status']       = $wc->status;
                    $result['note']         = "Serial: " . $wc->serial_no . ($wc->remarks ? " | " . $wc->remarks : "");
                    $result['url']          = "/warrantyClaim/{$wc->id}";
                    if ($wc->item) {
                        $result['items'][] = [
                            'title'       => $wc->item->title,
                            'code'        => $wc->serial_no,
                            'qty'         => 1,
                            'unit'        => 'Pcs',
                            'unit_price'  => 0,
                            'total_price' => 0,
                        ];
                    }
                }
                break;

            case 'Expense':
            case 'OfficeExpense':
                $exp = \App\Models\Expense::with(['employee', 'expense_details.account'])->find($id);
                if ($exp) {
                    $result['doc_no']       = $exp->expenseid;
                    $result['date']         = $exp->expense_date;
                    $result['party_name']   = optional($exp->employee)->full_name ?: 'Office Expense';
                    $result['party_mobile'] = optional($exp->employee)->mobile;
                    $result['total_amount'] = (float)$exp->total_amount;
                    $result['paid_amount']  = (float)$exp->total_amount;
                    $result['status']       = $exp->status ?: 'Approved';
                    $result['note']         = $exp->note ?: $exp->reason;
                    $result['url']          = "/expense/{$exp->id}";
                    if ($exp->expense_details) {
                        foreach ($exp->expense_details as $d) {
                            $result['items'][] = [
                                'title'       => optional($d->account)->account_name ?: 'Expense Head',
                                'code'        => optional($d->account)->account_code,
                                'qty'         => 1,
                                'unit'        => 'Item',
                                'unit_price'  => (float)$d->amount,
                                'total_price' => (float)$d->amount,
                            ];
                        }
                    }
                }
                break;

            case 'Loan':
            case 'LoanInfo':
                $loan = \App\Models\LoanInfo::with(['employee'])->find($id);
                if ($loan) {
                    $result['doc_no']       = $loan->trnsid;
                    $result['date']         = $loan->trns_date;
                    $result['party_name']   = optional($loan->employee)->full_name;
                    $result['party_mobile'] = optional($loan->employee)->mobile;
                    $result['total_amount'] = (float)$loan->amount;
                    $result['due_amount']   = (float)$loan->due_amount;
                    $result['status']       = $loan->trns_type;
                    $result['note']         = "Installment: " . $loan->total_installment . " | Reason: " . $loan->reason;
                    $result['url']          = "/loanInfo/{$loan->id}";
                }
                break;

            case 'Salary':
            case 'SalarySheet':
                $sal = \App\Models\SalarySheet::with(['salary_sheet_details.employee'])->find($id);
                if ($sal) {
                    $result['doc_no']       = $sal->title ?: ($sal->month . ' ' . $sal->year);
                    $result['date']         = $sal->generated_date;
                    $result['status']       = $sal->status;
                    $result['note']         = "Month: " . $sal->month . " " . $sal->year;
                    $result['url']          = "/salarySheet/{$sal->id}";
                    $totalGross = 0;
                    if ($sal->salary_sheet_details) {
                        foreach ($sal->salary_sheet_details as $sd) {
                            $totalGross += (float)$sd->gross_salary;
                            $result['items'][] = [
                                'title'       => optional($sd->employee)->full_name ?: 'Employee Salary',
                                'code'        => optional($sd->employee)->employee_id,
                                'qty'         => 1,
                                'unit'        => 'Staff',
                                'unit_price'  => (float)$sd->gross_salary,
                                'total_price' => (float)$sd->gross_salary,
                            ];
                        }
                    }
                    $result['total_amount'] = $totalGross;
                }
                break;

            case 'SalesReturn':
                $sr = \App\Models\SalesReturn::with(['client', 'details.item'])->find($id);
                if ($sr) {
                    $result['doc_no']       = $sr->return_no;
                    $result['date']         = $sr->return_date;
                    $result['party_name']   = optional($sr->client)->name;
                    $result['party_mobile'] = optional($sr->client)->mobile;
                    $result['total_amount'] = (float)$sr->total_refund_amount;
                    $result['note']         = $sr->return_reason;
                    $result['url']          = "/report/salesreturn";
                    if ($sr->details) {
                        foreach ($sr->details as $d) {
                            $result['items'][] = [
                                'title'       => optional($d->item)->title ?: 'Item',
                                'code'        => optional($d->item)->barcode,
                                'qty'         => (float)$d->qty,
                                'unit'        => 'Pcs',
                                'unit_price'  => (float)$d->price,
                                'total_price' => (float)$d->total_amount,
                            ];
                        }
                    }
                }
                break;
        }

        return response()->json($result);
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
        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        if ($payment->isSystemLocked()) {
            return response()->json([
                'message'   => $payment->lock_reason ?: 'This payment is linked to a system module and cannot be modified directly.',
                'exception' => $payment->lock_reason ?: 'This payment is linked to a system module and cannot be modified directly.'
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Only allowed fields can be updated during payment edit
            if ($request->has('payment_method')) {
                $payment->payment_method = $request->input('payment_method');
            }
            if ($request->has('mbanking_type')) {
                $payment->mbanking_type = $request->input('mbanking_type');
            }
            if ($request->has('trxid')) {
                $payment->trxid = $request->input('trxid');
            }
            if ($request->has('chequeno')) {
                $payment->chequeno = $request->input('chequeno');
            }
            if ($request->has('bank_id')) {
                $payment->bank_id = $request->input('bank_id');
            }
            if ($request->has('branch')) {
                $payment->branch = $request->input('branch');
            }
            if ($request->has('account_name')) {
                $payment->account_name = $request->input('account_name');
            }
            if ($request->has('accountno')) {
                $payment->accountno = $request->input('accountno');
            }
            if ($request->has('status')) {
                $payment->status = $request->input('status');
            }

            $payment->save();

            DB::commit();

            return $this->responseReturn("update", $payment);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['exception' => $e->getMessage()], 422);
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
        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        if ($payment->isSystemLocked()) {
            return response()->json([
                'message'   => $payment->lock_reason ?: 'This payment is linked to a system module and cannot be deleted directly.',
                'exception' => $payment->lock_reason ?: 'This payment is linked to a system module and cannot be deleted directly.'
            ], 422);
        }

        DB::beginTransaction();

        try {

            // 1. Collect all related references before deleting payment details
            $references = $payment->payment_details->map(function ($d) {
                return [
                    'reference_type' => $d->reference_type,
                    'reference_id'   => $d->reference_id,
                ];
            })->filter(function ($item) {
                return !empty($item['reference_type']) && !empty($item['reference_id']);
            })->unique(function ($item) {
                return $item['reference_type'] . '_' . $item['reference_id'];
            })->values()->all();

            // 2. Soft-delete all related vouchers and voucher details
            $this->removeVoucherByPaymentID($payment->id);

            // 3. Clear payment_id from SalesReturn / WarrantyClaim if attached
            SalesReturn::where('payment_id', $payment->id)->update(['payment_id' => null]);
            WarrantyClaim::where('payment_id', $payment->id)->update(['payment_id' => null]);

            // 4. Payment details (soft delete)
            $payment->payment_details()->delete();

            // 5. Payment (soft delete)
            $payment->delete();

            // 6. Recalculate reference tables after payment and details are soft-deleted
            foreach ($references as $ref) {
                $this->recalculateReference($ref['reference_type'], $ref['reference_id'], false);
            }

            DB::commit();
            return $this->responseReturn("delete", true);
        } catch (\Exception $e) {
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
