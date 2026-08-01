{{-- Exam Mode: one question at a time, Previous / Skip / Next-Finish --}}
{{-- navigation, its own progress bar. Grading happens entirely server-side --}}
{{-- via AgentTrainingCourseService::grade() — this partial only handles --}}
{{-- presentation, local answer state, and submission. --}}
{{-- --}}
{{-- This is the ONLY content on the dedicated exam page (training-course-exam --}}
{{-- .blade.php) — the lesson page itself never renders quiz questions, it --}}
{{-- just links here via a "Start Exam" button. --}}
{{-- --}}
{{-- Expects: $module (int), $questions (array of ['question'=>..,'options'=>[..]]), --}}
{{-- $progress (module progress array), $passingScore (int), $nextModule --}}
{{-- (progress array|null), $moduleUrl (string), $resultsUrl (string) --}}
<div
    class="crs-exam"
    data-module="{{ $module }}"
    data-passing="{{ $passingScore }}"
    data-submit-url="{{ url('/agent-training/module/' . $module . '/quiz') }}"
    data-results-url="{{ $resultsUrl }}"
>
    <script type="application/json" class="crs-exam-data">{!! json_encode($questions) !!}</script>

    <div class="crs-exam-intro">
        <div class="crs-exam-intro-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h4>Module {{ sprintf('%02d', $module) }} Exam</h4>
        <p class="crs-exam-intro-sub">{{ count($questions) }} questions &middot; Score at least {{ $passingScore }}% to pass{{ $nextModule ? ' and unlock Module ' . sprintf('%02d', $nextModule['number']) : '' }}.</p>

        @if ($progress['completed'])
            <div class="crs-exam-already-passed">✓ Already completed — best score {{ $progress['best_score'] }}% ({{ $progress['attempts'] }} attempt{{ $progress['attempts'] === 1 ? '' : 's' }})</div>
        @endif

        <p class="crs-exam-intro-rules">Once you begin, you're in Exam Mode: answer or skip each question to move forward, and skipped questions are marked incorrect and can't be answered later. Leaving before you finish will reset your progress — you'll start over from Question 1.</p>

        <button type="button" class="crs-exam-btn crs-exam-btn-primary crs-exam-start-btn">
            {{ $progress['completed'] ? 'Retake Exam' : 'Begin Exam' }}
        </button>
        <a href="{{ $moduleUrl }}" class="crs-exam-intro-return">Return to Module</a>
    </div>

    <div class="crs-exam-run" hidden>
        <div class="crs-exam-progress">
            <span class="crs-exam-qnum">Question 1 of {{ count($questions) }}</span>
            <div class="crs-exam-progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                <div class="crs-exam-progress-fill"></div>
            </div>
            <span class="crs-exam-progress-pct">0%</span>
        </div>

        <div class="crs-exam-card" tabindex="-1"></div>

        <div class="crs-exam-nav">
            <button type="button" class="crs-exam-btn crs-exam-btn-secondary crs-exam-prev">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Previous
            </button>
            <button type="button" class="crs-exam-btn crs-exam-btn-ghost crs-exam-skip">Skip</button>
            <button type="button" class="crs-exam-btn crs-exam-btn-primary crs-exam-next" disabled>Next</button>
        </div>
    </div>

    <div class="crs-exam-results" hidden></div>
</div>

