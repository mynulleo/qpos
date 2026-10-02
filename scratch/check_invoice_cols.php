<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cols = \Illuminate\Support\Facades\Schema::getColumnListing('invoices');
echo json_encode($cols, JSON_PRETTY_PRINT) . PHP_EOL;

$sample = \App\Models\Invoice::with(['client'])->latest('id')->first();
if ($sample) {
    echo "Sample invoice ID: {$sample->id}\n";
    echo "Sample invoice array: " . json_encode($sample->toArray(), JSON_PRETTY_PRINT) . PHP_EOL;
}
