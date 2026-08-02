<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\PersuasionScenario;
use App\Models\PersuasionSession;
use Illuminate\Http\Request;

class PersuasionScenarioAdminController extends Controller
{
    /** List + manage all scenarios (active and inactive). */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->isAdmin() && in_array('settings.practice-scenarios', $user->hidden_pages ?? [])) {
            abort(403, 'You do not have permission to view Practice Scenarios.');
        }

        $scenarios = PersuasionScenario::orderByRaw("FIELD(difficulty, 'EASY','MEDIUM','HARD')")
            ->orderBy('name')
            ->get();

        return view('practice.admin.index', compact('scenarios'));
    }

    /**
     * Admin/manager view of every agent's practice sessions — scores and
     * transcripts across the whole team, with the ability to delete a
     * session. Separate from the agent-facing 'practice.history' page,
     * which only ever shows the logged-in agent's own sessions.
     */
    public function history(Request $request)
    {
        $user = $request->user();
        if (!$user->isAdmin() && in_array('settings.practice-history', $user->hidden_pages ?? [])) {
            abort(403, 'You do not have permission to view Practice History.');
        }

        $sessions = PersuasionSession::with(['scenario', 'user'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('practice.admin.history', compact('sessions'));
    }

    /** Permanently removes an agent's practice session (soft delete). Allowed for admins and any staff granted access to the Practice History page. */
    public function destroySession(Request $request, PersuasionSession $session)
    {
        $user = $request->user();
        if (!$user->isAdmin() && in_array('settings.practice-history', $user->hidden_pages ?? [])) {
            abort(403, 'You do not have permission to delete Practice History records.');
        }

        $session->loadMissing(['scenario', 'user']);

        // ?? can't be used inside {$...} string interpolation, so pull it
        // out first (scenario can be null if it was soft-deleted since).
        $buyerName = $session->scenario->buyer_name ?? '—';

        // Log to the activity/audit trail BEFORE deleting, same pattern as
        // scenario deletion — keeps a record and allows restoring later.
        ActivityLog::log('delete', 'Practice Session', "Deleted practice session for '{$session->user->name}' (Buyer: {$buyerName})", [
            'model_class'   => PersuasionSession::class,
            'record_id'     => $session->id,
            'id'            => $session->id,
            'user_id'       => $session->user_id,
            'user_name'     => $session->user->name ?? null,
            'scenario_id'   => $session->scenario_id,
            'difficulty'    => $session->difficulty,
            'status'        => $session->status,
            'overall_score' => $session->overall_score,
            'scorecard'     => $session->scorecard,
            'started_at'    => $session->started_at,
            'ended_at'      => $session->ended_at,
        ]);

        $session->delete();

        return redirect()->route('practice.admin.history')->with('success', 'Practice session removed.');
    }

    /** Bulk-delete multiple practice sessions at once from the checkbox selection on Practice History. */
    public function bulkDestroySession(Request $request)
    {
        $user = $request->user();
        if (!$user->isAdmin() && in_array('settings.practice-history', $user->hidden_pages ?? [])) {
            abort(403, 'You do not have permission to delete Practice History records.');
        }

        $data = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:persuasion_sessions,id',
        ]);

        $sessions = PersuasionSession::with(['scenario', 'user'])->whereIn('id', $data['ids'])->get();

        foreach ($sessions as $session) {
            $buyerName = $session->scenario->buyer_name ?? '—';

            ActivityLog::log('delete', 'Practice Session', "Deleted practice session for '{$session->user->name}' (Buyer: {$buyerName})", [
                'model_class'   => PersuasionSession::class,
                'record_id'     => $session->id,
                'id'            => $session->id,
                'user_id'       => $session->user_id,
                'user_name'     => $session->user->name ?? null,
                'scenario_id'   => $session->scenario_id,
                'difficulty'    => $session->difficulty,
                'status'        => $session->status,
                'overall_score' => $session->overall_score,
                'scorecard'     => $session->scorecard,
                'started_at'    => $session->started_at,
                'ended_at'      => $session->ended_at,
            ]);

            $session->delete();
        }

        $message = count($sessions) . ' practice session(s) removed.';

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->route('practice.admin.history')->with('success', $message);
    }

    public function store(Request $request)
    {
        if (!$request->user()->isAdmin()) abort(403);

        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;

        PersuasionScenario::create($data);

        return redirect()->route('practice.admin')->with('success', 'Scenario created.');
    }

    public function update(Request $request, PersuasionScenario $scenario)
    {
        if (!$request->user()->isAdmin()) abort(403);

        $scenario->update($this->validated($request));

        return redirect()->route('practice.admin')->with('success', 'Scenario updated.');
    }

    public function destroy(PersuasionScenario $scenario)
    {
        if (!auth()->user()->isAdmin()) abort(403);

        // Log to the activity/audit trail BEFORE deleting so the Deleted
        // Records panel (which lists ActivityLog entries with action
        // 'delete') picks this up, and so it can be restored later.
        ActivityLog::log('delete', 'Practice Scenario', "Deleted scenario '{$scenario->name}' (Buyer: {$scenario->buyer_name})", [
            'model_class'         => PersuasionScenario::class,
            'record_id'           => $scenario->id,
            'id'                  => $scenario->id,
            'name'                => $scenario->name,
            'tagline'             => $scenario->tagline,
            'difficulty'          => $scenario->difficulty,
            'buyer_name'          => $scenario->buyer_name,
            'buyer_avatar'        => $scenario->buyer_avatar,
            'buyer_backstory'     => $scenario->buyer_backstory,
            'buyer_budget'        => $scenario->buyer_budget,
            'personality_traits'  => $scenario->personality_traits,
            'common_objections'   => $scenario->common_objections,
            'win_conditions'      => $scenario->win_conditions,
            'walkaway_triggers'   => $scenario->walkaway_triggers,
            'property_id'         => $scenario->property_id,
            'is_active'           => $scenario->is_active,
            'created_by'          => $scenario->created_by,
        ]);

        // Soft delete only — keeps historical sessions/scores intact for
        // any agent who already practiced against this scenario.
        $scenario->delete();

        return redirect()->route('practice.admin')->with('success', 'Scenario removed.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name'                => 'required|string|max:150',
            'tagline'             => 'nullable|string|max:200',
            'difficulty'          => 'required|in:EASY,MEDIUM,HARD',
            'buyer_name'          => 'required|string|max:100',
            'buyer_backstory'     => 'nullable|string',
            'buyer_budget'        => 'nullable|numeric|min:0',
            'personality_traits'  => 'nullable|string',
            'common_objections'   => 'nullable|string',
            'win_conditions'      => 'nullable|string',
            'walkaway_triggers'   => 'nullable|string',
            'is_active'           => 'nullable|boolean',
        ]);

        // Checkbox: absent from the request means unchecked/false.
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}