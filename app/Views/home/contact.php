<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contact Us — VTalanoa</title>
<link rel="icon" href="<?= base_url('favicon.ico') ?>">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
  body { font-family: 'Inter', sans-serif; color: var(--text); background: #fff; line-height: 1.6; }

  /* Nav */
  header { position: sticky; top: 0; z-index: 100; background: rgba(255,255,255,.95); backdrop-filter: blur(8px); border-bottom: 1px solid var(--line); }
  .wrap-nav { max-width: 1140px; margin: 0 auto; padding: 0 24px; display: flex; align-items: center; justify-content: space-between; height: 64px; }
  .logo img { display: block; }
  .nav-links { display: flex; gap: 28px; list-style: none; align-items: center; }
  .nav-links a { text-decoration: none; color: var(--muted); font-size: 14px; font-weight: 500; }
  .nav-links a:hover { color: var(--blue); }
  .btn-nav { background: var(--blue); color: #fff !important; padding: 8px 20px; border-radius: 8px; }
  .btn-nav:hover { background: #1560a0 !important; }

  /* Hero */
  .page-hero { background: linear-gradient(135deg, #262262 0%, #3d3797 50%, #1c75bc 100%); color: #fff; padding: 72px 24px 56px; text-align: center; }
  .page-hero h1 { font-size: clamp(30px, 5vw, 48px); font-weight: 800; margin-bottom: 14px; }
  .page-hero p { font-size: 16px; opacity: .85; max-width: 520px; margin: 0 auto; }

  /* Main layout */
  .contact-shell { max-width: 1100px; margin: 0 auto; padding: 72px 24px 96px; display: grid; grid-template-columns: 1fr 1.6fr; gap: 64px; align-items: start; }
  @media (max-width: 768px) { .contact-shell { grid-template-columns: 1fr; gap: 48px; } }

  /* Info column */
  .info-col h2 { font-size: 22px; font-weight: 700; color: var(--navy); margin-bottom: 8px; }
  .info-col > p { color: var(--muted); font-size: 15px; margin-bottom: 36px; }
  .contact-card { display: flex; gap: 16px; align-items: flex-start; margin-bottom: 28px; }
  .contact-icon { width: 46px; height: 46px; border-radius: 12px; background: linear-gradient(135deg,#eff6ff,#dbeafe); display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 20px; }
  .contact-info h4 { font-size: 14px; font-weight: 600; color: var(--navy); margin-bottom: 3px; }
  .contact-info p, .contact-info a { font-size: 14px; color: var(--muted); text-decoration: none; }
  .contact-info a:hover { color: var(--blue); }
  .divider { border: none; border-top: 1px solid var(--line); margin: 32px 0; }
  .response-badge { background: var(--bg); border: 1px solid var(--line); border-radius: 10px; padding: 16px 20px; font-size: 13px; color: var(--muted); }
  .response-badge strong { color: var(--text); }

  /* Form column */
  .form-card { background: #fff; border: 1px solid var(--line); border-radius: 20px; padding: 40px; box-shadow: 0 4px 24px rgba(0,0,0,.06); }
  .form-card h2 { font-size: 20px; font-weight: 700; color: var(--navy); margin-bottom: 6px; }
  .form-card > p { font-size: 14px; color: var(--muted); margin-bottom: 28px; }
  .form-group { margin-bottom: 20px; }
  .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
  @media (max-width: 480px) { .form-row { grid-template-columns: 1fr; } }
  label { display: block; font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 6px; }
  input, select, textarea { width: 100%; padding: 11px 14px; border: 1.5px solid var(--line); border-radius: 10px; font-size: 14px; font-family: inherit; color: var(--text); background: #fff; transition: border-color .2s, box-shadow .2s; outline: none; }
  input:focus, select:focus, textarea:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(28,117,188,.12); }
  textarea { resize: vertical; min-height: 140px; }
  .btn-submit { width: 100%; padding: 14px; background: linear-gradient(135deg, var(--navy), var(--blue)); color: #fff; border: none; border-radius: 10px; font-size: 15px; font-weight: 700; cursor: pointer; transition: opacity .2s; margin-top: 8px; }
  .btn-submit:hover { opacity: .9; }
  .alert { border-radius: 10px; padding: 14px 18px; font-size: 14px; margin-bottom: 24px; }
  .alert-success { background: #f0fdf4; border: 1px solid #86efac; color: #166534; }
  .alert-error   { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; }

  /* Footer */
  footer { border-top: 1px solid var(--line); background: var(--bg); }
  .foot { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; padding: 28px 24px; max-width: 1140px; margin: 0 auto; }
  .foot-links { display: flex; gap: 24px; }
  .foot-links a { color: var(--muted); text-decoration: none; font-size: 13px; }
  .foot-links a:hover { color: var(--blue); }
  .copy { font-size: 13px; color: var(--muted); }
</style>
</head>
<body>

<header>
  <div class="wrap-nav">
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
  </div>
</header>

<div class="page-hero">
  <h1>Contact Us</h1>
  <p>Have a question, feedback, or need support? We'd love to hear from you.</p>
</div>

<div class="contact-shell">

  <!-- Left: Info -->
  <div class="info-col">
    <h2>Get in touch</h2>
    <p>Our team is here to help. Reach out through any of the channels below or use the form.</p>

    <div class="contact-card">
      <div class="contact-icon">&#128140;</div>
      <div class="contact-info">
        <h4>General Support</h4>
        <p>Questions about your account, meetings, or billing.</p>
        <a href="mailto:support@navulifiji.com">support@navulifiji.com</a>
      </div>
    </div>

    <div class="contact-card">
      <div class="contact-icon">&#128272;</div>
      <div class="contact-info">
        <h4>Security</h4>
        <p>Report a vulnerability or security concern.</p>
        <a href="mailto:security@navulifiji.com">security@navulifiji.com</a>
      </div>
    </div>

    <div class="contact-card">
      <div class="contact-icon">&#128220;</div>
      <div class="contact-info">
        <h4>Privacy &amp; Data</h4>
        <p>Data requests, deletion, or policy questions.</p>
        <a href="mailto:privacy@navulifiji.com">privacy@navulifiji.com</a>
      </div>
    </div>

    <div class="contact-card">
      <div class="contact-icon">&#127979;</div>
      <div class="contact-info">
        <h4>Business &amp; Partnerships</h4>
        <p>Enterprise plans, integrations, and partnerships.</p>
        <a href="mailto:business@navulifiji.com">business@navulifiji.com</a>
      </div>
    </div>

    <hr class="divider">

    <div class="response-badge">
      &#9200; <strong>Response time:</strong> We typically reply within <strong>1–2 business days</strong>. For urgent issues, please mark your subject as <em>"Urgent"</em>.
    </div>
  </div>

  <!-- Right: Form -->
  <div class="form-card">
    <h2>Send us a message</h2>
    <p>Fill in the form and we'll get back to you as soon as possible.</p>

    <?php if (session()->has('success')): ?>
      <div class="alert alert-success">
        &#10003; <?= esc(session('success')) ?>
      </div>
    <?php endif; ?>

    <?php if (session()->has('error')): ?>
      <div class="alert alert-error">
        <?= esc(session('error')) ?>
      </div>
    <?php endif; ?>

    <?php $errors = session('errors') ?? []; ?>
    <?php if (!empty($errors)): ?>
      <div class="alert alert-error">
        <?php foreach ($errors as $e): ?>
          <?= esc($e) ?><br>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form action="<?= base_url('contact') ?>" method="POST" novalidate>
      <?= csrf_field() ?>

      <div class="form-row">
        <div>
          <label for="name">Your Name <span style="color:#ef4444">*</span></label>
          <input type="text" id="name" name="name" placeholder="John Doe"
                 value="<?= old('name') ?>" required>
        </div>
        <div>
          <label for="email">Email Address <span style="color:#ef4444">*</span></label>
          <input type="email" id="email" name="email" placeholder="you@example.com"
                 value="<?= old('email') ?>" required>
        </div>
      </div>

      <div class="form-group">
        <label for="topic">Topic</label>
        <select id="topic" name="topic">
          <option value="">— Select a topic —</option>
          <option value="General Enquiry"   <?= old('topic') === 'General Enquiry'   ? 'selected' : '' ?>>General Enquiry</option>
          <option value="Technical Support" <?= old('topic') === 'Technical Support' ? 'selected' : '' ?>>Technical Support</option>
          <option value="Billing"           <?= old('topic') === 'Billing'           ? 'selected' : '' ?>>Billing &amp; Subscriptions</option>
          <option value="Security"          <?= old('topic') === 'Security'          ? 'selected' : '' ?>>Security Issue</option>
          <option value="Privacy"           <?= old('topic') === 'Privacy'           ? 'selected' : '' ?>>Privacy &amp; Data</option>
          <option value="Feature Request"   <?= old('topic') === 'Feature Request'   ? 'selected' : '' ?>>Feature Request</option>
          <option value="Partnership"       <?= old('topic') === 'Partnership'       ? 'selected' : '' ?>>Business &amp; Partnership</option>
          <option value="Other"             <?= old('topic') === 'Other'             ? 'selected' : '' ?>>Other</option>
        </select>
      </div>

      <div class="form-group">
        <label for="subject">Subject <span style="color:#ef4444">*</span></label>
        <input type="text" id="subject" name="subject" placeholder="Brief description of your enquiry"
               value="<?= old('subject') ?>" required>
      </div>

      <div class="form-group">
        <label for="message">Message <span style="color:#ef4444">*</span></label>
        <textarea id="message" name="message" placeholder="Please describe your question or issue in as much detail as possible..." required><?= old('message') ?></textarea>
      </div>

      <button type="submit" class="btn-submit">
        &#9993; Send Message
      </button>
    </form>
  </div>

</div>

<footer>
  <div class="foot">
    <a class="logo" href="<?= base_url('/') ?>">
      <img src="<?= base_url('img/logo-web.png') ?>" alt="VTalanoa" style="height:30px;max-width:140px;object-fit:contain;">
    </a>
    <nav class="foot-links" aria-label="Footer">
      <a href="<?= base_url('privacy') ?>">Privacy Policy</a>
      <a href="<?= base_url('terms') ?>">Terms of Service</a>
      <a href="<?= base_url('contact') ?>">Contact</a>
    </nav>
    <span class="copy">&copy; <?= date('Y') ?> VTalanoa. All rights reserved.</span>
  </div>
</footer>

</body>
</html>
