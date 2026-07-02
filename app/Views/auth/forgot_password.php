<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<div class="auth-form-header">
    <h1 class="auth-title">Reset your password</h1>
    <p class="auth-subtitle">Enter your email and we'll send you a reset link</p>
</div>

<form action="<?= base_url('auth/forgot-password') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="form-group mb-4">
        <label class="form-label" for="email">Email Address</label>
        <div class="input-icon-wrap">
            <i class="fa-solid fa-envelope input-icon"></i>
            <input type="email" id="email" name="email" class="form-control form-control-app"
                   placeholder="you@example.com" value="<?= old('email') ?>" required autofocus>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-app w-100">
        <i class="fa-solid fa-paper-plane me-2"></i>
        Send Reset Link
    </button>
</form>

<div class="auth-switch-link">
    Remembered it? <a href="<?= base_url('auth/login') ?>">Back to Sign In</a>
</div>

<?= $this->endSection() ?>
