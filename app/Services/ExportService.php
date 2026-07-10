<?php

namespace App\Services;

use App\Models\CommissionRequest;
use App\Models\DepartmentalExpense;
use App\Models\Expense;
use App\Models\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Carbon;

class ExportService
{
    /**
     * Registry of every module supported by the Export Records feature.
     * - model:      the Eloquent model to query
     * - dateColumn: column used for the Start/End date range filter
     * - label:      human-readable module name shown in the UI
     * - columns:    [export header => resolver], resolver receives the record
     */
    public static function modules(): array
    {
        return [
            'commission-monitoring' => [
                'label'      => 'Commission Monitoring',
                'model'      => CommissionRequest::class,
                'dateColumn' => 'date_requested',
                'columns'    => [
                    'Client Name'          => fn ($r) => $r->client_name,
                    'Project Name'         => fn ($r) => $r->project_name,
                    'Property Details'     => fn ($r) => $r->property_details,
                    'Agent Name'           => fn ($r) => $r->agent_name,
                    'Net TCP'              => fn ($r) => $r->net_tcp,
                    'Commission %'         => fn ($r) => $r->commission_percent,
                    'Commission'           => fn ($r) => $r->commission,
                    'Terms of Payment'     => fn ($r) => $r->terms_of_payment,
                    'Mode of Payment'      => fn ($r) => $r->mode_of_payment,
                    'Date Requested'       => fn ($r) => optional($r->date_requested)->format('Y-m-d'),
                    'Date Released'        => fn ($r) => optional($r->date_released)->format('Y-m-d'),
                    'Status'               => fn ($r) => $r->status,
                    'Remarks'              => fn ($r) => $r->remarks,
                ],
            ],
            'departmental-expenses' => [
                'label'      => 'Departmental Expenses (Budget Requests)',
                'model'      => DepartmentalExpense::class,
                'dateColumn' => 'date_requested',
                'columns'    => [
                    'Control Number'          => fn ($r) => $r->control_number,
                    'Requestor Name'          => fn ($r) => $r->requestor_name,
                    'Department'              => fn ($r) => $r->department,
                    'Category'                => fn ($r) => $r->category,
                    'Requested Amount'        => fn ($r) => $r->requested_amount,
                    'Total Expenses'          => fn ($r) => $r->total_expenses,
                    'Amount Returned'         => fn ($r) => $r->amount_returned,
                    'Date Requested'          => fn ($r) => optional($r->date_requested)->format('Y-m-d'),
                    'Date Released'           => fn ($r) => optional($r->date_released)->format('Y-m-d'),
                    'Status'                  => fn ($r) => $r->status,
                ],
            ],
            'finance' => [
                'label'      => 'Finance (Department Expense Categories)',
                'model'      => Expense::class,
                'dateColumn' => 'expense_date',
                'scope'      => fn ($q) => $q->whereHas('department', fn ($d) => $d->where('name', 'FINANCE')),
                'columns'    => [
                    'Department'    => fn ($r) => optional($r->department)->name,
                    'Expense Date'  => fn ($r) => optional($r->expense_date)->format('Y-m-d'),
                    'Categories'    => fn ($r) => collect($r->categories_data ?? [])
                        ->map(fn ($c) => ($c['name'] ?? 'Category') . ': ' . ($c['amount'] ?? 0))
                        ->implode('; '),
                    'Total Amount'  => fn ($r) => $r->total_amount,
                ],
            ],
            'user-management' => [
                'label'      => 'User Management',
                'model'      => User::class,
                'dateColumn' => 'created_at',
                'columns'    => [
                    'Name'       => fn ($r) => $r->name,
                    'Email'      => fn ($r) => $r->email,
                    'Role'       => fn ($r) => $r->role,
                    'Position'   => fn ($r) => $r->position,
                    'Team'       => fn ($r) => $r->team_name,
                    'Status'     => fn ($r) => $r->status,
                    'Date Hired' => fn ($r) => $r->date_hired ? Carbon::parse($r->date_hired)->format('Y-m-d') : null,
                    'Created At' => fn ($r) => optional($r->created_at)->format('Y-m-d H:i'),
                ],
            ],
        ];
    }

    public static function moduleOptions(): array
    {
        $out = [];
        foreach (self::modules() as $key => $m) {
            $out[$key] = $m['label'];
        }
        return $out;
    }

    /**
     * Builds the filtered record collection for a module.
     */
    protected function getRecords(string $moduleKey, ?string $startDate, ?string $endDate)
    {
        $module = self::modules()[$moduleKey] ?? null;
        if (!$module) throw new \InvalidArgumentException('Unknown export module.');

        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = $module['model']::query();

        if (isset($module['scope'])) {
            $module['scope']($query);
        }

        if ($startDate) {
            $query->whereDate($module['dateColumn'], '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate($module['dateColumn'], '<=', $endDate);
        }

        return $query->orderBy($module['dateColumn'])->get();
    }

    /**
     * Streams a CSV download for the given module/date-range.
     */
    public function exportCsv(string $moduleKey, ?string $startDate, ?string $endDate)
    {
        $module  = self::modules()[$moduleKey];
        $records = $this->getRecords($moduleKey, $startDate, $endDate);
        $headers = array_keys($module['columns']);

        $filename = $this->buildFilename($moduleKey, 'csv');

        return response()->streamDownload(function () use ($records, $module, $headers) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($records as $record) {
                $row = [];
                foreach ($module['columns'] as $resolver) {
                    $row[] = $resolver($record);
                }
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Renders and returns a PDF download for the given module/date-range.
     */
    public function exportPdf(string $moduleKey, ?string $startDate, ?string $endDate)
    {
        $module  = self::modules()[$moduleKey];
        $records = $this->getRecords($moduleKey, $startDate, $endDate);
        $headers = array_keys($module['columns']);

        $rows = $records->map(function ($record) use ($module) {
            $row = [];
            foreach ($module['columns'] as $resolver) {
                $row[] = $resolver($record);
            }
            return $row;
        });

        $rangeLabel = $startDate || $endDate
            ? ($startDate ?: 'earliest') . ' to ' . ($endDate ?: 'latest')
            : 'All Records';

        $html = view('admin.export-pdf', [
            'moduleLabel' => $module['label'],
            'rangeLabel'  => $rangeLabel,
            'headers'     => $headers,
            'rows'        => $rows,
            'generatedAt' => now()->format('F j, Y g:i A'),
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', count($headers) > 6 ? 'landscape' : 'portrait');
        $dompdf->render();

        $filename = $this->buildFilename($moduleKey, 'pdf');

        return response($dompdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    protected function buildFilename(string $moduleKey, string $ext): string
    {
        return $moduleKey . '-export-' . now()->format('Y-m-d_His') . '.' . $ext;
    }
}