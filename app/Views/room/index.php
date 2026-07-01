<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= esc($meeting['title']) ?> — VTalanoa</title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= base_url('css/app.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/room.css') ?>" rel="stylesheet">
<style>
/* ── Reconnect banner ────────────────────────────────────── */
.reconnect-banner {
  display: none; position: fixed; top: 0; left: 0; right: 0; z-index: 9999;
  background: rgba(239,68,68,.92); color: #fff;
  align-items: center; justify-content: center; gap: 10px;
  padding: 10px 16px; font-weight: 600; font-size: 14px;
}
.reconnect-spinner {
  width: 16px; height: 16px;
  border: 2px solid rgba(255,255,255,.4); border-top-color: #fff;
  border-radius: 50%; animation: rSpin 0.8s linear infinite; flex-shrink: 0;
}
@keyframes rSpin { to { transform: rotate(360deg); } }

/* ── Time Left indicator ─────────────────────────────────── */
.time-left-wrap {
  display: none; align-items: center; gap: 5px; margin-left: 14px;
  padding: 2px 10px; background: var(--rm-btn);
  border-radius: 20px; font-size: 12px; font-weight: 500;
}
.time-left-label { color: var(--rm-text-3); white-space: nowrap; }
#timeRemaining   { font-variant-numeric: tabular-nums; color: var(--rm-text-2); min-width: 42px; text-align: right; }

/* ── SweetAlert mobile override ──────────────────────────── */
@media (max-width: 767px) {
  .swal2-container { padding: 12px !important; }
  .swal2-popup { width: 100% !important; max-width: 320px !important; margin: 0 auto !important; font-size: 14px !important; padding: 20px 16px !important; border-radius: 16px !important; }
  .swal2-title { font-size: 17px !important; }
  .swal2-html-container { font-size: 13px !important; }
  .swal2-actions { flex-wrap: wrap; gap: 8px !important; }
  .swal2-styled { min-width: 100px !important; }
}

