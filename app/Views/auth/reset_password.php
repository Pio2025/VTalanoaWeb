<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<div class="auth-form-header">
    <h1 class="auth-title">Set new password</h1>
    <p class="auth-subtitle">Choose a strong password for your account</p>
</div>

<form action="<?= base_url('auth/reset-password/' . esc($token)) ?>" method="POST">
    <?= csrf_field() ?>

    <div class="form-group mb-3">
        <label class="form-label" for="password">New Password</label>
        <div class="input-icon-wrap">
            <i class="fa-solid fa-lock input-icon"></i>
            <input type="password" id="password" name="password" class="form-control form-control-app"
                   placeholder="Minimum 8 characters" required>
            <button type="button" class="input-toggle-pw" onclick="togglePassword('password')">
                <i class="fa-solid fa-eye" id="password-eye"></i>
            </button>
        </div>
    </div>

    <div class="form-group mb-4">
        <label class="form-label" for="password_confirm">Confirm Password</label>
        <div class="input-icon-wrap">
            <i class="fa-solid fa-lock input-icon"></i>
            <input type="password" id="password_confirm" name="password_confirm" class="form-control form-control-app"
                   placeholder="Repeat new password" required>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-app w-100">
        <i class="fa-solid fa-key me-2"></i>
        Update Password
    </button>
</form>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    const eye   = document.getElementById(id + '-eye');
    input.type  = input.type === 'password' ? 'text' : 'password';
    eye.className = input.type === 'password' ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
}
</script>
<?= $this->endSection() ?>
