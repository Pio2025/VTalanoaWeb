<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= isset($isHostCopy) && $isHostCopy ? 'Meeting Created' : "You're Invited to" ?> <?= esc($meeting['title']) ?> — VTalanoa</title>
<style>
  body { margin:0; background:#f1f5f9; font-family:'Segoe UI',Arial,sans-serif; }
  .wrap { max-width:560px; margin:32px auto; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,.08); }
  .header { background:linear-gradient(135deg,#262262 0%,#3d3797 50%,#1c75bc 100%); padding:32px; text-align:center; }
  .header h2 { color:#fff; margin:16px 0 0; font-size:18px; font-weight:600; }
  .body { padding:36px 32px; }
  p  { margin:0 0 14px; color:#475569; font-size:15px; line-height:1.6; }
  .meeting-card { background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:20px; margin:20px 0; }
  .meeting-title { font-size:18px; font-weight:700; color:#1e293b; margin-bottom:14px; }
  .meta-row { display:flex; align-items:flex-start; gap:10px; margin-bottom:10px; font-size:14px; color:#475569; }
  .meta-icon { color:#1c75bc; width:18px; flex-shrink:0; margin-top:1px; }
  .meta-label { font-weight:600; color:#334155; min-width:90px; }
  .meta-value { color:#475569; }
  .pw-box { background:#fff7ed; border:1.5px solid #fed7aa; border-radius:8px; padding:12px 16px; margin-top:14px; }
  .pw-box-label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#ea580c; margin-bottom:4px; }
  .pw-value { font-size:20px; font-weight:800; color:#1e293b; letter-spacing:3px; font-family:monospace; }
  .id-box { background:#eff6ff; border:1.5px solid #bfdbfe; border-radius:8px; padding:12px 16px; margin-top:10px; }
  .id-box-label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#2563eb; margin-bottom:4px; }
  .id-value { font-size:20px; font-weight:800; color:#1e293b; letter-spacing:3px; font-family:monospace; }
  .btn { display:inline-block; padding:14px 32px; background:#1c75bc; color:#fff; font-size:16px; font-weight:700; border-radius:10px; text-decoration:none; margin:12px 0; }
  .divider { height:1px; background:#e2e8f0; margin:20px 0; }
  .footer { text-align:center; padding:20px; background:#f8fafc; font-size:12px; color:#94a3b8; }
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <img src="<?= base_url('img/logo-white.png') ?>" alt="VTalanoa" width="160" style="height:auto;display:block;margin:0 auto;">
    <?php if (!empty($isHostCopy)): ?>
    <h2>&#10003; Your Meeting Has Been Created</h2>
    <?php else: ?>
    <h2>You're Invited to a Meeting</h2>
    <?php endif; ?>
  </div>

  <div class="body">
    <?php if (!empty($isHostCopy)): ?>
    <p>Hi <strong><?= esc($hostName) ?></strong>,</p>
    <p>Your meeting has been successfully scheduled. Here are the details to share with your participants.</p>
    <?php else: ?>
    <p>Hi there,</p>
    <p><strong><?= esc($hostName) ?></strong> has invited you to a meeting on VTalanoa.</p>
    <?php endif; ?>

    <div class="meeting-card">
      <div class="meeting-title"><?= esc($meeting['title']) ?></div>

      <div class="meta-row">
        <span class="meta-icon">&#128197;</span>
        <span class="meta-label">Date</span>
        <span class="meta-value"><?= date('l, F j, Y', strtotime($meeting['scheduled_start'])) ?></span>
      </div>

      <div class="meta-row">
        <span class="meta-icon">&#128336;</span>
        <span class="meta-label">Time</span>
        <span class="meta-value"><?= date('g:i A', strtotime($meeting['scheduled_start'])) ?> &ndash; <?= date('g:i A', strtotime($meeting['scheduled_end'])) ?></span>
      </div>

      <?php if (!empty($meeting['description'])): ?>
      <div class="meta-row">
        <span class="meta-icon">&#128196;</span>
        <span class="meta-label">Agenda</span>
        <span class="meta-value"><?= esc($meeting['description']) ?></span>
      </div>
      <?php endif; ?>

      <div class="id-box">
        <div class="id-box-label">&#128279; Meeting ID</div>
        <div class="id-value"><?= esc($meeting['meeting_uuid']) ?></div>
      </div>

      <?php
        $pw = $plainPassword ?? $meeting['password'] ?? '';
      ?>
      <?php if (!empty($pw)): ?>
      <div class="pw-box">
        <div class="pw-box-label">&#128274; Meeting Password</div>
        <div class="pw-value"><?= esc($pw) ?></div>
      </div>
      <?php endif; ?>
    </div>

    <div style="text-align:center;margin:28px 0;">
      <a href="<?= esc($joinLink) ?>" class="btn">
        <?= !empty($isHostCopy) ? 'Start Meeting' : 'Join Meeting' ?>
      </a>
    </div>

    <div class="divider"></div>

    <p style="font-size:13px;color:#94a3b8;">
      Or paste this link into your browser:<br>
      <a href="<?= esc($joinLink) ?>" style="color:#1c75bc;word-break:break-all;"><?= esc($joinLink) ?></a>
    </p>
  </div>

  <div class="footer">
    &copy; <?= date('Y') ?> VTalanoa &mdash; Connect, collaborate, and communicate — anywhere.
  </div>
</div>
</body>
</html>
