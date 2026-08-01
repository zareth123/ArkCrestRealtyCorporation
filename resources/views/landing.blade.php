<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ArkCrest Realty | The Standard of Luxury Acquisition</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500;1,600;1,700&family=Inter:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  :root{
    --navy-950:#0d1a2b;
    --navy-900:#132840;
    --navy-800:#1c3552;
    --cream:#f7f5f0;
    --parchment-line:#ded7c8;
    --clay:#bb5a2a;
    --clay-dark:#9c481f;
    --gold:#9c8054;
    --ink:#1b2733;
    --ink-soft:#5b6774;
    --line-dark:rgba(255,255,255,0.14);
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

  h1,h2,h3{ font-family:'Playfair Display',serif; line-height:1.15; font-weight:600; }
  em, .italic{ font-style:italic; font-weight:500; }
  a{ color:inherit; text-decoration:none; }
  .mono{ font-family:'IBM Plex Mono',monospace; }

  /* -------- SCROLL PROGRESS -------- */
  .scroll-progress{
    position:fixed; top:0; left:0; height:3px; width:0%;
    background:linear-gradient(90deg,var(--clay),var(--gold));
    z-index:2000; transition:width .1s linear;
  }

  /* -------- SCROLL REVEAL -------- */
  .reveal{ opacity:0; transform:translateY(32px); transition:opacity .9s cubic-bezier(.16,1,.3,1), transform .9s cubic-bezier(.16,1,.3,1); }
  .reveal.visible{ opacity:1; transform:translateY(0); }
  .reveal-stagger > *{ opacity:0; transform:translateY(26px); transition:opacity .8s cubic-bezier(.16,1,.3,1), transform .8s cubic-bezier(.16,1,.3,1); }
  .reveal-stagger.visible > *{ opacity:1; transform:translateY(0); }
  .reveal-stagger.visible > *:nth-child(1){ transition-delay:.05s; }
  .reveal-stagger.visible > *:nth-child(2){ transition-delay:.14s; }
  .reveal-stagger.visible > *:nth-child(3){ transition-delay:.23s; }
  .reveal-stagger.visible > *:nth-child(4){ transition-delay:.32s; }
  .reveal-stagger.visible > *:nth-child(5){ transition-delay:.41s; }
  .reveal-stagger.visible > *:nth-child(6){ transition-delay:.5s; }

  @media (prefers-reduced-motion: reduce){
    html{ scroll-behavior:auto; }
    .reveal, .reveal-stagger > *{ opacity:1 !important; transform:none !important; transition:none !important; }
    *{ transition-duration:.01ms !important; animation-duration:.01ms !important; }
  }

  .wrap{ max-width:1180px; margin:0 auto; padding:0 40px; }
  section{ position:relative; }

  .eyebrow{
    font-family:'IBM Plex Mono',monospace;
    font-size:11px; letter-spacing:3px; text-transform:uppercase; font-weight:500;
    color:var(--clay);
    display:inline-flex; align-items:center; gap:10px;
  }
  .eyebrow.on-dark{ color:var(--gold); }
  .eyebrow .rule{ display:inline-block; width:24px; height:1px; background:currentColor; opacity:.7; }

  /* -------- corner bracket motif (signature element) -------- */
  .bracketed{ position:relative; }
  .bracketed::before, .bracketed::after{
    content:''; position:absolute; width:22px; height:22px;
    border-color:var(--gold); opacity:.55;
  }
  .bracketed::before{ top:0; left:0; border-top:1px solid; border-left:1px solid; }
  .bracketed::after{ bottom:0; right:0; border-bottom:1px solid; border-right:1px solid; }
  .bracketed.on-dark::before, .bracketed.on-dark::after{ border-color:var(--gold); }
  .bracketed.on-light::before, .bracketed.on-light::after{ border-color:var(--clay); opacity:.4; }

  /* -------- NAV -------- */
  .nav{
    position:fixed; top:0; left:0; right:0; z-index:1000;
    padding:16px 0;
    background:rgba(13,26,43,0.94);
    border-bottom:1px solid rgba(255,255,255,0.10);
    box-shadow:0 8px 30px rgba(5,12,20,0.18);
    backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px);
    transition:padding .35s cubic-bezier(.16,1,.3,1), background .35s ease, box-shadow .35s ease;
  }
  .nav.scrolled{ padding:10px 0; background:rgba(11,22,37,0.98); box-shadow:0 10px 34px rgba(5,12,20,0.3); }
  .nav.scrolled .brand .mark{ width:32px; height:32px; }
  .nav .wrap{
    width:100%; max-width:none; padding:0 28px;
    display:grid; grid-template-columns:minmax(0,1fr) auto minmax(0,1fr);
    align-items:center; gap:24px; position:relative;
  }
  .brand{ display:flex; align-items:center; gap:12px; color:#fff; flex-shrink:0; justify-self:start; }
  .brand .mark{ width:38px; height:38px; border-radius:50%; background:#fff; object-fit:contain; padding:2px; flex-shrink:0; }
  .brand .name{ font-size:14px; letter-spacing:3px; font-weight:700; text-transform:uppercase; }
  .nav-links{ display:flex; align-items:center; gap:32px; justify-self:center; }
  .nav-links > a{
    color:rgba(255,255,255,0.88); font-size:12px; letter-spacing:2px; text-transform:uppercase; font-weight:600;
    padding-bottom:8px; border-bottom:2px solid transparent; transition:.25s;
  }
  .nav-links > a.active, .nav-links > a:hover{ border-color:var(--clay); color:#fff; }
  .nav-links > a.fb-link{ color:#7fa8d6; }
  .nav-actions{ display:flex; align-items:center; gap:10px; flex-shrink:0; justify-self:end; }
  .nav-mobile-actions{ display:none; }

  .btn{
    display:inline-flex; align-items:center; justify-content:center; gap:8px;
    padding:13px 20px; font-size:11px; letter-spacing:1.5px; text-transform:uppercase; font-weight:700;
    border-radius:2px; white-space:nowrap;
    transition:transform .3s cubic-bezier(.16,1,.3,1), box-shadow .3s cubic-bezier(.16,1,.3,1), background .25s ease, border-color .25s ease, color .25s ease;
  }
  .btn svg{ width:15px; height:15px; }
  .btn-clay{ background:var(--clay); color:#fff; border:1px solid var(--clay); }
  .btn-clay:hover{ background:var(--clay-dark); border-color:var(--clay-dark); }
  .btn-outline{ border:1px solid rgba(255,255,255,0.5); color:#fff; }
  .btn-outline:hover{ background:rgba(255,255,255,0.1); border-color:#fff; }
  .btn-outline-dark{ border:1px solid rgba(19,40,64,0.4); color:var(--navy-900); }
  .btn-outline-dark:hover{ background:rgba(19,40,64,0.06); border-color:var(--navy-900); }
  .btn-fb{ background:#1877F2; color:#fff; border:1px solid #1877F2; }
  .btn-fb:hover{ background:#1465d1; border-color:#1465d1; }
  .btn-training{
    color:#0d1a2b; border:1px solid var(--gold);
    background:linear-gradient(135deg,#f7df9a,#d6a944);
    box-shadow:0 7px 18px rgba(214,169,68,0.18);
  }
  .btn-training:hover{ background:linear-gradient(135deg,#ffe7a2,#e0b54c); }

  @media (hover:hover) and (pointer:fine){
    .btn-clay:hover{ transform:translateY(-2px); box-shadow:0 10px 22px rgba(187,90,42,0.32); }
    .btn-outline:hover, .btn-outline-dark:hover{ transform:translateY(-2px); }
    .btn-fb:hover{ transform:translateY(-2px); box-shadow:0 10px 22px rgba(24,119,242,0.3); }
    .btn-training:hover{ transform:translateY(-2px); box-shadow:0 12px 26px rgba(214,169,68,0.32); }
  }

  /* -------- AUTHENTICATED USER MENU -------- */
  .ark-account-menu{ position:relative; flex-shrink:0; }
  .ark-account-trigger{
    display:flex; align-items:center; gap:9px; min-width:190px; max-width:230px;
    padding:5px 9px 5px 5px; border:1px solid rgba(255,255,255,.16); border-radius:8px;
    background:rgba(5,16,29,.34); color:#fff; cursor:pointer; font-family:inherit; text-align:left; transition:.2s;
  }
  .ark-account-trigger:hover, .ark-account-menu.open .ark-account-trigger{
    border-color:rgba(247,223,154,.58); background:rgba(5,16,29,.58);
  }
  .ark-account-avatar{
    width:36px; height:36px; border-radius:50%; overflow:hidden; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    background:linear-gradient(135deg,#f7df9a,#d6a944); color:#10223a; font-size:14px; font-weight:800; text-transform:uppercase;
  }
  .ark-account-avatar img{ width:100%; height:100%; object-fit:cover; }
  .ark-account-copy{ min-width:0; flex:1; }
  .ark-account-name, .ark-account-email{ display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
  .ark-account-name{ color:#fff; font-size:11px; font-weight:700; line-height:1.25; }
  .ark-account-email{ color:rgba(255,255,255,.62); font-size:9px; line-height:1.2; margin-top:2px; }
  .ark-account-chevron{ width:15px; height:15px; flex-shrink:0; transition:transform .2s; }
  .ark-account-menu.open .ark-account-chevron{ transform:rotate(180deg); }
  .ark-account-dropdown{
    display:none; position:absolute; top:calc(100% + 10px); right:0; width:260px; overflow:hidden;
    border:1px solid #e2e8f0; border-radius:10px; background:#fff; box-shadow:0 18px 45px rgba(0,0,0,.28); z-index:1100;
  }
  .ark-account-menu.open .ark-account-dropdown{ display:block; }
  .ark-account-dropdown-head{ padding:14px 16px; border-bottom:1px solid #edf1f5; background:#f8fafc; }
  .ark-account-dropdown-head strong, .ark-account-dropdown-head span{ display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
  .ark-account-dropdown-head strong{ color:#132840; font-size:13px; }
  .ark-account-dropdown-head span{ color:#64748b; font-size:11px; margin-top:3px; }
  .ark-account-action{
    display:flex; align-items:center; gap:10px; padding:13px 16px; color:#173b63;
    font-size:12px; font-weight:700; text-decoration:none; letter-spacing:.2px; transition:.18s;
  }
  .ark-account-action:hover{ background:#fff8e7; color:#9a6e12; }
  .ark-account-action svg{ width:18px; height:18px; flex-shrink:0; }

  .mobile-toggle{ display:none; flex-direction:column; gap:5px; cursor:pointer; background:transparent; border:0; padding:8px; }
  .mobile-toggle span{ width:24px; height:2px; background:#fff; transition:.2s; }

  /* -------- HERO (text-only, no photography) -------- */
  .hero{
    min-height:100vh;
    display:flex; align-items:center; justify-content:center;
    background:
      radial-gradient(ellipse 900px 500px at 50% -10%, rgba(156,128,84,0.14), transparent 60%),
      var(--navy-950);
    color:#fff;
    padding:150px 24px 100px;
    overflow:hidden;
  }
  .hero-inner{
    position:relative; max-width:820px; margin:0 auto; text-align:center;
    padding:64px 48px;
  }
  .hero-inner::before, .hero-inner::after{
    content:''; position:absolute; width:36px; height:36px; border-color:var(--gold); opacity:.5;
  }
  .hero-inner::before{ top:0; left:0; border-top:1px solid; border-left:1px solid; }
  .hero-inner::after{ bottom:0; right:0; border-bottom:1px solid; border-right:1px solid; }
  .hero-content > *{ opacity:0; transform:translateY(24px); animation:heroRise .9s cubic-bezier(.16,1,.3,1) forwards; }
  .hero-content > .eyebrow{ animation-delay:.15s; justify-content:center; }
  .hero-content > h1{ animation-delay:.32s; }
  .hero-content > p{ animation-delay:.5s; }
  .hero-content > .hero-ctas{ animation-delay:.68s; }
  .hero-content > .hero-stats{ animation-delay:.85s; }
  @keyframes heroRise{ to{ opacity:1; transform:translateY(0); } }

  .hero-content h1{ font-size:clamp(32px,5vw,58px); color:#fff; margin:22px 0 26px; }
  .hero-content h1 .line2{ display:block; font-style:normal; font-weight:700; }
  .hero-content p{ font-size:16px; color:rgba(255,255,255,0.78); max-width:520px; margin:0 auto 38px; }

  .hero-ctas{ display:flex; gap:16px; justify-content:center; flex-wrap:wrap; margin-bottom:52px; }

  .hero-stats{
    display:flex; justify-content:center; gap:28px; flex-wrap:wrap;
    font-family:'IBM Plex Mono',monospace; font-size:11px; letter-spacing:1.5px; text-transform:uppercase;
    color:rgba(255,255,255,0.55); padding-top:28px; border-top:1px solid rgba(255,255,255,0.14);
  }
  .hero-stats span b{ color:var(--gold); font-weight:600; margin-right:6px; }

  /* -------- FACEBOOK CTA BAND (primary conversion point) -------- */
  .fb-band{ background:var(--navy-900); color:#fff; padding:100px 0; text-align:center; }
  .fb-band .eyebrow{ justify-content:center; margin-bottom:18px; }
  .fb-band h2{ font-size:clamp(28px,3.6vw,42px); color:#fff; max-width:680px; margin:0 auto 20px; }
  .fb-band p{ color:rgba(255,255,255,0.72); font-size:15px; max-width:560px; margin:0 auto 40px; }
  .fb-band-panel{
    position:relative; max-width:640px; margin:0 auto; padding:44px 40px;
    border:1px solid rgba(255,255,255,0.14); border-radius:4px;
    background:rgba(255,255,255,0.03);
  }
  .fb-band-panel::before, .fb-band-panel::after{ content:''; position:absolute; width:20px; height:20px; border-color:var(--gold); opacity:.5; }
  .fb-band-panel::before{ top:-1px; left:-1px; border-top:1px solid; border-left:1px solid; }
  .fb-band-panel::after{ bottom:-1px; right:-1px; border-bottom:1px solid; border-right:1px solid; }
  .fb-band-panel .fb-icon{ width:38px; height:38px; color:#1877F2; margin:0 auto 18px; }
  .fb-band-panel h3{ font-style:italic; font-size:24px; color:#fff; margin-bottom:10px; }
  .fb-band-panel p{ font-size:14px; color:rgba(255,255,255,0.65); margin-bottom:28px; }

  /* -------- ABOUT / HERITAGE (text two-column) -------- */
  .about{ padding:130px 0; background:var(--cream); }
  .about .grid{ display:grid; grid-template-columns:1.1fr .9fr; gap:80px; align-items:start; }
  .about h2{ font-size:clamp(28px,3.4vw,40px); color:var(--navy-900); margin:18px 0 22px; }
  .about p{ color:var(--ink-soft); font-size:15px; max-width:460px; margin-bottom:20px; }
  .text-link{
    display:inline-flex; align-items:center; gap:12px;
    font-size:12px; letter-spacing:2px; text-transform:uppercase; font-weight:700;
    color:var(--navy-900); border-bottom:1px solid var(--navy-900); padding-bottom:6px; margin-top:10px;
  }
  .text-link .rule{ width:30px; height:1px; background:var(--clay); }

  .ledger{ border-top:1px solid var(--parchment-line); }
  .ledger-item{
    display:flex; justify-content:space-between; align-items:baseline; gap:20px;
    padding:20px 0; border-bottom:1px solid var(--parchment-line);
  }
  .ledger-item .k{ font-size:14px; color:var(--navy-900); font-weight:600; }
  .ledger-item .v{ font-family:'IBM Plex Mono',monospace; font-size:13px; color:var(--clay); text-align:right; white-space:nowrap; }

  /* -------- WHY CHOOSE (deed-clause numbering) -------- */
  .why{ padding:130px 0; background:#fff; }
  .why-grid{ display:grid; grid-template-columns:repeat(3,1fr); gap:0; border-top:1px solid var(--parchment-line); }
  .why-item{ padding:40px 36px 40px 0; border-bottom:1px solid var(--parchment-line); transition:padding-left .35s cubic-bezier(.16,1,.3,1); }
  .why-grid > .why-item:nth-child(3n+2), .why-grid > .why-item:nth-child(3n+3){ padding-left:36px; border-left:1px solid var(--parchment-line); }
  .why-item .clause{ font-family:'IBM Plex Mono',monospace; font-size:11px; font-weight:500; color:var(--clay); letter-spacing:1px; margin-bottom:14px; display:block; transition:transform .35s cubic-bezier(.16,1,.3,1); }
  .why-item h3{ font-style:italic; font-size:19px; color:var(--navy-900); margin-bottom:10px; }
  .why-item p{ font-size:14px; color:var(--ink-soft); }
  @media (hover:hover) and (pointer:fine){
    .why-item:hover{ padding-left:14px; }
    .why-item:hover .clause{ transform:translateX(4px); }
  }

  /* -------- PHILOSOPHY -------- */
  .philosophy{ padding:130px 0; background:var(--navy-950); color:#fff; }
  .philosophy .inner{ max-width:760px; margin:0 auto; text-align:center; }
  .philosophy .eyebrow{ justify-content:center; margin-bottom:18px; }
  .philosophy h2{ font-size:clamp(26px,3.4vw,38px); color:#fff; margin-bottom:24px; }
  .philosophy p{ color:rgba(255,255,255,0.72); font-size:15px; max-width:560px; margin:0 auto 14px; }

  /* -------- FOOTER CTA -------- */
  .cta-band{ background:var(--cream); color:var(--navy-950); padding:110px 0; text-align:center; }
  .cta-band h2{ color:var(--navy-950); font-size:clamp(26px,3.4vw,38px); margin-bottom:26px; }
  .cta-band p{ color:var(--ink-soft); font-size:15px; max-width:480px; margin:0 auto 34px; }
  .cta-buttons{ display:flex; gap:18px; justify-content:center; flex-wrap:wrap; }

  footer{ background:#0a1622; color:#8b9aab; padding:56px 0 30px; font-size:13px; }
  footer .wrap{ display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:20px; }
  footer .brand{ color:#fff; }
  footer .foot-links{ display:flex; gap:22px; }
  footer .foot-links a{ color:#8b9aab; font-size:12px; letter-spacing:.5px; transition:color .2s; }
  footer .foot-links a:hover{ color:#fff; }

  /* -------- RESPONSIVE -------- */
  @media (max-width:1200px){
    .nav .wrap{ padding-left:20px; padding-right:20px; gap:16px; }
    .nav-links{ gap:18px; }
    .brand .name{ font-size:12px; letter-spacing:2px; }
    .nav-actions{ gap:8px; }
    .nav-actions .btn{ padding:10px 12px; letter-spacing:1px; }
    .nav-actions .ark-account-trigger{ width:42px; min-width:42px; max-width:42px; height:42px; padding:2px; justify-content:center; border-radius:50%; }
    .nav-actions .ark-account-avatar{ width:36px; height:36px; }
    .nav-actions .ark-account-copy, .nav-actions .ark-account-chevron{ display:none; }
  }
  @media (max-width:900px){
    .wrap{ padding:0 24px; }
    .nav{ padding:13px 0; }
    .nav .wrap{ display:flex; padding-left:20px; padding-right:20px; justify-content:space-between; gap:16px; }
    .nav-links{
      display:none; position:absolute; top:calc(100% + 13px); right:20px;
      width:min(310px, calc(100vw - 40px)); max-height:calc(100vh - 90px); overflow-y:auto; -webkit-overflow-scrolling:touch;
      flex-direction:column; align-items:stretch; gap:4px; padding:18px;
      background:rgba(13,26,43,0.99); border:1px solid rgba(255,255,255,0.10); border-radius:5px; box-shadow:0 18px 40px rgba(0,0,0,0.32);
    }
    .nav-links.open{ display:flex; }
    .nav-links > a{ width:100%; padding:14px 6px; font-size:13px; }
    .nav-actions{ display:none; }
    .nav-mobile-actions{ display:grid; gap:9px; padding-top:12px; margin-top:6px; border-top:1px solid rgba(255,255,255,0.12); }
    .nav-mobile-actions .btn{ width:100%; padding:14px 18px; }
    .nav-mobile-actions .ark-account-menu{ width:100%; }
    .nav-mobile-actions .ark-account-trigger{ width:100%; max-width:none; min-width:0; }
    .nav-mobile-actions .ark-account-dropdown{ position:static; width:100%; margin-top:8px; box-shadow:none; }
    .mobile-toggle{ display:flex; padding:10px; }

    .hero{ padding:130px 20px 80px; }
    .hero-inner{ padding:44px 24px; }
    .fb-band{ padding:76px 0; }
    .about{ padding:80px 0; }
    .why{ padding:80px 0; }
    .philosophy{ padding:90px 0; }
    .cta-band{ padding:76px 0; }

    .about .grid{ grid-template-columns:1fr; gap:40px; }
    .why-grid{ grid-template-columns:1fr; }
    .why-grid > .why-item{ padding-left:0 !important; border-left:none !important; padding:30px 0; }
    .cta-buttons{ gap:14px; }
    .cta-buttons .btn{ flex:1 1 200px; }
    footer .wrap{ flex-direction:column; text-align:center; }
  }
  @media (max-width:600px){
    .wrap{ padding:0 18px; }
    .hero-content p{ font-size:15px; margin-bottom:30px; }
    .hero-ctas{ flex-direction:column; align-items:center; }
    .hero-ctas .btn{ width:100%; max-width:280px; }
    .hero-stats{ flex-direction:column; gap:10px; align-items:center; }
    .fb-band-panel{ padding:34px 24px; }
    .cta-buttons{ flex-direction:column; }
    .cta-buttons .btn{ width:100%; flex:none; }
    .reveal{ transform:translateY(20px); }
    .reveal-stagger > *{ transform:translateY(18px); }
  }
  @media (max-width:480px){
    .brand .name{ font-size:11px; letter-spacing:1.5px; }
    .brand .mark{ width:34px; height:34px; }
    .wrap{ padding:0 16px; }
    .btn{ padding:13px 16px; }
  }
  @media (max-width:360px){
    .hero-content h1{ font-size:28px; }
    .brand .name{ display:none; }
  }
</style>
</head>
<body>

  <div class="scroll-progress" id="scrollProgress"></div>

  <!-- NAV -->
  <nav class="nav" id="siteNav">
    <div class="wrap">
      <a href="{{ route('landing') }}" class="brand" aria-label="Go to ArkCrest landing page home">
        <img class="mark" src="{{ asset('images/ArkCrest_Logo.png') }}" alt="ArkCrest Realty logo">
        <div class="name">ArkCrest Realty</div>
      </a>

      <div class="nav-links" id="landingNavLinks">
        <a href="#home" class="active">Home</a>
        <a href="#about">About</a>
        <a href="#why">Why Us</a>
        <a href="https://facebook.com/ArkCrestRealty" target="_blank" rel="noopener noreferrer" class="fb-link">Facebook</a>

        <div class="nav-mobile-actions">
          @auth
            <a href="{{ route('agent-training') }}" class="btn btn-training">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18s-3.332.477-4.5 1.253"/></svg>
              Agent Training
            </a>
            <a href="{{ route('dashboard') }}" class="btn btn-clay">Dashboard</a>

            <div class="ark-account-menu" data-account-menu>
              <button type="button" class="ark-account-trigger" data-account-trigger aria-haspopup="true" aria-expanded="false" title="Open profile menu">
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
          @endauth
        </div>
      </div>

      <div class="nav-actions">
        @auth
          <a href="{{ route('agent-training') }}" class="btn btn-training">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18s-3.332.477-4.5 1.253"/></svg>
            Agent Training
          </a>
          <a href="{{ route('dashboard') }}" class="btn btn-clay">Dashboard</a>

          <div class="ark-account-menu" data-account-menu>
            <button type="button" class="ark-account-trigger" data-account-trigger aria-haspopup="true" aria-expanded="false" title="Open profile menu">
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
        @endauth
      </div>

      <button type="button" class="mobile-toggle" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="landingNavLinks">
        <span></span><span></span><span></span>
      </button>
    </div>
  </nav>

  <!-- HERO (text only) -->
  <section class="hero" id="home">
    <div class="hero-inner">
      <div class="hero-content">
        <span class="eyebrow on-dark"><span class="rule"></span>ArkCrest Realty<span class="rule"></span></span>
        <h1><em>The Standard of</em><span class="line2">Luxury Acquisition.</span></h1>
        <p>We curate high-yield, premium land across strategically chosen regions — and we keep every current listing, photo, and price in one place: our Facebook page.</p>
        <div class="hero-ctas">
          <a href="https://facebook.com/ArkCrestRealty" target="_blank" rel="noopener noreferrer" class="btn btn-fb">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M22 12.06C22 6.51 17.52 2 12 2S2 6.51 2 12.06C2 17.06 5.66 21.2 10.44 21.95v-6.98H7.9v-2.9h2.54V9.85c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56v1.87h2.78l-.44 2.9h-2.34v6.98C18.34 21.2 22 17.06 22 12.06Z"/></svg>
            See Our Listings
          </a>
          <a href="https://m.me/ArkCrestRealty" target="_blank" rel="noopener noreferrer" class="btn btn-outline">Inquire Now</a>
        </div>
        <div class="hero-stats mono">
          <span><b>100%</b>Verified Titles</span>
        </div>
      </div>
    </div>
  </section>

  <!-- FACEBOOK CTA BAND -->
  <section class="fb-band">
    <div class="wrap">
      <span class="eyebrow on-dark"><span class="rule"></span>Current Listings</span>
      <h2>Every estate we represent — photos, pricing, and updates — lives on <em>Facebook.</em></h2>
      <p>We keep our page current so you're always looking at real, verified inventory instead of stale brochures.</p>
      <div class="fb-band-panel reveal">
        <svg viewBox="0 0 24 24" fill="currentColor" class="fb-icon"><path d="M22 12.06C22 6.51 17.52 2 12 2S2 6.51 2 12.06C2 17.06 5.66 21.2 10.44 21.95v-6.98H7.9v-2.9h2.54V9.85c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56v1.87h2.78l-.44 2.9h-2.34v6.98C18.34 21.2 22 17.06 22 12.06Z"/></svg>
        <h3>ArkCrest Realty on Facebook</h3>
        <p>Follow along or message our team directly for the full portfolio and property details.</p>
        <a href="https://facebook.com/ArkCrestRealty" target="_blank" rel="noopener noreferrer" class="btn btn-fb">Visit Our Facebook Page</a>
      </div>
    </div>
  </section>

  <!-- ABOUT / HERITAGE -->
  <section class="about" id="about">
    <div class="wrap">
      <div class="grid">
        <div class="reveal">
          <span class="eyebrow"><span class="rule"></span>Our Heritage</span>
          <h2>Legacy is defined <em>by where you stand.</em></h2>
          <p>ArkCrest Realty delivers more than land; we provide the blueprint for your future. Our amber-standard vetting ensures every property meets our strict criteria for growth, safety, and prestige.</p>
          <p>We don't just sell land — we build legacy and lifestyle. Every property undergoes a rigorous selection process to ensure lasting growth, strategic location advantage, and a foundation of security for the generations that follow.</p>
          <a href="#" class="text-link"><span class="rule"></span>Our Full Story</a>
        </div>
        <div class="ledger reveal">
          <div class="ledger-item"><span class="k">Founded</span><span class="v mono">EST. 2024</span></div>
          <div class="ledger-item"><span class="k">Title Verification</span><span class="v mono">100% CLEARED</span></div>
          <div class="ledger-item"><span class="k">Current Listings</span><span class="v mono">VIA FACEBOOK</span></div>
        </div>
      </div>
    </div>
  </section>

  <!-- WHY CHOOSE (deed-clause style) -->
  <section class="why" id="why">
    <div class="wrap">
      <div class="section-head reveal" style="text-align:center; max-width:640px; margin:0 auto 60px;">
        <span class="eyebrow" style="justify-content:center; margin-bottom:18px;"><span class="rule"></span>Excellence Guaranteed<span class="rule"></span></span>
        <h2 style="font-size:clamp(28px,3.6vw,42px); color:var(--navy-900);">Why Choose <em>ArkCrest</em></h2>
      </div>
      <div class="why-grid reveal-stagger">
        <div class="why-item">
          <span class="clause">§ 01</span>
          <h3>Premium Locations</h3>
          <p>Strategically selected high-growth areas across the nation, ensuring your investment is positioned for maximum appreciation.</p>
        </div>
        <div class="why-item">
          <span class="clause">§ 02</span>
          <h3>Transparent Deals</h3>
          <p>Absolute clarity in every contract. We operate with zero hidden fees and fully secure, guided transactions from start to finish.</p>
        </div>
        <div class="why-item">
          <span class="clause">§ 03</span>
          <h3>Long-Term Value</h3>
          <p>We focus on "Legacy Lands" — properties designed to gain value over generations, providing security for you and your family.</p>
        </div>
        <div class="why-item">
          <span class="clause">§ 04</span>
          <h3>Vetted Ownership</h3>
          <p>Every square meter is rigorously checked for legal compliance and clean titles, giving you total peace of mind.</p>
        </div>
        <div class="why-item">
          <span class="clause">§ 05</span>
          <h3>Bespoke Consultation</h3>
          <p>Our experts don't just sell land; they provide tailored financial insights to align with your specific investment goals.</p>
        </div>
        <div class="why-item">
          <span class="clause">§ 06</span>
          <h3>Always-Current Listings</h3>
          <p>Rather than stale brochures, our Facebook page is updated in real time with the properties we currently represent.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- PHILOSOPHY -->
  <section class="philosophy" id="services">
    <div class="wrap">
      <div class="inner reveal">
        <span class="eyebrow on-dark"><span class="rule"></span>The ArkCrest Distinction<span class="rule"></span></span>
        <h2><em>More than property</em> — a lifestyle investment.</h2>
        <p>Every estate we represent is rigorously curated to deliver a rare combination of immediate comfort, long-term appreciation, and generational prestige.</p>
        <p>Prime locations. High-growth areas. Secure, fully guided transactions from inquiry to title transfer.</p>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="cta-band" id="inquire">
    <div class="wrap reveal" style="text-align:center;">
      <span class="eyebrow">Begin Your Legacy</span>
      <h2 style="margin-top:14px;">Ready to <em>secure your estate?</em></h2>
      <p>Message us on Facebook to browse current listings, or send an inquiry and a consultant will reach out within one business day.</p>
      <div class="cta-buttons">
        <a href="https://m.me/ArkCrestRealty" target="_blank" rel="noopener noreferrer" class="btn btn-clay">Inquire Now</a>
        <a href="https://facebook.com/ArkCrestRealty" target="_blank" rel="noopener noreferrer" class="btn btn-outline-dark">Visit Our Facebook Page</a>
      </div>
    </div>
  </section>

  <footer>
    <div class="wrap">
      <div class="brand" style="font-size:13px; letter-spacing:2px; text-transform:uppercase; font-weight:700;">ArkCrest Realty</div>
      <div class="foot-links">
        <a href="https://facebook.com/ArkCrestRealty" target="_blank" rel="noopener noreferrer">Facebook</a>
        <a href="#about">About</a>
        <a href="#why">Why Us</a>
      </div>
      <div>&copy; 2026 ArkCrest Realty Corporation. All rights reserved.</div>
    </div>
  </footer>

  <script>
  (function () {
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var progressBar = document.getElementById('scrollProgress');
    var nav = document.getElementById('siteNav');

    function onScroll() {
      var doc = document.documentElement;
      var scrollTop = doc.scrollTop || document.body.scrollTop;
      var scrollHeight = (doc.scrollHeight - doc.clientHeight) || 1;
      var pct = Math.min(100, Math.max(0, (scrollTop / scrollHeight) * 100));
      if (progressBar) progressBar.style.width = pct + '%';
      if (nav) nav.classList.toggle('scrolled', scrollTop > 40);
    }
    document.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    var revealEls = document.querySelectorAll('.reveal, .reveal-stagger');

    if (reduceMotion || !('IntersectionObserver' in window)) {
      revealEls.forEach(function (el) { el.classList.add('visible'); });
    } else {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            io.unobserve(entry.target);
          }
        });
      }, { threshold: 0.15, rootMargin: '0px 0px -60px 0px' });

      revealEls.forEach(function (el) { io.observe(el); });
    }
  })();
  </script>

  <script>
    (function () {
      var toggle = document.querySelector('.mobile-toggle');
      var links = document.querySelector('.nav-links');

      toggle.addEventListener('click', function () {
        var isOpen = links.classList.toggle('open');
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      });

      links.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
          links.classList.remove('open');
          toggle.setAttribute('aria-expanded', 'false');
        });
      });

      document.addEventListener('click', function (event) {
        if (!event.target.closest('.nav')) {
          links.classList.remove('open');
          toggle.setAttribute('aria-expanded', 'false');
        }
      });

      var navLinks = Array.prototype.slice.call(
        document.querySelectorAll('#landingNavLinks > a[href^="#"]')
      );
      var sections = navLinks
        .map(function (link) {
          return document.getElementById(link.getAttribute('href').slice(1));
        })
        .filter(Boolean);

      function setActive(id) {
        navLinks.forEach(function (link) {
          link.classList.toggle('active', link.getAttribute('href') === '#' + id);
        });
      }

      if ('IntersectionObserver' in window && sections.length) {
        var observer = new IntersectionObserver(
          function (entries) {
            var visible = entries.filter(function (e) { return e.isIntersecting; });
            if (visible.length) {
              visible.sort(function (a, b) { return b.intersectionRatio - a.intersectionRatio; });
              setActive(visible[0].target.id);
            }
          },
          { rootMargin: '-45% 0px -45% 0px', threshold: 0 }
        );
        sections.forEach(function (section) { observer.observe(section); });
      }

      navLinks.forEach(function (link) {
        link.addEventListener('click', function () {
          setActive(link.getAttribute('href').slice(1));
        });
      });
    })();
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