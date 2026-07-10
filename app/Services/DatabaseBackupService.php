<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Dompdf\Dompdf;
use Dompdf\Options;
use ZipArchive;
use Exception;

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

            $backups[] = [
                'filename'   => $filename,
                'type'       => str_ends_with(strtolower($filename), '.pdf') ? 'pdf' : 'zip',
                'size_bytes' => $disk->size($path),
                'size_human' => $this->humanFileSize($disk->size($path)),
                'created_at' => \Illuminate\Support\Carbon::createFromTimestamp($disk->lastModified($path)),
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
     |  CSV backup, bundled in a ZIP (restorable)
     * ------------------------------------------------------------------ */

    /**
     * Export every backupable table into its own .csv file, bundled together
     * into a single .zip archive. Returns the generated filename.
     */
    public function createCsvBackup(): string
    {
        $tables = $this->backupableTables();

        $filename = 'backup_' . now()->format('Y-m-d_His') . '.zip';
        $disk = Storage::disk(self::DISK);
        if (!$disk->exists(self::FOLDER)) {
            $disk->makeDirectory(self::FOLDER);
        }
        $fullPath = $disk->path(self::FOLDER . '/' . $filename);

        $zip = new ZipArchive();
        if ($zip->open($fullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception('Could not create backup archive.');
        }

        // ZipArchive::addFile only reads the source file when close() is called,
        // so every temp file must stay on disk until after close().
        $tmpFiles = [];

        try {
            foreach ($tables as $table) {
                $columns = Schema::getColumnListing($table);
                if (empty($columns)) continue;

                $tmpFile = tempnam(sys_get_temp_dir(), 'csvexport_');
                $tmpFiles[] = $tmpFile;

                $handle = fopen($tmpFile, 'w');
                fputcsv($handle, $columns);

                DB::table($table)->orderBy($columns[0])->chunk(self::CHUNK_SIZE, function ($rows) use ($handle, $columns) {
                    foreach ($rows as $row) {
                        $rowArray = (array) $row;
                        $line = [];
                        foreach ($columns as $colName) {
                            $line[] = $rowArray[$colName] ?? '';
                        }
                        fputcsv($handle, $line);
                    }
                });

                fclose($handle);
                $zip->addFile($tmpFile, $table . '.csv');
            }

            if ($zip->numFiles === 0) {
                // Shouldn't happen, but keep the archive from being invalid/empty.
                $zip->addFromString('empty.txt', 'No backupable tables were found.');
            }

            $zip->close();
        } finally {
            foreach ($tmpFiles as $f) {
                if (is_file($f)) @unlink($f);
            }
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
        $tables = $this->backupableTables();

        $html = '<style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color:#111; }
            h1 { font-size: 16px; color:#1e4575; margin-bottom:2px; }
            .meta { font-size: 10px; color:#6b7280; margin-bottom:18px; }
            h2 { font-size: 12px; color:#1e4575; background:#eef2f7; padding:4px 8px; margin-top:22px; page-break-inside: avoid; }
            table { border-collapse: collapse; width:100%; margin-bottom:6px; }
            th, td { border:1px solid #ccc; padding:3px 5px; text-align:left; word-break:break-all; }
            th { background:#f3f4f6; font-weight:bold; }
            .empty-note { color:#9ca3af; font-style:italic; padding:4px 0; }
        </style>';

        $html .= '<h1>ArkCrest Realty Corporation — System Data Export</h1>';
        $html .= '<div class="meta">Generated: ' . now()->format('F d, Y g:i A') . ' &middot; View-only export (not used for restore)</div>';

        foreach ($tables as $table) {
            $columns = Schema::getColumnListing($table);
            if (empty($columns)) continue;

            $html .= '<h2>' . e($table) . '</h2>';

            $rows = DB::table($table)->orderBy($columns[0])->limit(1000)->get();

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
                    $value = is_string($value) ? mb_strimwidth($value, 0, 200, '…') : $value;
                    $html .= '<td>' . e((string) $value) . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';

            if (DB::table($table)->count() > 1000) {
                $html .= '<div class="empty-note">Showing first 1000 rows only. Use the CSV backup for a complete export.</div>';
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
     * Restore the database from a previously created (or freshly uploaded) CSV/ZIP backup.
     *
     * For every .csv file in the zip whose name (minus extension) matches a
     * real, currently-existing table:
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
    public function restoreFromZip(string $absoluteZipPath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($absoluteZipPath) !== true) {
            throw new Exception('Could not open the backup archive. Make sure it is a valid .zip file created by this Backup & Restore feature.');
        }

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

                $tableName = pathinfo($entryName, PATHINFO_FILENAME);

                if (!Schema::hasTable($tableName) || in_array($tableName, self::EXCLUDED_TABLES, true)) {
                    $skipped[] = $tableName;
                    continue;
                }

                $content = $zip->getFromName($entryName);
                if ($content === false) {
                    $skipped[] = $tableName . ' (unreadable)';
                    continue;
                }

                // Write to a temp file and read with fgetcsv so quoted fields
                // containing commas/newlines (e.g. remarks) are parsed correctly.
                $tmpFile = tempnam(sys_get_temp_dir(), 'csvimport_');
                file_put_contents($tmpFile, $content);
                $handle = fopen($tmpFile, 'r');

                $header = fgetcsv($handle);
                if ($header === false) {
                    fclose($handle);
                    @unlink($tmpFile);
                    $skipped[] = $tableName . ' (empty)';
                    continue;
                }

                $tableColumns = Schema::getColumnListing($tableName);

                DB::table($tableName)->truncate();

                $batch = [];
                while (($values = fgetcsv($handle)) !== false) {
                    $record = [];
                    foreach ($header as $idx => $colName) {
                        if ($colName === '' || !in_array($colName, $tableColumns, true)) continue;
                        $value = $values[$idx] ?? null;
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
                @unlink($tmpFile);

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