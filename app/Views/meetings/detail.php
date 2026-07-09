<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $isHost = $user['user_id'] == $meeting['host_user_id']; ?>

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

<?php if ($isHost): ?>
<!-- Stats -->
<div class="stats-row mb-4" id="statsRow">
    <div class="stat-card">
        <div class="stat-icon stat-blue"><i class="fa-solid fa-users"></i></div>
        <div>
            <div class="stat-value" id="statParticipants">—</div>
            <div class="stat-label">Participants</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-green"><i class="fa-solid fa-clock"></i></div>
        <div>
            <div class="stat-value" id="statDuration">—</div>
            <div class="stat-label">Duration</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-purple"><i class="fa-solid fa-hourglass-half"></i></div>
        <div>
            <div class="stat-value" id="statAvgAttendance">—</div>
            <div class="stat-label">Avg. Attendance</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-orange"><i class="fa-solid fa-comments"></i></div>
        <div>
            <div class="stat-value" id="statMessages">—</div>
            <div class="stat-label">Messages</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon stat-blue"><i class="fa-solid fa-paperclip"></i></div>
        <div>
            <div class="stat-value" id="statFiles">—</div>
            <div class="stat-label">Files Shared</div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- Main -->
    <div class="col-lg-8">
        <div class="card-app mb-4">
            <div class="card-app-header">
                <h3 class="card-app-title"><i class="fa-solid fa-info-circle me-2"></i>Meeting Details</h3>
                <?php if ($isHost && $meeting['status'] === 'Scheduled'): ?>
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

        <?php if ($isHost): ?>
        <!-- Participants -->
        <div class="card-app mb-4">
            <div class="card-app-header">
                <h3 class="card-app-title"><i class="fa-solid fa-users me-2"></i>Participants</h3>
                <span class="badge-status badge-scheduled" id="participantsTotalBadge">0 total</span>
            </div>
            <div class="card-app-body p-0">
                <div class="table-responsive">
                    <table class="table table-app mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Left</th>
                            </tr>
                        </thead>
                        <tbody id="participantsTbody">
                            <tr><td colspan="5" class="empty-inline">Loading…</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="table-pagination">
                    <button class="btn btn-ghost btn-sm" id="participantsPrev" disabled>
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <span id="participantsPageLabel" class="mx-3 text-muted small">Page 1</span>
                    <button class="btn btn-ghost btn-sm" id="participantsNext" disabled>
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Chat -->
        <div class="card-app mb-4">
            <div class="card-app-header">
                <h3 class="card-app-title"><i class="fa-solid fa-comments me-2"></i>Chat Messages</h3>
            </div>
            <div class="card-app-body">
                <div class="chat-list" id="chatList">
                    <div class="empty-inline">Loading…</div>
                </div>
            </div>
        </div>

        <!-- Files -->
        <div class="card-app">
            <div class="card-app-header">
                <h3 class="card-app-title"><i class="fa-solid fa-paperclip me-2"></i>Files &amp; Resources</h3>
            </div>
            <div class="card-app-body">
                <div class="file-list" id="fileList">
                    <div class="empty-inline">Loading…</div>
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
                <?php if (!$isHost): ?>
                <a href="<?= base_url('join/' . $meeting['meeting_token']) ?>" class="btn btn-outline-app w-100 mt-2">
                    Join as Participant
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Invite Section -->
        <?php if ($isHost): ?>
        <div class="card-app mb-4">
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

        <!-- Actions (host only) -->
        <?php if ($isHost && $meeting['status'] === 'Scheduled'): ?>
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

<?php if ($isHost): ?>
// ── Meeting analytics / participants / chat / files ─────────────────────
const MEETING_TOKEN = '<?= esc($meeting['meeting_token']) ?>';
const BASE_URL      = '<?= base_url() ?>';

