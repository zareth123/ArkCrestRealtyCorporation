@extends('layouts.academy')

@section('title', 'ArkCrest Sales Academy')

@section('content')
            @if (session('error'))
                <div class="crs-flash-error">{{ session('error') }}</div>
            @endif

            <section class="training-hero">
                <div class="training-copy">
                    <div class="training-eyebrow">ArkCrest Sales Academy</div>
                    <h1>Build confidence. Master the process. <em>Close with integrity.</em></h1>
                    <p>
                        Welcome, {{ $trainingName }}. This is the Real Estate Agent Training course — practical,
                        Philippine-focused lessons for every stage of the sales cycle, with a short quiz to confirm
                        your understanding before each next module unlocks.
                    </p>
                    <div class="training-actions">
                        <a href="{{ $allModulesCompleted ? route('practice') : route('agent-training.module', $continueModule) }}" class="start-course-btn">
                            <svg fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            {{ $allModulesCompleted ? 'Start Challenge' : ($completedCount > 0 ? 'Continue Course' : 'Start Course') }}
                        </a>
                        <a href="#course-modules" class="outline-course-btn">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                            View Modules
                        </a>
                    </div>
                </div>

                <aside class="course-overview">
                    <div class="overview-label">Your Learning Path</div>
                    <div class="overview-title">Real Estate Sales Foundations</div>
                    <div class="progress-head"><span>Course progress</span><strong>{{ $overallPercent }}%</strong></div>
                    <div class="progress-track"><div class="progress-bar" style="width: {{ $overallPercent }}%"></div></div>
                    <div class="overview-stats">
                        <div class="overview-stat"><strong>{{ $completedCount }}/6</strong><span>Completed</span></div>
                        <div class="overview-stat"><strong>6</strong><span>Live Now</span></div>
                        <div class="overview-stat"><strong>4.5h</strong><span>Full Course</span></div>
                    </div>
                </aside>
            </section>

            <section class="training-section" id="course-modules">
                <div class="section-heading">
                    <div>
                        <h2>Course Modules</h2>
                        <p>Complete each module's quiz to unlock the next one. Your progress is saved to your account. Each module now opens on its own page.</p>
                    </div>
                </div>

                <div class="module-grid">
                    @foreach ($progress as $m)
                        @php
                            $cardHref = $m['unlocked'] ? route('agent-training.module', $m['number']) : null;
                            $statusLabel = $m['completed'] ? 'Completed' : ($m['unlocked'] ? 'Ready' : ($m['implemented'] ? 'Locked' : 'Coming Soon'));
                            $statusClass = $m['completed'] ? 'is-complete' : (!$m['unlocked'] ? 'locked' : '');
                        @endphp
                        @if ($cardHref)
                            <a href="{{ $cardHref }}" class="module-card module-card-link" id="overview-module-{{ sprintf('%02d', $m['number']) }}">
                        @else
                            <article class="module-card" id="overview-module-{{ sprintf('%02d', $m['number']) }}">
                        @endif
                            <div class="module-number">{{ sprintf('%02d', $m['number']) }}</div>
                            <h3>{{ $m['title'] }}</h3>
                            <p>{{ $m['summary'] }}</p>
                            <div class="module-meta">
                                <span>{{ $m['lessons'] }} Lessons · {{ $m['minutes'] }} min</span>
                                <span class="module-status {{ $statusClass }}">{{ $statusLabel }}</span>
                            </div>
                        @if ($cardHref)
                            </a>
                        @else
                            </article>
                        @endif
                    @endforeach
                    <a href="{{ route('practice') }}" class="module-card module-card-link" id="module-07">
                        <div class="module-number module-number-challenge">CHALLENGE</div>
                        <h3>Persuasion Practice</h3>
                        <p>Practice live persuasion and closing skills against an AI buyer roleplay, then get scored feedback.</p>
                        <div class="module-meta"><span>AI Roleplay · Self-paced</span><span class="module-status">Ready</span></div>
                    </a>
                </div>
            </section>

            <section class="training-section">
                <div class="learning-grid crs-info-grid">
                    <aside class="academy-panel">
                        <h3>How This Course Works</h3>
                        <p>A short set of rules so grading and unlocking always feel fair and predictable.</p>
                        <div class="feature-list">
                            <div class="feature-row">
                                <div class="feature-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                                <div><strong>Pass at {{ $passingScore }}%</strong><span>Score {{ $passingScore }}% or higher on a module's quiz to complete it.</span></div>
                            </div>
                            <div class="feature-row">
                                <div class="feature-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></div>
                                <div><strong>Sequential unlocking</strong><span>Each module unlocks only after the previous one is completed.</span></div>
                            </div>
                            <div class="feature-row">
                                <div class="feature-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div>
                                <div><strong>Progress is saved</strong><span>Your scores and completions are tied to your account, not your browser — they persist after logout and across devices.</span></div>
                            </div>
                            <div class="feature-row">
                                <div class="feature-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></div>
                                <div><strong>Unlimited retakes</strong><span>Didn't pass? Retake the quiz right away — only your best score is kept.</span></div>
                            </div>
                        </div>
                    </aside>

                    <aside class="academy-panel">
                        <h3>Planned Course Features</h3>
                        <p>Coming as the remaining modules are finalized.</p>
                        <div class="feature-list">
                            <div class="feature-row">
                                <div class="feature-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M4 6h9a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2z"/></svg></div>
                                <div><strong>Video Lessons</strong><span>Structured modules with lesson playback.</span></div>
                            </div>
                            <div class="feature-row">
                                <div class="feature-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
                                <div><strong>Completion Certificate</strong><span>Certificate generation after all 6 modules are completed.</span></div>
                            </div>
                            <div class="feature-row">
                                <div class="feature-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5a4 4 0 11-8 0 4 4 0 018 0zm6 2a4 4 0 10-8 0"/></svg></div>
                                <div><strong>Team Leaderboard</strong><span>See how your progress compares across your sales team.</span></div>
                            </div>
                        </div>
                    </aside>
                </div>
            </section>

@endsection

@push('academy-scripts')
<style>
    .module-status.is-complete { color: #2f8f4e; }
    .module-number-challenge {
        width: auto;
        height: auto;
        min-width: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fdecea !important;
        color: #c0392b;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.05em;
        line-height: 1;
        padding: 5px 10px;
        border-radius: 999px;
    }
    .crs-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    @media (max-width: 900px) { .crs-info-grid { grid-template-columns: 1fr; } }

    .crs-flash-error { margin-bottom: 18px; padding: 12px 16px; border: 1px solid #f3c3bd; border-radius: 10px; color: #8c2f26; background: #fff1ef; font-size: 12.5px; }
</style>
@endpush