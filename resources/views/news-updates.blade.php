<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>News &amp; Updates | ArkCrest Realty</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="icon" type="image/png" href="{{ asset('images/ArkCrest_Logo.png') }}">
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
  .nav-links > a,
  .nav-dropdown-trigger{
    color:rgba(255,255,255,0.88); font-size:12px; letter-spacing:2px; text-transform:uppercase; font-weight:600;
    padding:0 0 8px; border:0; border-bottom:2px solid transparent; background:transparent;
    font-family:inherit; line-height:1.6; cursor:pointer; transition:.25s;
  }
  .nav-links > a.active,
  .nav-links > a:hover,
  .nav-dropdown.active > .nav-dropdown-trigger,
  .nav-dropdown.open > .nav-dropdown-trigger,
  .nav-dropdown-trigger:hover{ border-color:var(--clay); color:#fff; }
  .nav-links > a.fb-link{ color:#7fa8d6; }

  .nav-dropdown{ position:relative; display:flex; align-items:center; }
  .nav-dropdown-trigger{ display:inline-flex; align-items:center; gap:7px; }
  .nav-dropdown-chevron{ width:13px; height:13px; transition:transform .22s ease; }
  .nav-dropdown.open .nav-dropdown-chevron{ transform:rotate(180deg); }
  .nav-dropdown-menu{
    position:absolute; top:calc(100% + 14px); left:50%; width:270px; padding:10px;
    display:grid; gap:3px; opacity:0; visibility:hidden; pointer-events:none;
    transform:translate(-50%,10px); background:rgba(13,26,43,.99);
    border:1px solid rgba(255,255,255,.12); border-radius:6px;
    box-shadow:0 18px 42px rgba(0,0,0,.34); transition:opacity .2s ease, transform .2s ease, visibility .2s ease;
  }
  .nav-dropdown.open .nav-dropdown-menu,
  .nav-dropdown:focus-within .nav-dropdown-menu{
    opacity:1; visibility:visible; pointer-events:auto; transform:translate(-50%,0);
  }
  .nav-dropdown-menu a{
    display:flex; align-items:center; justify-content:space-between; gap:14px;
    padding:11px 12px; color:rgba(255,255,255,.78); border-radius:4px;
    font-size:11px; letter-spacing:.8px; font-weight:600; text-transform:none;
    transition:background .2s ease, color .2s ease, padding-left .2s ease;
  }
  .nav-dropdown-menu a::after{ content:'›'; color:var(--gold); font-size:17px; line-height:1; }
  .nav-dropdown-menu a:hover,
  .nav-dropdown-menu a.active{ color:#fff; background:rgba(255,255,255,.08); padding-left:16px; }

  @media (min-width:901px) and (hover:hover) and (pointer:fine){
    .nav-dropdown:hover .nav-dropdown-menu{
      opacity:1; visibility:visible; pointer-events:auto; transform:translate(-50%,0);
    }
    .nav-dropdown:hover .nav-dropdown-chevron{ transform:rotate(180deg); }
  }
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

/* -------- NEWS & UPDATES PAGE -------- */
  .news-page{
    min-height:100vh;
    padding:170px 0 110px;
    background:
      linear-gradient(180deg,#dce9e4 0,#edf3f0 190px,#fff 190px,#fff 100%);
  }

  .news-page-head{
    max-width:760px;
    margin:0 auto 54px;
    text-align:center;
  }

  .news-page-head .eyebrow{
    justify-content:center;
  }

  .news-page-head h1{
    margin-top:16px;
    color:var(--navy-950);
    font-size:clamp(38px,5.5vw,64px);
  }

  .news-page-head p{
    max-width:650px;
    margin:20px auto 0;
    color:var(--ink-soft);
    font-size:15px;
  }

  .news-feature{
    display:grid;
    grid-template-columns:minmax(0,1.25fr) minmax(280px,.75fr);
    gap:0;
    overflow:hidden;
    border:1px solid var(--parchment-line);
    background:var(--cream);
  }

  .news-feature-copy{
    padding:54px;
  }

  .news-feature-copy h2{
    margin-top:16px;
    color:var(--navy-950);
    font-size:clamp(28px,3.8vw,44px);
  }

  .news-feature-copy p{
    max-width:620px;
    margin-top:18px;
    color:var(--ink-soft);
    font-size:14px;
  }

  .news-feature-side{
    display:flex;
    align-items:center;
    justify-content:center;
    min-height:320px;
    padding:40px;
    text-align:center;
    background:var(--navy-950);
    color:#fff;
  }

  .news-coming-soon{
    display:inline-flex;
    align-items:center;
    gap:9px;
    padding:10px 14px;
    border:1px solid rgba(255,255,255,.24);
    font-family:'IBM Plex Mono',monospace;
    font-size:10px;
    letter-spacing:1.5px;
    text-transform:uppercase;
  }

  .news-coming-soon::before{
    content:'';
    width:7px;
    height:7px;
    border-radius:50%;
    background:#d6a944;
  }

  .news-categories{
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:18px;
    margin-top:24px;
  }

  .news-category{
    min-height:190px;
    padding:30px 26px;
    border:1px solid var(--parchment-line);
    background:#fff;
  }

  .news-category-number{
    display:block;
    margin-bottom:24px;
    color:var(--clay);
    font-family:'IBM Plex Mono',monospace;
    font-size:10px;
    letter-spacing:1.4px;
  }

  .news-category h3{
    color:var(--navy-900);
    font-size:21px;
  }

  .news-category p{
    margin-top:12px;
    color:var(--ink-soft);
    font-size:13px;
  }

  @media (max-width:900px){
    .news-page{
      padding:135px 0 80px;
    }

    .news-feature{
      grid-template-columns:1fr;
    }

    .news-feature-copy{
      padding:38px 30px;
    }

    .news-feature-side{
      min-height:180px;
    }

    .news-categories{
      grid-template-columns:1fr;
    }
  }

  @media (max-width:600px){
    .news-page-head{
      margin-bottom:38px;
    }

    .news-feature-copy{
      padding:32px 24px;
    }

    .news-category{
      min-height:auto;
    }
  }

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
    .nav-links > a,
    .nav-dropdown-trigger{ width:100%; padding:14px 6px; font-size:13px; text-align:left; }
    .nav-dropdown{ width:100%; display:block; }
    .nav-dropdown-trigger{ justify-content:space-between; }
    .nav-dropdown-menu{
      position:static; width:100%; padding:4px 0 4px 12px; display:none; gap:2px;
      opacity:1; visibility:visible; pointer-events:auto; transform:none;
      background:rgba(255,255,255,.035); border:0; border-left:1px solid rgba(156,128,84,.55);
      border-radius:0; box-shadow:none; transition:none;
    }
    .nav-dropdown.open .nav-dropdown-menu{ display:grid; transform:none; }
    .nav-dropdown-menu a{ padding:11px 12px; font-size:12px; }
    .nav-dropdown-menu a:hover,
    .nav-dropdown-menu a.active{ padding-left:16px; }
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
    .news-updates{ padding:76px 0; }
    .news-updates-panel{ grid-template-columns:1fr; padding:36px 30px; gap:24px; }
    .news-status{ justify-self:start; }
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

/* -------- DYNAMIC NEWS POSTS -------- */
.news-empty-state{
  padding:70px 32px;
  border:1px solid var(--parchment-line);
  background:#fff;
  text-align:center;
}
.news-empty-state h2{
  margin-top:16px;
  color:var(--navy-950);
  font-size:clamp(28px,3.8vw,44px);
}
.news-empty-state p{
  max-width:540px;
  margin:16px auto 0;
  color:var(--ink-soft);
  font-size:14px;
}
.news-post-featured{
  overflow:hidden;
  margin-bottom:28px;
  border:1px solid var(--parchment-line);
  background:#fff;
}
.news-featured-media{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
  gap:1px;
  background:var(--parchment-line);
}
.news-featured-media figure{
  min-height:310px;
  background:var(--navy-950);
}
.news-featured-media img,
.news-featured-media video{
  display:block;
  width:100%;
  height:100%;
  min-height:310px;
  max-height:520px;
  object-fit:cover;
  background:var(--navy-950);
}
.news-featured-copy{
  padding:46px 50px 50px;
}
.news-post-date{
  display:flex;
  align-items:center;
  gap:10px;
  color:var(--clay);
  font-family:'IBM Plex Mono',monospace;
  font-size:10px;
  letter-spacing:1.2px;
  text-transform:uppercase;
}
.news-post-date::before{
  content:'';
  width:24px;
  height:1px;
  background:currentColor;
}
.news-featured-copy h2{
  margin-top:16px;
  color:var(--navy-950);
  font-size:clamp(30px,4vw,48px);
}
.news-featured-description{
  margin-top:20px;
  color:var(--ink-soft);
  font-size:15px;
  line-height:1.8;
}
.news-post-grid{
  display:grid;
  grid-template-columns:repeat(2,minmax(0,1fr));
  gap:22px;
}
.news-post-card{
  display:flex;
  overflow:hidden;
  flex-direction:column;
  border:1px solid var(--parchment-line);
  background:#fff;
}
.news-post-media{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(170px,1fr));
  gap:1px;
  background:var(--parchment-line);
}
.news-post-media figure{
  min-height:210px;
  background:var(--navy-950);
}
.news-post-media img,
.news-post-media video{
  display:block;
  width:100%;
  height:100%;
  min-height:210px;
  max-height:340px;
  object-fit:cover;
  background:var(--navy-950);
}
.news-post-card-copy{
  display:flex;
  flex:1;
  flex-direction:column;
  padding:30px;
}
.news-post-card-copy h2{
  margin-top:13px;
  color:var(--navy-950);
  font-size:25px;
}
.news-post-excerpt{
  margin-top:14px;
  color:var(--ink-soft);
  font-size:13px;
  line-height:1.7;
}
.news-post-details{
  margin-top:18px;
  border-top:1px solid var(--parchment-line);
}
.news-post-details summary{
  padding-top:15px;
  color:var(--clay);
  cursor:pointer;
  font-family:'IBM Plex Mono',monospace;
  font-size:10px;
  font-weight:600;
  letter-spacing:1px;
  list-style:none;
  text-transform:uppercase;
}
.news-post-details summary::-webkit-details-marker{
  display:none;
}
.news-post-details-content{
  padding-top:14px;
  color:var(--ink-soft);
  font-size:13px;
  line-height:1.75;
}
.news-page-pagination{
  display:flex;
  align-items:center;
  justify-content:center;
  gap:15px;
  margin-top:42px;
  color:var(--ink-soft);
  font-family:'IBM Plex Mono',monospace;
  font-size:10px;
  letter-spacing:.6px;
  text-transform:uppercase;
}
.news-page-pagination a,
.news-page-pagination span{
  padding:9px 13px;
  border:1px solid var(--parchment-line);
}
.news-page-pagination a{
  color:var(--navy-900);
  background:#fff;
}
.news-page-pagination .disabled{
  color:#a1a9b1;
  background:#f7f7f5;
}
.news-media-placeholder{
  display:flex;
  align-items:center;
  justify-content:center;
  min-height:250px;
  background:
    linear-gradient(135deg,rgba(156,128,84,.16),rgba(187,90,42,.10)),
    var(--navy-950);
  color:rgba(255,255,255,.6);
  font-family:'IBM Plex Mono',monospace;
  font-size:10px;
  letter-spacing:1.2px;
  text-transform:uppercase;
}
@media(max-width:900px){
  .news-post-grid{
    grid-template-columns:1fr;
  }
  .news-featured-copy{
    padding:36px 30px 40px;
  }
}
@media(max-width:600px){
  .news-featured-media,
  .news-post-media{
    grid-template-columns:1fr;
  }
  .news-featured-media figure,
  .news-featured-media img,
  .news-featured-media video{
    min-height:230px;
  }
  .news-post-card-copy{
    padding:25px 22px;
  }
  .news-page-pagination{
    gap:8px;
  }
}


/* -------- LATEST NEWS CARD LAYOUT -------- */
:root{
  --news-page-bg:#e7e7e7;
  --news-card-bg:#ffffff;
  --news-accent:#54b95b;
  --news-meta:#7c8794;
}
.sr-only{
  position:absolute !important;
  width:1px !important;
  height:1px !important;
  padding:0 !important;
  margin:-1px !important;
  overflow:hidden !important;
  clip:rect(0,0,0,0) !important;
  white-space:nowrap !important;
  border:0 !important;
}
.latest-news-page{
  min-height:100vh;
  padding:154px 0 100px;
  background:var(--news-page-bg);
}
.latest-news-page .wrap{
  max-width:1240px;
}
.latest-news-header{
  margin:0 auto 42px;
  text-align:center;
}
.latest-news-header h1{
  color:#081829;
  font-size:clamp(42px,5vw,58px);
  font-weight:600;
  letter-spacing:-1px;
}
.latest-news-header p{
  max-width:670px;
  margin:14px auto 0;
  color:#66717e;
  font-size:14px;
}
.latest-news-grid{
  display:grid;
  grid-template-columns:repeat(2,minmax(0,1fr));
  gap:22px;
}
.latest-news-card{
  display:grid;
  grid-template-columns:minmax(0,1fr) minmax(0,1fr);
  min-height:292px;
  overflow:hidden;
  background:var(--news-card-bg);
  box-shadow:0 1px 0 rgba(13,26,43,.04);
  transition:transform .28s ease, box-shadow .28s ease;
}
.latest-news-card:only-child{
  grid-column:1 / -1;
  width:min(100%,760px);
  justify-self:center;
}
.latest-news-card-media{
  position:relative;
  min-height:292px;
  overflow:hidden;
  background:#d6d9dc;
}
.latest-news-card-media figure{
  width:100%;
  height:100%;
  min-height:292px;
  margin:0;
}
.latest-news-card-media img,
.latest-news-card-media video{
  display:block;
  width:100%;
  height:100%;
  min-height:292px;
  object-fit:cover;
  background:#111d2b;
  transition:transform .5s cubic-bezier(.16,1,.3,1);
}
.latest-news-card-media video{
  object-fit:cover;
}
.latest-news-media-count{
  position:absolute;
  right:12px;
  bottom:12px;
  z-index:2;
  padding:6px 9px;
  border-radius:999px;
  background:rgba(8,24,41,.78);
  color:#fff;
  font-size:10px;
  font-weight:700;
  letter-spacing:.5px;
  backdrop-filter:blur(6px);
}
.latest-news-placeholder{
  display:flex;
  align-items:center;
  justify-content:center;
  width:100%;
  height:100%;
  min-height:292px;
  padding:24px;
  color:rgba(255,255,255,.78);
  text-align:center;
  font-family:'IBM Plex Mono',monospace;
  font-size:10px;
  letter-spacing:1.4px;
  text-transform:uppercase;
  background:
    linear-gradient(135deg,rgba(156,128,84,.28),rgba(187,90,42,.17)),
    #132840;
}
.latest-news-card-copy{
  display:flex;
  flex-direction:column;
  align-items:flex-start;
  min-width:0;
  padding:43px 30px 34px;
}
.latest-news-meta{
  display:flex;
  align-items:center;
  flex-wrap:wrap;
  gap:8px;
  color:var(--news-meta);
  font-size:11px;
  line-height:1.4;
}
.latest-news-meta-divider{
  width:1px;
  height:13px;
  background:#aeb5bd;
}
.latest-news-card-copy h2{
  margin-top:18px;
  color:#071828;
  font-family:'Inter',sans-serif;
  font-size:clamp(20px,1.8vw,25px);
  font-weight:500;
  line-height:1.25;
  overflow-wrap:anywhere;
}
.latest-news-excerpt{
  display:-webkit-box;
  overflow:hidden;
  margin-top:19px;
  color:#172230;
  font-family:'Playfair Display',serif;
  font-size:15px;
  font-style:italic;
  line-height:1.55;
  -webkit-box-orient:vertical;
  -webkit-line-clamp:3;
}
.latest-news-details{
  width:100%;
  margin-top:auto;
  padding-top:26px;
}
.latest-news-details summary{
  position:relative;
  display:inline-block;
  color:#111820;
  cursor:pointer;
  list-style:none;
  font-size:14px;
  font-weight:500;
  line-height:1.5;
}
.latest-news-details summary::-webkit-details-marker{
  display:none;
}
.latest-news-details summary::before{
  content:'Read More';
}
.latest-news-details[open] summary::before{
  content:'Read Less';
}
.latest-news-details summary::after{
  content:'';
  position:absolute;
  left:0;
  right:0;
  bottom:-5px;
  height:2px;
  background:var(--news-accent);
  transform-origin:left;
  transition:transform .2s ease;
}
.latest-news-details summary:hover::after{
  transform:scaleX(.72);
}
.latest-news-details-content{
  margin-top:18px;
  padding-top:17px;
  border-top:1px solid #e5e7e9;
  color:#4f5c68;
  font-size:13px;
  line-height:1.75;
}
.latest-news-empty{
  max-width:760px;
  margin:0 auto;
  padding:70px 32px;
  background:#fff;
  text-align:center;
}
.latest-news-empty h2{
  color:#081829;
  font-size:32px;
}
.latest-news-empty p{
  margin-top:12px;
  color:#687481;
  font-size:14px;
}
.latest-news-pagination{
  display:flex;
  align-items:center;
  justify-content:center;
  flex-wrap:wrap;
  gap:10px;
  margin-top:40px;
  color:#5f6974;
  font-size:12px;
}
.latest-news-pagination a,
.latest-news-pagination span{
  padding:10px 14px;
  background:#fff;
}
.latest-news-pagination a{
  color:#0d1a2b;
  transition:background .2s ease, color .2s ease;
}
.latest-news-pagination a:hover{
  background:#0d1a2b;
  color:#fff;
}
.latest-news-pagination .disabled{
  color:#9ca3aa;
  background:#f3f3f3;
}
@media (hover:hover) and (pointer:fine){
  .latest-news-card:hover{
    transform:translateY(-4px);
    box-shadow:0 14px 34px rgba(13,26,43,.10);
  }
  .latest-news-card:hover .latest-news-card-media img{
    transform:scale(1.035);
  }
}
@media(max-width:1040px){
  .latest-news-page{
    padding-top:134px;
  }
  .latest-news-grid{
    grid-template-columns:1fr;
  }
  .latest-news-card:only-child{
    width:100%;
  }
  .latest-news-card-copy h2{
    font-size:24px;
  }
}
@media(max-width:660px){
  .latest-news-page{
    padding:118px 0 72px;
  }
  .latest-news-header{
    margin-bottom:30px;
  }
  .latest-news-header p{
    font-size:13px;
  }
  .latest-news-card{
    grid-template-columns:1fr;
  }
  .latest-news-card-media,
  .latest-news-card-media figure,
  .latest-news-card-media img,
  .latest-news-card-media video,
  .latest-news-placeholder{
    min-height:235px;
  }
  .latest-news-card-copy{
    padding:30px 24px 28px;
  }
  .latest-news-details{
    padding-top:22px;
  }
}
@media(max-width:420px){
  .latest-news-header h1{
    font-size:38px;
  }
  .latest-news-meta{
    gap:6px;
    font-size:10px;
  }
  .latest-news-card-copy h2{
    font-size:21px;
  }
}


/* -------- NEWS ARTICLE DETAIL + MEDIA CAROUSEL -------- */
.news-card-read-more{
  position:relative;
  display:inline-block;
  margin-top:auto;
  padding-top:26px;
  color:#111820;
  font-size:14px;
  font-weight:500;
  line-height:1.5;
}
.news-card-read-more::after{
  content:'';
  position:absolute;
  left:0;
  bottom:-5px;
  width:100%;
  height:2px;
  background:var(--news-accent);
  transform-origin:left;
  transition:transform .2s ease;
}
.news-card-read-more:hover::after{ transform:scaleX(.72); }

.news-detail-shell{
  display:grid;
  grid-template-columns:minmax(0,1fr) 350px;
  align-items:start;
  gap:28px;
}
.news-detail-main{
  min-width:0;
  overflow:hidden;
  background:#fff;
  box-shadow:0 1px 0 rgba(13,26,43,.04);
}
.news-detail-back{
  display:inline-flex;
  align-items:center;
  gap:8px;
  margin-bottom:18px;
  color:#53606d;
  font-size:12px;
  font-weight:600;
}
.news-detail-back svg{ width:16px; height:16px; }
.news-detail-back:hover{ color:var(--clay); }

.news-detail-carousel{
  position:relative;
  overflow:hidden;
  background:#101d2b;
}
.news-detail-slides{
  position:relative;
  min-height:520px;
}
.news-detail-slide{
  width:100%;
  min-height:520px;
  margin:0;
  background:#101d2b;
}
.news-detail-slide[hidden]{ display:none !important; }
.news-detail-slide img,
.news-detail-slide video{
  display:block;
  width:100%;
  height:520px;
  object-fit:contain;
  background:#101d2b;
}
.news-detail-media-placeholder{
  display:flex;
  align-items:center;
  justify-content:center;
  min-height:520px;
  padding:40px;
  color:rgba(255,255,255,.72);
  text-align:center;
  font-family:'IBM Plex Mono',monospace;
  font-size:11px;
  letter-spacing:1.4px;
  text-transform:uppercase;
  background:
    linear-gradient(135deg,rgba(156,128,84,.28),rgba(187,90,42,.17)),
    #132840;
}
.news-carousel-button{
  position:absolute;
  top:50%;
  z-index:5;
  display:flex;
  align-items:center;
  justify-content:center;
  width:46px;
  height:46px;
  border:1px solid rgba(255,255,255,.36);
  border-radius:50%;
  color:#fff;
  background:rgba(8,24,41,.72);
  cursor:pointer;
  transform:translateY(-50%);
  backdrop-filter:blur(7px);
  transition:background .2s ease, transform .2s ease;
}
.news-carousel-button:hover{
  background:rgba(8,24,41,.94);
  transform:translateY(-50%) scale(1.05);
}
.news-carousel-button svg{ width:22px; height:22px; }
.news-carousel-prev{ left:18px; }
.news-carousel-next{ right:18px; }
.news-carousel-footer{
  position:absolute;
  left:0;
  right:0;
  bottom:0;
  z-index:4;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:16px;
  padding:42px 18px 16px;
  color:#fff;
  background:linear-gradient(180deg,transparent,rgba(3,12,22,.78));
  pointer-events:none;
}
.news-carousel-counter{
  flex-shrink:0;
  font-size:11px;
  font-weight:700;
  letter-spacing:.6px;
}
.news-carousel-dots{
  display:flex;
  align-items:center;
  justify-content:center;
  flex-wrap:wrap;
  gap:7px;
  pointer-events:auto;
}
.news-carousel-dot{
  width:8px;
  height:8px;
  padding:0;
  border:0;
  border-radius:50%;
  background:rgba(255,255,255,.48);
  cursor:pointer;
  transition:width .2s ease, border-radius .2s ease, background .2s ease;
}
.news-carousel-dot.is-active{
  width:24px;
  border-radius:999px;
  background:#fff;
}
.news-detail-copy{ padding:46px 52px 56px; }
.news-detail-copy h1{
  max-width:850px;
  margin-top:16px;
  color:#071828;
  font-family:'Inter',sans-serif;
  font-size:clamp(31px,4vw,49px);
  font-weight:600;
  line-height:1.13;
  letter-spacing:-1px;
  overflow-wrap:anywhere;
}
.news-detail-description{
  margin-top:28px;
  color:#3f4c58;
  font-size:15px;
  line-height:1.9;
}

.news-detail-sidebar{
  position:sticky;
  top:108px;
  min-width:0;
  background:#fff;
  box-shadow:0 1px 0 rgba(13,26,43,.04);
}
.news-sidebar-head{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  padding:22px 22px 18px;
  border-bottom:1px solid #e7e9eb;
}
.news-sidebar-head h2{
  color:#0d1a2b;
  font-family:'Inter',sans-serif;
  font-size:18px;
  font-weight:700;
}
.news-sidebar-head a{
  color:var(--clay);
  font-size:11px;
  font-weight:700;
}
.news-sidebar-list{ display:grid; }
.news-sidebar-item{
  display:grid;
  grid-template-columns:126px minmax(0,1fr);
  gap:13px;
  padding:15px;
  border-bottom:1px solid #eceeef;
  transition:background .2s ease;
}
.news-sidebar-item:last-child{ border-bottom:0; }
.news-sidebar-item:hover{ background:#f8f8f7; }
.news-sidebar-thumb{
  position:relative;
  min-height:84px;
  overflow:hidden;
  background:#132840;
}
.news-sidebar-thumb img,
.news-sidebar-thumb video{
  display:block;
  width:100%;
  height:84px;
  object-fit:cover;
  background:#132840;
}
.news-sidebar-video-badge{
  position:absolute;
  right:6px;
  bottom:6px;
  padding:3px 6px;
  border-radius:999px;
  color:#fff;
  background:rgba(8,24,41,.8);
  font-size:8px;
  font-weight:700;
  letter-spacing:.4px;
  text-transform:uppercase;
}
.news-sidebar-placeholder{
  display:flex;
  align-items:center;
  justify-content:center;
  width:100%;
  height:84px;
  padding:8px;
  color:rgba(255,255,255,.72);
  text-align:center;
  font-size:8px;
  letter-spacing:.7px;
  text-transform:uppercase;
  background:
    linear-gradient(135deg,rgba(156,128,84,.25),rgba(187,90,42,.15)),
    #132840;
}
.news-sidebar-copy{ min-width:0; }
.news-sidebar-copy h3{
  display:-webkit-box;
  overflow:hidden;
  color:#0d1a2b;
  font-family:'Inter',sans-serif;
  font-size:13px;
  font-weight:700;
  line-height:1.35;
  -webkit-box-orient:vertical;
  -webkit-line-clamp:3;
}
.news-sidebar-copy p{
  margin-top:8px;
  color:#77818c;
  font-size:9px;
  line-height:1.4;
}
.news-sidebar-empty{
  padding:28px 22px;
  color:#66717e;
  font-size:12px;
  line-height:1.65;
}

@media(max-width:1080px){
  .news-detail-shell{ grid-template-columns:minmax(0,1fr) 310px; }
  .news-sidebar-item{ grid-template-columns:105px minmax(0,1fr); }
  .news-sidebar-thumb,
  .news-sidebar-thumb img,
  .news-sidebar-thumb video,
  .news-sidebar-placeholder{ height:74px; min-height:74px; }
}
@media(max-width:900px){
  .news-detail-shell{ grid-template-columns:1fr; }
  .news-detail-sidebar{ position:static; }
  .news-sidebar-list{ grid-template-columns:repeat(2,minmax(0,1fr)); }
  .news-sidebar-item:nth-child(odd):last-child{ grid-column:1 / -1; }
}
@media(max-width:660px){
  .news-detail-slides,
  .news-detail-slide,
  .news-detail-media-placeholder{ min-height:320px; }
  .news-detail-slide img,
  .news-detail-slide video{ height:320px; }
  .news-detail-copy{ padding:32px 24px 40px; }
  .news-detail-copy h1{ font-size:30px; }
  .news-carousel-button{ width:40px; height:40px; }
  .news-carousel-prev{ left:10px; }
  .news-carousel-next{ right:10px; }
  .news-sidebar-list{ grid-template-columns:1fr; }
  .news-sidebar-item:nth-child(odd):last-child{ grid-column:auto; }
}
@media(max-width:420px){
  .news-detail-slides,
  .news-detail-slide,
  .news-detail-media-placeholder{ min-height:250px; }
  .news-detail-slide img,
  .news-detail-slide video{ height:250px; }
  .news-detail-copy h1{ font-size:26px; }
  .news-detail-description{ font-size:14px; }
  .news-sidebar-item{ grid-template-columns:112px minmax(0,1fr); }
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
        <div class="nav-dropdown" data-home-nav-dropdown>
          <button
            type="button"
            class="nav-dropdown-trigger"
            data-home-nav-trigger
            aria-haspopup="true"
            aria-expanded="false"
            aria-controls="homeSectionMenu"
          >
            <span>Home</span>
            <svg class="nav-dropdown-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>

          <div class="nav-dropdown-menu" id="homeSectionMenu" role="menu">
            <a href="{{ route('landing') }}#home" role="menuitem">Home Overview</a>
            <a href="{{ route('landing') }}#about" role="menuitem">Our Heritage</a>
            <a href="{{ route('landing') }}#why" role="menuitem">Why Choose ArkCrest</a>
            <a href="{{ route('landing') }}#testimonials" role="menuitem">Words from Our Clients</a>
            <a href="{{ route('landing') }}#services" role="menuitem">The ArkCrest Distinction</a>
            <a href="{{ route('landing') }}#awards" role="menuitem">Our Awards</a>
            <a href="{{ route('landing') }}#inquire" role="menuitem">Begin Your Legacy</a>
          </div>
        </div>

        <a href="{{ route('news-updates') }}" class="active" data-news-nav>News &amp; Updates</a>
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



  <main class="latest-news-page">
    <div class="wrap">
      @php
        $requestedPostId = (int) request()->query('post', 0);
        $selectedPost = $requestedPostId
          ? $posts->getCollection()->first(function ($item) use ($requestedPostId) {
              return (int) $item->id === $requestedPostId;
            })
          : null;

        $otherPosts = $selectedPost
          ? $posts->getCollection()->reject(function ($item) use ($selectedPost) {
              return (int) $item->id === (int) $selectedPost->id;
            })->take(8)
          : collect();

        $allNewsUrl = route('news-updates');
        if (request()->filled('page')) {
          $allNewsUrl .= '?page=' . (int) request()->query('page');
        }
      @endphp

      @if($selectedPost)
        <a href="{{ $allNewsUrl }}" class="news-detail-back">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
          Back to all news
        </a>

        <section class="news-detail-shell" aria-label="Selected news update">
          <article class="news-detail-main">
            @if($selectedPost->media->count() > 0)
              <div class="news-detail-carousel" data-news-carousel tabindex="0" aria-label="Media for {{ $selectedPost->title }}">
                <div class="news-detail-slides">
                  @foreach($selectedPost->media as $mediaIndex => $media)
                    <figure
                      class="news-detail-slide"
                      data-carousel-slide
                      data-slide-index="{{ $mediaIndex }}"
                      @if($mediaIndex !== 0) hidden @endif
                    >
                      @if($media->media_type === 'image')
                        <img
                          src="{{ $media->url }}"
                          alt="{{ $selectedPost->title }} media {{ $mediaIndex + 1 }}"
                          @if($mediaIndex === 0) fetchpriority="high" @else loading="lazy" @endif
                        >
                      @else
                        <video
                          src="{{ $media->url }}"
                          controls
                          playsinline
                          preload="metadata"
                        ></video>
                      @endif
                    </figure>
                  @endforeach
                </div>

                @if($selectedPost->media->count() > 1)
                  <button type="button" class="news-carousel-button news-carousel-prev" data-carousel-prev aria-label="Show previous media">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                  </button>

                  <button type="button" class="news-carousel-button news-carousel-next" data-carousel-next aria-label="Show next media">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                  </button>

                  <div class="news-carousel-footer">
                    <span class="news-carousel-counter" data-carousel-counter>1 / {{ $selectedPost->media->count() }}</span>
                    <div class="news-carousel-dots" aria-label="Choose media">
                      @foreach($selectedPost->media as $mediaIndex => $media)
                        <button
                          type="button"
                          class="news-carousel-dot{{ $mediaIndex === 0 ? ' is-active' : '' }}"
                          data-carousel-dot="{{ $mediaIndex }}"
                          aria-label="Show media {{ $mediaIndex + 1 }}"
                          aria-current="{{ $mediaIndex === 0 ? 'true' : 'false' }}"
                        ></button>
                      @endforeach
                    </div>
                  </div>
                @endif
              </div>
            @else
              <div class="news-detail-media-placeholder">ArkCrest Realty Update</div>
            @endif

            <div class="news-detail-copy">
              <div class="latest-news-meta">
                <span>{{ $selectedPost->published_at->format('D M d Y') }}</span>
                <span class="latest-news-meta-divider" aria-hidden="true"></span>
                <span>ArkCrest Update</span>
              </div>

              <h1>{{ $selectedPost->title }}</h1>
              <div class="news-detail-description">
                {!! nl2br(e($selectedPost->description)) !!}
              </div>
            </div>
          </article>

          <aside class="news-detail-sidebar" aria-label="Other news posts">
            <div class="news-sidebar-head">
              <h2>Other News</h2>
              <a href="{{ $allNewsUrl }}">View all</a>
            </div>

            @if($otherPosts->count() > 0)
              <div class="news-sidebar-list">
                @foreach($otherPosts as $otherPost)
                  @php
                    $otherPrimaryMedia = $otherPost->media->first();
                  @endphp
                  <a
                    href="{{ request()->fullUrlWithQuery(['post' => $otherPost->id]) }}"
                    class="news-sidebar-item"
                    aria-label="Read {{ $otherPost->title }}"
                  >
                    <div class="news-sidebar-thumb">
                      @if($otherPrimaryMedia)
                        @if($otherPrimaryMedia->media_type === 'image')
                          <img src="{{ $otherPrimaryMedia->url }}" alt="" loading="lazy">
                        @else
                          <video src="{{ $otherPrimaryMedia->url }}" muted playsinline preload="metadata"></video>
                          <span class="news-sidebar-video-badge">Video</span>
                        @endif
                      @else
                        <div class="news-sidebar-placeholder">ArkCrest Update</div>
                      @endif
                    </div>
                    <div class="news-sidebar-copy">
                      <h3>{{ $otherPost->title }}</h3>
                      <p>{{ $otherPost->published_at->format('M d, Y') }}</p>
                    </div>
                  </a>
                @endforeach
              </div>
            @else
              <p class="news-sidebar-empty">There are no other published posts on this page yet.</p>
            @endif
          </aside>
        </section>
      @else
        <header class="latest-news-header reveal">
          <h1>Latest News</h1>
          <p>Official ArkCrest Realty announcements, property highlights, recognitions, activities, and community updates.</p>
        </header>

        @if($posts->count() === 0)
          <section class="latest-news-empty reveal">
            <h2>No posts yet.</h2>
            <p>Published News &amp; Updates posts will appear here.</p>
          </section>
        @else
          <section class="latest-news-grid reveal-stagger" aria-label="Published News and Updates">
            @foreach($posts as $post)
              @php
                $primaryMedia = $post->media->first();
                $extraMediaCount = max(0, $post->media->count() - 1);
              @endphp

              <article class="latest-news-card" id="news-post-{{ $post->id }}">
                <div class="latest-news-card-media">
                  @if($primaryMedia)
                    <figure>
                      @if($primaryMedia->media_type === 'image')
                        <img src="{{ $primaryMedia->url }}" alt="{{ $post->title }}" loading="lazy">
                      @else
                        <video src="{{ $primaryMedia->url }}" muted playsinline preload="metadata"></video>
                      @endif
                    </figure>

                    @if($extraMediaCount > 0)
                      <span class="latest-news-media-count">+{{ $extraMediaCount }} media</span>
                    @endif
                  @else
                    <div class="latest-news-placeholder">ArkCrest Realty Update</div>
                  @endif
                </div>

                <div class="latest-news-card-copy">
                  <div class="latest-news-meta">
                    <span>{{ $post->published_at->format('D M d Y') }}</span>
                    <span class="latest-news-meta-divider" aria-hidden="true"></span>
                    <span>ArkCrest Update</span>
                  </div>

                  <h2>{{ $post->title }}</h2>
                  <p class="latest-news-excerpt">
                    {{ \Illuminate\Support\Str::limit($post->description, 150) }}
                  </p>

                  <a
                    href="{{ request()->fullUrlWithQuery(['post' => $post->id]) }}"
                    class="news-card-read-more"
                    aria-label="Read the full update: {{ $post->title }}"
                  >Read More</a>
                </div>
              </article>
            @endforeach
          </section>

          @if($posts->hasPages())
            <nav class="latest-news-pagination" aria-label="News and Updates pages">
              @if($posts->onFirstPage())
                <span class="disabled">Previous</span>
              @else
                <a href="{{ $posts->previousPageUrl() }}">Previous</a>
              @endif

              <span>Page {{ $posts->currentPage() }} of {{ $posts->lastPage() }}</span>

              @if($posts->hasMorePages())
                <a href="{{ $posts->nextPageUrl() }}">Next</a>
              @else
                <span class="disabled">Next</span>
              @endif
            </nav>
          @endif
        @endif
      @endif
    </div>
  </main>


  <!-- CTA AND FOOTER -->
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
        <a href="{{ route('landing') }}">Home</a>
        <a href="{{ route('news-updates') }}">News &amp; Updates</a>
        <a href="https://facebook.com/ArkCrestRealty" target="_blank" rel="noopener noreferrer">Facebook</a>
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
    var homeDropdown = document.querySelector('[data-home-nav-dropdown]');
    var homeTrigger = document.querySelector('[data-home-nav-trigger]');

    function setHomeDropdown(open) {
      if (!homeDropdown || !homeTrigger) return;
      homeDropdown.classList.toggle('open', open);
      homeTrigger.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    function closeMobileMenu() {
      if (!links || !toggle) return;
      links.classList.remove('open');
      toggle.setAttribute('aria-expanded', 'false');
      setHomeDropdown(false);
    }

    if (toggle && links) {
      toggle.addEventListener('click', function () {
        var isOpen = links.classList.toggle('open');
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        if (!isOpen) setHomeDropdown(false);
      });
    }

    if (homeTrigger) {
      homeTrigger.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        setHomeDropdown(!homeDropdown.classList.contains('open'));
      });
    }

    if (links) {
      links.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', closeMobileMenu);
      });
    }

    document.addEventListener('click', function (event) {
      if (homeDropdown && !event.target.closest('[data-home-nav-dropdown]')) {
        setHomeDropdown(false);
      }
      if (!event.target.closest('.nav')) {
        closeMobileMenu();
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        setHomeDropdown(false);
        closeMobileMenu();
      }
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


  <script>
  (function () {
    var carousels = document.querySelectorAll('[data-news-carousel]');

    Array.prototype.forEach.call(carousels, function (carousel) {
      var slides = Array.prototype.slice.call(carousel.querySelectorAll('[data-carousel-slide]'));
      var dots = Array.prototype.slice.call(carousel.querySelectorAll('[data-carousel-dot]'));
      var previousButton = carousel.querySelector('[data-carousel-prev]');
      var nextButton = carousel.querySelector('[data-carousel-next]');
      var counter = carousel.querySelector('[data-carousel-counter]');
      var currentIndex = 0;
      var touchStartX = null;

      if (slides.length < 2) return;

      function pauseVideos() {
        slides.forEach(function (slide) {
          var video = slide.querySelector('video');
          if (video && !video.paused) video.pause();
        });
      }

      function showSlide(index) {
        if (index < 0) index = slides.length - 1;
        if (index >= slides.length) index = 0;

        pauseVideos();
        currentIndex = index;

        slides.forEach(function (slide, slideIndex) {
          slide.hidden = slideIndex !== currentIndex;
        });

        dots.forEach(function (dot, dotIndex) {
          var active = dotIndex === currentIndex;
          dot.classList.toggle('is-active', active);
          dot.setAttribute('aria-current', active ? 'true' : 'false');
        });

        if (counter) counter.textContent = (currentIndex + 1) + ' / ' + slides.length;
      }

      if (previousButton) {
        previousButton.addEventListener('click', function () {
          showSlide(currentIndex - 1);
        });
      }

      if (nextButton) {
        nextButton.addEventListener('click', function () {
          showSlide(currentIndex + 1);
        });
      }

      dots.forEach(function (dot) {
        dot.addEventListener('click', function () {
          showSlide(parseInt(dot.getAttribute('data-carousel-dot'), 10) || 0);
        });
      });

      carousel.addEventListener('keydown', function (event) {
        if (event.key === 'ArrowLeft') {
          event.preventDefault();
          showSlide(currentIndex - 1);
        }
        if (event.key === 'ArrowRight') {
          event.preventDefault();
          showSlide(currentIndex + 1);
        }
      });

      carousel.addEventListener('touchstart', function (event) {
        touchStartX = event.changedTouches[0].clientX;
      }, { passive:true });

      carousel.addEventListener('touchend', function (event) {
        if (touchStartX === null) return;
        var difference = event.changedTouches[0].clientX - touchStartX;
        touchStartX = null;
        if (Math.abs(difference) < 45) return;
        showSlide(difference > 0 ? currentIndex - 1 : currentIndex + 1);
      }, { passive:true });

      showSlide(0);
    });
  })();
  </script>


</body>
</html>
