<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="<?= base_url('css/app.css') ?>" rel="stylesheet">
</head>
<body class="join-layout">

<div class="join-page">
    <!-- Brand -->
    <div class="join-brand">
        <a href="<?= base_url('/') ?>">
            <img src="<?= base_url('img/logo-white.png') ?>" alt="VTalanoa" style="height:28px;max-width:140px;object-fit:contain;">
        </a>
    </div>

    <div class="join-container">
        <!-- Preview Panel -->
        <div class="join-preview">
            <div class="preview-video-wrap">
                <video id="previewVideo" autoplay muted playsinline></video>
                <div class="preview-overlay" id="previewOff">
                    <div class="preview-avatar">
                        <span><?= strtoupper(substr($user['fname'] ?? 'G', 0, 1)) ?></span>
                    </div>
                    <p>Camera is off</p>
                </div>
                <div class="preview-controls">
                    <button class="prev-ctrl-btn" id="toggleCamBtn" onclick="toggleCam()">
                        <i class="fa-solid fa-video" id="camIcon"></i>
                    </button>
                    <button class="prev-ctrl-btn" id="toggleMicBtn" onclick="toggleMic()">
                        <i class="fa-solid fa-microphone" id="micIcon"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Join Panel -->
        <div class="join-panel">
            <div class="join-meeting-info">
                <h2 class="join-meeting-title"><?= esc($meeting['title']) ?></h2>
                <div class="join-meeting-host">
                    Hosted by <strong><?= esc($meeting['fname'] . ' ' . $meeting['lname']) ?></strong>
                </div>
                <div class="join-meeting-time">
                    <i class="fa-solid fa-clock me-1"></i>
                    <?= date('D, M j · h:i A', strtotime($meeting['scheduled_start'])) ?>
                </div>
                <?php if ($meeting['waiting_room']): ?>
                <div class="join-waiting-note">
                    <i class="fa-solid fa-hourglass-half me-1"></i>
                    This meeting has a waiting room. The host will admit you.
                </div>
                <?php endif; ?>
            </div>

            <div class="join-form-section">
                <div class="mb-3">
                    <label class="form-label">Your Display Name</label>
                    <input type="text" id="displayName" class="form-control form-control-app"
                           value="<?= esc(($user['fname'] ?? '') . ' ' . ($user['lname'] ?? '')) ?>"
                           placeholder="Enter your name" required>
                </div>

                <?php if (!empty($meeting['password'])): ?>
                <div class="mb-4">
                    <label class="form-label">Meeting Password</label>
                    <div class="input-icon-wrap">
                        <i class="fa-solid fa-lock input-icon"></i>
                        <input type="password" id="meetingPassword" class="form-control form-control-app"
                               placeholder="Enter meeting password" required>
                    </div>
                </div>
                <?php endif; ?>

                <button class="btn btn-primary btn-app w-100 btn-lg" onclick="joinMeeting()">
                    <i class="fa-solid fa-arrow-right-to-bracket me-2"></i>
                    Join Meeting
                </button>

                <?php if (!$user): ?>
                <p class="text-center mt-3 text-muted">
                    <a href="<?= base_url('auth/login') ?>">Sign in</a> for a better experience
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let localStream = null;
let camOn = true, micOn = true;

async function initPreview() {
    try {
        localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
        document.getElementById('previewVideo').srcObject = localStream;
        document.getElementById('previewOff').style.display = 'none';
    } catch(e) {
        console.log('Media access denied', e);
        document.getElementById('previewOff').style.display = 'flex';
    }
}

function toggleCam() {
    camOn = !camOn;
    if (localStream) {
        localStream.getVideoTracks().forEach(t => t.enabled = camOn);
    }
    const icon = document.getElementById('camIcon');
    const btn  = document.getElementById('toggleCamBtn');
    icon.className = camOn ? 'fa-solid fa-video' : 'fa-solid fa-video-slash';
    btn.classList.toggle('ctrl-off', !camOn);
    document.getElementById('previewOff').style.display = camOn ? 'none' : 'flex';
}

function toggleMic() {
    micOn = !micOn;
    if (localStream) {
        localStream.getAudioTracks().forEach(t => t.enabled = micOn);
    }
    const icon = document.getElementById('micIcon');
    const btn  = document.getElementById('toggleMicBtn');
    icon.className = micOn ? 'fa-solid fa-microphone' : 'fa-solid fa-microphone-slash';
    btn.classList.toggle('ctrl-off', !micOn);
}

async function joinMeeting() {
    const name  = document.getElementById('displayName')?.value.trim();
    if (!name) {
        Swal.fire({ icon: 'warning', title: 'Display Name Required', text: 'Please enter your name before joining.', confirmButtonColor: '#00aeef' });
        return;
    }

    <?php if (!empty($meeting['password'])): ?>
    const pass = document.getElementById('meetingPassword')?.value;
    <?php else: ?>
    const pass = null;
    <?php endif; ?>

    // Stop preview
    if (localStream) localStream.getTracks().forEach(t => t.stop());

    // Store preferences
    sessionStorage.setItem('nm_cam', camOn);
    sessionStorage.setItem('nm_mic', micOn);
    sessionStorage.setItem('nm_display_name', name);

    try {
        const res  = await fetch('<?= base_url('api/meetings/' . $meeting['meeting_token'] . '/join') ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
            body: JSON.stringify({ password: pass, display_name: name }),
        });
        const data = await res.json();

        if (res.ok) {
            window.location.href = data.room_url;
        } else {
            Swal.fire({ icon: 'error', title: 'Cannot Join', text: data.error || 'Could not join. Please try again.', confirmButtonColor: '#00aeef' });
        }
    } catch(e) {
        <?php if ($user): ?>
        window.location.href = '<?= base_url('room/' . $meeting['meeting_token']) ?>';
        <?php else: ?>
        window.location.href = '<?= base_url('auth/login') ?>?redirect=<?= urlencode(base_url('join/' . $meeting['meeting_token'])) ?>';
        <?php endif; ?>
    }
}

initPreview();
</script>
</body>
</html>