<style>
    /* ---- Intro screen ---- */
    .crs-exam-intro { max-width: 460px; margin: 10px auto 0; padding: 34px 28px; border: 1px solid var(--line); border-radius: 18px; background: #fff; text-align: center; box-shadow: 0 6px 24px rgba(20, 36, 58, .06); }
    .crs-exam-intro-icon { display: grid; place-items: center; width: 56px; height: 56px; margin: 0 auto 16px; border-radius: 50%; color: #fff; background: var(--blue-700); }
    .crs-exam-intro-icon svg { width: 26px; height: 26px; }
    .crs-exam-intro h4 { margin: 0 0 8px; color: #14243a; font-size: 18px; }
    .crs-exam-intro-sub { margin: 0 0 14px; color: var(--muted); font-size: 12.5px; }
    .crs-exam-already-passed { display: inline-flex; align-items: center; gap: 6px; margin: 0 0 14px; padding: 8px 12px; border-radius: 999px; color: #2f8f4e; background: #eafaf0; font-size: 11.5px; font-weight: 700; }
    .crs-exam-intro-rules { margin: 0 0 22px; padding: 12px 14px; border-radius: 10px; color: #778599; background: #f8fafc; font-size: 11.5px; line-height: 1.6; text-align: left; }
    .crs-exam-intro-return { display: block; margin-top: 16px; color: #8c99a9; font-size: 11.5px; font-weight: 700; }
    .crs-exam-intro-return:hover { color: #536278; }

    /* ---- Buttons ---- */
    .crs-exam-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 13px 24px; border: 0; border-radius: 10px; font-size: 12.5px; font-weight: 800; cursor: pointer; text-decoration: none; transition: .15s ease; }
    .crs-exam-btn svg { width: 15px; height: 15px; flex-shrink: 0; }
    .crs-exam-btn-primary { color: #14243a; background: linear-gradient(120deg, var(--gold), var(--gold-light)); }
    .crs-exam-btn-primary:hover:not(:disabled) { filter: brightness(1.03); }
    .crs-exam-btn-secondary { color: #26384f; background: #fff; border: 1px solid var(--line); }
    .crs-exam-btn-secondary:hover:not(:disabled) { border-color: #cdd6e2; background: #fafbfd; }
    .crs-exam-btn-ghost { color: #778599; background: transparent; border: 1px solid transparent; }
    .crs-exam-btn-ghost:hover:not(:disabled) { color: #46536a; background: #f4f6f9; }
    .crs-exam-btn:disabled { opacity: .45; cursor: not-allowed; }

    /* ---- Progress ---- */
    .crs-exam-progress { display: flex; align-items: center; gap: 12px; max-width: 640px; margin: 0 auto 22px; font-size: 11.5px; font-weight: 700; color: #778599; }
    .crs-exam-qnum { flex-shrink: 0; white-space: nowrap; }
    .crs-exam-progress-track { flex: 1; height: 8px; border-radius: 999px; background: #eef1f5; overflow: hidden; }
    .crs-exam-progress-fill { height: 100%; width: 0; border-radius: 999px; background: linear-gradient(90deg, var(--blue-700), #3d78bd); transition: width .25s ease; }
    .crs-exam-progress-pct { flex-shrink: 0; color: #26384f; }

    /* ---- Question card ---- */
    .crs-exam-card { max-width: 640px; margin: 0 auto; padding: 30px 32px; border: 1px solid var(--line); border-radius: 18px; background: #fff; box-shadow: 0 6px 24px rgba(20, 36, 58, .06); outline: none; animation: crsExamFadeIn .25s ease; }
    @keyframes crsExamFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
    .crs-exam-qtext { margin: 0 0 22px; color: #14243a; font-size: 16px; font-weight: 700; line-height: 1.55; }
    .crs-exam-options { display: grid; gap: 10px; }
    .crs-exam-option { display: flex; align-items: flex-start; gap: 12px; padding: 14px 17px; border: 1px solid #e3e8ef; border-radius: 11px; cursor: pointer; font-size: 13.5px; color: #46536a; line-height: 1.5; transition: .15s ease; }
    .crs-exam-option:hover { border-color: #cdd6e2; background: #fafbfd; }
    .crs-exam-option.is-selected, .crs-exam-option:has(input:checked) { border-color: var(--blue-700); background: #edf3fa; color: #14243a; font-weight: 600; }
    .crs-exam-option input { margin-top: 2px; flex-shrink: 0; accent-color: var(--blue-700); }
    .crs-exam-option.is-locked { cursor: not-allowed; opacity: .6; }
    .crs-exam-skip-note { margin: 14px 0 0; padding: 10px 14px; border-radius: 9px; color: #8c6512; background: #fff8e6; font-size: 11.5px; font-weight: 600; }

    /* ---- Nav row ---- */
    .crs-exam-nav { display: flex; align-items: center; justify-content: space-between; gap: 10px; max-width: 640px; margin: 22px auto 0; }
    .crs-exam-nav .crs-exam-next { margin-left: auto; }

    .crs-exam-error { max-width: 460px; margin: 16px auto 0; padding: 14px 16px; border-radius: 10px; color: #8c2f26; background: #fff1ef; font-size: 12.5px; text-align: center; }
    .crs-exam-result-actions { display: flex; justify-content: center; gap: 12px; flex-wrap: wrap; max-width: 460px; margin: 18px auto 0; }

    @media (max-width: 560px) {
        .crs-exam-intro, .crs-exam-card { padding: 24px 20px; }
        .crs-exam-nav { flex-wrap: wrap; }
        .crs-exam-nav .crs-exam-next { margin-left: 0; order: 3; width: 100%; }
        .crs-exam-nav .crs-exam-prev, .crs-exam-nav .crs-exam-skip { flex: 1; }
    }
</style>

@push('academy-scripts')
<script>
(function () {
    var csrfToken = document.querySelector('meta[name=csrf-token]').content;

    document.querySelectorAll('.crs-exam').forEach(function (examEl) {
        var submitUrl = examEl.getAttribute('data-submit-url');
        var resultsUrl = examEl.getAttribute('data-results-url');
        var questions = JSON.parse(examEl.querySelector('.crs-exam-data').textContent);
        var total = questions.length;

        var introEl = examEl.querySelector('.crs-exam-intro');
        var runEl = examEl.querySelector('.crs-exam-run');
        var resultsEl = examEl.querySelector('.crs-exam-results');
        var startBtn = introEl.querySelector('.crs-exam-start-btn');

        var qNumEl = runEl.querySelector('.crs-exam-qnum');
        var trackEl = runEl.querySelector('.crs-exam-progress-track');
        var fillEl = runEl.querySelector('.crs-exam-progress-fill');
        var pctEl = runEl.querySelector('.crs-exam-progress-pct');
        var cardEl = runEl.querySelector('.crs-exam-card');
        var prevBtn = runEl.querySelector('.crs-exam-prev');
        var skipBtn = runEl.querySelector('.crs-exam-skip');
        var nextBtn = runEl.querySelector('.crs-exam-next');

        var state = null;

        function escapeHtml(str) {
            var div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function freshState() {
            return {
                current: 0,
                answers: new Array(total).fill(null),
                skipped: new Array(total).fill(false),
            };
        }

        // Fully discards the in-progress attempt — called when the learner
        // confirms "Leave and Reset". No incomplete attempt is ever sent to
        // the server, so resetting here is purely local UI state.
        function resetExamState() {
            state = freshState();
            resultsEl.hidden = true;
            resultsEl.innerHTML = '';
            runEl.hidden = true;
            introEl.hidden = false;
        }

        function startExam() {
            state = freshState();
            introEl.hidden = true;
            resultsEl.hidden = true;
            resultsEl.innerHTML = '';
            runEl.hidden = false;
            renderQuestion();

            // An attempt is now in progress — protect against leaving.
            if (window.ExamLeaveGuard) {
                window.ExamLeaveGuard.setResetCallback(resetExamState);
                window.ExamLeaveGuard.enable();
            }
        }

        function renderQuestion() {
            var i = state.current;
            var q = questions[i];
            var isSkipped = state.skipped[i];
            var selected = state.answers[i];

            qNumEl.textContent = 'Question ' + (i + 1) + ' of ' + total;
            var pct = Math.round((i / total) * 100);
            fillEl.style.width = pct + '%';
            pctEl.textContent = pct + '%';
            trackEl.setAttribute('aria-valuenow', pct);

            var html = '<p class="crs-exam-qtext">' + escapeHtml(q.question) + '</p>';
            html += '<div class="crs-exam-options" role="radiogroup" aria-label="Answer choices">';
            q.options.forEach(function (opt, oi) {
                var checked = selected === oi ? ' checked' : '';
                var disabled = isSkipped ? ' disabled' : '';
                var selClass = selected === oi ? ' is-selected' : '';
                html += '<label class="crs-exam-option' + (isSkipped ? ' is-locked' : '') + selClass + '">'
                    + '<input type="radio" name="examQ' + i + '" value="' + oi + '"' + checked + disabled + '>'
                    + '<span>' + escapeHtml(opt) + '</span>'
                    + '</label>';
            });
            html += '</div>';

            if (isSkipped) {
                html += '<p class="crs-exam-skip-note">You skipped this question — it\u2019s marked incorrect and can\u2019t be answered now.</p>';
            }

            cardEl.innerHTML = html;
            cardEl.focus();

            cardEl.querySelectorAll('input[type=radio]').forEach(function (input) {
                input.addEventListener('change', function () {
                    state.answers[i] = parseInt(input.value, 10);
                    cardEl.querySelectorAll('.crs-exam-option').forEach(function (label) {
                        var labelInput = label.querySelector('input');
                        label.classList.toggle('is-selected', labelInput.checked);
                    });
                    updateNavButtons();
                });
            });

            prevBtn.disabled = (i === 0);
            skipBtn.hidden = isSkipped || selected !== null;
            nextBtn.textContent = (i === total - 1) ? 'Finish' : 'Next';
            updateNavButtons();
        }

        function updateNavButtons() {
            var i = state.current;
            var canAdvance = state.skipped[i] || state.answers[i] !== null;
            nextBtn.disabled = !canAdvance;
        }

        function advance() {
            if (state.current === total - 1) {
                finishExam();
            } else {
                state.current += 1;
                renderQuestion();
            }
        }

        prevBtn.addEventListener('click', function () {
            if (state.current > 0) {
                state.current -= 1;
                renderQuestion();
            }
        });

        skipBtn.addEventListener('click', function () {
            state.skipped[state.current] = true;
            state.answers[state.current] = null;
            advance();
        });

        nextBtn.addEventListener('click', advance);

        examEl.addEventListener('keydown', function (e) {
            if (runEl.hidden) return;
            if (e.target && e.target.tagName === 'INPUT') return;
            if (e.key === 'ArrowRight' && !nextBtn.disabled) nextBtn.click();
            if (e.key === 'ArrowLeft' && !prevBtn.disabled) prevBtn.click();
        });

        function finishExam() {
            var payload = questions.map(function (_, i) {
                if (state.skipped[i]) return -1;
                return state.answers[i] !== null ? state.answers[i] : -1;
            });

            prevBtn.disabled = true;
            skipBtn.disabled = true;
            nextBtn.disabled = true;
            nextBtn.textContent = 'Grading…';

            fetch(submitUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ answers: payload }),
            })
                .then(function (res) {
                    return res.json().then(function (data) { return { ok: res.ok, data: data }; });
                })
                .then(function (r) {
                    if (!r.ok) {
                        showError(r.data.message || 'Something went wrong. Please try again.');
                        return;
                    }

                    // Submission succeeded and is already persisted server-side —
                    // the attempt is "finished", so leaving no longer needs
                    // confirmation. Hand off to the dedicated Exam Results page.
                    if (window.ExamLeaveGuard) window.ExamLeaveGuard.disable();

                    var skippedCount = r.data.results.filter(function (res) { return res.selected === -1; }).length;
                    var params = new URLSearchParams({
                        score: r.data.score,
                        correct: r.data.correct,
                        total: r.data.total,
                        skipped: skippedCount,
                        passed: r.data.passed ? '1' : '0',
                    });
                    window.location.href = resultsUrl + '?' + params.toString();
                })
                .catch(function () {
                    showError('Network error — please check your connection and try again.');
                });
        }

        function showError(message) {
            runEl.hidden = true;
            resultsEl.hidden = false;
            resultsEl.innerHTML = '<div class="crs-exam-error">' + escapeHtml(message) + '</div>'
                + '<div class="crs-exam-result-actions"><button type="button" class="crs-exam-btn crs-exam-btn-secondary crs-exam-retry-btn">Try Again</button></div>';
            resultsEl.querySelector('.crs-exam-retry-btn').addEventListener('click', function () {
                resultsEl.hidden = true;
                runEl.hidden = false;
                prevBtn.disabled = (state.current === 0);
                skipBtn.disabled = false;
                nextBtn.disabled = false;
                nextBtn.textContent = (state.current === total - 1) ? 'Finish' : 'Next';
                renderQuestion();
            });
        }

        startBtn.addEventListener('click', startExam);
    });
})();
</script>
@endpush
