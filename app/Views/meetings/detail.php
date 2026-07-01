<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2 class="page-title"><?= esc($meeting['title']) ?></h2>
        <p class="page-subtitle">
            <span class="badge-status badge-<?= strtolower($meeting['status']) ?>"><?= esc($meeting['status']) ?></span>
        </p>
    </div>
    <div class="d-flex gap-2">
        <?php if ($meeting['status'] === 'Scheduled' || $meeting['status'] === 'Active'): ?>
            <button class="btn btn-primary btn-app" onclick="startMeeting('<?= esc($meeting['meeting_token']) ?>')">
                <i class="fa-solid fa-video me-2"></i>
                <?= $meeting['status'] === 'Active' ? 'Rejoin' : 'Start Meeting' ?>
            </button>
        <?php endif; ?>
        <a href="<?= base_url('meetings') ?>" class="btn btn-ghost">
            <i class="fa-solid fa-arrow-left me-2"></i> Back
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Details -->
    <div class="col-lg-8">
        <div class="card-app mb-4">
            <div class="card-app-header">
                <h3 class="card-app-title"><i class="fa-solid fa-info-circle me-2"></i>Meeting Details</h3>
                <?php if ($user['user_id'] == $meeting['host_user_id'] && $meeting['status'] === 'Scheduled'): ?>
                <a href="<?= base_url('meetings/' . $meeting['meeting_token'] . '/edit') ?>" class="btn btn-ghost btn-sm">
                    <i class="fa-solid fa-pen me-1"></i> Edit
                </a>
                <?php endif; ?>
            </div>
            <div class="card-app-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Meeting ID</div>
                        <div class="detail-value">
                            <code><?= esc($meeting['meeting_uuid']) ?></code>
                            <button class="btn-copy" onclick="copyText('<?= esc($meeting['meeting_uuid']) ?>')">
                                <i class="fa-solid fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Host</div>
                        <div class="detail-value"><?= esc($meeting['fname'] . ' ' . $meeting['lname']) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Date</div>
                        <div class="detail-value"><?= date('l, F j, Y', strtotime($meeting['scheduled_start'])) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Time</div>
                        <div class="detail-value">
                            <?= date('h:i A', strtotime($meeting['scheduled_start'])) ?> –
                            <?= date('h:i A', strtotime($meeting['scheduled_end'])) ?>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Waiting Room</div>
                        <div class="detail-value">
                            <?= $meeting['waiting_room'] ? '<span class="text-success"><i class="fa-solid fa-check me-1"></i>Enabled</span>' : '<span class="text-muted">Disabled</span>' ?>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Recording</div>
                        <div class="detail-value">
                            <?= $meeting['allow_recording'] ? '<span class="text-success"><i class="fa-solid fa-check me-1"></i>Allowed</span>' : '<span class="text-muted">Not allowed</span>' ?>
                        </div>
                    </div>
                    <?php if (!empty($meeting['description'])): ?>
                    <div class="detail-item full-width">
                        <div class="detail-label">Description</div>
                        <div class="detail-value"><?= nl2br(esc($meeting['description'])) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Invite Section -->
        <?php if ($user['user_id'] == $meeting['host_user_id']): ?>
        <div class="card-app">
            <div class="card-app-header">
                <h3 class="card-app-title"><i class="fa-solid fa-user-plus me-2"></i>Invite Participants</h3>
            </div>
            <div class="card-app-body">
                <div class="invite-form">
                    <div class="input-group mb-3">
                        <input type="email" id="inviteEmail" class="form-control form-control-app"
                               placeholder="Enter email addresses (comma separated)">
                        <button class="btn btn-primary btn-app" onclick="sendInvites()">
                            <i class="fa-solid fa-paper-plane me-2"></i>Send
                        </button>
                    </div>
                </div>

                <div class="invite-link-section">
                    <label class="form-label">Meeting Link</label>
                    <div class="input-group">
                        <input type="text" class="form-control form-control-app" readonly
                               value="<?= base_url('join/' . $meeting['meeting_token']) ?>" id="meetingLink">
                        <button class="btn btn-outline-app" onclick="copyLink()">
                            <i class="fa-solid fa-copy me-1"></i> Copy
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Quick Join -->
        <?php if ($meeting['status'] === 'Scheduled' || $meeting['status'] === 'Active'): ?>
        <div class="card-app mb-4 card-join">
            <div class="card-app-body text-center">
                <div class="join-icon">
                    <i class="fa-solid fa-video"></i>
                </div>
                <h4>Ready to join?</h4>
                <p class="text-muted mb-3">Click below to enter the meeting room</p>
                <button class="btn btn-primary btn-app w-100" onclick="startMeeting('<?= esc($meeting['meeting_token']) ?>')">
                    <i class="fa-solid fa-video me-2"></i>
                    <?= $meeting['status'] === 'Active' ? 'Rejoin Meeting' : 'Start Meeting' ?>
                </button>
                <?php if ($user['user_id'] != $meeting['host_user_id']): ?>
                <a href="<?= base_url('join/' . $meeting['meeting_token']) ?>" class="btn btn-outline-app w-100 mt-2">
                    Join as Participant
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Actions (host only) -->
        <?php if ($user['user_id'] == $meeting['host_user_id'] && $meeting['status'] === 'Scheduled'): ?>
        <div class="card-app">
            <div class="card-app-header">
                <h3 class="card-app-title">Actions</h3>
            </div>
            <div class="card-app-body d-grid gap-2">
                <a href="<?= base_url('meetings/' . $meeting['meeting_token'] . '/edit') ?>"
                   class="btn btn-outline-app">
                    <i class="fa-solid fa-pen me-2"></i>Edit Meeting
                </a>
                <button class="btn btn-outline-danger"
                        onclick="cancelMeeting('<?= esc($meeting['meeting_token']) ?>')">
                    <i class="fa-solid fa-xmark me-2"></i>Cancel Meeting
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
async function startMeeting(token) {
    const res  = await fetch(`<?= base_url('api/meetings/') ?>${token}/start`, {
        method: 'POST', headers: {'X-Requested-With': 'XMLHttpRequest'}
    });
    const data = await res.json();
    if (data.room_url) window.location.href = data.room_url;
}

