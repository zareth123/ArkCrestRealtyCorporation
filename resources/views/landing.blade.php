<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ArkCrest Realty | The Standard of Luxury Acquisition</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
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

  /* -------- SCROLL PROGRESS -------- */
  .scroll-progress{
    position:fixed;
    top:0; left:0;
    height:3px;
    width:0%;
    background:linear-gradient(90deg,var(--orange),#e6bd62);
    z-index:2000;
    transition:width .1s linear;
  }

  /* -------- SCROLL REVEAL SYSTEM -------- */
  .reveal{
    opacity:0;
    transform:translateY(36px);
    transition:opacity .9s cubic-bezier(.16,1,.3,1), transform .9s cubic-bezier(.16,1,.3,1);
    will-change:opacity, transform;
  }
  .reveal.visible{ opacity:1; transform:translateY(0); }
  .reveal-fade{ opacity:0; transition:opacity 1s ease; }
  .reveal-fade.visible{ opacity:1; }
  .reveal-scale{ opacity:0; transform:scale(.94); transition:opacity .9s cubic-bezier(.16,1,.3,1), transform .9s cubic-bezier(.16,1,.3,1); }
  .reveal-scale.visible{ opacity:1; transform:scale(1); }
  .reveal-stagger > *{
    opacity:0;
    transform:translateY(30px);
    transition:opacity .8s cubic-bezier(.16,1,.3,1), transform .8s cubic-bezier(.16,1,.3,1);
  }
  .reveal-stagger.visible > *{ opacity:1; transform:translateY(0); }
  .reveal-stagger.visible > *:nth-child(1){ transition-delay:.05s; }
  .reveal-stagger.visible > *:nth-child(2){ transition-delay:.15s; }
  .reveal-stagger.visible > *:nth-child(3){ transition-delay:.25s; }
  .reveal-stagger.visible > *:nth-child(4){ transition-delay:.35s; }
  .reveal-stagger.visible > *:nth-child(5){ transition-delay:.45s; }
  .reveal-stagger.visible > *:nth-child(6){ transition-delay:.55s; }

  @media (prefers-reduced-motion: reduce){
    html{ scroll-behavior:auto; }
    .reveal, .reveal-fade, .reveal-scale, .reveal-stagger > *{
      opacity:1 !important; transform:none !important; transition:none !important;
    }
    .hero-content > *{ animation:none !important; opacity:1 !important; transform:none !important; }
    .hero .ph img{ animation:none !important; }
    *{ transition-duration:.01ms !important; animation-duration:.01ms !important; }
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
    transition:transform .7s cubic-bezier(.16,1,.3,1);
  }
  .estate-card .ph, .about .ph, .collage .ph{ overflow:hidden; }
  .estate-card{ transition:transform .4s cubic-bezier(.16,1,.3,1); }
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
    transition:padding .35s cubic-bezier(.16,1,.3,1), background .35s ease, box-shadow .35s ease;
  }
  .nav.scrolled{
    padding:10px 0;
    background:rgba(11,22,37,0.98);
    box-shadow:0 10px 34px rgba(5,12,20,0.3);
  }
  .nav.scrolled .brand .mark{ width:32px; height:32px; }
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
    transition:transform .3s cubic-bezier(.16,1,.3,1), box-shadow .3s cubic-bezier(.16,1,.3,1), background .25s ease, border-color .25s ease, color .25s ease;
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
  .btn-training:hover{ background:linear-gradient(135deg,#ffe7a2,#e0b54c); }


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
  .hero .ph img{
    animation:heroKenBurns 16s ease-in-out infinite alternate;
    transform-origin:center;
  }
  @keyframes heroKenBurns{
    0%{ transform:scale(1.06) translate(0,0); }
    100%{ transform:scale(1.16) translate(-1.2%, -1%); }
  }
  .hero::after{
    content:'';
    position:absolute; inset:0;
    background:linear-gradient(180deg, rgba(8,16,26,0.55) 0%, rgba(8,16,26,0.35) 40%, rgba(8,16,26,0.65) 100%);
  }
  .hero-content{ position:relative; z-index:2; text-align:center; max-width:880px; padding:0 24px; }
  .hero-content > *{
    opacity:0;
    transform:translateY(26px);
    animation:heroRise .9s cubic-bezier(.16,1,.3,1) forwards;
  }
  .hero-content > .eyebrow{ animation-delay:.15s; }
  .hero-content > h1{ animation-delay:.32s; }
  .hero-content > p{ animation-delay:.5s; }
  .hero-content > .hero-cta{ animation-delay:.68s; }
  @keyframes heroRise{
    to{ opacity:1; transform:translateY(0); }
  }
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
    transition:gap .3s cubic-bezier(.16,1,.3,1), color .25s ease;
  }
  .hero-cta .rule{ width:34px; height:1px; background:var(--orange); transition:width .3s cubic-bezier(.16,1,.3,1); }
  .hero-cta:hover{ gap:20px; color:#ffd9b8; }
  .hero-cta:hover .rule{ width:46px; }

  @keyframes bounceDown{
    0%,100%{ transform:translateY(0); opacity:.55; }
    50%{ transform:translateY(8px); opacity:1; }
  }
  .scroll-indicator{
    position:absolute;
    left:50%;
    bottom:34px;
    transform:translateX(-50%);
    z-index:2;
    width:22px; height:36px;
    border:2px solid rgba(255,255,255,0.55);
    border-radius:14px;
    opacity:0;
    animation:heroRise .9s cubic-bezier(.16,1,.3,1) forwards;
    animation-delay:1s;
  }
  .scroll-indicator::before{
    content:'';
    position:absolute;
    top:7px; left:50%;
    width:4px; height:8px;
    margin-left:-2px;
    background:#fff;
    border-radius:2px;
    animation:bounceDown 1.8s ease-in-out infinite;
  }

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
    transition:background .35s ease;
  }
  .stat-card:hover{ background:#122843; }
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
    transition:background .25s ease, border-color .25s ease, transform .25s cubic-bezier(.16,1,.3,1);
  }
  .carousel-arrow:hover{ background:rgba(255,255,255,0.12); border-color:rgba(255,255,255,0.55); }
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
    transition:padding-left .35s cubic-bezier(.16,1,.3,1);
  }
  .why-grid > .why-item:nth-child(3n+2),
  .why-grid > .why-item:nth-child(3n+3){ padding-left:40px; border-left:1px solid var(--line); }
  .why-item .no{ font-size:12px; font-weight:700; color:var(--orange); letter-spacing:1px; margin-bottom:16px; display:block; transition:transform .35s cubic-bezier(.16,1,.3,1); }
  .why-item h3{ font-style:italic; font-size:20px; color:var(--navy-900); margin-bottom:12px; }
  .why-item p{ font-size:14px; color:var(--ink-soft); }

  /* -------- FOOTER CTA -------- */
  .cta-band{ background:var(--navy-950); color:#fff; padding:90px 0; text-align:center; }
  .cta-band h2{ color:#fff; font-size:clamp(26px,3.4vw,38px); margin-bottom:26px; }
  .cta-buttons{ display:flex; gap:18px; justify-content:center; flex-wrap:wrap; }

  footer{ background:#0a1622; color:#8b9aab; padding:56px 0 30px; font-size:13px; }
  footer .wrap{ display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:20px; }
  footer .brand{ color:#fff; }

  /* -------- RICHER HOVER FOR MOUSE/TRACKPAD ONLY (prevents "stuck" hover states on touch) -------- */
  @media (hover:hover) and (pointer:fine){
    .btn-orange:hover{ transform:translateY(-2px); box-shadow:0 10px 22px rgba(211,101,47,0.32); }
    .btn-outline:hover{ transform:translateY(-2px); }
    .btn-training:hover{ transform:translateY(-2px); box-shadow:0 12px 26px rgba(214,169,68,0.32); }
    .estate-card:hover .ph img,
    .about .ph:hover img,
    .collage .ph:hover img{ transform:scale(1.08); }
    .estate-card:hover{ transform:translateY(-6px); }
    .carousel-arrow:hover{ transform:translateY(-50%) scale(1.08); }
    .portfolio-nav .carousel-arrow:hover{ transform:scale(1.08); }
    .why-item:hover{ padding-left:14px; }
    .why-item:hover .no{ transform:translateX(4px); }
  }

  /* -------- INQUIRY MODAL -------- */
  .inquiry-overlay{
    position:fixed; inset:0; z-index:3000;
    display:none;
    align-items:center; justify-content:center;
    padding:24px;
    background:rgba(8,16,26,0.6);
    backdrop-filter:blur(4px);
    -webkit-backdrop-filter:blur(4px);
    opacity:0;
    transition:opacity .3s ease;
  }
  .inquiry-overlay.open{ display:flex; }
  .inquiry-overlay.visible{ opacity:1; }

  .inquiry-modal{
    position:relative;
    width:100%; max-width:520px;
    max-height:90vh;
    overflow-y:auto;
    background:var(--cream);
    border-radius:6px;
    box-shadow:0 30px 80px rgba(5,12,20,0.45);
    transform:translateY(24px) scale(.97);
    opacity:0;
    transition:transform .35s cubic-bezier(.16,1,.3,1), opacity .35s cubic-bezier(.16,1,.3,1);
  }
  .inquiry-overlay.visible .inquiry-modal{ transform:translateY(0) scale(1); opacity:1; }

  .inquiry-modal-head{
    background:var(--navy-950);
    color:#fff;
    padding:32px 36px 26px;
    border-radius:6px 6px 0 0;
    position:relative;
  }
  .inquiry-modal-head .eyebrow{ margin-bottom:10px; }
  .inquiry-modal-head h3{ font-family:'Playfair Display',serif; font-style:italic; font-weight:600; font-size:26px; color:#fff; margin-bottom:8px; }
  .inquiry-modal-head p{ font-size:13px; color:rgba(255,255,255,0.68); max-width:400px; }
  .inquiry-close{
    position:absolute; top:20px; right:20px;
    width:34px; height:34px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    background:rgba(255,255,255,0.08);
    border:1px solid rgba(255,255,255,0.16);
    color:#fff; cursor:pointer;
    transition:background .2s ease, transform .2s cubic-bezier(.16,1,.3,1);
  }
  .inquiry-close:hover{ background:rgba(255,255,255,0.18); transform:rotate(90deg); }
  .inquiry-close svg{ width:16px; height:16px; }

  .inquiry-form{ padding:30px 36px 36px; }
  .inquiry-field{ margin-bottom:18px; }
  .inquiry-field label{
    display:block;
    font-size:11px; letter-spacing:1.5px; text-transform:uppercase; font-weight:700;
    color:var(--navy-800); margin-bottom:8px;
  }
  .inquiry-field .req{ color:var(--orange); }
  .inquiry-field input,
  .inquiry-field select,
  .inquiry-field textarea{
    width:100%;
    font-family:'Inter',sans-serif;
    font-size:14px;
    color:var(--ink);
    background:#fff;
    border:1px solid var(--line);
    border-radius:3px;
    padding:13px 14px;
    transition:border-color .2s ease, box-shadow .2s ease;
  }
  .inquiry-field input:focus,
  .inquiry-field select:focus,
  .inquiry-field textarea:focus{
    outline:none;
    border-color:var(--orange);
    box-shadow:0 0 0 3px rgba(211,101,47,0.14);
  }
  .inquiry-field textarea{ resize:vertical; min-height:90px; }
  .inquiry-row{ display:grid; grid-template-columns:1fr 1fr; gap:16px; }
  .inquiry-hp{ position:absolute; left:-9999px; top:-9999px; opacity:0; height:0; width:0; }

  .inquiry-submit{ width:100%; border:none; cursor:pointer; margin-top:6px; padding:15px 18px; }
  .inquiry-submit:disabled{ opacity:.6; cursor:not-allowed; }
  .inquiry-submit .spinner{
    display:none;
    width:14px; height:14px;
    border:2px solid rgba(255,255,255,0.4);
    border-top-color:#fff;
    border-radius:50%;
    animation:inquirySpin .7s linear infinite;
  }
  .inquiry-submit.loading .spinner{ display:inline-block; }
  .inquiry-submit.loading .submit-label{ display:none; }
  @keyframes inquirySpin{ to{ transform:rotate(360deg); } }

  .inquiry-note{ font-size:12px; color:var(--ink-soft); text-align:center; margin-top:14px; }

  .inquiry-status{
    display:none;
    align-items:flex-start;
    gap:10px;
    font-size:13px;
    border-radius:4px;
    padding:12px 14px;
    margin-bottom:18px;
  }
  .inquiry-status.show{ display:flex; }
  .inquiry-status.success{ background:#eef7ee; color:#2e6b34; border:1px solid #cfe8d0; }
  .inquiry-status.error{ background:#fdeeec; color:#a33326; border:1px solid #f5cfc9; }

  .inquiry-success-state{ display:none; padding:56px 36px; text-align:center; }
  .inquiry-success-state.show{ display:block; }
  .inquiry-success-state .icon{
    width:56px; height:56px; margin:0 auto 20px;
    border-radius:50%;
    background:#eef7ee;
    color:#2e8b3d;
    display:flex; align-items:center; justify-content:center;
  }
  .inquiry-success-state .icon svg{ width:28px; height:28px; }
  .inquiry-success-state h3{ font-family:'Playfair Display',serif; font-style:italic; font-size:24px; color:var(--navy-900); margin-bottom:10px; }
  .inquiry-success-state p{ font-size:14px; color:var(--ink-soft); max-width:360px; margin:0 auto; }

  @media (max-width:560px){
    .inquiry-row{ grid-template-columns:1fr; gap:0; }
    .inquiry-modal-head{ padding:26px 24px 22px; }
    .inquiry-form{ padding:24px 24px 28px; }
    .inquiry-modal-head h3{ font-size:22px; }
  }

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
    .wrap{ padding:0 24px; }
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
      max-height:calc(100vh - 90px);
      overflow-y:auto;
      -webkit-overflow-scrolling:touch;
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
    .nav-links > a{ width:100%; padding:14px 6px; font-size:13px; }
    .nav-actions{ display:none; }
    .nav-mobile-actions{ display:grid; gap:9px; padding-top:12px; margin-top:6px; border-top:1px solid rgba(255,255,255,0.12); }
    .nav-mobile-actions .btn{ width:100%; padding:14px 18px; }
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
    .mobile-toggle{ display:flex; padding:10px; }

    /* section rhythm: cut the generous desktop whitespace down for mobile */
    .stats{ padding:76px 0; }
    .about{ padding:80px 0; }
    .portfolio{ padding:80px 0; }
    .philosophy{ padding:80px 0; }
    .distinction{ padding:96px 0; }
    .why{ padding:84px 0; }
    .cta-band{ padding:64px 0; }
    .section-head{ margin-bottom:44px; }

    .about .grid, .philosophy .grid{ grid-template-columns:1fr; gap:36px; }
    .about .grid{ display:flex; flex-direction:column-reverse; }
    .stat-row{ grid-template-columns:1fr; }
    .stat-card{ padding:38px 28px; }
    .estate-grid{ grid-template-columns:1fr; gap:28px; }
    .collage{ grid-template-columns:1fr; }
    .collage .col:first-child{ margin-top:0; }
    .collage .tall{ aspect-ratio:16/11; }
    .why-grid{ grid-template-columns:1fr; }
    .why-grid > .why-item{ padding-left:0 !important; border-left:none !important; padding:32px 0; }
    .portfolio-head{ flex-direction:column; align-items:flex-start; gap:20px; }
    .pill-row{ gap:16px 22px; }
    .cta-buttons{ gap:14px; }
    .cta-buttons .btn{ flex:1 1 200px; }
    footer .wrap{ flex-direction:column; text-align:center; }

    /* decorative carousel arrows crowd content on narrow screens */
    .about .carousel-arrow{ display:none; }
    .stats .carousel-arrow{ display:none; }
  }
  @media (max-width:600px){
    .wrap{ padding:0 18px; }
    .hero{ height:100svh; min-height:560px; }
    .hero-content{ padding:0 18px; }
    .hero-content p{ font-size:15px; margin-bottom:28px; }
    .hero-cta{ font-size:11px; letter-spacing:2px; }
    .scroll-indicator{ bottom:22px; width:20px; height:32px; }

    .eyebrow{ font-size:11px; letter-spacing:2px; }
    .section-head h2, .about h2, .philosophy h2, .distinction h2{ margin-bottom:14px; }
    .section-head p, .about p, .philosophy p{ font-size:14px; max-width:none; }

    .stat-card .num{ font-size:42px; }
    .stat-card .desc{ max-width:none; }

    .estate-card h3{ font-size:19px; }

    .distinction{ padding:76px 0; }
    .distinction p{ max-width:none; }
    .pill-row{ flex-direction:column; align-items:flex-start; gap:14px; padding-left:4px; }

    .why-item h3{ font-size:18px; }

    .cta-band h2{ margin-bottom:20px; }
    .cta-buttons{ flex-direction:column; }
    .cta-buttons .btn{ width:100%; flex:none; }

    /* keep transforms modest on small screens so reveals don't overshoot */
    .reveal{ transform:translateY(22px); }
    .reveal-stagger > *{ transform:translateY(20px); }
    .hero .ph img{ animation-duration:20s; }
  }
  @media (max-width:480px){
    .brand .name{ font-size:11px; letter-spacing:1.5px; }
    .brand .mark{ width:34px; height:34px; }
    .wrap{ padding:0 16px; }
    .btn{ padding:13px 16px; }
  }
  @media (max-width:360px){
    .hero-content h1{ font-size:30px; }
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
            <a href="#" class="btn btn-orange js-open-inquiry">Inquire Now</a>
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
          <a href="#" class="btn btn-orange js-open-inquiry">Inquire Now</a>
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
    <div class="scroll-indicator" aria-hidden="true"></div>
  </section>

  <!-- STATS -->
  <section class="stats">
    <div class="wrap" style="position:relative;">
      <div class="carousel-arrow left"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg></div>
      <div class="carousel-arrow right"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg></div>
      <div class="section-head reveal">
        <span class="eyebrow on-dark"><span class="rule"></span>Market Authority</span>
        <h2><em>Trusted by</em> Visionary Investors</h2>
        <p>We don't just broker land; we secure legacies. Our track record is built on the pillars of absolute transparency and high-yield strategic selection.</p>
      </div>
      <div class="stat-row reveal-stagger">
        <div class="stat-card">
          <div class="num"><span class="count" data-target="10">0</span><sup>+</sup></div>
          <div class="divider"></div>
          <div class="label">Elite Regions</div>
          <div class="desc">Hand-picked territories vetted for maximum capital appreciation and security.</div>
        </div>
        <div class="stat-card">
          <div class="num"><span class="count" data-target="500">0</span><sup>+</sup></div>
          <div class="divider"></div>
          <div class="label">Legacy Partners</div>
          <div class="desc">Distinguished families and corporate entities who trust the ArkCrest standard.</div>
        </div>
        <div class="stat-card">
          <div class="num"><span class="count" data-target="100">0</span><sup>%</sup></div>
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
        <div class="ph reveal-scale">
          <img
            src="{{ asset('images/DSC_6783.jpg') }}"
            alt="ArkCrest Realty team"
            loading="lazy"
          >
        </div>
        <div class="reveal">
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
      <div class="portfolio-head reveal">
        <div>
          <span class="eyebrow">The Portfolio</span>
          <h2><em>Featured</em> Estates</h2>
        </div>
        <div class="portfolio-nav">
          <div class="carousel-arrow"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg></div>
          <div class="carousel-arrow"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg></div>
        </div>
      </div>
      <div class="estate-grid reveal-stagger">
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
        <div class="reveal">
          <span class="eyebrow"><span class="rule"></span>ArkCrest Philosophy</span>
          <h2>We don't just sell land — <em>we build legacy and lifestyle.</em></h2>
          <p>Every property in our portfolio undergoes a rigorous selection process. We ensure lasting growth, strategic location advantage, and a foundation of security for the generations that follow.</p>
          <a href="#" class="text-link">Explore Our Vetting Process <span class="rule"></span></a>
        </div>
        <div class="collage reveal-scale">
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
    <div class="distinction-content reveal">
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
      <div class="section-head reveal">
        <span class="eyebrow"><span class="rule"></span>Excellence Guaranteed<span class="rule"></span></span>
        <h2>Why Choose <em>ArkCrest</em></h2>
      </div>
      <div class="why-grid reveal-stagger">
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
    <div class="wrap reveal">
      <span class="eyebrow on-dark">Begin Your Legacy</span>
      <h2 style="margin-top:14px;">Ready to <em>secure your estate?</em></h2>
      <div class="cta-buttons">
        <a href="#" class="btn btn-orange js-open-inquiry">Inquire Now</a>
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

  <!-- INQUIRY MODAL -->
  <div class="inquiry-overlay" id="inquiryOverlay" role="dialog" aria-modal="true" aria-labelledby="inquiryModalTitle">
    <div class="inquiry-modal">
      <div class="inquiry-modal-head">
        <button type="button" class="inquiry-close" id="inquiryClose" aria-label="Close inquiry form">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <span class="eyebrow on-dark"><span class="rule"></span>Begin Your Legacy</span>
        <h3 id="inquiryModalTitle">Let's discuss your estate.</h3>
        <p>Share a few details and one of our consultants will reach out within one business day.</p>
      </div>

      <div class="inquiry-success-state" id="inquirySuccess">
        <div class="icon">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h3>Inquiry Received</h3>
        <p id="inquirySuccessMessage">Thank you! Your inquiry has been received. Our team will reach out shortly.</p>
      </div>

      <form class="inquiry-form" id="inquiryForm" novalidate>
        @csrf
        <div class="inquiry-status" id="inquiryStatus"></div>

        <!-- Honeypot: left visually hidden; real visitors never fill this in -->
        <div class="inquiry-hp" aria-hidden="true">
          <label for="inquiry_website">Website</label>
          <input type="text" id="inquiry_website" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div class="inquiry-row">
          <div class="inquiry-field">
            <label for="inquiry_name">Full Name <span class="req">*</span></label>
            <input type="text" id="inquiry_name" name="full_name" required maxlength="255" autocomplete="name" placeholder="Juan Dela Cruz">
          </div>
          <div class="inquiry-field">
            <label for="inquiry_phone">Phone Number</label>
            <input type="tel" id="inquiry_phone" name="phone" maxlength="30" autocomplete="tel" placeholder="09XX XXX XXXX">
          </div>
        </div>

        <div class="inquiry-field">
          <label for="inquiry_email">Email Address <span class="req">*</span></label>
          <input type="email" id="inquiry_email" name="email" required maxlength="255" autocomplete="email" placeholder="you@email.com">
        </div>

        <div class="inquiry-field">
          <label for="inquiry_interest">Property Interest</label>
          <select id="inquiry_interest" name="property_interest">
            <option value="">Select an option</option>
            <option value="Manggas Estate — Urban Heritage Reserve">Manggas Estate — Urban Heritage Reserve</option>
            <option value="Mountain View Hills — Skyline Sanctuary">Mountain View Hills — Skyline Sanctuary</option>
            <option value="Lakeside Estates — Waterfront Legacy">Lakeside Estates — Waterfront Legacy</option>
            <option value="General Inquiry">General Inquiry</option>
          </select>
        </div>

        <div class="inquiry-field">
          <label for="inquiry_message">Message</label>
          <textarea id="inquiry_message" name="message" maxlength="2000" placeholder="Tell us a bit about what you're looking for..."></textarea>
        </div>

        <button type="submit" class="btn btn-orange inquiry-submit" id="inquirySubmit">
          <span class="submit-label">Submit Inquiry</span>
          <span class="spinner" aria-hidden="true"></span>
        </button>
        <div class="inquiry-note">By submitting, you agree to be contacted by ArkCrest Realty regarding your inquiry.</div>
      </form>
    </div>
  </div>

  <script>
  (function () {
    var overlay = document.getElementById('inquiryOverlay');
    var modal = overlay.querySelector('.inquiry-modal');
    var openTriggers = document.querySelectorAll('.js-open-inquiry');
    var closeBtn = document.getElementById('inquiryClose');
    var form = document.getElementById('inquiryForm');
    var submitBtn = document.getElementById('inquirySubmit');
    var statusBox = document.getElementById('inquiryStatus');
    var successState = document.getElementById('inquirySuccess');
    var successMessage = document.getElementById('inquirySuccessMessage');
    var lastFocused = null;

    function csrfToken() {
      var meta = document.querySelector('meta[name="csrf-token"]');
      return meta ? meta.getAttribute('content') : '';
    }

    function openModal() {
      lastFocused = document.activeElement;
      overlay.classList.add('open');
      document.body.style.overflow = 'hidden';
      requestAnimationFrame(function () { overlay.classList.add('visible'); });
      setTimeout(function () {
        var firstField = document.getElementById('inquiry_name');
        if (firstField) firstField.focus();
      }, 320);
    }

    function closeModal() {
      overlay.classList.remove('visible');
      document.body.style.overflow = '';
      setTimeout(function () {
        overlay.classList.remove('open');
        if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
      }, 300);
    }

    function resetForm() {
      form.reset();
      form.classList.remove('inquiry-hide');
      form.style.display = '';
      successState.classList.remove('show');
      statusBox.classList.remove('show', 'success', 'error');
      statusBox.textContent = '';
    }

    openTriggers.forEach(function (trigger) {
      trigger.addEventListener('click', function (event) {
        event.preventDefault();
        resetForm();
        openModal();
      });
    });

    closeBtn.addEventListener('click', closeModal);

    overlay.addEventListener('click', function (event) {
      if (event.target === overlay) closeModal();
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && overlay.classList.contains('open')) closeModal();
    });

    form.addEventListener('submit', function (event) {
      event.preventDefault();

      statusBox.classList.remove('show', 'success', 'error');

      var formData = new FormData(form);
      submitBtn.classList.add('loading');
      submitBtn.disabled = true;

      fetch("{{ route('inquire.store') }}", {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken(),
          'Accept': 'application/json',
        },
        body: formData,
      })
        .then(function (response) {
          return response.json().then(function (data) {
            return { ok: response.ok, data: data };
          });
        })
        .then(function (result) {
          submitBtn.classList.remove('loading');
          submitBtn.disabled = false;

          if (result.ok && result.data.success) {
            successMessage.textContent = result.data.message || 'Thank you! Your inquiry has been received.';
            form.style.display = 'none';
            successState.classList.add('show');
            setTimeout(closeModal, 3200);
          } else if (result.data.errors) {
            var firstError = Object.values(result.data.errors)[0];
            statusBox.textContent = Array.isArray(firstError) ? firstError[0] : 'Please check the form and try again.';
            statusBox.classList.add('show', 'error');
          } else {
            statusBox.textContent = 'Something went wrong. Please try again in a moment.';
            statusBox.classList.add('show', 'error');
          }
        })
        .catch(function () {
          submitBtn.classList.remove('loading');
          submitBtn.disabled = false;
          statusBox.textContent = 'Network error — please check your connection and try again.';
          statusBox.classList.add('show', 'error');
        });
    });
  })();
  </script>

  <script>
  (function () {
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // -------- Scroll progress + nav shrink --------
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

    // -------- Scroll reveal --------
    var revealEls = document.querySelectorAll('.reveal, .reveal-fade, .reveal-scale, .reveal-stagger');

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

    // -------- Animated stat counters --------
    var counters = document.querySelectorAll('.count');
    function animateCount(el) {
      var target = parseInt(el.getAttribute('data-target'), 10) || 0;
      if (reduceMotion) { el.textContent = target; return; }
      var duration = 1400;
      var start = null;
      function step(ts) {
        if (!start) start = ts;
        var progress = Math.min((ts - start) / duration, 1);
        var eased = 1 - Math.pow(1 - progress, 3);
        el.textContent = Math.round(eased * target);
        if (progress < 1) requestAnimationFrame(step);
      }
      requestAnimationFrame(step);
    }

    if (counters.length) {
      if (!('IntersectionObserver' in window)) {
        counters.forEach(animateCount);
      } else {
        var countIo = new IntersectionObserver(function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) {
              animateCount(entry.target);
              countIo.unobserve(entry.target);
            }
          });
        }, { threshold: 0.5 });
        counters.forEach(function (el) { countIo.observe(el); });
      }
    }
  })();
  </script>

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