<?php

namespace App\Services;

use App\Models\PersuasionScenario;
use App\Models\PersuasionSession;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PersuasionPracticeAiService
{
    private const ANTHROPIC_API_URL = 'https://api.anthropic.com/v1/messages';
    private const ANTHROPIC_API_VERSION = '2023-06-01';
    private const GEMINI_API_BASE = 'https://generativelanguage.googleapis.com/v1beta/models';

    // Hard cap on agent turns per session — a safety valve so a stubborn
    // (usually HARD-difficulty) buyer persona can't loop forever and run
    // up API costs. The system prompt nudges the AI to wind down before
    // this point; this constant is the failsafe if it doesn't listen.
    public const MAX_AGENT_TURNS = 15;

    /**
     * Sends the full conversation so far to the AI, roleplaying as the
     * scenario's buyer persona, and returns its reply plus whether the
     * buyer has decided to end the conversation (bought or walked away).
     *
     * @return array{reply: string, decision: string|null}
     *         decision is null (still chatting), "SOLD", or "WALKED_AWAY"
     */
    public function getBuyerReply(PersuasionSession $session): array
    {
        $scenario = $session->scenario;
        $messages = $session->messages()->orderBy('turn_number')->get();
        $agentTurns = $messages->where('sender', 'AGENT')->count();
        $systemPrompt = $this->buildSystemPrompt($scenario, $session->difficulty, $agentTurns);

        try {
            $text = $this->provider() === 'gemini'
                ? $this->callGeminiChat($systemPrompt, $messages)
                : $this->callAnthropicChat($systemPrompt, $messages);

            return $this->extractDecision($text);
        } catch (\Throwable $e) {
            Log::error('Persuasion practice AI call failed', [
                'session_id' => $session->id,
                'provider'   => $this->provider(),
                'message'    => $e->getMessage(),
            ]);

            return [
                'reply'    => "Sorry, I got distracted for a second — could you repeat that?",
                'decision' => null,
                'error'    => true,
            ];
        }
    }

    /**
     * Runs a second, separate AI call once a session ends: reviews the
     * full transcript against a fixed rubric and returns scores + written
     * feedback to store in persuasion_sessions.scorecard.
     */
    public function scoreSession(PersuasionSession $session): array
    {
        $scenario = $session->scenario;
        $messages = $session->messages()->orderBy('turn_number')->get();

        $transcript = $messages->map(function ($m) {
            $speaker = $m->sender === 'AGENT' ? 'Agent' : 'Buyer';
            return "{$speaker}: {$m->message}";
        })->implode("\n");

        $systemPrompt = $this->buildScoringPrompt($scenario, $session->difficulty);

        try {
            $text = $this->provider() === 'gemini'
                ? $this->callGeminiScoring($systemPrompt, $transcript)
                : $this->callAnthropicScoring($systemPrompt, $transcript);

            $clean = preg_replace('/```json|```/', '', $text);
            $parsed = json_decode(trim($clean), true);

            if (!is_array($parsed) || !isset($parsed['overall_score'])) {
                Log::error('Persuasion practice scoring returned unparseable JSON', [
                    'session_id' => $session->id,
                    'provider'   => $this->provider(),
                    'raw'        => $text,
                ]);

                return $this->fallbackScorecard();
            }

            return $parsed;
        } catch (\Throwable $e) {
            Log::error('Persuasion practice scoring call failed', [
                'session_id' => $session->id,
                'provider'   => $this->provider(),
                'message'    => $e->getMessage(),
            ]);

            return $this->fallbackScorecard();
        }
    }

    private function provider(): string
    {
        return config('services.ai_provider', 'anthropic');
    }

    // ── Anthropic (Claude) calls ────────────────────────────────────────

    private function callAnthropicChat(string $systemPrompt, $messages): string
    {
        $response = Http::withHeaders([
            'x-api-key'         => config('services.anthropic.key'),
            'anthropic-version' => self::ANTHROPIC_API_VERSION,
            'content-type'      => 'application/json',
        ])->timeout(30)->post(self::ANTHROPIC_API_URL, [
            'model'      => config('services.anthropic.model'),
            'max_tokens' => 500,
            'system'     => $systemPrompt,
            'messages'   => $messages->map(fn ($m) => [
                'role'    => $m->sender === 'AGENT' ? 'user' : 'assistant',
                'content' => $m->message,
            ])->values()->all(),
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Anthropic API error ' . $response->status() . ': ' . $response->body());
        }

        return collect($response->json('content'))->where('type', 'text')->pluck('text')->implode("\n");
    }

    private function callAnthropicScoring(string $systemPrompt, string $transcript): string
    {
        $response = Http::withHeaders([
            'x-api-key'         => config('services.anthropic.key'),
            'anthropic-version' => self::ANTHROPIC_API_VERSION,
            'content-type'      => 'application/json',
        ])->timeout(30)->post(self::ANTHROPIC_API_URL, [
            'model'      => config('services.anthropic.model'),
            'max_tokens' => 700,
            'system'     => $systemPrompt,
            'messages'   => [
                ['role' => 'user', 'content' => "Transcript:\n\n{$transcript}"],
            ],
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Anthropic API error ' . $response->status() . ': ' . $response->body());
        }

        return collect($response->json('content'))->where('type', 'text')->pluck('text')->implode("\n");
    }

    // ── Gemini calls ─────────────────────────────────────────────────────

    private function callGeminiChat(string $systemPrompt, $messages): string
    {
        $url = self::GEMINI_API_BASE . '/' . config('services.gemini.model') . ':generateContent';

        $response = Http::withHeaders([
            'x-goog-api-key' => config('services.gemini.key'),
            'content-type'   => 'application/json',
        ])->timeout(30)->post($url, [
            'system_instruction' => ['parts' => [['text' => $systemPrompt]]],
            // Gemini uses "model" instead of Anthropic's "assistant" for
            // the AI's own turns; "user" stays the same.
            'contents' => $messages->map(fn ($m) => [
                'role'  => $m->sender === 'AGENT' ? 'user' : 'model',
                'parts' => [['text' => $m->message]],
            ])->values()->all(),
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Gemini API error ' . $response->status() . ': ' . $response->body());
        }

        return (string) $response->json('candidates.0.content.parts.0.text', '');
    }

    private function callGeminiScoring(string $systemPrompt, string $transcript): string
    {
        $url = self::GEMINI_API_BASE . '/' . config('services.gemini.model') . ':generateContent';

        $response = Http::withHeaders([
            'x-goog-api-key' => config('services.gemini.key'),
            'content-type'   => 'application/json',
        ])->timeout(30)->post($url, [
            'system_instruction' => ['parts' => [['text' => $systemPrompt]]],
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => "Transcript:\n\n{$transcript}"]]],
            ],
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Gemini API error ' . $response->status() . ': ' . $response->body());
        }

        return (string) $response->json('candidates.0.content.parts.0.text', '');
    }

    // ── Shared prompt building / parsing (provider-agnostic) ─────────────

    /**
     * Builds the buyer-persona system prompt from scenario fields, with
     * difficulty-specific instructions layered on top.
     */
    private function buildSystemPrompt(PersuasionScenario $scenario, string $difficulty, int $agentTurns = 0): string
    {
        $traits     = $this->bulletList($scenario->linesOf('personality_traits'));
        $objections = $this->bulletList($scenario->linesOf('common_objections'));
        $winConds   = $this->bulletList($scenario->linesOf('win_conditions'));
        $walkConds  = $this->bulletList($scenario->linesOf('walkaway_triggers'));

        // Pacing guidance: as the conversation drags on, nudge the AI to
        // wrap toward a decision instead of stalling indefinitely. This is
        // a soft nudge — MAX_AGENT_TURNS in the controller is the hard cap.
        $turnsLeft = self::MAX_AGENT_TURNS - $agentTurns;
        $pacingNote = match (true) {
            $turnsLeft <= 3 => "You have had a long conversation already. Wrap toward a decision "
                . "very soon — either agree to buy if your win conditions have been reasonably met, "
                . "or politely end the conversation if they have not.",
            $turnsLeft <= 7 => "The conversation has gone on for a while. Start steering toward a "
                . "decision rather than raising new tangents.",
            default => '',
        };

        $difficultyNote = match ($difficulty) {
            'EASY'   => 'You are generally warm and ready to buy. Raise light concerns but agree fairly easily once reassured. Do not manufacture objections that are not in your persona.',
            'MEDIUM' => 'You have genuine concerns and want real answers before deciding. Do not agree until at least one or two of your objections have been properly addressed.',
            'HARD'   => 'You are skeptical and hard to convince. Push back firmly, and be willing to threaten to end the conversation if the agent is vague, pushy, or dismissive. Only change your mind if the agent genuinely earns it.',
            default  => '',
        };

        $budgetLine = $scenario->buyer_budget
            ? "Your budget is around ₱" . number_format((float) $scenario->buyer_budget, 0) . "."
            : '';

        return <<<PROMPT
You are roleplaying as {$scenario->buyer_name}, a prospective real estate
buyer, in a sales-training simulation. You are talking to a real estate
agent who is practicing their persuasion and closing skills. Stay in
character at all times. Never break character, never mention that you are
an AI, and never mention this is a training exercise.

BACKGROUND:
{$scenario->buyer_backstory}
{$budgetLine}

PERSONALITY:
{$traits}

OBJECTIONS YOU MAY RAISE (use naturally, don't dump them all at once):
{$objections}

WHAT WOULD CONVINCE YOU TO BUY:
{$winConds}

WHAT WOULD MAKE YOU WALK AWAY / END THE CONVERSATION:
{$walkConds}

DIFFICULTY GUIDANCE ({$difficulty}):
{$difficultyNote}

PACING:
{$pacingNote}

CONVERSATION RULES:
- Reply as the buyer only, in first person, in natural conversational
  language. Keep replies concise (1-4 sentences), like a real chat message.
- If the agent has genuinely met your win conditions, decide to buy.
- If the agent triggers a walk-away condition, or the conversation drags
  with no progress, decide to end the conversation without buying.
- When you decide the conversation is over (either outcome), end your
  reply with a new line containing exactly one of these tags:
  [[DECISION: SOLD]] or [[DECISION: WALKED_AWAY]]
- Do not include the tag unless you are actually ending the conversation
  this turn. Keep chatting normally otherwise.
PROMPT;
    }

    private function buildScoringPrompt(PersuasionScenario $scenario, string $difficulty): string
    {
        return <<<PROMPT
You are a sales training evaluator for a real estate company. You will be
given the transcript of a practice roleplay conversation between an agent
(the trainee) and an AI-played prospective buyer named {$scenario->buyer_name}
(difficulty: {$difficulty}).

Score the AGENT's performance only, from 0-100 on each of these:
- rapport: did the agent build trust and listen well?
- objection_handling: did the agent address the buyer's concerns directly and credibly?
- product_knowledge: did the agent answer factual questions confidently and accurately?
- closing_technique: did the agent move the conversation toward a decision without being pushy?

Also provide:
- overall_score: 0-100 overall
- summary: 2-3 sentence plain-language summary of how it went
- suggestions: an array of 2-4 short, specific, actionable tips for next time

Respond ONLY with a raw JSON object in this exact shape, no markdown fences,
no preamble:
{"rapport":0,"objection_handling":0,"product_knowledge":0,"closing_technique":0,"overall_score":0,"summary":"","suggestions":["",""]}
PROMPT;
    }

    /** Pulls the [[DECISION: ...]] tag (if any) out of the buyer's reply text. */
    private function extractDecision(string $text): array
    {
        $decision = null;

        if (preg_match('/\[\[DECISION:\s*(SOLD|WALKED_AWAY)\s*\]\]/i', $text, $matches)) {
            $decision = strtoupper($matches[1]);
            $text = trim(preg_replace('/\[\[DECISION:\s*(SOLD|WALKED_AWAY)\s*\]\]/i', '', $text));
        }

        return ['reply' => $text, 'decision' => $decision, 'error' => false];
    }

    private function bulletList(array $lines): string
    {
        if (empty($lines)) {
            return '- (none specified)';
        }

        return collect($lines)->map(fn ($l) => "- {$l}")->implode("\n");
    }

    private function fallbackScorecard(): array
    {
        return [
            'rapport'            => null,
            'objection_handling' => null,
            'product_knowledge'  => null,
            'closing_technique'  => null,
            'overall_score'      => null,
            'summary'            => 'Scoring is temporarily unavailable. Your conversation was saved and can be reviewed manually.',
            'suggestions'        => [],
        ];
    }
}