<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentDetail;
use App\Models\StockTransaction;
use App\Models\Wastage;
use App\Models\WastageDetail;

echo "=== PAYMENT DETAILS FOR SALES RETURN ===\n";
$payDetails = PaymentDetail::where('reference_type', 'SalesReturn')->with('payment')->get();
foreach ($payDetails as $pd) {
    echo "PaymentDetail ID: {$pd->id}, PaymentID: {$pd->payment_id}, InvoiceID: {$pd->reference_id}, Amount: {$pd->amount}\n";
    if ($pd->payment) {
        echo "  Payment Slip: {$pd->payment->payslipno}, Date: {$pd->payment->payment_date}, ClientID: {$pd->payment->client_id}, Method: {$pd->payment->payment_method}\n";
    }
    $inv = Invoice::with('details.item', 'client')->find($pd->reference_id);
    if ($inv) {
        echo "  Invoice No: {$inv->invoice_no}, Date: {$inv->invoice_date}, Client: " . ($inv->client ? $inv->client->name : 'Walk-in') . "\n";
    }
}

echo "\n=== STOCK TRANSACTIONS (SalesReturn) ===\n";
$st = StockTransaction::where('reference_type', 'SalesReturn')->get();
foreach ($st as $s) {
    echo "ST ID: {$s->id}, Item: {$s->item_id}, RefID: {$s->reference_id}, Date: {$s->transaction_date}, QtyIn: {$s->qty_in}, Color: {$s->color_id}, Size: {$s->size_id}\n";
}

echo "\n=== WASTAGES (POS Return) ===\n";
$ws = Wastage::where('note', 'like', '%POS Return%')->with('details')->get();
foreach ($ws as $w) {
    echo "Wastage ID: {$w->id}, AuditNo: {$w->audit_number}, Date: {$w->audit_date}, Note: {$w->note}\n";
    foreach ($w->details as $wd) {
        echo "  Detail ID: {$wd->id}, ItemID: {$wd->item_id}, Reason: {$wd->reason}, Qty: {$wd->quantity}, Price: {$wd->unit_price}, Remarks: {$wd->remarks}\n";
    }
}
