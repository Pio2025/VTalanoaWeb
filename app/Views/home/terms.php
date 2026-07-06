<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Terms of Service — VTalanoa</title>
<link rel="icon" href="<?= base_url('favicon.ico') ?>">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="<?= base_url('css/public.css') ?>" rel="stylesheet">
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

  /* Hero band */
  .page-hero { background: linear-gradient(135deg, #262262 0%, #3d3797 50%, #1c75bc 100%); color: #fff; padding: 64px 24px 48px; text-align: center; }
  .page-hero h1 { font-size: clamp(28px, 5vw, 42px); font-weight: 700; margin-bottom: 12px; }
  .page-hero p { font-size: 15px; opacity: .82; }

  /* Content */
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
  .highlight-box { background: #eff6ff; border-left: 4px solid var(--blue); border-radius: 0 8px 8px 0; padding: 16px 20px; margin: 20px 0; font-size: 14px; color: #1e40af; }
  a { color: var(--blue); }

  @media (max-width: 640px) {
    .content { padding: 40px 20px 60px; }
  }
</style>
</head>
<body>

<?= view('partials/public_nav') ?>

<div class="page-hero">
  <h1>Terms of Service</h1>
  <p>Please read these terms carefully before using VTalanoa.</p>
</div>

<div class="content">
  <p class="last-updated">Last updated: <strong>1 July 2026</strong> &nbsp;|&nbsp; Effective date: <strong>1 July 2026</strong></p>

  <div class="toc">
    <h3>Table of Contents</h3>
    <ol>
      <li><a href="#acceptance">Acceptance of Terms</a></li>
      <li><a href="#description">Description of Service</a></li>
      <li><a href="#accounts">User Accounts</a></li>
      <li><a href="#conduct">Acceptable Use</a></li>
      <li><a href="#content">User Content</a></li>
      <li><a href="#ip">Intellectual Property</a></li>
      <li><a href="#privacy">Privacy</a></li>
      <li><a href="#payments">Payments &amp; Subscriptions</a></li>
      <li><a href="#termination">Termination</a></li>
      <li><a href="#disclaimer">Disclaimers &amp; Limitation of Liability</a></li>
      <li><a href="#changes">Changes to Terms</a></li>
      <li><a href="#contact">Contact Us</a></li>
    </ol>
  </div>

  <section id="acceptance">
    <h2>1. Acceptance of Terms</h2>
    <p>By accessing or using VTalanoa ("the Service", "we", "us", or "our") at <strong>vtalanoa.com</strong>, you agree to be bound by these Terms of Service ("Terms"). If you do not agree to all the terms, you may not use the Service.</p>
    <p>These Terms apply to all visitors, users, and others who access or use the Service. By creating an account or using any feature of VTalanoa, you confirm you are at least 18 years of age, or 13 years of age with parental consent, and have the legal capacity to enter into these Terms.</p>
  </section>

  <section id="description">
    <h2>2. Description of Service</h2>
    <p>VTalanoa is an online video meeting and collaboration platform that allows users to:</p>
    <ul>
      <li>Host and join video meetings with HD video and audio</li>
      <li>Share screens and collaborate in real time</li>
      <li>Use in-meeting chat and file sharing</li>
      <li>Record meetings (where permitted)</li>
      <li>Schedule and manage meetings</li>
      <li>Invite participants via secure links</li>
    </ul>
    <p>We reserve the right to modify, suspend, or discontinue any aspect of the Service at any time with reasonable notice.</p>
  </section>

  <section id="accounts">
    <h2>3. User Accounts</h2>
    <p>To use most features of VTalanoa, you must register for an account. When creating an account, you agree to:</p>
    <ul>
      <li>Provide accurate, current, and complete information</li>
      <li>Maintain and promptly update your account information</li>
      <li>Keep your password secure and not share it with others</li>
      <li>Accept responsibility for all activities that occur under your account</li>
      <li>Notify us immediately at <a href="mailto:support@vtalanoa.com">support@vtalanoa.com</a> of any unauthorised access</li>
    </ul>
    <p>You may not create an account on behalf of someone else without their explicit consent, or use another person's account without permission.</p>
  </section>

  <section id="conduct">
    <h2>4. Acceptable Use</h2>
    <p>You agree not to use VTalanoa to:</p>
    <ul>
      <li>Violate any applicable law or regulation</li>
      <li>Harass, abuse, threaten, or intimidate other users</li>
      <li>Transmit spam, unsolicited messages, or advertising</li>
      <li>Distribute malware, viruses, or other harmful software</li>
      <li>Attempt to gain unauthorised access to any part of the Service or its infrastructure</li>
      <li>Record, screenshot, or distribute meeting content without all participants' consent</li>
      <li>Impersonate any person or entity</li>
      <li>Engage in any activity that disrupts or interferes with the Service</li>
      <li>Share illegal, obscene, defamatory, or harmful content</li>
    </ul>
    <div class="highlight-box">
      Violation of this Acceptable Use Policy may result in immediate account suspension or termination without refund.
    </div>
  </section>

  <section id="content">
    <h2>5. User Content</h2>
    <p>You retain ownership of any content you create, share, or transmit through VTalanoa ("User Content"), including meeting recordings, chat messages, and shared files.</p>
    <p>By using the Service, you grant VTalanoa a limited, non-exclusive, royalty-free licence to process and transmit your User Content solely as necessary to provide the Service. We do not claim ownership of your content and will not use it for marketing or other purposes without your consent.</p>
    <p>You are solely responsible for your User Content and represent that:</p>
    <ul>
      <li>You own the content or have the right to share it</li>
      <li>The content does not infringe any third-party rights</li>
      <li>The content complies with all applicable laws</li>
    </ul>
  </section>

  <section id="ip">
    <h2>6. Intellectual Property</h2>
    <p>The VTalanoa platform, including its software, design, logos, and documentation, is owned by VTalanoa / Navuli Fiji and protected by copyright, trademark, and other intellectual property laws.</p>
    <p>You may not copy, modify, distribute, sell, or lease any part of the Service, nor may you reverse-engineer or extract source code, unless permitted by law or with our written consent.</p>
  </section>

  <section id="privacy">
    <h2>7. Privacy</h2>
    <p>Your use of VTalanoa is also governed by our <a href="<?= base_url('privacy') ?>">Privacy Policy</a>, which is incorporated into these Terms by reference. Please review the Privacy Policy to understand how we collect, use, and share your information.</p>
  </section>

  <section id="payments">
    <h2>8. Payments &amp; Subscriptions</h2>
    <p>VTalanoa offers a free Basic plan and paid Pro and Business subscriptions. By subscribing to a paid plan, you agree to:</p>
    <ul>
      <li>Pay all fees as described in our <a href="<?= base_url('pricing') ?>">Pricing</a> page</li>
      <li>Provide accurate billing information</li>
      <li>Authorise us to charge your payment method on a recurring basis</li>
    </ul>
    <p>Subscriptions renew automatically unless cancelled before the renewal date. Refunds are handled on a case-by-case basis — contact <a href="mailto:support@vtalanoa.com">support@vtalanoa.com</a> within 7 days of a charge if you believe an error occurred.</p>
    <p>We reserve the right to change pricing with 30 days' notice to existing subscribers.</p>
  </section>

  <section id="termination">
    <h2>9. Termination</h2>
    <p>You may delete your account at any time by contacting us at <a href="mailto:support@vtalanoa.com">support@vtalanoa.com</a>. Upon deletion, your data will be removed in accordance with our Privacy Policy.</p>
    <p>We may suspend or terminate your access to the Service immediately, without prior notice, if you violate these Terms, engage in fraudulent activity, or if we are required to do so by law. Upon termination, your right to use the Service will immediately cease.</p>
  </section>

  <section id="disclaimer">
    <h2>10. Disclaimers &amp; Limitation of Liability</h2>
    <p>The Service is provided on an "as is" and "as available" basis without warranties of any kind, either express or implied, including but not limited to warranties of merchantability, fitness for a particular purpose, or non-infringement.</p>
    <p>VTalanoa does not warrant that the Service will be uninterrupted, error-free, or free from viruses or other harmful components. We are not responsible for any loss of data, loss of profits, or any other damages arising from your use of or inability to use the Service.</p>
    <p>To the maximum extent permitted by law, VTalanoa's total liability to you for any claims arising from these Terms or your use of the Service shall not exceed the amount you paid us in the 12 months prior to the claim.</p>
  </section>

  <section id="changes">
    <h2>11. Changes to Terms</h2>
    <p>We may update these Terms from time to time. When we make material changes, we will notify you by email (to the address on your account) and update the "Last updated" date at the top of this page.</p>
    <p>Continued use of the Service after changes become effective constitutes acceptance of the new Terms. If you do not agree to the updated Terms, you must stop using the Service.</p>
  </section>

  <section id="contact">
    <h2>12. Contact Us</h2>
    <p>If you have any questions about these Terms of Service, please contact us:</p>
    <ul>
      <li><strong>Email:</strong> <a href="mailto:support@vtalanoa.com">support@vtalanoa.com</a></li>
      <li><strong>Website:</strong> <a href="<?= base_url('/') ?>">vtalanoa.com</a></li>
    </ul>
  </section>
</div>

<?= view('partials/public_foot') ?>
</body>
</html>
