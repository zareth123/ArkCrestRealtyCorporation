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
        foreach ($departments as $dept) {
            $commitments[$dept->name] = $this->remainingBudget($dept->name);
        }

        return view('departmental-expenses', compact('requests', 'categories', 'departments', 'requestorNames', 'commitments'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'requestor_name' => 'required|string',
                'department' => 'required|string',
                'category' => 'required|string',
                'date_requested' => 'required|date',
                'requested_amount' => 'nullable|numeric|min:0',
                'status' => 'required|in:' . implode(',', \App\Models\DepartmentalExpense::STATUSES),
                'date_released' => 'required_if:status,LIQUIDATED|nullable|date',
                'total_expenses' => 'nullable|numeric',
                'amount_returned' => 'nullable|numeric',
                'date_of_amount_returned' => 'nullable|date'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->validator->errors()->first()], 422);
        }

        $lockDate = !empty($validated['date_released']) ? $validated['date_released'] : ($validated['date_requested'] ?? null);
        if (!empty($lockDate)) {
            $d = Carbon::parse($lockDate);
            if (\App\Models\PeriodLock::isLocked((int)$d->month, (int)$d->year)) {
                return response()->json(['success' => false, 'message' => date('F Y', mktime(0,0,0,$d->month,1,$d->year)) . ' is locked. No changes allowed for this period.'], 422);
            }
        }

        // TEMPORARILY DISABLED — budget amounts aren't set up on Departments yet,
        // so this check blocks everything. Re-enable by uncommenting once
        // budgets are configured. (Disabled: {{ today's date }})
        /*
        if ($validated['status'] === 'LIQUIDATED') {
            $totalExpenses = (float) ($validated['total_expenses'] ?? 0);
            $budget = $this->remainingBudget($validated['department']);
            if ($totalExpenses > $budget['remaining']) {
                return response()->json([
                    'success' => false,
                    'message' => sprintf(
                        'This exceeds %s\'s remaining budget. Remaining: ₱%s, Attempted: ₱%s.',
                        $validated['department'],
                        number_format($budget['remaining'], 2),
                        number_format($totalExpenses, 2)
                    ),
                ], 422);
            }
        }
        */

        if (!empty($validated['date_requested']) && !empty($validated['date_released'])) {
            $dateRequested = \Carbon\Carbon::parse($validated['date_requested']);
            $dateReleased = \Carbon\Carbon::parse($validated['date_released']);
            
            if ($dateRequested->gt($dateReleased)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Date Released must be on or after Date Requested'
                ], 422);
            }
        }
        
        if (!empty($validated['date_released']) && !empty($validated['date_of_amount_returned'])) {
            $dateReleased = \Carbon\Carbon::parse($validated['date_released']);
            $dateReturned = \Carbon\Carbon::parse($validated['date_of_amount_returned']);
            
            if ($dateReleased->gt($dateReturned)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Date of Amount Returned must be on or after Date Released'
                ], 422);
            }
        }
        
        if (!empty($validated['date_requested']) && !empty($validated['date_of_amount_returned'])) {
            $dateRequested = \Carbon\Carbon::parse($validated['date_requested']);
            $dateReturned = \Carbon\Carbon::parse($validated['date_of_amount_returned']);
            
            if ($dateRequested->gt($dateReturned)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Date of Amount Returned must be on or after Date Requested'
                ], 422);
            }
        }

        $date = $validated['date_requested'] ? Carbon::parse($validated['date_requested']) : Carbon::now();
        $month = $date->format('m');
        $year  = $date->format('y');

        $controlNumber = \DB::transaction(function() use ($month, $year) {
            $count = 1;
            while (DepartmentalExpense::withTrashed()->where('control_number', sprintf('ARCS-%s-%03d-%s', $month, $count, $year))->exists()) {
                $count++;
            }
            return sprintf('ARCS-%s-%03d-%s', $month, $count, $year);
        });

        $validated['control_number'] = $controlNumber;

        if (empty($validated['date_requested'])) {
            $validated['date_requested'] = null;
        }
        if (empty($validated['date_released'])) {
            $validated['date_released'] = null;
        }
        if (empty($validated['date_of_amount_returned'])) {
            $validated['date_of_amount_returned'] = null;
        }

        if (empty($validated['total_expenses'])) {
            $validated['total_expenses'] = null;
        }
        if (empty($validated['amount_returned'])) {
            $validated['amount_returned'] = null;
        }

        if (isset($validated['total_expenses']) && $validated['total_expenses'] > 0) {
            $validated['amount_returned'] = $validated['requested_amount'] - $validated['total_expenses'];
        }

        try {
            $DepartmentalExpense = DepartmentalExpense::create($validated);
        } catch (\Exception $e) {
            \Log::error('DepartmentalExpense create error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to save: ' . $e->getMessage()], 500);
        }

        ActivityLog::log('create', 'Departmental Expenses', "Added expense '{$validated['category']}' for {$validated['department']} by {$validated['requestor_name']} (₱" . number_format($validated['requested_amount'] ?? 0, 2) . ")");
        return response()->json([
            'success' => true,
            'message' => 'Commission request created successfully',
            'data' => $DepartmentalExpense
        ]);
    }

    public function update(Request $request, $id)
    {
        $DepartmentalExpense = DepartmentalExpense::findOrFail($id);

        if ($DepartmentalExpense->date_requested) {
            $d = Carbon::parse($DepartmentalExpense->date_requested);
            if (\App\Models\PeriodLock::isLocked((int)$d->month, (int)$d->year)) {
                return response()->json(['success' => false, 'message' => date('F Y', mktime(0,0,0,$d->month,1,$d->year)) . ' is locked. No changes allowed for this period.'], 422);
            }
        }
        
        try {
            $validated = $request->validate([
                'control_number' => 'required|string|unique:departmental_expenses,control_number,' . $id,
                'requestor_name' => 'required|string',
                'department' => 'required|string',
                'category' => 'required|string',
                'date_requested' => 'nullable|date',
                'requested_amount' => 'nullable|numeric|min:0',
                'status' => 'required|in:' . implode(',', \App\Models\DepartmentalExpense::STATUSES),
                'date_released' => 'nullable|date',
                'total_expenses' => 'nullable|numeric',
                'amount_returned' => 'nullable|numeric',
                'date_of_amount_returned' => 'nullable|date'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->validator->errors()->first()], 422);
        }

        // TEMPORARILY DISABLED — see matching note in store() above.
        /*
        if ($validated['status'] === 'LIQUIDATED') {
            $totalExpenses = (float) ($validated['total_expenses'] ?? 0);
            $budget = $this->remainingBudget($validated['department'], (int) $id);
            if ($totalExpenses > $budget['remaining']) {
                return response()->json([
                    'success' => false,
                    'message' => sprintf(
                        'This exceeds %s\'s remaining budget. Remaining: ₱%s, Attempted: ₱%s.',
                        $validated['department'],
                        number_format($budget['remaining'], 2),
                        number_format($totalExpenses, 2)
                    ),
                ], 422);
            }
        }
        */

        if (!empty($validated['date_requested']) && !empty($validated['date_released'])) {
            $dateRequested = \Carbon\Carbon::parse($validated['date_requested']);
            $dateReleased = \Carbon\Carbon::parse($validated['date_released']);
            
            if ($dateRequested->gt($dateReleased)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Date Released must be on or after Date Requested'
                ], 422);
            }
        }
        
        if (!empty($validated['date_released']) && !empty($validated['date_of_amount_returned'])) {
            $dateReleased = \Carbon\Carbon::parse($validated['date_released']);
            $dateReturned = \Carbon\Carbon::parse($validated['date_of_amount_returned']);
            
            if ($dateReleased->gt($dateReturned)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Date of Amount Returned must be on or after Date Released'
                ], 422);
            }
        }
        
        if (!empty($validated['date_requested']) && !empty($validated['date_of_amount_returned'])) {
            $dateRequested = \Carbon\Carbon::parse($validated['date_requested']);
            $dateReturned = \Carbon\Carbon::parse($validated['date_of_amount_returned']);
            
            if ($dateRequested->gt($dateReturned)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Date of Amount Returned must be on or after Date Requested'
                ], 422);
            }
        }

        if ($validated['control_number'] !== $DepartmentalExpense->control_number) {
            $existingRequest = DepartmentalExpense::where('control_number', $validated['control_number'])
                ->where('id', '!=', $id)
                ->first();
            
            if ($existingRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Control number already exists. Please use a unique control number.'
                ], 422);
            }
        }

        if (empty($validated['date_requested'])) {
            $validated['date_requested'] = null;
        }
        if (empty($validated['date_released'])) {
            $validated['date_released'] = null;
        }
        if (empty($validated['date_of_amount_returned'])) {
            $validated['date_of_amount_returned'] = null;
        }

        if (empty($validated['total_expenses'])) {
            $validated['total_expenses'] = null;
        }
        if (empty($validated['amount_returned'])) {
            $validated['amount_returned'] = null;
        }

        if (isset($validated['total_expenses']) && $validated['total_expenses'] > 0) {
            $validated['amount_returned'] = $validated['requested_amount'] - $validated['total_expenses'];
        }
        
        $DepartmentalExpense->update($validated);

        // Keep the "view & print" Budget Request Form (reachable from this
        // page via the Form button) in sync with the latest release /
        // liquidation details entered here — e.g. via the "UPDATE RECORD"
        // popup shown when Status is switched to LIQUIDATED. The view
        // already falls back to these columns when the snapshot doesn't
        // have its own value, but we sync explicitly too so a snapshot
        // that already has stale values gets overwritten as well.
        $snapshotKey = 'budget_form_snapshot_' . $DepartmentalExpense->id;
        $rawSnapshot = \DB::table('app_settings')->where('key', $snapshotKey)->value('value');
        if ($rawSnapshot !== null) {
            $snapshot = json_decode($rawSnapshot, true);
            if (!is_array($snapshot)) {
                $snapshot = [];
            }
            $snapshot['actual_date_released'] = $DepartmentalExpense->date_released
                ? $DepartmentalExpense->date_released->format('Y-m-d')
                : null;
            $snapshot['total_expenses'] = $DepartmentalExpense->total_expenses !== null
                ? (string) $DepartmentalExpense->total_expenses
                : '';
            $snapshot['amount_returned'] = $DepartmentalExpense->amount_returned !== null
                ? (string) $DepartmentalExpense->amount_returned
                : '';
            \DB::table('app_settings')->updateOrInsert(
                ['key' => $snapshotKey],
                ['value' => json_encode($snapshot), 'updated_at' => now()]
            );
        }

        ActivityLog::log('update', 'Departmental Expenses', "Updated expense ID: {$id} ({$validated['department']} - {$validated['category']})");
        return response()->json([
            'success' => true,
            'message' => 'Commission request updated successfully',
            'data' => $DepartmentalExpense
        ]);
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