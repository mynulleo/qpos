<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== SALES_RETURNS COUNT ===\n";
echo "Count: " . DB::table('sales_returns')->count() . "\n\n";

echo "=== PAYMENT_DETAILS (reference_type = 'SalesReturn') ===\n";
$pd = DB::table('payment_details')
    ->join('payments', 'payments.id', '=', 'payment_details.payment_id')
    ->where('payment_details.reference_type', 'SalesReturn')
    ->select('payment_details.*', 'payments.payslipno', 'payments.payment_date', 'payments.payment_method', 'payments.client_id', 'payments.trxid')
    ->get();
echo "Count: " . $pd->count() . "\n";
foreach ($pd as $p) {
    echo "ID: {$p->id}, PaymentID: {$p->payment_id}, InvoiceID: {$p->reference_id}, ClientID: {$p->client_id}, Date: {$p->payment_date}, Amount: {$p->amount}, Method: {$p->payment_method}\n";
}

echo "\n=== STOCK_TRANSACTIONS (reference_type = 'SalesReturn') ===\n";
$st = DB::table('stock_transactions')
    ->where('reference_type', 'SalesReturn')
    ->get();
echo "Count: " . $st->count() . "\n";
foreach ($st as $s) {
    echo "ID: {$s->id}, ItemID: {$s->item_id}, RefID(Invoice): {$s->reference_id}, Date: {$s->transaction_date}, QtyIn: {$s->qty_in}\n";
}

echo "\n=== WASTAGES (POS Return) ===\n";
$w = DB::table('wastages')
    ->where('note', 'like', '%POS Return%')
    ->get();
echo "Count: " . $w->count() . "\n";
foreach ($w as $row) {
    echo "ID: {$row->id}, AuditNo: {$row->audit_number}, Date: {$row->audit_date}, Note: {$row->note}, TotalQty: {$row->total_qty}, TotalLoss: {$row->total_loss_amount}\n";
}

echo "\n=== WASTAGE_DETAILS (POS Return) ===\n";
$wd = DB::table('wastage_details')
    ->where('remarks', 'like', '%POS Return%')
    ->get();
echo "Count: " . $wd->count() . "\n";
foreach ($wd as $row) {
    echo "ID: {$row->id}, WastageID: {$row->wastage_id}, ItemID: {$row->item_id}, Reason: {$row->reason}, Qty: {$row->quantity}, Price: {$row->unit_price}, Amount: {$row->total_amount}, Remarks: {$row->remarks}\n";
}
