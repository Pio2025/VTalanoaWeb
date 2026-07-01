<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<div class="auth-form-header">
    <h1 class="auth-title">Welcome back</h1>
    <p class="auth-subtitle">Sign in to your VTalanoa account</p>
</div>

<!-- Social Login Buttons -->
<div class="social-login-section">
    <a href="<?= base_url('auth/social/google') ?>" class="btn btn-social btn-google">
        <svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
        </svg>
        Continue with Google
    </a>
    <div class="social-row">
        <a href="<?= base_url('auth/social/facebook') ?>" class="btn btn-social btn-facebook">
            <i class="fa-brands fa-facebook-f"></i>
            Facebook
        </a>
        <a href="<?= base_url('auth/social/microsoft') ?>" class="btn btn-social btn-microsoft">
            <svg width="18" height="18" viewBox="0 0 21 21" xmlns="http://www.w3.org/2000/svg">
                <rect x="1" y="1" width="9" height="9" fill="#F25022"/>
                <rect x="11" y="1" width="9" height="9" fill="#7FBA00"/>
                <rect x="1" y="11" width="9" height="9" fill="#00A4EF"/>
                <rect x="11" y="11" width="9" height="9" fill="#FFB900"/>
            </svg>
            Microsoft
        </a>
        <a href="<?= base_url('auth/social/apple') ?>" class="btn btn-social btn-apple">
            <i class="fa-brands fa-apple"></i>
            Apple
        </a>
    </div>
</div>

<div class="auth-divider"><span>or sign in with email</span></div>

<!-- Email/Password Form -->
<form action="<?= base_url('auth/login') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="form-group mb-3">
        <label class="form-label" for="email">Email Address</label>
        <div class="input-icon-wrap">
            <i class="fa-solid fa-envelope input-icon"></i>
            <input type="email" id="email" name="email" class="form-control form-control-app"
                   value="<?= old('email') ?>" placeholder="you@example.com" required autofocus>
        </div>
    </div>

    <div class="form-group mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <label class="form-label" for="password">Password</label>
            <a href="<?= base_url('auth/forgot-password') ?>" class="form-forgot-link">Forgot password?</a>
        </div>
        <div class="input-icon-wrap">
            <i class="fa-solid fa-lock input-icon"></i>
            <input type="password" id="password" name="password" class="form-control form-control-app"
                   placeholder="Enter your password" required>
            <button type="button" class="input-toggle-pw" onclick="togglePassword('password')">
                <i class="fa-solid fa-eye" id="password-eye"></i>
            </button>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-app btn-block w-100">
        <i class="fa-solid fa-right-to-bracket me-2"></i>
        Sign In
    </button>
</form>

<div class="auth-switch-link">
    Don't have an account? <a href="<?= base_url('auth/register') ?>">Create one free</a>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    const eye   = document.getElementById(id + '-eye');
    if (input.type === 'password') {
        input.type = 'text';
        eye.className = 'fa-solid fa-eye-slash';
    } else {
        input.type = 'password';
        eye.className = 'fa-solid fa-eye';
    }
}
</script>
<?= $this->endSection() ?>
