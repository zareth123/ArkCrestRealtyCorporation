@extends('layouts.academy-exam')

@section('title', 'Exam Results — Module ' . sprintf('%02d', $moduleNumber) . ' · ArkCrest Sales Academy')
@section('exam-title', 'Module ' . sprintf('%02d', $moduleNumber) . ' · Exam Results')

@php
    $moduleUrl = route('agent-training.module', $moduleNumber);
    $retakeUrl = route('agent-training.module.exam', $moduleNumber);
    // Results pages are never protected — a finished attempt is already
    // saved, so the learner may freely return without a confirmation.
    $examExitUrl = $moduleUrl;
@endphp

@section('content')
    <div class="crs-exam-result-card {{ $passed ? 'is-pass' : 'is-fail' }}">
        <div class="crs-exam-result-score">{{ $correct }} / {{ $total }}</div>
        <div class="crs-exam-result-pct">{{ $score }}%</div>
        <div class="crs-exam-result-status">{{ $passed ? 'Passed' : 'Failed' }}</div>
        <div class="crs-exam-result-breakdown">
            <span>Correct: {{ $correct }}</span>
            <span>Incorrect: {{ $incorrect }}</span>
            <span>Skipped: {{ $skipped }}</span>
        </div>
    </div>

    <div class="crs-exam-result-meta">
        <div class="crs-exam-result-meta-item"><strong>{{ $module['best_score'] ?? $score }}%</strong><span>Best Score</span></div>
        <div class="crs-exam-result-meta-item"><strong>#{{ $attempts }}</strong><span>Attempt</span></div>
        <div class="crs-exam-result-meta-item"><strong>{{ $passingScore }}%</strong><span>Passing Score</span></div>
    </div>

    <div class="crs-exam-result-actions">
        @if ($passed && $nextModule && $nextModule['unlocked'])
            <a href="{{ route('agent-training.module', $nextModule['number']) }}" class="crs-exam-btn crs-exam-btn-primary">
                Continue to Next Module
            </a>
            <a href="{{ $moduleUrl }}" class="crs-exam-btn crs-exam-btn-secondary">Return to Module</a>
        @elseif ($passed)
            <a href="{{ $moduleUrl }}" class="crs-exam-btn crs-exam-btn-primary">Return to Module</a>
        @else
            <a href="{{ $retakeUrl }}" class="crs-exam-btn crs-exam-btn-primary">Retake Exam</a>
            <a href="{{ $moduleUrl }}" class="crs-exam-btn crs-exam-btn-secondary">Return to Module</a>
        @endif

        @if ($passed)
            <a href="{{ $retakeUrl }}" class="crs-exam-btn crs-exam-btn-ghost">Retake Exam</a>
        @endif
    </div>

    <style>
        .crs-exam-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 13px 24px; border: 0; border-radius: 10px; font-size: 12.5px; font-weight: 800; cursor: pointer; text-decoration: none; transition: .15s ease; }
        .crs-exam-btn-primary { color: #14243a; background: linear-gradient(120deg, var(--gold), var(--gold-light)); }
        .crs-exam-btn-primary:hover { filter: brightness(1.03); }
        .crs-exam-btn-secondary { color: #26384f; background: #fff; border: 1px solid var(--line); }
        .crs-exam-btn-secondary:hover { border-color: #cdd6e2; background: #fafbfd; }
        .crs-exam-btn-ghost { color: #778599; background: transparent; border: 1px solid transparent; }
        .crs-exam-btn-ghost:hover { color: #46536a; background: #f4f6f9; }

        .crs-exam-result-card { max-width: 460px; margin: 30px auto 0; padding: 34px 28px; border-radius: 18px; text-align: center; }
        .crs-exam-result-card.is-pass { color: #1f5c31; background: #eafaf0; border: 1px solid #b9e8c4; }
        .crs-exam-result-card.is-fail { color: #8c2f26; background: #fff1ef; border: 1px solid #f3c3bd; }
        .crs-exam-result-score { font-size: 30px; font-weight: 800; }
        .crs-exam-result-pct { margin-top: 2px; font-size: 15px; font-weight: 700; opacity: .85; }
        .crs-exam-result-status { display: inline-block; margin: 14px 0 18px; padding: 6px 18px; border-radius: 999px; font-size: 11.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .4px; background: rgba(255, 255, 255, .55); }
        .crs-exam-result-breakdown { display: flex; justify-content: center; gap: 16px; flex-wrap: wrap; font-size: 11.5px; font-weight: 700; }
        .crs-exam-result-breakdown span { padding: 5px 10px; border-radius: 999px; background: rgba(255, 255, 255, .55); }

        .crs-exam-result-meta { display: flex; justify-content: center; gap: 14px; max-width: 460px; margin: 18px auto 0; flex-wrap: wrap; }
        .crs-exam-result-meta-item { flex: 1; min-width: 110px; padding: 14px; border: 1px solid var(--line); border-radius: 12px; background: #fff; text-align: center; }
        .crs-exam-result-meta-item strong { display: block; color: #14243a; font-size: 16px; }
        .crs-exam-result-meta-item span { display: block; margin-top: 3px; color: #8c99a9; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .3px; }

        .crs-exam-result-actions { display: flex; justify-content: center; gap: 12px; flex-wrap: wrap; max-width: 460px; margin: 24px auto 0; }

        @media (max-width: 560px) {
            .crs-exam-result-card { padding: 26px 20px; }
            .crs-exam-result-actions { flex-direction: column; }
            .crs-exam-result-actions .crs-exam-btn { width: 100%; }
        }
    </style>
@endsection
