<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/ArkCrest_Logo.png') }}">
    <title>@yield('title', 'ArkCrest Sales Academy')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;1,600&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy-950: #081523;
            --navy-900: #0d1f33;
            --blue-700: #24558c;
            --gold: #d7aa48;
            --gold-light: #f4d98a;
            --cream: #f7f5ef;
            --ink: #17263a;
            --muted: #708095;
            --line: #e3e8ef;
            --exam-topbar-height: 64px;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            color: var(--ink);
            background: linear-gradient(180deg, #f8fafc 0%, var(--cream) 100%);
            -webkit-font-smoothing: antialiased;
        }
        a { color: inherit; text-decoration: none; }
        button { font: inherit; }

        /* ---- Distraction-free top bar: brand + exit only, no sidebar/breadcrumbs ---- */
        .exam-topbar {
            position: fixed;
            inset: 0 0 auto 0;
            z-index: 1000;
            height: var(--exam-topbar-height);
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 0 22px;
            color: #fff;
            background: rgba(8, 21, 35, .97);
            border-bottom: 1px solid rgba(244, 217, 138, .22);
            box-shadow: 0 8px 30px rgba(8, 21, 35, .18);
        }
        .exam-brand { display: inline-flex; align-items: center; gap: 10px; min-width: 0; }
        .exam-brand img { width: 32px; height: 32px; padding: 2px; border-radius: 50%; object-fit: contain; background: #fff; }
        .exam-brand-copy { line-height: 1.2; overflow: hidden; }
        .exam-brand-copy strong { display: block; font-size: 11.5px; letter-spacing: .5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .exam-brand-copy span { display: block; margin-top: 1px; color: rgba(255, 255, 255, .5); font-size: 9.5px; font-weight: 700; letter-spacing: .8px; text-transform: uppercase; }
        .exam-topbar-title {
            margin-left: 10px;
            padding-left: 18px;
            border-left: 1px solid rgba(255, 255, 255, .16);
            color: rgba(255, 255, 255, .78);
            font-size: 11.5px;
            font-weight: 800;
            letter-spacing: .4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .exam-exit-btn {
            margin-left: auto;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 14px;
            border: 1px solid rgba(255, 255, 255, .18);
            border-radius: 9px;
            color: rgba(255, 255, 255, .85);
            background: rgba(255, 255, 255, .05);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .4px;
            text-transform: uppercase;
            cursor: pointer;
            transition: .15s ease;
        }
        .exam-exit-btn:hover { color: #fff; border-color: rgba(244, 217, 138, .5); background: rgba(244, 217, 138, .10); }
        .exam-exit-btn svg { width: 14px; height: 14px; }

        .exam-main {
            min-height: 100vh;
            padding: calc(var(--exam-topbar-height) + 30px) 20px 48px;
        }
        .exam-shell { max-width: 720px; margin: 0 auto; }

        @media (max-width: 560px) {
            .exam-brand-copy span { display: none; }
            .exam-topbar-title { display: none; }
            .exam-main { padding: calc(var(--exam-topbar-height) + 20px) 14px 36px; }
        }

        /* ---- Leave Exam confirmation modal (shared by all Exam Mode pages) ---- */
        .exam-leave-overlay {
            position: fixed;
            inset: 0;
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(8, 12, 20, .58);
            backdrop-filter: blur(2px);
        }
        .exam-leave-overlay.is-open { display: flex; }
        .exam-leave-modal {
            width: 100%;
            max-width: 400px;
            padding: 26px 26px 22px;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 20px 60px rgba(8, 21, 35, .32);
            animation: examLeaveIn .18s ease;
        }
        @keyframes examLeaveIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        .exam-leave-modal h3 { margin: 0 0 10px; color: #14243a; font-size: 16px; }
        .exam-leave-modal p { margin: 0 0 20px; color: #536278; font-size: 12.5px; line-height: 1.65; }
        .exam-leave-actions { display: flex; flex-direction: column; gap: 10px; }
        .exam-leave-btn { display: inline-flex; align-items: center; justify-content: center; padding: 12px 16px; border-radius: 10px; border: 0; font-size: 12.5px; font-weight: 800; cursor: pointer; }
        .exam-leave-stay { color: #14243a; background: linear-gradient(120deg, var(--gold), var(--gold-light)); }
        .exam-leave-stay:hover { filter: brightness(1.03); }
        .exam-leave-reset { color: #8c2f26; background: #fff1ef; border: 1px solid #f3c3bd; }
        .exam-leave-reset:hover { background: #fbe4e1; }
    </style>
</head>
<body>

    <header class="exam-topbar">
        <span class="exam-brand" aria-hidden="true">
            <img src="{{ asset('images/ArkCrest_Logo.png') }}" alt="ArkCrest Realty logo">
            <span class="exam-brand-copy">
                <strong>ArkCrest Realty</strong>
                <span>Sales Academy</span>
            </span>
        </span>
        <span class="exam-topbar-title">@yield('exam-title', 'Exam Mode')</span>
        <a href="{{ $examExitUrl ?? route('agent-training') }}" class="exam-exit-btn" id="examExitBtn">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            Exit Exam
        </a>
    </header>

    <main class="exam-main">
        <div class="exam-shell">
            @yield('content')
        </div>
    </main>

    <div class="exam-leave-overlay" id="examLeaveOverlay" role="dialog" aria-modal="true" aria-labelledby="examLeaveTitle">
        <div class="exam-leave-modal">
            <h3 id="examLeaveTitle">Leave Exam?</h3>
            <p>Leaving this exam will reset your current attempt. Your answers, skipped questions, and exam progress will be permanently lost.</p>
            <div class="exam-leave-actions">
                <button type="button" class="exam-leave-btn exam-leave-stay" id="examLeaveStayBtn">Stay in Exam</button>
                <button type="button" class="exam-leave-btn exam-leave-reset" id="examLeaveResetBtn">Leave and Reset</button>
            </div>
        </div>
    </div>

    <script>
        // Generic "leave protection" guard shared by all Exam Mode pages.
        // Exam-taking JS (training-course-quiz.blade.php) calls
        // ExamLeaveGuard.enable() once an attempt starts, and .disable()
        // once it's submitted/graded or the learner is on a results page —
        // matching the spec's "only unfinished attempts require confirmation".
        window.ExamLeaveGuard = (function () {
            var active = false;
            var pendingHref = null;
            var resetCallback = null;
            var overlay = document.getElementById('examLeaveOverlay');
            var stayBtn = document.getElementById('examLeaveStayBtn');
            var resetBtn = document.getElementById('examLeaveResetBtn');

            function openModal(href) {
                pendingHref = href;
                overlay.classList.add('is-open');
            }
            function closeModal() {
                overlay.classList.remove('is-open');
                pendingHref = null;
            }

            stayBtn.addEventListener('click', closeModal);
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) closeModal();
            });
            resetBtn.addEventListener('click', function () {
                active = false;
                if (typeof resetCallback === 'function') resetCallback();
                var href = pendingHref;
                overlay.classList.remove('is-open');
                pendingHref = null;
                if (href) window.location.href = href;
            });

            // In-app navigation: any link on the page (Exit Exam button,
            // brand, etc.) while an attempt is in progress.
            document.addEventListener('click', function (e) {
                if (!active) return;
                var link = e.target.closest('a[href]');
                if (!link) return;
                if (link.target === '_blank') return;
                var href = link.getAttribute('href');
                if (!href || href.indexOf('javascript:') === 0 || href.charAt(0) === '#') return;
                e.preventDefault();
                openModal(link.href);
            });

            // Browser back / refresh / close-tab: browsers only allow a
            // native, non-customizable confirmation for these — showing our
            // own copy here isn't possible for security reasons, so this is
            // the standard behavior across LMS platforms.
            window.addEventListener('beforeunload', function (e) {
                if (!active) return;
                e.preventDefault();
                e.returnValue = '';
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && overlay.classList.contains('is-open')) closeModal();
            });

            return {
                enable: function () { active = true; },
                disable: function () { active = false; closeModal(); },
                isActive: function () { return active; },
                setResetCallback: function (fn) { resetCallback = fn; },
            };
        })();
    </script>

    @stack('academy-scripts')
</body>
</html>
