<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Services | ArkCrest Realty</title>
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

  /* -------- placeholder blocks (swap these for real photos) -------- */
  .ph{
    position:relative; display:flex; align-items:center; justify-content:center; text-align:center;
    background:
      repeating-linear-gradient(135deg, rgba(19,40,64,0.06) 0 12px, rgba(19,40,64,0.02) 12px 24px),
      linear-gradient(160deg,#dfe6ea,#c7d2d9);
    border:1px dashed #9fb0bd; color:#4a5a68;
    font-family:'Inter',sans-serif; font-size:13px; letter-spacing:.5px; overflow:hidden;
  }
  .ph span{ background:rgba(255,255,255,0.85); padding:6px 14px; border-radius:3px; font-weight:600; }

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

  /* -------- HERO -------- */
  .hero{ height:100vh; height:100svh; min-height:560px; position:relative; display:flex; align-items:center; justify-content:center; color:#fff; overflow:hidden; }
  .hero::after{
    content:''; position:absolute; inset:0;
    background:linear-gradient(180deg, rgba(8,16,26,0.55) 0%, rgba(8,16,26,0.35) 40%, rgba(8,16,26,0.72) 100%);
  }
  .hero-content{ position:relative; z-index:2; text-align:center; max-width:760px; padding:0 24px; }
  .hero-content h1{ font-size:clamp(32px,5.2vw,58px); color:#fff; margin-bottom:22px; }
  .hero-content p{ font-size:16px; color:rgba(255,255,255,0.82); max-width:520px; margin:0 auto 30px; }
  .hero-content .eyebrow{ justify-content:center; margin-bottom:14px; }
  .hero-cta{ font-size:12px; letter-spacing:3px; text-transform:uppercase; font-weight:700; color:#fff; display:inline-flex; align-items:center; gap:14px; }
  .hero-cta .rule{ width:34px; height:1px; background:var(--orange); }

  /* -------- OUR EXPERTISE -------- */
  .expertise{ padding:clamp(64px, 12vw, 140px) 0; background:#fff; }
  .expertise-head{ text-align:center; max-width:620px; margin:0 auto 56px; }
  .expertise-head .eyebrow{ justify-content:center; margin-bottom:16px; }
  .expertise-head h2{ font-size:clamp(28px, 3.6vw, 42px); color:var(--navy-900); }
  .expertise-head .divider{ width:40px; height:1px; background:var(--orange); margin:22px auto 0; }

  .service-grid{ display:grid; grid-template-columns:1fr 1fr 1fr; gap:36px; }
  .service-card .ph{ aspect-ratio:3/4; border-radius:2px; margin-bottom:22px; }
  .service-card h3{ font-style:italic; font-size:20px; color:var(--navy-900); margin-bottom:10px; }
  .service-card p{ font-size:14px; color:var(--ink-soft); }

  .text-link{
    display:inline-flex; align-items:center; gap:12px;
    font-size:12px; letter-spacing:2px; text-transform:uppercase; font-weight:700;
    border-bottom:1px solid currentColor; padding-bottom:6px;
  }
  .text-link.navy{ color:var(--navy-900); }
  .text-link.orange{ color:var(--orange); }

  /* -------- EXCLUSIVE PROPERTIES (dark, portfolio teaser) -------- */
  .exclusive{ background:var(--navy-950); color:#fff; padding:clamp(64px, 12vw, 140px) 0; }
  .exclusive-head{ display:flex; align-items:flex-end; justify-content:space-between; gap:40px; margin-bottom:44px; }
  .exclusive-head h2{ font-size:clamp(28px, 3.6vw, 42px); color:#fff; margin-top:10px; }
  .exclusive-head .text-link{ color:var(--orange); white-space:nowrap; }

  .estate-grid-2{ display:grid; grid-template-columns:1fr 1fr; gap:24px; }
  .estate-tile{
    position:relative; aspect-ratio:4/3.4; border-radius:2px; overflow:hidden;
    background-size:cover; background-position:center;
  }
  .estate-tile::after{
    content:''; position:absolute; inset:0;
    background:linear-gradient(180deg, rgba(11,28,48,0) 40%, rgba(11,28,48,0.85) 100%);
  }
  .estate-tile .tile-content{ position:absolute; left:28px; right:28px; bottom:26px; z-index:2; }
  .estate-tile .tag{ font-size:11px; letter-spacing:2px; text-transform:uppercase; color:var(--orange); font-weight:700; margin-bottom:8px; display:block; }
  .estate-tile h3{ font-style:italic; font-size:24px; color:#fff; }

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

    .service-grid{ grid-template-columns:1fr; }
    .exclusive-head{ flex-direction:column; align-items:flex-start; gap:16px; }
    .estate-grid-2{ grid-template-columns:1fr; }

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
        <a href="{{ route('services') }}" class="active">Services</a>
        <a href="{{ route('portfolio') }}">Portfolio</a>
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

  {{-- HERO --}}
  <section class="hero">
    <div class="hero-photo" style="background-image: url('{{ asset('images/testing-image.jpg') }}');"></div>
    <div class="hero-content">
      <span class="eyebrow on-dark"><span class="rule"></span>Established 2024</span>
      <h1><em>Preserving</em> Legacy<br><em>Through Land.</em></h1>
      <p>Curating the finest land opportunities for the most discerning investors across the nation.</p>
      <a href="#our-expertise" class="hero-cta">Explore Services <span class="rule"></span></a>
    </div>
  </section>

  {{-- OUR EXPERTISE --}}
  <section class="expertise" id="our-expertise">
    <div class="wrap">
      <div class="expertise-head">
        <span class="eyebrow"><span class="rule"></span>Distinguished Portfolio</span>
        <h2><em>Our</em> Expertise</h2>
        <div class="divider"></div>
      </div>
      <div class="service-grid">
        <div class="service-card">
          <div class="ph"><span>Premium Land Sales photo</span></div>
          <h3>Premium Land Sales</h3>
          <p>Curated residential and commercial parcels in the region's most coveted development zones.</p>
        </div>
        <div class="service-card">
          <div class="ph"><span>Estate Consultation photo</span></div>
          <h3>Estate Consultation</h3>
          <p>Strategic analysis to ensure your land acquisition aligns with future market appreciation.</p>
        </div>
        <div class="service-card">
          <div class="ph"><span>Priority Tripping photo</span></div>
          <h3>Priority Tripping</h3>
          <p>Privately guided site inspections tailored to your schedule and specific investment criteria.</p>
        </div>
      </div>
    </div>
  </section>

  {{-- EXCLUSIVE PROPERTIES (portfolio teaser) --}}
  <section class="exclusive">
    <div class="wrap">
      <div class="exclusive-head">
        <div>
          <span class="eyebrow on-dark">The Collection</span>
          <h2><em>Exclusive</em> Properties</h2>
        </div>
        <a href="{{ route('portfolio') }}" class="text-link">View All <span class="rule"></span></a>
      </div>
      <div class="estate-grid-2">
        <a href="{{ route('portfolio') }}" class="estate-tile">
          <div class="tile-content">
            <span class="tag">Residential</span>
            <h3>The Golden Crest Estates</h3>
          </div>
        </a>
        <a href="{{ route('portfolio') }}" class="estate-tile">
          <div class="tile-content">
            <span class="tag">Agricultural</span>
            <h3>Heritage Farmlands</h3>
          </div>
        </a>
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