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
        // first unlocked-but-not-yet-completed module, defaulting to Module 1.
        $continueModule = 1;
        foreach ($progress as $m) {
            if ($m['unlocked'] && !$m['completed']) {
                $continueModule = $m['number'];
                break;
            }
        }

        return view('training-course', [
            'progress'       => $progress,
            'quizzes'        => $quizzes,
            'overallPercent' => $overallPercent,
            'completedCount' => $completedCount,
            'passingScore'   => AgentTrainingCourseService::PASSING_SCORE,
            'continueModule' => $continueModule,
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

        $validated = $request->validate([
            'answers' => ['required', 'array', 'size:' . $questionCount],
            'answers.*' => ['required', 'integer', 'min:0', 'max:3'],
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
            'overall_percent' => AgentTrainingCourseService::overallPercent($updatedProgress),
            'completed_count' => AgentTrainingCourseService::completedCount($updatedProgress),
        ]);
    }
}