<?php

namespace App\Http\Controllers;

use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Exception;

class BackupController extends Controller
{
    protected DatabaseBackupService $backups;

    public function __construct(DatabaseBackupService $backups)
    {
        $this->backups = $backups;
    }

    /** Every method in this controller is admin-only. */
    private function guardAdmin(): void
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can access Backup & Restore.');
        }
    }

    public function index()
    {
        $this->guardAdmin();

        return view('admin.backup', [
            'backups' => $this->backups->listBackups(),
            'tables'  => $this->backups->backupableTables(),
        ]);
    }

    public function createCsv(Request $request)
    {
        $this->guardAdmin();

        try {
            $filename = $this->backups->createCsvBackup();
            return back()->with('backup_success', "Backup created successfully: {$filename}");
        } catch (\Throwable $e) {
            return back()->with('backup_error', 'Failed to create backup: ' . $e->getMessage());
        }
    }

    public function createPdf(Request $request)
    {
        $this->guardAdmin();

        try {
            $filename = $this->backups->createPdfBackup();
            return back()->with('backup_success', "PDF export created successfully: {$filename}");
        } catch (\Throwable $e) {
            return back()->with('backup_error', 'Failed to create PDF export: ' . $e->getMessage());
        }
    }

    public function download(string $filename)
    {
        $this->guardAdmin();

        try {
            $path = $this->backups->diskPathFor($filename);
            return response()->download($path, basename($filename));
        } catch (\Throwable $e) {
            return back()->with('backup_error', 'Download failed: ' . $e->getMessage());
        }
    }

    public function destroy(string $filename)
    {
        $this->guardAdmin();

        try {
            $this->backups->deleteBackup($filename);
            return back()->with('backup_success', "Backup deleted: {$filename}");
        } catch (\Throwable $e) {
            return back()->with('backup_error', 'Delete failed: ' . $e->getMessage());
        }
    }

    /**
     * Restore from a backup file that's already stored on the server (from the list).
     */
    public function restore(Request $request, string $filename)
    {
        $this->guardAdmin();

        if ($request->input('confirm_text') !== 'RESTORE') {
            return back()->with('backup_error', 'Restore cancelled: confirmation text did not match.');
        }

        try {
            $path = $this->backups->diskPathFor($filename);
            $result = $this->backups->restoreFromZip($path);

            $msg = 'Restore complete. Tables restored: ' . implode(', ', $result['restored']) . '.';
            if (!empty($result['skipped'])) {
                $msg .= ' Skipped (not found or not backupable): ' . implode(', ', $result['skipped']) . '.';
            }
            return back()->with('backup_success', $msg);
        } catch (\Throwable $e) {
            return back()->with('backup_error', 'Restore failed — no partial changes were kept: ' . $e->getMessage());
        }
    }

    /**
     * Restore from a freshly uploaded ZIP backup (from the admin's local device),
     * without needing it to already exist in the server-side backup list.
     */
    public function uploadAndRestore(Request $request)
    {
        $this->guardAdmin();

        $request->validate([
            'restore_file' => ['required', 'file', 'mimes:zip'],
        ]);

        if ($request->input('confirm_text') !== 'RESTORE') {
            return back()->with('backup_error', 'Restore cancelled: confirmation text did not match.');
        }

        try {
            $absolutePath = $request->file('restore_file')->getRealPath();
            $result = $this->backups->restoreFromZip($absolutePath);

            $msg = 'Restore complete. Tables restored: ' . implode(', ', $result['restored']) . '.';
            if (!empty($result['skipped'])) {
                $msg .= ' Skipped (not found or not backupable): ' . implode(', ', $result['skipped']) . '.';
            }
            return back()->with('backup_success', $msg);
        } catch (\Throwable $e) {
            return back()->with('backup_error', 'Restore failed — no partial changes were kept: ' . $e->getMessage());
        }
    }
}