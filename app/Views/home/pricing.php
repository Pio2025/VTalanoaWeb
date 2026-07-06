<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pricing — VTalanoa</title>
<link rel="icon" href="<?= base_url('favicon.ico') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="<?= base_url('css/public.css') ?>" rel="stylesheet">
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

  .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;font-weight:600;font-size:.95rem;padding:12px 22px;border-radius:12px;text-decoration:none;border:0;cursor:pointer;transition:transform .15s ease, box-shadow .15s ease}
  .btn:focus-visible{outline:3px solid var(--blue);outline-offset:2px}
  .btn-primary{background:var(--blue);color:#fff;box-shadow:0 8px 20px -8px rgba(28,117,188,.55)}
  .btn-primary:hover{transform:translateY(-1px);box-shadow:0 12px 26px -8px rgba(28,117,188,.6)}
  .btn-ink{background:var(--ink);color:#fff}
  .btn-ink:hover{transform:translateY(-1px)}
  .btn-ghost{background:transparent;color:var(--ink);border:1.5px solid var(--line)}
  .btn-ghost:hover{border-color:var(--ink)}
  .btn-full{width:100%;justify-content:center}

  /* ---------- Page hero ---------- */
  .page-hero{padding:72px 0 56px;text-align:center}
  .eyebrow{display:inline-flex;align-items:center;gap:8px;font-size:.8rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--blue);background:rgba(28,117,188,.1);border:1px solid rgba(28,117,188,.25);padding:6px 14px;border-radius:999px;margin-bottom:20px}
  .page-hero h1{font-size:clamp(2rem,4vw,3.2rem);font-weight:800;letter-spacing:-.025em;margin-bottom:16px}
  .page-hero p{font-size:1.05rem;color:var(--muted);max-width:52ch;margin:0 auto}

  /* ---------- Pricing grid ---------- */
  .pricing-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:24px;
    padding-bottom:80px;
    align-items:start;
  }

  .plan{
    background:var(--surface);
    border:1px solid var(--line);
    border-radius:20px;
    padding:36px 30px 32px;
    transition:transform .18s ease, box-shadow .18s ease;
    position:relative;
  }
  .plan:hover{transform:translateY(-4px);box-shadow:var(--shadow)}

  /* Pro card — featured */
  .plan.featured{
    background:linear-gradient(145deg,var(--ink) 0%,#1e3a7a 60%,var(--blue) 100%);
    border-color:transparent;
    color:#fff;
    transform:scale(1.03);
    box-shadow:var(--shadow);
  }
  .plan.featured:hover{transform:scale(1.03) translateY(-4px)}

  .plan-badge{
    display:inline-block;
    font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;
    padding:4px 12px;border-radius:999px;
    background:rgba(28,117,188,.12);color:var(--blue);
    margin-bottom:20px;
  }
  .plan.featured .plan-badge{background:rgba(255,255,255,.18);color:#fff}

  .plan-name{font-size:1.15rem;font-weight:700;margin-bottom:6px}
  .plan.featured .plan-name{color:#fff}

  .plan-desc{font-size:.9rem;color:var(--muted);margin-bottom:24px;min-height:40px}
  .plan.featured .plan-desc{color:rgba(255,255,255,.72)}

  .plan-price{display:flex;align-items:flex-end;gap:4px;margin-bottom:28px}
  .price-amount{font-family:'Bricolage Grotesque',sans-serif;font-size:3rem;font-weight:800;line-height:1;letter-spacing:-.04em}
  .plan.featured .price-amount{color:#fff}
  .price-period{font-size:.88rem;color:var(--muted);padding-bottom:6px}
  .plan.featured .price-period{color:rgba(255,255,255,.65)}
  .price-free{font-family:'Bricolage Grotesque',sans-serif;font-size:2.4rem;font-weight:800;line-height:1;letter-spacing:-.03em;color:var(--ink)}

  .plan-divider{height:1px;background:var(--line);margin-bottom:24px}
  .plan.featured .plan-divider{background:rgba(255,255,255,.18)}

  .feature-list{list-style:none;display:flex;flex-direction:column;gap:13px;margin-bottom:32px}
  .feature-list li{display:flex;align-items:flex-start;gap:10px;font-size:.92rem}
  .plan.featured .feature-list li{color:rgba(255,255,255,.9)}
  .feature-list li .check{
    flex-shrink:0;width:18px;height:18px;border-radius:50%;
    background:rgba(28,117,188,.12);
    display:flex;align-items:center;justify-content:center;margin-top:2px;
  }
  .plan.featured .feature-list li .check{background:rgba(255,255,255,.2)}
  .feature-list li .check svg{width:10px;height:10px;fill:var(--blue)}
  .plan.featured .feature-list li .check svg{fill:#fff}

  .feature-list li .cross{
    flex-shrink:0;width:18px;height:18px;border-radius:50%;
    background:rgba(38,34,98,.06);
    display:flex;align-items:center;justify-content:center;margin-top:2px;
  }
  .feature-list li .cross svg{width:10px;height:10px;fill:var(--muted)}
  .feature-list li.dim{color:var(--muted)}

  /* Featured plan CTA */
  .plan.featured .btn-primary{background:#fff;color:var(--ink);box-shadow:none}
  .plan.featured .btn-primary:hover{background:var(--tint-ink)}

  /* ---------- FAQ / compare note ---------- */
  .note-strip{text-align:center;padding:0 0 72px}
  .note-strip p{font-size:.9rem;color:var(--muted)}
  .note-strip a{color:var(--blue);text-decoration:none;font-weight:600}
  .note-strip a:hover{text-decoration:underline}

  /* ---------- Responsive ---------- */
  @media (max-width:960px){
    .pricing-grid{grid-template-columns:1fr}
    .plan.featured{transform:none}
    .plan.featured:hover{transform:translateY(-4px)}
  }
  @media (max-width:640px){
    .wrap{padding:0 20px}
    .note-strip{padding-top:40px}
  }
  @media (max-width:480px){
    .page-hero{padding:48px 0 36px}
  }
  @media (prefers-reduced-motion:reduce){
    *{animation:none !important;transition:none !important}
  }
</style>
</head>
<body>

<?= view('partials/public_nav') ?>

<main>
  <!-- Page hero -->
  <section class="wrap page-hero">
    <span class="eyebrow">Simple, transparent pricing</span>
    <h1>Plans for every team size</h1>
    <p>Start free and upgrade when you need more. No hidden fees, no surprises — cancel anytime.</p>
  </section>

  <!-- Pricing cards -->
  <section class="wrap pricing-grid">

    <!-- Basic -->
    <div class="plan">
      <span class="plan-badge">Basic</span>
      <div class="plan-name">Basic</div>
      <div class="plan-desc">Perfect for personal use and small teams getting started.</div>
      <div class="plan-price">
        <span class="price-free">Free</span>
      </div>
      <div class="plan-divider"></div>
      <ul class="feature-list">
        <li>
          <span class="check"><svg viewBox="0 0 10 10"><path d="M1.5 5l2.5 2.5 4.5-5"/></svg></span>
          Up to <strong>100 participants</strong>
        </li>
        <li>
          <span class="check"><svg viewBox="0 0 10 10"><path d="M1.5 5l2.5 2.5 4.5-5"/></svg></span>
          <strong>60-minute</strong> meeting limit
        </li>
        <li>
          <span class="check"><svg viewBox="0 0 10 10"><path d="M1.5 5l2.5 2.5 4.5-5"/></svg></span>
          HD video &amp; audio
        </li>
        <li class="dim">
          <span class="cross"><svg viewBox="0 0 10 10"><path d="M2 2l6 6M8 2l-6 6"/></svg></span>
          Screen sharing
        </li>
        <li>
          <span class="check"><svg viewBox="0 0 10 10"><path d="M1.5 5l2.5 2.5 4.5-5"/></svg></span>
          In-meeting chat
        </li>
        <li class="dim">
          <span class="cross"><svg viewBox="0 0 10 10"><path d="M2 2l6 6M8 2l-6 6"/></svg></span>
          Live captions
        </li>
        <li>
          <span class="check"><svg viewBox="0 0 10 10"><path d="M1.5 5l2.5 2.5 4.5-5"/></svg></span>
          Browser-based, no download needed
        </li>
        <li class="dim">
          <span class="cross"><svg viewBox="0 0 10 10"><path d="M2 2l6 6M8 2l-6 6"/></svg></span>
          Meeting recordings
        </li>
        <li class="dim">
          <span class="cross"><svg viewBox="0 0 10 10"><path d="M2 2l6 6M8 2l-6 6"/></svg></span>
          Custom meeting URL
        </li>
        <li class="dim">
          <span class="cross"><svg viewBox="0 0 10 10"><path d="M2 2l6 6M8 2l-6 6"/></svg></span>
          Admin dashboard
        </li>
      </ul>
      <a class="btn btn-ghost btn-full" href="<?= base_url('auth/register') ?>">Get started free</a>
    </div>

    <!-- Pro (featured) -->
    <div class="plan featured">
      <span class="plan-badge">Most popular</span>
      <div class="plan-name">Pro</div>
      <div class="plan-desc">For growing teams that need longer calls, recordings, and more control.</div>
      <div class="plan-price">
        <span class="price-amount">$30</span>
        <span class="price-period">/ month</span>
      </div>
      <div class="plan-divider"></div>
      <ul class="feature-list">
        <li>
          <span class="check"><svg viewBox="0 0 10 10"><path d="M1.5 5l2.5 2.5 4.5-5"/></svg></span>
          Everything in Basic
        </li>
        <li>
          <span class="check"><svg viewBox="0 0 10 10"><path d="M1.5 5l2.5 2.5 4.5-5"/></svg></span>
          Up to <strong>300 participants</strong>
        </li>
        <li>
          <span class="check"><svg viewBox="0 0 10 10"><path d="M1.5 5l2.5 2.5 4.5-5"/></svg></span>
          <strong>Unlimited</strong> meeting duration
        </li>
        <li>
          <span class="check"><svg viewBox="0 0 10 10"><path d="M1.5 5l2.5 2.5 4.5-5"/></svg></span>
          Meeting recordings
        </li>
        <li>
          <span class="check"><svg viewBox="0 0 10 10"><path d="M1.5 5l2.5 2.5 4.5-5"/></svg></span>
          Custom meeting room URL
        </li>
        <li>
          <span class="check"><svg viewBox="0 0 10 10"><path d="M1.5 5l2.5 2.5 4.5-5"/></svg></span>
          Meeting scheduling &amp; calendar sync
        </li>
        <li>
          <span class="check"><svg viewBox="0 0 10 10"><path d="M1.5 5l2.5 2.5 4.5-5"/></svg></span>
          Email invitations
        </li>
        <li>
          <span class="check"><svg viewBox="0 0 10 10"><path d="M1.5 5l2.5 2.5 4.5-5"/></svg></span>
          Priority support
        </li>
        <li>
          <span class="cross"><svg viewBox="0 0 10 10"><path d="M2 2l6 6M8 2l-6 6"/></svg></span>
          Custom branding
        </li>
      </ul>
      <a class="btn btn-primary btn-full" href="<?= base_url('auth/register') ?>">Start Pro — $30/mo</a>
    </div>

    <!-- Business -->
    <div class="plan">
      <span class="plan-badge">Business</span>
      <div class="plan-name">Business</div>
      <div class="plan-desc">For organisations that need scale, branding, and enterprise controls.</div>
      <div class="plan-price">
        <span class="price-amount">$50</span>
        <span class="price-period">/ month</span>
      </div>
      <div class="plan-divider"></div>
      <ul class="feature-list">
        <li>
          <span class="check"><svg viewBox="0 0 10 10"><path d="M1.5 5l2.5 2.5 4.5-5"/></svg></span>
          Everything in Pro
        </li>
        <li>
          <span class="check"><svg viewBox="0 0 10 10"><path d="M1.5 5l2.5 2.5 4.5-5"/></svg></span>
          Up to <strong>500 participants</strong>
        </li>
        <li>
          <span class="check"><svg viewBox="0 0 10 10"><path d="M1.5 5l2.5 2.5 4.5-5"/></svg></span>
          Admin dashboard &amp; user management
        </li>
        <li>
          <span class="check"><svg viewBox="0 0 10 10"><path d="M1.5 5l2.5 2.5 4.5-5"/></svg></span>
          Single Sign-On (SSO)
        </li>
        <li>
          <span class="check"><svg viewBox="0 0 10 10"><path d="M1.5 5l2.5 2.5 4.5-5"/></svg></span>
          Advanced analytics &amp; reports
        </li>
        <li>
          <span class="check"><svg viewBox="0 0 10 10"><path d="M1.5 5l2.5 2.5 4.5-5"/></svg></span>
          Custom branding &amp; logo
        </li>
        <li>
          <span class="check"><svg viewBox="0 0 10 10"><path d="M1.5 5l2.5 2.5 4.5-5"/></svg></span>
          99.9% uptime SLA guarantee
        </li>
        <li>
          <span class="check"><svg viewBox="0 0 10 10"><path d="M1.5 5l2.5 2.5 4.5-5"/></svg></span>
          Dedicated account support
        </li>
        <li>
          <span class="check"><svg viewBox="0 0 10 10"><path d="M1.5 5l2.5 2.5 4.5-5"/></svg></span>
          API access
        </li>
      </ul>
      <a class="btn btn-ink btn-full" href="<?= base_url('auth/register') ?>">Start Business — $50/mo</a>
    </div>

  </section>

  <div class="wrap note-strip">
    <p>All paid plans are billed monthly. Need a custom plan for a large organisation? <a href="<?= base_url('contact') ?>">Contact us</a>.</p>
  </div>
</main>

<?= view('partials/public_foot') ?>
</body>
</html>
