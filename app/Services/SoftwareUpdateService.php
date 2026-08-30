<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SoftwareUpdateService
{
    /**
     * Get current database update status by comparing codebase with tenant database.
     *
     * @return array
     */
    public function getUpdateStatus(): array
    {
        try {
            $ranMigrations = DB::table('migrations')->pluck('migration')->toArray();
        } catch (\Throwable $e) {
            $ranMigrations = [];
        }

        // 1. Scan pending Laravel migration files
        $allMigrationFiles = glob(database_path('migrations/*.php')) ?: [];
        $pendingMigrations = [];

        foreach ($allMigrationFiles as $file) {
            $migrationName = pathinfo($file, PATHINFO_FILENAME);
            if (!in_array($migrationName, $ranMigrations)) {
                $humanTitle = preg_replace('/^\d{4}_\d{2}_\d{2}_\d+_(create_|add_|modify_)?/', '', $migrationName);
                $pendingMigrations[] = [
                    'name'  => $migrationName,
                    'title' => ucwords(str_replace('_', ' ', $humanTitle)),
                    'date'  => substr($migrationName, 0, 10),
                ];
            }
        }

        // 2. Scan pending custom SQL patch files (if any in database/sql_updates/*.sql)
        $pendingSqlPatches = [];
        $sqlFiles = glob(database_path('sql_updates/*.sql')) ?: [];

        if (!empty($sqlFiles)) {
            $this->ensureSqlPatchTableExists();
            try {
                $appliedPatches = DB::table('applied_sql_patches')->pluck('patch_name')->toArray();
            } catch (\Throwable $e) {
                $appliedPatches = [];
            }

            foreach ($sqlFiles as $file) {
                $patchName = basename($file);
                if (!in_array($patchName, $appliedPatches)) {
                    $pendingSqlPatches[] = [
                        'name'  => $patchName,
                        'title' => ucwords(str_replace(['_', '.sql'], [' ', ''], $patchName)),
                        'path'  => $file,
                    ];
                }
            }
        }

        $pendingCount = count($pendingMigrations) + count($pendingSqlPatches);

        // Get latest executed migration
        $lastMigration = null;
        if (!empty($ranMigrations)) {
            $lastMigration = end($ranMigrations);
        }

        // Database connection details
        $connection = DB::getDefaultConnection();
        $dbName = config("database.connections.{$connection}.database", 'tenant');

        return [
            'is_update_needed'    => $pendingCount > 0,
            'pending_count'       => $pendingCount,
            'pending_migrations'  => $pendingMigrations,
            'pending_sql_patches' => $pendingSqlPatches,
            'total_migrations'    => count($allMigrationFiles),
            'executed_migrations' => count($ranMigrations),
            'last_migration'      => $lastMigration,
            'database_name'       => $dbName,
            'php_version'         => PHP_VERSION,
            'laravel_version'     => app()->version(),
        ];
    }

    /**
     * Execute all pending migrations and SQL patches on tenant database safely.
     *
     * @return array
     */
    public function runUpdate(): array
    {
        $statusBefore = $this->getUpdateStatus();
        if (!$statusBefore['is_update_needed']) {
            return [
                'success'          => true,
                'message'          => 'Database is already completely up to date! No pending updates.',
                'output'           => "No pending migrations or SQL patches.\nDatabase schema is up to date.",
                'is_update_needed' => false,
                'pending_count'    => 0,
            ];
        }

        $logs = [];
        $batch = (DB::table('migrations')->max('batch') ?: 0) + 1;

        // 1. Run Pending Migrations Safely
        foreach ($statusBefore['pending_migrations'] as $item) {
            $migrationName = $item['name'];
            $filePath = database_path("migrations/{$migrationName}.php");

            if (!file_exists($filePath)) {
                continue;
            }

            try {
                $fileContent = file_get_contents($filePath);

                // Check if migration is creating a table that already exists
                if (preg_match("/Schema::create\s*\(\s*['\"]([^'\"]+)['\"]/", $fileContent, $matches)) {
                    $tableName = $matches[1];
                    if (Schema::hasTable($tableName)) {
                        // Table already exists in DB from dump, baseline it into migrations table
                        DB::table('migrations')->insert([
                            'migration' => $migrationName,
                            'batch'     => $batch,
                        ]);
                        $logs[] = "Synced existing table: {$tableName} ({$migrationName})";
                        continue;
                    }
                }

                // Check if migration is adding a column that already exists
                if (preg_match("/Schema::table\s*\(\s*['\"]([^'\"]+)['\"].*?->(?:string|integer|bigInteger|tinyInteger|boolean|text|decimal|date|enum)\s*\(\s*['\"]([^'\"]+)['\"]/s", $fileContent, $colMatches)) {
                    $tableName = $colMatches[1];
                    $columnName = $colMatches[2];
                    if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, $columnName)) {
                        DB::table('migrations')->insert([
                            'migration' => $migrationName,
                            'batch'     => $batch,
                        ]);
                        $logs[] = "Synced existing column: {$tableName}.{$columnName} ({$migrationName})";
                        continue;
                    }
                }

                // Execute the migration class
                $migrationObject = require $filePath;
                if (is_object($migrationObject) && method_exists($migrationObject, 'up')) {
                    $migrationObject->up();
                }

                DB::table('migrations')->insert([
                    'migration' => $migrationName,
                    'batch'     => $batch,
                ]);
                $logs[] = "Migrated: {$migrationName}";

            } catch (\Throwable $e) {
                // If it failed because table/column already exists, safely mark as recorded
                if (str_contains($e->getMessage(), 'already exists') || str_contains($e->getMessage(), 'Duplicate column')) {
                    DB::table('migrations')->insert([
                        'migration' => $migrationName,
                        'batch'     => $batch,
                    ]);
                    $logs[] = "Resolved already existing structure: {$migrationName}";
                } else {
                    $logs[] = "Failed: {$migrationName} - " . $e->getMessage();
                }
            }
        }

        // 2. Run Custom SQL Patches if any
        if (!empty($statusBefore['pending_sql_patches'])) {
            $this->ensureSqlPatchTableExists();
            foreach ($statusBefore['pending_sql_patches'] as $patch) {
                try {
                    $sql = file_get_contents($patch['path']);
                    if (!empty(trim($sql))) {
                        DB::unprepared($sql);
                    }
                    DB::table('applied_sql_patches')->insert([
                        'patch_name'   => $patch['name'],
                        'executed_at'  => now(),
                    ]);
                    $logs[] = "Executed SQL Patch: {$patch['name']}";
                } catch (\Throwable $e) {
                    $logs[] = "Error executing SQL Patch {$patch['name']}: " . $e->getMessage();
                }
            }
        }

        // 3. Clear application caches
        try {
            Artisan::call('optimize:clear');
            $logs[] = "Application caches cleared successfully.";
        } catch (\Throwable $e) {
            // Ignore cache clear error
        }

        // 4. Verify post-update status
        $statusAfter = $this->getUpdateStatus();

        return [
            'success'          => $statusAfter['pending_count'] === 0,
            'message'          => $statusAfter['pending_count'] === 0
                                    ? 'Database successfully updated to the latest version!'
                                    : 'Some updates were applied, but pending items remain.',
            'output'           => implode("\n", $logs),
            'is_update_needed' => $statusAfter['is_update_needed'],
            'pending_count'    => $statusAfter['pending_count'],
            'status'           => $statusAfter,
        ];
    }

    /**
     * Ensure the applied_sql_patches table exists on the tenant DB.
     */
    protected function ensureSqlPatchTableExists(): void
    {
        try {
            if (!Schema::hasTable('applied_sql_patches')) {
                Schema::create('applied_sql_patches', function ($table) {
                    $table->id();
                    $table->string('patch_name')->unique();
                    $table->timestamp('executed_at')->useCurrent();
                });
            }
        } catch (\Throwable $e) {
            // Table may already exist or connection issue
        }
    }
}
