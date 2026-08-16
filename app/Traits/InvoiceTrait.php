<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use App\Models\PaymentDetail;

trait InvoiceTrait
{

    public function previousDue($client_id, $invoiceId)
    {
        // current invoice
        $currentInvoice = Invoice::find($invoiceId);

        if (!$currentInvoice) {
            return 0;
        }

        // =========================
        // Previous invoices (by date or id)
        // =========================
        $previousInvoices = Invoice::where('client_id', $client_id)
            ->whereNull('deleted_at')
            ->where('id', '<', $invoiceId) // OR use invoice_date
            ->pluck('id');

        if ($previousInvoices->isEmpty()) {
            return 0;
        }

        // =========================
        // Total Invoice Amount
        // =========================
        $totalInvoiceAmount = Invoice::whereIn('id', $previousInvoices)
            ->sum('amount');

        // =========================
        // Total Paid Amount
        // =========================
        $totalPaid = PaymentDetail::where('reference_type', 'Invoice')
            ->whereIn('reference_id', $previousInvoices)
            ->whereNull('deleted_at')
            ->sum('amount');

        // =========================
        // Previous Due
        // =========================
        $previousDue = $totalInvoiceAmount - $totalPaid;

        return $previousDue;
    }
}
