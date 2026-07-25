<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AgentCashAdvance;
use App\Models\AgentCashAdvanceRepayment;
use App\Models\ActivityLog;
use App\Models\SalesAgent;
use App\Models\SalesTeam;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AgentCashAdvanceController extends Controller
{
    public function index()
    {
        $records = AgentCashAdvance::with(['agent.team', 'reviewer', 'repayments'])
            ->orderBy('id', 'desc')
            ->get();

        // Agents sourced from the real sales_agents table (not free text),
        // same reasoning as Cash Advance sourcing Employees from Users.
        $agents = SalesAgent::where('is_active', true)
            ->with('team')
            ->orderBy('name')
            ->get(['id', 'team_id', 'name']);

        // Teams for the "Team" field on the request form, sourced the same
        // way Cash Advance sources Departments.
        $teams = SalesTeam::orderBy('team_name')->pluck('team_name');

        $totalRecords = $records->count();
        $pendingCount = $records->where('status', 'PENDING')->count();
        // Rejected requests no longer count toward money committed.
        $totalRequested = $records->where('status', '!=', 'REJECTED')->sum('amount');

        return view('agent-cash-advance', compact('records', 'agents', 'teams', 'totalRecords', 'pendingCount', 'totalRequested'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'agent_id'           => 'required|integer|exists:sales_agents,id',
                'team'               => 'required|string|max:150',
                'amount'             => 'required|numeric|gt:0',
                'purpose'            => 'required|string|max:500',
                'date_requested'     => 'required|date',
                'date_needed'        => 'required|date|after_or_equal:date_requested',
                'repayment_type'     => 'required|in:INSTALLMENT,OTHERS',
                'installment_terms'  => 'required_if:repayment_type,INSTALLMENT|nullable|integer|min:1|max:' . AgentCashAdvance::MAX_INSTALLMENT_TERMS,
                'repayment_date'     => 'required_if:repayment_type,OTHERS|nullable|date|after_or_equal:date_needed',
            ], [
                'agent_id.required'       => 'Please select an agent.',
                'agent_id.exists'         => 'Selected agent could not be found.',
                'team.required'           => 'Please select a team.',
                'amount.required'         => 'Please enter an amount.',
                'amount.gt'               => 'Amount must be greater than ₱0.',
                'purpose.required'        => 'Please enter a purpose.',
                'date_requested.required' => 'Please select the date requested.',
                'date_needed.required'    => 'Please select the date needed.',
                'date_needed.after_or_equal' => 'Date needed cannot be earlier than the date requested.',
                'repayment_type.required' => 'Please select a repayment type.',
                'repayment_type.in'       => 'Repayment type must be either Installment or Others.',
                'installment_terms.required_if' => 'Please select the number of terms.',
                'installment_terms.max'   => 'A maximum of ' . AgentCashAdvance::MAX_INSTALLMENT_TERMS . ' terms is allowed.',
                'repayment_date.required_if' => 'Please select a repayment date.',
                'repayment_date.after_or_equal' => 'Repayment date cannot be earlier than the date needed.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->validator->errors()->first()], 422);
        }

        $agent = SalesAgent::find($validated['agent_id']);
        if (!$agent) {
            return response()->json(['success' => false, 'message' => 'Selected agent could not be found.'], 422);
        }

        // Only persist the fields relevant to the chosen repayment type —
        // an Installment request has no repayment_date, an Others request
        // has no installment_terms.
        $isInstallment = $validated['repayment_type'] === 'INSTALLMENT';

        $record = AgentCashAdvance::create([
            'control_number'     => AgentCashAdvance::nextControlNumber(),
            'agent_id'           => $agent->id,
            'agent_name'         => $agent->name,
            'team'               => $validated['team'],
            'amount'             => $validated['amount'],
            'purpose'            => $validated['purpose'],
            'date_requested'     => $validated['date_requested'],
            'date_needed'        => $validated['date_needed'],
            'repayment_type'     => $validated['repayment_type'],
            'installment_terms'  => $isInstallment ? $validated['installment_terms'] : null,
            'repayment_date'     => $isInstallment ? null : $validated['repayment_date'],
            'status'             => 'PENDING',
        ]);

        // Pre-generate the repayment schedule now, since the number of terms
        // (or the single Others repayment) is already fixed at request time.
        // Each row starts PENDING and is marked PAID later from the Records
        // page's Edit / repayment-tracking workflow.
        $totalTerms = $isInstallment ? (int) $validated['installment_terms'] : 1;
        for ($term = 1; $term <= $totalTerms; $term++) {
            $record->repayments()->create([
                'term_number' => $term,
                'status'      => 'PENDING',
            ]);
        }

        ActivityLog::log('create', 'Agent Cash Advance', "Submitted agent cash advance {$record->control_number} for {$agent->name} (₱" . number_format($validated['amount'], 2) . ")");

        return response()->json([
            'success' => true,
            'message' => "Agent cash advance {$record->control_number} submitted successfully.",
            'data'    => $record,
        ]);
    }

    public function approve($id)
    {
        $record = AgentCashAdvance::findOrFail($id);

        if ($record->status !== 'PENDING') {
            return response()->json(['success' => false, 'message' => 'Only pending requests can be approved.'], 422);
        }

        $record->update([
            'status'      => 'APPROVED',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        ActivityLog::log('update', 'Agent Cash Advance', "Approved agent cash advance {$record->control_number} for {$record->agent_name}");

        return response()->json([
            'success' => true,
            'message' => "{$record->control_number} has been approved.",
            'data'    => $record,
        ]);
    }

    public function reject($id)
    {
        $record = AgentCashAdvance::findOrFail($id);

        if ($record->status !== 'PENDING') {
            return response()->json(['success' => false, 'message' => 'Only pending requests can be rejected.'], 422);
        }

        $record->update([
            'status'      => 'REJECTED',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        ActivityLog::log('update', 'Agent Cash Advance', "Rejected agent cash advance {$record->control_number} for {$record->agent_name}");

        return response()->json([
            'success' => true,
            'message' => "{$record->control_number} has been rejected. The amount is no longer counted in Total Requested.",
            'data'    => $record,
        ]);
    }

    /**
     * Full record detail for the View (read-only, printable) modal.
     */
    public function show($id)
    {
        $record = AgentCashAdvance::with(['agent.team', 'reviewer', 'repayments'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => array_merge($record->toArray(), [
                'payment_stage_label' => $record->payment_stage_label,
                'display_status'      => $record->display_status,
                'amount_per_term'     => $record->amount_per_term,
            ]),
        ]);
    }

    /**
     * Repayment terms for the Edit (repayment-tracking) modal.
     */
    public function repayments($id)
    {
        $record = AgentCashAdvance::with('repayments')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => [
                'repayment_type'      => $record->repayment_type,
                'amount'              => $record->amount,
                'amount_per_term'     => $record->amount_per_term,
                'repayment_date'      => optional($record->repayment_date)->format('Y-m-d'),
                'status'              => $record->status,
                'payment_stage_label' => $record->payment_stage_label,
                'terms'               => $record->repayments->map(fn ($t) => [
                    'id'          => $t->id,
                    'term_number' => $t->term_number,
                    'status'      => $t->status,
                    'date_paid'   => optional($t->date_paid)->format('Y-m-d'),
                ])->values(),
            ],
        ]);
    }

    /**
     * Recompute the Payment Stage and, once every term is settled,
     * automatically flip the record's Status to Completed.
     */
    private function syncPaymentProgress(AgentCashAdvance $record): AgentCashAdvance
    {
        $record->refresh();

        $totalTerms = $record->total_terms;
        $paidTerms  = $record->paid_terms;

        if ($record->status === 'APPROVED' && $totalTerms > 0 && $paidTerms >= $totalTerms) {
            $record->update(['status' => 'COMPLETED']);
            $record->refresh();
        }

        return $record;
    }

    public function markRepaymentPaid(Request $request, $repaymentId)
    {
        $validated = $request->validate([
            'date_paid' => 'required|date',
        ], [
            'date_paid.required' => 'Please enter the date paid.',
        ]);

        return DB::transaction(function () use ($validated, $repaymentId) {
            $term = AgentCashAdvanceRepayment::lockForUpdate()->findOrFail($repaymentId);

            if ($term->status === 'PAID') {
                return response()->json(['success' => false, 'message' => 'This term is already marked as paid.'], 422);
            }

            $record = AgentCashAdvance::lockForUpdate()->findOrFail($term->agent_cash_advance_id);

            if ($record->status !== 'APPROVED' && $record->status !== 'COMPLETED') {
                return response()->json(['success' => false, 'message' => 'Only an approved agent cash advance can have repayments recorded.'], 422);
            }

            $term->update([
                'status'    => 'PAID',
                'date_paid' => $validated['date_paid'],
            ]);

            $record = $this->syncPaymentProgress($record);

            ActivityLog::log('update', 'Agent Cash Advance', "Recorded repayment term {$term->term_number} for {$record->control_number} ({$record->agent_name})");

            return response()->json([
                'success'             => true,
                'message'             => $record->status === 'COMPLETED'
                    ? "All repayment terms are settled — {$record->control_number} is now Completed."
                    : 'Term marked as paid.',
                'status'              => $record->status,
                'display_status'      => $record->display_status,
                'payment_stage_label' => $record->payment_stage_label,
                'term'                => [
                    'id'        => $term->id,
                    'status'    => $term->status,
                    'date_paid' => optional($term->date_paid)->format('Y-m-d'),
                ],
            ]);
        });
    }

    public function unmarkRepaymentPaid($repaymentId)
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403);

        return DB::transaction(function () use ($repaymentId) {
            $term = AgentCashAdvanceRepayment::lockForUpdate()->findOrFail($repaymentId);

            $term->update([
                'status'    => 'PENDING',
                'date_paid' => null,
            ]);

            $record = AgentCashAdvance::lockForUpdate()->findOrFail($term->agent_cash_advance_id);

            // A record that was auto-completed reverts to Approved once a
            // term is unmarked, since it's no longer fully settled.
            if ($record->status === 'COMPLETED') {
                $record->update(['status' => 'APPROVED']);
                $record->refresh();
            }
            $record->unsetRelation('repayments');
            return response()->json([
                'success'             => true,
                'message'             => 'Term reverted to pending.',
                'status'              => $record->status,
                'display_status'      => $record->display_status,
                'payment_stage_label' => $record->payment_stage_label,
                'term'                => [
                    'id'        => $term->id,
                    'status'    => $term->status,
                    'date_paid' => null,
                ],
            ]);
        });
    }

    public function destroy($id)
    {
        $record = AgentCashAdvance::findOrFail($id);

        ActivityLog::log('delete', 'Agent Cash Advance', "Deleted agent cash advance {$record->control_number} for {$record->agent_name}", [
            'model_class'    => AgentCashAdvance::class,
            'record_id'      => $record->id,
            'id'             => $record->id,
            'control_number' => $record->control_number,
            'agent_name'     => $record->agent_name,
            'amount'         => $record->amount,
            'status'         => $record->status,
        ]);

        $record->delete();

        return response()->json([
            'success' => true,
            'message' => "{$record->control_number} was deleted.",
        ]);
    }

    /**
     * Delete a single repayment term row. Used by the Repayment Records
     * table's bulk "Delete Selected" action. (Cash Advance's reference UI
     * ships this same button but has no matching backend route — this
     * module adds the missing endpoint so bulk delete actually works.)
     */
    public function destroyRepayment($repaymentId)
    {
        $term = AgentCashAdvanceRepayment::findOrFail($repaymentId);
        $record = $term->agentCashAdvance;

        ActivityLog::log('delete', 'Agent Cash Advance', "Deleted repayment term {$term->term_number} for " . ($record->control_number ?? '#' . $term->agent_cash_advance_id) . ($record ? " ({$record->agent_name})" : ''));

        $term->delete();

        return response()->json([
            'success' => true,
            'message' => 'Repayment record was deleted.',
        ]);
    }
}
