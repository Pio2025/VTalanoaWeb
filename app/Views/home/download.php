<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Download VTalanoa — Free on every platform</title>
<link rel="icon" href="<?= base_url('favicon.ico') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="<?= base_url('css/public.css') ?>" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --navy:       #262262;
    --blue:       #1c75bc;
    --blue-light: #e5f1fa;
    --blue-mid:   #d2e7f6;
    --text:       #1e293b;
    --muted:      #5d5c82;
    --line:       rgba(38,34,98,.12);
    --bg:         #f8fafc;
    --surface:    #ffffff;
    --radius:     14px;
    --shadow:     0 4px 24px rgba(38,34,98,.10);
    --shadow-lg:  0 16px 48px rgba(38,34,98,.16);
  }

  html { scroll-behavior: smooth; }
  body { font-family: 'Inter', system-ui, sans-serif; color: var(--text); background: var(--bg); line-height: 1.6; }
  h1, h2, h3 { font-family: 'Bricolage Grotesque', 'Inter', sans-serif; line-height: 1.12; }
  a { color: var(--blue); text-decoration: none; }
  a:hover { text-decoration: underline; }

  .wrap { max-width: 1160px; margin: 0 auto; padding: 0 24px; }

  /* ── Hero ── */
  .hero {
    background: linear-gradient(145deg, var(--navy) 0%, #1a3068 55%, var(--blue) 100%);
    position: relative; overflow: hidden;
    padding: 88px 24px 80px; text-align: center; color: #fff;
  }
  .hero::before {
    content: '';
    position: absolute; inset: 0;
    background-image: radial-gradient(circle, rgba(255,255,255,.05) 1px, transparent 1px);
    background-size: 30px 30px;
  }
  .hero::after {
    content: '';
    position: absolute; width: 600px; height: 600px; border-radius: 50%;
    background: rgba(0,174,239,.12); bottom: -200px; right: -100px;
  }
  .hero-inner { position: relative; z-index: 1; max-width: 680px; margin: 0 auto; }
  .hero-badge { display: inline-flex; align-items: center; gap: 8px; font-size: .8rem; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: rgba(255,255,255,.9); background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.25); padding: 6px 14px; border-radius: 999px; margin-bottom: 24px; }
  .hero h1 { font-size: clamp(2rem, 5vw, 3.4rem); font-weight: 800; letter-spacing: -.025em; margin-bottom: 18px; }
  .hero h1 span { color: #7dd3fc; }
  .hero p { font-size: 1.05rem; color: rgba(255,255,255,.78); max-width: 52ch; margin: 0 auto 32px; }
  .hero-cta { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
  .btn-hero-white { background: #fff; color: var(--navy); font-weight: 700; padding: 13px 28px; border-radius: 10px; font-size: .95rem; box-shadow: 0 8px 24px rgba(0,0,0,.15); }
  .btn-hero-white:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(0,0,0,.2); text-decoration: none; }
  .btn-hero-outline { border: 2px solid rgba(255,255,255,.5); color: #fff; padding: 13px 28px; border-radius: 10px; font-size: .95rem; font-weight: 600; }
  .btn-hero-outline:hover { background: rgba(255,255,255,.1); text-decoration: none; }
  .hero-note { margin-top: 20px; font-size: .82rem; color: rgba(255,255,255,.5); }

  /* ── Platform tabs ── */
  .platform-tabs-wrap { background: var(--surface); border-bottom: 1px solid var(--line); position: sticky; top: 68px; z-index: 50; }
  .platform-tabs { display: flex; gap: 0; max-width: 1160px; margin: 0 auto; padding: 0 24px; overflow-x: auto; scrollbar-width: none; }
  .platform-tabs::-webkit-scrollbar { display: none; }
  .ptab { display: flex; align-items: center; gap: 8px; padding: 16px 20px; font-size: .9rem; font-weight: 600; color: var(--muted); border-bottom: 3px solid transparent; cursor: pointer; white-space: nowrap; background: none; border-top: 0; border-left: 0; border-right: 0; transition: color .15s, border-color .15s; }
  .ptab:hover { color: var(--navy); }
  .ptab.active { color: var(--blue); border-bottom-color: var(--blue); }
  .ptab svg { width: 20px; height: 20px; flex-shrink: 0; }

  /* ── Download sections ── */
  .dl-section { padding: 72px 24px; }
  .dl-section:nth-child(even) { background: var(--surface); }
  .dl-section-inner { max-width: 1160px; margin: 0 auto; }

  /* Featured browser card */
  .featured-card {
    background: linear-gradient(135deg, var(--navy) 0%, #1a3068 60%, var(--blue) 100%);
    border-radius: 24px; padding: 56px 48px;
    display: grid; grid-template-columns: 1fr auto; gap: 48px; align-items: center;
    color: #fff; margin-bottom: 56px; position: relative; overflow: hidden;
  }
  .featured-card::before {
    content: '';
    position: absolute; inset: 0;
    background-image: radial-gradient(circle, rgba(255,255,255,.04) 1px, transparent 1px);
    background-size: 24px 24px;
  }
  .featured-card-content { position: relative; z-index: 1; }
  .featured-tag { display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.25); color: rgba(255,255,255,.9); font-size: .75rem; font-weight: 700; letter-spacing: .07em; text-transform: uppercase; padding: 4px 12px; border-radius: 20px; margin-bottom: 20px; }
  .featured-card h2 { font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 800; margin-bottom: 14px; letter-spacing: -.02em; }
  .featured-card p { color: rgba(255,255,255,.75); font-size: .98rem; max-width: 48ch; margin-bottom: 28px; }
  .featured-perks { display: flex; flex-direction: column; gap: 10px; margin-bottom: 32px; }
  .featured-perk { display: flex; align-items: center; gap: 10px; font-size: .9rem; color: rgba(255,255,255,.88); }
  .perk-check { width: 20px; height: 20px; background: rgba(0,174,239,.35); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: .7rem; }
  .featured-actions { display: flex; gap: 12px; flex-wrap: wrap; }
  .featured-visual { position: relative; z-index: 1; }
  .browser-mockup { width: 260px; background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.15); border-radius: 16px; overflow: hidden; }
  .browser-bar { background: rgba(255,255,255,.1); padding: 10px 14px; display: flex; align-items: center; gap: 8px; }
  .browser-dot { width: 8px; height: 8px; border-radius: 50%; }
  .bd-r { background: rgba(255,100,100,.7); }
  .bd-y { background: rgba(255,200,50,.7); }
  .bd-g { background: rgba(100,200,100,.7); }
  .browser-url { flex: 1; background: rgba(255,255,255,.1); border-radius: 4px; height: 20px; margin-left: 8px; display: flex; align-items: center; padding: 0 8px; }
  .browser-url span { font-size: 9px; color: rgba(255,255,255,.6); font-family: monospace; }
  .browser-screen { padding: 16px; display: flex; flex-direction: column; gap: 8px; min-height: 160px; }
  .bs-tile { border-radius: 8px; display: flex; align-items: center; justify-content: center; }
  .bs-main { background: linear-gradient(135deg, #3a358a, var(--navy)); height: 100px; flex: 1; }
  .bs-row { display: flex; gap: 6px; }
  .bs-sm { background: rgba(28,117,188,.5); height: 52px; flex: 1; border-radius: 6px; }
  .bs-sm:nth-child(2) { background: rgba(38,34,98,.6); }

  /* Platform cards grid */
  .platforms-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
  .platform-card {
    background: var(--surface); border: 1.5px solid var(--line);
    border-radius: var(--radius); padding: 28px 24px;
    display: flex; flex-direction: column; gap: 16px;
    transition: border-color .2s, box-shadow .2s, transform .2s;
    position: relative;
  }
  .platform-card:hover { border-color: var(--blue); box-shadow: var(--shadow); transform: translateY(-2px); }
  .platform-icon-wrap { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; }
  .pi-windows { background: linear-gradient(135deg, #00adef, #0067b8); }
  .pi-mac     { background: linear-gradient(135deg, #555, #222); }
  .pi-ios     { background: linear-gradient(135deg, #555, #1c1c1e); }
  .pi-android { background: linear-gradient(135deg, #78c257, #3ddc84); }
  .pi-linux   { background: linear-gradient(135deg, #f0a500, #dd6b20); }
  .pi-pwa     { background: linear-gradient(135deg, var(--blue), var(--navy)); }
  .platform-icon-wrap svg { width: 28px; height: 28px; fill: #fff; }
  .platform-card h3 { font-size: 1rem; font-weight: 700; color: var(--navy); margin: 0; }
  .platform-card p { font-size: .88rem; color: var(--muted); line-height: 1.6; flex: 1; }
  .platform-card .card-footer { display: flex; flex-direction: column; gap: 10px; }
  .btn-dl {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    padding: 11px 18px; border-radius: 10px; font-size: .9rem; font-weight: 600;
    text-decoration: none; transition: all .15s; border: 0; cursor: pointer;
    width: 100%;
  }
  .btn-dl:hover { text-decoration: none; }
  .btn-dl-primary { background: var(--blue); color: #fff; }
  .btn-dl-primary:hover { background: #1560a0; }
  .btn-dl-outline { background: transparent; border: 1.5px solid var(--line); color: var(--navy); }
  .btn-dl-outline:hover { border-color: var(--blue); color: var(--blue); }
  .badge-soon { display: inline-flex; align-items: center; justify-content: center; background: var(--blue-light); color: var(--blue); font-size: .78rem; font-weight: 700; letter-spacing: .04em; padding: 4px 10px; border-radius: 6px; text-transform: uppercase; }
  .sys-req { font-size: .8rem; color: var(--muted); margin-top: 4px; }

  /* ── Why download section ── */
  .why-section { padding: 72px 24px; background: var(--surface); }
  .section-head { text-align: center; max-width: 600px; margin: 0 auto 52px; }
  .section-head h2 { font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 800; color: var(--navy); letter-spacing: -.02em; margin-bottom: 12px; }
  .section-head p { color: var(--muted); font-size: .98rem; }
  .why-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; }
  .why-card { text-align: center; padding: 36px 24px; border: 1.5px solid var(--line); border-radius: var(--radius); background: var(--bg); }
  .why-icon { width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 28px; }
  .wi-blue   { background: var(--blue-light); }
  .wi-green  { background: #d1fae5; }
  .wi-purple { background: #ede9fe; }
  .why-card h3 { font-size: 1rem; font-weight: 700; color: var(--navy); margin-bottom: 10px; }
  .why-card p { font-size: .9rem; color: var(--muted); line-height: 1.65; }

  /* ── System requirements ── */
  .req-section { padding: 72px 24px; }
  .req-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 32px; }
  .req-tab { padding: 8px 20px; border-radius: 20px; font-size: .88rem; font-weight: 600; border: 1.5px solid var(--line); background: var(--surface); color: var(--muted); cursor: pointer; transition: all .15s; }
  .req-tab.active, .req-tab:hover { background: var(--blue); border-color: var(--blue); color: #fff; }
  .req-panel { display: none; }
  .req-panel.active { display: block; }
  .req-table { width: 100%; border-collapse: collapse; font-size: .9rem; }
  .req-table th { background: var(--navy); color: #fff; padding: 12px 16px; text-align: left; font-weight: 600; font-size: .85rem; }
  .req-table th:first-child { border-radius: 10px 0 0 0; }
  .req-table th:last-child  { border-radius: 0 10px 0 0; }
  .req-table td { padding: 11px 16px; border-bottom: 1px solid var(--line); vertical-align: top; color: var(--text); }
  .req-table tr:nth-child(even) td { background: var(--bg); }
  .req-table tr:last-child td { border-bottom: none; }

  /* ── CTA band ── */
  .cta-band { padding: 72px 24px; background: linear-gradient(135deg, var(--navy) 0%, #1e3a7a 55%, var(--blue) 100%); text-align: center; color: #fff; }
  .cta-band h2 { font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 800; margin-bottom: 12px; letter-spacing: -.02em; }
  .cta-band p { color: rgba(255,255,255,.75); max-width: 52ch; margin: 0 auto 32px; }
  .cta-band-actions { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }

  /* ── Responsive ── */
  @media (max-width: 920px) {
    .featured-card { grid-template-columns: 1fr; }
    .featured-visual { display: none; }
    .why-grid { grid-template-columns: 1fr 1fr; }
  }
  @media (max-width: 680px) {
    .hero { padding: 64px 20px 56px; }
    .platforms-grid { grid-template-columns: 1fr 1fr; }
    .why-grid { grid-template-columns: 1fr; }
    .featured-card { padding: 36px 28px; }
    .dl-section { padding: 48px 20px; }
    .wrap { padding: 0 20px; }
  }
  @media (max-width: 480px) {
    .hero h1 { font-size: 1.9rem; }
    .hero-cta { flex-direction: column; }
    .hero-cta a { width: 100%; justify-content: center; }
    .platforms-grid { grid-template-columns: 1fr; }
    .platform-tabs { gap: 0; }
    .ptab { padding: 14px 16px; font-size: .82rem; }
    .cta-band-actions { flex-direction: column; align-items: center; }
    .cta-band-actions a { width: 100%; max-width: 280px; }
  }
  @media (prefers-reduced-motion: reduce) { *, *::before, *::after { transition: none !important; } }
</style>
</head>
<body>

<?= view('partials/public_nav') ?>

<!-- ── Hero ── -->
<section class="hero">
  <div class="hero-inner">
    <span class="hero-badge">&#127760; Available everywhere</span>
    <h1>VTalanoa on every<br><span>device you own</span></h1>
    <p>Start or join a meeting from your browser, phone, or desktop — no friction, no barriers. One account, every platform.</p>
    <div class="hero-cta">
      <a class="btn-hero-white" href="<?= base_url('auth/register') ?>">
        &#128187; Start for free in browser
      </a>
      <a class="btn-hero-outline" href="#platforms">
        &#11015; See all platforms
      </a>
    </div>
    <p class="hero-note">Free forever &nbsp;&middot;&nbsp; No credit card &nbsp;&middot;&nbsp; Works on any modern browser</p>
  </div>
</section>

<!-- ── Platform tabs ── -->
<div class="platform-tabs-wrap">
  <div class="platform-tabs" role="tablist">
    <button class="ptab active" data-tab="all" role="tab" aria-selected="true">
      <svg viewBox="0 0 24 24"><path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 14H4V8h16v10z"/></svg>
      All Platforms
    </button>
    <button class="ptab" data-tab="desktop" role="tab">
      <svg viewBox="0 0 24 24"><path d="M20 18c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2H0v2h24v-2h-4zM4 6h16v10H4V6z"/></svg>
      Desktop
    </button>
    <button class="ptab" data-tab="mobile" role="tab">
      <svg viewBox="0 0 24 24"><path d="M17 1H7a2 2 0 0 0-2 2v18a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2zm0 17H7V5h10v13z"/></svg>
      Mobile
    </button>
    <button class="ptab" data-tab="browser" role="tab">
      <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
      Web Browser
    </button>
  </div>
</div>

<!-- ── Download Platforms ── -->
<section class="dl-section" id="platforms">
  <div class="dl-section-inner">

    <!-- Browser featured card -->
    <div class="featured-card" data-category="browser all">
      <div class="featured-card-content">
        <span class="featured-tag">&#10024; No download needed</span>
        <h2>Use VTalanoa right in<br>your browser</h2>
        <p>Start a meeting in seconds — no install, no waiting. VTalanoa's web app runs in Chrome, Edge, Firefox, and Safari with full HD video and audio.</p>
        <div class="featured-perks">
          <div class="featured-perk"><div class="perk-check">&#10003;</div> Works in Chrome, Edge, Firefox, Safari</div>
          <div class="featured-perk"><div class="perk-check">&#10003;</div> Full HD video with noise cancellation</div>
          <div class="featured-perk"><div class="perk-check">&#10003;</div> Screen sharing &amp; virtual backgrounds</div>
          <div class="featured-perk"><div class="perk-check">&#10003;</div> Always up to date — no updates needed</div>
        </div>
        <div class="featured-actions">
          <a href="<?= base_url('auth/register') ?>" class="btn-hero-white" style="font-size:.9rem;padding:12px 24px;">
            &#128187; Open in browser — it's free
          </a>
        </div>
      </div>
      <div class="featured-visual">
        <div class="browser-mockup">
          <div class="browser-bar">
            <span class="browser-dot bd-r"></span>
            <span class="browser-dot bd-y"></span>
            <span class="browser-dot bd-g"></span>
            <div class="browser-url"><span>vtalanoa.com/room</span></div>
          </div>
          <div class="browser-screen">
            <div class="bs-tile bs-main"></div>
            <div class="bs-row">
              <div class="bs-sm"></div>
              <div class="bs-sm"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Platform cards -->
    <div class="platforms-grid">

      <!-- Windows -->
      <div class="platform-card" data-category="desktop all">
        <div class="platform-icon-wrap pi-windows">
          <svg viewBox="0 0 24 24"><path d="M0 3.449L9.75 2.1v9.451H0m10.949-9.602L24 0v11.4H10.949M0 12.6h9.75v9.451L0 20.699M10.949 12.6H24V24l-12.9-1.801"/></svg>
        </div>
        <h3>Windows</h3>
        <p>Native desktop app for Windows 10 and later. Faster performance, system notifications, and deeper OS integration.</p>
        <div class="card-footer">
          <span class="badge-soon">Coming Soon</span>
          <span class="sys-req">Requires Windows 10 (64-bit) or later</span>
        </div>
      </div>

      <!-- macOS -->
      <div class="platform-card" data-category="desktop all">
        <div class="platform-icon-wrap pi-mac">
          <svg viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/></svg>
        </div>
        <h3>macOS</h3>
        <p>Optimised for Apple Silicon and Intel Macs. Runs natively on macOS 12 Monterey and later with full Retina display support.</p>
        <div class="card-footer">
          <span class="badge-soon">Coming Soon</span>
          <span class="sys-req">Requires macOS 12 Monterey or later</span>
        </div>
      </div>

      <!-- iOS -->
      <div class="platform-card" data-category="mobile all">
        <div class="platform-icon-wrap pi-ios">
          <svg viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/></svg>
        </div>
        <h3>iPhone &amp; iPad</h3>
        <p>Take your meetings on the go. VTalanoa for iOS supports Face ID, PiP, and background audio — fully optimised for iPhone and iPad.</p>
        <div class="card-footer">
          <span class="badge-soon">Coming Soon</span>
          <span class="sys-req">Requires iOS 15 or later</span>
        </div>
      </div>

      <!-- Android -->
      <div class="platform-card" data-category="mobile all">
        <div class="platform-icon-wrap pi-android">
          <svg viewBox="0 0 24 24"><path d="m6.18 15.64a2.18 2.18 0 0 1-2.18 2.18C2.98 17.82 2 16.84 2 15.64V8.36C2 7.16 2.98 6.18 4.18 6.18s2.18.98 2.18 2.18m11.64 0a2.18 2.18 0 0 0 2.18 2.18C21.02 17.82 22 16.84 22 15.64V8.36C22 7.16 21.02 6.18 19.82 6.18s-2.18.98-2.18 2.18M17 3.32l1.14-2.26A.4.4 0 0 0 17.78.77l-1.2 2.34A7.56 7.56 0 0 0 12 2c-1.7 0-3.28.56-4.58 1.11L6.22.77a.4.4 0 0 0-.36.29L7 3.32A7.63 7.63 0 0 0 4 9.5h16A7.63 7.63 0 0 0 17 3.32zm-7.5 3.18a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5m5 0a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5M4.5 19.91a2.09 2.09 0 0 0 2.09 2.09h.91V24a2 2 0 0 0 4 0v-2h1v2a2 2 0 0 0 4 0v-2h.91a2.09 2.09 0 0 0 2.09-2.09V10h-15z"/></svg>
        </div>
        <h3>Android</h3>
        <p>Seamless video meetings on any Android device. Supports background blur, noise suppression, and adaptive bitrate for mobile data.</p>
        <div class="card-footer">
          <span class="badge-soon">Coming Soon</span>
          <span class="sys-req">Requires Android 8.0 or later</span>
        </div>
      </div>

      <!-- Linux -->
      <div class="platform-card" data-category="desktop all">
        <div class="platform-icon-wrap pi-linux">
          <svg viewBox="0 0 24 24"><path d="M12.504 0C6.03 0 0 5.993 0 12.493c0 6.5 6.03 11.5 12.504 11.5 6.475 0 11.496-5 11.496-11.5C24 5.993 18.979 0 12.504 0zm0 1.388c6.104 0 10.612 4.705 10.612 11.105 0 6.4-4.508 10.612-10.612 10.612-6.103 0-11.116-4.212-11.116-10.612 0-6.4 5.013-11.105 11.116-11.105zm0 2.224c-2.41 0-4.42.786-5.93 2.07-1.51 1.285-2.4 3.038-2.4 4.952 0 1.18.352 2.34.975 3.33.622.99 1.51 1.83 2.605 2.474L6.83 20.25h1.58l.476-3.617c.994.24 2.012.359 3.016.359 1.004 0 2.022-.12 3.016-.359l.476 3.617h1.58l-.924-4.812c1.094-.644 1.983-1.484 2.605-2.474.622-.99.975-2.15.975-3.33 0-1.914-.89-3.667-2.4-4.952-1.51-1.284-3.52-2.07-5.93-2.07zm-3.47 4.556c.49 0 .889.4.889.891 0 .49-.4.89-.89.89-.49 0-.89-.4-.89-.89 0-.49.4-.89.89-.89zm6.94 0c.49 0 .889.4.889.891 0 .49-.4.89-.89.89-.49 0-.89-.4-.89-.89 0-.49.4-.89.89-.89z"/></svg>
        </div>
        <h3>Linux</h3>
        <p>Available as a Snap and AppImage for Ubuntu, Fedora, Debian, and other major distributions. Use the browser app in the meantime.</p>
        <div class="card-footer">
          <span class="badge-soon">Coming Soon</span>
          <span class="sys-req">Ubuntu 20.04+, Fedora 34+, Debian 11+</span>
        </div>
      </div>

      <!-- PWA -->
      <div class="platform-card" data-category="browser all">
        <div class="platform-icon-wrap pi-pwa">
          <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>
        </div>
        <h3>Install as App (PWA)</h3>
        <p>Add VTalanoa to your home screen or taskbar directly from your browser — no App Store needed. Works offline for scheduled meetings.</p>
        <div class="card-footer">
          <a href="<?= base_url('auth/register') ?>" class="btn-dl btn-dl-primary">
            &#43; Add to Home Screen
          </a>
          <span class="sys-req">Supported in Chrome, Edge, and Safari</span>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ── Why Download ── -->
<section class="why-section">
  <div class="wrap">
    <div class="section-head">
      <h2>More ways to meet</h2>
      <p>Whether you're at your desk, on the go, or switching devices mid-meeting — VTalanoa keeps you connected.</p>
    </div>
    <div class="why-grid">
      <div class="why-card">
        <div class="why-icon wi-blue">&#9889;</div>
        <h3>Instant start</h3>
        <p>No sign-in required for guests. Share a link and anyone joins directly in their browser in under 5 seconds.</p>
      </div>
      <div class="why-card">
        <div class="why-icon wi-green">&#128274;</div>
        <h3>Secure by default</h3>
        <p>End-to-end encrypted meetings, optional waiting rooms, and per-meeting passwords. Your calls stay private.</p>
      </div>
      <div class="why-card">
        <div class="why-icon wi-purple">&#127760;</div>
        <h3>Works anywhere</h3>
        <p>Low-bandwidth mode ensures clear audio even on 3G. Adaptive bitrate keeps video smooth regardless of connection.</p>
      </div>
    </div>
  </div>
</section>

<!-- ── System Requirements ── -->
<section class="req-section">
  <div class="wrap">
    <div class="section-head" style="text-align:left;max-width:100%;margin-bottom:28px;">
      <h2>System requirements</h2>
      <p style="color:var(--muted);font-size:.95rem;margin-top:8px;">Check compatibility before downloading the native app.</p>
    </div>
    <div class="req-tabs" role="tablist">
      <button class="req-tab active" data-req="web" role="tab">Web Browser</button>
      <button class="req-tab" data-req="windows" role="tab">Windows</button>
      <button class="req-tab" data-req="mac" role="tab">macOS</button>
      <button class="req-tab" data-req="mobile" role="tab">Mobile</button>
    </div>
    <div style="overflow-x:auto;">
      <div class="req-panel active" id="req-web">
        <table class="req-table">
          <thead><tr><th>Requirement</th><th>Minimum</th><th>Recommended</th></tr></thead>
          <tbody>
            <tr><td>Browser</td><td>Chrome 90+, Edge 90+, Firefox 88+, Safari 14+</td><td>Latest version of any listed browser</td></tr>
            <tr><td>Processor</td><td>1.6 GHz dual-core</td><td>2.0 GHz quad-core or better</td></tr>
            <tr><td>RAM</td><td>4 GB</td><td>8 GB or more</td></tr>
            <tr><td>Internet</td><td>1 Mbps up/down</td><td>5 Mbps up/down for HD video</td></tr>
            <tr><td>Camera</td><td>Any 720p webcam</td><td>1080p HD webcam</td></tr>
            <tr><td>Microphone</td><td>Built-in or USB</td><td>Headset or external mic recommended</td></tr>
          </tbody>
        </table>
      </div>
      <div class="req-panel" id="req-windows">
        <table class="req-table">
          <thead><tr><th>Requirement</th><th>Minimum</th><th>Recommended</th></tr></thead>
          <tbody>
            <tr><td>Operating System</td><td>Windows 10 (64-bit) version 1903+</td><td>Windows 11 (64-bit)</td></tr>
            <tr><td>Processor</td><td>1.6 GHz dual-core (x86/x64)</td><td>2.4 GHz quad-core</td></tr>
            <tr><td>RAM</td><td>4 GB</td><td>8 GB</td></tr>
            <tr><td>Storage</td><td>500 MB free</td><td>1 GB free</td></tr>
            <tr><td>Display</td><td>1024 &times; 768</td><td>1920 &times; 1080 (Full HD)</td></tr>
            <tr><td>Internet</td><td>1 Mbps up/down</td><td>5 Mbps up/down</td></tr>
          </tbody>
        </table>
      </div>
      <div class="req-panel" id="req-mac">
        <table class="req-table">
          <thead><tr><th>Requirement</th><th>Minimum</th><th>Recommended</th></tr></thead>
          <tbody>
            <tr><td>Operating System</td><td>macOS 12 Monterey</td><td>macOS 14 Sonoma or later</td></tr>
            <tr><td>Processor</td><td>Intel Core i5 or Apple M1</td><td>Apple M2 or Intel Core i7+</td></tr>
            <tr><td>RAM</td><td>4 GB</td><td>8 GB or more</td></tr>
            <tr><td>Storage</td><td>500 MB free</td><td>1 GB free</td></tr>
            <tr><td>Display</td><td>1280 &times; 800</td><td>Retina display (2560 &times; 1600)</td></tr>
            <tr><td>Internet</td><td>1 Mbps up/down</td><td>5 Mbps up/down</td></tr>
          </tbody>
        </table>
      </div>
      <div class="req-panel" id="req-mobile">
        <table class="req-table">
          <thead><tr><th>Platform</th><th>Minimum OS</th><th>Notes</th></tr></thead>
          <tbody>
            <tr><td>iPhone</td><td>iOS 15 or later</td><td>Supports Face ID, PiP, background audio</td></tr>
            <tr><td>iPad</td><td>iPadOS 15 or later</td><td>Full split-view multitasking support</td></tr>
            <tr><td>Android Phone</td><td>Android 8.0 (Oreo)</td><td>Works on all major Android OEMs</td></tr>
            <tr><td>Android Tablet</td><td>Android 8.0 (Oreo)</td><td>Optimised layout for large screens</td></tr>
            <tr><td>Storage (all)</td><td>200 MB free</td><td>&mdash;</td></tr>
            <tr><td>Internet</td><td>500 Kbps (low-bandwidth mode)</td><td>2 Mbps for HD quality</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<!-- ── CTA band ── -->
<section class="cta-band">
  <h2>Start meeting today — for free</h2>
  <p>Sign up and host your first meeting in minutes. No credit card, no commitment.</p>
  <div class="cta-band-actions">
    <a href="<?= base_url('auth/register') ?>" class="btn-hero-white">
      &#127970; Create free account
    </a>
    <a href="<?= base_url('auth/login') ?>" class="btn-hero-outline">
      Sign in
    </a>
  </div>
</section>

<?= view('partials/public_foot') ?>

<script>
// Platform tabs filter
document.querySelectorAll('.ptab').forEach(tab => {
  tab.addEventListener('click', function() {
    document.querySelectorAll('.ptab').forEach(t => { t.classList.remove('active'); t.setAttribute('aria-selected','false'); });
    this.classList.add('active');
    this.setAttribute('aria-selected','true');
    const filter = this.dataset.tab;
    document.querySelectorAll('[data-category]').forEach(card => {
      card.style.display = (filter === 'all' || card.dataset.category.includes(filter)) ? '' : 'none';
    });
  });
});

// System req tabs
document.querySelectorAll('.req-tab').forEach(tab => {
  tab.addEventListener('click', function() {
    document.querySelectorAll('.req-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.req-panel').forEach(p => p.classList.remove('active'));
    this.classList.add('active');
    document.getElementById('req-' + this.dataset.req)?.classList.add('active');
  });
});
</script>
</body>
</html>
