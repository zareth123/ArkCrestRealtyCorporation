<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Expense;
use App\Models\DepartmentalExpense;
use App\Models\ActivityLog;

class FormsController extends Controller
{
    /**
     * Find the next available ARCS control number for the given month/year
     * by scanning the departmental_expenses table (including soft-deleted
     * rows so numbers are never reused). Shared by the preview endpoint and
     * the actual submit endpoint so the number shown on-screen always
     * matches the number that gets saved.
     */
    private function nextAvailableControlNumber(string $month, string $year): string
    {
        $count = 1;
        while (DepartmentalExpense::withTrashed()->where('control_number', sprintf('ARCS-%s-%03d-%s', $month, $count, $year))->exists()) {
            $count++;
        }
        return sprintf('ARCS-%s-%03d-%s', $month, $count, $year);
    }
    public function index()
    {
        $departments = Department::with('categories')->get();
        $requestorNames = \App\Models\CommissionRequest::whereNotNull('requestor_name')
            ->where('requestor_name', '!=', '')
            ->distinct()
            ->orderBy('requestor_name')
            ->pluck('requestor_name')
            ->toArray();

        // Data needed for the Site Visit Form tab
        try {
            $teams = \App\Models\SalesTeam::orderBy('team_name')->pluck('team_name');
        } catch (\Exception $e) {
            $teams = collect();
        }
        try {
            $properties = \Schema::hasTable('properties') ? \App\Models\Property::orderBy('name')->get() : collect();
        } catch (\Exception $e) {
            $properties = collect();
        }

        return view('forms', compact('departments', 'requestorNames', 'teams', 'properties'));
    }

    public function siteVisit()
    {
        // Site Visit Form now lives as a tab inside the main Forms page
        return redirect()->route('forms', ['tab' => 'site-visit']);
    }

    public function nextControlNumber(Request $request)
    {
        $month = now()->format('m');
        $year  = now()->format('y');

        // Preview only — this scans the same table/logic used at submit
        // time, so the number shown on the form matches what actually gets
        // saved (barring another submission landing in between).
        $controlNumber = $this->nextAvailableControlNumber($month, $year);

        return response()->json(['control_number' => $controlNumber]);
    }

    public function incrementControlNumber(Request $request)
    {
        $key = 'ctrl_num_' . now()->format('Y_m');
        $current = (int)(\DB::table('app_settings')->where('key', $key)->value('value') ?? 0);
        \DB::table('app_settings')->updateOrInsert(
            ['key' => $key],
            ['value' => $current + 1, 'created_at' => now(), 'updated_at' => now()]
        );
        return response()->json(['ok' => true, 'next' => $current + 1]);
    }

    /**
     * Called when the Budget Request Form's "Print and Submit" button is
     * used. Saves the form as a new row in the "All Expenses" table
     * (departmental_expenses) with a freshly-assigned, unique control
     * number, then returns that control number so the front-end can show
     * it on the form before printing.
     */
    public function submitBudgetRequest(Request $request)
    {
        try {
            $validated = $request->validate([
                'requestor_name'   => 'required|string',
                'department'       => 'required|string',
                'category'         => 'required|string',
                'date_requested'   => 'nullable|date',
                'requested_amount' => 'nullable|numeric|min:0',
                'form_snapshot'    => 'nullable|array',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->validator->errors()->first()], 422);
        }

        if (empty($validated['date_requested'])) {
            $validated['date_requested'] = null;
        }

        $month = now()->format('m');
        $year  = now()->format('y');

        $departmentalExpense = null;
        $controlNumber = null;

        \DB::transaction(function () use (&$departmentalExpense, &$controlNumber, $validated, $request, $month, $year) {
            $controlNumber = $this->nextAvailableControlNumber($month, $year);

            $departmentalExpense = DepartmentalExpense::create([
                'control_number'   => $controlNumber,
                'requestor_name'   => $validated['requestor_name'],
                'department'       => $validated['department'],
                'category'         => $validated['category'],
                'date_requested'   => $validated['date_requested'],
                'requested_amount' => $validated['requested_amount'] ?? null,

                // A freshly submitted Budget Request Form always lands in
                // the All Expenses table as "PENDING". The release /
                // liquidation fields stay blank here — Finance fills them
                // in later (via Edit) once the request is actually
                // released and liquidated.
                'status'                  => 'PENDING',
                'date_released'           => null,
                'total_expenses'          => null,
                'amount_returned'         => null,
                'date_of_amount_returned' => null,
            ]);

            // Full snapshot of everything printed on the form (target
            // date, liquidation line items, signatures, remarks, etc.), so
            // the exact form can be viewed and printed again later. Stored
            // in the existing app_settings key/value table (keyed by this
            // expense's id) rather than a new column, so no migration is
            // needed for this feature.
            $snapshot = $request->input('form_snapshot');
            if (!empty($snapshot)) {
                \DB::table('app_settings')->updateOrInsert(
                    ['key' => 'budget_form_snapshot_' . $departmentalExpense->id],
                    ['value' => json_encode($snapshot), 'created_at' => now(), 'updated_at' => now()]
                );
            }
        });

        ActivityLog::log(
            'create',
            'Departmental Expenses',
            "Budget Request Form submitted: '{$validated['category']}' for {$validated['department']} by {$validated['requestor_name']} (₱" . number_format($validated['requested_amount'] ?? 0, 2) . ") [{$controlNumber}]"
        );

        return response()->json([
            'success'        => true,
            'control_number' => $controlNumber,
            'data'           => $departmentalExpense,
        ]);
    }
}