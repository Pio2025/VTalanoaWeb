<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Contact Form Submission — VTalanoa</title>
<style>
  body { margin:0; background:#f1f5f9; font-family:'Segoe UI',Arial,sans-serif; }
  .wrap { max-width:560px; margin:32px auto; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,.08); }
  .header { background:linear-gradient(135deg,#262262 0%,#3d3797 50%,#1c75bc 100%); padding:32px; text-align:center; }
  .header h2 { color:#fff; margin:16px 0 0; font-size:18px; font-weight:600; }
  .body { padding:36px 32px; }
  p  { margin:0 0 14px; color:#475569; font-size:15px; line-height:1.6; }
  .field-label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#94a3b8; margin-bottom:4px; }
  .field-value { font-size:15px; color:#1e293b; margin-bottom:20px; }
  .message-box { background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:20px; margin-top:8px; font-size:15px; color:#374151; line-height:1.7; white-space:pre-wrap; }
  .footer { text-align:center; padding:20px; background:#f8fafc; font-size:12px; color:#94a3b8; }
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <img src="<?= base_url('img/logo-web.png') ?>" alt="VTalanoa" width="160" style="height:auto;display:block;margin:0 auto;">
    <h2>&#128172; New Contact Form Submission</h2>
  </div>
  <div class="body">
    <div class="field-label">From</div>
    <div class="field-value"><?= esc($fromName) ?> &lt;<a href="mailto:<?= esc($fromEmail) ?>" style="color:#1c75bc;"><?= esc($fromEmail) ?></a>&gt;</div>

    <div class="field-label">Subject</div>
    <div class="field-value"><?= esc($subject) ?></div>

    <div class="field-label">Message</div>
    <div class="message-box"><?= esc($message) ?></div>

    <p style="margin-top:24px;font-size:13px;color:#94a3b8;">
      Reply directly to this email to respond to <?= esc($fromName) ?>.
    </p>
  </div>
  <div class="footer">
    &copy; <?= date('Y') ?> VTalanoa &mdash; Submitted via vtalanoa.com/contact
  </div>
</div>
</body>
</html>
