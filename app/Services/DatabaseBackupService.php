<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;
use ZipArchive;

class DatabaseBackupService
{
    /**
     * Tables that are transient/framework-internal and should never be
     * included in a backup or touched during a restore.
     */
    protected const EXCLUDED_TABLES = [
        'migrations',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
        'sessions',
        'password_reset_tokens',
    ];

    /** Storage disk (private — never publicly accessible) and folder used for backups. */
    protected const DISK = 'local';
    protected const FOLDER = 'backups';

    /** Max rows fetched per chunk while reading/writing, to keep memory usage low. */
    protected const CHUNK_SIZE = 500;

    /* ------------------------------------------------------------------ *
     |  Table discovery
     * ------------------------------------------------------------------ */

    /**
     * Get the list of table names to include in a backup, sorted alphabetically.
     */
    public function backupableTables(): array
    {
        $tables = [];
        foreach (Schema::getTables() as $table) {
            // Schema::getTables() returns an array of assoc arrays with a 'name' key (Laravel 11+).
            $name = is_array($table) ? ($table['name'] ?? null) : $table;
            if (!$name) continue;
            if (in_array($name, self::EXCLUDED_TABLES, true)) continue;
            $tables[] = $name;
        }
        sort($tables);
        return $tables;
    }

    /* ------------------------------------------------------------------ *
     |  Listing / housekeeping
     * ------------------------------------------------------------------ */

    /**
     * List all backup files currently on disk, most recent first.
     */
    public function listBackups(): array
    {
        $disk = Storage::disk(self::DISK);
        if (!$disk->exists(self::FOLDER)) {
            $disk->makeDirectory(self::FOLDER);
        }

        $files = $disk->files(self::FOLDER);
        $backups = [];

        foreach ($files as $path) {
            $filename = basename($path);
            if (!preg_match('/\.(zip|pdf)$/i', $filename)) continue;

            // Prefer the timestamp encoded in the filename itself
            // (backup_YYYY-MM-DD_HHiiss.ext) over the filesystem's "last
            // modified" time — mtime can drift if the file is ever copied,
            // moved, or touched by backup tools, git, etc., while the
            // filename always reflects the exact moment it was generated.
            if (preg_match('/^backup_(\d{4}-\d{2}-\d{2}_\d{6})\./', $filename, $m)) {
                $createdAt = \Illuminate\Support\Carbon::createFromFormat('Y-m-d_His', $m[1]);
            } else {
                $createdAt = \Illuminate\Support\Carbon::createFromTimestamp($disk->lastModified($path));
            }

            $backups[] = [
                'filename'   => $filename,
                'type'       => str_ends_with(strtolower($filename), '.pdf') ? 'pdf' : 'csv',
                'size_bytes' => $disk->size($path),
                'size_human' => $this->humanFileSize($disk->size($path)),
                'created_at' => $createdAt,
            ];
        }

        usort($backups, fn($a, $b) => $b['created_at'] <=> $a['created_at']);

        return $backups;
    }

    /**
     * Resolve a backup filename to a safe, validated path on disk.
     * Throws if the filename is invalid or the file does not exist, preventing
     * path traversal (e.g. "../../.env") from ever reaching the filesystem call.
     */
    public function resolveSafePath(string $filename): string
    {
        $filename = basename($filename); // strip any directory components
        if (!preg_match('/^backup_[0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{6}\.(zip|pdf)$/', $filename)) {
            throw new Exception('Invalid backup filename.');
        }

        $path = self::FOLDER . '/' . $filename;
        if (!Storage::disk(self::DISK)->exists($path)) {
            throw new Exception('Backup file not found.');
        }

        return $path;
    }

    public function deleteBackup(string $filename): void
    {
        $path = $this->resolveSafePath($filename);
        Storage::disk(self::DISK)->delete($path);
    }

    public function diskPathFor(string $filename): string
    {
        $path = $this->resolveSafePath($filename);
        return Storage::disk(self::DISK)->path($path);
    }

    /* ------------------------------------------------------------------ *
     |  CSV backup (restorable) — one CSV per table, zipped together.
     |  Uses only PHP's built-in fputcsv/ZipArchive, no Composer package
     |  required, so it works on every machine that can run PHP at all.
     * ------------------------------------------------------------------ */

