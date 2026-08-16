<?php

/**
 * @Quill Information Technology
 */

namespace App\Http\Controllers\Admin;

use Storage;
use Exception;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\InvoiceDetails;
use App\Models\Voucher;
use App\Models\InvoiceMonth;
use Illuminate\Http\Request;
use App\Models\PaymentDetail;
use App\Models\VoucherDetail;
use App\Http\Resources\Resource;
use App\Traits\InvoiceTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Base\BaseController;
use Illuminate\Pagination\LengthAwarePaginator;

class InvoiceController extends BaseController
{
    use InvoiceTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['invoice_details', 'client:id,clientid,name,org_name', 'payment_details:id,payment_id,reference_type,reference_id,amount,is_closed'])->latest();

        // ✅ account_id → invoice_details টেবিলে filter
        if (!empty($request->account_id)) {
            $query->whereHas('invoice_details', function ($q) use ($request) {
                $q->where('account_id', $request->account_id);
            });
        }

        // ✅ payment_status → invoices টেবিলে
        if (!empty($request->client_id)) {
            $query->where('client_id', $request->client_id);
        }

        // ✅ invoice date range
        if (!empty($request->from_invoice_date) && !empty($request->to_invoice_date)) {
            $query->whereBetween('invoice_date', [
                date('Y-m-d', strtotime($request->from_invoice_date)),
                date('Y-m-d', strtotime($request->to_invoice_date))
            ]);
        } elseif (!empty($request->from_invoice_date)) {
            $query->whereDate('invoice_date', '>=', date('Y-m-d', strtotime($request->from_invoice_date)));
        } elseif (!empty($request->to_invoice_date)) {
            $query->whereDate('invoice_date', '<=', date('Y-m-d', strtotime($request->to_invoice_date)));
        }

        // ✅ payment_status → invoices টেবিলে
        if (!empty($request->is_closed) &&  $request->is_closed == 1) {
            $query->where('is_closed', 1);
        }

        // ✅ field_name & value (যদি generic search থাকে)
        if (!empty($request->field_name) && !empty($request->value)) {
            $query->whereLike($request->field_name, $request->value);
        }

        if ($request->allData) {
            return $query->get();
        } else {
            $datas = $query->paginate($request->pagination);
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
        DB::beginTransaction();
        try {
            $data = $request->all();

            $data['client_id'] = $request->client['id'];

            $invoiceDetails = $data['invoice_details'] ?? [];

            unset($data['invoice_details']);

            $data['invoice_no'] = Invoice::generateInvoiceNumber();
            $data['invoice_date'] = vue_to_server_date($data['invoice_date']);

            $invoice = Invoice::create($data);
            foreach ($invoiceDetails as $d) {
                $invoice->invoice_details()->create([
                    'workorder_id' => $d['workorder_id'],
                    'reference' => $d['reference'],
                    'description' => $d['description'],
                    'qty' => $d['qty'],
                    'amount' => $d['amount'],
                    'currency_id' => $d['currency_id'],
                    'currency_rate' => $d['currency_rate'],
                    'total_amount' => $d['total_amount'] ?? 0,
                ]);
            }

            DB::commit();
            return $this->responseReturn("create", $invoice);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json(['exception' => $ex->getMessage()], 422);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $invoice = Invoice::with([
            'client',
            'invoice_details',
            'invoice_details.account'
        ])->find($id);


        return response()->json([
            'id'              => $invoice->id,
            'client_id'       => $invoice->client_id,
            'original_amount' => $invoice->original_amount,
            'invoice_date'    => $invoice->invoice_date,
            'discount'        => $invoice->discount,
            'original_amount' => $invoice->original_amount,
            'discount'        => $invoice->discount,
            'amount'          => $invoice->amount,
            'client'          => $invoice->client,
            'invoice_details' => $invoice->invoice_details->map(function ($row) {
                return [
                    'invoice_id'    => $row->invoice_id,
                    'workorder_id'  => $row->workorder_id,
                    'account'       => $row->account,
                    'description'   => $row->description,
                    'reference'     => $row->reference,
                    'qty'           => $row->qty,
                    'amount'        => $row->amount,
                    'currency_id'   => $row->currency_id,
                    'currency_rate' => $row->currency_rate,
                    'total_amount'  => $row->total_amount,
                ];
            })->values(),
            'status'          => $invoice->status,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Invoice  $invoice
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
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        DB::beginTransaction();
        try {
            $data = $request->all();

            $details = $data['invoice_details'] ?? [];
            $months  = $data['invoice_months'] ?? [];

            unset($data['invoice_details'], $data['invoice_months']);

            $data['invoice_date'] = vue_to_server_date($data['invoice_date']);

            $invoice->fill($data)->save();

            /*
            |--------------------------------------------------------------------------
            | INVOICE DETAILS (UPDATE / CREATE / DELETE)
            |--------------------------------------------------------------------------
            */

            $existingDetailIds = $invoice->invoice_details()->pluck('id')->toArray();
            $submittedDetailIds = collect($details)->pluck('id')->filter()->toArray();

            // DELETE (which are removed from frontend)
            $deleteIds = array_diff($existingDetailIds, $submittedDetailIds);
            if (!empty($deleteIds)) {
                InvoiceDetails::whereIn('id', $deleteIds)->delete();
            }

            $account_id = Account::whereNotNull('system_key_name')->first()->id;

            foreach ($details as $d) {

                if (!empty($d['id'])) {
                    // UPDATE (this will trigger boot->updating)
                    $detail = InvoiceDetails::find($d['id']);
                    if ($detail) {
                        $detail->update([
                            'workorder_id'  => $d['workorder_id'],
                            'reference'     => $d['reference'],
                            'description'   => $d['description'],
                            'qty'           => $d['qty'],
                            'currency_id'   => $d['currency_id'] ?? null,
                            'currency_rate' => $d['currency_rate'] ?? null,
                            'amount'        => $d['amount'] ?? 0,
                            'total_amount'  => $d['total_amount'] ?? 0,
                        ]);
                    }
                } else {
                    // CREATE (this will trigger boot->created)
                    $invoice->invoice_details()->create([
                        'workorder_id'  => $d['workorder_id'],
                        'reference'     => $d['reference'],
                        'description'   => $d['description'],
                        'qty'           => $d['qty'],
                        'currency_id'   => $d['currency_id'] ?? null,
                        'currency_rate' => $d['currency_rate'] ?? null,
                        'amount'        => $d['amount'] ?? 0,
                        'total_amount'  => $d['total_amount'] ?? 0,
                    ]);
                }
            }

            DB::commit();

            return $this->responseReturn("update", $invoice);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json(['exception' => $ex->getMessage()], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);

        DB::beginTransaction();

        try {

            // 1️⃣ Check payment
            $hasPayment = PaymentDetail::where('reference_type', 'Invoice')
                ->where('reference_id', $invoice->id)
                ->exists();

            if ($hasPayment) {
                return response()->json([
                    'message' => 'This invoice has payments. Delete is not allowed.'
                ], 422);
            }

            // 2️⃣ Delete vouchers (with boot)
            $vouchers = Voucher::where('source', 'Invoice')
                ->where('source_id', $invoice->id)
                ->get();

            foreach ($vouchers as $voucher) {
                // voucher details delete (boot trigger)
                $voucher->voucher_details->each->delete();
                // voucher delete (boot trigger)
                $voucher->delete();
            }

            // 3️⃣ Invoice details delete (boot trigger)
            $invoice->invoice_details->each->delete();

            // 4️⃣ Invoice delete (boot trigger)
            $invoice->delete();

            DB::commit();

            return $this->responseReturn("delete", true);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'exception' => $e->getMessage()
            ], 422);
        }
    }

    public function paynow($invoiceid)
    {
        if (!$invoiceid) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice ID not provided'
            ], 400);
        }

        $invoice = Invoice::find($invoiceid);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        try {
            $invoice->paid_amount = $invoice->amount;
            $invoice->payment_date = date('Y-m-d');
            $invoice->card_type = 'cash';
            $invoice->payment_status = 'paid';
            $invoice->save();

            return response()->json([
                'success' => true,
                'message' => 'Cash Payment completed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    public function months(Invoice $invoice)
    {
        return InvoiceMonth::where('invoice_id', $invoice->id)
            ->pluck('invoice_month')
            ->map(fn($m) => substr($m, 0, 7))
            ->toArray();
    }


    public function bill(Request $request, $invoiceid)
    {
        if ($request->format() == 'html') {
            return view('admin.layouts.admin_app');
        }
        $invoice = Invoice::with(
            [
                'client',
                'invoice_details.currency:id,title,short_name',
            ]
        )->where('id', $invoiceid)->first();

        if ($invoice) {
            $client_id = $invoice->client_id;
            $previous_due = $this->previousDue($client_id, $invoice->id);
            $invoice->previous_due = $previous_due;
            $invoice->total_amount  = $invoice->amount + $previous_due;
        }

        return $invoice->toArray();
    }

    public function moneyreceipt(Request $request, $invoiceid)
    {
        if ($request->format() == 'html') {
            return view('admin.layouts.admin_app');
        }

        $invoice = Invoice::with(
            [
                'client:id,name,org_name,mobile,email,address',
                'invoice_details.account:id,account_name',
                'payment_details.payment:payslipno,payment_date'
            ]
        )->where('id', $invoiceid)->first();
        return $invoice;
    }

    public function generateInvoice()
    {
        return true;
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
