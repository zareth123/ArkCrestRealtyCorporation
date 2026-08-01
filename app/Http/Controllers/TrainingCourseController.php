<?php

namespace App\Http\Controllers;

use App\Models\TrainingModuleProgress;
use App\Services\AgentTrainingCourseService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TrainingCourseController extends Controller
{
    /** Real Estate Agent Training landing page — modules, lesson content, and per-user progress. */
    public function index(Request $request)
    {
        $user = $request->user();

        $progress = AgentTrainingCourseService::progressFor($user);
        $quizzes = [
            1 => AgentTrainingCourseService::quizForView(1),
            2 => AgentTrainingCourseService::quizForView(2),
            3 => AgentTrainingCourseService::quizForView(3),
            4 => AgentTrainingCourseService::quizForView(4),
            5 => AgentTrainingCourseService::quizForView(5),
            6 => AgentTrainingCourseService::quizForView(6),
        ];
        $overallPercent = AgentTrainingCourseService::overallPercent($progress);
        $completedCount = AgentTrainingCourseService::completedCount($progress);

        // Where the "Start / Continue Course" button should jump to: the
        // first unlocked-but-not-yet-completed *numbered* module, defaulting
        // to Module 1. We only look at real training modules here (1..TOTAL_MODULES) —
        // $progress may also carry non-module entries (e.g. Persuasion Practice),
        // which must never be picked as $continueModule since it isn't rendered
        // by training-modules.module-XX / the module route.
        $totalModules = AgentTrainingCourseService::TOTAL_MODULES;
        $continueModule = null;
        for ($number = 1; $number <= $totalModules; $number++) {
            if (isset($progress[$number]) && $progress[$number]['unlocked'] && !$progress[$number]['completed']) {
                $continueModule = $number;
                break;
            }
        }
        // All numbered modules are complete — nothing left to "continue" into.
        $allModulesCompleted = is_null($continueModule);

        return view('training-course', [
            'progress'       => $progress,
            'quizzes'        => $quizzes,
            'overallPercent' => $overallPercent,
            'completedCount' => $completedCount,
            'passingScore'   => AgentTrainingCourseService::PASSING_SCORE,
            'continueModule' => $continueModule,
            'allModulesCompleted' => $allModulesCompleted,
        ]);
    }

    /**
     * Renders a single module's own page: lesson content, its quiz, and
     * previous/next navigation. Each module now lives at its own URL
     * (/agent-training/module/{module}) instead of being rendered as one
     * long accordion on the course overview page.
     */
    public function showModule(Request $request, int $module)
    {
        $modulesMeta = AgentTrainingCourseService::modules();

        if (!isset($modulesMeta[$module])) {
            abort(404);
        }

        $user = $request->user();
        $progress = AgentTrainingCourseService::progressFor($user);

        if (!$progress[$module]['unlocked']) {
            $previous = $module - 1;
            $message = isset($modulesMeta[$previous])
                ? 'Complete Module ' . sprintf('%02d', $previous) . ' — ' . $modulesMeta[$previous]['title'] . ' before opening this module.'
                : 'This module is not available yet.';

            return redirect()
                ->route('agent-training')
                ->with('error', $message);
        }

        // The lesson page no longer renders quiz questions itself (they now
        // live on the dedicated exam page) — it only needs the count for the
        // "Start Exam" entry card.
        $questionCount = count(AgentTrainingCourseService::quizForView($module));

        $totalModules = AgentTrainingCourseService::TOTAL_MODULES;
        $prevModule = $module > 1 ? $progress[$module - 1] : null;
        $nextModule = $module < $totalModules ? $progress[$module + 1] : null;

        return view('training-course-module', [
            'module'         => $progress[$module],
            'moduleNumber'   => $module,
            'questionCount'  => $questionCount,
            'passingScore'   => AgentTrainingCourseService::PASSING_SCORE,
            'prevModule'     => $prevModule,
            'nextModule'     => $nextModule,
            'overallPercent' => AgentTrainingCourseService::overallPercent($progress),
            'completedCount' => AgentTrainingCourseService::completedCount($progress),
            'totalModules'   => $totalModules,
        ]);
    }

    /**
     * Renders the dedicated, distraction-free Exam Mode page for a module
     * (LMS-style: leaves the lesson page entirely). Unlock rules mirror
     * showModule() exactly — a module's exam can't be opened until the
     * module itself is unlocked.
     */
    public function showExam(Request $request, int $module)
    {
        $modulesMeta = AgentTrainingCourseService::modules();

        if (!isset($modulesMeta[$module])) {
            abort(404);
        }

        $user = $request->user();
        $progress = AgentTrainingCourseService::progressFor($user);

        if (!$progress[$module]['unlocked']) {
            $previous = $module - 1;
            $message = isset($modulesMeta[$previous])
                ? 'Complete Module ' . sprintf('%02d', $previous) . ' — ' . $modulesMeta[$previous]['title'] . ' before opening this module.'
                : 'This module is not available yet.';

            return redirect()
                ->route('agent-training')
                ->with('error', $message);
        }

        $questions = AgentTrainingCourseService::quizForView($module);
        $totalModules = AgentTrainingCourseService::TOTAL_MODULES;
        $nextModule = $module < $totalModules ? $progress[$module + 1] : null;

        return view('training-course-exam', [
            'module'         => $progress[$module],
            'moduleNumber'   => $module,
            'questions'      => $questions,
            'passingScore'   => AgentTrainingCourseService::PASSING_SCORE,
            'nextModule'     => $nextModule,
            'totalModules'   => $totalModules,
        ]);
    }

    /**
     * Renders the dedicated Exam Results page. Score/correct/skipped are
     * read from the query string set right after grading (see the redirect
     * in training-course-quiz.blade.php) so the breakdown reflects that
     * specific attempt; everything else (best score, attempt number, pass
     * state) is read live from the persisted progress row, so a direct
     * visit or a page refresh still renders a correct — if slightly less
     * granular — summary instead of breaking.
     */
    public function examResults(Request $request, int $module)
    {
        $modulesMeta = AgentTrainingCourseService::modules();

        if (!isset($modulesMeta[$module])) {
            abort(404);
        }

        $user = $request->user();
        $progress = AgentTrainingCourseService::progressFor($user);

        if (!$progress[$module]['unlocked']) {
            return redirect()
                ->route('agent-training')
                ->with('error', 'This module is not available yet.');
        }

        $moduleProgress = $progress[$module];
        $totalQuestions = count(AgentTrainingCourseService::quizForView($module));

        $validated = $request->validate([
            'score'    => ['nullable', 'integer', 'min:0', 'max:100'],
            'correct'  => ['nullable', 'integer', 'min:0'],
            'total'    => ['nullable', 'integer', 'min:0'],
            'skipped'  => ['nullable', 'integer', 'min:0'],
            'passed'   => ['nullable', 'in:0,1'],
        ]);

        $total = $validated['total'] ?? $totalQuestions;
        $score = $validated['score'] ?? ($moduleProgress['last_score'] ?? 0);
        $correct = array_key_exists('correct', $validated) && $validated['correct'] !== null
            ? $validated['correct']
            : (int) round($score / 100 * $total);
        $skipped = $validated['skipped'] ?? 0;
        $incorrect = max(0, $total - $correct - $skipped);
        $passed = array_key_exists('passed', $validated) && $validated['passed'] !== null
            ? $validated['passed'] === '1'
            : $score >= AgentTrainingCourseService::PASSING_SCORE;

        $totalModules = AgentTrainingCourseService::TOTAL_MODULES;
        $nextModuleNumber = $module + 1;
        $nextModule = $nextModuleNumber <= $totalModules ? $progress[$nextModuleNumber] : null;

        return view('training-course-exam-results', [
            'moduleNumber' => $module,
            'module'       => $moduleProgress,
            'score'        => $score,
            'correct'      => $correct,
            'incorrect'    => $incorrect,
            'skipped'      => $skipped,
            'total'        => $total,
            'passed'       => $passed,
            'attempts'     => $moduleProgress['attempts'],
            'passingScore' => AgentTrainingCourseService::PASSING_SCORE,
            'nextModule'   => $nextModule,
            'totalModules' => $totalModules,
        ]);
    }

    /**
     * Grades a "Check Your Understanding" quiz submission server-side and
     * persists attempts/score/completion to the database. Also enforces
     * sequential unlocking — a module can't be completed unless the
     * previous one has already been passed, and only implemented modules
     * (1 & 2 for now) accept submissions at all.
     */
    public function submitQuiz(Request $request, int $module)
    {
        $modulesMeta = AgentTrainingCourseService::modules();

        if (!isset($modulesMeta[$module]) || !$modulesMeta[$module]['implemented']) {
            return response()->json([
                'message' => 'This module is not yet available.',
            ], 404);
        }

        $user = $request->user();
        $progress = AgentTrainingCourseService::progressFor($user);

        if (!$progress[$module]['unlocked']) {
            return response()->json([
                'message' => 'Complete the previous module before attempting this quiz.',
            ], 403);
        }

        $questionCount = count(AgentTrainingCourseService::quizBank()[$module]);

        // -1 represents a skipped question (learner pressed "Skip" instead of
        // selecting an option). It's an intentionally out-of-range index so
        // AgentTrainingCourseService::grade() will never match it against a
        // correct answer (0-3), meaning skipped questions are always graded
        // as incorrect without any change needed to the grading logic itself.
        $validated = $request->validate([
            'answers' => ['required', 'array', 'size:' . $questionCount],
            'answers.*' => ['required', 'integer', 'min:-1', 'max:3'],
        ]);

        $grading = AgentTrainingCourseService::grade($module, $validated['answers']);

        $row = TrainingModuleProgress::firstOrNew([
            'user_id' => $user->id,
            'module_number' => $module,
        ]);

        $row->attempts = ($row->attempts ?? 0) + 1;
        $row->last_score = $grading['score'];
        $row->best_score = max($row->best_score ?? 0, $grading['score']);
        $row->last_attempted_at = now();

        if ($grading['passed'] && !$row->passed) {
            $row->passed = true;
            $row->completed_at = now();
        }

        $row->save();

        // Recompute full state after saving so the response reflects reality.
        $updatedProgress = AgentTrainingCourseService::progressFor($user);

        return response()->json([
            'module'          => $module,
            'score'           => $grading['score'],
            'correct'         => $grading['correct'],
            'total'           => $grading['total'],
            'passed'          => $grading['passed'],
            'passing_score'   => AgentTrainingCourseService::PASSING_SCORE,
            'results'         => $grading['results'],
            'attempts'        => $row->attempts,
            'best_score'      => $row->best_score,
            'module_completed' => $updatedProgress[$module]['completed'],
            'next_unlocked'   => isset($updatedProgress[$module + 1]) ? $updatedProgress[$module + 1]['unlocked'] : false,
            'next_module'     => $module + 1 <= AgentTrainingCourseService::TOTAL_MODULES ? $module + 1 : null,
            'overall_percent' => AgentTrainingCourseService::overallPercent($updatedProgress),
            'completed_count' => AgentTrainingCourseService::completedCount($updatedProgress),
        ]);
    }
}