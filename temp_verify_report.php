<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\Admin\ReportController;

$request = Request::create('/report/sales-return', 'GET', [
    'from_date' => date('Y-m-01'),
    'to_date'   => date('Y-m-t'),
]);
$request->headers->set('Accept', 'application/json');

$controller = new ReportController();
$response = $controller->salesReturn($request);

echo "Status Code: " . $response->getStatusCode() . "\n";
$data = json_decode($response->getContent(), true);
echo "From: " . ($data['from'] ?? 'N/A') . " To: " . ($data['to'] ?? 'N/A') . "\n";
echo "Summary Total Returns: " . ($data['summary']['total_returns'] ?? 0) . "\n";
echo "Summary Total Qty: " . ($data['summary']['total_qty'] ?? 0) . "\n";
echo "Summary Total Refund: Tk. " . ($data['summary']['total_refund'] ?? 0) . "\n";
echo "Summary Restocked Qty: " . ($data['summary']['restocked_qty'] ?? 0) . "\n";
echo "Summary Wastage/Expired Qty: " . ($data['summary']['wastage_qty'] ?? 0) . "\n";
echo "Returns List Count: " . count($data['returns'] ?? []) . "\n";

foreach ($data['returns'] as $ret) {
    echo " -> [{$ret['return_no']}] Date: {$ret['return_date']}, Inv: " . ($ret['invoice']['invoice_no'] ?? 'N/A') . ", Reason: {$ret['return_reason']}, Note: '{$ret['note']}', Refund: Tk. {$ret['total_refund_amount']}, Method: {$ret['payment_method']}\n";
}

echo "\nItem Breakdown Count: " . count($data['item_breakdown'] ?? []) . "\n";
foreach ($data['item_breakdown'] as $ib) {
    echo " -> [{$ib['barcode']}] {$ib['item_title']}: Qty {$ib['total_qty']} {$ib['unit']}, Total Tk. {$ib['total_refund']}, Reasons: {$ib['reasons_text']}\n";
}
