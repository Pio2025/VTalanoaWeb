<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2 class="page-title">Schedule a Meeting</h2>
        <p class="page-subtitle">Set up a new meeting for your team</p>
    </div>
    <a href="<?= base_url('meetings') ?>" class="btn btn-ghost">
        <i class="fa-solid fa-arrow-left me-2"></i> Back
    </a>
</div>

<div class="form-page-wrap">
    <div class="card-app">
        <div class="card-app-body">
            <form id="scheduleForm" onsubmit="createMeeting(event)">
                <?= csrf_field() ?>

                <div class="form-section">
                    <h4 class="form-section-title">Meeting Details</h4>

                    <div class="mb-4">
                        <label class="form-label required">Meeting Title</label>
                        <input type="text" name="title" id="title" class="form-control form-control-app"
                               placeholder="e.g., Weekly Team Standup" required maxlength="200"
                               autocomplete="off">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Description / Agenda</label>
                        <textarea name="description" class="form-control form-control-app" rows="3"
                                  placeholder="Optional: describe the meeting agenda..."></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h4 class="form-section-title">Date & Time</h4>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label required">Start Date & Time</label>
                            <input type="datetime-local" name="scheduled_start" id="scheduled_start"
                                   class="form-control form-control-app" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">End Date & Time</label>
                            <input type="datetime-local" name="scheduled_end" id="scheduled_end"
                                   class="form-control form-control-app" required>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h4 class="form-section-title">Security & Options</h4>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Meeting Password</label>

                            <!-- Toggle row -->
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" id="pwToggle"
                                           onchange="handlePwToggle(this.checked)">
                                    <label class="form-check-label small text-muted" for="pwToggle">
                                        Require password
                                    </label>
                                </div>
                            </div>

                            <!-- Password field (hidden until toggle is on) -->
                            <div id="pwField" style="display:none;">
                                <div class="input-icon-wrap">
                                    <i class="fa-solid fa-lock input-icon"></i>
                                    <input type="text" name="password" id="meeting_password"
                                           class="form-control form-control-app"
                                           placeholder="Auto-generated or type your own"
                                           autocomplete="new-password" maxlength="50">
                                    <div class="d-flex" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);gap:4px;">
                                        <button type="button" class="input-toggle-pw" style="position:static;transform:none;"
                                                onclick="togglePwVisibility()" title="Show/hide">
                                            <i class="fa-solid fa-eye" id="pw-eye"></i>
                                        </button>
                                        <button type="button" class="input-toggle-pw" style="position:static;transform:none;"
                                                onclick="generatePassword()" title="Regenerate">
                                            <i class="fa-solid fa-rotate"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-text">10-character password auto-generated. You can edit it.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Max Participants</label>
                            <input type="number" name="max_participants" class="form-control form-control-app"
                                   value="300" min="2" max="500">
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="toggle-option">
                                <div class="toggle-option-text">
                                    <div class="toggle-label">Waiting Room</div>
                                    <div class="toggle-desc">Participants wait for host approval</div>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="waiting_room"
                                           id="waiting_room" value="1" checked>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="toggle-option">
                                <div class="toggle-option-text">
                                    <div class="toggle-label">Allow Recording</div>
                                    <div class="toggle-desc">Participants can record locally</div>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="allow_recording"
                                           id="allow_recording" value="1" checked>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="<?= base_url('meetings') ?>" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary btn-app" id="submitBtn">
                        <i class="fa-solid fa-calendar-plus me-2"></i> Schedule Meeting
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Set default times
const now = new Date();
now.setMinutes(now.getMinutes() + 5);
const end = new Date(now.getTime() + 30 * 60000);

const fmt = d => d.toISOString().slice(0,16);
document.getElementById('scheduled_start').value = fmt(now);
document.getElementById('scheduled_end').value   = fmt(end);

async function createMeeting(e) {
    e.preventDefault();
    const btn  = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Creating...';

    const form = new FormData(e.target);
    const data = {
        title:            form.get('title'),
        description:      form.get('description'),
        scheduled_start:  form.get('scheduled_start'),
        scheduled_end:    form.get('scheduled_end'),
        password:         form.get('password'),
        waiting_room:     form.get('waiting_room') ? 1 : 0,
        allow_recording:  form.get('allow_recording') ? 1 : 0,
        max_participants: parseInt(form.get('max_participants')) || 300,
    };

    try {
        const res  = await fetch('<?= base_url('api/meetings') ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
            body: JSON.stringify(data),
        });
        const json = await res.json();
        if (res.ok) {
            window.location.href = '<?= base_url('meetings/') ?>' + json.meeting_token;
        } else {
            showToast(json.errors ? Object.values(json.errors).join(', ') : 'Failed to create meeting.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-calendar-plus me-2"></i> Schedule Meeting';
        }
    } catch(err) {
        showToast('Network error. Please try again.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-calendar-plus me-2"></i> Schedule Meeting';
    }
}

function generatePassword() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
    let pw = '';
    // Guarantee at least one digit and one letter
    pw += '23456789'[Math.floor(Math.random() * 8)];
    pw += 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz'[Math.floor(Math.random() * 48)];
    for (let i = 2; i < 10; i++) {
        pw += chars[Math.floor(Math.random() * chars.length)];
    }
    // Shuffle
    pw = pw.split('').sort(() => Math.random() - 0.5).join('');
    document.getElementById('meeting_password').value = pw;
}

let pwVisible = true; // starts as text type

function handlePwToggle(on) {
    const field = document.getElementById('pwField');
    const input = document.getElementById('meeting_password');
    if (on) {
        field.style.display = 'block';
        generatePassword();
        input.type = 'text';
        pwVisible  = true;
        document.getElementById('pw-eye').className = 'fa-solid fa-eye-slash';
    } else {
        field.style.display = 'none';
        input.value = '';
    }
}

function togglePwVisibility() {
    const input = document.getElementById('meeting_password');
    const eye   = document.getElementById('pw-eye');
    pwVisible   = !pwVisible;
    input.type  = pwVisible ? 'text' : 'password';
    eye.className = pwVisible ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
}
</script>
<?= $this->endSection() ?>
