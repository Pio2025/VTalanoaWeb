<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2 class="page-title">My Meetings</h2>
        <p class="page-subtitle">Manage your scheduled and past meetings</p>
    </div>
    <a href="<?= base_url('meetings/schedule') ?>" class="btn btn-primary btn-app">
        <i class="fa-solid fa-plus me-2"></i> New Meeting
    </a>
</div>

<!-- Filter Tabs -->
<div class="filter-tabs mb-4">
    <button class="filter-tab active" data-filter="all">All</button>
    <button class="filter-tab" data-filter="Scheduled">Upcoming</button>
    <button class="filter-tab" data-filter="Active">Active</button>
    <button class="filter-tab" data-filter="Ended">Past</button>
    <button class="filter-tab" data-filter="Cancelled">Cancelled</button>
</div>

<?php if (empty($meetings)): ?>
    <div class="empty-page-state">
        <div class="empty-page-icon"><i class="fa-solid fa-calendar-xmark"></i></div>
        <h3>No meetings yet</h3>
        <p>Schedule your first meeting and start connecting.</p>
        <a href="<?= base_url('meetings/schedule') ?>" class="btn btn-primary btn-app">
            <i class="fa-solid fa-plus me-2"></i> Schedule a Meeting
        </a>
    </div>
<?php else: ?>
    <div class="meetings-grid" id="meetingsGrid">
        <?php foreach ($meetings as $m): ?>
        <div class="meeting-card" data-status="<?= esc($m['status']) ?>">
            <div class="meeting-card-header">
                <div class="meeting-card-status">
                    <span class="badge-status badge-<?= strtolower($m['status']) ?>"><?= esc($m['status']) ?></span>
                </div>
                <div class="meeting-card-menu dropdown">
                    <button class="btn-icon-app" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= base_url('meetings/' . $m['meeting_token']) ?>">
                            <i class="fa-solid fa-eye me-2"></i>View Details
                        </a></li>
                        <?php if ($m['status'] === 'Scheduled'): ?>
                        <li><a class="dropdown-item" href="<?= base_url('meetings/' . $m['meeting_token'] . '/edit') ?>">
                            <i class="fa-solid fa-pen me-2"></i>Edit
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#"
                               onclick="cancelMeeting('<?= esc($m['meeting_token']) ?>')">
                            <i class="fa-solid fa-trash me-2"></i>Cancel
                        </a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <div class="meeting-card-body">
                <h4 class="meeting-card-title"><?= esc($m['title']) ?></h4>
                <?php if (!empty($m['description'])): ?>
                    <p class="meeting-card-desc"><?= esc(substr($m['description'], 0, 80)) ?>...</p>
                <?php endif; ?>

                <div class="meeting-card-meta">
                    <div class="meta-item">
                        <i class="fa-solid fa-calendar"></i>
                        <?= date('D, M d Y', strtotime($m['scheduled_start'])) ?>
                    </div>
                    <div class="meta-item">
                        <i class="fa-solid fa-clock"></i>
                        <?= date('h:i A', strtotime($m['scheduled_start'])) ?> –
                        <?= date('h:i A', strtotime($m['scheduled_end'])) ?>
                    </div>
                    <?php if ($m['waiting_room']): ?>
                    <div class="meta-item">
                        <i class="fa-solid fa-door-open"></i> Waiting room on
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="meeting-card-footer">
                <?php if ($m['status'] === 'Scheduled' || $m['status'] === 'Active'): ?>
                    <button class="btn btn-primary btn-sm btn-app"
                            onclick="startMeeting('<?= esc($m['meeting_token']) ?>')">
                        <i class="fa-solid fa-video me-1"></i>
                        <?= $m['status'] === 'Active' ? 'Rejoin' : 'Start' ?>
                    </button>
                    <button class="btn btn-outline-app btn-sm ms-2"
                            onclick="copyLink('<?= base_url('join/' . $m['meeting_token']) ?>')">
                        <i class="fa-solid fa-link me-1"></i>Copy Link
                    </button>
                <?php else: ?>
                    <a href="<?= base_url('meetings/' . $m['meeting_token']) ?>" class="btn btn-ghost btn-sm">
                        <i class="fa-solid fa-eye me-1"></i>Details
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($pager): ?>
    <div class="pagination-wrap">
        <?= $pager->links('default', 'bootstrap_full') ?>
    </div>
    <?php endif; ?>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Filter
document.querySelectorAll('.filter-tab').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const filter = this.dataset.filter;
        document.querySelectorAll('.meeting-card').forEach(card => {
            card.style.display = (filter === 'all' || card.dataset.status === filter) ? '' : 'none';
        });
    });
});

async function startMeeting(token) {
    const res = await fetch(`<?= base_url('api/meetings/') ?>${token}/start`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}
    });
    const data = await res.json();
    if (data.room_url) window.location.href = data.room_url;
}

function copyLink(url) {
    navigator.clipboard.writeText(url).then(() => {
        showToast('Meeting link copied!', 'success');
    });
}

async function cancelMeeting(token) {
    const result = await Swal.fire({
        title: 'Cancel Meeting?',
        text: 'This meeting will be cancelled and participants will not be able to join.',
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
        method: 'DELETE',
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    });
    if (res.ok) {
        Swal.fire({ title: 'Cancelled', text: 'The meeting has been cancelled.', icon: 'success', timer: 1500, showConfirmButton: false })
            .then(() => location.reload());
    }
}
</script>
<?= $this->endSection() ?>
