<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SalesReturn;
use App\Models\SalesReturnDetail;
use App\Models\PaymentDetail;
use App\Models\Payment;
use App\Models\StockTransaction;
use App\Models\Wastage;
use App\Models\WastageDetail;
use App\Models\Invoice;
use App\Models\InvoiceDetails;
use Illuminate\Support\Facades\DB;

echo "=== STARTING HISTORICAL SALES RETURN SYNC ===\n";

DB::beginTransaction();

try {
    // 1. Process all PaymentDetail records with reference_type = 'SalesReturn'
    $paymentDetails = PaymentDetail::where('reference_type', 'SalesReturn')
        ->with('payment')
        ->get();

    foreach ($paymentDetails as $pd) {
        $payment = $pd->payment;
        $invoiceId = $pd->reference_id;
        $invoice = Invoice::with(['details.item', 'client'])->find($invoiceId);

        if (!$invoice) {
            echo "Skipping PaymentDetail ID {$pd->id}: Invoice #{$invoiceId} not found\n";
            continue;
        }

        // Check if already synced by payment_id or (invoice_id + payment_id)
        $existing = SalesReturn::where('payment_id', $pd->payment_id)->first();
        if ($existing) {
            echo "Already synced: SalesReturn #{$existing->return_no} for Payment #{$pd->payment_id}\n";
            continue;
        }

        // Check if there is a matching Wastage
        $wastage = Wastage::where('note', 'like', "%POS Return%[ID:{$invoiceId}]%")
            ->orWhere('note', 'like', "%Invoice #{$invoice->invoice_no}%")
            ->with('wastage_details')
            ->first();

        $returnReason = 'Client request';
        $returnNote = null;
        $wastageId = null;

        if ($wastage) {
            $wastageId = $wastage->id;
            // Parse reason from wastage note
            if (preg_match('/Reason:\s*([^\|]+)/i', $wastage->note, $rMatches)) {
                $returnReason = trim($rMatches[1]);
            }
            if (preg_match('/Note:\s*(.+)$/i', $wastage->note, $nMatches)) {
                $returnNote = trim($nMatches[1]);
            }
        }

        $rawDate = $payment ? $payment->getRawOriginal('payment_date') : $invoice->getRawOriginal('invoice_date');
        if (!$rawDate) $rawDate = date('Y-m-d');

        $returnNo = SalesReturn::generateReturnNo();
        $totalRefund = floatval($pd->amount);
        $totalQty = 0;

        // Determine item lines
        $itemsToInsert = [];

        if ($wastage && $wastage->wastage_details->isNotEmpty()) {
            foreach ($wastage->wastage_details as $wd) {
                $qty = floatval($wd->quantity);
                $rate = floatval($wd->unit_price);
                $amount = floatval($wd->total_amount);
                $totalQty += $qty;

                $itemsToInsert[] = [
                    'item_id'       => $wd->item_id,
                    'category_id'   => $wd->category_id,
                    'color_id'      => $wd->color_id,
                    'size_id'       => $wd->size_id,
                    'unit_id'       => $wd->unit_id,
                    'qty'           => $qty,
                    'rate'          => $rate,
                    'refund_amount' => $amount,
                    'return_reason' => $wd->reason ?: $returnReason,
                    'serial_no'     => $wd->serial_no,
                    'note'          => $returnNote,
                ];
            }
        } else {
            // Check StockTransaction lines for this invoice
            $stockTrans = StockTransaction::where('reference_type', 'SalesReturn')
                ->where('reference_id', (string)$invoiceId)
                ->where('transaction_date', $rawDate)
                ->get();

            if ($stockTrans->isEmpty()) {
                $stockTrans = StockTransaction::where('reference_type', 'SalesReturn')
                    ->where('reference_id', (string)$invoiceId)
                    ->get();
            }

            if ($stockTrans->isNotEmpty()) {
                foreach ($stockTrans as $st) {
                    $qty = floatval($st->qty_in);
                    $totalQty += $qty;

                    // Match with invoice detail rate
                    $invDet = $invoice->details->first(function ($d) use ($st) {
                        return $d->item_id == $st->item_id &&
                            (($st->color_id === null && empty($d->color_id)) || $d->color_id == $st->color_id) &&
                            (($st->size_id === null && empty($d->size_id)) || $d->size_id == $st->size_id);
                    }) ?? $invoice->details->firstWhere('item_id', $st->item_id);

                    $rate = $invDet ? floatval($invDet->rate) : ($totalQty > 0 ? round($totalRefund / $totalQty, 2) : 0);
                    $lineRefund = $qty * $rate;

                    $itemsToInsert[] = [
                        'item_id'           => $st->item_id,
                        'invoice_detail_id' => $invDet ? $invDet->id : null,
                        'category_id'       => $invDet && $invDet->item ? $invDet->item->category_id : null,
                        'color_id'          => $st->color_id,
                        'size_id'           => $st->size_id,
                        'unit_id'           => $invDet && $invDet->item ? $invDet->item->unit_id : null,
                        'qty'               => $qty,
                        'rate'              => $rate,
                        'refund_amount'     => $lineRefund > 0 ? $lineRefund : $totalRefund,
                        'return_reason'     => 'Client request',
                        'serial_no'         => $invDet ? $invDet->serial_no : null,
                        'note'              => $returnNote,
                    ];
                }
            } else {
                // Fallback from invoice details
                if ($invoice->details->isNotEmpty()) {
                    $firstDetail = $invoice->details->first();
                    $qty = 1;
                    $totalQty = 1;
                    $rate = $totalRefund;
                    $itemsToInsert[] = [
                        'item_id'           => $firstDetail->item_id,
                        'invoice_detail_id' => $firstDetail->id,
                        'category_id'       => $firstDetail->item ? $firstDetail->item->category_id : null,
                        'color_id'          => $firstDetail->color_id,
                        'size_id'           => $firstDetail->size_id,
                        'unit_id'           => $firstDetail->item ? $firstDetail->item->unit_id : null,
                        'qty'               => $qty,
                        'rate'              => $rate,
                        'refund_amount'     => $totalRefund,
                        'return_reason'     => $returnReason,
                        'serial_no'         => $firstDetail->serial_no,
                        'note'              => $returnNote,
                    ];
                }
            }
        }

        if ($totalQty <= 0) $totalQty = 1;

        $salesReturn = SalesReturn::create([
            'return_no'           => $returnNo,
            'invoice_id'          => $invoice->id,
            'client_id'           => $payment ? $payment->client_id : $invoice->client_id,
            'branch_id'           => $invoice->branch_id ?? 1,
            'return_date'         => $rawDate,
            'return_reason'       => $returnReason,
            'note'                => $returnNote,
            'payment_method'      => $payment ? $payment->payment_method : 'Cash',
            'mbanking_type'       => $payment ? $payment->mbanking_type : null,
            'trxid'               => $payment ? $payment->trxid : null,
            'total_qty'           => $totalQty,
            'total_refund_amount' => $totalRefund,
            'payment_id'          => $payment ? $payment->id : null,
            'wastage_id'          => $wastageId,
            'created_by'          => $payment ? $payment->created_by : 1,
            'status'              => 'active',
        ]);

        foreach ($itemsToInsert as $item) {
            $item['sales_return_id'] = $salesReturn->id;
            $item['status'] = 'active';
            SalesReturnDetail::create($item);
        }

        echo "Created SalesReturn #{$salesReturn->return_no} for Invoice #{$invoice->invoice_no} (Reason: {$returnReason}, Refund: {$totalRefund}, Items: " . count($itemsToInsert) . ")\n";
    }

    // 2. Check any remaining StockTransactions with reference_type='SalesReturn' not attached to a payment
    $orphanStockTrans = StockTransaction::where('reference_type', 'SalesReturn')->get();
    foreach ($orphanStockTrans as $st) {
        $invId = intval($st->reference_id);
        if (!$invId) continue;

        $already = SalesReturn::where('invoice_id', $invId)
            ->whereHas('details', function ($q) use ($st) {
                $q->where('item_id', $st->item_id);
            })->first();

        if ($already) continue;

        $invoice = Invoice::with(['details.item', 'client'])->find($invId);
        if (!$invoice) continue;

        $invDet = $invoice->details->first(function ($d) use ($st) {
            return $d->item_id == $st->item_id &&
                (($st->color_id === null && empty($d->color_id)) || $d->color_id == $st->color_id) &&
                (($st->size_id === null && empty($d->size_id)) || $d->size_id == $st->size_id);
        }) ?? $invoice->details->firstWhere('item_id', $st->item_id);

        $qty = floatval($st->qty_in);
        $rate = $invDet ? floatval($invDet->rate) : 0;
        $amount = $qty * $rate;
        $rawDate = $st->getRawOriginal('transaction_date') ?: date('Y-m-d');
        $returnNo = SalesReturn::generateReturnNo();

        $sr = SalesReturn::create([
            'return_no'           => $returnNo,
            'invoice_id'          => $invoice->id,
            'client_id'           => $invoice->client_id,
            'branch_id'           => $invoice->branch_id ?? 1,
            'return_date'         => $rawDate,
            'return_reason'       => 'Client request',
            'note'                => 'Historical return',
            'payment_method'      => 'Cash',
            'total_qty'           => $qty,
            'total_refund_amount' => $amount,
            'payment_id'          => null,
            'wastage_id'          => null,
            'created_by'          => 1,
            'status'              => 'active',
        ]);

        SalesReturnDetail::create([
            'sales_return_id'   => $sr->id,
            'invoice_detail_id' => $invDet ? $invDet->id : null,
            'item_id'           => $st->item_id,
            'category_id'       => $invDet && $invDet->item ? $invDet->item->category_id : null,
            'color_id'          => $st->color_id,
            'size_id'           => $st->size_id,
            'unit_id'           => $invDet && $invDet->item ? $invDet->item->unit_id : null,
            'qty'               => $qty,
            'rate'              => $rate,
            'refund_amount'     => $amount,
            'return_reason'     => 'Client request',
            'serial_no'         => $invDet ? $invDet->serial_no : null,
            'note'              => 'Historical return',
            'status'            => 'active',
        ]);

        echo "Created SalesReturn from StockTrans #{$sr->return_no} for Invoice #{$invoice->invoice_no} (Item: {$st->item_id}, Qty: {$qty}, Amount: {$amount})\n";
    }

    DB::commit();
    echo "=== SYNC COMPLETED SUCCESSFULLY ===\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
