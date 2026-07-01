<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <h2 class="page-title">Edit Meeting</h2>
        <p class="page-subtitle">Update meeting details</p>
    </div>
    <a href="<?= base_url('meetings/' . $meeting['meeting_token']) ?>" class="btn btn-ghost">
        <i class="fa-solid fa-arrow-left me-2"></i> Back
    </a>
</div>

<div class="form-page-wrap">
    <div class="card-app">
        <div class="card-app-header">
            <h3 class="card-app-title"><i class="fa-solid fa-pen me-2"></i>Meeting Details</h3>
        </div>
        <div class="card-app-body">
            <form id="editMeetingForm">
                <?= csrf_field() ?>
                <div class="form-section">
                    <div class="mb-3">
                        <label class="form-label required">Meeting Title</label>
                        <input type="text" id="title" class="form-control form-control-app"
                               value="<?= esc($meeting['title']) ?>" required maxlength="150"
                               placeholder="e.g. Weekly Team Standup">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea id="description" class="form-control form-control-app" rows="3"
                                  placeholder="Optional agenda or notes..."><?= esc($meeting['description']) ?></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label required">Start Date & Time</label>
                            <input type="datetime-local" id="scheduled_start" class="form-control form-control-app"
                                   value="<?= date('Y-m-d\TH:i', strtotime($meeting['scheduled_start'])) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">End Date & Time</label>
                            <input type="datetime-local" id="scheduled_end" class="form-control form-control-app"
                                   value="<?= date('Y-m-d\TH:i', strtotime($meeting['scheduled_end'])) ?>" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Meeting Password</label>
                            <div class="input-icon-wrap">
                                <i class="fa-solid fa-lock input-icon"></i>
                                <input type="password" id="password" class="form-control form-control-app"
                                       placeholder="Leave blank to keep current" id="meetingPw">
                                <button type="button" class="input-toggle-pw" onclick="togglePassword('meetingPw')">
                                    <i class="fa-solid fa-eye" id="meetingPw-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Leave blank to keep the existing password.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Max Participants</label>
                            <input type="number" id="max_participants" class="form-control form-control-app"
                                   value="<?= esc($meeting['max_participants']) ?>" min="2" max="500">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">Meeting Options</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="toggle-option">
                                <div>
                                    <div class="toggle-label"><i class="fa-solid fa-hourglass-half me-2 text-primary"></i>Waiting Room</div>
                                    <div class="toggle-desc">Participants wait for your approval</div>
                                </div>
                                <div class="form-check form-switch ms-3">
                                    <input class="form-check-input" type="checkbox" id="waiting_room"
                                           <?= $meeting['waiting_room'] ? 'checked' : '' ?>>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="toggle-option">
                                <div>
                                    <div class="toggle-label"><i class="fa-solid fa-circle-dot me-2 text-danger"></i>Allow Recording</div>
                                    <div class="toggle-desc">Participants can record this meeting</div>
                                </div>
                                <div class="form-check form-switch ms-3">
                                    <input class="form-check-input" type="checkbox" id="allow_recording"
                                           <?= $meeting['allow_recording'] ? 'checked' : '' ?>>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-app">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Save Changes
                    </button>
                    <a href="<?= base_url('meetings/' . $meeting['meeting_token']) ?>" class="btn btn-ghost">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.getElementById('editMeetingForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const body = {
        title:            document.getElementById('title').value,
        description:      document.getElementById('description').value,
        scheduled_start:  document.getElementById('scheduled_start').value,
        scheduled_end:    document.getElementById('scheduled_end').value,
        max_participants: parseInt(document.getElementById('max_participants').value),
        waiting_room:     document.getElementById('waiting_room').checked ? 1 : 0,
        allow_recording:  document.getElementById('allow_recording').checked ? 1 : 0,
    };

    const pw = document.getElementById('meetingPw')?.value;
    if (pw) body.password = pw;

    const res  = await fetch('<?= base_url('api/meetings/' . $meeting['meeting_token']) ?>', {
        method:  'PUT',
        headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
        body:    JSON.stringify(body),
    });
    const data = await res.json();

    if (res.ok) {
        showToast('Meeting updated!', 'success');
        setTimeout(() => {
            window.location.href = '<?= base_url('meetings/' . $meeting['meeting_token']) ?>';
        }, 1000);
    } else {
        showToast(data.error || 'Could not update meeting.', 'error');
    }
});
</script>
<?= $this->endSection() ?>
