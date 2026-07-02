<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'VTalanoa') ?></title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="<?= base_url('css/app.css') ?>" rel="stylesheet">
</head>
<body class="auth-layout">

<div class="auth-split">

    <!-- Left brand panel -->
    <div class="auth-panel-brand">
        <div class="auth-panel-brand-inner">
            <a href="<?= base_url('/') ?>" class="auth-brand-link">
                <img src="<?= base_url('img/logo-white.png') ?>" alt="VTalanoa" style="height:42px;max-width:200px;object-fit:contain;">
            </a>
            <div class="auth-brand-body">
                <h2 class="auth-brand-headline">Meet anyone,<br>anywhere.</h2>
                <p class="auth-brand-sub">HD video meetings, secure by default — free for everyone to start.</p>
                <ul class="auth-brand-perks">
                    <li>
                        <span class="perk-icon"><svg viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="10" fill="rgba(255,255,255,.18)"/><path d="M6 10l3 3 5-5" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                        Up to 100 guests — no account needed
                    </li>
                    <li>
                        <span class="perk-icon"><svg viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="10" fill="rgba(255,255,255,.18)"/><path d="M6 10l3 3 5-5" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                        HD video, screen sharing &amp; live chat
                    </li>
                    <li>
                        <span class="perk-icon"><svg viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="10" fill="rgba(255,255,255,.18)"/><path d="M6 10l3 3 5-5" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                        End-to-end encrypted &amp; secure
                    </li>
                    <li>
                        <span class="perk-icon"><svg viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="10" fill="rgba(255,255,255,.18)"/><path d="M6 10l3 3 5-5" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                        Works in any browser, on any device
                    </li>
                </ul>
            </div>
            <div class="auth-panel-footer">
                &copy; <?= date('Y') ?> VTalanoa. All rights reserved.
            </div>
        </div>
    </div>

    <!-- Right form panel -->
    <div class="auth-panel-form">
        <div class="auth-panel-form-inner">

            <!-- Mobile-only logo -->
            <div class="auth-mobile-logo">
                <a href="<?= base_url('/') ?>">
                    <img src="<?= base_url('img/logo.png') ?>" alt="VTalanoa" style="height:36px;object-fit:contain;">
                </a>
            </div>

            <!-- Flash Messages -->
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success mb-4">
                    <i class="fa-solid fa-circle-check me-2"></i>
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger mb-4">
                    <i class="fa-solid fa-circle-exclamation me-2"></i>
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">
                        <?php foreach (session()->getFlashdata('errors') as $err): ?>
                            <li><?= esc($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
