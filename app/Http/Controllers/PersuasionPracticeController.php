<?php

namespace App\Http\Controllers;

use App\Models\PersuasionMessage;
use App\Models\PersuasionScenario;
use App\Models\PersuasionSession;
use App\Services\PersuasionPracticeAiService;
use Illuminate\Http\Request;

class PersuasionPracticeController extends Controller
{
    public function __construct(private PersuasionPracticeAiService $ai)
    {
    }

    /** Scenario picker — choose a buyer persona + difficulty to practice against. */
    public function index(Request $request)
    {
        $scenarios = PersuasionScenario::where('is_active', true)
            ->orderByRaw("FIELD(difficulty, 'EASY','MEDIUM','HARD')")
            ->orderBy('name')
            ->get()
            ->groupBy('difficulty');

        $userId = $request->user()->id;
        $stats = [
            'total_scenarios'    => $scenarios->flatten()->count(),
            'sessions_completed' => PersuasionSession::where('user_id', $userId)
                ->where('status', '!=', 'IN_PROGRESS')
                ->count(),
            'best_score' => PersuasionSession::where('user_id', $userId)
                ->whereNotNull('overall_score')
                ->max('overall_score'),
            'sold_count' => PersuasionSession::where('user_id', $userId)
                ->where('status', 'SOLD')
                ->count(),
        ];

        return view('practice.index', compact('scenarios', 'stats'));
    }

    /** Past sessions for the logged-in user, with score if finished. */
    public function history(Request $request)
    {
        $sessions = PersuasionSession::with('scenario')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('practice.history', compact('sessions'));
    }

    /** Creates a new session for the chosen scenario and redirects into the chat screen. */
    public function start(Request $request, PersuasionScenario $scenario)
    {
        $session = PersuasionSession::create([
            'user_id'     => $request->user()->id,
            'scenario_id' => $scenario->id,
            'difficulty'  => $scenario->difficulty,
            'status'      => 'IN_PROGRESS',
            'started_at'  => now(),
        ]);

        // Static opening line so the agent isn't staring at a blank chat.
        // Kept static (not AI-generated) for speed/cost — the persona
        // comes through starting with the buyer's first real reply.
        PersuasionMessage::create([
            'session_id'  => $session->id,
            'sender'      => 'BUYER',
            'message'     => "Hi, I'm " . $scenario->buyer_name . ". I saw your listing — tell me more about it.",
            'turn_number' => 1,
        ]);

        return redirect()->route('practice.chat', $session);
    }

    /** The chat screen for an in-progress (or finished/read-only) session. */
    public function chat(Request $request, PersuasionSession $session)
    {
        abort_unless($session->user_id === $request->user()->id, 403);

        $session->load(['scenario', 'messages']);

        return view('practice.chat', compact('session'));
    }

    /**
     * Agent sends a message; the buyer AI replies via Claude. If the buyer
     * decides to buy or walk away, the session is ended and scored
     * automatically as part of this same request.
     */
    public function sendMessage(Request $request, PersuasionSession $session)
    {
        abort_unless($session->user_id === $request->user()->id, 403);

        if ($session->is_finished) {
            return response()->json(['error' => 'This session has already ended.'], 422);
        }

        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $nextTurn = ($session->messages()->max('turn_number') ?? 0) + 1;

        $agentMessage = PersuasionMessage::create([
            'session_id'  => $session->id,
            'sender'      => 'AGENT',
            'message'     => $request->message,
            'turn_number' => $nextTurn,
        ]);

        $result = $this->ai->getBuyerReply($session->fresh(['scenario', 'messages']));

        $buyerMessage = PersuasionMessage::create([
            'session_id'  => $session->id,
            'sender'      => 'BUYER',
            'message'     => $result['reply'],
            'turn_number' => $nextTurn + 1,
            'is_error'    => $result['error'] ?? false,
        ]);

        [$sessionEnded, $session] = $this->applyDecisionOutcome($session, $result);

        return response()->json([
            'agent_message' => $agentMessage,
            'buyer_message' => $buyerMessage,
            'session_ended' => $sessionEnded,
            'session'       => $sessionEnded ? $session->fresh() : null,
        ]);
    }

    /**
     * Retries the most recent BUYER message when it was a system error
     * fallback (not a genuine persona reply) — replaces it in place rather
     * than appending a new turn, and without re-sending the agent's
     * message (which was already saved and already counted).
     */
    public function retryMessage(Request $request, PersuasionSession $session)
    {
        abort_unless($session->user_id === $request->user()->id, 403);

        if ($session->is_finished) {
            return response()->json(['error' => 'This session has already ended.'], 422);
        }

        $lastMessage = $session->messages()->orderByDesc('turn_number')->first();

        abort_unless(
            $lastMessage && $lastMessage->sender === 'BUYER' && $lastMessage->is_error,
            422,
            'Nothing to retry.'
        );

        $turnNumber = $lastMessage->turn_number;
        $lastMessage->delete();

        $result = $this->ai->getBuyerReply($session->fresh(['scenario', 'messages']));

        $buyerMessage = PersuasionMessage::create([
            'session_id'  => $session->id,
            'sender'      => 'BUYER',
            'message'     => $result['reply'],
            'turn_number' => $turnNumber,
            'is_error'    => $result['error'] ?? false,
        ]);

        [$sessionEnded, $session] = $this->applyDecisionOutcome($session, $result);

        return response()->json([
            'buyer_message' => $buyerMessage,
            'session_ended' => $sessionEnded,
            'session'       => $sessionEnded ? $session->fresh() : null,
        ]);
    }

    /**
     * Shared by sendMessage() and retryMessage(): finishes the session if
     * the buyer decided, or if the hard turn cap has been reached.
     *
     * @return array{0: bool, 1: PersuasionSession}
     */
    private function applyDecisionOutcome(PersuasionSession $session, array $result): array
    {
        $sessionEnded = false;
        $agentTurnCount = $session->messages()->where('sender', 'AGENT')->count();

        if (in_array($result['decision'], ['SOLD', 'WALKED_AWAY'], true)) {
            $status = $result['decision'] === 'SOLD' ? 'SOLD' : 'NOT_SOLD';
            $this->finishSession($session, $status);
            $sessionEnded = true;
        } elseif ($agentTurnCount >= PersuasionPracticeAiService::MAX_AGENT_TURNS) {
            // Failsafe: the AI persona still hasn't decided after the max
            // number of turns. Force-end the session so it can't loop
            // forever and keep burning API calls.
            $this->finishSession($session, 'NOT_SOLD');
            $sessionEnded = true;
        }

        return [$sessionEnded, $session];
    }

    /**
     * Ends a session (agent gives up, or manually wraps up the chat) and
     * runs the scoring pass.
     */
    public function end(Request $request, PersuasionSession $session)
    {
        abort_unless($session->user_id === $request->user()->id, 403);

        if (!$session->is_finished) {
            $this->finishSession($session, $request->input('status', 'ABANDONED'));
        }

        return response()->json(['session' => $session->fresh()]);
    }

    /** Marks the session as finished with a status and runs the scoring pass. */
    private function finishSession(PersuasionSession $session, string $status): void
    {
        $scorecard = $this->ai->scoreSession($session->fresh(['scenario', 'messages']));

        $session->update([
            'status'        => $status,
            'ended_at'      => now(),
            'overall_score' => $scorecard['overall_score'] ?? null,
            'scorecard'     => $scorecard,
        ]);
    }
}