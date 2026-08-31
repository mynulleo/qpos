<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\SoftwareUpdateService;
use Illuminate\Support\Facades\DB;

$service = new SoftwareUpdateService();

echo "1. Current status:\n";
$s1 = $service->getUpdateStatus();
echo "Is update needed: " . ($s1['is_update_needed'] ? 'YES' : 'NO') . " (pending: {$s1['pending_count']})\n";

// Simulate a developer adding a new migration in codebase
$dummyMigrationFile = database_path('migrations/2026_09_01_999999_test_saas_auto_detected_update.php');
file_put_contents($dummyMigrationFile, '<?php use Illuminate\Database\Migrations\Migration; return new class extends Migration { public function up() {} };');

echo "\n2. After developer adds a new migration to codebase:\n";
$s2 = $service->getUpdateStatus();
echo "Is update needed: " . ($s2['is_update_needed'] ? 'YES' : 'NO') . " (pending: {$s2['pending_count']})\n";
if (!empty($s2['pending_migrations'])) {
    echo "Detected pending migration: " . $s2['pending_migrations'][0]['name'] . "\n";
}

echo "\n3. User clicks 'Update Database':\n";
$res = $service->runUpdate();
echo "Result success: " . ($res['success'] ? 'YES' : 'NO') . "\n";

echo "\n4. After update completes:\n";
$s3 = $service->getUpdateStatus();
echo "Is update needed: " . ($s3['is_update_needed'] ? 'YES' : 'NO') . " (pending: {$s3['pending_count']})\n";

// Cleanup dummy migration file & DB row
@unlink($dummyMigrationFile);
DB::table('migrations')->where('migration', '2026_09_01_999999_test_saas_auto_detected_update')->delete();
echo "\nTest cleanup done.\n";