    /**
     * Export every backupable table into its own .csv file, all bundled
     * into a single .zip archive. Returns the generated filename.
     */
    public function createCsvBackup(): string
    {
        if (!class_exists(ZipArchive::class)) {
            throw new Exception('The PHP "zip" extension is not enabled on this server. Please enable ext-zip in php.ini to create backups.');
        }

        $tables = $this->backupableTables();

        $filename = 'backup_' . now()->format('Y-m-d_His') . '.zip';
        $disk = Storage::disk(self::DISK);
        if (!$disk->exists(self::FOLDER)) {
            $disk->makeDirectory(self::FOLDER);
        }
        $fullPath = $disk->path(self::FOLDER . '/' . $filename);

        $zip = new ZipArchive();
        if ($zip->open($fullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception('Could not create the backup zip file.');
        }

        $tmpDir = sys_get_temp_dir() . '/arkcrest_backup_' . uniqid();
        mkdir($tmpDir, 0777, true);

        try {
            foreach ($tables as $table) {
                $columns = Schema::getColumnListing($table);
                if (empty($columns)) continue;

                $tmpFile = $tmpDir . '/' . $table . '.csv';
                $handle = fopen($tmpFile, 'w');

                // Header row
                fputcsv($handle, $columns);

                // Data rows, chunked to keep memory usage sane
                DB::table($table)->orderBy($columns[0])->chunk(self::CHUNK_SIZE, function ($rows) use ($handle, $columns) {
                    foreach ($rows as $row) {
                        $rowArray = (array) $row;
                        $line = [];
                        foreach ($columns as $colName) {
                            $value = $rowArray[$colName] ?? null;
                            $line[] = $value === null ? '' : (string) $value;
                        }
                        fputcsv($handle, $line);
                    }
                });

                fclose($handle);
                $zip->addFile($tmpFile, $table . '.csv');
            }

            $zip->close();
        } finally {
            // Clean up temp CSVs regardless of success/failure
            foreach (glob($tmpDir . '/*.csv') as $f) {
                @unlink($f);
            }
            @rmdir($tmpDir);
        }

        return $filename;
    }

    /* ------------------------------------------------------------------ *
     |  PDF export (view-only, NOT used for restore)
     * ------------------------------------------------------------------ */

    /**
     * Export every backupable table into a single readable PDF document.
     * This is intended for viewing/printing/archival only — restoring the
     * system from a PDF is not supported.
     */
    public function createPdfBackup(): string
    {
        // Rendering many tables/rows through Dompdf is memory-hungry (it builds
        // a full layout tree per cell). Bump the limit just for this request
        // as a safety net — this only affects this one PHP process, not the
        // server-wide setting, and silently no-ops if the host disallows it.
        ini_set('memory_limit', '512M');

        // This export is for quick viewing/printing, not a full data dump
        // (that's what the CSV backup is for) — capping rows per table keeps
        // both the memory usage and the resulting PDF's size reasonable.
        $rowLimit = 1000000;

        $tables = $this->backupableTables();

        $html = '<style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color:#111; }
            h1 { font-size: 16px; color:#1e4575; margin-bottom:2px; }
            .meta { font-size: 10px; color:#6b7280; margin-bottom:18px; }
            h2 { font-size: 12px; color:#1e4575; background:#eef2f7; padding:4px 8px; margin-top:22px; page-break-inside: avoid; }
            table { border-collapse: collapse; width:100%; margin-bottom:6px; table-layout: fixed; }
            th, td { border:1px solid #ccc; padding:3px 4px; text-align:left; word-break:break-all; overflow-wrap:break-word; font-size: 7.5px; }
            th { background:#f3f4f6; font-weight:bold; }
            .empty-note { color:#9ca3af; font-style:italic; padding:4px 0; }
        </style>';

        $html .= '<h1>ArkCrest Realty Corporation — System Data Export</h1>';
        $html .= '<div class="meta">Generated: ' . now()->format('F d, Y g:i A') . ' &middot; View-only export (not used for restore)</div>';

        foreach ($tables as $table) {
            $columns = Schema::getColumnListing($table);
            if (empty($columns)) continue;

            $html .= '<h2>' . e($table) . '</h2>';

            $rows = DB::table($table)->orderBy($columns[0])->limit($rowLimit)->get();

            if ($rows->isEmpty()) {
                $html .= '<div class="empty-note">No records.</div>';
                continue;
            }

            $html .= '<table><thead><tr>';
            foreach ($columns as $col) {
                $html .= '<th>' . e($col) . '</th>';
            }
            $html .= '</tr></thead><tbody>';

            foreach ($rows as $row) {
                $html .= '<tr>';
                foreach ($columns as $col) {
                    $value = $row->{$col} ?? '';
                    $value = is_string($value) ? mb_strimwidth($value, 0, 80, '…') : $value;
                    $html .= '<td>' . e((string) $value) . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';

            if (DB::table($table)->count() > $rowLimit) {
                $html .= '<div class="empty-note">Showing first ' . $rowLimit . ' rows only. Use the CSV backup for a complete export.</div>';
            }
        }

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'backup_' . now()->format('Y-m-d_His') . '.pdf';
        $disk = Storage::disk(self::DISK);
        if (!$disk->exists(self::FOLDER)) {
            $disk->makeDirectory(self::FOLDER);
        }
        $disk->put(self::FOLDER . '/' . $filename, $dompdf->output());

        return $filename;
    }

    /* ------------------------------------------------------------------ *
     |  Restore (DESTRUCTIVE)
     * ------------------------------------------------------------------ */

    /**
     * Restore the database from a previously created (or freshly uploaded) CSV backup
     * (a .zip archive containing one .csv file per table).
     *
     * For every .csv entry whose name (minus extension) matches a real,
     * currently-existing table:
     *   - the table is truncated
     *   - rows from the CSV are re-inserted, matched by column header name
     *     against the table's CURRENT columns (columns in the file that no
     *     longer exist on the table are ignored; columns on the table that
     *     aren't in the file are left null/default).
     *
     * Entries that don't correspond to a real table are skipped and reported
     * back as warnings. Everything runs inside a single DB transaction so a
     * failure partway through rolls back cleanly.
     *
     * @return array{restored: string[], skipped: string[]}
     */
    public function restoreFromCsv(string $absoluteZipPath): array
    {
        if (!class_exists(ZipArchive::class)) {
            throw new Exception('The PHP "zip" extension is not enabled on this server. Please enable ext-zip in php.ini to restore backups.');
        }

        $zip = new ZipArchive();
        if ($zip->open($absoluteZipPath) !== true) {
            throw new Exception('Could not open the backup file — it may not be a valid .zip archive.');
        }

        $tmpDir = sys_get_temp_dir() . '/arkcrest_restore_' . uniqid();
        mkdir($tmpDir, 0777, true);

        $restored = [];
        $skipped = [];

        $driver = DB::connection()->getDriverName();

        DB::beginTransaction();
        try {
            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
            } elseif ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF');
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entryName = $zip->getNameIndex($i);
                if (!str_ends_with(strtolower($entryName), '.csv')) continue;

                $tableName = basename($entryName, '.csv');

                if (!Schema::hasTable($tableName)) {
                    $skipped[] = $tableName;
                    continue;
                }
                if (in_array($tableName, self::EXCLUDED_TABLES, true)) {
                    $skipped[] = $tableName;
                    continue;
                }

                $extractedPath = $tmpDir . '/' . basename($entryName);
                file_put_contents($extractedPath, $zip->getFromIndex($i));

                $handle = fopen($extractedPath, 'r');
                $fileColumns = fgetcsv($handle);

                if ($fileColumns === false || empty($fileColumns)) {
                    fclose($handle);
                    $skipped[] = $tableName . ' (empty)';
                    continue;
                }

                $tableColumns = Schema::getColumnListing($tableName);

                DB::table($tableName)->truncate();

                $batch = [];
                while (($row = fgetcsv($handle)) !== false) {
                    $record = [];
                    foreach ($fileColumns as $idx => $colName) {
                        if ($colName === '' || !in_array($colName, $tableColumns, true)) continue;
                        $value = $row[$idx] ?? null;
                        $record[$colName] = ($value === null || $value === '') ? null : $value;
                    }
                    if (!empty($record)) {
                        $batch[] = $record;
                    }

                    if (count($batch) >= self::CHUNK_SIZE) {
                        DB::table($tableName)->insert($batch);
                        $batch = [];
                    }
                }
                if (!empty($batch)) {
                    DB::table($tableName)->insert($batch);
                }

                fclose($handle);
                $restored[] = $tableName;
            }

            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } elseif ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON');
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        } finally {
            $zip->close();
            foreach (glob($tmpDir . '/*.csv') as $f) {
                @unlink($f);
            }
            @rmdir($tmpDir);
        }

        return ['restored' => $restored, 'skipped' => $skipped];
    }

    /* ------------------------------------------------------------------ *
     |  Helpers
     * ------------------------------------------------------------------ */

    protected function humanFileSize(int $bytes): string
    {
        if ($bytes < 1024) return $bytes . ' B';
        $units = ['KB', 'MB', 'GB'];
        $value = $bytes;
        foreach ($units as $unit) {
            $value /= 1024;
            if ($value < 1024) return round($value, 2) . ' ' . $unit;
        }
        return round($value, 2) . ' TB';
    }
}