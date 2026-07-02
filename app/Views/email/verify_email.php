<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Verify Your Email — VTalanoa</title>
<style>
  body { margin:0; background:#f1f5f9; font-family:'Segoe UI',Arial,sans-serif; }
  .wrap { max-width:560px; margin:32px auto; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,.08); }
  .header { background:linear-gradient(135deg,#262262 0%,#3d3797 50%,#1c75bc 100%); padding:32px; text-align:center; }
  .body { padding:36px 32px; }
  h2 { margin:0 0 12px; font-size:22px; color:#1e293b; }
  p  { margin:0 0 16px; color:#475569; font-size:15px; line-height:1.6; }
  .btn { display:inline-block; padding:14px 32px; background:#1c75bc; color:#fff; font-size:16px; font-weight:700; border-radius:10px; text-decoration:none; margin:12px 0; }
  .footer { text-align:center; padding:20px; background:#f8fafc; font-size:12px; color:#94a3b8; }
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <img src="<?= base_url('img/logo-web.png') ?>" alt="VTalanoa" width="160" style="height:auto;display:block;margin:0 auto;">
  </div>
  <div class="body">
    <h2>Verify Your Email Address</h2>
    <p>Welcome to VTalanoa, <?= esc($name) ?>!</p>
    <p>Please click the button below to verify your email address and activate your account.</p>
    <div style="text-align:center;margin:28px 0;">
      <a href="<?= esc($verifyLink) ?>" class="btn">Verify Email Address</a>
    </div>
    <p style="font-size:13px;color:#94a3b8;">
      If you didn't create a VTalanoa account, you can safely ignore this email.<br><br>
      Or copy and paste this URL:<br>
      <a href="<?= esc($verifyLink) ?>" style="color:#1c75bc;word-break:break-all;"><?= esc($verifyLink) ?></a>
    </p>
  </div>
  <div class="footer">
    &copy; <?= date('Y') ?> VTalanoa. All rights reserved.
  </div>
</div>
</body>
</html>
