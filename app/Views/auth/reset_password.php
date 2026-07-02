<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<div class="auth-form-header">
    <h1 class="auth-title">Set new password</h1>
    <p class="auth-subtitle">Choose a strong password for your account</p>
</div>

<form action="<?= base_url('auth/reset-password/' . esc($token)) ?>" method="POST" id="resetForm">
    <?= csrf_field() ?>

    <div class="form-group mb-3">
        <label class="form-label" for="password">New Password</label>
        <div class="input-icon-wrap">
            <i class="fa-solid fa-lock input-icon"></i>
            <input type="password" id="password" name="password"
                   class="form-control form-control-app"
                   placeholder="Minimum 8 characters"
                   minlength="8" required
                   oninput="checkStrength(this.value)">
            <button type="button" class="input-toggle-pw" onclick="togglePassword('password')">
                <i class="fa-solid fa-eye" id="password-eye"></i>
            </button>
        </div>
        <!-- Password strength bar -->
        <div class="pw-strength-wrap" id="pwStrengthWrap" style="display:none;margin-top:8px">
            <div class="pw-strength-bar"><div class="pw-strength-fill" id="pwStrengthFill"></div></div>
            <span class="pw-strength-label" id="pwStrengthLabel"></span>
        </div>
    </div>

    <div class="form-group mb-4">
        <label class="form-label" for="password_confirm">Confirm Password</label>
        <div class="input-icon-wrap">
            <i class="fa-solid fa-lock input-icon"></i>
            <input type="password" id="password_confirm" name="password_confirm"
                   class="form-control form-control-app"
                   placeholder="Repeat new password" required
                   oninput="checkMatch()">
            <button type="button" class="input-toggle-pw" onclick="togglePassword('password_confirm')">
                <i class="fa-solid fa-eye" id="password_confirm-eye"></i>
            </button>
        </div>
        <div class="pw-match-msg" id="pwMatchMsg" style="display:none;font-size:12px;margin-top:6px"></div>
    </div>

    <button type="submit" class="btn btn-primary btn-app w-100" id="submitBtn">
        <i class="fa-solid fa-key me-2"></i>
        Update Password
    </button>
</form>

<div class="auth-switch-link" style="margin-top:16px">
    <a href="<?= base_url('auth/login') ?>">
        <i class="fa-solid fa-arrow-left me-1" style="font-size:11px"></i>Back to Sign In
    </a>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<style>
.pw-strength-bar { height: 4px; background: #e2e8f0; border-radius: 2px; overflow: hidden; margin-bottom: 4px; }
.pw-strength-fill { height: 100%; border-radius: 2px; transition: width .3s ease, background .3s ease; }
.pw-strength-label { font-size: 11.5px; font-weight: 600; }
</style>
<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    const eye   = document.getElementById(id + '-eye');
    input.type  = input.type === 'password' ? 'text' : 'password';
    eye.className = input.type === 'password' ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
}

function checkStrength(val) {
    const wrap  = document.getElementById('pwStrengthWrap');
    const fill  = document.getElementById('pwStrengthFill');
    const label = document.getElementById('pwStrengthLabel');
    if (!val) { wrap.style.display = 'none'; return; }
    wrap.style.display = 'block';

    let score = 0;
    if (val.length >= 8)  score++;
    if (val.length >= 12) score++;
    if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
    if (/\d/.test(val))   score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const levels = [
        { pct: '20%',  bg: '#ef4444', text: 'Very weak',  color: '#ef4444' },
        { pct: '40%',  bg: '#f97316', text: 'Weak',       color: '#f97316' },
        { pct: '60%',  bg: '#eab308', text: 'Fair',       color: '#eab308' },
        { pct: '80%',  bg: '#22c55e', text: 'Strong',     color: '#22c55e' },
        { pct: '100%', bg: '#16a34a', text: 'Very strong',color: '#16a34a' },
    ];
    const l = levels[Math.min(score - 1, 4)] || levels[0];
    fill.style.width      = l.pct;
    fill.style.background = l.bg;
    label.textContent     = l.text;
    label.style.color     = l.color;
}

function checkMatch() {
    const pw      = document.getElementById('password').value;
    const confirm = document.getElementById('password_confirm').value;
    const msg     = document.getElementById('pwMatchMsg');
    if (!confirm) { msg.style.display = 'none'; return; }
    msg.style.display = 'block';
    if (pw === confirm) {
        msg.textContent  = '✓ Passwords match';
        msg.style.color  = '#16a34a';
    } else {
        msg.textContent  = '✗ Passwords do not match';
        msg.style.color  = '#ef4444';
    }
}

document.getElementById('resetForm').addEventListener('submit', function(e) {
    const pw      = document.getElementById('password').value;
    const confirm = document.getElementById('password_confirm').value;
    if (pw !== confirm) {
        e.preventDefault();
        document.getElementById('pwMatchMsg').textContent = '✗ Passwords do not match';
        document.getElementById('pwMatchMsg').style.color = '#ef4444';
        document.getElementById('pwMatchMsg').style.display = 'block';
    }
});
</script>
<?= $this->endSection() ?>
