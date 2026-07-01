<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Reset Your Password — VTalanoa</title>
<style>
  body { margin:0; background:#f1f5f9; font-family:'Segoe UI',Arial,sans-serif; }
  .wrap { max-width:560px; margin:32px auto; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,.08); }
  .header { background:linear-gradient(135deg,#262262 0%,#3d3797 50%,#0090c8 100%); padding:36px 32px; text-align:center; }
  .logo { display:inline-flex; align-items:center; gap:10px; color:#fff; font-size:22px; font-weight:800; text-decoration:none; }
  .logo-icon { width:40px; height:40px; background:#00aeef; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px; }
  .body { padding:36px 32px; }
  h2 { margin:0 0 12px; font-size:22px; color:#1e293b; }
  p  { margin:0 0 16px; color:#475569; font-size:15px; line-height:1.6; }
  .btn { display:inline-block; padding:14px 32px; background:#00aeef; color:#fff; font-size:16px; font-weight:700; border-radius:10px; text-decoration:none; margin:12px 0; }
  .note { background:#f8fafc; border-left:4px solid #00aeef; border-radius:4px; padding:12px 16px; font-size:13px; color:#64748b; margin-top:20px; }
  .footer { text-align:center; padding:20px; background:#f8fafc; font-size:12px; color:#94a3b8; }
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div class="logo">
      <div class="logo-icon">V</div>
      VTalanoa
    </div>
  </div>
  <div class="body">
    <h2>Reset Your Password</h2>
    <p>Hi <?= esc($name) ?>,</p>
    <p>We received a request to reset the password for your VTalanoa account. Click the button below to create a new password.</p>
    <div style="text-align:center;margin:28px 0;">
      <a href="<?= esc($resetLink ?? $resetUrl ?? '#') ?>" class="btn">Reset Password</a>
    </div>
    <div class="note">
      <strong>This link expires in 1 hour.</strong><br>
      If you didn't request a password reset, you can safely ignore this email. Your password will remain unchanged.
    </div>
    <p style="margin-top:20px;font-size:13px;color:#94a3b8;">
      Or copy and paste this URL into your browser:<br>
      <?php $rl = $resetLink ?? $resetUrl ?? '#'; ?>
      <a href="<?= esc($rl) ?>" style="color:#00aeef;word-break:break-all;"><?= esc($rl) ?></a>
    </p>
  </div>
  <div class="footer">
    &copy; <?= date('Y') ?> VTalanoa. All rights reserved.
  </div>
</div>
</body>
</html>