/* ── Recording indicator ─────────────────────────────────── */
.rec-indicator {
  display: none; align-items: center; gap: 5px; padding: 2px 10px;
  background: rgba(239,68,68,.12); border: 1px solid rgba(239,68,68,.4);
  border-radius: 20px; font-size: 11px; font-weight: 700; color: #ef4444;
  margin-left: 10px; white-space: nowrap;
}
.rec-dot { width: 8px; height: 8px; background: #ef4444; border-radius: 50%; animation: recPulse 1.2s ease-in-out infinite; flex-shrink: 0; }
@keyframes recPulse { 0%,100%{opacity:1} 50%{opacity:.25} }

/* ── Raised-hand badge ───────────────────────────────────── */
.hand-raised-badge {
  position: absolute; top: 6px; left: 6px; font-size: 18px;
  display: none; align-items: center; justify-content: center;
  background: rgba(0,0,0,.5); border-radius: 8px; padding: 3px 6px;
  line-height: 1; z-index: 10; pointer-events: none;
}
.hand-raised-indicator { font-size: 14px; margin-right: 2px; }

/* ── Host remove button on tiles ─────────────────────────── */
.tile-remove-btn {
  position: absolute; top: 6px; right: 6px;
  width: 26px; height: 26px; background: rgba(239,68,68,.8);
  border: none; border-radius: 50%; color: #fff; font-size: 13px;
  cursor: pointer; display: flex; align-items: center; justify-content: center;
  z-index: 20; opacity: 0; transition: opacity 0.2s; pointer-events: all;
}
.video-tile:hover .tile-remove-btn, .tile-remove-btn:focus { opacity: 1; }
@media (max-width: 767px) { .tile-remove-btn { opacity: 0.75; } }

/* ── Background effects buttons in settings ──────────────── */
.settings-bg-options { display: flex; gap: 8px; }
.settings-bg-btn {
  flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px;
  background: var(--rm-btn); border: 2px solid transparent;
  border-radius: 10px; padding: 10px 8px; color: var(--rm-text-2);
  cursor: pointer; font-size: 11px; font-weight: 500; transition: all .15s;
}
.settings-bg-btn i { font-size: 18px; }
.settings-bg-btn.active, .settings-bg-btn:hover {
  border-color: var(--rm-primary); color: var(--rm-text); background: var(--rm-primary-bg);
}
.settings-hint { font-size: 10px; color: var(--rm-text-3); font-weight: 400; margin-left: auto; white-space: nowrap; }

/* ── Spotlight / focus overlay — always dark (video context) */
.spotlight-overlay {
  display: none; position: absolute; inset: 0;
  z-index: 900; flex-direction: column; background: #0a0a0a;
}
.spotlight-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 8px 16px; background: rgba(0,0,0,.6); flex-shrink: 0;
}
.spotlight-name { color: #fff; font-weight: 600; font-size: 14px; }
.spotlight-exit-btn { background: rgba(255,255,255,.12); color: #fff; border: none; border-radius: 8px; padding: 6px 14px; font-size: 13px; cursor: pointer; }
.spotlight-main { flex: 1; min-height: 0; position: relative; display: flex; align-items: center; justify-content: center; background: #111; }
.spotlight-video { width: 100%; height: 100%; object-fit: cover; }
.spotlight-novideo { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: #1e2228; }
.spotlight-strip {
  height: 90px; flex-shrink: 0; display: flex; overflow-x: auto;
  gap: 4px; padding: 4px 8px; background: rgba(0,0,0,.7);
  scrollbar-width: none; -webkit-overflow-scrolling: touch;
}
.spotlight-strip::-webkit-scrollbar { display: none; }
.spotlight-thumb {
  flex-shrink: 0; width: 130px; height: 82px; border-radius: 8px; overflow: hidden;
  cursor: pointer; position: relative; background: #1e2228;
  border: 2px solid transparent; transition: border-color 0.15s;
}
.spotlight-thumb:hover { border-color: rgba(255,255,255,.4); }
.thumb-name {
  position: absolute; bottom: 4px; left: 4px; color: #fff; font-size: 9px;
  background: rgba(0,0,0,.65); padding: 2px 5px; border-radius: 4px;
  max-width: calc(100% - 8px); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

/* ── Mobile tab bar ──────────────────────────────────────── */
.mobile-tab-bar { display: none; }

/* ── Waiting room action buttons ─────────────────────────── */
.waiting-actions { display: flex; gap: 6px; flex-shrink: 0; }
.btn-remove-waiting {
  padding: 4px 10px; background: rgba(239,68,68,.08);
  border: 1px solid #ef4444; color: #ef4444; border-radius: 6px;
  font-size: 12px; font-weight: 500; cursor: pointer; transition: background 0.15s;
}
.btn-remove-waiting:hover { background: rgba(239,68,68,.18); }

/* ── Mobile panel pull handle ────────────────────────────── */
.room-panel::before {
  content: ''; display: block; flex-shrink: 0;
  width: 36px; height: 4px; background: var(--rm-border);
  border-radius: 2px; margin: 8px auto 0;
}

/* ── Mobile-responsive Room UI ───────────────────────────── */
@media (max-width: 767px) {
  html, body { height: 100%; overflow: hidden; }
  .room-layout { position: fixed !important; top: 0; right: 0; bottom: 0; left: 0; display: flex !important; flex-direction: column !important; height: auto !important; }
  .room-header { padding: 6px 12px; flex-shrink: 0; }
  .room-title  { display: none; }
  .room-main   { flex: 1 !important; min-height: 0 !important; overflow: hidden; display: flex; }
  .video-area  { flex: 1; width: 100%; min-height: 0; }
  .video-grid .video-tile { min-width: 0 !important; min-height: 0 !important; }
  .room-panels { position: fixed !important; top: 0; right: 0; bottom: 0; left: 0; pointer-events: none; z-index: 500; display: block; }
  .room-panels.panel-is-open { pointer-events: auto; }
  .room-panel {
    display: flex !important; position: absolute;
    bottom: 0; left: 0; right: 0; height: 72vh; max-height: 72vh;
    border-radius: 16px 16px 0 0; flex-direction: column; overflow: hidden;
    transform: translateY(105%); visibility: hidden; pointer-events: none;
    transition: transform 0.3s cubic-bezier(.4,0,.2,1), visibility 0s linear 0.3s;
  }
  .room-panel.panel-open {
    transform: translateY(0); visibility: visible; pointer-events: auto !important;
    transition: transform 0.3s cubic-bezier(.4,0,.2,1); touch-action: manipulation;
  }
  #panelBackdrop {
    display: none; position: absolute; inset: 0;
    background: rgba(0,0,0,.45); z-index: 1;
    -webkit-tap-highlight-color: transparent;
  }
  #panelBackdrop.active { display: block; }
  .room-panel { z-index: 2; }

  /* Mobile tab bar */
  .mobile-tab-bar {
    display: flex; flex-shrink: 0;
    background: var(--rm-header);
    backdrop-filter: blur(12px);
    border-top: 1px solid var(--rm-border);
    padding: 4px 4px 2px; gap: 2px; justify-content: space-around;
  }
  .mobile-tab {
    display: flex; flex-direction: column; align-items: center; gap: 2px;
    background: none; border: none; color: var(--rm-text-2);
    font-size: 9px; font-weight: 500; min-width: 44px;
    padding: 5px 6px 4px; border-radius: 8px; position: relative;
    cursor: pointer; -webkit-tap-highlight-color: transparent;
  }
  .mobile-tab i      { font-size: 19px; }
  .mobile-tab.active { color: var(--rm-primary); }
  .mobile-tab-badge {
    position: absolute; top: 1px; right: 2px;
    background: #ef4444; color: #fff; font-size: 8px; font-weight: 700;
    min-width: 15px; height: 15px; border-radius: 8px;
    display: none; align-items: center; justify-content: center; padding: 0 3px;
  }
  .mobile-tab-badge.show { display: flex; }

  /* Control bar */
  .control-bar {
    flex-shrink: 0 !important; flex-direction: row !important; height: auto !important;
    padding: 6px 8px !important;
    padding-bottom: max(12px, env(safe-area-inset-bottom, 12px)) !important;
    gap: 4px; justify-content: center;
  }
  .control-left  { display: none !important; }
  .control-right { display: none !important; }
  .control-group.control-center { width: auto; justify-content: center; gap: 6px; }
  .ctrl-btn {
    flex-direction: column !important; min-width: 52px; height: auto !important;
    padding: 7px 8px 5px !important; gap: 2px; border-radius: 12px;
  }
  .ctrl-btn i  { font-size: 20px !important; }
  .ctrl-label  { display: block !important; font-size: 9px !important; line-height: 1.2; }
  .ctrl-btn.ctrl-end { padding: 7px 14px 5px !important; }
  .waiting-overlay-card { width: 92%; max-width: 360px; padding: 24px 20px; }
  .screen-share-thumbs { height: 72px; }
  .screen-share-thumbs .video-tile { width: 110px !important; height: 64px !important; }
  .spotlight-header { padding: 6px 12px; }
  .spotlight-strip  { height: 72px; padding: 3px 6px; }
  .spotlight-thumb  { width: 108px; height: 66px; }
}

/* ── Breakout manager Swal styles ────────────────────────── */
.br-manager { text-align: left; }
.br-top     { display: flex; align-items: center; gap: 8px; margin-bottom: 14px; flex-wrap: wrap; }
.br-count-select, .br-room-select {
  background: var(--rm-input); border: 1px solid var(--rm-input-b);
  color: var(--rm-input-c); border-radius: 6px; padding: 4px 8px; font-size: 12px;
}
.br-peers-list { max-height: 240px; overflow-y: auto; display: flex; flex-direction: column; gap: 6px; }
.br-peer-row {
  display: flex; align-items: center; justify-content: space-between; gap: 8px;
  background: var(--rm-item-hover); padding: 8px 10px; border-radius: 8px;
}
.br-peer-name  { font-size: 13px; color: var(--rm-text); flex: 1; }
.br-room-select{ flex-shrink: 0; min-width: 130px; }
.btn-br-auto {
  background: var(--rm-primary-bg); border: 1px solid var(--rm-primary-b);
  color: var(--rm-primary); border-radius: 6px; padding: 4px 10px;
  font-size: 11px; cursor: pointer; white-space: nowrap;
}
.btn-br-auto:hover { background: var(--rm-primary-b); }

/* ── Poll notification quick-vote buttons ────────────────── */
.poll-notif-btn {
  display: block; width: 100%;
  background: var(--rm-btn); border: 1px solid var(--rm-border);
  color: var(--rm-text); border-radius: 8px;
  padding: 8px 14px; font-size: 13px; text-align: left; cursor: pointer;
  transition: background .15s, border-color .15s;
}
.poll-notif-btn:hover { background: var(--rm-primary-bg); border-color: var(--rm-primary-b); }
</style>
</head>
<body class="room-body">

<!-- Reconnect banner (shown when socket drops; hidden on reconnect) -->
<div id="reconnectBanner" class="reconnect-banner">
  <div class="reconnect-spinner"></div>
  <span id="reconnectMsg">Reconnecting…</span>
</div>

<!-- Peer reconnecting banner (shown when a remote participant loses connectivity) -->
<div id="peerReconnectBanner" class="peer-reconnect-banner" style="display:none">
  <div class="reconnect-spinner"></div>
  <span id="peerReconnectNames">Reconnecting…</span>
</div>

<!-- Meeting Room -->
<div class="room-layout">

    <!-- Top Bar -->
    <header class="room-header">
        <div class="room-header-left">
            <a href="<?= base_url('/') ?>" class="room-brand" title="Home">
                <img src="<?= base_url('img/logo.png') ?>" alt="MeetWave" style="height:30px;max-width:130px;object-fit:contain;">
            </a>
            <div class="room-title"><?= esc($meeting['title']) ?></div>
        </div>
        <div class="room-header-center">
            <div class="room-timer" id="roomTimer">00:00</div>
            <div id="lockIndicator" class="lock-indicator" style="display:none">
                <i class="fa-solid fa-lock"></i> Locked
            </div>
            <div id="cohostBadge" class="cohost-header-badge" style="display:none">
                <i class="fa-solid fa-shield-halved me-1"></i>Co-host
            </div>
            <div class="time-left-wrap" id="timeLeftWrap">
                <span class="time-left-label">Time Left:</span>
                <span id="timeRemaining">--:--</span>
            </div>
            <div class="rec-indicator" id="recIndicator">
                <span class="rec-dot"></span>REC
            </div>
        </div>
        <div class="room-header-right">
            <div class="room-info">
                <button class="room-info-btn" onclick="copyMeetingLink()">
                    <i class="fa-solid fa-link"></i>
                    <span class="d-none d-md-inline">Copy Link</span>
                </button>
                <span class="room-participant-count">
                    <i class="fa-solid fa-users"></i>
                    <span id="participantCount">1</span>
                </span>
            </div>
            <button class="room-theme-btn" id="themeToggleBtn" onclick="toggleRoomTheme()" title="Toggle dark / light mode" aria-label="Toggle theme">
                <i class="fa-solid fa-moon" id="themeIcon"></i>
            </button>
        </div>
    </header>

    <!-- Breakout rooms status bar (host only; visible while rooms are active) -->
    <?php if ($user['user_id'] == $meeting['host_user_id']): ?>
    <div id="brRoomsBar" class="br-rooms-bar" style="display:none">
        <i class="fa-solid fa-door-open me-2 text-info"></i>
        <span class="br-bar-title">Breakout Rooms:</span>
        <div id="brRoomsList" class="br-rooms-list-inline"></div>
        <button class="btn-br-small" onclick="broadcastToBreakouts()" title="Broadcast to all rooms">
            <i class="fa-solid fa-bullhorn me-1"></i>Broadcast
        </button>
        <button class="btn-br-end" onclick="endBreakoutRooms()" title="End all breakout rooms">
            <i class="fa-solid fa-xmark me-1"></i>End Rooms
        </button>
    </div>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="room-main">

        <!-- Video Grid -->
        <div class="video-area" id="videoArea">
            <div class="video-grid" id="videoGrid">
                <!-- Local video tile (click to spotlight) -->
                <div class="video-tile local-tile" id="localTile" onclick="focusTile('local')" style="cursor:pointer" title="Click to focus">
                    <video id="localVideo" autoplay muted playsinline></video>
                    <div class="tile-overlay">
                        <div class="tile-name">
                            <?= esc($user['fname'] . ' ' . $user['lname']) ?>
                            <span class="badge-you">You</span>
                        </div>
                        <div class="tile-indicators">
                            <span class="tile-indicator" id="localMicIndicator" title="Mic muted">
                                <i class="fa-solid fa-microphone-slash"></i>
                            </span>
                        </div>
                    </div>
                    <div class="tile-no-video" id="localNoVideo">
                        <?php if (!empty($user['profile_photo'])): ?>
                        <img src="<?= base_url(esc($user['profile_photo'])) ?>" class="tile-avatar-img" alt="">
                        <?php else: ?>
                        <div class="tile-avatar">
                            <?= strtoupper(substr($user['fname'], 0, 1) . substr($user['lname'], 0, 1)) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Screen Share Overlay -->
            <div class="screen-share-area" id="screenShareArea" style="display:none">
                <div class="screen-share-main">
                    <video id="screenShareVideo" autoplay playsinline></video>
                    <div class="screen-share-label">
                        <i class="fa-solid fa-display me-1"></i>
                        <span id="screenSharerName">Screen Share</span>
                    </div>
                </div>
                <div class="screen-share-thumbs" id="thumbsStrip"></div>
            </div>
        </div>

        <!-- Spotlight / focus overlay — inside room-main so it doesn't cover the control bar -->
        <div id="spotlightOverlay" class="spotlight-overlay">
            <div class="spotlight-header">
                <span id="spotlightName" class="spotlight-name"></span>
                <button id="spotlightExitBtn" class="spotlight-exit-btn" onclick="exitSpotlight()">
                    <i class="fa-solid fa-compress me-1"></i>Exit Focus
                </button>
            </div>
            <div class="spotlight-main">
                <video id="spotlightVideo" autoplay playsinline class="spotlight-video"></video>
                <div id="spotlightNoVideo" class="spotlight-novideo" style="display:none">
                    <div class="tile-avatar" id="spotlightAvatar"></div>
                </div>
            </div>
            <div class="spotlight-strip" id="spotlightStrip"></div>
        </div>

        <!-- Side Panels -->
        <div class="room-panels">
            <!-- Waiting Room Panel (host + co-host) -->
            <div class="room-panel" id="waitingPanel" style="display:none">
                <div class="panel-header">
                    <h4><i class="fa-solid fa-hourglass-half me-2"></i>Waiting Room</h4>
                    <button class="panel-close" onclick="togglePanel('waiting')">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="panel-body" id="waitingList">
                    <div class="panel-empty">No one is waiting</div>
                </div>
                <div class="panel-footer">
                    <button class="btn btn-primary btn-sm w-100" onclick="admitAll()">
                        <i class="fa-solid fa-check-double me-1"></i>Admit All
                    </button>
                </div>
            </div>

            <!-- Participants Panel -->
            <div class="room-panel" id="participantsPanel" style="display:none">
                <div class="panel-header">
                    <h4><i class="fa-solid fa-users me-2"></i>Participants (<span id="pCount">1</span>)</h4>
                    <button class="panel-close" onclick="togglePanel('participants')">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="panel-body" id="participantsList"></div>
            </div>

            <!-- Chat Panel -->
            <div class="room-panel" id="chatPanel" style="display:none">
                <div class="panel-header">
                    <h4><i class="fa-solid fa-comment-dots me-2"></i>Chat</h4>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <button class="panel-close" onclick="exportChatTranscript()" title="Export chat transcript">
                            <i class="fa-solid fa-download"></i>
                        </button>
                        <button class="panel-close" onclick="togglePanel('chat')">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
                <div class="panel-body chat-messages" id="chatMessages"></div>
                <div id="typingIndicator" class="chat-typing-indicator" style="display:none"></div>
                <div class="panel-footer chat-input-area" style="position:relative;">
                    <input type="file" id="chatFileInput" style="display:none"
                           accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx"
                           onchange="handleFileSelect(this)">
                    <div id="emojiPanel" class="emoji-panel"></div>
                    <div class="chat-input-wrap">
                        <button class="chat-attach-btn" onclick="document.getElementById('chatFileInput').click()" title="Attach file">
                            <i class="fa-solid fa-paperclip"></i>
                        </button>
                        <button class="chat-attach-btn" onclick="toggleEmojiPanel()" title="Emoji">
                            <i class="fa-regular fa-face-smile"></i>
                        </button>
                        <button class="chat-attach-btn" onclick="showPollForm()" title="Create poll">
                            <i class="fa-solid fa-chart-bar"></i>
                        </button>
                        <input type="text" id="chatInput" class="chat-input" placeholder="Message…"
                               onkeydown="chatKeydown(event)">
                        <button class="chat-send-btn" onclick="sendChat()">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Settings Panel -->
            <div class="room-panel" id="settingsPanel" style="display:none">
                <div class="panel-header">
                    <h4><i class="fa-solid fa-gear me-2"></i>Settings</h4>
                    <button class="panel-close" onclick="togglePanel('settings')">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="panel-body" style="overflow-y:auto;">

                    <!-- Microphone -->
                    <div class="settings-group">
                        <label class="settings-label">
                            <i class="fa-solid fa-microphone me-2"></i>Microphone
                            <span class="settings-hint">Voice input device</span>
                        </label>
                        <select id="settingsMic" class="settings-select" onchange="switchMicDevice(this.value)"></select>
                    </div>

                    <!-- Speaker -->
                    <div class="settings-group" id="speakerGroup">
                        <label class="settings-label">
                            <i class="fa-solid fa-volume-high me-2"></i>Speaker
                            <span class="settings-hint">Audio output device</span>
                        </label>
                        <select id="settingsSpeaker" class="settings-select" onchange="applySpeakerDevice(this.value)"></select>
                    </div>

                    <!-- Volume -->
                    <div class="settings-group">
                        <label class="settings-label">
                            <i class="fa-solid fa-volume-low me-2"></i>Participant Volume
                            <span id="volumeVal" class="ms-auto">100%</span>
                        </label>
                        <input type="range" id="volumeSlider" class="settings-range" min="0" max="100" value="100"
                               oninput="applyVolume(this.value)">
                        <small class="text-muted" style="font-size:10px;">Controls how loud other participants sound to you</small>
                    </div>

                    <!-- Camera -->
                    <div class="settings-group">
                        <label class="settings-label">
                            <i class="fa-solid fa-video me-2"></i>Camera
                            <span class="settings-hint">Video input device</span>
                        </label>
                        <select id="settingsCam" class="settings-select" onchange="switchCamDevice(this.value)"></select>
                    </div>

                    <!-- Camera preview -->
                    <div class="settings-preview-wrap">
                        <video id="settingsPreview" autoplay muted playsinline class="settings-preview"></video>
                        <div class="settings-preview-label">Camera Preview</div>
                    </div>

                    <!-- Background Effects -->
                    <div class="settings-group">
                        <label class="settings-label">
                            <i class="fa-solid fa-wand-magic-sparkles me-2"></i>Background Effect
                            <span class="settings-hint">Affects outgoing video</span>
                        </label>
                        <div class="settings-bg-options">
                            <button class="settings-bg-btn active" id="bgNone" onclick="applyBgEffect('none')" title="No effect">
                                <i class="fa-solid fa-ban"></i>
                                <span>None</span>
                            </button>
                            <button class="settings-bg-btn" id="bgBlur" onclick="applyBgEffect('blur')" title="Blur background">
                                <i class="fa-solid fa-circle-half-stroke"></i>
                                <span>Blur BG</span>
                            </button>
                            <button class="settings-bg-btn" id="bgImage" onclick="applyBgEffect('image')" title="Virtual background">
                                <i class="fa-solid fa-image"></i>
                                <span>Virtual BG</span>
                            </button>
                        </div>
                        <div id="bgImagePicker" style="display:none;margin-top:10px;">
                            <small class="text-muted d-block mb-2">Upload a background image (JPG or PNG):</small>
                            <input type="file" id="bgFileInput" accept="image/jpeg,image/png,image/webp" style="display:none"
                                   onchange="loadCustomBg(this)">
                            <button class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('bgFileInput').click()">
                                <i class="fa-solid fa-upload me-1"></i>Upload Image
                            </button>
                        </div>
                        <small class="text-muted d-block mt-2" style="font-size:10px;">
                            Background effects use AI processing — first load may take a few seconds.
                        </small>
                    </div>

                    <!-- Touch Up My Appearance -->
                    <div class="settings-group">
                        <label class="settings-label">
                            <i class="fa-solid fa-wand-sparkles me-2"></i>Touch Up My Appearance
                            <span class="settings-hint">Soft video filter</span>
                        </label>
                        <div class="settings-bg-options">
                            <button class="settings-bg-btn active" id="touchUpOff" onclick="setTouchUp(false)" title="No filter">
                                <i class="fa-solid fa-ban"></i>
                                <span>Off</span>
                            </button>
                            <button class="settings-bg-btn" id="touchUpOn" onclick="setTouchUp(true)" title="Enable touch-up">
                                <i class="fa-solid fa-face-smile-wink"></i>
                                <span>Touch Up</span>
                            </button>
                        </div>
                        <small class="text-muted d-block mt-2" style="font-size:10px;">
                            Applies a soft-focus brightness filter to your outgoing video.
                        </small>
                    </div>

                </div>
            </div>

            <!-- AI Companion Panel -->
            <div class="room-panel" id="aiPanel" style="display:none">
                <div class="panel-header">
                    <h4><i class="fa-solid fa-robot me-2"></i>AI Companion</h4>
                    <button class="panel-close" onclick="togglePanel('ai')">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <!-- Panel body split into transcript strip + AI messages -->
                <div class="ai-panel-body">

                    <!-- Live Transcript strip -->
                    <div class="ai-transcript-strip">
                        <div class="ai-strip-header">
                            <i class="fa-solid fa-microphone-lines"></i>
                            <span>Live Transcript</span>
                            <span id="aiSegmentCount" class="ai-count-badge">0</span>
                            <span id="transcribeDot" class="transcribe-dot" style="display:none"></span>
                            <button id="btnTranscribe" class="btn-transcribe ms-auto" onclick="toggleTranscription()">
                                <i class="fa-solid fa-circle-dot me-1"></i>
                                <span id="transcribeLabel">Start</span>
                            </button>
                        </div>
                        <div class="transcript-content" id="transcriptContent">
                            <div class="transcript-empty">Click <strong>Start</strong> to capture speech. Works in Chrome &amp; Edge.</div>
                        </div>
                        <div class="transcript-interim" id="transcriptInterim"></div>
                    </div>

                    <!-- Quick AI action buttons -->
                    <div class="ai-quick-strip">
                        <button id="btnAiSummarize" class="ai-action-btn" onclick="askAI('summary')">
                            <i class="fa-solid fa-list-check"></i>Summarize
                        </button>
                        <button id="btnAiActions" class="ai-action-btn" onclick="askAI('actions')">
                            <i class="fa-solid fa-square-check"></i>Action Items
                        </button>
                        <button id="btnAiEmail" class="ai-action-btn" onclick="askAI('email')">
                            <i class="fa-solid fa-envelope"></i>Draft Email
                        </button>
                    </div>

                    <!-- AI conversation -->
                    <div class="ai-messages" id="aiMessages">
                        <div class="ai-welcome">
                            <i class="fa-solid fa-robot ai-welcome-icon"></i>
                            <p>Start transcription, then use the buttons above to summarize, find action items, draft a follow-up email, or ask me anything about the discussion.</p>
                        </div>
                    </div>

                    <!-- Loading indicator -->
                    <div id="aiLoadingIndicator" class="ai-loading" style="display:none">
                        <div class="reconnect-spinner"></div>
                        <span>AI is thinking…</span>
                    </div>

                </div>
                <div class="panel-footer chat-input-area">
                    <div class="chat-input-wrap">
                        <input type="text" id="aiInput" class="chat-input"
                               placeholder="Ask about the meeting…"
                               onkeydown="aiInputKeydown(event)">
                        <button id="aiSendBtn" class="chat-send-btn" onclick="sendAIQuestion()">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Panel backdrop — inside .room-panels so it sits in the same stacking context -->
            <div id="panelBackdrop"></div>
        </div>
    </div>

    <!-- Mobile tab bar: Chat / People / Waiting / Settings (visible only on mobile) -->
    <div class="mobile-tab-bar" id="mobileTabBar">
        <button class="mobile-tab" id="mbChat" onclick="togglePanel('chat')" aria-label="Chat">
            <i class="fa-solid fa-comment-dots"></i>
            <span class="mobile-tab-badge" id="chatBadge2"></span>
            <span>Chat</span>
        </button>
        <button class="mobile-tab" id="mbPeople" onclick="togglePanel('participants')" aria-label="People">
            <i class="fa-solid fa-users"></i>
            <span>People</span>
        </button>
        <button class="mobile-tab" id="mbWaiting" onclick="togglePanel('waiting')" aria-label="Waiting"
                style="<?= $user['user_id'] == $meeting['host_user_id'] ? '' : 'display:none' ?>">
            <i class="fa-solid fa-hourglass-half"></i>
            <span class="mobile-tab-badge" id="waitingBadge2"></span>
            <span>Waiting</span>
        </button>
        <button class="mobile-tab" id="mbWhiteboard" onclick="openWhiteboard()" aria-label="Whiteboard">
            <i class="fa-solid fa-pen-to-square"></i>
            <span>Board</span>
        </button>
        <button class="mobile-tab" id="mbAI" onclick="togglePanel('ai')" aria-label="AI Companion">
            <i class="fa-solid fa-robot"></i>
            <span>AI</span>
        </button>
        <button class="mobile-tab" id="mbSettings" onclick="togglePanel('settings')" aria-label="Settings">
            <i class="fa-solid fa-gear"></i>
            <span>Settings</span>
        </button>
    </div>

    <!-- Control Bar -->
    <footer class="control-bar">
        <div class="control-group control-left">
            <div class="control-time" id="controlTimer">00:00</div>
        </div>

        <div class="control-group control-center">
            <!-- Mic -->
            <button class="ctrl-btn" id="btnMic" onclick="toggleMic()" title="Toggle Microphone">
                <i class="fa-solid fa-microphone" id="micIcon"></i>
                <span class="ctrl-label">Mic</span>
            </button>

            <!-- Camera -->
            <button class="ctrl-btn" id="btnCam" onclick="toggleCam()" title="Toggle Camera">
                <i class="fa-solid fa-video" id="camIcon"></i>
                <span class="ctrl-label">Camera</span>
            </button>

            <!-- Screen Share -->
            <button class="ctrl-btn" id="btnScreen" onclick="toggleScreen()" title="Share Screen">
                <i class="fa-solid fa-display" id="screenIcon"></i>
                <span class="ctrl-label">Share</span>
            </button>

            <!-- Record (host only) -->
            <?php if ($user['user_id'] == $meeting['host_user_id']): ?>
            <button class="ctrl-btn" id="btnRecord" onclick="toggleRecording()" title="Record Meeting">
                <i class="fa-solid fa-circle" id="recordIcon"></i>
                <span class="ctrl-label" id="recordLabel">Record</span>
            </button>
            <?php endif; ?>

            <!-- Raise Hand -->
            <button class="ctrl-btn" id="btnHand" onclick="toggleHand()" title="Raise / Lower Hand">
                <i class="fa-solid fa-hand"></i>
                <span class="ctrl-label" id="handLabel">Hand</span>
            </button>

            <!-- End/Leave -->
            <button class="ctrl-btn ctrl-end" id="btnEnd" onclick="endOrLeave()" title="End/Leave">
                <i class="fa-solid fa-phone-slash"></i>
                <span class="ctrl-label">
                    <?= $user['user_id'] == $meeting['host_user_id'] ? 'End' : 'Leave' ?>
                </span>
            </button>
        </div>

        <div class="control-group control-right">
            <!-- Chat -->
            <button class="ctrl-btn ctrl-panel" id="btnChat" onclick="togglePanel('chat')" title="Chat">
                <i class="fa-solid fa-comment-dots"></i>
                <span class="chat-badge" id="chatBadge"></span>
                <span class="ctrl-label">Chat</span>
            </button>

            <!-- Participants -->
            <button class="ctrl-btn ctrl-panel" id="btnParticipants" onclick="togglePanel('participants')" title="Participants">
                <i class="fa-solid fa-users"></i>
                <span class="ctrl-label">People</span>
            </button>

            <!-- Waiting Room (host always; co-host shown via JS) -->
            <button class="ctrl-btn ctrl-panel" id="btnWaiting" onclick="togglePanel('waiting')" title="Waiting Room"
                    style="<?= $user['user_id'] == $meeting['host_user_id'] ? '' : 'display:none' ?>">
                <i class="fa-solid fa-hourglass-half"></i>
                <span class="waiting-badge" id="waitingBadge"></span>
                <span class="ctrl-label">Waiting</span>
            </button>

            <?php if ($user['user_id'] == $meeting['host_user_id']): ?>
            <!-- Lock Meeting (host only) -->
            <button class="ctrl-btn" id="btnLock" onclick="toggleLockMeeting()" title="Lock / Unlock Meeting">
                <i class="fa-solid fa-lock-open" id="lockIcon"></i>
                <span class="ctrl-label" id="lockLabel">Lock</span>
            </button>
            <?php endif; ?>

            <!-- Whiteboard -->
            <button class="ctrl-btn ctrl-panel" id="btnWhiteboard" onclick="openWhiteboard()" title="Collaborative Whiteboard">
                <i class="fa-solid fa-pen-to-square"></i>
                <span class="ctrl-label">Board</span>
            </button>

            <?php if ($user['user_id'] == $meeting['host_user_id']): ?>
            <!-- Breakout Rooms (host only) -->
            <button class="ctrl-btn ctrl-panel" id="btnBreakout" onclick="openBreakoutManager()" title="Breakout Rooms">
                <i class="fa-solid fa-door-open"></i>
                <span class="ctrl-label">Breakout</span>
            </button>
            <?php endif; ?>

            <!-- AI Companion -->
            <button class="ctrl-btn ctrl-panel" id="btnAI" onclick="togglePanel('ai')" title="AI Companion">
                <i class="fa-solid fa-robot"></i>
                <span class="ctrl-label">AI</span>
            </button>

            <!-- Settings -->
            <button class="ctrl-btn ctrl-panel" id="btnSettings" onclick="togglePanel('settings')" title="Device Settings">
                <i class="fa-solid fa-gear"></i>
                <span class="ctrl-label">Settings</span>
            </button>
        </div>
    </footer>
</div>

<!-- Whiteboard Overlay -->
<div id="wbOverlay" style="display:none;position:fixed;inset:0;z-index:800;background:#0f172a;flex-direction:column;">
    <div class="wb-toolbar">
        <div class="wb-tools">
            <button class="wb-tool-btn active" id="wbToolPen" onclick="setWbTool('pen')" title="Pen">
                <i class="fa-solid fa-pen"></i>
            </button>
            <button class="wb-tool-btn" id="wbToolEraser" onclick="setWbTool('eraser')" title="Eraser">
                <i class="fa-solid fa-eraser"></i>
            </button>
        </div>
        <div class="wb-colors">
            <?php
            $wbColors = ['#ffffff','#ef4444','#f97316','#eab308','#22c55e','#00aeef','#8b5cf6','#ec4899','#000000'];
            foreach ($wbColors as $c):
            ?>
            <button class="wb-color-swatch<?= $c === '#ffffff' ? ' active' : '' ?>"
                    data-color="<?= $c ?>"
                    style="background:<?= $c ?>;"
                    onclick="setWbColor('<?= $c ?>')" title="<?= $c ?>"></button>
            <?php endforeach; ?>
        </div>
        <div class="wb-width-wrap">
            <input type="range" min="1" max="20" value="3" class="wb-width-slider"
                   oninput="setWbWidth(this.value)">
            <span id="wbWidthVal" class="wb-width-val">3px</span>
        </div>
        <div class="wb-actions">
            <button class="wb-action-btn" onclick="wbClear()" title="Clear">
                <i class="fa-solid fa-trash"></i> Clear
            </button>
            <button class="wb-action-btn" onclick="wbDownload()" title="Download PNG">
                <i class="fa-solid fa-download"></i> Save
            </button>
            <button class="wb-action-btn wb-close-btn" onclick="closeWhiteboard()" title="Close">
                <i class="fa-solid fa-xmark"></i> Close
            </button>
        </div>
    </div>
    <div class="wb-canvas-area">
        <canvas id="wbCanvas" style="cursor:crosshair;touch-action:none;"></canvas>
    </div>
</div>

<!-- Waiting Room Overlay (shown to non-hosts when waiting_room is enabled) -->
<div id="waitingOverlay" class="waiting-overlay" style="display:none">
    <div class="waiting-overlay-card">
        <div class="waiting-spinner"></div>
        <h3>Waiting to be admitted</h3>
        <p>The host will let you in shortly.</p>
        <button class="btn btn-ghost mt-2" onclick="confirmEnd()">
            <i class="fa-solid fa-arrow-left me-1"></i>Leave
        </button>
    </div>
</div>

<!-- End Meeting Modal -->
<div class="modal fade" id="endModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content modal-app">
            <div class="modal-body text-center p-4">
                <div class="end-modal-icon"><i class="fa-solid fa-phone-slash"></i></div>
                <h4 class="mb-2">
                    <?= $user['user_id'] == $meeting['host_user_id'] ? 'End Meeting?' : 'Leave Meeting?' ?>
                </h4>
                <p class="text-muted mb-4">
                    <?= $user['user_id'] == $meeting['host_user_id']
                        ? 'This will end the meeting for everyone.'
                        : 'You will be disconnected from the meeting.' ?>
                </p>
                <div class="d-grid gap-2">
                    <button class="btn btn-danger btn-app" onclick="confirmEnd()">
                        <i class="fa-solid fa-phone-slash me-2"></i>
                        <?= $user['user_id'] == $meeting['host_user_id'] ? 'End for All' : 'Leave' ?>
                    </button>
                    <button class="btn btn-ghost" data-bs-dismiss="modal">Stay</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>
<script>
const MEETING_UUID  = '<?= esc($meeting['meeting_uuid']) ?>';
const MEETING_TOKEN = '<?= esc($meeting['meeting_token']) ?>';
const MEETING_ID    = <?= (int)$meeting['meeting_id'] ?>;
const USER_ID       = <?= (int)$user['user_id'] ?>;
const DISPLAY_NAME  = '<?= esc($user['fname'] . ' ' . $user['lname']) ?>';
const IS_HOST       = <?= $user['user_id'] == $meeting['host_user_id'] ? 'true' : 'false' ?>;
const HOST_USER_ID  = <?= (int)$meeting['host_user_id'] ?>;
const WAITING_ROOM      = <?= !empty($meeting['waiting_room']) ? 'true' : 'false' ?>;
const MAX_PARTICIPANTS  = <?= (int)($meeting['max_participants'] ?? 300) ?>;
const SIGNALING_URL = '<?= esc($signalingUrl) ?>';
const API_TOKEN     = '<?= esc($token) ?>';
const BASE_URL      = '<?= base_url() ?>';
const ICE_SERVERS    = { iceServers: <?= json_encode($iceServers) ?> };
const SFU_PROXY_BASE = '<?= esc($sfuProxyBase) ?>';
const USER_AVATAR    = '<?= !empty($user['profile_photo']) ? base_url(esc($user['profile_photo'])) : '' ?>';
const IS_GUEST       = <?= !empty($user['is_guest']) ? 'true' : 'false' ?>;
<?php
// Compute meeting end time from available fields
$endTime = null;
if (!empty($meeting['end_time'])) {
    $endTime = $meeting['end_time'];
} elseif (!empty($meeting['start_time']) && !empty($meeting['duration_minutes'])) {
    $endTime = date('Y-m-d H:i:s', strtotime($meeting['start_time']) + (int)$meeting['duration_minutes'] * 60);
}
?>
const MEETING_END_TIME        = <?= $endTime ? "'" . esc($endTime) . "'" : 'null' ?>;
const MEETING_DURATION_MINUTES = <?= !empty($meeting['duration_minutes']) ? (int)$meeting['duration_minutes'] : '0' ?>;
</script>
<script>
/* ── Room theme toggle (light default, dark opt-in) ───────── */
(function () {
  var saved = localStorage.getItem('room-theme') || 'light';
  if (saved === 'dark') {
    document.body.classList.add('room-dark');
    var icon = document.getElementById('themeIcon');
    if (icon) icon.className = 'fa-solid fa-sun';
  }
})();

function toggleRoomTheme() {
  var body = document.body;
  var icon = document.getElementById('themeIcon');
  if (body.classList.contains('room-dark')) {
    body.classList.remove('room-dark');
    if (icon) icon.className = 'fa-solid fa-moon';
    localStorage.setItem('room-theme', 'light');
  } else {
    body.classList.add('room-dark');
    if (icon) icon.className = 'fa-solid fa-sun';
    localStorage.setItem('room-theme', 'dark');
  }
}
</script>
<script src="<?= base_url('js/app.js') ?>"></script>
<script src="<?= base_url('js/sounds.js') ?>"></script>
<script src="<?= base_url('js/socket.js') ?>"></script>
<script src="<?= base_url('js/webrtc.js') ?>"></script>
<script src="<?= base_url('js/recorder.js') ?>"></script>
<script src="<?= base_url('js/screenshare.js') ?>"></script>
<script src="<?= base_url('js/virtual_bg.js') ?>"></script>
<script src="<?= base_url('js/room.js') ?>"></script>
<script src="<?= base_url('js/breakout.js') ?>"></script>
<script src="<?= base_url('js/whiteboard.js') ?>"></script>
<script src="<?= base_url('js/transcript.js') ?>"></script>
<script src="<?= base_url('js/ai_companion.js') ?>"></script>
</body>
</html>
