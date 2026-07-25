<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DepartmentalExpense;
use App\Models\ActivityLog;
use Carbon\Carbon;

class DepartmentalExpensesController extends Controller
{
    private $categories = [
        'Admin' => [
            'Pantry Supplies',
            'Office Rental',
            'Utilities',
            'Office Supplies and Equipments',
            'Maintenance and Repairs',
            'Transportation',
            'Food/ Meals',
            'Medical Supplies',
            'Cleaning / Janitorial Supplies',
            'Miscellaneous'
        ],
        'Sales & Marketing' => [
            'Advertisement Cost',
            'Sales Incentives',
            'Agent Allowances',
            'Transportation',
            'Food/ Meals',
            'Sales Miscellaneous'
        ],
        'HR' => [
            'Office Staff Allowances',
            'Recruitment and Hiring',
            'Licenses and Permits',
            'Transportation',
            'Events/ Program',
            'Miscellaneous'
        ],
        'Finance' => [
            'Retention Fees',
            'Penalty/ Fines',
            'Tax and Licenses',
            'Miscellaneous'
        ],
        'Executive' => [
            'Food/ Meals',
            'Transportation',
            'Repairs and Maintenance',
            'Miscellaneous'
        ]
    ];

    /**
     * How much of a department's allowable budget is already committed:
     *  - "liquidated" => sum of total_expenses for LIQUIDATED records
     *                    (money actually spent — reduces remaining budget)
     *  - "remaining"  => allowable_budget - liquidated
     * $excludeId lets an update() call check against the budget as if its
     * own (pre-edit) record didn't count yet, so editing a record you're
     * about to re-liquidate doesn't double-count its old total_expenses.
     */
    private function remainingBudget(string $departmentName, ?int $excludeId = null): array
    {
        $department = \App\Models\Department::where('name', $departmentName)->first();
        $allowable = $department ? (float) $department->allowable_budget : 0;

        $liquidated = (float) DepartmentalExpense::where('department', $departmentName)
            ->where('status', 'LIQUIDATED')
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->sum('total_expenses');

        return [
            'allowable'  => $allowable,
            'liquidated' => $liquidated,
            'remaining'  => $allowable - $liquidated,
        ];
    }


    /**
     * Accept legacy values from cached/older front-ends and normalize them
     * into the new two-status workflow before validation.
     */
    private function prepareStatusInput(Request $request): void
    {
        $liquidationStatus = strtoupper(trim((string) $request->input('status', '')));
        $releaseStatus = strtoupper(trim((string) $request->input('release_status', '')));

        if (in_array($liquidationStatus, ['PENDING', 'NOT LIQUIDATED'], true)) {
            $liquidationStatus = 'NOT YET LIQUIDATED';
        }

        // REJECTED used to live in the combined status column. It now belongs
        // exclusively to Release Status.
        if ($liquidationStatus === 'REJECTED') {
            $releaseStatus = 'REJECTED';
            $liquidationStatus = 'NOT YET LIQUIDATED';
        }

        if ($liquidationStatus === '') {
            $liquidationStatus = 'NOT YET LIQUIDATED';
        }

        if ($releaseStatus === '') {
            $releaseStatus = ($liquidationStatus === 'LIQUIDATED' || $request->filled('date_released'))
                ? 'RELEASED'
                : 'NOT YET RELEASED';
        }

        $request->merge([
            'release_status' => $releaseStatus,
            'status' => $liquidationStatus,
        ]);
    }

