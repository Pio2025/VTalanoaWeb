<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<!-- Welcome Banner -->
<div class="welcome-banner">
    <div class="welcome-text">
        <h2>Good <?= date('H') < 12 ? 'morning' : (date('H') < 17 ? 'afternoon' : 'evening') ?>, <?= esc($user['fname']) ?>!</h2>
        <p>Ready to connect? Start or join a meeting below.</p>
    </div>
    <div class="welcome-actions">
        <a href="<?= base_url('meetings/schedule') ?>" class="btn btn-primary btn-app">
            <i class="fa-solid fa-plus me-2"></i> New Meeting
        </a>
        <button class="btn btn-outline-app ms-2" data-bs-toggle="modal" data-bs-target="#joinModal">
            <i class="fa-solid fa-arrow-right-to-bracket me-2"></i> Join Meeting
        </button>
    </div>
</div>

<!-- Quick Stats -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-primary">
            <div class="stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
            <div class="stat-content">
                <div class="stat-number"><?= $total ?></div>
                <div class="stat-label">Total Meetings</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-success">
            <div class="stat-icon"><i class="fa-solid fa-clock"></i></div>
            <div class="stat-content">
                <div class="stat-number"><?= count($upcoming) ?></div>
                <div class="stat-label">Upcoming</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-info">
            <div class="stat-icon"><i class="fa-solid fa-circle-play"></i></div>
            <div class="stat-content">
                <div class="stat-number"><?= count($recent) ?></div>
                <div class="stat-label">Recent</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-warning">
            <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
            <div class="stat-content">
                <div class="stat-number">P2P</div>
                <div class="stat-label">Connection Type</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Upcoming Meetings -->
    <div class="col-lg-7">
        <div class="card-app">
            <div class="card-app-header">
                <h3 class="card-app-title">
                    <i class="fa-solid fa-calendar-days me-2"></i>Upcoming Meetings
                </h3>
                <a href="<?= base_url('meetings') ?>" class="btn-link-app">View all</a>
            </div>
            <div class="card-app-body p-0">
                <?php if (empty($upcoming)): ?>
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fa-solid fa-calendar-xmark"></i></div>
                        <div class="empty-text">No upcoming meetings</div>
                        <a href="<?= base_url('meetings/schedule') ?>" class="btn btn-primary btn-sm mt-3">Schedule one now</a>
                    </div>
                <?php else: ?>
                    <ul class="meeting-list">
                        <?php foreach ($upcoming as $m): ?>
                        <li class="meeting-list-item">
                            <div class="meeting-date-badge">
                                <span class="badge-day"><?= date('d', strtotime($m['scheduled_start'])) ?></span>
                                <span class="badge-mon"><?= date('M', strtotime($m['scheduled_start'])) ?></span>
                            </div>
                            <div class="meeting-info">
                                <div class="meeting-title"><?= esc($m['title']) ?></div>
                                <div class="meeting-meta">
                                    <i class="fa-solid fa-clock me-1"></i>
                                    <?= date('h:i A', strtotime($m['scheduled_start'])) ?> –
                                    <?= date('h:i A', strtotime($m['scheduled_end'])) ?>
                                </div>
                            </div>
                            <div class="meeting-actions">
                                <span class="badge-status badge-scheduled">Scheduled</span>
                                <a href="<?= base_url('meetings/' . $m['meeting_token']) ?>" class="btn-icon-app" title="Details">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </a>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions + Recent -->
    <div class="col-lg-5">
        <!-- Quick Start -->
        <div class="card-app mb-4">
            <div class="card-app-header">
                <h3 class="card-app-title">
                    <i class="fa-solid fa-bolt me-2"></i>Quick Start
                </h3>
            </div>
            <div class="card-app-body">
                <div class="quick-actions">
                    <a href="<?= base_url('meetings/schedule') ?>" class="quick-action-card">
                        <div class="qa-icon qa-blue"><i class="fa-solid fa-video"></i></div>
                        <div class="qa-label">New Meeting</div>
                    </a>
                    <button class="quick-action-card" data-bs-toggle="modal" data-bs-target="#joinModal">
                        <div class="qa-icon qa-green"><i class="fa-solid fa-arrow-right-to-bracket"></i></div>
                        <div class="qa-label">Join Meeting</div>
                    </button>
                    <a href="<?= base_url('meetings/schedule') ?>" class="quick-action-card">
                        <div class="qa-icon qa-purple"><i class="fa-solid fa-calendar-plus"></i></div>
                        <div class="qa-label">Schedule</div>
                    </a>
                    <a href="<?= base_url('profile') ?>" class="quick-action-card">
                        <div class="qa-icon qa-orange"><i class="fa-solid fa-gear"></i></div>
                        <div class="qa-label">Settings</div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card-app">
            <div class="card-app-header">
                <h3 class="card-app-title">
                    <i class="fa-solid fa-history me-2"></i>Recent Activity
                </h3>
            </div>
            <div class="card-app-body p-0">
                <?php if (empty($recent)): ?>
                    <div class="empty-state py-4">
                        <div class="empty-text">No recent meetings</div>
                    </div>
                <?php else: ?>
                    <ul class="meeting-list">
                        <?php foreach ($recent as $m): ?>
                        <li class="meeting-list-item">
                            <div class="meeting-info">
                                <div class="meeting-title"><?= esc($m['title']) ?></div>
                                <div class="meeting-meta">
                                    <?= date('M d, Y', strtotime($m['scheduled_start'])) ?>
                                </div>
                            </div>
                            <div class="meeting-actions">
                                <span class="badge-status badge-<?= strtolower($m['status']) ?>"><?= esc($m['status']) ?></span>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Join Meeting Modal -->
<div class="modal fade" id="joinModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-app">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-arrow-right-to-bracket me-2"></i>Join a Meeting
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Meeting ID or Link</label>
                    <input type="text" id="joinMeetingId" class="form-control form-control-app"
                           placeholder="Enter meeting ID or paste invite link">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password (if required)</label>
                    <input type="password" id="joinPassword" class="form-control form-control-app"
                           placeholder="Meeting password">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-app" onclick="joinMeeting()">
                    <i class="fa-solid fa-arrow-right me-2"></i>Join
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
async function joinMeeting() {
    let input = document.getElementById('joinMeetingId').value.trim();
    if (!input) return;

    // Extract identifier from a pasted join/room URL
    let identifier = input;
    const urlMatch = input.match(/\/join\/([^/?#\s]+)/i) || input.match(/\/room\/([^/?#\s]+)/i);
    if (urlMatch) identifier = urlMatch[1];

    // 10-digit numeric Meeting ID → resolve to token first
    if (/^\d{10}$/.test(identifier)) {
        try {
            const res  = await fetch('<?= base_url('api/meetings/resolve/') ?>' + identifier, {
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            });
            const data = await res.json();
            if (data.join_url) {
                window.location.href = data.join_url;
                return;
            }
        } catch(e) {}
        showToast('Meeting not found. Please check the Meeting ID.', 'error');
        return;
    }

    // UUID token or other value → go directly to join page
    window.location.href = '<?= base_url('join/') ?>' + identifier;
}

document.getElementById('joinMeetingId')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') joinMeeting();
});
</script>
<?= $this->endSection() ?>
