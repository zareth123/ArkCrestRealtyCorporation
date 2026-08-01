@extends('layouts.academy')

@section('title', 'Module ' . sprintf('%02d', $moduleNumber) . ' — ' . $module['title'] . ' · ArkCrest Sales Academy')

@section('content')
            <section class="crs-page">
                <a href="{{ route('agent-training') }}#overview-module-{{ sprintf('%02d', $moduleNumber) }}" class="crs-back-link">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Back to Course Overview
                </a>

                <div class="crs-progress-strip">
                    <span>Course progress</span>
                    <div class="crs-progress-strip-track"><div class="crs-progress-strip-bar" style="width: {{ $overallPercent }}%"></div></div>
                    <strong>{{ $completedCount }}/{{ $totalModules }} modules</strong>
                </div>

                <header class="crs-page-head">
                    <div class="crs-module-detail-badge">Module {{ sprintf('%02d', $moduleNumber) }} of {{ sprintf('%02d', $totalModules) }}</div>
                    <h1>{{ $module['title'] }}</h1>
                    <p>{{ $module['summary'] }}</p>
                    <div class="crs-page-head-meta">
                        <span>⏱ {{ $module['minutes'] }} min</span>
                        <span>{{ $module['lessons'] }} Lessons</span>
                        @if ($module['completed'])
                            <span class="crs-status crs-status-complete">✓ Completed · Best {{ $module['best_score'] }}%</span>
                        @else
                            <span class="crs-status crs-status-ready">Ready</span>
                        @endif
                    </div>
                </header>

                <div class="crs-module-body crs-module-body-standalone">
                    @include('training-modules.module-' . sprintf('%02d', $moduleNumber))

                    {{-- Exam entry card — the lesson page never shows quiz questions --}}
                    {{-- itself; it only opens the dedicated, distraction-free exam page. --}}
                    <div class="crs-exam-entry">
                        <div class="crs-exam-entry-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="crs-exam-entry-copy">
                            <h4>Module Exam</h4>
                            <p>{{ $questionCount }} questions &middot; Score at least {{ $passingScore }}% to pass{{ $nextModule ? ' and unlock Module ' . sprintf('%02d', $nextModule['number']) : '' }}.</p>
                            @if ($module['completed'])
                                <span class="crs-exam-entry-badge">✓ Completed — best score {{ $module['best_score'] }}% ({{ $module['attempts'] }} attempt{{ $module['attempts'] === 1 ? '' : 's' }})</span>
                            @endif
                        </div>
                        <a href="{{ route('agent-training.module.exam', $moduleNumber) }}" class="crs-exam-entry-btn">
                            {{ $module['completed'] ? 'Retake Exam' : 'Start Exam' }}
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>

                <nav class="crs-module-nav">
                    @if ($prevModule)
                        <a href="{{ route('agent-training.module', $prevModule['number']) }}" class="crs-module-nav-link crs-module-nav-prev">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            <span><small>Previous</small>Module {{ sprintf('%02d', $prevModule['number']) }} — {{ $prevModule['title'] }}</span>
                        </a>
                    @else
                        <a href="{{ route('agent-training') }}" class="crs-module-nav-link crs-module-nav-prev">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            <span><small>Back to</small>Course Overview</span>
                        </a>
                    @endif

                    @if ($nextModule)
                        <a href="{{ $nextModule['unlocked'] ? route('agent-training.module', $nextModule['number']) : route('agent-training') }}"
                           class="crs-module-nav-link crs-module-nav-next {{ $nextModule['unlocked'] ? '' : 'is-disabled' }}"
                           id="crsNextModuleLink">
                            <span><small>{{ $nextModule['unlocked'] ? 'Next' : 'Locked until you pass this quiz' }}</small>Module {{ sprintf('%02d', $nextModule['number']) }} — {{ $nextModule['title'] }}</span>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @else
                        <a href="{{ route('agent-training') }}" class="crs-module-nav-link crs-module-nav-next">
                            <span><small>Finish</small>Back to Course Overview</span>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @endif
                </nav>
            </section>
@endsection

