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

                    @include('training-course-quiz', [
                        'module' => $moduleNumber,
                        'questions' => $questions,
                        'progress' => $module,
                        'passingScore' => $passingScore,
                    ])
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

    .crs-quiz { padding-top: 6px; border-top: 2px dashed #e3e8ef; }
    .crs-quiz-head { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; margin: 20px 0 4px; flex-wrap: wrap; }
    .crs-quiz h4 { margin: 0; color: #14243a; font-size: 15px; }
    .crs-quiz-sub { margin: 0 0 16px; color: var(--muted); font-size: 12px; }
    .crs-already-passed { display: inline-flex; align-items: center; gap: 6px; margin-bottom: 16px; padding: 8px 12px; border-radius: 999px; color: #2f8f4e; background: #eafaf0; font-size: 11.5px; font-weight: 700; }

    .crs-quiz-question { margin-bottom: 18px; padding: 14px 16px; border: 1px solid var(--line); border-radius: 12px; transition: border-color .15s ease; }
    .crs-quiz-question.crs-q-missing { border-color: #e0776f; background: #fff6f5; }
    .crs-quiz-q-text { margin: 0 0 12px; color: #26384f; font-size: 13px; font-weight: 600; line-height: 1.5; }
    .crs-quiz-options { display: grid; gap: 8px; }
    .crs-quiz-option { display: flex; align-items: flex-start; gap: 10px; padding: 10px 12px; border: 1px solid #e3e8ef; border-radius: 9px; cursor: pointer; font-size: 12.5px; color: #46536a; line-height: 1.5; transition: .15s ease; }
    .crs-quiz-option:hover { border-color: #cdd6e2; background: #fafbfd; }
    .crs-quiz-option input { margin-top: 2px; flex-shrink: 0; }
    .crs-quiz-option.is-correct { border-color: #7fd68a; background: #eafaf0; color: #1f5c31; }
    .crs-quiz-option.is-wrong { border-color: #e0776f; background: #fff1ef; color: #8c2f26; }

    .crs-quiz-error { margin-bottom: 14px; padding: 10px 14px; border-radius: 9px; color: #8c2f26; background: #fff1ef; font-size: 12px; }
    .crs-quiz-actions { display: flex; align-items: center; gap: 12px; }
    .crs-quiz-submit { padding: 11px 22px; border: 0; border-radius: 10px; color: #14243a; background: linear-gradient(120deg, var(--gold), var(--gold-light)); font-size: 12.5px; font-weight: 800; cursor: pointer; }
    .crs-quiz-submit:disabled { opacity: .6; cursor: default; }
    .crs-quiz-retake { padding: 10px 18px; border: 1px solid var(--line); border-radius: 10px; color: #26384f; background: #fff; font-size: 12px; font-weight: 700; cursor: pointer; }

    .crs-quiz-result { margin-top: 16px; padding: 16px 18px; border-radius: 12px; font-size: 13px; line-height: 1.6; }
    .crs-quiz-result.is-pass { color: #1f5c31; background: #eafaf0; border: 1px solid #b9e8c4; }
    .crs-quiz-result.is-fail { color: #8c2f26; background: #fff1ef; border: 1px solid #f3c3bd; }
    .crs-quiz-result strong { font-size: 18px; }
    .crs-unlock-note { margin-top: 8px; font-size: 11.5px; opacity: .8; }

    @media (max-width: 700px) {
        .crs-module-nav { flex-direction: column; }
        .crs-module-nav-next { justify-content: flex-start; text-align: left; }
        .crs-module-nav-next span { order: 0; }
    }
</style>

<script>
(function () {
    var csrfToken = document.querySelector('meta[name=csrf-token]').content;

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

    // ---- Quiz submission ----
    document.querySelectorAll('.crs-quiz-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var quizEl = form.closest('.crs-quiz');
            var moduleNum = quizEl.getAttribute('data-module');
            var questionEls = form.querySelectorAll('.crs-quiz-question');
            var answers = [];
            var missing = false;

            questionEls.forEach(function (qEl) {
                var checked = qEl.querySelector('input[type=radio]:checked');
                if (!checked) {
                    missing = true;
                    qEl.classList.add('crs-q-missing');
                } else {
                    qEl.classList.remove('crs-q-missing');
                    answers.push(parseInt(checked.value, 10));
                }
            });

            var errorBox = quizEl.querySelector('.crs-quiz-error');

            if (missing) {
                errorBox.textContent = 'Please answer every question before submitting.';
                errorBox.hidden = false;
                return;
            }
            errorBox.hidden = true;

            var submitBtn = form.querySelector('.crs-quiz-submit');
            submitBtn.disabled = true;
            var originalLabel = submitBtn.textContent;
            submitBtn.textContent = 'Grading…';

            fetch('/agent-training/module/' + moduleNum + '/quiz', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ answers: answers })
            })
            .then(function (res) {
                return res.json().then(function (data) { return { ok: res.ok, data: data }; });
            })
            .then(function (r) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalLabel;

                if (!r.ok) {
                    errorBox.textContent = r.data.message || 'Something went wrong. Please try again.';
                    errorBox.hidden = false;
                    return;
                }

                renderQuizResult(quizEl, questionEls, r.data);
            })
            .catch(function () {
                submitBtn.disabled = false;
                submitBtn.textContent = originalLabel;
                errorBox.textContent = 'Network error — please check your connection and try again.';
                errorBox.hidden = false;
            });
        });
    });

    function renderQuizResult(quizEl, questionEls, data) {
        data.results.forEach(function (r, i) {
            var qEl = questionEls[i];
            var optionLabels = qEl.querySelectorAll('.crs-quiz-option');
            optionLabels.forEach(function (label, oi) {
                label.classList.remove('is-correct', 'is-wrong');
                var input = label.querySelector('input');
                input.disabled = true;
                if (oi === r.correct) {
                    label.classList.add('is-correct');
                } else if (oi === r.selected && !r.is_correct) {
                    label.classList.add('is-wrong');
                }
            });
        });

        var resultEl = quizEl.querySelector('.crs-quiz-result');
        resultEl.hidden = false;
        resultEl.className = 'crs-quiz-result ' + (data.passed ? 'is-pass' : 'is-fail');

        var html = '<strong>' + data.score + '%</strong> — ' + data.correct + '/' + data.total + ' correct.<br>';
        html += data.passed
            ? 'Passed! This module is now marked complete.'
            : 'Not quite — you need ' + data.passing_score + '% to pass. Review the highlighted answers and try again.';
        resultEl.innerHTML = html;

        if (data.passed) {
            var note = document.createElement('div');
            note.className = 'crs-unlock-note';
            note.textContent = data.next_module ? 'Reloading — the next module will unlock…' : 'Reloading…';
            resultEl.appendChild(note);
            setTimeout(function () { window.location.reload(); }, 1600);
        } else {
            var retakeBtn = document.createElement('button');
            retakeBtn.type = 'button';
            retakeBtn.className = 'crs-quiz-retake';
            retakeBtn.style.marginTop = '12px';
            retakeBtn.textContent = 'Retake Quiz';
            retakeBtn.addEventListener('click', function () { resetQuiz(quizEl, questionEls); });
            resultEl.appendChild(retakeBtn);
        }
    }

    function resetQuiz(quizEl, questionEls) {
        questionEls.forEach(function (qEl) {
            qEl.classList.remove('crs-q-missing');
            qEl.querySelectorAll('.crs-quiz-option').forEach(function (label) {
                label.classList.remove('is-correct', 'is-wrong');
                var input = label.querySelector('input');
                input.disabled = false;
                input.checked = false;
            });
        });
        var resultEl = quizEl.querySelector('.crs-quiz-result');
        resultEl.hidden = true;
        resultEl.innerHTML = '';
    }
})();
</script>
@endpush
