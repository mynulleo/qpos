<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use PDO;

class DatabaseBackupRestoreService
{
    protected string $storagePath;
    protected string $snapshotPath;

    public function __construct()
    {
        $this->storagePath = storage_path('app/backups/databases');
        $this->snapshotPath = storage_path('app/backups/databases/snapshots');

        if (!File::isDirectory($this->storagePath)) {
            File::makeDirectory($this->storagePath, 0755, true);
        }

        if (!File::isDirectory($this->snapshotPath)) {
            File::makeDirectory($this->snapshotPath, 0755, true);
        }
    }

    /**
     * Get overview statistics of the active database connection.
     */
    public function getDatabaseInfo(): array
    {
        $connection = DB::connection();
        $dbName = $connection->getDatabaseName();
        $pdo = $connection->getPdo();

        // Total tables count
        $tables = $this->getAllTables($pdo);
        $tableCount = count($tables);

        // Database size calculation
        $dbSizeMb = 0.0;
        try {
            $sizeQuery = "SELECT SUM(data_length + index_length) / 1024 / 1024 AS size_mb 
                          FROM information_schema.TABLES 
                          WHERE table_schema = :dbname";
            $stmt = $pdo->prepare($sizeQuery);
            $stmt->execute([':dbname' => $dbName]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $dbSizeMb = round((float) ($row['size_mb'] ?? 0), 2);
        } catch (Exception $e) {
            $dbSizeMb = 0.0;
        }

        // Check if mysqldump is available
        $hasMysqldump = $this->isMysqldumpAvailable();

        // Stored backup files
        $backups = $this->getStoredBackupsList();

        return [
            'database_name' => $dbName,
            'table_count' => $tableCount,
            'size_mb' => $dbSizeMb,
            'has_mysqldump' => $hasMysqldump,
            'php_version' => PHP_VERSION,
            'mysql_version' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
            'server_os' => PHP_OS_FAMILY,
            'stored_backups' => $backups,
            'total_backups_count' => count($backups),
        ];
    }

    /**
     * Generate complete .sql backup.
     *
     * @param string|null $destinationPath If provided, writes to this file path; otherwise generates default filename in storage.
     * @param bool $isSnapshot
     * @return array [ 'file_path' => string, 'file_name' => string, 'file_size' => int, 'method' => string ]
     */
    public function generateBackupSql(?string $destinationPath = null, bool $isSnapshot = false): array
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(600);

        $connection = DB::connection();
        $dbName = $connection->getDatabaseName();
        $timestamp = date('Y-m-d_H-i-s');

        if (empty($destinationPath)) {
            $prefix = $isSnapshot ? 'snapshot_before_restore' : 'backup';
            $folder = $isSnapshot ? $this->snapshotPath : $this->storagePath;
            $fileName = "{$prefix}_{$dbName}_{$timestamp}.sql";
            $destinationPath = $folder . DIRECTORY_SEPARATOR . $fileName;
        } else {
            $fileName = basename($destinationPath);
        }

        $method = 'mysqldump';
        $success = false;

        // Try mysqldump first
        if ($this->isMysqldumpAvailable()) {
            $success = $this->dumpWithMysqldump($destinationPath);
        }

        // Fallback to Native PHP PDO dumper if mysqldump was not available or failed
        if (!$success || !file_exists($destinationPath) || filesize($destinationPath) === 0) {
            $method = 'php_pdo_native';
            $this->dumpWithPdoNative($destinationPath);
        }

        $fileSize = file_exists($destinationPath) ? filesize($destinationPath) : 0;

        return [
            'file_path' => $destinationPath,
            'file_name' => $fileName,
            'file_size' => $fileSize,
            'file_size_human' => $this->humanFileSize($fileSize),
            'method' => $method,
            'generated_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * Restore database from a .sql file.
     *
     * @param string $filePath Absolute path to the .sql file
     * @param bool $createSafetySnapshot
     * @return array
     */
    public function restoreFromSqlFile(string $filePath, bool $createSafetySnapshot = true): array
    {
        if (!file_exists($filePath)) {
            throw new Exception("SQL backup file not found at: {$filePath}");
        }

        ini_set('memory_limit', '1024M');
        set_time_limit(900);

        $snapshotInfo = null;
        if ($createSafetySnapshot) {
            try {
                $snapshotInfo = $this->generateBackupSql(null, true);
            } catch (Exception $e) {
                Log::warning("Failed to create pre-restore snapshot: " . $e->getMessage());
            }
        }

        $startTime = microtime(true);
        $connection = DB::connection();
        $pdo = $connection->getPdo();

        // Disable foreign key checks, unique checks, strict mode for restore
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $pdo->exec("SET UNIQUE_CHECKS = 0;");
        $pdo->exec("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';");

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new Exception("Unable to open SQL file for reading: {$filePath}");
        }

        $executedQueries = 0;
        $currentQuery = '';
        $inComment = false;

        try {
            while (($line = fgets($handle)) !== false) {
                $trimmed = trim($line);

                // Skip blank lines
                if ($trimmed === '') {
                    continue;
                }

                // Handle single-line comments
                if (str_starts_with($trimmed, '--') || str_starts_with($trimmed, '#')) {
                    continue;
                }

                // Handle multi-line comments
                if (str_starts_with($trimmed, '/*')) {
                    if (!str_contains($trimmed, '*/')) {
                        $inComment = true;
                        continue;
                    } else {
                        // Check if it's conditional comments like /*!40101 ... */
                        if (preg_match('/^\/\*!\d+\s+(.*?)\s*\*\/;?$/', $trimmed, $matches)) {
                            $trimmed = $matches[1];
                        } else {
                            continue;
                        }
                    }
                }

                if ($inComment) {
                    if (str_contains($trimmed, '*/')) {
                        $inComment = false;
                    }
                    continue;
                }

                $currentQuery .= $line;

                // If end of statement found
                if (str_ends_with(rtrim($trimmed), ';')) {
                    $sqlToExecute = trim($currentQuery);
                    if ($sqlToExecute !== '') {
                        $pdo->exec($sqlToExecute);
                        $executedQueries++;
                    }
                    $currentQuery = '';
                }
            }

            // Execute any remaining query
            $trailingQuery = trim($currentQuery);
            if ($trailingQuery !== '') {
                $pdo->exec($trailingQuery);
                $executedQueries++;
            }
        } finally {
            fclose($handle);
            // Re-enable foreign key checks
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
            $pdo->exec("SET UNIQUE_CHECKS = 1;");
        }

        $duration = round(microtime(true) - $startTime, 2);

        return [
            'success' => true,
            'message' => 'Database restored successfully!',
            'executed_statements' => $executedQueries,
            'duration_seconds' => $duration,
            'snapshot' => $snapshotInfo,
            'database_name' => $connection->getDatabaseName(),
        ];
    }

    /**
     * Get list of all stored backup files and snapshots.
     */
    public function getStoredBackupsList(): array
    {
        $files = [];

        // Regular backups
        if (File::isDirectory($this->storagePath)) {
            foreach (File::files($this->storagePath) as $file) {
                if ($file->getExtension() === 'sql') {
                    $files[] = [
                        'file_name' => $file->getFilename(),
                        'file_size' => $file->getSize(),
                        'file_size_human' => $this->humanFileSize($file->getSize()),
                        'created_at' => date('Y-m-d H:i:s', $file->getMTime()),
                        'type' => 'manual',
                        'is_snapshot' => false,
                    ];
                }
            }
        }

        // Snapshots
        if (File::isDirectory($this->snapshotPath)) {
            foreach (File::files($this->snapshotPath) as $file) {
                if ($file->getExtension() === 'sql') {
                    $files[] = [
                        'file_name' => $file->getFilename(),
                        'file_size' => $file->getSize(),
                        'file_size_human' => $this->humanFileSize($file->getSize()),
                        'created_at' => date('Y-m-d H:i:s', $file->getMTime()),
                        'type' => 'snapshot',
                        'is_snapshot' => true,
                    ];
                }
            }
        }

        // Sort latest first
        usort($files, function ($a, $b) {
            return strcmp($b['created_at'], $a['created_at']);
        });

        return $files;
    }

    /**
     * Delete a stored backup file.
     */
    public function deleteBackupFile(string $fileName): bool
    {
        $safeName = basename($fileName);
        $path1 = $this->storagePath . DIRECTORY_SEPARATOR . $safeName;
        $path2 = $this->snapshotPath . DIRECTORY_SEPARATOR . $safeName;

        if (file_exists($path1)) {
            return unlink($path1);
        }

        if (file_exists($path2)) {
            return unlink($path2);
        }

        return false;
    }

    /**
     * Get the absolute path for a stored backup file name.
     */
    public function getBackupFilePath(string $fileName): ?string
    {
        $safeName = basename($fileName);
        $path1 = $this->storagePath . DIRECTORY_SEPARATOR . $safeName;
        $path2 = $this->snapshotPath . DIRECTORY_SEPARATOR . $safeName;

        if (file_exists($path1)) {
            return $path1;
        }

        if (file_exists($path2)) {
            return $path2;
        }

        return null;
    }

    /**
     * Dump using mysqldump CLI.
     */
    protected function dumpWithMysqldump(string $destinationPath): bool
    {
        try {
            $connection = DB::connection();
            $config = $connection->getConfig();

            $host = $config['host'] ?? '127.0.0.1';
            $port = $config['port'] ?? '3306';
            $database = $config['database'] ?? $connection->getDatabaseName();
            $username = $config['username'] ?? 'root';
            $password = $config['password'] ?? '';

            if ($host === 'localhost') {
                $host = '127.0.0.1';
            }

            $passPart = !empty($password) ? "--password=" . escapeshellarg($password) : "";

            $command = sprintf(
                'mysqldump --host=%s --port=%s --user=%s %s --default-character-set=utf8mb4 --single-transaction --routines --triggers --skip-lock-tables %s > %s 2>&1',
                escapeshellarg($host),
                escapeshellarg((string) $port),
                escapeshellarg($username),
                $passPart,
                escapeshellarg($database),
                escapeshellarg($destinationPath)
            );

            exec($command, $output, $returnVar);

            if ($returnVar === 0 && file_exists($destinationPath) && filesize($destinationPath) > 0) {
                return true;
            }

            return false;
        } catch (Exception $e) {
            Log::warning("mysqldump error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Pure PHP PDO streaming dumper (100% portable fallback).
     */
    protected function dumpWithPdoNative(string $destinationPath): void
    {
        $connection = DB::connection();
        $dbName = $connection->getDatabaseName();
        $pdo = $connection->getPdo();

        $fp = fopen($destinationPath, 'w');
        if (!$fp) {
            throw new Exception("Cannot create backup destination file: {$destinationPath}");
        }

        $timestamp = date('Y-m-d H:i:s');
        $phpVersion = PHP_VERSION;
        $serverVersion = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);

        // Header
        fwrite($fp, "-- =========================================================\n");
        fwrite($fp, "-- QPOS Database Backup Utility\n");
        fwrite($fp, "-- Database: `{$dbName}`\n");
        fwrite($fp, "-- Generation Time: {$timestamp}\n");
        fwrite($fp, "-- PHP Version: {$phpVersion} | MySQL Version: {$serverVersion}\n");
        fwrite($fp, "-- =========================================================\n\n");
        fwrite($fp, "SET FOREIGN_KEY_CHECKS = 0;\n");
        fwrite($fp, "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n");
        fwrite($fp, "SET time_zone = '+00:00';\n");
        fwrite($fp, "SET NAMES utf8mb4;\n\n");

        $tables = $this->getAllTables($pdo);

        foreach ($tables as $table) {
            // Drop table if exists
            fwrite($fp, "-- ---------------------------------------------------------\n");
            fwrite($fp, "-- Table structure for table `{$table}`\n");
            fwrite($fp, "-- ---------------------------------------------------------\n\n");
            fwrite($fp, "DROP TABLE IF EXISTS `{$table}`;\n");

            // Show create table
            $createTableStmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
            $createTableRow = $createTableStmt->fetch(PDO::FETCH_ASSOC);
            $createSql = $createTableRow['Create Table'] ?? ($createTableRow['Create View'] ?? null);

            if ($createSql) {
                fwrite($fp, $createSql . ";\n\n");
            }

            // Dump data in chunks
            fwrite($fp, "-- Dumping data for table `{$table}`\n\n");

            $countStmt = $pdo->query("SELECT COUNT(*) FROM `{$table}`");
            $totalRows = (int) $countStmt->fetchColumn();

            if ($totalRows > 0) {
                $chunkSize = 250;
                $offset = 0;

                while ($offset < $totalRows) {
                    $rowsStmt = $pdo->query("SELECT * FROM `{$table}` LIMIT {$chunkSize} OFFSET {$offset}");
                    $rows = $rowsStmt->fetchAll(PDO::FETCH_ASSOC);

                    if (empty($rows)) {
                        break;
                    }

                    $columns = array_keys($rows[0]);
                    $quotedColumns = array_map(fn($col) => "`{$col}`", $columns);
                    $columnList = implode(', ', $quotedColumns);

                    $insertPrefix = "INSERT INTO `{$table}` ({$columnList}) VALUES\n";
                    $valueChunks = [];

                    foreach ($rows as $row) {
                        $values = [];
                        foreach ($row as $val) {
                            if (is_null($val)) {
                                $values[] = 'NULL';
                            } elseif (is_numeric($val) && !is_string($val)) {
                                $values[] = $val;
                            } else {
                                $values[] = $pdo->quote($val);
                            }
                        }
                        $valueChunks[] = "  (" . implode(', ', $values) . ")";
                    }

                    fwrite($fp, $insertPrefix . implode(",\n", $valueChunks) . ";\n\n");
                    $offset += $chunkSize;
                }
            }
            fwrite($fp, "\n");
        }

        // Footer
        fwrite($fp, "SET FOREIGN_KEY_CHECKS = 1;\n");
        fwrite($fp, "-- Backup completed at " . date('Y-m-d H:i:s') . "\n");

        fclose($fp);
    }

    /**
     * Get all base tables for the current active database.
     */
    protected function getAllTables(PDO $pdo): array
    {
        $stmt = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
        $tables = [];
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        return $tables;
    }

    /**
     * Check if mysqldump CLI binary is executable.
     */
    protected function isMysqldumpAvailable(): bool
    {
        try {
            $checkCmd = (PHP_OS_FAMILY === 'Windows') ? 'where mysqldump 2>NUL' : 'which mysqldump 2>/dev/null';
            exec($checkCmd, $out, $ret);
            return $ret === 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Format bytes into human readable format.
     */
    protected function humanFileSize(int $bytes, int $decimals = 2): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }
        $sz = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = (int) floor((strlen((string) $bytes) - 1) / 3);
        return sprintf("%.{$decimals}f %s", $bytes / pow(1024, $factor), $sz[$factor] ?? 'B');
    }
}
