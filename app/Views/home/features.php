<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Features — VTalanoa</title>
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

  h1,h2,h3,h4{font-family:'Bricolage Grotesque','Inter',sans-serif;line-height:1.12}
  .wrap{max-width:1160px;margin:0 auto;padding:0 24px}

  /* ---- Nav ---- */
  header{position:sticky;top:0;z-index:50;backdrop-filter:blur(12px);background:rgba(255,255,255,.65);border-bottom:1px solid var(--line)}
  .nav{display:flex;align-items:center;gap:32px;height:68px}
  .logo{display:flex;align-items:center;gap:10px;text-decoration:none;color:var(--ink);font-family:'Bricolage Grotesque',sans-serif;font-weight:800;font-size:1.25rem;letter-spacing:-.02em}
  .nav-links{display:flex;gap:26px;margin-left:8px}
  .nav-links a{color:var(--muted);text-decoration:none;font-size:.92rem;font-weight:500}
  .nav-links a:hover,.nav-links a.active{color:var(--ink)}
  .nav-links a.active{font-weight:700}
  .nav-cta{margin-left:auto;display:flex;gap:12px;align-items:center}

  .hamburger{display:none;background:none;border:0;cursor:pointer;padding:6px;color:var(--ink);margin-left:auto;border-radius:8px;transition:background .15s}
  .hamburger:hover{background:var(--tint-ink)}
  .hamburger svg{width:24px;height:24px;fill:var(--ink);display:block}

  .mobile-nav{display:none;position:fixed;inset:0;background:rgba(38,34,98,.96);z-index:200;flex-direction:column;align-items:center;justify-content:center;gap:28px;padding:40px 24px}
  .mobile-nav.open{display:flex}
  .mobile-nav a{color:#fff;font-size:1.3rem;font-weight:600;text-decoration:none;font-family:'Bricolage Grotesque',sans-serif;opacity:.9;transition:opacity .15s}
  .mobile-nav a:hover{opacity:1}
  .mobile-nav .divider{width:60px;height:1px;background:rgba(255,255,255,.2)}
  .mobile-nav-close{position:absolute;top:20px;right:20px;background:rgba(255,255,255,.12);border:0;color:#fff;font-size:1.3rem;cursor:pointer;width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;transition:background .15s}
  .mobile-nav-close:hover{background:rgba(255,255,255,.22)}
  .mobile-nav .btn{font-size:1rem;padding:12px 32px}

  .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;font-weight:600;font-size:.95rem;padding:12px 22px;border-radius:12px;text-decoration:none;border:0;cursor:pointer;transition:transform .15s ease,box-shadow .15s ease}
  .btn:focus-visible{outline:3px solid var(--blue);outline-offset:2px}
  .btn-primary{background:var(--blue);color:#fff;box-shadow:0 8px 20px -8px rgba(28,117,188,.55)}
  .btn-primary:hover{transform:translateY(-1px);box-shadow:0 12px 26px -8px rgba(28,117,188,.6)}
  .btn-ghost{background:transparent;color:var(--ink);border:1.5px solid var(--line)}
  .btn-ghost:hover{border-color:var(--ink)}

  /* ---- Page hero ---- */
  .page-hero{padding:80px 0 64px;text-align:center}
  .eyebrow{display:inline-flex;align-items:center;gap:8px;font-size:.8rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--blue);background:rgba(28,117,188,.1);border:1px solid rgba(28,117,188,.25);padding:6px 14px;border-radius:999px;margin-bottom:20px}
  .page-hero h1{font-size:clamp(2.2rem,4.5vw,3.5rem);font-weight:800;letter-spacing:-.03em;margin-bottom:18px}
  .page-hero h1 span{color:var(--blue)}
  .page-hero p{font-size:1.08rem;color:var(--muted);max-width:56ch;margin:0 auto 32px}
  .hero-actions{display:flex;gap:14px;justify-content:center;flex-wrap:wrap}

  /* ---- Plan badge ---- */
  .plan-tag{
    display:inline-block;font-size:.7rem;font-weight:700;letter-spacing:.06em;
    text-transform:uppercase;padding:3px 10px;border-radius:999px;vertical-align:middle;
    margin-left:8px;
  }
  .tag-basic{background:rgba(38,34,98,.1);color:var(--ink)}
  .tag-pro{background:rgba(28,117,188,.12);color:var(--blue)}
  .tag-business{background:rgba(28,117,188,.2);color:#0f4f8a}

  /* ---- Showcase (alternating) ---- */
  .showcase{padding:16px 0 80px}
  .showcase-item{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:64px;
    align-items:center;
    padding:64px 0;
    border-top:1px solid var(--line);
  }
  .showcase-item:first-child{border-top:none}
  .showcase-item.reverse .showcase-visual{order:-1}

  .showcase-text .eyebrow{margin-bottom:14px}
  .showcase-text h2{font-size:clamp(1.6rem,2.8vw,2.2rem);font-weight:800;letter-spacing:-.025em;margin-bottom:14px}
  .showcase-text p{font-size:.98rem;color:var(--muted);max-width:44ch;margin-bottom:24px}

  .feature-bullets{list-style:none;display:flex;flex-direction:column;gap:12px;margin-bottom:28px}
  .feature-bullets li{display:flex;align-items:flex-start;gap:10px;font-size:.93rem}
  .feature-bullets li .dot-check{
    flex-shrink:0;width:20px;height:20px;border-radius:50%;
    background:rgba(28,117,188,.12);
    display:flex;align-items:center;justify-content:center;margin-top:2px;
  }
  .feature-bullets li .dot-check svg{width:11px;height:11px;fill:var(--blue)}

  /* ---- Visual mockups ---- */
  .showcase-visual{
    background:var(--surface);
    border-radius:20px;
    border:1px solid var(--line);
    box-shadow:var(--shadow);
    overflow:hidden;
    min-height:300px;
    display:flex;
    flex-direction:column;
  }

  .mock-bar{
    display:flex;align-items:center;gap:8px;
    padding:12px 16px;border-bottom:1px solid var(--line);
    background:rgba(255,255,255,.8);
  }
  .mock-dot{width:10px;height:10px;border-radius:50%;background:var(--tint-ink-2)}
  .mock-title{margin-left:6px;font-size:.78rem;font-weight:600;color:var(--muted)}
  .mock-body{flex:1;padding:20px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--tint-ink) 0%,var(--tint-blue) 100%)}

  /* video grid mock */
  .vid-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;width:100%}
  .vid-tile{aspect-ratio:16/10;border-radius:12px;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden}
  .vid-tile:nth-child(1){background:linear-gradient(135deg,#3a358a,var(--ink))}
  .vid-tile:nth-child(2){background:linear-gradient(135deg,#2f8fd6,var(--blue))}
  .vid-tile:nth-child(3){background:linear-gradient(135deg,var(--blue),var(--ink))}
  .vid-tile:nth-child(4){background:linear-gradient(135deg,#514cab,#2a86c9)}
  .vid-avatar{width:42px;height:42px;border-radius:50%;background:rgba(255,255,255,.92);display:flex;align-items:center;justify-content:center;font-weight:800;font-family:'Bricolage Grotesque',sans-serif;font-size:.9rem;color:var(--ink)}
  .vid-tile:nth-child(2) .vid-avatar{color:var(--blue)}
  .vid-nametag{position:absolute;left:8px;bottom:8px;background:rgba(38,34,98,.55);color:#fff;font-size:.62rem;font-weight:600;padding:3px 8px;border-radius:6px;backdrop-filter:blur(4px)}
  .speaking-ring{box-shadow:inset 0 0 0 2.5px #6fd08c}

  /* screen share mock */
  .screen-mock{width:100%;background:#1a1a2e;border-radius:12px;overflow:hidden}
  .screen-topbar{background:#252545;padding:8px 14px;display:flex;align-items:center;gap:8px}
  .screen-dot{width:9px;height:9px;border-radius:50%}
  .screen-dot:nth-child(1){background:#ff5f57}
  .screen-dot:nth-child(2){background:#febc2e}
  .screen-dot:nth-child(3){background:#28c840}
  .screen-content{padding:20px;display:flex;flex-direction:column;gap:10px}
  .screen-line{height:10px;border-radius:4px;background:rgba(255,255,255,.12)}
  .screen-line.short{width:60%}
  .screen-line.med{width:80%}
  .screen-line.long{width:95%}
  .screen-block{height:80px;border-radius:8px;background:rgba(28,117,188,.25);margin-top:6px}
  .presenter-strip{display:flex;gap:8px;padding:10px 14px;border-top:1px solid rgba(255,255,255,.08)}
  .presenter-thumb{width:56px;height:36px;border-radius:6px;background:linear-gradient(135deg,#3a358a,var(--ink));display:flex;align-items:center;justify-content:center}
  .presenter-thumb.active{box-shadow:0 0 0 2px var(--blue)}
  .presenter-thumb span{font-size:.6rem;font-weight:700;color:#fff}

  /* chat mock */
  .chat-mock{width:100%;display:flex;flex-direction:column;gap:12px;padding:4px}
  .chat-msg{display:flex;gap:10px;align-items:flex-start}
  .chat-msg.me{flex-direction:row-reverse}
  .chat-avatar{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:800;font-family:'Bricolage Grotesque',sans-serif;flex-shrink:0}
  .chat-avatar.a1{background:linear-gradient(135deg,#3a358a,var(--ink));color:#fff}
  .chat-avatar.a2{background:linear-gradient(135deg,var(--blue),#2f8fd6);color:#fff}
  .chat-avatar.a3{background:linear-gradient(135deg,#514cab,#2a86c9);color:#fff}
  .chat-bubble{background:var(--tint-ink);border-radius:12px 12px 12px 4px;padding:10px 14px;max-width:200px}
  .chat-msg.me .chat-bubble{background:var(--blue);color:#fff;border-radius:12px 12px 4px 12px}
  .chat-bubble p{font-size:.78rem;line-height:1.5}
  .chat-bubble .chat-time{font-size:.65rem;opacity:.55;margin-top:3px}

  /* recording mock */
  .rec-mock{width:100%;display:flex;flex-direction:column;gap:12px;padding:4px}
  .rec-item{background:rgba(255,255,255,.7);border-radius:12px;padding:14px 16px;display:flex;align-items:center;gap:14px;border:1px solid var(--line)}
  .rec-icon{width:40px;height:40px;border-radius:10px;background:rgba(28,117,188,.12);display:flex;align-items:center;justify-content:center;flex-shrink:0}
  .rec-icon svg{width:20px;height:20px;fill:var(--blue)}
  .rec-info{flex:1}
  .rec-name{font-size:.85rem;font-weight:600;color:var(--ink)}
  .rec-meta{font-size:.75rem;color:var(--muted)}
  .rec-dur{font-size:.78rem;font-weight:600;color:var(--blue)}

  /* caption mock */
  .caption-wrap{width:100%;background:#111827;border-radius:12px;overflow:hidden;position:relative}
  .caption-video{height:160px;background:linear-gradient(135deg,#1e2d52 0%,#0d1b3e 100%);display:flex;align-items:center;justify-content:center}
  .caption-avatar-big{width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,#3a358a,var(--ink));display:flex;align-items:center;justify-content:center;font-weight:800;font-family:'Bricolage Grotesque',sans-serif;font-size:1.3rem;color:#fff;box-shadow:0 0 0 3px #6fd08c}
  .caption-bar{padding:14px 16px;background:#1a2235}
  .caption-text{font-size:.8rem;color:#e2e8f0;line-height:1.7;text-align:center}
  .caption-text span{background:rgba(28,117,188,.35);border-radius:3px;padding:0 3px}
  .caption-lang{display:flex;justify-content:flex-end;margin-top:8px}
  .caption-lang small{font-size:.65rem;color:rgba(255,255,255,.4);font-weight:600;letter-spacing:.05em;text-transform:uppercase}

  /* admin mock */
  .admin-mock{width:100%;display:flex;flex-direction:column;gap:10px;padding:4px}
  .admin-stat-row{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
  .admin-stat{background:rgba(255,255,255,.7);border-radius:12px;padding:14px;border:1px solid var(--line);text-align:center}
  .admin-stat b{display:block;font-family:'Bricolage Grotesque',sans-serif;font-size:1.4rem;font-weight:800;color:var(--ink)}
  .admin-stat span{font-size:.72rem;color:var(--muted)}
  .admin-table{background:rgba(255,255,255,.7);border-radius:12px;overflow:hidden;border:1px solid var(--line)}
  .admin-row{display:flex;align-items:center;gap:12px;padding:10px 14px;border-bottom:1px solid var(--line);font-size:.8rem}
  .admin-row:last-child{border-bottom:none}
  .admin-row .user-dot{width:28px;height:28px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:800;color:#fff}
  .admin-row .user-name{flex:1;font-weight:600;color:var(--ink)}
  .admin-row .user-role{font-size:.7rem;color:var(--muted)}
  .status-pill{font-size:.68rem;font-weight:700;padding:3px 9px;border-radius:999px}
  .status-active{background:rgba(111,208,140,.2);color:#1a7a3a}
  .status-pending{background:rgba(254,188,46,.2);color:#8a6200}

  /* ---- Features grid (smaller features) ---- */
  .feat-grid-section{padding:0 0 80px}
  .feat-grid-head{text-align:center;margin-bottom:48px}
  .feat-grid-head h2{font-size:clamp(1.7rem,3vw,2.3rem);font-weight:800;letter-spacing:-.025em;margin-bottom:12px}
  .feat-grid-head p{color:var(--muted);max-width:52ch;margin:0 auto}
  .feat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:22px}
  .feat-card{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);padding:28px 24px;transition:transform .18s ease,box-shadow .18s ease}
  .feat-card:hover{transform:translateY(-4px);box-shadow:var(--shadow)}
  .feat-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:16px}
  .feat-icon.ink{background:rgba(38,34,98,.1)}
  .feat-icon.ink svg{fill:var(--ink)}
  .feat-icon.blue{background:rgba(28,117,188,.12)}
  .feat-icon.blue svg{fill:var(--blue)}
  .feat-icon svg{width:22px;height:22px}
  .feat-card h3{font-size:1rem;font-weight:700;margin-bottom:6px}
  .feat-card p{font-size:.88rem;color:var(--muted);line-height:1.6}

  /* ---- CTA band ---- */
  .cta-band{background:linear-gradient(120deg,var(--ink) 0%,#1e3a7a 55%,var(--blue) 100%);border-radius:24px;padding:64px 48px;text-align:center;color:#fff;box-shadow:var(--shadow);margin-bottom:80px}
  .cta-band h2{font-size:clamp(1.6rem,3vw,2.3rem);font-weight:800;letter-spacing:-.02em;margin-bottom:12px}
  .cta-band p{color:rgba(255,255,255,.78);max-width:52ch;margin:0 auto 30px}
  .cta-band .btn-primary{background:#fff;color:var(--ink);box-shadow:none}
  .cta-band .btn-primary:hover{background:var(--tint-ink);transform:translateY(-1px)}
  .cta-actions{display:flex;gap:14px;justify-content:center;flex-wrap:wrap}
  .cta-band .btn-ghost-white{background:transparent;color:#fff;border:1.5px solid rgba(255,255,255,.4);border-radius:12px;padding:12px 22px;font-weight:600;font-size:.95rem;text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:border-color .15s}
  .cta-band .btn-ghost-white:hover{border-color:#fff}

  /* ---- Footer ---- */
  footer{border-top:1px solid var(--line);background:rgba(255,255,255,.55);backdrop-filter:blur(8px)}
  .foot{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:28px 0;flex-wrap:wrap}
  .foot-links{display:flex;gap:22px;flex-wrap:wrap}
  .foot-links a{color:var(--muted);text-decoration:none;font-size:.88rem}
  .foot-links a:hover{color:var(--ink)}
  .copy{font-size:.85rem;color:var(--muted)}

  /* ---- Responsive ---- */
  @media(max-width:920px){
    .nav-links,.nav-cta{display:none}
    .hamburger{display:block}
    .showcase-item{grid-template-columns:1fr;gap:36px;padding:48px 0}
    .showcase-item.reverse .showcase-visual{order:0}
    .feat-grid{grid-template-columns:repeat(2,1fr)}
  }
  @media(max-width:640px){
    .feat-grid{grid-template-columns:1fr}
    .admin-stat-row{grid-template-columns:repeat(2,1fr)}
    .cta-band{padding:40px 24px;border-radius:16px}
    .page-hero{padding:52px 0 40px}
  }
  @media(max-width:480px){
    .wrap{padding:0 16px}
    .hero-actions{flex-direction:column;align-items:center}
    .foot{flex-direction:column;text-align:center}
    .foot-links{justify-content:center}
    .vid-grid{grid-template-columns:repeat(2,1fr)}
  }
  @media(prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}}
</style>
</head>
<body>

<!-- Mobile nav -->
<div class="mobile-nav" id="mobileNav" role="dialog" aria-modal="true" aria-label="Mobile navigation">
  <button class="mobile-nav-close" id="mobileNavClose" aria-label="Close menu">&#x2715;</button>
  <a href="<?= base_url('features') ?>" onclick="closeMobileNav()">Features</a>
  <a href="<?= base_url('pricing') ?>" onclick="closeMobileNav()">Pricing</a>
  <a href="#" onclick="closeMobileNav()">Download</a>
  <a href="#" onclick="closeMobileNav()">Help</a>
  <div class="divider"></div>
  <a class="btn btn-ghost" href="<?= base_url('auth/login') ?>" style="color:#fff;border-color:rgba(255,255,255,.4)">Sign in</a>
  <a class="btn btn-primary" href="<?= base_url('auth/register') ?>">Sign up free</a>
</div>

<header>
  <div class="wrap nav">
    <a class="logo" href="<?= base_url('/') ?>">
      <img src="<?= base_url('img/logo-web.png') ?>" alt="VTalanoa" style="height:36px;max-width:180px;object-fit:contain;">
    </a>
    <nav class="nav-links" aria-label="Main">
      <a href="<?= base_url('features') ?>" class="active">Features</a>
      <a href="<?= base_url('pricing') ?>">Pricing</a>
      <a href="#">Download</a>
      <a href="#">Help</a>
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
  <section class="wrap page-hero">
    <span class="eyebrow">Built for real conversations</span>
    <h1>Everything you need to<br><span>meet, collaborate, and grow</span></h1>
    <p>From one-on-one catch-ups to company-wide all-hands — VTalanoa gives every team the tools to connect clearly, securely, and without limits.</p>
    <div class="hero-actions">
      <a class="btn btn-primary" href="<?= base_url('auth/register') ?>">Start for free</a>
      <a class="btn btn-ghost" href="<?= base_url('pricing') ?>">See pricing</a>
    </div>
  </section>

  <!-- Showcase items -->
  <section class="wrap showcase">

    <!-- HD Video & Audio -->
    <div class="showcase-item">
      <div class="showcase-text">
        <span class="eyebrow">HD Video &amp; Audio</span>
        <h2>Crystal-clear quality, every call</h2>
        <p>Adaptive HD video and intelligent noise suppression mean your meetings stay crisp and clear — on any network, any device.</p>
        <ul class="feature-bullets">
          <li><span class="dot-check"><svg viewBox="0 0 11 11"><path d="M1.5 5.5l2.8 2.8 5-5.5"/></svg></span>Up to 1080p video resolution</li>
          <li><span class="dot-check"><svg viewBox="0 0 11 11"><path d="M1.5 5.5l2.8 2.8 5-5.5"/></svg></span>AI-powered background noise suppression</li>
          <li><span class="dot-check"><svg viewBox="0 0 11 11"><path d="M1.5 5.5l2.8 2.8 5-5.5"/></svg></span>Bandwidth-adaptive streaming — no dropped calls</li>
          <li><span class="dot-check"><svg viewBox="0 0 11 11"><path d="M1.5 5.5l2.8 2.8 5-5.5"/></svg></span>Works in browser — no plug-ins or downloads</li>
        </ul>
        <span class="plan-tag tag-basic">Basic</span><span class="plan-tag tag-pro">Pro</span><span class="plan-tag tag-business">Business</span>
      </div>
      <div class="showcase-visual">
        <div class="mock-bar">
          <span class="mock-dot"></span><span class="mock-dot"></span><span class="mock-dot"></span>
          <span class="mock-title">Weekly team sync &middot; 4 participants</span>
        </div>
        <div class="mock-body">
          <div class="vid-grid">
            <div class="vid-tile speaking-ring"><div class="vid-avatar">AM</div><span class="vid-nametag">Amelia</span></div>
            <div class="vid-tile"><div class="vid-avatar">JT</div><span class="vid-nametag">Josef</span></div>
            <div class="vid-tile"><div class="vid-avatar">RK</div><span class="vid-nametag">Rani</span></div>
            <div class="vid-tile"><div class="vid-avatar">DS</div><span class="vid-nametag">Diego</span></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Screen Sharing -->
    <div class="showcase-item reverse">
      <div class="showcase-visual">
        <div class="mock-bar">
          <span class="mock-dot"></span><span class="mock-dot"></span><span class="mock-dot"></span>
          <span class="mock-title">Amelia is sharing her screen</span>
        </div>
        <div class="mock-body" style="padding:14px;background:#111827">
          <div class="screen-mock">
            <div class="screen-topbar">
              <span class="screen-dot"></span><span class="screen-dot"></span><span class="screen-dot"></span>
            </div>
            <div class="screen-content">
              <div class="screen-line long"></div>
              <div class="screen-line med"></div>
              <div class="screen-block"></div>
              <div class="screen-line short"></div>
              <div class="screen-line long"></div>
            </div>
            <div class="presenter-strip">
              <div class="presenter-thumb active"><span>AM</span></div>
              <div class="presenter-thumb"><span>JT</span></div>
              <div class="presenter-thumb"><span>RK</span></div>
            </div>
          </div>
        </div>
      </div>
      <div class="showcase-text">
        <span class="eyebrow">Screen Sharing</span>
        <h2>Present anything, collaborate instantly</h2>
        <p>Share your full screen, a single window, or a browser tab. Annotate in real time and hand off control to let others drive.</p>
        <ul class="feature-bullets">
          <li><span class="dot-check"><svg viewBox="0 0 11 11"><path d="M1.5 5.5l2.8 2.8 5-5.5"/></svg></span>Full screen, window, or tab sharing</li>
          <li><span class="dot-check"><svg viewBox="0 0 11 11"><path d="M1.5 5.5l2.8 2.8 5-5.5"/></svg></span>Real-time annotation tools</li>
          <li><span class="dot-check"><svg viewBox="0 0 11 11"><path d="M1.5 5.5l2.8 2.8 5-5.5"/></svg></span>Remote control hand-off</li>
          <li><span class="dot-check"><svg viewBox="0 0 11 11"><path d="M1.5 5.5l2.8 2.8 5-5.5"/></svg></span>Multiple presenters in one session</li>
        </ul>
        <span class="plan-tag tag-pro">Pro</span><span class="plan-tag tag-business">Business</span>
      </div>
    </div>

    <!-- In-meeting Chat -->
    <div class="showcase-item">
      <div class="showcase-text">
        <span class="eyebrow">In-Meeting Chat</span>
        <h2>Keep the conversation flowing</h2>
        <p>Send messages, share links, and drop files without ever breaking the flow of a call. Participants can react without unmuting.</p>
        <ul class="feature-bullets">
          <li><span class="dot-check"><svg viewBox="0 0 11 11"><path d="M1.5 5.5l2.8 2.8 5-5.5"/></svg></span>Real-time group and private messaging</li>
          <li><span class="dot-check"><svg viewBox="0 0 11 11"><path d="M1.5 5.5l2.8 2.8 5-5.5"/></svg></span>File and image sharing in-chat</li>
          <li><span class="dot-check"><svg viewBox="0 0 11 11"><path d="M1.5 5.5l2.8 2.8 5-5.5"/></svg></span>Emoji reactions without unmuting</li>
          <li><span class="dot-check"><svg viewBox="0 0 11 11"><path d="M1.5 5.5l2.8 2.8 5-5.5"/></svg></span>Chat history saved after the meeting ends</li>
        </ul>
        <span class="plan-tag tag-basic">Basic</span><span class="plan-tag tag-pro">Pro</span><span class="plan-tag tag-business">Business</span>
      </div>
      <div class="showcase-visual">
        <div class="mock-bar">
          <span class="mock-dot"></span><span class="mock-dot"></span><span class="mock-dot"></span>
          <span class="mock-title">Meeting chat</span>
        </div>
        <div class="mock-body" style="align-items:flex-start;padding:20px 16px">
          <div class="chat-mock">
            <div class="chat-msg">
              <div class="chat-avatar a1">AM</div>
              <div class="chat-bubble"><p>Can everyone see my slides?</p><div class="chat-time">2:14 PM</div></div>
            </div>
            <div class="chat-msg">
              <div class="chat-avatar a2">JT</div>
              <div class="chat-bubble"><p>Yes, looks great! 👍</p><div class="chat-time">2:14 PM</div></div>
            </div>
            <div class="chat-msg me">
              <div class="chat-avatar a3">RK</div>
              <div class="chat-bubble"><p>Can you go back one slide?</p><div class="chat-time">2:15 PM</div></div>
            </div>
            <div class="chat-msg">
              <div class="chat-avatar a1">AM</div>
              <div class="chat-bubble"><p>Sure! Here's the summary page.</p><div class="chat-time">2:15 PM</div></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Meeting Recordings -->
    <div class="showcase-item reverse">
      <div class="showcase-visual">
        <div class="mock-bar">
          <span class="mock-dot"></span><span class="mock-dot"></span><span class="mock-dot"></span>
          <span class="mock-title">My recordings</span>
        </div>
        <div class="mock-body" style="align-items:flex-start;padding:20px 16px">
          <div class="rec-mock">
            <div class="rec-item">
              <div class="rec-icon"><svg viewBox="0 0 24 24"><path d="M17 10.5V7a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-3.5l4 4v-11l-4 4z"/></svg></div>
              <div class="rec-info">
                <div class="rec-name">Weekly team sync</div>
                <div class="rec-meta">Jul 2, 2026 &middot; 4 participants</div>
              </div>
              <div class="rec-dur">48:22</div>
            </div>
            <div class="rec-item">
              <div class="rec-icon"><svg viewBox="0 0 24 24"><path d="M17 10.5V7a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-3.5l4 4v-11l-4 4z"/></svg></div>
              <div class="rec-info">
                <div class="rec-name">Q3 planning session</div>
                <div class="rec-meta">Jul 1, 2026 &middot; 12 participants</div>
              </div>
              <div class="rec-dur">1:12:05</div>
            </div>
            <div class="rec-item">
              <div class="rec-icon"><svg viewBox="0 0 24 24"><path d="M17 10.5V7a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-3.5l4 4v-11l-4 4z"/></svg></div>
              <div class="rec-info">
                <div class="rec-name">Client onboarding call</div>
                <div class="rec-meta">Jun 30, 2026 &middot; 3 participants</div>
              </div>
              <div class="rec-dur">32:47</div>
            </div>
          </div>
        </div>
      </div>
      <div class="showcase-text">
        <span class="eyebrow">Meeting Recordings</span>
        <h2>Never miss a moment</h2>
        <p>Record any meeting with one click. Recordings are stored in the cloud and shareable with a link — perfect for teammates who couldn't attend.</p>
        <ul class="feature-bullets">
          <li><span class="dot-check"><svg viewBox="0 0 11 11"><path d="M1.5 5.5l2.8 2.8 5-5.5"/></svg></span>One-click cloud recording</li>
          <li><span class="dot-check"><svg viewBox="0 0 11 11"><path d="M1.5 5.5l2.8 2.8 5-5.5"/></svg></span>Shareable playback links</li>
          <li><span class="dot-check"><svg viewBox="0 0 11 11"><path d="M1.5 5.5l2.8 2.8 5-5.5"/></svg></span>Auto-generated transcripts</li>
          <li><span class="dot-check"><svg viewBox="0 0 11 11"><path d="M1.5 5.5l2.8 2.8 5-5.5"/></svg></span>Download as MP4 for offline use</li>
        </ul>
        <span class="plan-tag tag-pro">Pro</span><span class="plan-tag tag-business">Business</span>
      </div>
    </div>

    <!-- Live Captions -->
    <div class="showcase-item">
      <div class="showcase-text">
        <span class="eyebrow">Live Captions</span>
        <h2>Meetings that everyone can follow</h2>
        <p>Real-time captions powered by AI transcribe every word as it is spoken — making meetings more accessible, inclusive, and productive for global teams.</p>
        <ul class="feature-bullets">
          <li><span class="dot-check"><svg viewBox="0 0 11 11"><path d="M1.5 5.5l2.8 2.8 5-5.5"/></svg></span>Real-time AI speech-to-text captions</li>
          <li><span class="dot-check"><svg viewBox="0 0 11 11"><path d="M1.5 5.5l2.8 2.8 5-5.5"/></svg></span>Support for multiple languages</li>
          <li><span class="dot-check"><svg viewBox="0 0 11 11"><path d="M1.5 5.5l2.8 2.8 5-5.5"/></svg></span>Full transcript saved after the call</li>
          <li><span class="dot-check"><svg viewBox="0 0 11 11"><path d="M1.5 5.5l2.8 2.8 5-5.5"/></svg></span>Toggle captions on or off per participant</li>
        </ul>
        <span class="plan-tag tag-pro">Pro</span><span class="plan-tag tag-business">Business</span>
      </div>
      <div class="showcase-visual">
        <div class="mock-bar">
          <span class="mock-dot"></span><span class="mock-dot"></span><span class="mock-dot"></span>
          <span class="mock-title">Live captions — English</span>
        </div>
        <div class="mock-body" style="padding:16px;background:#0d1117">
          <div class="caption-wrap">
            <div class="caption-video">
              <div class="caption-avatar-big">AM</div>
            </div>
            <div class="caption-bar">
              <div class="caption-text">
                "Let's walk through the <span>Q3 roadmap</span> and make sure everyone is aligned before the sprint begins."
              </div>
              <div class="caption-lang"><small>Auto-detected: English</small></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Admin Dashboard -->
    <div class="showcase-item reverse">
      <div class="showcase-visual">
        <div class="mock-bar">
          <span class="mock-dot"></span><span class="mock-dot"></span><span class="mock-dot"></span>
          <span class="mock-title">Admin dashboard</span>
        </div>
        <div class="mock-body" style="align-items:flex-start;padding:16px">
          <div class="admin-mock">
            <div class="admin-stat-row">
              <div class="admin-stat"><b>24</b><span>Active users</span></div>
              <div class="admin-stat"><b>138</b><span>Meetings this month</span></div>
              <div class="admin-stat"><b>99.9%</b><span>Uptime SLA</span></div>
            </div>
            <div class="admin-table">
              <div class="admin-row">
                <div class="user-dot" style="background:linear-gradient(135deg,#3a358a,#262262)">AM</div>
                <div><div class="user-name">Amelia Morgan</div><div class="user-role">Admin</div></div>
                <span class="status-pill status-active">Active</span>
              </div>
              <div class="admin-row">
                <div class="user-dot" style="background:linear-gradient(135deg,#1c75bc,#2f8fd6)">JT</div>
                <div><div class="user-name">Josef Torres</div><div class="user-role">Member</div></div>
                <span class="status-pill status-active">Active</span>
              </div>
              <div class="admin-row">
                <div class="user-dot" style="background:linear-gradient(135deg,#514cab,#2a86c9)">RK</div>
                <div><div class="user-name">Rani Kumar</div><div class="user-role">Member</div></div>
                <span class="status-pill status-pending">Pending</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="showcase-text">
        <span class="eyebrow">Admin Dashboard</span>
        <h2>Full control over your organisation</h2>
        <p>Manage your entire team from one place. Add and remove users, assign roles, monitor usage, and enforce security policies — all without leaving VTalanoa.</p>
        <ul class="feature-bullets">
          <li><span class="dot-check"><svg viewBox="0 0 11 11"><path d="M1.5 5.5l2.8 2.8 5-5.5"/></svg></span>User management and role assignment</li>
          <li><span class="dot-check"><svg viewBox="0 0 11 11"><path d="M1.5 5.5l2.8 2.8 5-5.5"/></svg></span>Advanced analytics and usage reports</li>
          <li><span class="dot-check"><svg viewBox="0 0 11 11"><path d="M1.5 5.5l2.8 2.8 5-5.5"/></svg></span>Single Sign-On (SSO) integration</li>
          <li><span class="dot-check"><svg viewBox="0 0 11 11"><path d="M1.5 5.5l2.8 2.8 5-5.5"/></svg></span>Custom branding and white-labelling</li>
        </ul>
        <span class="plan-tag tag-business">Business</span>
      </div>
    </div>

  </section>

  <!-- Secondary features grid -->
  <section class="wrap feat-grid-section">
    <div class="feat-grid-head">
      <h2>Everything else included</h2>
      <p>Every plan comes packed with the tools you need to run great meetings from day one.</p>
    </div>
    <div class="feat-grid">
      <article class="feat-card">
        <div class="feat-icon blue"><svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 3-1.34 3-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg></div>
        <h3>Up to 500 participants <span class="plan-tag tag-business">Business</span></h3>
        <p>Scale your meetings from intimate team calls up to 500-person company all-hands, all on the same platform.</p>
      </article>
      <article class="feat-card">
        <div class="feat-icon ink"><svg viewBox="0 0 24 24"><path d="M12 1 3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg></div>
        <h3>End-to-end encryption</h3>
        <p>Every meeting is encrypted in transit. Lobby controls ensure only invited guests can enter your calls.</p>
      </article>
      <article class="feat-card">
        <div class="feat-icon blue"><svg viewBox="0 0 24 24"><path d="M17 1H7a2 2 0 0 0-2 2v18a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2zm0 17H7V5h10v13z"/></svg></div>
        <h3>Works on every device</h3>
        <p>Join from any browser on Windows, Mac, iOS, or Android. No downloads or plug-ins required for guests.</p>
      </article>
      <article class="feat-card">
        <div class="feat-icon ink"><svg viewBox="0 0 24 24"><path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/></svg></div>
        <h3>Meeting scheduling <span class="plan-tag tag-pro">Pro</span></h3>
        <p>Schedule meetings in advance, sync with your calendar, and send automatic email invitations to all participants.</p>
      </article>
      <article class="feat-card">
        <div class="feat-icon blue"><svg viewBox="0 0 24 24"><path d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z"/></svg></div>
        <h3>Custom meeting URL <span class="plan-tag tag-pro">Pro</span></h3>
        <p>Create a personalised, memorable meeting link that you can share with clients and colleagues permanently.</p>
      </article>
      <article class="feat-card">
        <div class="feat-icon ink"><svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/></svg></div>
        <h3>Email invitations <span class="plan-tag tag-pro">Pro</span></h3>
        <p>Send branded email invitations with join links, dial-in details, and agenda notes directly from VTalanoa.</p>
      </article>
      <article class="feat-card">
        <div class="feat-icon blue"><svg viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zm4.24 16L12 15.45 7.77 18l1.12-4.81-3.73-3.23 4.92-.42L12 5l1.92 4.53 4.92.42-3.73 3.23L16.23 18z"/></svg></div>
        <h3>99.9% uptime SLA <span class="plan-tag tag-business">Business</span></h3>
        <p>A contractual uptime guarantee backed by globally distributed infrastructure so your meetings never fail you.</p>
      </article>
      <article class="feat-card">
        <div class="feat-icon ink"><svg viewBox="0 0 24 24"><path d="M20 3H4v10c0 2.21 1.79 4 4 4h6c2.21 0 4-1.79 4-4v-3h2c1.11 0 2-.89 2-2V5c0-1.11-.89-2-2-2zm0 5h-2V5h2v3zM4 19h16v2H4z"/></svg></div>
        <h3>API access <span class="plan-tag tag-business">Business</span></h3>
        <p>Integrate VTalanoa into your own products and workflows with a full REST API and webhook support.</p>
      </article>
      <article class="feat-card">
        <div class="feat-icon blue"><svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg></div>
        <h3>Unlimited call duration <span class="plan-tag tag-pro">Pro</span></h3>
        <p>No more 60-minute cut-offs. Pro and Business plans let your meetings run as long as you need, no interruptions.</p>
      </article>
    </div>
  </section>

  <!-- CTA -->
  <section class="wrap">
    <div class="cta-band">
      <h2>Ready to meet without limits?</h2>
      <p>Start free today — no credit card needed. Upgrade to Pro or Business when your team is ready to grow.</p>
      <div class="cta-actions">
        <a class="btn btn-primary" href="<?= base_url('auth/register') ?>">Get started free</a>
        <a class="btn-ghost-white" href="<?= base_url('pricing') ?>">Compare plans</a>
      </div>
    </div>
  </section>

</main>

<footer>
  <div class="wrap foot">
    <a class="logo" href="<?= base_url('/') ?>">
      <img src="<?= base_url('img/logo-web.png') ?>" alt="VTalanoa" style="height:32px;max-width:160px;object-fit:contain;">
    </a>
    <nav class="foot-links" aria-label="Footer">
      <a href="<?= base_url('privacy') ?>">Privacy</a>
      <a href="<?= base_url('terms') ?>">Terms</a>
      <a href="mailto:support@navulifiji.com">Support</a>
      <a href="mailto:support@navulifiji.com">Contact</a>
    </nav>
    <span class="copy">&copy; <?= date('Y') ?> VTalanoa. All rights reserved.</span>
  </div>
</footer>

<script>
(function(){
  const hamburger   = document.getElementById('hamburger');
  const mobileNav   = document.getElementById('mobileNav');
  const mobileClose = document.getElementById('mobileNavClose');

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
  mobileNav.addEventListener('click', function(e){ if(e.target===mobileNav) closeMobileNav(); });
  document.addEventListener('keydown', function(e){ if(e.key==='Escape') closeMobileNav(); });
})();
</script>
</body>
</html>