    /**
     * Enforce the release -> liquidation workflow and normalize fields that
     * must stay blank until their workflow stage is reached.
     */
    private function normalizeWorkflow(array &$validated): void
    {
        $validated['release_status'] = strtoupper(trim($validated['release_status']));
        $validated['status'] = strtoupper(trim($validated['status']));

        if ($validated['release_status'] === 'REJECTED') {
            $validated['status'] = 'NOT YET LIQUIDATED';
            $validated['date_released'] = null;
            $validated['total_expenses'] = null;
            $validated['amount_returned'] = null;
            $validated['date_of_amount_returned'] = null;
            return;
        }

        if ($validated['release_status'] === 'NOT YET RELEASED') {
            if ($validated['status'] === 'LIQUIDATED') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'status' => 'The request must be released before it can be marked as liquidated.',
                ]);
            }

            $validated['date_released'] = null;
            $validated['total_expenses'] = null;
            $validated['amount_returned'] = null;
            $validated['date_of_amount_returned'] = null;
            return;
        }

        if (empty($validated['date_released'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'date_released' => 'Date Released is required when Release Status is RELEASED.',
            ]);
        }

        if ($validated['status'] === 'LIQUIDATED') {
            if (!array_key_exists('total_expenses', $validated) || $validated['total_expenses'] === null || $validated['total_expenses'] === '') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'total_expenses' => 'Complete the liquidation form and enter Total Expenses before marking the record as liquidated.',
                ]);
            }

            $requestedAmount = (float) ($validated['requested_amount'] ?? 0);
            $totalExpenses = (float) $validated['total_expenses'];
            $validated['amount_returned'] = max(0, $requestedAmount - $totalExpenses);
        } else {
            // A released request may wait for liquidation, but liquidation
            // values must not be stored until the form is completed.
            $validated['total_expenses'] = null;
            $validated['amount_returned'] = null;
            $validated['date_of_amount_returned'] = null;
        }

        if (!empty($validated['date_requested']) && !empty($validated['date_released'])) {
            $dateRequested = Carbon::parse($validated['date_requested']);
            $dateReleased = Carbon::parse($validated['date_released']);

            if ($dateRequested->gt($dateReleased)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'date_released' => 'Date Released must be on or after Date Requested.',
                ]);
            }
        }

        if (!empty($validated['date_released']) && !empty($validated['date_of_amount_returned'])) {
            $dateReleased = Carbon::parse($validated['date_released']);
            $dateReturned = Carbon::parse($validated['date_of_amount_returned']);

            if ($dateReleased->gt($dateReturned)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'date_of_amount_returned' => 'Date of Amount Returned must be on or after Date Released.',
                ]);
            }
        }

        if ($validated['status'] === 'LIQUIDATED'
            && (float) ($validated['amount_returned'] ?? 0) > 0
            && empty($validated['date_of_amount_returned'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'date_of_amount_returned' => 'Date of Amount Returned is required when there is an amount to return.',
            ]);
        }
    }


    private function ensurePeriodUnlocked(DepartmentalExpense $expense): void
    {
        if (!$expense->date_requested) {
            return;
        }

        $date = Carbon::parse($expense->date_requested);
        if (\App\Models\PeriodLock::isLocked((int) $date->month, (int) $date->year)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'date_requested' => date('F Y', mktime(0, 0, 0, $date->month, 1, $date->year))
                    . ' is locked. No changes allowed for this period.',
            ]);
        }
    }

    private function syncBudgetFormSnapshot(DepartmentalExpense $expense): void
    {
        $snapshotKey = 'budget_form_snapshot_' . $expense->id;
        $rawSnapshot = \DB::table('app_settings')->where('key', $snapshotKey)->value('value');

        if ($rawSnapshot === null) {
            return;
        }

        $snapshot = json_decode($rawSnapshot, true);
        if (!is_array($snapshot)) {
            $snapshot = [];
        }

        $snapshot['release_status'] = $expense->release_status;
        $snapshot['liquidation_status'] = $expense->status;
        $snapshot['actual_date_released'] = $expense->date_released
            ? $expense->date_released->format('Y-m-d')
            : null;
        $snapshot['total_expenses'] = $expense->total_expenses !== null
            ? (string) $expense->total_expenses
            : '';
        $snapshot['amount_returned'] = $expense->amount_returned !== null
            ? (string) $expense->amount_returned
            : '';
        $snapshot['date_of_amount_returned'] = $expense->date_of_amount_returned
            ? $expense->date_of_amount_returned->format('Y-m-d')
            : null;

        \DB::table('app_settings')->updateOrInsert(
            ['key' => $snapshotKey],
            ['value' => json_encode($snapshot), 'updated_at' => now()]
        );
    }

    public function index()
    {
        $requests = DepartmentalExpense::orderBy('control_number', 'asc')->orderBy('id', 'asc')->get();

        $departments = \App\Models\Department::with('expenses', 'categories')->get();

        $categories = [];
        foreach ($departments as $dept) {
            $dbCats = $dept->categories->pluck('name')->toArray();
            if (!empty($dbCats)) {
                $categories[$dept->name] = $dbCats;
            }
        }

        foreach ($this->categories as $key => $cats) {
            $fullName = ['Admin' => 'Administrative', 'HR' => 'Human Resource'][$key] ?? $key;
            if (!isset($categories[$fullName]) || empty($categories[$fullName])) {
                $categories[$fullName] = $cats;
            }
        }

        $requestorNames = DepartmentalExpense::select('requestor_name')
            ->distinct()
            ->orderBy('requestor_name')
            ->pluck('requestor_name');

        // Budget commitments per department, driven by actual LIQUIDATED
        // DepartmentalExpense records — used by the "Departments Allowable
        // Budgets" card grid so Remaining/progress bar reflect real spend.
        $commitments = [];
        $commitments = [];
        $recentExpenses = [];
        foreach ($departments as $dept) {
            $commitments[$dept->name] = $this->remainingBudget($dept->name);

            // 3 most recent LIQUIDATED expenses for this department,
            // most recently released first
            $recentExpenses[$dept->name] = DepartmentalExpense::where('department', $dept->name)
                ->where('status', 'LIQUIDATED')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->take(3)
                ->get();
        }

        return view('departmental-expenses', compact('requests', 'categories', 'departments', 'requestorNames', 'commitments', 'recentExpenses'));
    }

    public function store(Request $request)
    {
        $this->prepareStatusInput($request);

        try {
            $validated = $request->validate([
                'requestor_name' => 'required|string',
                'department' => 'required|string',
                'release_status' => 'required|in:' . implode(',', DepartmentalExpense::RELEASE_STATUSES),
                'status' => 'required|in:' . implode(',', DepartmentalExpense::LIQUIDATION_STATUSES),
                'category' => 'required|string',
                'date_requested' => 'required|date',
                'requested_amount' => 'nullable|numeric|min:0',
                'date_released' => 'nullable|date',
                'total_expenses' => 'nullable|numeric|min:0',
                'amount_returned' => 'nullable|numeric|min:0',
                'date_of_amount_returned' => 'nullable|date',
            ]);

            $this->normalizeWorkflow($validated);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first(),
            ], 422);
        }

        $lockDate = $validated['date_released'] ?? $validated['date_requested'];
        if (!empty($lockDate)) {
            $d = Carbon::parse($lockDate);
            if (\App\Models\PeriodLock::isLocked((int) $d->month, (int) $d->year)) {
                return response()->json([
                    'success' => false,
                    'message' => date('F Y', mktime(0, 0, 0, $d->month, 1, $d->year)) . ' is locked. No changes allowed for this period.',
                ], 422);
            }
        }

        $date = Carbon::parse($validated['date_requested']);
        $month = $date->format('m');
        $year = $date->format('y');

        $controlNumber = \DB::transaction(function () use ($month, $year) {
            $count = 1;
            while (DepartmentalExpense::withTrashed()
                ->where('control_number', sprintf('ARCS-%s-%03d-%s', $month, $count, $year))
                ->exists()) {
                $count++;
            }

            return sprintf('ARCS-%s-%03d-%s', $month, $count, $year);
        });

        $validated['control_number'] = $controlNumber;

        try {
            $departmentalExpense = DepartmentalExpense::create($validated);
        } catch (\Exception $e) {
            \Log::error('DepartmentalExpense create error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save: ' . $e->getMessage(),
            ], 500);
        }

        ActivityLog::log(
            'create',
            'Departmental Expenses',
            "Added expense '{$validated['category']}' for {$validated['department']} by {$validated['requestor_name']} (₱" . number_format($validated['requested_amount'] ?? 0, 2) . ") [Release: {$validated['release_status']}; Liquidation: {$validated['status']}]"
        );

        return response()->json([
            'success' => true,
            'message' => 'Expense request created successfully.',
            'data' => $departmentalExpense,
        ]);
    }

    public function update(Request $request, $id)
    {
        $departmentalExpense = DepartmentalExpense::findOrFail($id);

        if ($departmentalExpense->date_requested) {
            $d = Carbon::parse($departmentalExpense->date_requested);
            if (\App\Models\PeriodLock::isLocked((int) $d->month, (int) $d->year)) {
                return response()->json([
                    'success' => false,
                    'message' => date('F Y', mktime(0, 0, 0, $d->month, 1, $d->year)) . ' is locked. No changes allowed for this period.',
                ], 422);
            }
        }

        $this->prepareStatusInput($request);

        try {
            $validated = $request->validate([
                'control_number' => 'required|string|unique:departmental_expenses,control_number,' . $id,
                'requestor_name' => 'required|string',
                'department' => 'required|string',
                'release_status' => 'required|in:' . implode(',', DepartmentalExpense::RELEASE_STATUSES),
                'status' => 'required|in:' . implode(',', DepartmentalExpense::LIQUIDATION_STATUSES),
                'category' => 'required|string',
                'date_requested' => 'required|date',
                'requested_amount' => 'nullable|numeric|min:0',
                'date_released' => 'nullable|date',
                'total_expenses' => 'nullable|numeric|min:0',
                'amount_returned' => 'nullable|numeric|min:0',
                'date_of_amount_returned' => 'nullable|date',
            ]);

            $this->normalizeWorkflow($validated);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first(),
            ], 422);
        }

        $departmentalExpense->update($validated);

        $this->syncBudgetFormSnapshot($departmentalExpense);

        ActivityLog::log(
            'update',
            'Departmental Expenses',
            "Updated expense ID: {$id} ({$validated['department']} - {$validated['category']}) [Release: {$validated['release_status']}; Liquidation: {$validated['status']}]"
        );

        return response()->json([
            'success' => true,
            'message' => 'Expense request updated successfully.',
            'data' => $departmentalExpense->fresh(),
        ]);
    }

    public function updateReleaseStatus(Request $request, $id)
    {
        $expense = DepartmentalExpense::findOrFail($id);

        try {
            $this->ensurePeriodUnlocked($expense);

            $validated = $request->validate([
                'release_status' => 'required|in:' . implode(',', DepartmentalExpense::RELEASE_STATUSES),
                'date_released' => 'nullable|date',
            ]);

            $releaseStatus = strtoupper(trim($validated['release_status']));
            $updates = ['release_status' => $releaseStatus];

            if ($releaseStatus === 'RELEASED') {
                $dateReleased = $validated['date_released'] ?? now()->toDateString();

                if ($expense->date_requested && Carbon::parse($expense->date_requested)->gt(Carbon::parse($dateReleased))) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'date_released' => 'Date Released must be on or after Date Requested.',
                    ]);
                }

                $updates['date_released'] = $dateReleased;

                // A newly released request still waits for its liquidation form.
                if ($expense->status !== 'LIQUIDATED') {
                    $updates['status'] = 'NOT YET LIQUIDATED';
                    $updates['total_expenses'] = null;
                    $updates['amount_returned'] = null;
                    $updates['date_of_amount_returned'] = null;
                }
            } else {
                // Reverting or rejecting a release intentionally clears the saved liquidation form.
                $updates['date_released'] = null;
                $updates['status'] = 'NOT YET LIQUIDATED';
                $updates['total_expenses'] = null;
                $updates['amount_returned'] = null;
                $updates['date_of_amount_returned'] = null;
            }

            $expense->update($updates);
            $expense->refresh();
            $this->syncBudgetFormSnapshot($expense);

            ActivityLog::log(
                'update',
                'Departmental Expenses',
                "Changed release status for {$expense->control_number} to {$expense->release_status}"
            );

            return response()->json([
                'success' => true,
                'message' => 'Release status updated successfully.',
                'data' => $expense,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first(),
            ], 422);
        }
    }

    public function updateLiquidationStatus(Request $request, $id)
    {
        $expense = DepartmentalExpense::findOrFail($id);

        try {
            $this->ensurePeriodUnlocked($expense);

            $validated = $request->validate([
                'status' => 'required|in:' . implode(',', DepartmentalExpense::LIQUIDATION_STATUSES),
                'total_expenses' => 'nullable|numeric|min:0',
                'amount_returned' => 'nullable|numeric|min:0',
                'date_of_amount_returned' => 'nullable|date',
            ]);

            $liquidationStatus = strtoupper(trim($validated['status']));

            if ($expense->release_status !== 'RELEASED' || !$expense->date_released) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'status' => 'Set Release Status to RELEASED before changing the Liquidation Status.',
                ]);
            }

            if ($liquidationStatus === 'NOT YET LIQUIDATED') {
                $expense->update([
                    'status' => 'NOT YET LIQUIDATED',
                    'total_expenses' => null,
                    'amount_returned' => null,
                    'date_of_amount_returned' => null,
                ]);
            } else {
                if (!array_key_exists('total_expenses', $validated)
                    || $validated['total_expenses'] === null
                    || $validated['total_expenses'] === '') {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'total_expenses' => 'Complete the liquidation form and enter Total Expenses.',
                    ]);
                }

                $totalExpenses = (float) $validated['total_expenses'];
                $requestedAmount = (float) ($expense->requested_amount ?? 0);
                $amountReturned = max(0, $requestedAmount - $totalExpenses);
                $dateReturned = $validated['date_of_amount_returned'] ?? null;

                if ($amountReturned > 0 && empty($dateReturned)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'date_of_amount_returned' => 'Date of Amount Returned is required when there is an amount to return.',
                    ]);
                }

                if (!empty($dateReturned)
                    && Carbon::parse($expense->date_released)->gt(Carbon::parse($dateReturned))) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'date_of_amount_returned' => 'Date of Amount Returned must be on or after Date Released.',
                    ]);
                }

                $expense->update([
                    'status' => 'LIQUIDATED',
                    'total_expenses' => $totalExpenses,
                    'amount_returned' => $amountReturned,
                    'date_of_amount_returned' => $amountReturned > 0 ? $dateReturned : null,
                ]);
            }

            $expense->refresh();
            $this->syncBudgetFormSnapshot($expense);

            ActivityLog::log(
                'update',
                'Departmental Expenses',
                "Changed liquidation status for {$expense->control_number} to {$expense->status}"
            );

            return response()->json([
                'success' => true,
                'message' => $expense->status === 'LIQUIDATED'
                    ? 'Liquidation form saved successfully.'
                    : 'Liquidation status updated successfully.',
                'data' => $expense,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first(),
            ], 422);
        }
    }

    public function restore($id)
    {
        if (!auth()->user()->isAdmin()) abort(403);
        $record = \App\Models\DepartmentalExpense::onlyTrashed()->findOrFail($id);
        $record->restore();
        ActivityLog::log('restore', 'Departmental Expenses', "Restored expense '{$record->control_number}'");
        return redirect()->route('settings')->with('success', 'Record restored.')->with('open_section', 'deleted');
    }

    public function purge($id)
    {
        if (!auth()->user()->isAdmin()) abort(403);
        $record = \App\Models\DepartmentalExpense::onlyTrashed()->findOrFail($id);
        $record->forceDelete();
        ActivityLog::log('delete', 'Departmental Expenses', "Permanently deleted expense '{$record->control_number}'");
        return redirect()->route('settings')->with('success', 'Record permanently deleted.')->with('open_section', 'deleted');
    }

    public function destroy($id)
    {
        $DepartmentalExpense = DepartmentalExpense::findOrFail($id);

        if ($DepartmentalExpense->date_requested) {
            $d = Carbon::parse($DepartmentalExpense->date_requested);
            if (\App\Models\PeriodLock::isLocked((int)$d->month, (int)$d->year)) {
                return response()->json(['success' => false, 'message' => date('F Y', mktime(0,0,0,$d->month,1,$d->year)) . ' is locked. No changes allowed for this period.'], 422);
            }
        }

        ActivityLog::log('delete', 'Departmental Expenses', "Deleted expense ID: {$id} ({$DepartmentalExpense->department} - {$DepartmentalExpense->category})", [
            'id'                     => $DepartmentalExpense->id,
            'control_number'         => $DepartmentalExpense->control_number,
            'requestor_name'         => $DepartmentalExpense->requestor_name,
            'department'             => $DepartmentalExpense->department,
            'release_status'         => $DepartmentalExpense->release_status,
            'category'               => $DepartmentalExpense->category,
            'date_requested'         => $DepartmentalExpense->date_requested,
            'requested_amount'       => $DepartmentalExpense->requested_amount,
            'status'                 => $DepartmentalExpense->status,
            'date_released'          => $DepartmentalExpense->date_released,
            'total_expenses'         => $DepartmentalExpense->total_expenses,
            'amount_returned'        => $DepartmentalExpense->amount_returned,
            'date_of_amount_returned'=> $DepartmentalExpense->date_of_amount_returned,
        ]);
        $DepartmentalExpense->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Commission request deleted successfully'
        ]);
    }

    public function getDepartments(Request $request)
    {
        $search = $request->get('search', '');
        
        $departments = DepartmentalExpense::select('department')
            ->distinct()
            ->when($search, function($query) use ($search) {
                return $query->where('department', 'like', '%' . $search . '%');
            })
            ->orderBy('department')
            ->limit(10)
            ->pluck('department');
        
        return response()->json($departments);
    }

    public function getCategories(Request $request)
    {
        $search = $request->get('search', '');
        $department = $request->get('department', '');
        
        $categories = DepartmentalExpense::select('category')
            ->distinct()
            ->when($search, function($query) use ($search) {
                return $query->where('category', 'like', '%' . $search . '%');
            })
            ->when($department, function($query) use ($department) {
                return $query->where('department', $department);
            })
            ->orderBy('category')
            ->limit(10)
            ->pluck('category');
        
        return response()->json($categories);
    }
    
    /**
     * Show the original Budget Request / Liquidation Form for a single
     * "All Expenses" record, exactly as it was filled in and submitted,
     * so it can be viewed and printed again at any time.
     */
    public function viewForm($id)
    {
        $expense = DepartmentalExpense::withTrashed()->findOrFail($id);

        $raw = \DB::table('app_settings')
            ->where('key', 'budget_form_snapshot_' . $expense->id)
            ->value('value');

        $formData = $raw ? json_decode($raw, true) : [];

        return view('budget-request-view', compact('expense', 'formData'));
    }

    public function printLiquidation(Request $request)
    {
        $controlNumbers = $request->query('controls', '');
        
        if (empty($controlNumbers)) {
            $requests = DepartmentalExpense::orderBy('control_number', 'asc')->get();
        } else {
            $controlNumbersArray = explode(',', $controlNumbers);
            $requests = DepartmentalExpense::whereIn('control_number', $controlNumbersArray)
                ->orderBy('control_number', 'asc')
                ->get();
        }
        
        $groupedRequests = $requests->groupBy('control_number');
        
        return view('liquidation-print', compact('groupedRequests'));
    }
}