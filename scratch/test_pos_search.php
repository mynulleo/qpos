<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$req = new \Illuminate\Http\Request(['term' => '1']);
$ctrl = new \App\Http\Controllers\Admin\PosController();
try {
    $res = $ctrl->searchItems($req);
    echo "SUCCESS: Found " . count((array)$res->getData()) . " items\n";
    echo substr(json_encode($res->getData(), JSON_PRETTY_PRINT), 0, 500) . "\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
