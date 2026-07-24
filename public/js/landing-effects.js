// ArkCrest Realty — Landing Page Hover & Interaction Effects
// Used on: welcome, About, Services, Portfolio
// Safe to include on every landing page — every block below checks
// that its target elements exist before doing anything, so it will
// never throw an error on a page that doesn't have a given section.
(function () {
  'use strict';

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var isTouch = window.matchMedia('(hover: none), (pointer: coarse)').matches;

  document.addEventListener('DOMContentLoaded', function () {
    injectStyles();
    initStickyNav();
    initButtonEffects();
    initCardTilt();
    initImageZoom();
    initNavUnderline();
    initScrollReveal();
    initCarouselArrowPulse();
  });

  /* ---------------------------------------------------------------
     0. Inject the small amount of CSS the effects below rely on.
        Kept in JS so nothing in the existing <style> blocks has
        to be touched — this file is purely additive.
  --------------------------------------------------------------- */
  function injectStyles() {
    var css = `
      /* Sticky nav: overridden from the page's own position:absolute */
      .nav{
        position:fixed !important;
        transition:background-color .35s ease, padding .35s ease, box-shadow .35s ease;
      }
      .nav.is-scrolled{
        background-color:rgba(13,26,43,0.96);
        padding-top:14px; padding-bottom:14px;
        box-shadow:0 8px 24px -14px rgba(0,0,0,0.4);
      }
      body{ padding-top:0; }

      .btn{ position:relative; overflow:hidden; will-change:transform; }
      .btn .ripple{
        position:absolute; border-radius:50%; pointer-events:none;
        background:rgba(255,255,255,0.55);
        transform:scale(0); opacity:.6;
        animation:btn-ripple .6s ease-out forwards;
      }
      .btn-outline .ripple{ background:rgba(211,101,47,0.35); }
      @keyframes btn-ripple{ to{ transform:scale(2.6); opacity:0; } }

      .text-link{ transition:letter-spacing .3s ease, color .25s ease; }
      .text-link:hover{ letter-spacing:2.6px; color:var(--orange,#d3652f); }
      .text-link:hover .rule{ width:44px !important; transition:width .3s ease; }
      .text-link .rule{ transition:width .3s ease; }

      .service-card, .estate-card, .stat-card, .why-item, .tile, .estate-tile{
        transition:transform .45s cubic-bezier(.2,.8,.2,1), box-shadow .45s ease;
        will-change:transform;
      }
      .service-card:hover, .estate-card:hover, .stat-card:hover, .tile:hover, .estate-tile:hover{
        box-shadow:0 22px 44px -18px rgba(13,26,43,0.35);
      }
      .why-item{ position:relative; }
      .why-item:hover .no{ color:var(--navy-900,#132840); transition:color .3s ease; }
      .why-item .no{ transition:color .3s ease, transform .3s ease; display:inline-block; }
      .why-item:hover .no{ transform:translateX(4px); }

      .ph{ overflow:hidden; }
      .ph, .hero-photo{ transition:transform .6s cubic-bezier(.2,.8,.2,1); }
      .service-card:hover .ph, .estate-card:hover .ph, .about .ph:hover,
      .collage .ph:hover, .tile:hover .ph, .estate-tile:hover .ph{
        transform:scale(1.045);
      }

      .nav-links a{ position:relative; }
      .nav-links a .u-line{
        position:absolute; left:0; bottom:0; height:2px; width:0;
        background:var(--orange,#d3652f); transition:width .3s ease;
      }
      .nav-links a:hover .u-line{ width:100%; }

      .carousel-arrow{ transition:transform .25s ease, background .25s ease; }
      .carousel-arrow:hover{ transform:scale(1.08); }
      .carousel-arrow:active{ transform:scale(0.94); }

      .pill{ transition:transform .3s ease; }
      .pill:hover{ transform:translateY(-3px); }
      .pill:hover .dot{ background:#fff; box-shadow:0 0 0 3px var(--orange,#d3652f); transition:.25s; }

      [data-reveal]{
        opacity:0; transform:translateY(28px);
        transition:opacity .7s ease, transform .7s cubic-bezier(.2,.8,.2,1);
      }
      [data-reveal].is-visible{ opacity:1; transform:translateY(0); }
    `;
    var style = document.createElement('style');
    style.setAttribute('data-source', 'landing-effects');
    style.textContent = css;
    document.head.appendChild(style);
  }

  /* ---------------------------------------------------------------
     0.5 Sticky nav — stays fixed on scroll; gains a solid navy
         background once you scroll past the hero so the white
         logo/links stay readable over light sections underneath.
  --------------------------------------------------------------- */
  function initStickyNav() {
    var nav = document.querySelector('.nav');
    if (!nav) return;

    var THRESHOLD = 60;

    function update() {
      var scrolled = window.scrollY > THRESHOLD;
      nav.classList.toggle('is-scrolled', scrolled);
    }

    window.addEventListener('scroll', update, { passive: true });
    update(); // set correct state on load (e.g. if page opens mid-scroll)
  }

  /* ---------------------------------------------------------------
     1. Buttons — soft magnetic pull toward the cursor + click ripple
  --------------------------------------------------------------- */
  function initButtonEffects() {
    var buttons = document.querySelectorAll('.btn');
    if (!buttons.length) return;

    buttons.forEach(function (btn) {
      if (!isTouch && !reduceMotion) {
        btn.addEventListener('mousemove', function (e) {
          var rect = btn.getBoundingClientRect();
          var x = e.clientX - rect.left - rect.width / 2;
          var y = e.clientY - rect.top - rect.height / 2;
          btn.style.transform = 'translate(' + (x * 0.12) + 'px,' + (y * 0.28) + 'px)';
        });
        btn.addEventListener('mouseleave', function () {
          btn.style.transform = '';
        });
      }

      btn.addEventListener('click', function (e) {
        var rect = btn.getBoundingClientRect();
        var ripple = document.createElement('span');
        var size = Math.max(rect.width, rect.height);
        ripple.className = 'ripple';
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
        ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
        btn.appendChild(ripple);
        window.setTimeout(function () { ripple.remove(); }, 650);
      });
    });
  }

  /* ---------------------------------------------------------------
     2. Cards — gentle 3D tilt that follows the cursor
  --------------------------------------------------------------- */
  function initCardTilt() {
    if (isTouch || reduceMotion) return;

    var selector = '.service-card, .estate-card, .stat-card, .tile, .estate-tile';
    var cards = document.querySelectorAll(selector);
    if (!cards.length) return;

    cards.forEach(function (card) {
      card.style.transformStyle = 'preserve-3d';
      card.style.perspective = '900px';

      card.addEventListener('mousemove', function (e) {
        var rect = card.getBoundingClientRect();
        var px = (e.clientX - rect.left) / rect.width;
        var py = (e.clientY - rect.top) / rect.height;
        var rotateX = (0.5 - py) * 6;
        var rotateY = (px - 0.5) * 8;
        card.style.transform =
          'perspective(900px) rotateX(' + rotateX + 'deg) rotateY(' + rotateY + 'deg) translateY(-6px)';
      });

      card.addEventListener('mouseleave', function () {
        card.style.transform = '';
      });
    });
  }

  /* ---------------------------------------------------------------
     3. Photo placeholders — subtle zoom handled purely via CSS
        (see injectStyles). Here we just make sure any dynamically
        inserted real <img> tags inside .ph also participate.
  --------------------------------------------------------------- */
  function initImageZoom() {
    document.querySelectorAll('.ph img').forEach(function (img) {
      img.style.transition = 'transform .6s cubic-bezier(.2,.8,.2,1)';
      img.style.width = '100%';
    });
  }

  /* ---------------------------------------------------------------
     4. Nav links — animated underline that grows from the left
  --------------------------------------------------------------- */
  function initNavUnderline() {
    var links = document.querySelectorAll('.nav-links a:not(.staff-login-mobile)');
    links.forEach(function (link) {
      if (link.querySelector('.u-line')) return;
      var line = document.createElement('span');
      line.className = 'u-line';
      link.appendChild(line);
    });
  }

  /* ---------------------------------------------------------------
     5. Scroll reveal — fade/slide up sections & cards as they enter
        the viewport. Adds [data-reveal] to sensible elements.
  --------------------------------------------------------------- */
  function initScrollReveal() {
    var targets = document.querySelectorAll(
      '.section-head, .service-card, .estate-card, .stat-card, .why-item, ' +
      '.tile, .estate-tile, .about .grid > *, .philosophy .grid > *, .distinction-content'
    );
    if (!targets.length) return;

    if (reduceMotion || !('IntersectionObserver' in window)) {
      targets.forEach(function (el) { el.removeAttribute('data-reveal'); });
      return;
    }

    targets.forEach(function (el) { el.setAttribute('data-reveal', ''); });

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

    targets.forEach(function (el) { observer.observe(el); });
  }

  /* ---------------------------------------------------------------
     6. Carousel arrows — small pulse the first time they're usable
  --------------------------------------------------------------- */
  function initCarouselArrowPulse() {
    var nextBtn = document.getElementById('estate-next');
    if (!nextBtn || reduceMotion) return;
    window.setTimeout(function () {
      nextBtn.animate(
        [{ transform: 'scale(1)' }, { transform: 'scale(1.15)' }, { transform: 'scale(1)' }],
        { duration: 700, easing: 'ease-in-out' }
      );
    }, 900);
  }
})();