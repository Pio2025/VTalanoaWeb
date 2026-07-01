<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'VTalanoa') ?></title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- App CSS -->
    <link href="<?= base_url('css/app.css') ?>" rel="stylesheet">
    <?= $this->renderSection('head') ?>
</head>
<body class="app-layout">

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="<?= base_url('dashboard') ?>" class="sidebar-brand">
            <img src="<?= base_url('img/logo.png') ?>" alt="VTalanoa" style="height:36px;max-width:160px;object-fit:contain;">
        </a>
        <button class="sidebar-toggle d-lg-none" id="sidebarClose">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav-list">
            <li class="nav-item <?= uri_string() === 'dashboard' ? 'active' : '' ?>">
                <a href="<?= base_url('dashboard') ?>" class="nav-link">
                    <i class="fa-solid fa-gauge-high"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item <?= str_starts_with(uri_string(), 'meetings') ? 'active' : '' ?>">
                <a href="<?= base_url('meetings') ?>" class="nav-link">
                    <i class="fa-solid fa-calendar-days"></i>
                    <span>My Meetings</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('meetings/schedule') ?>" class="nav-link">
                    <i class="fa-solid fa-plus-circle"></i>
                    <span>New Meeting</span>
                </a>
            </li>
            <li class="nav-item <?= uri_string() === 'workspace' ? 'active' : '' ?>">
                <a href="<?= base_url('workspace') ?>" class="nav-link">
                    <i class="fa-solid fa-briefcase"></i>
                    <span>Workplace</span>
                </a>
            </li>
            <li class="nav-divider"></li>
            <li class="nav-item <?= uri_string() === 'profile' ? 'active' : '' ?>">
                <a href="<?= base_url('profile') ?>" class="nav-link">
                    <i class="fa-solid fa-user-circle"></i>
                    <span>Profile</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('auth/logout') ?>" class="nav-link nav-logout">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Sign Out</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <?php $user = session()->get('auth_user'); ?>
        <a href="<?= base_url('profile') ?>" class="user-card">
            <div class="user-avatar">
                <?php if (!empty($user['profile_photo'])): ?>
                    <img src="<?= base_url($user['profile_photo']) ?>" alt="Avatar">
                <?php else: ?>
                    <span><?= strtoupper(substr($user['fname'] ?? 'U', 0, 1)) . strtoupper(substr($user['lname'] ?? '', 0, 1)) ?></span>
                <?php endif; ?>
            </div>
            <div class="user-info">
                <div class="user-name"><?= esc($user['fname'] . ' ' . $user['lname']) ?></div>
                <div class="user-email"><?= esc($user['email']) ?></div>
            </div>
        </a>
    </div>
</aside>

<!-- Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Main Content -->
<main class="main-content">
    <!-- Top Bar -->
    <header class="topbar">
        <button class="topbar-menu-btn d-lg-none" id="sidebarOpen">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="topbar-title">
            <?= esc($pageTitle ?? ($title ?? 'VTalanoa')) ?>
        </div>
        <div class="topbar-actions">
            <a href="<?= base_url('meetings/schedule') ?>" class="btn btn-primary btn-sm btn-new-meeting">
                <i class="fa-solid fa-plus"></i>
                <span class="d-none d-sm-inline">New Meeting</span>
            </a>
        </div>
    </header>

    <!-- Flash Messages -->
    <div class="flash-container">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i>
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i>
                <ul class="mb-0 mt-1">
                    <?php foreach (session()->getFlashdata('errors') as $err): ?>
                        <li><?= esc($err) ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>

    <div class="content-area">
        <?= $this->renderSection('content') ?>
    </div>
</main>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= base_url('js/app.js') ?>"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
