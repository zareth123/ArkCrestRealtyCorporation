<?php

namespace App\Http\Controllers;

use App\Services\ExportService;
use Illuminate\Http\Request;

class AdminExportController extends Controller
{
    protected ExportService $exporter;

    public function __construct(ExportService $exporter)
    {
        $this->exporter = $exporter;
    }

    private function guardAdmin(): void
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can export records.');
        }
    }

    public function index()
    {
        $this->guardAdmin();

        return view('admin.export', [
            'modules' => ExportService::moduleOptions(),
        ]);
    }

    public function download(Request $request)
    {
        $this->guardAdmin();

        $validated = $request->validate([
            'module'     => 'required|string|in:' . implode(',', array_keys(ExportService::moduleOptions())),
            'format'     => 'required|in:csv,pdf',
            'range_type' => 'required|in:all,range',
            'start_date' => 'nullable|date|required_if:range_type,range',
            'end_date'   => 'nullable|date|required_if:range_type,range|after_or_equal:start_date',
        ]);

        $startDate = $validated['range_type'] === 'range' ? $validated['start_date'] : null;
        $endDate   = $validated['range_type'] === 'range' ? $validated['end_date'] : null;

        return $validated['format'] === 'csv'
            ? $this->exporter->exportCsv($validated['module'], $startDate, $endDate)
            : $this->exporter->exportPdf($validated['module'], $startDate, $endDate);
    }
}