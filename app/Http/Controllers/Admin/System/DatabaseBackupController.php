<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Services\DatabaseBackupRestoreService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DatabaseBackupController extends Controller
{
    protected DatabaseBackupRestoreService $backupService;

    public function __construct(DatabaseBackupRestoreService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Render SPA view for database backup & restore.
     */
    public function index(Request $request)
    {
        return view('admin.layouts.admin_app');
    }

    /**
     * Get database stats and list of backups.
     */
    public function info(): JsonResponse
    {
        try {
            $data = $this->backupService->getDatabaseInfo();
            return response()->json($data);
        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to load database information: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate backup and download .sql file directly to user's browser.
     */
    public function download(Request $request): BinaryFileResponse|JsonResponse
    {
        try {
            $backup = $this->backupService->generateBackupSql();
            $filePath = $backup['file_path'];
            $fileName = $backup['file_name'];

            if (!file_exists($filePath)) {
                return response()->json(['error' => true, 'message' => 'Backup file generation failed.'], 500);
            }

            return response()->download($filePath, $fileName, [
                'Content-Type' => 'application/sql',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Backup generation error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate backup and save it in server storage.
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $backup = $this->backupService->generateBackupSql();

            return response()->json([
                'success' => true,
                'message' => 'Backup created and saved successfully!',
                'data' => $backup,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create backup: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download an existing backup file from server storage.
     */
    public function downloadfile(string $filename): BinaryFileResponse|JsonResponse
    {
        try {
            $filePath = $this->backupService->getBackupFilePath($filename);

            if (!$filePath || !file_exists($filePath)) {
                return response()->json(['error' => true, 'message' => 'Requested backup file does not exist.'], 404);
            }

            return response()->download($filePath, basename($filePath), [
                'Content-Type' => 'application/sql',
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a stored backup file from server.
     */
    public function deletefile(string $filename): JsonResponse
    {
        try {
            $deleted = $this->backupService->deleteBackupFile($filename);

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Backup file deleted successfully.',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Backup file could not be found or deleted.',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Restore database from uploaded .sql file or selected server file.
     */
    public function restore(Request $request): JsonResponse
    {
        $createSnapshot = filter_var($request->input('create_safety_snapshot', true), FILTER_VALIDATE_BOOLEAN);
        $tempPath = null;

        try {
            // Check if file is uploaded
            if ($request->hasFile('sql_file')) {
                $file = $request->file('sql_file');

                if (!$file->isValid()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Uploaded SQL file is invalid or corrupted.',
                    ], 422);
                }

                $extension = strtolower($file->getClientOriginalExtension());
                if ($extension !== 'sql') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please upload a valid .sql backup file.',
                    ], 422);
                }

                $tempDir = storage_path('app/backups/temp');
                if (!file_exists($tempDir)) {
                    mkdir($tempDir, 0755, true);
                }

                $tempFileName = 'upload_' . uniqid() . '.sql';
                $tempPath = $tempDir . DIRECTORY_SEPARATOR . $tempFileName;
                $file->move($tempDir, $tempFileName);
                $filePathToRestore = $tempPath;
            } elseif ($request->filled('file_name')) {
                // Restore from server stored file
                $fileName = $request->input('file_name');
                $filePathToRestore = $this->backupService->getBackupFilePath($fileName);

                if (!$filePathToRestore || !file_exists($filePathToRestore)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected server backup file not found.',
                    ], 404);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide a .sql file or select a backup file to restore.',
                ], 422);
            }

            // Run restore
            $result = $this->backupService->restoreFromSqlFile($filePathToRestore, $createSnapshot);

            // Clear application caches
            try {
                Artisan::call('cache:clear');
                Artisan::call('config:clear');
            } catch (Exception $e) {
                // Non-fatal
            }

            return response()->json([
                'success' => true,
                'message' => 'Database restored successfully!',
                'data' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database restore failed: ' . $e->getMessage(),
            ], 500);
        } finally {
            // Cleanup temp file if created
            if ($tempPath && file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }

    /* Aliases for backwards compatibility */
    public function downloadBackup(Request $request) { return $this->download($request); }
    public function createStoredBackup(Request $request) { return $this->create($request); }
    public function downloadStoredBackup(string $filename) { return $this->downloadfile($filename); }
    public function deleteStoredBackup(string $filename) { return $this->deletefile($filename); }
}
