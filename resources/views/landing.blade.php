<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ArkCrest Realty | The Standard of Luxury Acquisition</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500;1,600;1,700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root{
    --navy-950:#0d1a2b;
    --navy-900:#132840;
    --navy-800:#1b3a5c;
    --navy-700:#264a70;
    --cream:#f7f5f0;
    --white:#ffffff;
    --orange:#d3652f;
    --orange-dark:#b8531f;
    --ink:#1b2733;
    --ink-soft:#5b6774;
    --line:#e3e0d8;
    --line-dark:rgba(255,255,255,0.12);
  }

  *{margin:0;padding:0;box-sizing:border-box;}

  html{scroll-behavior:smooth;}

  body{
    font-family:'Inter',sans-serif;
    color:var(--ink);
    background:var(--cream);
    line-height:1.6;
    -webkit-font-smoothing:antialiased;
  }

  h1,h2,h3{
    font-family:'Playfair Display',serif;
    line-height:1.15;
    font-weight:600;
  }

  em, .italic{ font-style:italic; font-weight:500; }

  a{ color:inherit; text-decoration:none; }

  img{ max-width:100%; display:block; }

  .eyebrow{
    font-size:12px;
    letter-spacing:3px;
    text-transform:uppercase;
    font-weight:600;
    color:var(--orange);
    display:inline-flex;
    align-items:center;
    gap:10px;
  }
  .eyebrow.on-dark{ color:#7fa8d6; }
  .eyebrow .rule{ display:inline-block; width:26px; height:1px; background:currentColor; opacity:.7; }

  .wrap{
    max-width:1240px;
    margin:0 auto;
    padding:0 40px;
  }

  section{ position:relative; }

  /* -------- image frames -------- */
  .ph{
    position:relative;
    display:flex;
    align-items:center;
    justify-content:center;
    text-align:center;
    background:
      repeating-linear-gradient(135deg, rgba(19,40,64,0.06) 0 12px, rgba(19,40,64,0.02) 12px 24px),
      linear-gradient(160deg,#dfe6ea,#c7d2d9);
    border:1px dashed #9fb0bd;
    color:#4a5a68;
    font-family:'Inter',sans-serif;
    font-size:13px;
    letter-spacing:.5px;
    overflow:hidden;
  }
  .ph span{
    background:rgba(255,255,255,0.85);
    padding:6px 14px;
    border-radius:3px;
    font-weight:600;
  }

  .ph img{
    width:100%;
    height:100%;
    display:block;
    object-fit:cover;
  }
  .ph.dark{
    background:
      repeating-linear-gradient(135deg, rgba(255,255,255,0.04) 0 12px, rgba(255,255,255,0.01) 12px 24px),
      linear-gradient(160deg,#1b3350,#0c1c30);
    border-color:#2c4a6b;
    color:#cdd9e3;
  }
  .ph.dark span{ background:rgba(10,20,32,0.65); color:#eaf0f5; }

  /* -------- NAV -------- */
  .nav{
    position:fixed;
    top:0; left:0; right:0;
    z-index:1000;
    padding:16px 0;
    background:rgba(13,26,43,0.94);
    border-bottom:1px solid rgba(255,255,255,0.10);
    box-shadow:0 8px 30px rgba(5,12,20,0.18);
    backdrop-filter:blur(12px);
    -webkit-backdrop-filter:blur(12px);
  }
  .nav .wrap{
    width:100%;
    max-width:none;
    padding:0 28px;
    display:grid;
    grid-template-columns:minmax(0,1fr) auto minmax(0,1fr);
    align-items:center;
    gap:24px;
    position:relative;
  }
  .brand{
    display:flex;
    align-items:center;
    gap:12px;
    color:#fff;
    flex-shrink:0;
    justify-self:start;
  }
  .brand .mark{
    width:38px; height:38px; border-radius:50%;
    background:#fff;
    object-fit:contain;
    padding:2px;
    flex-shrink:0;
  }
  .brand .name{
    font-size:14px; letter-spacing:3px; font-weight:700; text-transform:uppercase;
  }
  .nav-links{ display:flex; align-items:center; gap:32px; justify-self:center; }
  .nav-links > a{
    color:rgba(255,255,255,0.88);
    font-size:12px; letter-spacing:2px; text-transform:uppercase; font-weight:600;
    padding-bottom:8px;
    border-bottom:2px solid transparent;
    transition:.25s;
  }
  .nav-links > a.active, .nav-links > a:hover{ border-color:var(--orange); color:#fff; }
  .nav-actions{ display:flex; align-items:center; gap:10px; flex-shrink:0; justify-self:end; }
  .nav-mobile-actions{ display:none; }
  .btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    padding:12px 18px;
    font-size:11px; letter-spacing:1.5px; text-transform:uppercase; font-weight:700;
    border-radius:2px;
    transition:.25s;
    white-space:nowrap;
  }
  .btn svg{ width:15px; height:15px; }
  .btn-orange{ background:var(--orange); color:#fff; border:1px solid var(--orange); }
  .btn-orange:hover{ background:var(--orange-dark); border-color:var(--orange-dark); }
  .btn-outline{ border:1px solid rgba(255,255,255,0.5); color:#fff; }
  .btn-outline:hover{ background:rgba(255,255,255,0.1); border-color:#fff; }
  .btn-training{
    color:#0d1a2b;
    border:1px solid #e6bd62;
    background:linear-gradient(135deg,#f7df9a,#d6a944);
    box-shadow:0 7px 18px rgba(214,169,68,0.18);
  }
  .btn-training:hover{ transform:translateY(-1px); background:linear-gradient(135deg,#ffe7a2,#e0b54c); }


  /* -------- AUTHENTICATED USER MENU -------- */
  .ark-account-menu{
    position:relative;
    flex-shrink:0;
  }
  .ark-account-trigger{
    display:flex;
    align-items:center;
    gap:9px;
    min-width:190px;
    max-width:230px;
    padding:5px 9px 5px 5px;
    border:1px solid rgba(255,255,255,.16);
    border-radius:8px;
    background:rgba(5,16,29,.34);
    color:#fff;
    cursor:pointer;
    font-family:inherit;
    text-align:left;
    transition:.2s;
  }
  .ark-account-trigger:hover,
  .ark-account-menu.open .ark-account-trigger{
    border-color:rgba(247,223,154,.58);
    background:rgba(5,16,29,.58);
  }
  .ark-account-avatar{
    width:36px;
    height:36px;
    border-radius:50%;
    overflow:hidden;
    flex-shrink:0;
    display:flex;
    align-items:center;
    justify-content:center;
    background:linear-gradient(135deg,#f7df9a,#d6a944);
    color:#10223a;
    font-size:14px;
    font-weight:800;
    text-transform:uppercase;
  }
  .ark-account-avatar img{ width:100%; height:100%; object-fit:cover; }
  .ark-account-copy{ min-width:0; flex:1; }
  .ark-account-name,
  .ark-account-email{
    display:block;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
  }
  .ark-account-name{ color:#fff; font-size:11px; font-weight:700; line-height:1.25; }
  .ark-account-email{ color:rgba(255,255,255,.62); font-size:9px; line-height:1.2; margin-top:2px; }
  .ark-account-chevron{ width:15px; height:15px; flex-shrink:0; transition:transform .2s; }
  .ark-account-menu.open .ark-account-chevron{ transform:rotate(180deg); }
  .ark-account-dropdown{
    display:none;
    position:absolute;
    top:calc(100% + 10px);
    right:0;
    width:260px;
    overflow:hidden;
    border:1px solid #e2e8f0;
    border-radius:10px;
    background:#fff;
    box-shadow:0 18px 45px rgba(0,0,0,.28);
    z-index:1100;
  }
  .ark-account-menu.open .ark-account-dropdown{ display:block; }
  .ark-account-dropdown-head{
    padding:14px 16px;
    border-bottom:1px solid #edf1f5;
    background:#f8fafc;
  }
  .ark-account-dropdown-head strong,
  .ark-account-dropdown-head span{
    display:block;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
  }
  .ark-account-dropdown-head strong{ color:#132840; font-size:13px; }
  .ark-account-dropdown-head span{ color:#64748b; font-size:11px; margin-top:3px; }
  .ark-account-action{
    display:flex;
    align-items:center;
    gap:10px;
    padding:13px 16px;
    color:#173b63;
    font-size:12px;
    font-weight:700;
    text-decoration:none;
    letter-spacing:.2px;
    text-transform:none;
    transition:.18s;
  }
  .ark-account-action:hover{ background:#fff8e7; color:#9a6e12; }
  .ark-account-action svg{ width:18px; height:18px; flex-shrink:0; }

  .mobile-toggle{
    display:none;
    flex-direction:column;
    gap:5px;
    cursor:pointer;
    background:transparent;
    border:0;
    padding:8px;
  }
  .mobile-toggle span{ width:24px; height:2px; background:#fff; transition:.2s; }

  /* -------- HERO -------- */
  .hero{
    height:100vh;
    min-height:640px;
    position:relative;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#fff;
    overflow:hidden;
  }
  .hero .ph{ position:absolute; inset:0; }
  .hero::after{
    content:'';
    position:absolute; inset:0;
    background:linear-gradient(180deg, rgba(8,16,26,0.55) 0%, rgba(8,16,26,0.35) 40%, rgba(8,16,26,0.65) 100%);
  }
  .hero-content{ position:relative; z-index:2; text-align:center; max-width:880px; padding:0 24px; }
  .hero-content h1{
    font-size:clamp(34px,5.6vw,64px);
    color:#fff;
    margin-bottom:24px;
  }
  .hero-content h1 .line2{ display:block; font-style:normal; font-weight:700; }
  .hero-content p{
    font-size:16px;
    color:rgba(255,255,255,0.82);
    max-width:560px;
    margin:0 auto 34px;
  }
  .hero-cta{
    font-size:12px; letter-spacing:3px; text-transform:uppercase; font-weight:700;
    color:#fff;
    display:inline-flex; align-items:center; gap:14px;
  }
  .hero-cta .rule{ width:34px; height:1px; background:var(--orange); }

  /* -------- STATS (dark) -------- */
  .stats{
    background:var(--navy-950);
    color:#fff;
    padding:120px 0;
  }
  .section-head{ text-align:center; max-width:640px; margin:0 auto 60px; }
  .section-head .eyebrow{ justify-content:center; margin-bottom:18px; }
  .section-head h2{ font-size:clamp(28px,3.6vw,42px); margin-bottom:18px; }
  .section-head p{ color:var(--ink-soft); font-size:15px; }
  .stats .section-head p{ color:#a9b8c6; }
  .stats .section-head h2{ color:#fff; }

  .stat-row{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:2px;
    background:var(--line-dark);
  }
  .stat-card{
    background:#0f2035;
    padding:52px 36px;
    text-align:center;
  }
  .stat-card .num{
    font-family:'Playfair Display',serif;
    font-style:italic;
    font-weight:600;
    font-size:52px;
    color:#7fa8d6;
    margin-bottom:18px;
  }
  .stat-card .num sup{ font-size:24px; }
  .stat-card .divider{ width:34px; height:1px; background:rgba(255,255,255,0.25); margin:0 auto 18px; }
  .stat-card .label{ font-size:13px; letter-spacing:2px; text-transform:uppercase; font-weight:700; margin-bottom:14px; }
  .stat-card .desc{ font-size:14px; color:#a9b8c6; max-width:230px; margin:0 auto; }

  .carousel-arrow{
    position:absolute; top:50%; transform:translateY(-50%);
    width:44px; height:44px; border-radius:50%;
    border:1px solid rgba(255,255,255,0.3);
    display:flex; align-items:center; justify-content:center;
    color:#fff; cursor:pointer; z-index:5;
    background:rgba(255,255,255,0.03);
    transition:.2s;
  }
  .carousel-arrow:hover{ background:rgba(255,255,255,0.12); }
  .carousel-arrow.left{ left:24px; }
  .carousel-arrow.right{ right:24px; }
  .carousel-arrow.on-light{ border-color:rgba(19,40,64,0.25); color:var(--navy-900); }

  /* -------- ABOUT / HERITAGE -------- */
  .about{ padding:130px 0; background:var(--cream); }
  .about .grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:80px;
    align-items:center;
  }
  .about .ph{ aspect-ratio:4/3; border-radius:2px; }
  .about h2{ font-size:clamp(28px,3.4vw,40px); color:var(--navy-900); margin:18px 0 22px; }
  .about p{ color:var(--ink-soft); font-size:15px; max-width:460px; margin-bottom:30px; }
  .text-link{
    display:inline-flex; align-items:center; gap:12px;
    font-size:12px; letter-spacing:2px; text-transform:uppercase; font-weight:700;
    color:var(--navy-900); border-bottom:1px solid var(--navy-900); padding-bottom:6px;
  }
  .text-link .rule{ width:30px; height:1px; background:var(--orange); }

  /* -------- PORTFOLIO -------- */
  .portfolio{ background:var(--navy-800); color:#fff; padding:130px 0; }
  .portfolio-head{
    display:flex; align-items:flex-end; justify-content:space-between; margin-bottom:56px;
  }
  .portfolio-head .eyebrow{ color:var(--orange); margin-bottom:14px; }
  .portfolio-head h2{ font-size:clamp(30px,3.8vw,46px); color:#fff; }
  .portfolio-nav{ display:flex; gap:12px; }
  .portfolio-nav .carousel-arrow{ position:static; transform:none; }

  .estate-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:36px;
  }
  .estate-card .ph{ aspect-ratio:4/3; margin-bottom:22px; }
  .estate-card h3{ font-style:italic; font-size:22px; color:#fff; margin-bottom:6px; }
  .estate-card .tag{ font-size:11px; letter-spacing:2px; text-transform:uppercase; color:#9fb3c6; font-weight:600; }

  /* -------- PHILOSOPHY -------- */
  .philosophy{ padding:140px 0; background:var(--cream); overflow:hidden; }
  .philosophy .grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:70px;
    align-items:center;
  }
  .philosophy h2{ font-size:clamp(28px,3.6vw,42px); color:var(--navy-900); margin:18px 0 24px; }
  .philosophy p{ color:var(--ink-soft); font-size:15px; max-width:440px; margin-bottom:30px; }
  .collage{ display:grid; grid-template-columns:1.1fr 1fr; gap:22px; }
  .collage .col{ display:flex; flex-direction:column; gap:22px; }
  .collage .col:first-child{ margin-top:40px; }
  .collage .ph{ border-radius:2px; }
  .collage .tall{ aspect-ratio:3/4.6; }
  .collage .short{ aspect-ratio:4/3; }

  /* -------- DISTINCTION BANNER -------- */
  .distinction{
    padding:150px 0;
    color:#fff;
    text-align:center;
    position:relative;
    overflow:hidden;
  }
  .distinction .ph{ position:absolute; inset:0; }
  .distinction::after{
    content:'';
    position:absolute; inset:0;
    background:linear-gradient(180deg, rgba(11,28,48,0.72), rgba(11,28,48,0.82));
  }
  .distinction-content{ position:relative; z-index:2; max-width:740px; margin:0 auto; padding:0 24px; }
  .distinction h2{ font-size:clamp(28px,4vw,42px); color:#fff; margin:16px 0 22px; }
  .distinction p{ color:rgba(255,255,255,0.78); font-size:15px; max-width:560px; margin:0 auto 40px; }
  .pill-row{ display:flex; justify-content:center; gap:44px; flex-wrap:wrap; }
  .pill{ display:flex; align-items:center; gap:10px; font-size:12px; letter-spacing:1.5px; text-transform:uppercase; font-weight:700; }
  .pill .dot{ width:7px; height:7px; background:var(--orange); transform:rotate(45deg); }

  /* -------- WHY CHOOSE -------- */
  .why{ padding:140px 0; background:#fff; position:relative; }
  .why-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:0;
    border-top:1px solid var(--line);
  }
  .why-item{
    padding:44px 40px 44px 0;
    border-bottom:1px solid var(--line);
  }
  .why-grid > .why-item:nth-child(3n+2),
  .why-grid > .why-item:nth-child(3n+3){ padding-left:40px; border-left:1px solid var(--line); }
  .why-item .no{ font-size:12px; font-weight:700; color:var(--orange); letter-spacing:1px; margin-bottom:16px; display:block; }
  .why-item h3{ font-style:italic; font-size:20px; color:var(--navy-900); margin-bottom:12px; }
  .why-item p{ font-size:14px; color:var(--ink-soft); }

  /* -------- FOOTER CTA -------- */
  .cta-band{ background:var(--navy-950); color:#fff; padding:90px 0; text-align:center; }
  .cta-band h2{ color:#fff; font-size:clamp(26px,3.4vw,38px); margin-bottom:26px; }
  .cta-buttons{ display:flex; gap:18px; justify-content:center; flex-wrap:wrap; }

  footer{ background:#0a1622; color:#8b9aab; padding:56px 0 30px; font-size:13px; }
  footer .wrap{ display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:20px; }
  footer .brand{ color:#fff; }

  /* -------- RESPONSIVE -------- */
  @media (max-width:1200px){
    .nav .wrap{ padding-left:20px; padding-right:20px; gap:16px; }
    .nav-links{ gap:18px; }
    .brand .name{ font-size:12px; letter-spacing:2px; }
    .nav-actions{ gap:8px; }
    .nav-actions .btn{ padding:10px 12px; letter-spacing:1px; }
    .nav-actions .ark-account-trigger{
      width:42px;
      min-width:42px;
      max-width:42px;
      height:42px;
      padding:2px;
      justify-content:center;
      border-radius:50%;
    }
    .nav-actions .ark-account-avatar{ width:36px; height:36px; }
    .nav-actions .ark-account-copy,
    .nav-actions .ark-account-chevron{ display:none; }
  }
  @media (max-width:900px){
    .nav{ padding:13px 0; }
    .nav .wrap{
      display:flex;
      padding-left:20px;
      padding-right:20px;
      justify-content:space-between;
      gap:16px;
    }
    .nav-links{
      display:none;
      position:absolute;
      top:calc(100% + 13px);
      right:20px;
      width:min(310px, calc(100vw - 40px));
      flex-direction:column;
      align-items:stretch;
      gap:4px;
      padding:18px;
      background:rgba(13,26,43,0.99);
      border:1px solid rgba(255,255,255,0.10);
      border-radius:5px;
      box-shadow:0 18px 40px rgba(0,0,0,0.32);
    }
    .nav-links.open{ display:flex; }
    .nav-links > a{ width:100%; padding:10px 4px; }
    .nav-actions{ display:none; }
    .nav-mobile-actions{ display:grid; gap:9px; padding-top:12px; margin-top:6px; border-top:1px solid rgba(255,255,255,0.12); }
    .nav-mobile-actions .btn{ width:100%; }
    .nav-mobile-actions .ark-account-menu{ width:100%; }
    .nav-mobile-actions .ark-account-trigger{
      width:100%;
      max-width:none;
      min-width:0;
    }
    .nav-mobile-actions .ark-account-dropdown{
      position:static;
      width:100%;
      margin-top:8px;
      box-shadow:none;
    }
    .mobile-toggle{ display:flex; }
    .about .grid, .philosophy .grid{ grid-template-columns:1fr; gap:40px; }
    .stat-row{ grid-template-columns:1fr; }
    .estate-grid{ grid-template-columns:1fr; }
    .why-grid{ grid-template-columns:1fr; }
    .why-grid > .why-item{ padding-left:0 !important; border-left:none !important; }
    .portfolio-head{ flex-direction:column; align-items:flex-start; gap:20px; }
    .pill-row{ gap:22px; }
    footer .wrap{ flex-direction:column; text-align:center; }
  }
  @media (max-width:480px){
    .brand .name{ font-size:11px; letter-spacing:1.5px; }
    .brand .mark{ width:34px; height:34px; }
  }
</style>
</head>
<body>

  <!-- NAV -->
  <nav class="nav">
    <div class="wrap">
      <a href="{{ route('landing') }}" class="brand" aria-label="Go to ArkCrest landing page home">
        <img
            class="mark"
            src="{{ asset('images/ArkCrest_Logo.png') }}"
            alt="ArkCrest Realty logo"
        >
        <div class="name">ArkCrest Realty</div>
      </a>

      <div class="nav-links" id="landingNavLinks">
        <a href="#home" class="active">Home</a>
        <a href="#about">About</a>
        <a href="#services">Services</a>
        <a href="#portfolio">Portfolio</a>

        <div class="nav-mobile-actions">
          @auth
            <a href="{{ route('agent-training') }}" class="btn btn-training">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18s-3.332.477-4.5 1.253"/></svg>
              Agent Training
            </a>
            <a href="{{ route('dashboard') }}" class="btn btn-orange">Dashboard</a>

            <div class="ark-account-menu" data-account-menu>
              <button
                type="button"
                class="ark-account-trigger"
                data-account-trigger
                aria-haspopup="true"
                aria-expanded="false"
                title="Open profile menu"
              >
                <span class="ark-account-avatar">
                  @if(auth()->user()->avatar_url)
                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }} profile picture">
                  @else
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                  @endif
                </span>
                <span class="ark-account-copy">
                  <span class="ark-account-name">{{ auth()->user()->name ?? 'Staff' }}</span>
                  <span class="ark-account-email">{{ auth()->user()->email ?? '' }}</span>
                </span>
                <svg class="ark-account-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
              </button>
              <div class="ark-account-dropdown" data-account-dropdown>
                <div class="ark-account-dropdown-head">
                  <strong>{{ auth()->user()->name ?? 'Staff' }}</strong>
                  <span>{{ auth()->user()->email ?? '' }}</span>
                </div>
                <a href="{{ route('settings', ['panel' => 'profile']) }}" class="ark-account-action">
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 11l6.768-6.768a2.5 2.5 0 113.536 3.536L12.536 14.536A4 4 0 019.707 15.7L7 16l.3-2.707A4 4 0 018.464 10.464L9 11zm-2 9.5V19h4.5"/>
                  </svg>
                  Edit Profile Settings
                </a>
              </div>
            </div>
          @else
            <a href="{{ route('login') }}" class="btn btn-outline">Staff Login</a>
            <a href="#inquire" class="btn btn-orange">Inquire Now</a>
          @endauth
        </div>
      </div>

      <div class="nav-actions">
        @auth
          <a href="{{ route('agent-training') }}" class="btn btn-training">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18s-3.332.477-4.5 1.253"/></svg>
            Agent Training
          </a>
          <a href="{{ route('dashboard') }}" class="btn btn-orange">Dashboard</a>

          <div class="ark-account-menu" data-account-menu>
            <button
              type="button"
              class="ark-account-trigger"
              data-account-trigger
              aria-haspopup="true"
              aria-expanded="false"
              title="Open profile menu"
            >
              <span class="ark-account-avatar">
                @if(auth()->user()->avatar_url)
                  <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }} profile picture">
                @else
                  {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                @endif
              </span>
              <span class="ark-account-copy">
                <span class="ark-account-name">{{ auth()->user()->name ?? 'Staff' }}</span>
                <span class="ark-account-email">{{ auth()->user()->email ?? '' }}</span>
              </span>
              <svg class="ark-account-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>
            <div class="ark-account-dropdown" data-account-dropdown>
              <div class="ark-account-dropdown-head">
                <strong>{{ auth()->user()->name ?? 'Staff' }}</strong>
                <span>{{ auth()->user()->email ?? '' }}</span>
              </div>
              <a href="{{ route('settings', ['panel' => 'profile']) }}" class="ark-account-action">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 11l6.768-6.768a2.5 2.5 0 113.536 3.536L12.536 14.536A4 4 0 019.707 15.7L7 16l.3-2.707A4 4 0 018.464 10.464L9 11zm-2 9.5V19h4.5"/>
                </svg>
                Edit Profile Settings
              </a>
            </div>
          </div>
        @else
          <a href="{{ route('login') }}" class="btn btn-outline">Staff Login</a>
          <a href="#inquire" class="btn btn-orange">Inquire Now</a>
        @endauth
      </div>

      <button type="button" class="mobile-toggle" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="landingNavLinks">
        <span></span><span></span><span></span>
      </button>
    </div>
  </nav>

  <!-- HERO -->
  <section class="hero" id="home">
    <div class="ph">
      <img
        src="{{ asset('images/test-image2.jpg') }}"
        alt="ArkCrest premium estate landscape"
        loading="eager"
        fetchpriority="high"
      >
    </div>
    <div class="hero-content">
      <h1><em>The Standard of</em><span class="line2">Luxury Acquisition.</span></h1>
      <p>Curating high-yield, premium properties across strategic locations. Build your legacy on a foundation of trust and prestige.</p>
      <a href="#portfolio" class="hero-cta">Explore Collection <span class="rule"></span></a>
    </div>
  </section>

  <!-- STATS -->
  <section class="stats">
    <div class="wrap" style="position:relative;">
      <div class="carousel-arrow left"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg></div>
      <div class="carousel-arrow right"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg></div>
      <div class="section-head">
        <span class="eyebrow on-dark"><span class="rule"></span>Market Authority</span>
        <h2><em>Trusted by</em> Visionary Investors</h2>
        <p>We don't just broker land; we secure legacies. Our track record is built on the pillars of absolute transparency and high-yield strategic selection.</p>
      </div>
      <div class="stat-row">
        <div class="stat-card">
          <div class="num">10<sup>+</sup></div>
          <div class="divider"></div>
          <div class="label">Elite Regions</div>
          <div class="desc">Hand-picked territories vetted for maximum capital appreciation and security.</div>
        </div>
        <div class="stat-card">
          <div class="num">500<sup>+</sup></div>
          <div class="divider"></div>
          <div class="label">Legacy Partners</div>
          <div class="desc">Distinguished families and corporate entities who trust the ArkCrest standard.</div>
        </div>
        <div class="stat-card">
          <div class="num">100<sup>%</sup></div>
          <div class="divider"></div>
          <div class="label">Security Rating</div>
          <div class="desc">Every transaction is fully guided, legally bulletproof, and executed with total clarity.</div>
        </div>
      </div>
    </div>
  </section>

  <!-- ABOUT / HERITAGE -->
  <section class="about" id="about">
    <div class="wrap" style="position:relative;">
      <div class="carousel-arrow left on-light" style="left:-22px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg></div>
      <div class="carousel-arrow right on-light" style="right:-22px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg></div>
      <div class="grid">
        <div class="ph">
          <img
            src="{{ asset('images/DSC_6783.jpg') }}"
            alt="ArkCrest Realty team"
            loading="lazy"
          >
        </div>
        <div>
          <span class="eyebrow"><span class="rule"></span>Our Heritage</span>
          <h2>Legacy is defined <em>by where you stand.</em></h2>
          <p>ArkCrest Realty delivers more than land; we provide the blueprint for your future. Our amber-standard vetting ensures every property meets our strict criteria for growth, safety, and prestige.</p>
          <a href="#" class="text-link"><span class="rule"></span>Our Full Story</a>
        </div>
      </div>
    </div>
  </section>

  <!-- PORTFOLIO -->
  <section class="portfolio" id="portfolio">
    <div class="wrap">
      <div class="portfolio-head">
        <div>
          <span class="eyebrow">The Portfolio</span>
          <h2><em>Featured</em> Estates</h2>
        </div>
        <div class="portfolio-nav">
          <div class="carousel-arrow"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg></div>
          <div class="carousel-arrow"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg></div>
        </div>
      </div>
      <div class="estate-grid">
        <div class="estate-card">
          <div class="ph">
            <img
              src="{{ asset('images/test-image2.jpg') }}"
              alt="Manggas Estate"
              loading="lazy"
            >
          </div>
          <h3>Manggas Estate</h3>
          <div class="tag">Urban Heritage Reserve</div>
        </div>
        <div class="estate-card">
          <div class="ph">
            <img
              src="{{ asset('images/testing-image.jpg') }}"
              alt="Mountain View Hills"
              loading="lazy"
            >
          </div>
          <h3>Mountain View Hills</h3>
          <div class="tag">Skyline Sanctuary</div>
        </div>
        <div class="estate-card">
          <div class="ph">
            <img
              src="{{ asset('images/testing-image3.jpg') }}"
              alt="Lakeside Estates"
              loading="lazy"
            >
          </div>
          <h3>Lakeside Estates</h3>
          <div class="tag">Waterfront Legacy</div>
        </div>
      </div>
    </div>
  </section>

  <!-- PHILOSOPHY -->
  <section class="philosophy">
    <div class="wrap">
      <div class="grid">
        <div>
          <span class="eyebrow"><span class="rule"></span>ArkCrest Philosophy</span>
          <h2>We don't just sell land — <em>we build legacy and lifestyle.</em></h2>
          <p>Every property in our portfolio undergoes a rigorous selection process. We ensure lasting growth, strategic location advantage, and a foundation of security for the generations that follow.</p>
          <a href="#" class="text-link">Explore Our Vetting Process <span class="rule"></span></a>
        </div>
        <div class="collage">
          <div class="col">
            <div class="ph tall">
              <img
                src="{{ asset('images/DSC_6935.jpg') }}"
                alt="ArkCrest property presentation"
                loading="lazy"
              >
            </div>
          </div>
          <div class="col">
            <div class="ph short">
              <img
                src="{{ asset('images/DSC_6938.jpg') }}"
                alt="ArkCrest realty event"
                loading="lazy"
              >
            </div>
            <div class="ph short">
              <img
                src="{{ asset('images/DSC_6989.jpg') }}"
                alt="ArkCrest client milestone"
                loading="lazy"
              >
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- DISTINCTION BANNER -->
  <section class="distinction" id="services">
    <div class="ph dark">
      <img
        src="{{ asset('images/DSC_7067.jpg') }}"
        alt="ArkCrest Realty distinction"
        loading="lazy"
      >
    </div>
    <div class="distinction-content">
      <span class="eyebrow on-dark">The ArkCrest Distinction</span>
      <h2><em>More Than Property —</em><br>A Lifestyle Investment</h2>
      <p>Every estate within our portfolio is rigorously curated to deliver a rare combination of immediate comfort, long-term appreciation, and generational prestige.</p>
      <div class="pill-row">
        <div class="pill"><span class="dot"></span>Prime Locations</div>
        <div class="pill"><span class="dot"></span>High Growth Areas</div>
        <div class="pill"><span class="dot"></span>Secure Investment</div>
      </div>
    </div>
  </section>

  <!-- WHY CHOOSE -->
  <section class="why">
    <div class="wrap">
      <div class="section-head">
        <span class="eyebrow"><span class="rule"></span>Excellence Guaranteed<span class="rule"></span></span>
        <h2>Why Choose <em>ArkCrest</em></h2>
      </div>
      <div class="why-grid">
        <div class="why-item">
          <span class="no">01</span>
          <h3>Premium Locations</h3>
          <p>Strategically selected high-growth areas across the nation, ensuring your investment is positioned for maximum appreciation.</p>
        </div>
        <div class="why-item">
          <span class="no">02</span>
          <h3>Transparent Deals</h3>
          <p>Absolute clarity in every contract. We operate with zero hidden fees and fully secure, guided transactions from start to finish.</p>
        </div>
        <div class="why-item">
          <span class="no">03</span>
          <h3>Long-Term Value</h3>
          <p>We focus on "Legacy Lands" — properties designed to gain value over generations, providing security for you and your family.</p>
        </div>
        <div class="why-item">
          <span class="no">04</span>
          <h3>Vetted Ownership</h3>
          <p>Every square meter is rigorously checked for legal compliance and clean titles, giving you total peace of mind.</p>
        </div>
        <div class="why-item">
          <span class="no">05</span>
          <h3>Bespoke Consultation</h3>
          <p>Our experts don't just sell land; they provide tailored financial insights to align with your specific investment goals.</p>
        </div>
        <div class="why-item">
          <span class="no">06</span>
          <h3>Future-Ready Assets</h3>
          <p>Properties integrated with upcoming infrastructure developments, ensuring high demand and elite living standards.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="cta-band" id="inquire">
    <div class="wrap">
      <span class="eyebrow on-dark">Begin Your Legacy</span>
      <h2 style="margin-top:14px;">Ready to <em>secure your estate?</em></h2>
      <div class="cta-buttons">
        <a href="#" class="btn btn-orange">Inquire Now</a>
        <a href="#" class="btn btn-outline">View Portfolio</a>
      </div>
    </div>
  </section>

  <footer>
    <div class="wrap">
      <div class="brand" style="font-size:13px; letter-spacing:2px; text-transform:uppercase; font-weight:700;">ArkCrest Realty</div>
      <div>&copy; 2026 ArkCrest Realty Corporation. All rights reserved.</div>
    </div>
  </footer>

  <script>
    const toggle = document.querySelector('.mobile-toggle');
    const links = document.querySelector('.nav-links');

    toggle.addEventListener('click', () => {
      const isOpen = links.classList.toggle('open');
      toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    links.querySelectorAll('a').forEach((link) => {
      link.addEventListener('click', () => {
        links.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
      });
    });

    document.addEventListener('click', (event) => {
      if (!event.target.closest('.nav')) {
        links.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
      }
    });
  </script>


<script>
(function () {
    var menus = Array.prototype.slice.call(document.querySelectorAll('[data-account-menu]'));

    function closeMenu(menu) {
        menu.classList.remove('open');
        var trigger = menu.querySelector('[data-account-trigger]');
        if (trigger) trigger.setAttribute('aria-expanded', 'false');
    }

    menus.forEach(function (menu) {
        var trigger = menu.querySelector('[data-account-trigger]');
        if (!trigger) return;

        trigger.addEventListener('click', function (event) {
            event.stopPropagation();
            var willOpen = !menu.classList.contains('open');

            menus.forEach(function (otherMenu) {
                if (otherMenu !== menu) closeMenu(otherMenu);
            });

            menu.classList.toggle('open', willOpen);
            trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });

        menu.addEventListener('click', function (event) {
            event.stopPropagation();
        });
    });

    document.addEventListener('click', function () {
        menus.forEach(closeMenu);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') menus.forEach(closeMenu);
    });
})();
</script>

</body>
</html>