<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/ArkCrest_Logo.png') }}">
    <title>ArkCrest Sales Academy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;1,600&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy-950: #081523;
            --navy-900: #0d1f33;
            --navy-850: #112944;
            --navy-800: #17385f;
            --blue-700: #24558c;
            --gold: #d7aa48;
            --gold-light: #f4d98a;
            --cream: #f7f5ef;
            --white: #ffffff;
            --ink: #17263a;
            --muted: #708095;
            --line: #e3e8ef;
            --sidebar-width: 286px;
            --navbar-height: 76px;
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
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

        /* Course navbar — independent from the dashboard layout */
        .academy-navbar {
            position: fixed;
            inset: 0 0 auto 0;
            z-index: 1000;
            height: var(--navbar-height);
            display: flex;
            align-items: center;
            gap: 24px;
            padding: 0 28px;
            color: #fff;
            background: rgba(8, 21, 35, .97);
            border-bottom: 1px solid rgba(244, 217, 138, .22);
            box-shadow: 0 8px 30px rgba(8, 21, 35, .18);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }
        .academy-brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }
        .academy-brand img {
            width: 44px;
            height: 44px;
            padding: 2px;
            border-radius: 50%;
            object-fit: contain;
            background: #fff;
        }
        .brand-copy { line-height: 1.15; }
        .brand-copy strong {
            display: block;
            font-size: 13px;
            letter-spacing: 2.1px;
            text-transform: uppercase;
        }
        .brand-copy span {
            display: block;
            margin-top: 4px;
            color: var(--gold-light);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.4px;
            text-transform: uppercase;
        }
        .academy-nav-title {
            margin-left: 18px;
            padding-left: 24px;
            border-left: 1px solid rgba(255, 255, 255, .16);
            color: rgba(255, 255, 255, .72);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.7px;
            text-transform: uppercase;
        }
        .academy-nav-actions {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .nav-action {
            min-height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0 14px;
            border: 1px solid rgba(255, 255, 255, .18);
            border-radius: 9px;
            color: rgba(255, 255, 255, .88);
            background: rgba(255, 255, 255, .05);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .7px;
            text-transform: uppercase;
            cursor: pointer;
            transition: .2s ease;
        }
        .nav-action:hover {
            color: #fff;
            border-color: rgba(244, 217, 138, .55);
            background: rgba(244, 217, 138, .10);
        }
        .nav-action svg { width: 17px; height: 17px; }
        .user-chip {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-left: 4px;
            padding-left: 14px;
            border-left: 1px solid rgba(255, 255, 255, .15);
        }
        .user-avatar {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            color: #17263a;
            background: linear-gradient(135deg, var(--gold-light), var(--gold));
            font-weight: 900;
        }
        .user-copy { max-width: 170px; }
        .user-copy strong,
        .user-copy span {
            display: block;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        .user-copy strong { font-size: 12px; }
        .user-copy span { margin-top: 2px; color: rgba(255, 255, 255, .52); font-size: 10px; }
        .sidebar-toggle {
            display: none;
            width: 42px;
            height: 42px;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, .18);
            border-radius: 9px;
            color: #fff;
            background: rgba(255, 255, 255, .06);
            cursor: pointer;
        }
        .sidebar-toggle svg { width: 21px; height: 21px; }

        /* Course-only sidebar */
        .academy-sidebar {
            position: fixed;
            top: var(--navbar-height);
            bottom: 0;
            left: 0;
            z-index: 900;
            width: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            padding: 28px 20px 22px;
            overflow-y: auto;
            color: #fff;
            background:
                radial-gradient(circle at 20% 0%, rgba(36, 85, 140, .42), transparent 34%),
                linear-gradient(180deg, var(--navy-900), var(--navy-950));
            border-right: 1px solid rgba(255, 255, 255, .08);
        }
        .sidebar-label {
            margin: 0 10px 8px;
            color: var(--gold-light);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1.7px;
            text-transform: uppercase;
        }
        .sidebar-course-title {
            margin: 0 10px 22px;
            font-family: 'Playfair Display', serif;
            font-size: 23px;
            line-height: 1.25;
        }
        .sidebar-progress {
            margin: 0 4px 24px;
            padding: 15px;
            border: 1px solid rgba(255, 255, 255, .10);
            border-radius: 13px;
            background: rgba(255, 255, 255, .045);
        }
        .sidebar-progress-head {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 9px;
            color: rgba(255, 255, 255, .66);
            font-size: 11px;
        }
        .sidebar-progress-head strong { color: var(--gold-light); }
        .sidebar-progress-track {
            height: 7px;
            overflow: hidden;
            border-radius: 999px;
            background: rgba(255, 255, 255, .12);
        }
        .sidebar-progress-bar {
            width: 0;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--gold), var(--gold-light));
        }
        .course-navigation { display: grid; gap: 7px; }
        .course-link {
            display: grid;
            grid-template-columns: 32px 1fr auto;
            align-items: center;
            gap: 10px;
            min-height: 54px;
            padding: 8px 10px;
            border: 1px solid transparent;
            border-radius: 11px;
            color: rgba(255, 255, 255, .66);
            transition: .2s ease;
        }
        .course-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, .06);
        }
        .course-link.active {
            color: #fff;
            border-color: rgba(244, 217, 138, .26);
            background: linear-gradient(90deg, rgba(215, 170, 72, .17), rgba(255, 255, 255, .04));
        }
        .course-link-number {
            width: 30px;
            height: 30px;
            display: grid;
            place-items: center;
            border-radius: 9px;
            color: var(--gold-light);
            background: rgba(244, 217, 138, .10);
            font-size: 10px;
            font-weight: 900;
        }
        .course-link strong,
        .course-link small { display: block; }
        .course-link strong { font-size: 11px; line-height: 1.35; }
        .course-link small { margin-top: 3px; color: rgba(255, 255, 255, .40); font-size: 9px; }
        .course-lock { color: rgba(255, 255, 255, .28); }
        .course-lock svg { width: 14px; height: 14px; }
        .sidebar-footer {
            margin-top: auto;
            padding-top: 24px;
        }
        .sidebar-footer .nav-action { width: 100%; margin-top: 8px; }

        .academy-overlay {
            position: fixed;
            inset: var(--navbar-height) 0 0 0;
            z-index: 850;
            display: none;
            background: rgba(4, 12, 20, .58);
            backdrop-filter: blur(2px);
        }

        /* Page content */
        .academy-main {
            min-height: 100vh;
            margin-left: var(--sidebar-width);
            padding: calc(var(--navbar-height) + 30px) 34px 48px;
        }
        .academy-content { max-width: 1370px; margin: 0 auto; }
        .training-hero {
            position: relative;
            min-height: 360px;
            display: grid;
            grid-template-columns: minmax(0, 1.25fr) minmax(300px, .75fr);
            align-items: center;
            gap: 36px;
            overflow: hidden;
            padding: 46px 48px;
            border-radius: 22px;
            color: #fff;
            background:
                linear-gradient(100deg, rgba(8, 20, 35, .97) 0%, rgba(18, 48, 79, .92) 54%, rgba(18, 48, 79, .56) 100%),
                url('{{ asset('images/test-image2.jpg') }}') center / cover no-repeat;
            box-shadow: 0 18px 45px rgba(13, 26, 43, .22);
        }
        .training-hero::before,
        .training-hero::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(247, 223, 154, .15);
        }
        .training-hero::before { width: 360px; height: 360px; right: -120px; top: -155px; }
        .training-hero::after { width: 230px; height: 230px; right: 185px; bottom: -170px; }
        .training-copy,
        .course-overview { position: relative; z-index: 2; }
        .training-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
            color: var(--gold-light);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 2.2px;
            text-transform: uppercase;
        }
        .training-eyebrow::before { content: ''; width: 30px; height: 1px; background: currentColor; }
        .training-copy h1 {
            max-width: 820px;
            margin: 0 0 18px;
            font-family: 'Playfair Display', serif;
            font-size: clamp(36px, 4.5vw, 60px);
            font-weight: 600;
            line-height: 1.03;
        }
        .training-copy h1 em { color: var(--gold-light); font-weight: 600; }
        .training-copy p {
            max-width: 680px;
            margin: 0;
            color: rgba(255, 255, 255, .76);
            font-size: 14px;
            line-height: 1.8;
        }
        .training-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 28px;
        }
        .start-course-btn,
        .outline-course-btn {
            min-height: 47px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 0 22px;
            border-radius: 9px;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .8px;
            text-transform: uppercase;
            cursor: pointer;
            transition: .2s ease;
        }
        .start-course-btn {
            border: 1px solid var(--gold);
            color: #142033;
            background: linear-gradient(135deg, var(--gold-light), var(--gold));
            box-shadow: 0 10px 25px rgba(214, 169, 68, .23);
        }
        .start-course-btn:hover { transform: translateY(-2px); box-shadow: 0 14px 30px rgba(214, 169, 68, .31); }
        .outline-course-btn { border: 1px solid rgba(255, 255, 255, .35); color: #fff; background: rgba(255, 255, 255, .05); }
        .outline-course-btn:hover { background: rgba(255, 255, 255, .12); }
        .start-course-btn svg,
        .outline-course-btn svg { width: 17px; height: 17px; }
        .course-overview {
            padding: 26px;
            border: 1px solid rgba(255, 255, 255, .13);
            border-radius: 17px;
            background: rgba(7, 18, 31, .70);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        .overview-label { margin-bottom: 8px; color: rgba(255, 255, 255, .55); font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; }
        .overview-title { margin-bottom: 22px; font-size: 20px; font-weight: 800; }
        .progress-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; font-size: 12px; }
        .progress-head strong { color: var(--gold-light); }
        .progress-track { height: 8px; overflow: hidden; border-radius: 999px; background: rgba(255, 255, 255, .12); }
        .progress-bar { width: 0; height: 100%; border-radius: inherit; background: linear-gradient(90deg, var(--gold), var(--gold-light)); }
        .overview-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-top: 22px; }
        .overview-stat { padding: 13px 8px; border-radius: 10px; text-align: center; background: rgba(255, 255, 255, .06); }
        .overview-stat strong { display: block; margin-bottom: 2px; color: #fff; font-size: 16px; }
        .overview-stat span { color: rgba(255, 255, 255, .54); font-size: 9px; letter-spacing: .7px; text-transform: uppercase; }
        .course-notice { display: none; margin-top: 14px; padding: 12px 14px; border: 1px solid rgba(244, 217, 138, .45); border-radius: 9px; color: var(--gold-light); background: rgba(8, 21, 35, .82); font-size: 12px; }

        .training-section { scroll-margin-top: calc(var(--navbar-height) + 20px); margin-top: 30px; }
        .section-heading { display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; margin-bottom: 17px; }
        .section-heading h2 { margin: 0; color: #14243a; font-size: 23px; }
        .section-heading p { margin: 5px 0 0; color: var(--muted); font-size: 13px; }
        .mockup-badge { display: inline-flex; align-items: center; gap: 7px; padding: 8px 11px; border: 1px solid #eedb9f; border-radius: 999px; color: #946d16; background: #fff8e6; font-size: 10px; font-weight: 800; letter-spacing: .8px; text-transform: uppercase; }
        .mockup-badge::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: var(--gold); }
        .module-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
        .module-card { position: relative; overflow: hidden; padding: 20px; border: 1px solid var(--line); border-radius: 15px; background: #fff; box-shadow: 0 3px 14px rgba(20, 36, 58, .055); transition: .2s ease; }
        .module-card:hover { transform: translateY(-3px); border-color: #d8c080; box-shadow: 0 10px 25px rgba(20, 36, 58, .10); }
        .module-number { width: 38px; height: 38px; display: grid; place-items: center; margin-bottom: 17px; border-radius: 10px; color: var(--blue-700); background: #edf3fa; font-size: 13px; font-weight: 800; }
        .module-card:first-child .module-number { color: #8c6512; background: #fff5d8; }
        .module-card h3 { margin: 0 0 8px; color: #14243a; font-size: 15px; line-height: 1.35; }
        .module-card p { min-height: 58px; margin: 0; color: #778599; font-size: 12px; line-height: 1.65; }
        .module-meta { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-top: 18px; padding-top: 14px; border-top: 1px solid #edf0f4; color: #8c99a9; font-size: 10px; font-weight: 700; letter-spacing: .4px; text-transform: uppercase; }
        .module-status { color: #946d16; }
        .module-status.locked { color: #9aa4b1; }
        .learning-grid { display: grid; grid-template-columns: 1.35fr .65fr; gap: 18px; }
        .lesson-preview,
        .academy-panel { padding: 22px; border: 1px solid var(--line); border-radius: 16px; background: #fff; box-shadow: 0 3px 14px rgba(20, 36, 58, .055); }
        .lesson-preview { display: grid; grid-template-columns: 190px 1fr; align-items: center; gap: 22px; }
        .lesson-thumbnail { position: relative; height: 140px; overflow: hidden; border-radius: 12px; background: url('{{ asset('images/DSC_6783.jpg') }}') center / cover no-repeat; }
        .lesson-thumbnail::after { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(13, 26, 43, .12), rgba(13, 26, 43, .68)); }
        .play-button { position: absolute; z-index: 2; top: 50%; left: 50%; width: 48px; height: 48px; display: grid; place-items: center; transform: translate(-50%, -50%); border-radius: 50%; color: var(--blue-700); background: rgba(255, 255, 255, .92); box-shadow: 0 8px 20px rgba(0, 0, 0, .18); }
        .play-button svg { width: 19px; height: 19px; margin-left: 2px; }
        .lesson-kicker { margin-bottom: 6px; color: #a37929; font-size: 10px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; }
        .lesson-content h3 { margin: 0 0 8px; color: #14243a; font-size: 18px; }
        .lesson-content p { margin: 0; color: #778599; font-size: 12px; line-height: 1.65; }
        .lesson-list { display: grid; gap: 10px; margin-top: 15px; }
        .lesson-item { display: flex; align-items: center; gap: 9px; color: #536278; font-size: 11px; }
        .lesson-item span:first-child { width: 22px; height: 22px; display: grid; place-items: center; border-radius: 50%; color: var(--blue-700); background: #edf3fa; font-size: 9px; font-weight: 800; }
        .academy-panel h3 { margin: 0 0 6px; color: #14243a; font-size: 17px; }
        .academy-panel > p { margin: 0 0 18px; color: #778599; font-size: 12px; line-height: 1.6; }
        .feature-list { display: grid; gap: 12px; }
        .feature-row { display: flex; align-items: flex-start; gap: 11px; padding: 11px; border-radius: 10px; background: #f8fafc; }
        .feature-icon { width: 34px; height: 34px; display: grid; place-items: center; flex-shrink: 0; border-radius: 9px; color: var(--blue-700); background: #edf3fa; }
        .feature-icon svg { width: 17px; height: 17px; }
        .feature-row strong { display: block; margin-bottom: 2px; color: #26384f; font-size: 12px; }
        .feature-row span { display: block; color: #8995a5; font-size: 10px; line-height: 1.45; }

        @media (max-width: 1180px) {
            .training-hero { grid-template-columns: 1fr; }
            .course-overview { max-width: 700px; }
            .module-grid { grid-template-columns: repeat(2, 1fr); }
            .learning-grid { grid-template-columns: 1fr; }
            .academy-nav-title { display: none; }
            .user-copy { display: none; }
        }
        @media (max-width: 900px) {
            :root { --navbar-height: 68px; }
            .academy-navbar { padding: 0 16px; }
            .academy-brand img { width: 40px; height: 40px; }
            .brand-copy strong { font-size: 11px; letter-spacing: 1.5px; }
            .brand-copy span { font-size: 9px; }
            .sidebar-toggle { display: inline-flex; }
            .academy-sidebar { transform: translateX(-100%); transition: transform .25s ease; }
            .academy-sidebar.open { transform: translateX(0); }
            .academy-overlay.open { display: block; }
            .academy-main { margin-left: 0; padding: calc(var(--navbar-height) + 22px) 20px 40px; }
            .nav-dashboard-label { display: none; }
        }
        @media (max-width: 680px) {
            .academy-navbar { gap: 10px; }
            .brand-copy span { display: none; }
            .academy-nav-actions .nav-action:not(.dashboard-action) { display: none; }
            .user-chip { display: none; }
            .academy-main { padding-left: 14px; padding-right: 14px; }
            .training-hero { padding: 32px 23px; border-radius: 16px; }
            .training-copy h1 { font-size: 36px; }
            .overview-stats { grid-template-columns: 1fr; }
            .module-grid { grid-template-columns: 1fr; }
            .lesson-preview { grid-template-columns: 1fr; }
            .lesson-thumbnail { height: 190px; }
            .section-heading { align-items: flex-start; flex-direction: column; }
            .training-actions { align-items: stretch; }
            .start-course-btn,
            .outline-course-btn { width: 100%; }
        }
        @media (max-width: 430px) {
            .brand-copy strong { max-width: 150px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
            .dashboard-action { width: 42px; padding: 0; }
            .nav-dashboard-label { display: none; }
        }
    </style>
</head>
<body>
    @php
        $trainingUser = auth()->user();
        $trainingName = $trainingUser->preferred_address
            ? $trainingUser->preferred_address . ' ' . $trainingUser->name
            : $trainingUser->name;
        $trainingInitial = strtoupper(substr($trainingUser->name ?: 'A', 0, 1));
    @endphp

    <header class="academy-navbar">
        <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Open course navigation" aria-expanded="false">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        <a href="{{ route('landing') }}" class="academy-brand" aria-label="Go to ArkCrest landing page">
            <img src="{{ asset('images/ArkCrest_Logo.png') }}" alt="ArkCrest Realty logo">
            <span class="brand-copy">
                <strong>ArkCrest Realty</strong>
                <span>Sales Academy</span>
            </span>
        </a>

        <div class="academy-nav-title">Real Estate Agent Training</div>

        <nav class="academy-nav-actions" aria-label="Training page actions">
            <a href="{{ route('landing') }}" class="nav-action">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Public Site
            </a>
            <a href="{{ route('dashboard') }}" class="nav-action dashboard-action">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                <span class="nav-dashboard-label">Dashboard</span>
            </a>
            <div class="user-chip" title="{{ $trainingName }}">
                <span class="user-avatar">{{ $trainingInitial }}</span>
                <span class="user-copy">
                    <strong>{{ $trainingName }}</strong>
                    <span>{{ $trainingUser->email }}</span>
                </span>
            </div>
        </nav>
    </header>

    <aside class="academy-sidebar" id="academySidebar" aria-label="Course navigation">
        <p class="sidebar-label">Learning Path</p>
        <h2 class="sidebar-course-title">Real Estate Sales Foundations</h2>

        <div class="sidebar-progress">
            <div class="sidebar-progress-head"><span>Overall progress</span><strong>0%</strong></div>
            <div class="sidebar-progress-track"><div class="sidebar-progress-bar"></div></div>
        </div>

        <nav class="course-navigation">
            <a href="#module-01" class="course-link active">
                <span class="course-link-number">01</span>
                <span><strong>Sales Fundamentals</strong><small>3 lessons · 35 min</small></span>
                <span></span>
            </a>
            <a href="#module-02" class="course-link">
                <span class="course-link-number">02</span>
                <span><strong>Property Knowledge</strong><small>3 lessons · 45 min</small></span>
                <span class="course-lock"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></span>
            </a>
            <a href="#module-03" class="course-link">
                <span class="course-link-number">03</span>
                <span><strong>Client Qualification</strong><small>3 lessons · 40 min</small></span>
                <span class="course-lock"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></span>
            </a>
            <a href="#module-04" class="course-link">
                <span class="course-link-number">04</span>
                <span><strong>Site Visits</strong><small>3 lessons · 50 min</small></span>
                <span class="course-lock"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></span>
            </a>
            <a href="#module-05" class="course-link">
                <span class="course-link-number">05</span>
                <span><strong>Ethical Selling</strong><small>3 lessons · 45 min</small></span>
                <span class="course-lock"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></span>
            </a>
            <a href="#module-06" class="course-link">
                <span class="course-link-number">06</span>
                <span><strong>Closing & After-Sales</strong><small>3 lessons · 35 min</small></span>
                <span class="course-lock"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="{{ route('dashboard') }}" class="nav-action">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
                Back to Dashboard
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-action">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <div class="academy-overlay" id="academyOverlay"></div>

    <main class="academy-main">
        <div class="academy-content">
            <section class="training-hero">
                <div class="training-copy">
                    <div class="training-eyebrow">ArkCrest Sales Academy</div>
                    <h1>Build confidence. Master the process. <em>Close with integrity.</em></h1>
                    <p>
                        Welcome, {{ $trainingName }}. This training mockup introduces the future learning path for ArkCrest staff, sales associates, and real estate agents.
                    </p>
                    <div class="training-actions">
                        <button type="button" class="start-course-btn" onclick="startMockCourse()">
                            <svg fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            Start Course
                        </button>
                        <a href="#course-modules" class="outline-course-btn">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                            View Modules
                        </a>
                    </div>
                    <div id="courseNotice" class="course-notice">This is currently a course mockup. Lesson videos, quizzes, progress tracking, and certificates can be connected in the next phase.</div>
                </div>

                <aside class="course-overview">
                    <div class="overview-label">Your Learning Path</div>
                    <div class="overview-title">Real Estate Sales Foundations</div>
                    <div class="progress-head"><span>Course progress</span><strong>0%</strong></div>
                    <div class="progress-track"><div class="progress-bar"></div></div>
                    <div class="overview-stats">
                        <div class="overview-stat"><strong>6</strong><span>Modules</span></div>
                        <div class="overview-stat"><strong>18</strong><span>Lessons</span></div>
                        <div class="overview-stat"><strong>4.5h</strong><span>Duration</span></div>
                    </div>
                </aside>
            </section>

            <section class="training-section" id="course-modules">
                <div class="section-heading">
                    <div>
                        <h2>Course Modules</h2>
                        <p>A mock learning path designed around ArkCrest sales and client service standards.</p>
                    </div>
                    <div class="mockup-badge">Preview Version</div>
                </div>

                <div class="module-grid">
                    <article class="module-card" id="module-01">
                        <div class="module-number">01</div>
                        <h3>Real Estate Sales Fundamentals</h3>
                        <p>Understand the agent's role, the sales cycle, buyer expectations, and professional conduct.</p>
                        <div class="module-meta"><span>3 Lessons · 35 min</span><span class="module-status">Ready</span></div>
                    </article>
                    <article class="module-card" id="module-02">
                        <div class="module-number">02</div>
                        <h3>Property and Market Knowledge</h3>
                        <p>Present developments clearly, explain value drivers, and match properties to client goals.</p>
                        <div class="module-meta"><span>3 Lessons · 45 min</span><span class="module-status locked">Locked</span></div>
                    </article>
                    <article class="module-card" id="module-03">
                        <div class="module-number">03</div>
                        <h3>Client Discovery and Qualification</h3>
                        <p>Ask better questions, identify priorities, qualify leads, and prepare relevant recommendations.</p>
                        <div class="module-meta"><span>3 Lessons · 40 min</span><span class="module-status locked">Locked</span></div>
                    </article>
                    <article class="module-card" id="module-04">
                        <div class="module-number">04</div>
                        <h3>Site Visits and Property Presentation</h3>
                        <p>Prepare professional site visits and communicate features, benefits, and investment potential.</p>
                        <div class="module-meta"><span>3 Lessons · 50 min</span><span class="module-status locked">Locked</span></div>
                    </article>
                    <article class="module-card" id="module-05">
                        <div class="module-number">05</div>
                        <h3>Documentation and Ethical Selling</h3>
                        <p>Follow responsible documentation practices and protect the client through transparent communication.</p>
                        <div class="module-meta"><span>3 Lessons · 45 min</span><span class="module-status locked">Locked</span></div>
                    </article>
                    <article class="module-card" id="module-06">
                        <div class="module-number">06</div>
                        <h3>Closing and After-Sales Service</h3>
                        <p>Handle objections, guide the decision, complete the handoff, and maintain long-term relationships.</p>
                        <div class="module-meta"><span>3 Lessons · 35 min</span><span class="module-status locked">Locked</span></div>
                    </article>
                </div>
            </section>

            <section class="training-section">
                <div class="section-heading">
                    <div>
                        <h2>First Lesson Preview</h2>
                        <p>The content below is visual scaffolding for the future training experience.</p>
                    </div>
                </div>

                <div class="learning-grid">
                    <article class="lesson-preview">
                        <div class="lesson-thumbnail">
                            <div class="play-button">
                                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </div>
                        </div>
                        <div class="lesson-content">
                            <div class="lesson-kicker">Module 01 · Lesson 01</div>
                            <h3>The ArkCrest Standard of Client Service</h3>
                            <p>Learn how professional preparation, product knowledge, transparency, and timely follow-through shape a trusted client experience.</p>
                            <div class="lesson-list">
                                <div class="lesson-item"><span>1</span><span>Represent the brand professionally</span></div>
                                <div class="lesson-item"><span>2</span><span>Understand the client's real objective</span></div>
                                <div class="lesson-item"><span>3</span><span>Guide every next step with clarity</span></div>
                            </div>
                        </div>
                    </article>

                    <aside class="academy-panel">
                        <h3>Planned Course Features</h3>
                        <p>These items can be implemented when the final course materials and rules are ready.</p>
                        <div class="feature-list">
                            <div class="feature-row">
                                <div class="feature-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M4 6h9a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2z"/></svg></div>
                                <div><strong>Video Lessons</strong><span>Structured modules with lesson playback.</span></div>
                            </div>
                            <div class="feature-row">
                                <div class="feature-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                                <div><strong>Knowledge Checks</strong><span>Short quizzes after each learning section.</span></div>
                            </div>
                            <div class="feature-row">
                                <div class="feature-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
                                <div><strong>Completion Certificate</strong><span>Certificate generation after requirements are met.</span></div>
                            </div>
                        </div>
                    </aside>
                </div>
            </section>
        </div>
    </main>

    <script>
        (function () {
            var sidebar = document.getElementById('academySidebar');
            var overlay = document.getElementById('academyOverlay');
            var toggle = document.getElementById('sidebarToggle');
            var courseLinks = document.querySelectorAll('.course-link');

            function setSidebar(open) {
                sidebar.classList.toggle('open', open);
                overlay.classList.toggle('open', open);
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            }

            toggle.addEventListener('click', function () {
                setSidebar(!sidebar.classList.contains('open'));
            });
            overlay.addEventListener('click', function () { setSidebar(false); });

            courseLinks.forEach(function (link) {
                link.addEventListener('click', function () {
                    courseLinks.forEach(function (item) { item.classList.remove('active'); });
                    link.classList.add('active');
                    if (window.innerWidth <= 900) { setSidebar(false); }
                });
            });

            window.addEventListener('resize', function () {
                if (window.innerWidth > 900) { setSidebar(false); }
            });
        })();

        function startMockCourse() {
            var notice = document.getElementById('courseNotice');
            notice.style.display = 'block';
            notice.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    </script>
</body>
</html>
