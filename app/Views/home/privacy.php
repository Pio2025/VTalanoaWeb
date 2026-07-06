<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Privacy Policy — VTalanoa</title>
<link rel="icon" href="<?= base_url('favicon.ico') ?>">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --navy: #262262;
    --blue: #1c75bc;
    --text: #1e293b;
    --muted: #64748b;
    --line: #e2e8f0;
    --bg: #f8fafc;
  }
  body { font-family: 'Inter', sans-serif; color: var(--text); background: #fff; line-height: 1.7; }

  header { position: sticky; top: 0; z-index: 100; background: rgba(255,255,255,.95); backdrop-filter: blur(8px); border-bottom: 1px solid var(--line); }
  .wrap { max-width: 900px; margin: 0 auto; padding: 0 24px; }
  .wrap.nav { display: flex; align-items: center; justify-content: space-between; height: 64px; }
  .logo img { display: block; }
  .nav-links { display: flex; gap: 28px; list-style: none; }
  .nav-links a { text-decoration: none; color: var(--muted); font-size: 14px; font-weight: 500; }
  .nav-links a:hover { color: var(--blue); }
  .btn-nav { background: var(--blue); color: #fff !important; padding: 8px 20px; border-radius: 8px; }
  .btn-nav:hover { background: #1560a0 !important; color: #fff !important; }

  .page-hero { background: linear-gradient(135deg, #262262 0%, #3d3797 50%, #1c75bc 100%); color: #fff; padding: 64px 24px 48px; text-align: center; }
  .page-hero h1 { font-size: clamp(28px, 5vw, 42px); font-weight: 700; margin-bottom: 12px; }
  .page-hero p { font-size: 15px; opacity: .82; }

  .content { max-width: 780px; margin: 0 auto; padding: 56px 24px 80px; }
  .last-updated { font-size: 13px; color: var(--muted); margin-bottom: 40px; padding-bottom: 24px; border-bottom: 1px solid var(--line); }
  .toc { background: var(--bg); border: 1px solid var(--line); border-radius: 12px; padding: 24px 28px; margin-bottom: 48px; }
  .toc h3 { font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; color: var(--muted); margin-bottom: 14px; }
  .toc ol { padding-left: 20px; }
  .toc li { margin-bottom: 6px; }
  .toc a { color: var(--blue); text-decoration: none; font-size: 14px; }
  .toc a:hover { text-decoration: underline; }

  section { margin-bottom: 48px; }
  section h2 { font-size: 20px; font-weight: 700; color: var(--navy); margin-bottom: 14px; padding-bottom: 10px; border-bottom: 2px solid var(--blue); display: inline-block; }
  section p { color: #374151; margin-bottom: 14px; font-size: 15px; }
  section ul, section ol { padding-left: 22px; margin-bottom: 14px; }
  section li { color: #374151; font-size: 15px; margin-bottom: 8px; }
  .data-table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 14px; }
  .data-table th { background: var(--navy); color: #fff; padding: 12px 16px; text-align: left; font-weight: 600; }
  .data-table td { padding: 11px 16px; border-bottom: 1px solid var(--line); vertical-align: top; }
  .data-table tr:nth-child(even) td { background: var(--bg); }
  .highlight-box { background: #eff6ff; border-left: 4px solid var(--blue); border-radius: 0 8px 8px 0; padding: 16px 20px; margin: 20px 0; font-size: 14px; color: #1e40af; }
  a { color: var(--blue); }

  footer { border-top: 1px solid var(--line); background: var(--bg); }
  .foot { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; padding: 28px 24px; max-width: 900px; margin: 0 auto; }
  .foot-links { display: flex; gap: 24px; flex-wrap: wrap; }
  .foot-links a { color: var(--muted); text-decoration: none; font-size: 13px; }
  .foot-links a:hover { color: var(--blue); }
  .copy { font-size: 13px; color: var(--muted); }
  .data-table { overflow-x: auto; display: block; }

  /* Hamburger & mobile nav */
  .hamburger { display: none; background: none; border: 0; cursor: pointer; padding: 6px; border-radius: 8px; }
  .hamburger:hover { background: #f1f5f9; }
  .hamburger svg { width: 24px; height: 24px; display: block; fill: var(--navy); }
  .mobile-nav { display: none; position: fixed; inset: 0; background: rgba(38,34,98,.97); z-index: 300; flex-direction: column; align-items: center; justify-content: center; gap: 28px; padding: 40px 24px; }
  .mobile-nav.open { display: flex; }
  .mobile-nav a { color: #fff; font-size: 1.2rem; font-weight: 600; text-decoration: none; }
  .mobile-nav a:hover { opacity: .8; }
  .mobile-nav .divider { width: 60px; height: 1px; background: rgba(255,255,255,.2); }
  .mobile-nav .btn-nav-m { background: var(--blue); padding: 12px 32px; border-radius: 10px; font-size: 1rem; }
  .mobile-nav-close { position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,.15); border: 0; color: #fff; width: 42px; height: 42px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1.3rem; }
  @media (max-width: 768px) {
    .nav-links { display: none; }
    .hamburger { display: block; }
  }
  @media (max-width: 640px) {
    .content { padding: 40px 20px 60px; }
    .foot { flex-direction: column; align-items: center; text-align: center; }
    .foot-links { justify-content: center; }
  }
</style>
</head>
<body>

<div class="mobile-nav" id="mobileNav" role="dialog" aria-modal="true" aria-label="Mobile navigation">
  <button class="mobile-nav-close" id="mobileNavClose" aria-label="Close menu">&#x2715;</button>
  <a href="<?= base_url('features') ?>">Features</a>
  <a href="<?= base_url('pricing') ?>">Pricing</a>
  <a href="<?= base_url('support') ?>">Help</a>
  <div class="divider"></div>
  <a href="<?= base_url('auth/login') ?>">Sign In</a>
  <a href="<?= base_url('auth/register') ?>" class="btn-nav-m">Get Started</a>
</div>

<header>
  <div class="wrap nav">
    <a class="logo" href="<?= base_url('/') ?>">
      <img src="<?= base_url('img/logo-web.png') ?>" alt="VTalanoa" style="height:36px;max-width:180px;object-fit:contain;">
    </a>
    <nav>
      <ul class="nav-links">
        <li><a href="<?= base_url('features') ?>">Features</a></li>
        <li><a href="<?= base_url('pricing') ?>">Pricing</a></li>
        <li><a href="<?= base_url('auth/login') ?>">Sign In</a></li>
        <li><a href="<?= base_url('auth/register') ?>" class="btn-nav">Get Started</a></li>
      </ul>
    </nav>
    <button class="hamburger" id="hamburger" aria-label="Open menu" aria-expanded="false" aria-controls="mobileNav">
      <svg viewBox="0 0 24 24"><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/></svg>
    </button>
  </div>
</header>

<div class="page-hero">
  <h1>Privacy Policy</h1>
  <p>How VTalanoa collects, uses, and protects your information.</p>
</div>

<div class="content">
  <p class="last-updated">Last updated: <strong>1 July 2026</strong> &nbsp;|&nbsp; Effective date: <strong>1 July 2026</strong></p>

  <div class="toc">
    <h3>Table of Contents</h3>
    <ol>
      <li><a href="#overview">Overview</a></li>
      <li><a href="#data-collect">Information We Collect</a></li>
      <li><a href="#data-use">How We Use Your Information</a></li>
      <li><a href="#data-share">How We Share Your Information</a></li>
      <li><a href="#cookies">Cookies &amp; Tracking</a></li>
      <li><a href="#retention">Data Retention</a></li>
      <li><a href="#security">Security</a></li>
      <li><a href="#rights">Your Rights</a></li>
      <li><a href="#children">Children's Privacy</a></li>
      <li><a href="#international">International Transfers</a></li>
      <li><a href="#changes">Changes to This Policy</a></li>
      <li><a href="#contact">Contact Us</a></li>
    </ol>
  </div>

  <section id="overview">
    <h2>1. Overview</h2>
    <p>VTalanoa ("we", "us", "our") is a video meeting and collaboration platform operated by VTalanoa and accessible at <strong>vtalanoa.com</strong>. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our platform.</p>
    <p>By using VTalanoa, you agree to the collection and use of information in accordance with this policy. If you do not agree with any part of this policy, please discontinue use of the Service.</p>
    <div class="highlight-box">
      We do not sell your personal data to third parties. Ever.
    </div>
  </section>

  <section id="data-collect">
    <h2>2. Information We Collect</h2>
    <p>We collect information in the following ways:</p>

    <table class="data-table">
      <thead>
        <tr><th>Category</th><th>What We Collect</th><th>When</th></tr>
      </thead>
      <tbody>
        <tr>
          <td><strong>Account Data</strong></td>
          <td>Name, email address, username, password (hashed), timezone, profile photo</td>
          <td>When you register</td>
        </tr>
        <tr>
          <td><strong>Meeting Data</strong></td>
          <td>Meeting title, description, schedule, participant list, meeting recordings (if enabled)</td>
          <td>When you create or join a meeting</td>
        </tr>
        <tr>
          <td><strong>Usage Data</strong></td>
          <td>Pages visited, features used, session duration, IP address, browser type, device info</td>
          <td>Automatically during use</td>
        </tr>
        <tr>
          <td><strong>Communications</strong></td>
          <td>In-meeting chat messages, support emails you send us</td>
          <td>When you communicate</td>
        </tr>
        <tr>
          <td><strong>Payment Data</strong></td>
          <td>Billing name, payment method details (processed via our payment provider — we do not store full card numbers)</td>
          <td>When you subscribe to a paid plan</td>
        </tr>
        <tr>
          <td><strong>Social Login</strong></td>
          <td>Profile name, email, and provider ID from Google, Microsoft, Facebook, or Apple when you sign in via OAuth</td>
          <td>When you use social login</td>
        </tr>
      </tbody>
    </table>
  </section>

  <section id="data-use">
    <h2>3. How We Use Your Information</h2>
    <p>We use the information we collect to:</p>
    <ul>
      <li>Create and manage your account</li>
      <li>Provide, operate, and improve the Service</li>
      <li>Process payments and manage subscriptions</li>
      <li>Send transactional emails (password resets, meeting invitations, email verification)</li>
      <li>Respond to your support requests</li>
      <li>Monitor for and prevent fraudulent or abusive activity</li>
      <li>Comply with legal obligations</li>
      <li>Analyse usage patterns to improve the platform (using aggregated, anonymised data)</li>
    </ul>
    <p>We will not use your data for automated decision-making that produces legal or similarly significant effects without your consent.</p>
  </section>

  <section id="data-share">
    <h2>4. How We Share Your Information</h2>
    <p>We do not sell, trade, or rent your personal information. We may share data only in the following circumstances:</p>
    <ul>
      <li><strong>With other participants in a meeting</strong> — your display name and video/audio are shared with other participants by design.</li>
      <li><strong>Service providers</strong> — we use third-party services (hosting, email delivery, payment processing) that may process your data on our behalf under strict data processing agreements.</li>
      <li><strong>Legal requirements</strong> — we may disclose your information if required to do so by law, court order, or governmental authority.</li>
      <li><strong>Business transfers</strong> — in the event of a merger, acquisition, or sale of assets, your data may be transferred as part of the transaction with advance notice to you.</li>
    </ul>
  </section>

  <section id="cookies">
    <h2>5. Cookies &amp; Tracking</h2>
    <p>We use cookies and similar tracking technologies to enhance your experience. The types of cookies we use:</p>
    <ul>
      <li><strong>Essential cookies</strong> — necessary for authentication, session management, and security (e.g. CSRF protection). These cannot be disabled.</li>
      <li><strong>Preference cookies</strong> — remember your settings such as theme and timezone.</li>
      <li><strong>Analytics cookies</strong> — help us understand how the platform is used so we can improve it. Data is aggregated and anonymised.</li>
    </ul>
    <p>You can control non-essential cookies through your browser settings. Disabling essential cookies will prevent you from logging in.</p>
  </section>

  <section id="retention">
    <h2>6. Data Retention</h2>
    <p>We retain your data for as long as your account is active or as needed to provide the Service. Specifically:</p>
    <ul>
      <li><strong>Account data</strong> — retained until you delete your account or request deletion.</li>
      <li><strong>Meeting recordings</strong> — retained for 90 days after the meeting ends, then automatically deleted unless you download them.</li>
      <li><strong>Chat messages</strong> — retained for 12 months, then purged.</li>
      <li><strong>Server logs</strong> — retained for 30 days for security and debugging purposes.</li>
      <li><strong>Payment records</strong> — retained for 7 years as required by financial regulations.</li>
    </ul>
    <p>After account deletion, we will anonymise or delete your personal data within 30 days, except where retention is required by law.</p>
  </section>

  <section id="security">
    <h2>7. Security</h2>
    <p>We take the security of your data seriously and implement industry-standard measures, including:</p>
    <ul>
      <li>HTTPS encryption for all data in transit</li>
      <li>Bcrypt hashing for passwords (never stored in plain text)</li>
      <li>Hashed, time-limited tokens for password reset links</li>
      <li>CSRF protection on all forms</li>
      <li>JWT-based session authentication</li>
      <li>Regular security reviews and dependency updates</li>
    </ul>
    <p>Despite our efforts, no method of transmission over the internet or electronic storage is 100% secure. We cannot guarantee absolute security, and you use the Service at your own risk. Please notify us immediately at <a href="mailto:security@vtalanoa.com">security@vtalanoa.com</a> if you suspect any breach.</p>
  </section>

  <section id="rights">
    <h2>8. Your Rights</h2>
    <p>Depending on your location, you may have the following rights regarding your personal data:</p>
    <ul>
      <li><strong>Access</strong> — request a copy of the personal data we hold about you</li>
      <li><strong>Correction</strong> — request that we correct inaccurate or incomplete data</li>
      <li><strong>Deletion</strong> — request that we delete your data ("right to be forgotten")</li>
      <li><strong>Portability</strong> — receive your data in a structured, machine-readable format</li>
      <li><strong>Objection</strong> — object to certain types of data processing</li>
      <li><strong>Restriction</strong> — request that we restrict how we use your data</li>
    </ul>
    <p>To exercise any of these rights, contact us at <a href="mailto:privacy@vtalanoa.com">privacy@vtalanoa.com</a>. We will respond within 30 days.</p>
    <div class="highlight-box">
      You can update your name, email, and profile photo at any time from your <a href="<?= base_url('profile') ?>">account profile page</a>.
    </div>
  </section>

  <section id="children">
    <h2>9. Children's Privacy</h2>
    <p>VTalanoa is not directed at children under the age of 13. We do not knowingly collect personal information from children under 13. If you are a parent or guardian and believe your child has provided us with personal information, please contact us immediately at <a href="mailto:privacy@vtalanoa.com">privacy@vtalanoa.com</a> and we will delete the information promptly.</p>
    <p>Users between 13 and 18 may use VTalanoa only with verifiable parental or guardian consent.</p>
  </section>

  <section id="international">
    <h2>10. International Transfers</h2>
    <p>VTalanoa is operated from Fiji. Your information may be processed and stored on servers located outside your country of residence, including in countries where data protection laws may differ from those in your jurisdiction.</p>
    <p>By using the Service, you consent to the transfer of your information to these locations. We take steps to ensure that any international transfers comply with applicable data protection requirements.</p>
  </section>

  <section id="changes">
    <h2>11. Changes to This Policy</h2>
    <p>We may update this Privacy Policy from time to time. When we make material changes, we will notify you by email and update the "Last updated" date at the top of this page.</p>
    <p>We encourage you to review this policy periodically. Continued use of the Service after any changes indicates your acceptance of the updated policy.</p>
  </section>

  <section id="contact">
    <h2>12. Contact Us</h2>
    <p>If you have questions, concerns, or requests regarding this Privacy Policy, please contact us:</p>
    <ul>
      <li><strong>General privacy:</strong> <a href="mailto:privacy@vtalanoa.com">privacy@vtalanoa.com</a></li>
      <li><strong>Security issues:</strong> <a href="mailto:security@vtalanoa.com">security@vtalanoa.com</a></li>
      <li><strong>General support:</strong> <a href="mailto:support@vtalanoa.com">support@vtalanoa.com</a></li>
      <li><strong>Website:</strong> <a href="<?= base_url('/') ?>">vtalanoa.com</a></li>
    </ul>
  </section>
</div>

<footer>
  <div class="foot">
    <a class="logo" href="<?= base_url('/') ?>">
      <img src="<?= base_url('img/logo-web.png') ?>" alt="VTalanoa" style="height:30px;max-width:140px;object-fit:contain;">
    </a>
    <nav class="foot-links" aria-label="Footer">
      <a href="<?= base_url('privacy') ?>">Privacy Policy</a>
      <a href="<?= base_url('terms') ?>">Terms of Service</a>
      <a href="mailto:support@vtalanoa.com">Support</a>
    </nav>
    <span class="copy">&copy; <?= date('Y') ?> VTalanoa. All rights reserved.</span>
  </div>
</footer>

<script>
(function(){
  const h = document.getElementById('hamburger');
  const n = document.getElementById('mobileNav');
  const c = document.getElementById('mobileNavClose');
  function open(){ n.classList.add('open'); h?.setAttribute('aria-expanded','true'); document.body.style.overflow='hidden'; }
  function close(){ n.classList.remove('open'); h?.setAttribute('aria-expanded','false'); document.body.style.overflow=''; }
  h?.addEventListener('click', open);
  c?.addEventListener('click', close);
  document.addEventListener('keydown', e => e.key === 'Escape' && close());
})();
</script>
</body>
</html>