async function cancelMeeting(token) {
    const result = await Swal.fire({
        title: 'Cancel Meeting?',
        text: 'This meeting will be cancelled. This cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, cancel it',
        cancelButtonText: 'Keep it',
        reverseButtons: true,
    });
    if (!result.isConfirmed) return;
    const res = await fetch(`<?= base_url('api/meetings/') ?>${token}`, {
        method: 'DELETE', headers: {'X-Requested-With': 'XMLHttpRequest'}
    });
    if (res.ok) {
        await Swal.fire({ title: 'Cancelled', text: 'The meeting has been cancelled.', icon: 'success', timer: 1500, showConfirmButton: false });
        window.location.href = '<?= base_url('meetings') ?>';
    }
}

async function sendInvites() {
    const raw    = document.getElementById('inviteEmail').value;
    const emails = raw.split(',').map(e => e.trim()).filter(Boolean);
    if (!emails.length) return;

    const res = await fetch(`<?= base_url('api/meetings/' . $meeting['meeting_token'] . '/invite') ?>`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
        body: JSON.stringify({ emails }),
    });
    const data = await res.json();
    showToast(`Invitations sent to ${data.sent_to?.length ?? 0} address(es)`, 'success');
    document.getElementById('inviteEmail').value = '';
}

function copyLink() {
    const link = document.getElementById('meetingLink').value;
    navigator.clipboard.writeText(link).then(() => showToast('Link copied!', 'success'));
}

function copyText(text) {
    navigator.clipboard.writeText(text).then(() => showToast('Copied!', 'success'));
}
</script>
<?= $this->endSection() ?>
