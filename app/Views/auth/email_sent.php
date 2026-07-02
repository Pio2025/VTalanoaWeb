<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<div class="auth-form-header">
    <div class="email-sent-icon">
        <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="24" cy="24" r="24" fill="rgba(28,117,188,.1)"/>
            <path d="M10 16a2 2 0 0 1 2-2h24a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H12a2 2 0 0 1-2-2V16z" stroke="#1c75bc" stroke-width="1.8" fill="none"/>
            <path d="M10 16l14 10 14-10" stroke="#1c75bc" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
    </div>
    <h1 class="auth-title">Check your inbox</h1>
    <p class="auth-subtitle">
        We've sent a password reset link to<br>
        <strong><?= esc($email ?? 'your email address') ?></strong>
    </p>
</div>

<div class="email-sent-steps">
    <div class="sent-step">
        <span class="sent-step-num">1</span>
        <span>Open the email from <strong>VTalanoa</strong></span>
    </div>
    <div class="sent-step">
        <span class="sent-step-num">2</span>
        <span>Click the <strong>Reset Password</strong> button</span>
    </div>
    <div class="sent-step">
        <span class="sent-step-num">3</span>
        <span>Choose a new password and sign in</span>
    </div>
</div>

<div class="email-sent-note">
    <svg viewBox="0 0 16 16" fill="none" width="15" height="15"><circle cx="8" cy="8" r="7" stroke="#1c75bc" stroke-width="1.4"/><path d="M8 7v4M8 5v.5" stroke="#1c75bc" stroke-width="1.4" stroke-linecap="round"/></svg>
    <span>The link expires in <strong>1 hour</strong>. Check your spam folder if you don't see it.</span>
</div>

<a href="<?= base_url('auth/forgot-password') ?>" class="btn btn-primary btn-app w-100 mt-3">
    <svg viewBox="0 0 20 20" fill="none" width="16" height="16" style="margin-right:6px"><path d="M3 10h14M3 10l5-5M3 10l5 5" stroke="#fff" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
    Resend reset link
</a>

<div class="auth-switch-link">
    Remembered your password? <a href="<?= base_url('auth/login') ?>">Back to Sign In</a>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<style>
.email-sent-icon {
    width: 72px; height: 72px;
    margin: 0 auto 16px;
    display: flex; align-items: center; justify-content: center;
}
.email-sent-icon svg { width: 72px; height: 72px; }
.email-sent-steps {
    display: flex; flex-direction: column; gap: 12px;
    background: #f8f9fc; border-radius: 12px;
    padding: 18px 20px; margin-bottom: 16px;
}
.sent-step {
    display: flex; align-items: center; gap: 12px;
    font-size: 13.5px; color: #475569;
}
.sent-step-num {
    width: 24px; height: 24px; border-radius: 50%;
    background: #1c75bc; color: #fff;
    font-size: 12px; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.email-sent-note {
    display: flex; align-items: flex-start; gap: 8px;
    font-size: 12.5px; color: #64748b; margin-bottom: 4px;
    background: rgba(28,117,188,.06); border-radius: 8px;
    padding: 10px 12px;
}
</style>
<?= $this->endSection() ?>
