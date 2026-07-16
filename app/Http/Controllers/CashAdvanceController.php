<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CashAdvance;
use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;

class CashAdvanceController extends Controller
{
    public function index()
    {
        $records = CashAdvance::with(['employee', 'reviewer'])
            ->orderBy('id', 'desc')
            ->get();

        // Employees sourced straight from the Users table so names / IDs on
        // the form are always accurate and can't be mistyped.
        $employees = User::whereIn('status', ['active', 'pre_registered'])
            ->orderBy('name')
            ->get(['id', 'name', 'employee_id', 'position']);

        $totalRecords = $records->count();
        $pendingCount = $records->where('status', 'PENDING')->count();
        // Rejected requests no longer count toward money committed.
        $totalRequested = $records->where('status', '!=', 'REJECTED')->sum('amount');

        return view('cash-advance', compact('records', 'employees', 'totalRecords', 'pendingCount', 'totalRequested'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id'     => 'required|integer|exists:users,id',
                'amount'          => 'required|numeric|gt:0',
                'reason'          => 'required|string|max:500',
                'repayment_date'  => 'required|date|after_or_equal:' . now()->toDateString(),
            ], [
                'employee_id.required'    => 'Please select an employee.',
                'employee_id.exists'      => 'Selected employee could not be found.',
                'amount.required'         => 'Please enter an amount.',
                'amount.gt'               => 'Amount must be greater than ₱0.',
                'reason.required'         => 'Please enter a reason.',
                'repayment_date.required' => 'Please select a repayment date.',
                'repayment_date.after_or_equal' => 'Repayment date cannot be earlier than today.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->validator->errors()->first()], 422);
        }

        $employee = User::find($validated['employee_id']);
        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Selected employee could not be found.'], 422);
        }

        $record = CashAdvance::create([
            'control_number'  => CashAdvance::nextControlNumber(),
            'employee_id'     => $employee->id,
            'employee_name'   => $employee->name,
            'amount'          => $validated['amount'],
            'reason'          => $validated['reason'],
            'repayment_date'  => $validated['repayment_date'],
            'status'          => 'PENDING',
        ]);

        ActivityLog::log('create', 'Cash Advance', "Submitted cash advance {$record->control_number} for {$employee->name} (₱" . number_format($validated['amount'], 2) . ")");

        return response()->json([
            'success' => true,
            'message' => "Cash advance {$record->control_number} submitted successfully.",
            'data'    => $record,
        ]);
    }

    public function approve($id)
    {
        $record = CashAdvance::findOrFail($id);

        if ($record->status !== 'PENDING') {
            return response()->json(['success' => false, 'message' => 'Only pending requests can be approved.'], 422);
        }

        $record->update([
            'status'      => 'APPROVED',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        ActivityLog::log('update', 'Cash Advance', "Approved cash advance {$record->control_number} for {$record->employee_name}");

        return response()->json([
            'success' => true,
            'message' => "{$record->control_number} has been approved.",
            'data'    => $record,
        ]);
    }

    public function reject($id)
    {
        $record = CashAdvance::findOrFail($id);

        if ($record->status !== 'PENDING') {
            return response()->json(['success' => false, 'message' => 'Only pending requests can be rejected.'], 422);
        }

        $record->update([
            'status'      => 'REJECTED',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        ActivityLog::log('update', 'Cash Advance', "Rejected cash advance {$record->control_number} for {$record->employee_name}");

        return response()->json([
            'success' => true,
            'message' => "{$record->control_number} has been rejected. The amount is no longer counted in Total Requested.",
            'data'    => $record,
        ]);
    }

    public function destroy($id)
    {
        $record = CashAdvance::findOrFail($id);

        ActivityLog::log('delete', 'Cash Advance', "Deleted cash advance {$record->control_number} for {$record->employee_name}", [
             'model_class'    => CashAdvance::class,
            'record_id'      => $record->id,
            'id'             => $record->id,
            'control_number' => $record->control_number,
            'employee_name'  => $record->employee_name,
            'amount'         => $record->amount,
            'status'         => $record->status,
        ]);

        $record->delete();

        return response()->json([
            'success' => true,
            'message' => "{$record->control_number} was deleted.",
        ]);
    }
}