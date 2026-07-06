<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>VTalanoa — Free video meetings for everyone</title>
<link rel="icon" href="<?= base_url('favicon.ico') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root{
    --ink:       #262262;
    --blue:      #1c75bc;
    --tint-ink:  #edecf7;
    --tint-blue: #e5f1fa;
    --tint-ink-2:#dddbf0;
    --tint-blue-2:#d2e7f6;
    --surface:   #ffffff;
    --text:      #262262;
    --muted:     #5d5c82;
    --line:      rgba(38,34,98,.12);
    --radius:    16px;
    --shadow:    0 18px 50px -18px rgba(38,34,98,.28);
  }

  *{margin:0;padding:0;box-sizing:border-box}
  html{scroll-behavior:smooth}

  body{
    font-family:'Inter',system-ui,sans-serif;
    color:var(--text);
    line-height:1.6;
    background:
      radial-gradient(900px 600px at 85% -5%, var(--tint-blue-2) 0%, transparent 60%),
      radial-gradient(800px 600px at -10% 30%, var(--tint-ink-2) 0%, transparent 55%),
      linear-gradient(135deg, var(--tint-ink) 0%, var(--tint-blue) 100%);
    background-attachment:fixed;
  }

  h1,h2,h3{font-family:'Bricolage Grotesque','Inter',sans-serif;line-height:1.12}

  .wrap{max-width:1160px;margin:0 auto;padding:0 24px}

  /* ---------- Nav ---------- */
  header{
    position:sticky;top:0;z-index:50;
    backdrop-filter:blur(12px);
    background:rgba(255,255,255,.65);
    border-bottom:1px solid var(--line);
  }
  .nav{display:flex;align-items:center;gap:32px;height:68px}
  .logo{display:flex;align-items:center;gap:10px;text-decoration:none;color:var(--ink);font-family:'Bricolage Grotesque',sans-serif;font-weight:800;font-size:1.25rem;letter-spacing:-.02em}
  .logo-mark{width:34px;height:34px;border-radius:10px;position:relative;overflow:hidden;background:var(--ink);flex-shrink:0}
  .logo-mark::after{content:"";position:absolute;inset:0;background:linear-gradient(135deg,transparent 46%,var(--blue) 46%)}
  .nav-links{display:flex;gap:26px;margin-left:8px}
  .nav-links a{color:var(--muted);text-decoration:none;font-size:.92rem;font-weight:500}
  .nav-links a:hover{color:var(--ink)}
  .nav-cta{margin-left:auto;display:flex;gap:12px;align-items:center}

  /* Hamburger */
  .hamburger{
    display:none;background:none;border:0;cursor:pointer;
    padding:6px;color:var(--ink);margin-left:auto;
    border-radius:8px;transition:background .15s;
  }
  .hamburger:hover{background:var(--tint-ink)}
  .hamburger svg{width:24px;height:24px;fill:var(--ink);display:block}

  /* Mobile nav overlay */
  .mobile-nav{
    display:none;position:fixed;inset:0;
    background:rgba(38,34,98,.96);
    z-index:200;
    flex-direction:column;align-items:center;justify-content:center;gap:28px;
    padding:40px 24px;
  }
  .mobile-nav.open{display:flex}
  .mobile-nav a{
    color:#fff;font-size:1.3rem;font-weight:600;
    text-decoration:none;font-family:'Bricolage Grotesque',sans-serif;
    opacity:.9;transition:opacity .15s;
  }
  .mobile-nav a:hover{opacity:1}
  .mobile-nav .divider{width:60px;height:1px;background:rgba(255,255,255,.2)}
  .mobile-nav-close{
    position:absolute;top:20px;right:20px;
    background:rgba(255,255,255,.12);border:0;
    color:#fff;font-size:1.3rem;cursor:pointer;
    width:40px;height:40px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    transition:background .15s;
  }
  .mobile-nav-close:hover{background:rgba(255,255,255,.22)}
  .mobile-nav .btn{font-size:1rem;padding:12px 32px}

  .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;font-weight:600;font-size:.95rem;padding:12px 22px;border-radius:12px;text-decoration:none;border:0;cursor:pointer;transition:transform .15s ease, box-shadow .15s ease}
  .btn:focus-visible{outline:3px solid var(--blue);outline-offset:2px}
  .btn-primary{background:var(--blue);color:#fff;box-shadow:0 8px 20px -8px rgba(28,117,188,.55)}
  .btn-primary:hover{transform:translateY(-1px);box-shadow:0 12px 26px -8px rgba(28,117,188,.6)}
  .btn-ink{background:var(--ink);color:#fff}
  .btn-ink:hover{transform:translateY(-1px)}
  .btn-ghost{background:transparent;color:var(--ink);border:1.5px solid var(--line)}
  .btn-ghost:hover{border-color:var(--ink)}

  /* ---------- Hero ---------- */
  .hero{display:grid;grid-template-columns:1.05fr 1fr;gap:56px;align-items:center;padding:88px 0 72px}
  .eyebrow{display:inline-flex;align-items:center;gap:8px;font-size:.8rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--blue);background:rgba(28,117,188,.1);border:1px solid rgba(28,117,188,.25);padding:6px 14px;border-radius:999px;margin-bottom:20px}
  .hero h1{font-size:clamp(2.1rem,4.6vw,3.7rem);font-weight:800;letter-spacing:-.025em;margin-bottom:20px}
  .hero h1 span{color:var(--blue)}
  .hero p.lead{font-size:1.05rem;color:var(--muted);max-width:46ch;margin-bottom:32px}
  .hero-actions{display:flex;gap:14px;flex-wrap:wrap;margin-bottom:22px}
  .hero-note{font-size:.85rem;color:var(--muted)}
  .hero-note strong{color:var(--ink)}

  /* ---------- Mock meeting window ---------- */
  .meet{background:var(--surface);border-radius:20px;box-shadow:var(--shadow);border:1px solid var(--line);overflow:hidden}
  .meet-top{display:flex;align-items:center;gap:8px;padding:12px 16px;border-bottom:1px solid var(--line)}
  .dot{width:10px;height:10px;border-radius:50%;background:var(--tint-ink-2)}
  .meet-title{margin-left:8px;font-size:.8rem;font-weight:600;color:var(--muted)}
  .rec{margin-left:auto;display:flex;align-items:center;gap:6px;font-size:.72rem;font-weight:700;color:#c23a3a}
  .rec::before{content:"";width:8px;height:8px;border-radius:50%;background:#c23a3a;animation:blink 1.6s infinite}
  @keyframes blink{50%{opacity:.25}}
  .grid-tiles{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;padding:14px}
  .tile{position:relative;aspect-ratio:16/10.5;border-radius:12px;display:flex;align-items:center;justify-content:center;overflow:hidden}
  .tile:nth-child(1){background:linear-gradient(135deg,#3a358a,var(--ink))}
  .tile:nth-child(2){background:linear-gradient(135deg,#2f8fd6,var(--blue))}
  .tile:nth-child(3){background:linear-gradient(135deg,var(--blue),var(--ink))}
  .tile:nth-child(4){background:linear-gradient(135deg,#514cab,#2a86c9)}
  .avatar{width:48px;height:48px;border-radius:50%;background:rgba(255,255,255,.92);display:flex;align-items:center;justify-content:center;font-weight:800;font-family:'Bricolage Grotesque',sans-serif;font-size:1rem;color:var(--ink)}
  .tile:nth-child(2) .avatar{color:var(--blue)}
  .name-tag{position:absolute;left:10px;bottom:10px;background:rgba(38,34,98,.55);color:#fff;font-size:.68rem;font-weight:600;padding:3px 9px;border-radius:6px;backdrop-filter:blur(4px)}
  .speaking{box-shadow:inset 0 0 0 2.5px #6fd08c}
  .mic-off{position:absolute;right:10px;bottom:10px;width:22px;height:22px;border-radius:50%;background:rgba(38,34,98,.55);display:flex;align-items:center;justify-content:center}
  .mic-off svg{width:12px;height:12px;fill:#fff}
  .meet-bar{display:flex;justify-content:center;gap:12px;padding:12px 16px;border-top:1px solid var(--line)}
  .ctrl{width:42px;height:42px;border-radius:50%;border:0;display:flex;align-items:center;justify-content:center;background:var(--tint-ink);cursor:pointer;transition:background .15s}
  .ctrl:hover{background:var(--tint-ink-2)}
  .ctrl svg{width:18px;height:18px;fill:var(--ink)}
  .ctrl.end{background:#d94f4f;width:56px;border-radius:22px}
  .ctrl.end svg{fill:#fff}

  /* ---------- Stats strip ---------- */
  .strip{padding:8px 0 56px;text-align:center}
  .strip p{font-size:.82rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin-bottom:16px}
  .strip .stats{display:flex;justify-content:center;gap:56px;flex-wrap:wrap}
  .stat b{display:block;font-family:'Bricolage Grotesque',sans-serif;font-size:1.9rem;font-weight:800;color:var(--ink)}
  .stat span{font-size:.85rem;color:var(--muted)}

  /* ---------- Features ---------- */
  .section{padding:72px 0}
  .section-head{max-width:640px;margin:0 auto 48px;text-align:center}
  .section-head h2{font-size:clamp(1.7rem,3vw,2.4rem);font-weight:800;letter-spacing:-.02em;margin-bottom:14px}
  .section-head p{color:var(--muted)}
  .cards{display:grid;grid-template-columns:repeat(3,1fr);gap:22px}
  .card{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);padding:30px 26px;transition:transform .18s ease, box-shadow .18s ease}
  .card:hover{transform:translateY(-4px);box-shadow:var(--shadow)}
  .card .icon{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:18px}
  .card:nth-child(odd) .icon{background:rgba(38,34,98,.1)}
  .card:nth-child(even) .icon{background:rgba(28,117,188,.12)}
  .card .icon svg{width:22px;height:22px}
  .card:nth-child(odd) .icon svg{fill:var(--ink)}
  .card:nth-child(even) .icon svg{fill:var(--blue)}
  .card h3{font-size:1.05rem;font-weight:700;margin-bottom:8px}
  .card p{font-size:.93rem;color:var(--muted)}

  /* ---------- CTA band ---------- */
  .cta-band{background:linear-gradient(120deg,var(--ink) 0%,#1e3a7a 55%,var(--blue) 100%);border-radius:24px;padding:64px 48px;text-align:center;color:#fff;box-shadow:var(--shadow)}
  .cta-band h2{font-size:clamp(1.6rem,3vw,2.3rem);font-weight:800;letter-spacing:-.02em;margin-bottom:12px}
  .cta-band p{color:rgba(255,255,255,.78);max-width:52ch;margin:0 auto 30px}
  .cta-band .btn-primary{background:#fff;color:var(--ink);box-shadow:none}
  .cta-band .btn-primary:hover{background:var(--tint-ink);transform:translateY(-1px)}

  /* ---------- Footer ---------- */
  footer{margin-top:72px;border-top:1px solid var(--line);background:rgba(255,255,255,.55);backdrop-filter:blur(8px)}
  .foot{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:28px 0;flex-wrap:wrap}
  .foot-links{display:flex;gap:22px;flex-wrap:wrap}
  .foot-links a{color:var(--muted);text-decoration:none;font-size:.88rem}
  .foot-links a:hover{color:var(--ink)}
  .copy{font-size:.85rem;color:var(--muted)}

  /* ---------- Responsive ---------- */
  @media (max-width:1080px){
    .cards{grid-template-columns:repeat(2,1fr)}
  }
  @media (max-width:920px){
    .hero{grid-template-columns:1fr;padding:56px 0 48px;gap:40px}
    .nav-links{display:none}
    .nav-cta{display:none}
    .hamburger{display:block}
  }
  @media (max-width:680px){
    .cards{grid-template-columns:1fr}
    .strip .stats{gap:28px}
    .section{padding:48px 0}
    .cta-band{padding:40px 24px;border-radius:16px}
    .hero{padding:40px 0 36px}
  }
  @media (max-width:640px){
    .foot{flex-direction:column;align-items:center;text-align:center;gap:14px}
    .foot-links{justify-content:center;flex-wrap:wrap;gap:16px}
  }
  @media (max-width:480px){
    .wrap{padding:0 16px}
    .hero h1{font-size:1.9rem}
    .hero-actions{flex-direction:column}
    .hero-actions .btn{width:100%;justify-content:center}
    .meet-bar{gap:8px}
    .ctrl{width:36px;height:36px}
    .ctrl.end{width:48px}
    .avatar{width:38px;height:38px;font-size:.85rem}
  }
  @media (max-width:360px){
    .hero h1{font-size:1.65rem}
    .grid-tiles{gap:6px;padding:10px}
    .stat b{font-size:1.5rem}
  }
  @media (prefers-reduced-motion:reduce){
    *{animation:none !important;transition:none !important}
  }
</style>
</head>
<body>

<!-- Mobile nav overlay -->
<div class="mobile-nav" id="mobileNav" role="dialog" aria-modal="true" aria-label="Mobile navigation">
  <button class="mobile-nav-close" id="mobileNavClose" aria-label="Close menu">&#x2715;</button>
  <a href="<?= base_url('features') ?>">Features</a>
  <a href="<?= base_url('pricing') ?>">Pricing</a>
  <a href="<?= base_url('download') ?>">Download</a>
  <a href="<?= base_url('support') ?>">Help</a>
  <div class="divider"></div>
  <a class="btn btn-ghost" href="<?= base_url('auth/login') ?>" style="color:#fff;border-color:rgba(255,255,255,.4)">Sign in</a>
  <a class="btn btn-primary" href="<?= base_url('auth/register') ?>">Sign up free</a>
</div>

<header>
  <div class="wrap nav">
    <a class="logo" href="<?= base_url('/') ?>">
      <img src="<?= base_url('img/logo-web.png') ?>" alt="MeetWave" style="height:36px;max-width:180px;object-fit:contain;">
    </a>
    <nav class="nav-links" aria-label="Main">
      <a href="<?= base_url('features') ?>">Features</a>
      <a href="<?= base_url('pricing') ?>">Pricing</a>
      <a href="<?= base_url('download') ?>">Download</a>
      <a href="<?= base_url('support') ?>">Help</a>
    </nav>
    <div class="nav-cta">
      <a class="btn btn-ghost" href="<?= base_url('auth/login') ?>">Sign in</a>
      <a class="btn btn-primary" href="<?= base_url('auth/register') ?>">Sign up free</a>
    </div>
    <button class="hamburger" id="hamburger" aria-label="Open menu" aria-expanded="false" aria-controls="mobileNav">
      <svg viewBox="0 0 24 24"><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/></svg>
    </button>
  </div>
</header>

<main>
  <!-- Hero -->
  <section class="wrap hero">
    <div>
      <span class="eyebrow">Free for everyone</span>
      <h1>Meet, chat, and get things done — <span>together</span></h1>
      <p class="lead">Start a video meeting in one click. Invite anyone with a link — no downloads or sign-ups needed for guests. Crystal-clear video, live captions, and screen sharing, all free.</p>
      <div class="hero-actions">
        <a class="btn btn-primary" href="<?= base_url('auth/register') ?>">Start a free meeting</a>
        <a class="btn btn-ghost" href="<?= base_url('auth/login') ?>">Sign in</a>
      </div>
      <p class="hero-note"><strong>No credit card required.</strong> Meetings up to 100 people, for up to 60 minutes.</p>
    </div>

    <!-- Mock meeting window -->
    <div class="meet" role="img" aria-label="Preview of a MeetWave video call with four participants">
      <div class="meet-top">
        <span class="dot"></span><span class="dot"></span><span class="dot"></span>
        <span class="meet-title">Weekly team sync &middot; 4 participants</span>
        <span class="rec">REC</span>
      </div>
      <div class="grid-tiles">
        <div class="tile speaking"><div class="avatar">AM</div><span class="name-tag">Amelia &middot; Speaking</span></div>
        <div class="tile"><div class="avatar">JT</div><span class="name-tag">Josef</span>
          <span class="mic-off"><svg viewBox="0 0 24 24"><path d="M19 11a7 7 0 0 1-11.95 4.95l1.42-1.42A5 5 0 0 0 17 11h2zM12 14a3 3 0 0 0 3-3V5a3 3 0 0 0-6 0v6a3 3 0 0 0 3 3zM3.3 2.3 21.7 20.7l-1.4 1.4L1.9 3.7l1.4-1.4z"/></svg></span>
        </div>
        <div class="tile"><div class="avatar">RK</div><span class="name-tag">Rani</span></div>
        <div class="tile"><div class="avatar">DS</div><span class="name-tag">Diego</span></div>
      </div>
      <div class="meet-bar">
        <button class="ctrl" aria-label="Toggle microphone"><svg viewBox="0 0 24 24"><path d="M12 14a3 3 0 0 0 3-3V5a3 3 0 0 0-6 0v6a3 3 0 0 0 3 3zm5-3a5 5 0 0 1-10 0H5a7 7 0 0 0 6 6.92V21h2v-3.08A7 7 0 0 0 19 11h-2z"/></svg></button>
        <button class="ctrl" aria-label="Toggle camera"><svg viewBox="0 0 24 24"><path d="M17 10.5V7a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-3.5l4 4v-11l-4 4z"/></svg></button>
        <button class="ctrl" aria-label="Share screen"><svg viewBox="0 0 24 24"><path d="M4 4h16a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1h-6v2h3v2H7v-2h3v-2H4a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1zm8 3-4 4h3v4h2v-4h3l-4-4z"/></svg></button>
        <button class="ctrl end" aria-label="Leave meeting"><svg viewBox="0 0 24 24"><path d="M12 9c-1.6 0-3.15.25-4.6.72v3.1c0 .4-.23.74-.56.9-.98.49-1.87 1.12-2.66 1.85-.18.18-.43.28-.7.28-.28 0-.53-.11-.71-.29L.29 13.08a.996.996 0 0 1 0-1.41C3.34 8.78 7.46 7 12 7s8.66 1.78 11.71 4.67c.18.18.29.43.29.7 0 .28-.11.53-.29.71l-2.48 2.48c-.18.18-.43.29-.71.29-.27 0-.52-.1-.7-.28-.79-.73-1.68-1.36-2.66-1.85a.996.996 0 0 1-.56-.9v-3.1C14.15 9.25 13.6 9 12 9z"/></svg></button>
      </div>
    </div>
  </section>

  <!-- Stats strip -->
  <section class="wrap strip">
    <p>Trusted by teams, classrooms, and families</p>
    <div class="stats">
      <div class="stat"><b>10M+</b><span>meetings hosted</span></div>
      <div class="stat"><b>100</b><span>participants per call, free</span></div>
      <div class="stat"><b>99.9%</b><span>uptime, worldwide</span></div>
    </div>
  </section>

  <!-- Features -->
  <section class="wrap section" id="features">
    <div class="section-head">
      <h2>Everything you need to meet, in one place</h2>
      <p>From quick catch-ups to all-hands meetings, MeetWave keeps every conversation simple, secure, and free to start.</p>
    </div>
    <div class="cards">
      <article class="card">
        <div class="icon"><svg viewBox="0 0 24 24"><path d="M17 10.5V7a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-3.5l4 4v-11l-4 4z"/></svg></div>
        <h3>HD video and audio</h3>
        <p>Sharp video and noise suppression keep every voice clear, even on slower connections. Quality adapts automatically so calls never stutter.</p>
      </article>
      <article class="card">
        <div class="icon"><svg viewBox="0 0 24 24"><path d="M12 1 3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg></div>
        <h3>Secure by default</h3>
        <p>Every meeting is encrypted, with lobby controls and host tools so only the people you invite can join. Your conversations stay yours.</p>
      </article>
      <article class="card">
        <div class="icon"><svg viewBox="0 0 24 24"><path d="M4 4h16a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1h-6v2h3v2H7v-2h3v-2H4a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1zm8 3-4 4h3v4h2v-4h3l-4-4z"/></svg></div>
        <h3>Share and collaborate</h3>
        <p>Present your screen, chat alongside the call, and share files without leaving the meeting. Everything stays in one thread afterward.</p>
      </article>
      <article class="card">
        <div class="icon"><svg viewBox="0 0 24 24"><path d="M12 3a9 9 0 1 0 9 9h-2a7 7 0 1 1-7-7V3zm1 5v4.59l3.3 3.3-1.42 1.42L11 13.4V8h2z"/></svg></div>
        <h3>Live captions</h3>
        <p>Real-time captions in multiple languages make meetings easier to follow for everyone — great for global teams and noisy rooms.</p>
      </article>
      <article class="card">
        <div class="icon"><svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 3-1.34 3-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg></div>
        <h3>Up to 100 guests, free</h3>
        <p>Host meetings with up to 100 people at no cost. Guests join straight from the browser with a link — no account, no install.</p>
      </article>
      <article class="card">
        <div class="icon"><svg viewBox="0 0 24 24"><path d="M17 1H7a2 2 0 0 0-2 2v18a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2zm0 17H7V5h10v13z"/></svg></div>
        <h3>Works on every device</h3>
        <p>Start on your laptop, continue on your phone. MeetWave runs in the browser and as an app on Windows, Mac, iOS, and Android.</p>
      </article>
    </div>
  </section>

  <!-- CTA band -->
  <section class="wrap section" id="signup">
    <div class="cta-band">
      <h2>Your next meeting is one click away</h2>
      <p>Sign up free and start hosting unlimited meetings today. Upgrade anytime for longer calls, recordings, and more participants.</p>
      <a class="btn btn-primary" href="<?= base_url('auth/register') ?>">Sign up free</a>
    </div>
  </section>
</main>

<footer>
  <div class="wrap foot">
    <a class="logo" href="<?= base_url('/') ?>">
      <img src="<?= base_url('img/logo-web.png') ?>" alt="MeetWave" style="height:32px;max-width:160px;object-fit:contain;">
    </a>
    <nav class="foot-links" aria-label="Footer">
      <a href="<?= base_url('support') ?>">Help</a>
      <a href="<?= base_url('contact') ?>">Contact</a>
      <a href="<?= base_url('privacy') ?>">Privacy</a>
      <a href="<?= base_url('terms') ?>">Terms</a>
    </nav>
    <span class="copy">&copy; <?= date('Y') ?> MeetWave. All rights reserved.</span>
  </div>
</footer>

<script>
(function(){
  const hamburger    = document.getElementById('hamburger');
  const mobileNav    = document.getElementById('mobileNav');
  const mobileClose  = document.getElementById('mobileNavClose');

  function openNav(){
    mobileNav.classList.add('open');
    hamburger.setAttribute('aria-expanded','true');
    document.body.style.overflow='hidden';
  }
  function closeMobileNav(){
    mobileNav.classList.remove('open');
    hamburger.setAttribute('aria-expanded','false');
    document.body.style.overflow='';
  }
  window.closeMobileNav = closeMobileNav;

  hamburger.addEventListener('click', openNav);
  mobileClose.addEventListener('click', closeMobileNav);
  mobileNav.addEventListener('click', function(e){
    if(e.target === mobileNav) closeMobileNav();
  });
  document.addEventListener('keydown', function(e){
    if(e.key === 'Escape') closeMobileNav();
  });
})();
</script>
</body>
</html>
