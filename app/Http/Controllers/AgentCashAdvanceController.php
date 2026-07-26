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
                'agent_id'           => 'nullable|integer|exists:sales_agents,id',
                'agent_name'         => 'required_without:agent_id|nullable|string|max:150',
                'team'               => 'required|string|max:150',
                'amount'             => 'required|numeric|gt:0',
                'purpose'            => 'required|string|max:500',
                'date_requested'     => 'required|date',
                'date_needed'        => 'required|date|after_or_equal:date_requested',
                'repayment_type'     => 'required|in:INSTALLMENT,OTHERS',
                'installment_terms'  => 'required_if:repayment_type,INSTALLMENT|nullable|integer|min:1|max:' . AgentCashAdvance::MAX_INSTALLMENT_TERMS,
                'repayment_date'     => 'required_if:repayment_type,OTHERS|nullable|date|after_or_equal:date_needed',
            ], [
                'agent_id.exists'         => 'Selected agent could not be found.',
                'agent_name.required_without' => 'Please enter or select an agent.',
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

        // A selected agent (from the sales_agents table) is still resolved
        // and used exactly as before. If none was selected, fall back to
        // whatever name was typed directly into the field — the request is
        // no longer required to match an existing agent record.
        $agent = null;
        if (!empty($validated['agent_id'])) {
            $agent = SalesAgent::find($validated['agent_id']);
            if (!$agent) {
                return response()->json(['success' => false, 'message' => 'Selected agent could not be found.'], 422);
            }
        }

        $agentName = $agent ? $agent->name : trim($validated['agent_name']);

        // Only persist the fields relevant to the chosen repayment type —
        // an Installment request has no repayment_date, an Others request
        // has no installment_terms.
        $isInstallment = $validated['repayment_type'] === 'INSTALLMENT';

        $record = AgentCashAdvance::create([
            'control_number'     => AgentCashAdvance::nextControlNumber(),
            'agent_id'           => $agent?->id,
            'agent_name'         => $agentName,
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

        // No repayment rows are created here anymore. The term schedule
        // (1..total_terms) is derived on the fly from installment_terms /
        // repayment_type — a row in agent_cash_advance_repayments is only
        // ever written once a term is actually paid (see markRepaymentPaid()).

        ActivityLog::log('create', 'Agent Cash Advance', "Submitted agent cash advance {$record->control_number} for {$agentName} (₱" . number_format($validated['amount'], 2) . ")");

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

        // Default amount suggested per term: the equal split for an
        // INSTALLMENT plan, or the full amount for a one-time OTHERS
        // repayment. This is only ever a starting suggestion — the actual
        // amount recorded is whatever is submitted with the payment.
        $defaultTermAmount = $record->repayment_type === 'INSTALLMENT'
            ? $record->amount_per_term
            : (float) $record->amount;

        $paidByTerm = $record->repayments->keyBy('term_number');

        // The schedule (1..total_terms) is generated on the fly. A term
        // only has a row in agent_cash_advance_repayments once it has
        // actually been paid — unpaid terms are synthesized here so the
        // modal still has something to render for each term.
        $terms = collect(range(1, $record->total_terms))->map(function ($termNumber) use ($paidByTerm, $defaultTermAmount) {
            $paid = $paidByTerm->get($termNumber);

            if ($paid) {
                return [
                    'id'          => $paid->id,
                    'term_number' => $termNumber,
                    'status'      => $paid->status,
                    'amount'      => $paid->amount !== null ? (float) $paid->amount : $defaultTermAmount,
                    'date_paid'   => optional($paid->date_paid)->format('Y-m-d'),
                ];
            }

            return [
                'id'          => null,
                'term_number' => $termNumber,
                'status'      => 'PENDING',
                'amount'      => $defaultTermAmount,
                'date_paid'   => null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data'    => [
                'repayment_type'      => $record->repayment_type,
                'amount'              => $record->amount,
                'amount_per_term'     => $record->amount_per_term,
                'repayment_date'      => optional($record->repayment_date)->format('Y-m-d'),
                'status'              => $record->status,
                'payment_stage_label' => $record->payment_stage_label,
                'terms'               => $terms,
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

    /**
     * Record a payment for a single term. Since terms are no longer
     * pre-created, $termNumber identifies the term (1..total_terms) rather
     * than an existing repayment row — the row is created here, the first
     * and only time it comes into existence.
     */
    public function markRepaymentPaid(Request $request, $id, $termNumber)
    {
        $validated = $request->validate([
            'date_paid' => 'required|date',
            'amount'    => 'required|numeric|gt:0',
        ], [
            'date_paid.required' => 'Please enter the date paid.',
            'amount.required'    => 'Please enter the amount paid.',
            'amount.gt'          => 'Amount must be greater than ₱0.',
        ]);

        return DB::transaction(function () use ($validated, $id, $termNumber) {
            $record = AgentCashAdvance::lockForUpdate()->findOrFail($id);

            if ($record->status !== 'APPROVED' && $record->status !== 'COMPLETED') {
                return response()->json(['success' => false, 'message' => 'Only an approved agent cash advance can have repayments recorded.'], 422);
            }

            $termNumber = (int) $termNumber;
            if ($termNumber < 1 || $termNumber > $record->total_terms) {
                return response()->json(['success' => false, 'message' => 'Invalid term number.'], 422);
            }

            $existing = $record->repayments()->where('term_number', $termNumber)->lockForUpdate()->first();
            if ($existing && $existing->status === 'PAID') {
                return response()->json(['success' => false, 'message' => 'This term is already marked as paid.'], 422);
            }

            $term = $record->repayments()->updateOrCreate(
                ['term_number' => $termNumber],
                [
                    'status'    => 'PAID',
                    'amount'    => $validated['amount'],
                    'date_paid' => $validated['date_paid'],
                ]
            );

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
                    'amount'    => (float) $term->amount,
                    'date_paid' => optional($term->date_paid)->format('Y-m-d'),
                ],
            ]);
        });
    }

    /**
     * Undo a payment. Since a repayment row now only ever exists because a
     * payment was recorded, undoing it deletes the row entirely rather than
     * resetting it to PENDING, keeping the table free of un-paid entries.
     */
    public function unmarkRepaymentPaid($id, $termNumber)
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403);

        return DB::transaction(function () use ($id, $termNumber) {
            $record = AgentCashAdvance::lockForUpdate()->findOrFail($id);
            $term = $record->repayments()->where('term_number', (int) $termNumber)->lockForUpdate()->first();

            if (!$term) {
                return response()->json(['success' => false, 'message' => 'This term has not been paid yet.'], 422);
            }

            $term->delete();

            // A record that was auto-completed reverts to Approved once a
            // term is unmarked, since it's no longer fully settled.
            if ($record->status === 'COMPLETED') {
                $record->update(['status' => 'APPROVED']);
                $record->refresh();
            }
            $record->unsetRelation('repayments');

            return response()->json([
                'success'             => true,
                'message'             => 'Payment removed; term reverted to pending.',
                'status'              => $record->status,
                'display_status'      => $record->display_status,
                'payment_stage_label' => $record->payment_stage_label,
                'term'                => [
                    'id'          => null,
                    'term_number' => (int) $termNumber,
                    'status'      => 'PENDING',
                    'date_paid'   => null,
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
