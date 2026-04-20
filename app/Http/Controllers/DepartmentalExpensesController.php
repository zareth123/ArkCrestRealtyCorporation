<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CommissionRequest;
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

    public function index()
    {
        $requests = CommissionRequest::orderBy('date_requested', 'asc')->orderBy('id', 'asc')->get();
        
        // Get departments with their expenses
        $departments = \App\Models\Department::with('expenses', 'categories')->get();
        
        // Build categories from DB + hardcoded fallback
        $categories = [];
        foreach ($departments as $dept) {
            $dbCats = $dept->categories->pluck('name')->toArray();
            if (!empty($dbCats)) {
                $categories[$dept->name] = $dbCats;
            }
        }
        // Merge hardcoded categories for existing departments that have no DB categories
        foreach ($this->categories as $key => $cats) {
            $fullName = ['Admin' => 'Administrative', 'HR' => 'Human Resource'][$key] ?? $key;
            if (!isset($categories[$fullName]) || empty($categories[$fullName])) {
                $categories[$fullName] = $cats;
            }
        }
        
        // Get unique requestor names for autocomplete
        $requestorNames = CommissionRequest::select('requestor_name')
            ->distinct()
            ->orderBy('requestor_name')
            ->pluck('requestor_name');
        
        return view('departmental-expenses', compact('requests', 'categories', 'departments', 'requestorNames'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'requestor_name' => 'required|string',
                'department' => 'required|string',
                'category' => 'required|string',
                'date_requested' => 'nullable|date',
                'requested_amount' => 'nullable|numeric|min:0',
                'status' => 'required|in:LIQUIDATED,NOT YET LIQUIDATED',
                'date_released' => 'nullable|date',
                'total_expenses' => 'nullable|numeric',
                'amount_returned' => 'nullable|numeric',
                'date_of_amount_returned' => 'nullable|date'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->validator->errors()->first()], 422);
        }

        // Check period lock
        if (!empty($validated['date_requested'])) {
            $d = Carbon::parse($validated['date_requested']);
            if (\App\Models\PeriodLock::isLocked((int)$d->month, (int)$d->year)) {
                return response()->json(['success' => false, 'message' => date('F Y', mktime(0,0,0,$d->month,1,$d->year)) . ' is locked. No changes allowed for this period.'], 422);
            }
        }
        
        // Validate date logic: date_requested <= date_released <= date_of_amount_returned
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

        // Generate unique control number - reuse gaps from deleted records
        $date = $validated['date_requested'] ? Carbon::parse($validated['date_requested']) : Carbon::now();
        $month = $date->format('m');
        $year  = $date->format('y');

        $controlNumber = \DB::transaction(function() use ($month, $year) {
            $count = 1;
            while (CommissionRequest::where('control_number', sprintf('ARCS-%s-%03d-%s', $month, $count, $year))->exists()) {
                $count++;
            }
            return sprintf('ARCS-%s-%03d-%s', $month, $count, $year);
        });

        $validated['control_number'] = $controlNumber;
        
        // Convert empty strings to null for date fields
        if (empty($validated['date_requested'])) {
            $validated['date_requested'] = null;
        }
        if (empty($validated['date_released'])) {
            $validated['date_released'] = null;
        }
        if (empty($validated['date_of_amount_returned'])) {
            $validated['date_of_amount_returned'] = null;
        }
        
        // Convert empty strings to null for numeric fields
        if (empty($validated['total_expenses'])) {
            $validated['total_expenses'] = null;
        }
        if (empty($validated['amount_returned'])) {
            $validated['amount_returned'] = null;
        }
        
        // Auto-calculate amount_returned if total_expenses is provided
        if (isset($validated['total_expenses']) && $validated['total_expenses'] > 0) {
            $validated['amount_returned'] = $validated['requested_amount'] - $validated['total_expenses'];
        }

        try {
            $commissionRequest = CommissionRequest::create($validated);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to save. Please try again.'], 500);
        }

        ActivityLog::log('create', 'Departmental Expenses', "Added expense '{$validated['category']}' for {$validated['department']} by {$validated['requestor_name']} (₱" . number_format($validated['requested_amount'] ?? 0, 2) . ")");
        return response()->json([
            'success' => true,
            'message' => 'Commission request created successfully',
            'data' => $commissionRequest
        ]);
    }

    public function update(Request $request, $id)
    {
        $commissionRequest = CommissionRequest::findOrFail($id);

        // Check period lock on existing record
        if ($commissionRequest->date_requested) {
            $d = Carbon::parse($commissionRequest->date_requested);
            if (\App\Models\PeriodLock::isLocked((int)$d->month, (int)$d->year)) {
                return response()->json(['success' => false, 'message' => date('F Y', mktime(0,0,0,$d->month,1,$d->year)) . ' is locked. No changes allowed for this period.'], 422);
            }
        }
        
        try {
            $validated = $request->validate([
                'control_number' => 'required|string|unique:commission_requests,control_number,' . $id,
                'requestor_name' => 'required|string',
                'department' => 'required|string',
                'category' => 'required|string',
                'date_requested' => 'nullable|date',
                'requested_amount' => 'nullable|numeric|min:0',
                'status' => 'required|in:LIQUIDATED,NOT YET LIQUIDATED',
                'date_released' => 'nullable|date',
                'total_expenses' => 'nullable|numeric',
                'amount_returned' => 'nullable|numeric',
                'date_of_amount_returned' => 'nullable|date'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->validator->errors()->first()], 422);
        }
        
        // Validate date logic: date_requested <= date_released <= date_of_amount_returned
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
        
        // Check if control number is being changed and if it's unique
        if ($validated['control_number'] !== $commissionRequest->control_number) {
            $existingRequest = CommissionRequest::where('control_number', $validated['control_number'])
                ->where('id', '!=', $id)
                ->first();
            
            if ($existingRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Control number already exists. Please use a unique control number.'
                ], 422);
            }
        }
        
        // Convert empty strings to null for date fields
        if (empty($validated['date_requested'])) {
            $validated['date_requested'] = null;
        }
        if (empty($validated['date_released'])) {
            $validated['date_released'] = null;
        }
        if (empty($validated['date_of_amount_returned'])) {
            $validated['date_of_amount_returned'] = null;
        }
        
        // Convert empty strings to null for numeric fields
        if (empty($validated['total_expenses'])) {
            $validated['total_expenses'] = null;
        }
        if (empty($validated['amount_returned'])) {
            $validated['amount_returned'] = null;
        }
        
        // Auto-calculate amount_returned if total_expenses is provided
        if (isset($validated['total_expenses']) && $validated['total_expenses'] > 0) {
            $validated['amount_returned'] = $validated['requested_amount'] - $validated['total_expenses'];
        }
        
        $commissionRequest->update($validated);
        ActivityLog::log('update', 'Departmental Expenses', "Updated expense ID: {$id} ({$validated['department']} - {$validated['category']})");
        return response()->json([
            'success' => true,
            'message' => 'Commission request updated successfully',
            'data' => $commissionRequest
        ]);
    }

    public function destroy($id)
    {
        $commissionRequest = CommissionRequest::findOrFail($id);

        // Check period lock
        if ($commissionRequest->date_requested) {
            $d = Carbon::parse($commissionRequest->date_requested);
            if (\App\Models\PeriodLock::isLocked((int)$d->month, (int)$d->year)) {
                return response()->json(['success' => false, 'message' => date('F Y', mktime(0,0,0,$d->month,1,$d->year)) . ' is locked. No changes allowed for this period.'], 422);
            }
        }

        ActivityLog::log('delete', 'Departmental Expenses', "Deleted expense ID: {$id} ({$commissionRequest->department} - {$commissionRequest->category})", [
            'id'                     => $commissionRequest->id,
            'control_number'         => $commissionRequest->control_number,
            'requestor_name'         => $commissionRequest->requestor_name,
            'department'             => $commissionRequest->department,
            'category'               => $commissionRequest->category,
            'date_requested'         => $commissionRequest->date_requested,
            'requested_amount'       => $commissionRequest->requested_amount,
            'status'                 => $commissionRequest->status,
            'date_released'          => $commissionRequest->date_released,
            'total_expenses'         => $commissionRequest->total_expenses,
            'amount_returned'        => $commissionRequest->amount_returned,
            'date_of_amount_returned'=> $commissionRequest->date_of_amount_returned,
        ]);
        $commissionRequest->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Commission request deleted successfully'
        ]);
    }

    // API endpoint for department autocomplete
    public function getDepartments(Request $request)
    {
        $search = $request->get('search', '');
        
        $departments = CommissionRequest::select('department')
            ->distinct()
            ->when($search, function($query) use ($search) {
                return $query->where('department', 'like', '%' . $search . '%');
            })
            ->orderBy('department')
            ->limit(10)
            ->pluck('department');
        
        return response()->json($departments);
    }

    // API endpoint for category autocomplete
    public function getCategories(Request $request)
    {
        $search = $request->get('search', '');
        $department = $request->get('department', '');
        
        $categories = CommissionRequest::select('category')
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
    
    public function printLiquidation(Request $request)
    {
        // Get all visible rows data from query parameters
        $controlNumbers = $request->query('controls', '');
        
        if (empty($controlNumbers)) {
            // If no specific control numbers, get all requests
            $requests = CommissionRequest::orderBy('control_number', 'asc')->get();
        } else {
            // Get specific control numbers
            $controlNumbersArray = explode(',', $controlNumbers);
            $requests = CommissionRequest::whereIn('control_number', $controlNumbersArray)
                ->orderBy('control_number', 'asc')
                ->get();
        }
        
        // Group by control number
        $groupedRequests = $requests->groupBy('control_number');
        
        return view('liquidation-print', compact('groupedRequests'));
    }
}