@push('academy-scripts')
<style>
    .crs-page { max-width: 880px; margin: 0 auto; padding-bottom: 40px; }

    .crs-back-link { display: inline-flex; align-items: center; gap: 6px; margin-bottom: 18px; color: #536278; font-size: 12.5px; font-weight: 700; text-decoration: none; }
    .crs-back-link:hover { color: var(--blue-700); }
    .crs-back-link svg { width: 15px; height: 15px; }

    .crs-progress-strip { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; padding: 10px 16px; border: 1px solid var(--line); border-radius: 999px; background: #fff; font-size: 11.5px; color: #778599; font-weight: 700; }
    .crs-progress-strip-track { flex: 1; height: 6px; border-radius: 999px; background: #eef1f5; overflow: hidden; }
    .crs-progress-strip-bar { height: 100%; border-radius: 999px; background: linear-gradient(120deg, var(--gold), var(--gold-light)); }
    .crs-progress-strip strong { flex-shrink: 0; color: #26384f; }

    .crs-page-head { margin-bottom: 22px; padding: 24px 26px; border: 1px solid var(--line); border-radius: 16px; background: #fff; box-shadow: 0 3px 14px rgba(20, 36, 58, .055); }
    .crs-page-head .crs-module-detail-badge { display: inline-block; margin-bottom: 12px; }
    .crs-page-head h1 { margin: 0 0 8px; color: #14243a; font-size: 22px; }
    .crs-page-head p { margin: 0 0 14px; color: var(--muted); font-size: 13.5px; line-height: 1.6; }
    .crs-page-head-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 14px; color: #8c99a9; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .3px; }

    .crs-module-body-standalone { padding: 0; border-top: 0; }
    .crs-module-intro { margin: 0 0 20px; padding: 14px 16px; border-radius: 10px; color: #46536a; background: #f8fafc; font-size: 13px; line-height: 1.7; }

    .crs-module-nav { display: flex; gap: 14px; margin-top: 28px; }
    .crs-module-nav-link { display: flex; align-items: center; gap: 10px; flex: 1; padding: 14px 18px; border: 1px solid var(--line); border-radius: 14px; background: #fff; color: #26384f; text-decoration: none; font-size: 12.5px; font-weight: 700; transition: .15s ease; }
    .crs-module-nav-link:hover { border-color: #cdd6e2; background: #fafbfd; }
    .crs-module-nav-link svg { width: 18px; height: 18px; flex-shrink: 0; color: #9aa4b1; }
    .crs-module-nav-link small { display: block; margin-bottom: 2px; color: #9aa4b1; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .3px; }
    .crs-module-nav-next { justify-content: flex-end; text-align: right; }
    .crs-module-nav-next span { order: 1; }
    .crs-module-nav-link.is-disabled { opacity: .55; cursor: not-allowed; }

    .crs-objective { margin: 0 0 22px; padding: 12px 16px; border-left: 3px solid var(--gold); border-radius: 0 8px 8px 0; color: #536278; background: #fbf8f0; font-size: 12.5px; line-height: 1.6; }

    /* ---- Exam entry card: opens the dedicated, distraction-free exam page ---- */
    .crs-exam-entry { display: flex; align-items: center; gap: 18px; margin: 30px 0 6px; padding: 22px 24px; border: 1px solid var(--line); border-radius: 16px; background: #fff; box-shadow: 0 3px 14px rgba(20, 36, 58, .05); }
    .crs-exam-entry-icon { flex-shrink: 0; display: grid; place-items: center; width: 46px; height: 46px; border-radius: 50%; color: #fff; background: var(--blue-700); }
    .crs-exam-entry-icon svg { width: 22px; height: 22px; }
    .crs-exam-entry-copy { flex: 1; min-width: 0; }
    .crs-exam-entry-copy h4 { margin: 0 0 4px; color: #14243a; font-size: 15px; }
    .crs-exam-entry-copy p { margin: 0; color: var(--muted); font-size: 12px; line-height: 1.6; }
    .crs-exam-entry-badge { display: inline-flex; align-items: center; gap: 6px; margin-top: 8px; padding: 5px 11px; border-radius: 999px; color: #2f8f4e; background: #eafaf0; font-size: 11px; font-weight: 700; }
    .crs-exam-entry-btn { flex-shrink: 0; display: inline-flex; align-items: center; gap: 8px; padding: 13px 22px; border-radius: 10px; color: #14243a; background: linear-gradient(120deg, var(--gold), var(--gold-light)); font-size: 12.5px; font-weight: 800; white-space: nowrap; transition: .15s ease; }
    .crs-exam-entry-btn:hover { filter: brightness(1.03); }
    .crs-exam-entry-btn svg { width: 15px; height: 15px; }
    @media (max-width: 620px) {
        .crs-exam-entry { flex-direction: column; align-items: flex-start; text-align: left; }
        .crs-exam-entry-btn { width: 100%; justify-content: center; }
    }

    .crs-lesson { margin-bottom: 26px; padding-bottom: 26px; border-bottom: 1px solid #f0f2f5; }
    .crs-lesson:last-of-type { border-bottom: none; }
    .crs-lesson h3 { display: flex; align-items: center; gap: 10px; margin: 0 0 12px; color: #14243a; font-size: 15.5px; }
    .crs-lesson-num { flex-shrink: 0; padding: 4px 9px; border-radius: 6px; color: var(--blue-700); background: #edf3fa; font-size: 10px; font-weight: 800; letter-spacing: .4px; text-transform: uppercase; }
    .crs-lesson p { margin: 0 0 12px; color: #46536a; font-size: 13px; line-height: 1.75; }
    .crs-list { margin: 0 0 12px; padding-left: 20px; color: #46536a; font-size: 13px; line-height: 1.8; }
    .crs-list li { margin-bottom: 4px; }

    .crs-sub-label { display: inline-block; margin: 4px 0 10px; padding: 3px 9px; border-radius: 6px; color: #778599; background: #f4f6f9; font-size: 10px; font-weight: 800; letter-spacing: .4px; text-transform: uppercase; }

    .crs-pin-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 14px; margin: 6px 0 14px; }
    @media (max-width: 560px) { .crs-pin-grid { grid-template-columns: 1fr; } }
    .crs-pin { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 18px 14px; border: 1px solid var(--line); border-radius: 14px; background: #fff; cursor: pointer; text-align: center; transition: .15s ease; }
    .crs-pin:hover { border-color: #cdd6e2; background: #fafbfd; }
    .crs-pin.is-active { border-color: var(--blue-700); background: #edf3fa; }
    .crs-pin-icon { display: grid; place-items: center; width: 50px; height: 50px; border-radius: 50%; color: var(--blue-700); background: #f0f4fa; transition: .15s ease; }
    .crs-pin.is-active .crs-pin-icon { color: #fff; background: var(--blue-700); }
    .crs-pin-icon svg { width: 22px; height: 22px; }
    .crs-pin-label { font-size: 12.5px; font-weight: 700; color: #26384f; }
    .crs-pin-check { width: 16px; height: 16px; color: #c3cbd6; }
    .crs-pin.is-active .crs-pin-check { color: #2f8f4e; }

    .crs-pin-detail { display: none; margin: 0 0 18px; padding: 14px 16px; border: 1px solid #edf0f4; border-radius: 10px; color: #46536a; background: #f8fafc; font-size: 12.5px; line-height: 1.7; }
    .crs-pin-detail.is-open { display: block; }
    .crs-pin-detail strong { display: block; margin-bottom: 4px; color: #14243a; font-size: 13px; }
    .crs-pin-detail p { margin: 0; }

    .crs-compare-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin: 6px 0 16px; }
    @media (max-width: 700px) { .crs-compare-grid { grid-template-columns: 1fr; } }
    .crs-compare-card { padding: 16px 18px; border: 1px solid var(--line); border-radius: 14px; background: #fff; cursor: pointer; transition: .15s ease; }
    .crs-compare-card:hover { border-color: #cdd6e2; }
    .crs-compare-card.is-open { border-color: var(--blue-700); background: #fbfcfe; }
    .crs-compare-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
    .crs-compare-head strong { color: #14243a; font-size: 13px; }
    .crs-compare-toggle { width: 16px; height: 16px; flex-shrink: 0; color: #9aa4b1; transition: transform .2s ease; }
    .crs-compare-card.is-open .crs-compare-toggle { transform: rotate(45deg); }
    .crs-compare-body { display: none; margin-top: 10px; padding-top: 10px; border-top: 1px dashed #e3e8ef; }
    .crs-compare-card.is-open .crs-compare-body { display: block; }
    .crs-compare-body p { margin: 0; color: #46536a; font-size: 12.5px; line-height: 1.7; }

    .crs-cycle { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin: 14px 0 18px; }
    @media (max-width: 900px) { .crs-cycle { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 560px) { .crs-cycle { grid-template-columns: 1fr; } }
    .crs-cycle-step { padding: 12px; border-radius: 10px; background: #f8fafc; }
    .crs-cycle-step span { display: inline-grid; place-items: center; width: 20px; height: 20px; margin-bottom: 6px; border-radius: 50%; color: #fff; background: var(--blue-700); font-size: 10px; font-weight: 800; }
    .crs-cycle-step strong { display: block; margin-bottom: 3px; color: #14243a; font-size: 12px; }
    .crs-cycle-step small { display: block; color: #778599; font-size: 10.5px; line-height: 1.5; }

    .crs-scenario { margin: 0 0 14px; padding: 14px 16px; border: 1px solid #dfeadf; border-radius: 10px; background: #f3faf4; }
    .crs-scenario strong { display: block; margin-bottom: 6px; color: #276b3a; font-size: 11.5px; letter-spacing: .3px; text-transform: uppercase; }
    .crs-scenario p { margin: 0; color: #385c40; font-size: 12.5px; line-height: 1.65; }

    .crs-takeaway-line { padding: 12px 16px; border-radius: 10px; color: #1f3350; background: #edf3fa; font-size: 12.5px; line-height: 1.6; }

    .crs-callout { margin: 4px 0 24px; padding: 16px 18px; border: 1px solid #eedb9f; border-radius: 12px; background: #fff8e6; }
    .crs-callout strong { display: block; margin-bottom: 6px; color: #8c6512; font-size: 12.5px; }
    .crs-callout p { margin: 0; color: #6b5417; font-size: 12.5px; line-height: 1.65; }

    .crs-key-takeaways { margin-bottom: 26px; padding: 18px 20px; border-radius: 12px; background: #14243a; }
    .crs-key-takeaways h4 { margin: 0 0 10px; color: #f4d98a; font-size: 13px; letter-spacing: .3px; text-transform: uppercase; }
    .crs-key-takeaways ul { margin: 0; padding-left: 18px; color: #dce4ef; font-size: 12.5px; line-height: 1.8; }

    .crs-status { padding: 5px 10px; border-radius: 999px; font-size: 10px; }
    .crs-status-ready { color: #946d16; background: #fff8e6; }
    .crs-status-complete { color: #2f8f4e; background: #eafaf0; }
    .crs-module-detail-badge { flex-shrink: 0; padding: 7px 12px; border-radius: 999px; color: #8c6512; background: #fff5d8; font-size: 11px; font-weight: 800; letter-spacing: .5px; }

    @media (max-width: 700px) {
        .crs-module-nav { flex-direction: column; }
        .crs-module-nav-next { justify-content: flex-start; text-align: left; }
        .crs-module-nav-next span { order: 0; }
    }
</style>

<script>
(function () {
    // ---- Pin-select visual aids (tap an icon to reveal its detail panel) ----
    document.querySelectorAll('.crs-pin').forEach(function (pin) {
        pin.addEventListener('click', function () {
            var grid = pin.closest('.crs-pin-grid');
            var group = grid.getAttribute('data-group');
            var target = pin.getAttribute('data-target');

            grid.querySelectorAll('.crs-pin').forEach(function (p) { p.classList.remove('is-active'); });
            pin.classList.add('is-active');

            document.querySelectorAll('.crs-pin-detail[data-detail^="' + group + '-"]').forEach(function (d) {
                d.classList.remove('is-open');
            });
            var detail = document.querySelector('.crs-pin-detail[data-detail="' + target + '"]');
            if (detail) detail.classList.add('is-open');
        });
    });

    // ---- Compare cards (tap to reveal) ----
    document.querySelectorAll('.crs-compare-card').forEach(function (card) {
        card.addEventListener('click', function () {
            card.classList.toggle('is-open');
        });
    });

})();
</script>
@endpush