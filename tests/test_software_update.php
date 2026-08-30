<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\SoftwareUpdateService;

$service = new SoftwareUpdateService();

echo "=== 1. CHECKING INITIAL UPDATE STATUS ===\n";
$status = $service->getUpdateStatus();
echo "Is Update Needed: " . ($status['is_update_needed'] ? 'YES' : 'NO') . "\n";
echo "Pending Updates Count: " . $status['pending_count'] . "\n";
echo "Database Name: " . $status['database_name'] . "\n";

if ($status['is_update_needed']) {
    echo "\n=== 2. RUNNING SAFE DATABASE UPDATE ===\n";
    $result = $service->runUpdate();
    echo "Success: " . ($result['success'] ? 'YES' : 'NO') . "\n";
    echo "Message: " . $result['message'] . "\n";
    echo "Logs:\n" . substr($result['output'], 0, 800) . "...\n";

    echo "\n=== 3. VERIFYING POST-UPDATE STATUS ===\n";
    $statusAfter = $service->getUpdateStatus();
    echo "Is Update Needed: " . ($statusAfter['is_update_needed'] ? 'YES' : 'NO') . "\n";
    echo "Pending Count: " . $statusAfter['pending_count'] . "\n";
    echo "Total Migrations in DB: " . $statusAfter['executed_migrations'] . " / " . $statusAfter['total_migrations'] . "\n";
} else {
    echo "Database is already completely up to date!\n";
}
