<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2 class="page-title">My Profile</h2>
        <p class="page-subtitle">Manage your account settings</p>
    </div>
</div>

<div class="form-page-wrap">
    <div class="row g-4">
        <!-- Profile Form -->
        <div class="col-lg-8">
            <div class="card-app">
                <div class="card-app-header">
                    <h3 class="card-app-title"><i class="fa-solid fa-user-circle me-2"></i>Personal Information</h3>
                </div>
                <div class="card-app-body">
                    <form action="<?= base_url('profile') ?>" method="POST" enctype="multipart/form-data">
                        <?= csrf_field() ?>

                        <!-- Avatar Upload -->
                        <div class="profile-avatar-upload mb-4">
                            <div class="avatar-preview" id="avatarPreview">
                                <?php if (!empty($user['profile_photo'])): ?>
                                    <img src="<?= base_url($user['profile_photo']) ?>" id="avatarImg" alt="Avatar">
                                <?php else: ?>
                                    <div class="avatar-initials" id="avatarInitials">
                                        <?= strtoupper(substr($user['fname'],0,1) . substr($user['lname'],0,1)) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="avatar-upload-actions">
                                <label for="profile_photo" class="btn btn-outline-app btn-sm">
                                    <i class="fa-solid fa-camera me-2"></i>Change Photo
                                </label>
                                <input type="file" id="profile_photo" name="profile_photo"
                                       accept="image/*" class="d-none" onchange="previewAvatar(this)">
                                <p class="form-text mt-1">JPG, PNG or GIF. Max 2MB.</p>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name</label>
                                <input type="text" name="fname" class="form-control form-control-app"
                                       value="<?= esc($user['fname']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="lname" class="form-control form-control-app"
                                       value="<?= esc($user['lname']) ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control form-control-app"
                                   value="<?= esc($user['email']) ?>" readonly disabled>
                            <div class="form-text">Email cannot be changed.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control form-control-app"
                                   value="<?= esc($user['username']) ?>" readonly disabled>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Timezone</label>
                            <select name="timezone" class="form-select form-control-app">
                                <?php
                                $zones = \DateTimeZone::listIdentifiers();
                                foreach ($zones as $tz):
                                ?>
                                <option value="<?= esc($tz) ?>" <?= $user['timezone'] === $tz ? 'selected' : '' ?>>
                                    <?= esc($tz) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3">Change Password</h5>
                        <p class="text-muted mb-3">Leave blank to keep your current password.</p>

                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <div class="input-icon-wrap">
                                <i class="fa-solid fa-lock input-icon"></i>
                                <input type="password" name="new_password" id="new_password"
                                       class="form-control form-control-app" placeholder="Minimum 8 characters">
                                <button type="button" class="input-toggle-pw" onclick="togglePassword('new_password')">
                                    <i class="fa-solid fa-eye" id="new_password-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Confirm New Password</label>
                            <div class="input-icon-wrap">
                                <i class="fa-solid fa-lock input-icon"></i>
                                <input type="password" name="confirm_password"
                                       class="form-control form-control-app" placeholder="Repeat new password">
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-app">
                                <i class="fa-solid fa-floppy-disk me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Account Info Sidebar -->
        <div class="col-lg-4">
            <div class="card-app mb-4">
                <div class="card-app-header">
                    <h3 class="card-app-title"><i class="fa-solid fa-shield me-2"></i>Account</h3>
                </div>
                <div class="card-app-body">
                    <div class="account-info-list">
                        <div class="account-info-item">
                            <span class="info-label">Status</span>
                            <span class="badge bg-success"><?= esc($user['user_status']) ?></span>
                        </div>
                        <div class="account-info-item">
                            <span class="info-label">Auth Type</span>
                            <span class="badge bg-info"><?= esc($user['auth_type']) ?></span>
                        </div>
                        <div class="account-info-item">
                            <span class="info-label">Joined</span>
                            <span><?= date('M j, Y', strtotime($user['created_at'])) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-app">
                <div class="card-app-header">
                    <h3 class="card-app-title"><i class="fa-solid fa-link me-2"></i>Linked Accounts</h3>
                </div>
                <div class="card-app-body">
                    <div class="linked-accounts">
                        <div class="linked-account-item">
                            <div class="la-icon la-google"><i class="fa-brands fa-google"></i></div>
                            <span>Google</span>
                            <a href="<?= base_url('auth/social/google') ?>" class="btn btn-xs btn-outline-app ms-auto">Connect</a>
                        </div>
                        <div class="linked-account-item">
                            <div class="la-icon la-facebook"><i class="fa-brands fa-facebook-f"></i></div>
                            <span>Facebook</span>
                            <a href="<?= base_url('auth/social/facebook') ?>" class="btn btn-xs btn-outline-app ms-auto">Connect</a>
                        </div>
                        <div class="linked-account-item">
                            <div class="la-icon la-microsoft"><i class="fa-brands fa-windows"></i></div>
                            <span>Microsoft</span>
                            <a href="<?= base_url('auth/social/microsoft') ?>" class="btn btn-xs btn-outline-app ms-auto">Connect</a>
                        </div>
                        <div class="linked-account-item">
                            <div class="la-icon la-apple"><i class="fa-brands fa-apple"></i></div>
                            <span>Apple</span>
                            <a href="<?= base_url('auth/social/apple') ?>" class="btn btn-xs btn-outline-app ms-auto">Connect</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            let img = document.getElementById('avatarImg');
            if (!img) {
                document.getElementById('avatarInitials').style.display = 'none';
                img = document.createElement('img');
                img.id = 'avatarImg';
                document.getElementById('avatarPreview').appendChild(img);
            }
            img.src = e.target.result;
            img.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function togglePassword(id) {
    const input = document.getElementById(id);
    const eye   = document.getElementById(id + '-eye');
    input.type  = input.type === 'password' ? 'text' : 'password';
    eye.className = input.type === 'password' ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
}
</script>
<?= $this->endSection() ?>