function escapeHtml(str) {
    return String(str ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function formatDuration(seconds) {
    if (seconds === null || seconds === undefined) return '—';
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    if (h > 0) return `${h}h ${m}m`;
    if (m > 0) return `${m}m`;
    return `${seconds}s`;
}

function formatFileSize(bytes) {
    if (!bytes) return '0 KB';
    const kb = bytes / 1024;
    if (kb < 1024) return `${kb.toFixed(1)} KB`;
    return `${(kb / 1024).toFixed(1)} MB`;
}

function formatWhen(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr.replace(' ', 'T'));
    if (isNaN(d.getTime())) return '—';
    return d.toLocaleString(undefined, { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
}

function displayName(row) {
    const full = `${row.fname ?? ''} ${row.lname ?? ''}`.trim();
    return full || row.guest_name || 'Guest';
}

function initials(name) {
    return name.split(' ').map(s => s[0]).filter(Boolean).slice(0, 2).join('').toUpperCase();
}

function statusBadgeClass(status) {
    switch (status) {
        case 'Admitted': return 'active';
        case 'Waiting':  return 'scheduled';
        case 'Removed':  return 'cancelled';
        default:         return 'ended'; // Left
    }
}

async function loadStats() {
    try {
        const res = await fetch(`${BASE_URL}api/meetings/${MEETING_TOKEN}/stats`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!res.ok) return;
        const { data } = await res.json();
        document.getElementById('statParticipants').textContent   = data.total_participants ?? 0;
        document.getElementById('statDuration').textContent       = formatDuration(data.duration_seconds);
        document.getElementById('statAvgAttendance').textContent  = formatDuration(data.avg_attendance_seconds);
    } catch (e) { /* stats are supplementary; leave placeholders on failure */ }
}

let participantsPage     = 1;
let participantsHasMore  = false;

async function loadParticipants(page = 1) {
    const tbody = document.getElementById('participantsTbody');
    tbody.innerHTML = '<tr><td colspan="5" class="empty-inline">Loading…</td></tr>';
    try {
        const res = await fetch(`${BASE_URL}api/meetings/${MEETING_TOKEN}/participants?page=${page}&per_page=10`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!res.ok) throw new Error('request failed');
        const json = await res.json();

        participantsPage    = json.page;
        participantsHasMore  = json.has_more;
        document.getElementById('participantsTotalBadge').textContent = `${json.total} total`;
        document.getElementById('participantsPageLabel').textContent  = `Page ${json.page}`;
        document.getElementById('participantsPrev').disabled = json.page <= 1;
        document.getElementById('participantsNext').disabled = !json.has_more;

        if (!json.data.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="empty-inline">No participants yet.</td></tr>';
            return;
        }

        tbody.innerHTML = json.data.map(p => {
            const name   = displayName(p);
            const avatar = p.profile_photo
                ? `<img src="${BASE_URL}${escapeHtml(p.profile_photo)}" alt="">`
                : escapeHtml(initials(name));
            return `<tr>
                <td>
                    <div class="participant-name-cell">
                        <div class="participant-avatar">${avatar}</div>
                        <span>${escapeHtml(name)}</span>
                    </div>
                </td>
                <td>${escapeHtml(p.role || 'Participant')}</td>
                <td><span class="badge-status badge-${statusBadgeClass(p.status)}">${escapeHtml(p.status)}</span></td>
                <td>${formatWhen(p.joined_at)}</td>
                <td>${formatWhen(p.left_at)}</td>
            </tr>`;
        }).join('');
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="5" class="empty-inline">Unable to load participants.</td></tr>';
    }
}

document.getElementById('participantsPrev').addEventListener('click', () => {
    if (participantsPage > 1) loadParticipants(participantsPage - 1);
});
document.getElementById('participantsNext').addEventListener('click', () => {
    if (participantsHasMore) loadParticipants(participantsPage + 1);
});

async function loadChat() {
    const list = document.getElementById('chatList');
    try {
        const res = await fetch(`${BASE_URL}api/chat/${MEETING_TOKEN}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!res.ok) throw new Error('request failed');
        const { data } = await res.json();
        document.getElementById('statMessages').textContent = data.length;

        if (!data.length) {
            list.innerHTML = '<div class="empty-inline">No chat messages yet.</div>';
            return;
        }

        list.innerHTML = data.map(m => {
            const name       = displayName(m);
            const text       = m.message ? `<div class="chat-msg-text">${escapeHtml(m.message)}</div>` : '';
            const attachment = m.file_url
                ? `<a class="chat-msg-attachment" href="${escapeHtml(m.file_url)}" target="_blank" rel="noopener">
                       <i class="fa-solid fa-paperclip"></i>${escapeHtml(m.file_name || 'Attachment')}
                   </a>`
                : '';
            return `<div class="chat-msg">
                <div class="chat-msg-avatar">${escapeHtml(initials(name))}</div>
                <div class="chat-msg-body">
                    <div class="chat-msg-head">
                        <span class="chat-msg-name">${escapeHtml(name)}</span>
                        <span class="chat-msg-time">${formatWhen(m.sent_at)}</span>
                    </div>
                    ${text}${attachment}
                </div>
            </div>`;
        }).join('');
        list.scrollTop = list.scrollHeight;
    } catch (e) {
        list.innerHTML = '<div class="empty-inline">Unable to load chat messages.</div>';
    }
}

function fileIcon(mime) {
    if (!mime) return 'fa-file';
    if (mime.startsWith('image/')) return 'fa-file-image';
    if (mime === 'application/pdf') return 'fa-file-pdf';
    if (mime.includes('word')) return 'fa-file-word';
    if (mime.includes('powerpoint') || mime.includes('presentation')) return 'fa-file-powerpoint';
    if (mime.includes('excel') || mime.includes('sheet')) return 'fa-file-excel';
    return 'fa-file';
}

async function loadFiles() {
    const list = document.getElementById('fileList');
    try {
        const res = await fetch(`${BASE_URL}api/meetings/${MEETING_TOKEN}/files`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!res.ok) throw new Error('request failed');
        const { data } = await res.json();
        document.getElementById('statFiles').textContent = data.length;

        if (!data.length) {
            list.innerHTML = '<div class="empty-inline">No files shared yet.</div>';
            return;
        }

        list.innerHTML = data.map(f => `
            <a class="file-row" href="${escapeHtml(f.file_url)}" target="_blank" rel="noopener">
                <div class="file-icon"><i class="fa-solid ${fileIcon(f.mime_type)}"></i></div>
                <div class="file-meta">
                    <div class="file-name">${escapeHtml(f.file_name)}</div>
                    <div class="file-sub">${formatFileSize(f.file_size)} · ${formatWhen(f.created_at)}</div>
                </div>
            </a>
        `).join('');
    } catch (e) {
        list.innerHTML = '<div class="empty-inline">Unable to load files.</div>';
    }
}

loadStats();
loadParticipants(1);
loadChat();
loadFiles();
<?php endif; ?>
</script>
<?= $this->endSection() ?>
