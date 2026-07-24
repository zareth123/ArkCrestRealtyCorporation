<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Portfolio | ArkCrest Realty</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
    overflow-x:hidden;
  }
  h1,h2,h3{ font-family:'Playfair Display',serif; line-height:1.15; font-weight:600; }
  em, .italic{ font-style:italic; font-weight:500; }
  a{ color:inherit; text-decoration:none; }
  img{ max-width:100%; display:block; }

  .eyebrow{
    font-size:12px; letter-spacing:3px; text-transform:uppercase; font-weight:600;
    color:var(--orange); display:inline-flex; align-items:center; gap:10px;
  }
  .eyebrow.on-dark{ color:#7fa8d6; }
  .eyebrow .rule{ display:inline-block; width:26px; height:1px; background:currentColor; opacity:.7; }

  .wrap{ max-width:1240px; margin:0 auto; padding:0 clamp(20px, 4vw, 40px); }
  section{ position:relative; }

  .hero-photo{ position:absolute; inset:0; background:var(--navy-900) center/cover no-repeat; }

  /* -------- NAV -------- */
  .nav{ position:absolute; top:0; left:0; right:0; z-index:50; padding:clamp(18px, 3.5vw, 28px) 0; }
  .nav .wrap{ display:flex; align-items:center; justify-content:space-between; }
  .brand{ display:flex; align-items:center; gap:12px; color:#fff; }
  .brand .mark{ width:34px; height:34px; border-radius:50%; background:#fff; }
  .brand .name{ font-size:14px; letter-spacing:3px; font-weight:700; text-transform:uppercase; white-space:nowrap; }
  .nav-links{ display:flex; gap:44px; }
  .nav-links a{
    color:rgba(255,255,255,0.88); font-size:12px; letter-spacing:2px; text-transform:uppercase; font-weight:600;
    padding-bottom:8px; border-bottom:2px solid transparent; transition:.25s;
  }
  .nav-links a.active, .nav-links a:hover{ border-color:var(--orange); color:#fff; }
  .nav-actions{ display:flex; align-items:center; gap:22px; }
  .nav-links-mobile-actions{ display:none; }
  .staff-login{ font-size:11px; letter-spacing:1.5px; text-transform:uppercase; color:rgba(255,255,255,0.65); font-weight:600; transition:.2s; }
  .staff-login:hover{ color:#fff; }
  .btn{ display:inline-block; padding:13px 26px; font-size:12px; letter-spacing:2px; text-transform:uppercase; font-weight:700; border-radius:2px; transition:.25s; }
  .btn-orange{ background:var(--orange); color:#fff; }
  .btn-orange:hover{ background:var(--orange-dark); }

  .mobile-toggle{ display:none; flex-direction:column; justify-content:center; gap:5px; cursor:pointer; width:36px; height:36px; z-index:60; position:relative; }
  .mobile-toggle span{ width:24px; height:2px; background:#fff; transition:transform .25s, opacity .25s; transform-origin:center; }
  .mobile-toggle.open span:nth-child(1){ transform:translateY(7px) rotate(45deg); }
  .mobile-toggle.open span:nth-child(2){ opacity:0; }
  .mobile-toggle.open span:nth-child(3){ transform:translateY(-7px) rotate(-45deg); }

  /* -------- HERO BANNER (shorter than Home/About — this page gets to the point fast) -------- */
  .hero{ height:clamp(420px, 55vh, 620px); position:relative; display:flex; align-items:center; justify-content:center; color:#fff; overflow:hidden; }
  .hero::after{
    content:''; position:absolute; inset:0;
    background:linear-gradient(180deg, rgba(8,16,26,0.6) 0%, rgba(8,16,26,0.4) 45%, rgba(8,16,26,0.75) 100%);
  }
  .hero-content{ position:relative; z-index:2; text-align:center; max-width:700px; padding:0 24px; }
  .hero-content h1{ font-size:clamp(30px,4.6vw,50px); color:#fff; margin-bottom:18px; }
  .hero-content p{ font-size:15px; color:rgba(255,255,255,0.82); max-width:480px; margin:0 auto; }
  .hero-content .eyebrow{ justify-content:center; margin-bottom:14px; }

  /* -------- COLLECTION -------- */
  .collection{ padding:clamp(64px, 12vw, 130px) 0; background:var(--cream); }
  .collection-head{ text-align:center; max-width:640px; margin:0 auto clamp(40px, 7vw, 64px); }
  .collection-head .eyebrow{ justify-content:center; margin-bottom:14px; }
  .collection-head h2{ font-size:clamp(28px,3.6vw,42px); color:var(--navy-900); margin-bottom:18px; }
  .collection-head .divider{ width:40px; height:2px; background:var(--orange); margin:0 auto 22px; }
  .collection-head p{ color:var(--ink-soft); font-size:15px; }

  .collection-grid{
    display:grid;
    grid-template-columns:repeat(3, 1fr);
    grid-template-rows:clamp(280px, 34vw, 420px) clamp(200px, 22vw, 300px);
    gap:24px;
  }
  .collection-grid .tile:first-child{ grid-column:span 2; }

  .tile{
    position:relative; border-radius:2px; overflow:hidden;
    background:
      repeating-linear-gradient(135deg, rgba(19,40,64,0.06) 0 12px, rgba(19,40,64,0.02) 12px 24px),
      linear-gradient(160deg,#dfe6ea,#c7d2d9);
    border:1px dashed #9fb0bd;
  }
  .tile.no-photo{
    background:var(--navy-800);
    border:none;
  }
  .tile::after{
    content:''; position:absolute; inset:0;
    background:linear-gradient(180deg, rgba(13,26,43,0) 40%, rgba(13,26,43,0.78) 100%);
  }
  .tile .ph-note{
    position:absolute; top:16px; left:16px; z-index:2;
    font-size:11px; letter-spacing:.5px; color:#4a5a68;
    background:rgba(255,255,255,0.85); padding:4px 10px; border-radius:3px; font-weight:600;
  }
  .tile.no-photo .ph-note{ display:none; }
  .tile-content{ position:absolute; left:0; right:0; bottom:0; z-index:2; padding:22px 24px; color:#fff; }
  .tile-content .tag{ display:block; font-size:11px; letter-spacing:2px; text-transform:uppercase; font-weight:700; color:var(--orange); margin-bottom:8px; }
  .tile-content h3{ font-style:italic; font-size:22px; color:#fff; margin-bottom:6px; }
  .tile-content .meta{ font-size:11px; letter-spacing:1px; text-transform:uppercase; color:rgba(255,255,255,0.72); }

  footer{ background:#0a1622; color:#8b9aab; padding:56px 0 30px; font-size:13px; }
  footer .wrap{ display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:20px; }
  footer .brand{ color:#fff; }

  /* -------- RESPONSIVE -------- */
  @media (max-width:900px){
    .mobile-toggle{ display:flex; }
    .nav-links{
      display:none; position:absolute; top:100%; left:0; right:0;
      flex-direction:column; gap:0; background:rgba(10,20,32,0.98);
      padding:8px 24px 20px; backdrop-filter:blur(6px);
    }
    .nav-links.open{ display:flex; }
    .nav-links a{ width:100%; text-align:center; padding:14px 0; border-bottom:1px solid rgba(255,255,255,0.08); }
    .nav-links a:last-child{ border-bottom:none; }
    .staff-login{ display:none; }
    .nav-actions{ display:none; }
    .nav-links-mobile-actions{
      display:flex; flex-direction:column; gap:12px;
      width:100%; margin-top:10px;
    }
    .nav-links-mobile-actions a{ padding:0; border-bottom:none; }
    .nav-links-mobile-actions .staff-login-mobile{
      text-align:center; padding:10px 0; font-size:12px; letter-spacing:1.5px;
      text-transform:uppercase; color:rgba(255,255,255,0.65); font-weight:600;
    }
    .nav-links-mobile-actions .btn{ width:100%; text-align:center; }

    .collection-grid{
      grid-template-columns:1fr;
      grid-template-rows:none;
      gap:20px;
    }
    .collection-grid .tile{ aspect-ratio:4/3; }
    .collection-grid .tile:first-child{ grid-column:auto; aspect-ratio:16/11; }

    footer .wrap{ flex-direction:column; text-align:center; }
  }

  @media (max-width:520px){
    .wrap{ padding:0 20px; }
    .brand .name{ font-size:12px; letter-spacing:2px; }
    .btn{ padding:11px 18px; font-size:11px; }
  }
</style>
</head>
<link rel="icon" type="image/png" href="{{ asset('images/ArkCrest_Logo.png') }}">
<body>

  {{-- NAV --}}
  <nav class="nav">
    <div class="wrap">
      <div class="brand">
        <div class="mark "style="background-image: url('{{ asset('images/ArkCrest_Logo.png') }}');background-size: cover; background-position: center; background-repeat: no-repeat;"></div>
        <div class="name">ArkCrest Realty</div>
      </div>
      <div class="nav-links">
        <a href="{{ url('/') }}">Home</a>
        <a href="{{ route('about') }}">About</a>
        <a href="{{ route('services') }}">Services</a>
        <a href="{{ route('portfolio') }}" class="active">Portfolio</a>
        <div class="nav-links-mobile-actions">
          <a href="{{ route('login') }}" class="staff-login-mobile">Staff Login</a>
          <a href="{{ url('/#inquire') }}" class="btn btn-orange">Inquire Now</a>
        </div>
      </div>
      <div class="nav-actions">
        <a href="{{ route('login') }}" class="staff-login">Staff Login</a>
        <a href="{{ url('/#inquire') }}" class="btn btn-orange">Inquire Now</a>
      </div>
      <div class="mobile-toggle"><span></span><span></span><span></span></div>
    </div>
  </nav>

  {{-- HERO BANNER --}}
  <section class="hero">
    <div class="hero-photo" style="background-image: url('{{ asset('images/testing-image.jpg') }}');"></div>
    <div class="hero-content">
      <span class="eyebrow on-dark"><span class="rule"></span>Our Holdings</span>
      <h1><em>The</em> Portfolio.</h1>
      <p>A curated selection of land assets across the nation's most promising growth corridors.</p>
    </div>
  </section>

  {{-- THE COLLECTION --}}
  <section class="collection">
    <div class="wrap">
      <div class="collection-head">
        <span class="eyebrow">Curated Holdings</span>
        <h2><em>The</em> Collection</h2>
        <div class="divider"></div>
        <p>Explore a hand-selected portfolio of the nation's most prestigious land assets, ranging from coastal escapes to mountain-view estates.</p>
      </div>

      <div class="collection-grid">
        <div class="tile">
          <span class="ph-note">Photo placeholder</span>
          <div class="tile-content">
            <span class="tag">Highland Estate</span>
            <h3>The Golden Crest Estates</h3>
            <span class="meta">12.5 Hectares &bull; Tagaytay Ridge</span>
          </div>
        </div>
        <div class="tile">
          <span class="ph-note">Photo placeholder</span>
          <div class="tile-content">
            <span class="tag">Agricultural</span>
            <h3>Heritage Farmlands</h3>
            <span class="meta">9.8 Hectares &bull; Bukidnon Valley</span>
          </div>
        </div>
        <div class="tile">
          <span class="ph-note">Photo placeholder</span>
          <div class="tile-content">
            <span class="tag">Coastal</span>
            <h3>Azure Waters Reserve</h3>
            <span class="meta">2.8 Hectares &bull; Cagayan</span>
          </div>
        </div>
        <div class="tile no-photo">
          <div class="tile-content">
            <span class="tag">Development</span>
            <h3>Vanguard Peak</h3>
            <span class="meta">15 Hectares &bull; Antipolo Heights</span>
          </div>
        </div>
        <div class="tile">
          <span class="ph-note">Photo placeholder</span>
          <div class="tile-content">
            <span class="tag">Residential</span>
            <h3>The Sovereign Grove</h3>
            <span class="meta">4.2 Hectares &bull; Laguna</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <footer>
    <div class="wrap">
      <div class="brand" style="font-size:13px; letter-spacing:2px; text-transform:uppercase; font-weight:700;">ArkCrest Realty</div>
      <div>&copy; {{ date('Y') }} ArkCrest Realty Corporation. All rights reserved.</div>
    </div>
  </footer>

  <script>
    const toggle = document.querySelector('.mobile-toggle');
    const links = document.querySelector('.nav-links');

    function setMenu(open){
      links.classList.toggle('open', open);
      toggle.classList.toggle('open', open);
      toggle.setAttribute('aria-expanded', open);
      document.body.style.overflow = open ? 'hidden' : '';
    }

    toggle.setAttribute('role', 'button');
    toggle.setAttribute('aria-label', 'Toggle navigation menu');
    toggle.setAttribute('aria-expanded', 'false');

    toggle.addEventListener('click', () => setMenu(!links.classList.contains('open')));
    links.querySelectorAll('a').forEach(link => link.addEventListener('click', () => setMenu(false)));
    window.addEventListener('resize', () => { if (window.innerWidth > 900) setMenu(false); });
  </script>

  <script src="{{ asset('js/landing-effects.js') }}"></script>

</body>
</html>