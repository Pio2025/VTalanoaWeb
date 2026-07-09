/* VTalanoa — Room Controller */

let micEnabled            = true;
let camEnabled            = true;
let screenEnabled         = false;
let timerInterval         = null;
let startTime             = null;
let meetingEndTime        = null;   // ms timestamp — null if no scheduled end
let endModal              = null;
// settingsModal removed — settings now opens as a sliding panel
let settingsPreviewStream = null;
let unreadChat            = 0;
let activePanel           = null;
let sfuSetupDone          = false;   // tracks whether first SFU session was created
let handRaised            = false;   // whether the local user has their hand raised
let currentScreenSharerId = null;    // socketId of who is currently screen sharing
let isCoHost              = false;   // becomes true when host assigns co-host role
let meetingLocked         = false;   // mirror of server lock state
let _brActiveRooms        = [];      // host's view of open breakout rooms

// Participants map: socketId → {userId, displayName, photoUrl}
const participants = {};
// Tracks which remote socketIds currently have their hand raised
const raisedHands  = new Set();
// Reconnecting peers: userId (string) → { displayName, timeoutId }
const reconnectingPeers = {};
// Set true once 'meeting-ended' is received, so subsequent peer-left events
// (the host's own disconnect, and any participants redirected away) are
// explained by that instead of being mistaken for a connectivity drop.
let _meetingEnding = false;
let _meetingEndingHostName = 'The host';
// Typing state
const _typingPeers = {};             // socketId → senderName
let   _isTyping    = false;
let   _typingTimeout = null;
// Chat log + polls
const _chatLog     = [];             // for export transcript
const _polls       = {};             // pollId → { question, options[], voted }
let   _pollCounter = 0;
// Reactions: messageId → { emoji → Set(socketId) }. Toggle-relay model: every
// client (sender included) applies the exact same (socketId, messageId,
// emoji) toggle locally and via the 'chat-reaction'/'chat-reaction-update'
// relay, so all clients converge on the same aggregate without a server-side
// source of truth (mirrors the existing ephemeral poll-vote relay).
const _reactions   = {};
let   _msgCounter  = 0;
const _REACTION_EMOJIS = ['👍', '❤️', '😂', '😮', '😢', '🙏'];
// Image gallery — all images shared in chat, for lightbox traversal
const _chatImages  = [];             // { src, name } — appended as images arrive
let   _imgLightboxIdx = 0;

/* ── Init ──────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', async () => {
  endModal = new bootstrap.Modal(document.getElementById('endModal'));

  // Read prefs from join page
  micEnabled = sessionStorage.getItem('nm_mic') !== 'false';
  camEnabled = sessionStorage.getItem('nm_cam') !== 'false';

  // Compute meeting end time once (from PHP-injected constants)
  if (typeof MEETING_END_TIME !== 'undefined' && MEETING_END_TIME) {
    meetingEndTime = new Date(MEETING_END_TIME).getTime();
  } else if (typeof MEETING_DURATION_MINUTES !== 'undefined' && MEETING_DURATION_MINUTES > 0) {
    meetingEndTime = Date.now() + MEETING_DURATION_MINUTES * 60 * 1000;
  }
  if (meetingEndTime) {
    document.getElementById('timeLeftWrap')?.style.setProperty('display', 'flex');
  }

  // Mobile keyboard: lift active panel above virtual keyboard
  if (window.visualViewport) {
    window.visualViewport.addEventListener('resize', _onViewportResize);
  }

  // Init media
  await initLocalStream();
  applyMicState();
  applyCamState();

  // Connect signaling
  const sock = connectSocket();
  bindSocketEvents(sock);

  // Set initial grid (just the local tile)
  updateGridLayout();

  // Start timer
  startTime = Date.now();
  timerInterval = setInterval(updateTimer, 1000);

  // Reflow video grid on resize
  window.addEventListener('resize', updateGridLayout);

  // Close the active panel when the user taps the backdrop
  document.getElementById('panelBackdrop')?.addEventListener('click', () => {
    if (activePanel) togglePanel(activePanel);
  });

  // Prevent device back button from navigating away from the meeting.
  // Push a history entry so popstate fires instead of an actual navigation.
  history.pushState(null, null, window.location.href);
  window.addEventListener('popstate', () => {
    history.pushState(null, null, window.location.href);
    endOrLeave(); // Show the Leave/End dialog instead
  });

  // Disable screen share button on browsers that don't support getDisplayMedia
  if (typeof _checkScreenShareSupport === 'function') _checkScreenShareSupport();

  // Chat typing detection
  const chatInputEl = document.getElementById('chatInput');
  if (chatInputEl) {
    chatInputEl.addEventListener('input', () => {
      if (!_isTyping) { _isTyping = true; emitSafe('typing-start'); }
      clearTimeout(_typingTimeout);
      _typingTimeout = setTimeout(() => { _isTyping = false; emitSafe('typing-stop'); }, 3000);
    });
    chatInputEl.addEventListener('blur', () => {
      if (_isTyping) { _isTyping = false; clearTimeout(_typingTimeout); emitSafe('typing-stop'); }
    });
  }
});

/* ── Timer ─────────────────────────────────────────────────── */
function updateTimer() {
  // Elapsed call time
  const elapsed = Math.floor((Date.now() - startTime) / 1000);
  const h = Math.floor(elapsed / 3600);
  const m = Math.floor((elapsed % 3600) / 60);
  const s = elapsed % 60;
  const str = h > 0 ? `${pad(h)}:${pad(m)}:${pad(s)}` : `${pad(m)}:${pad(s)}`;
  const t1 = document.getElementById('roomTimer');
  const t2 = document.getElementById('controlTimer');
  if (t1) t1.textContent = str;
  if (t2) t2.textContent = str;

  // Time remaining countdown
  if (meetingEndTime) {
    const remaining = Math.max(0, Math.floor((meetingEndTime - Date.now()) / 1000));
    const rh = Math.floor(remaining / 3600);
    const rm = Math.floor((remaining % 3600) / 60);
    const rs = remaining % 60;
    const rStr = rh > 0 ? `${pad(rh)}:${pad(rm)}:${pad(rs)}` : `${pad(rm)}:${pad(rs)}`;

    const remEl = document.getElementById('timeRemaining');
    if (remEl) {
      remEl.textContent = remaining > 0 ? rStr : '00:00';
      remEl.style.color = remaining < 300 ? '#ef4444'
                        : remaining < 600 ? '#f59e0b'
                        : '';
    }

    // One-shot toast warnings
    if (IS_HOST && remaining === 300) showToast('5 minutes remaining.', 'default');
    if (remaining === 60)             showToast('1 minute remaining.', 'default');
  }
}
function pad(n) { return String(n).padStart(2, '0'); }

/* ── Mobile keyboard — lift panel above virtual keyboard ─── */
function _onViewportResize() {
  if (!window.visualViewport) return;
  if (!window.matchMedia('(max-width: 767px)').matches) return;

  const keyboardH = window.innerHeight - window.visualViewport.height;
  const panelsEl  = document.querySelector('.room-panels');
  if (!panelsEl) return;

  if (keyboardH > 80) {
    // Keyboard is open — push panels container up so input stays visible
    panelsEl.style.bottom = `${keyboardH}px`;
    // Scroll chat to bottom so the newest message is visible
    const msgs = document.getElementById('chatMessages');
    if (msgs) setTimeout(() => { msgs.scrollTop = msgs.scrollHeight; }, 80);
  } else {
    // Keyboard closed — restore
    panelsEl.style.bottom = '';
  }
}

/* ── Socket Events ─────────────────────────────────────────── */
function bindSocketEvents(sock) {
  console.log('[ROOM] bindSocketEvents — socket id:', sock.id,
    '| IS_HOST:', IS_HOST, '| MEETING_UUID:', MEETING_UUID);

  sock.on('connect', () =>
    console.log('[SOCKET] Connected — id:', sock.id));
  sock.on('disconnect', reason =>
    console.log('[SOCKET] Disconnected — reason:', reason));
  sock.on('connect_error', err =>
    console.error('[SOCKET] Connection error:', err.message));

  sock.on('meeting-full', ({ limit }) => {
    console.warn('[ROOM] meeting-full — capacity:', limit);
    document.body.innerHTML = `
      <div style="position:fixed;inset:0;background:#0a0f1c;display:flex;align-items:center;justify-content:center;z-index:9999;font-family:Inter,sans-serif;">
        <div style="text-align:center;color:#fff;padding:48px 32px;max-width:400px;">
          <div style="font-size:56px;margin-bottom:20px;">🔒</div>
          <h2 style="margin:0 0 12px;font-size:24px;font-weight:700;">Meeting Full</h2>
          <p style="color:rgba(255,255,255,.6);margin:0 0 8px;">This meeting has reached its maximum capacity of <strong>${limit}</strong> participants.</p>
          <p style="color:rgba(255,255,255,.35);font-size:13px;margin-top:28px;">Redirecting you back…</p>
        </div>
      </div>`;
    setTimeout(() => { window.location.href = (typeof BASE_URL !== 'undefined' ? BASE_URL : '/'); }, 3500);
  });

  sock.on('meeting-lock-status', ({ locked }) => {
    meetingLocked = locked;
    const icon  = document.getElementById('lockIcon');
    const label = document.getElementById('lockLabel');
    const ind   = document.getElementById('lockIndicator');
    const btn   = document.getElementById('btnLock');
    if (icon)  icon.className = locked ? 'fa-solid fa-lock' : 'fa-solid fa-lock-open';
    if (label) label.textContent = locked ? 'Unlock' : 'Lock';
    btn?.classList.toggle('active', locked);
    if (ind) ind.style.display = locked ? 'flex' : 'none';
    if (locked) showToast('Meeting is now locked — no new participants can join.', 'default');
    else         showToast('Meeting unlocked.', 'default');
  });

  sock.on('meeting-locked', () => {
    document.body.innerHTML = `
      <div style="position:fixed;inset:0;background:#0a0f1c;display:flex;align-items:center;justify-content:center;z-index:9999;font-family:Inter,sans-serif;">
        <div style="text-align:center;color:#fff;padding:48px 32px;max-width:400px;">
          <div style="font-size:56px;margin-bottom:20px;">🔒</div>
          <h2 style="margin:0 0 12px;font-size:24px;font-weight:700;">Meeting Locked</h2>
          <p style="color:rgba(255,255,255,.6);margin:0 0 8px;">The host has locked this meeting. New participants cannot join right now.</p>
          <p style="color:rgba(255,255,255,.35);font-size:13px;margin-top:28px;">Redirecting you back…</p>
        </div>
      </div>`;
    setTimeout(() => { window.location.href = (typeof BASE_URL !== 'undefined' ? BASE_URL : '/'); }, 3500);
  });

  sock.on('you-are-cohost', () => {
    isCoHost = true;
    const badge = document.getElementById('cohostBadge');
    if (badge) badge.style.display = 'flex';
    _showWaitingControls(true);
    showToast('You are now a co-host.', 'success');
    renderParticipants();
  });

  sock.on('cohost-revoked-self', () => {
    isCoHost = false;
    const badge = document.getElementById('cohostBadge');
    if (badge) badge.style.display = 'none';
    if (!IS_HOST) _showWaitingControls(false);
    showToast('Your co-host role has been removed.', 'default');
    renderParticipants();
  });

  sock.on('cohost-assigned', ({ socketId }) => {
    if (participants[socketId]) participants[socketId].isCoHost = true;
    renderParticipants();
  });

  sock.on('cohost-revoked', ({ socketId }) => {
    if (participants[socketId]) participants[socketId].isCoHost = false;
    renderParticipants();
  });

  sock.on('host-denied', () => {
    console.log('[ROOM] host-denied — another session is already hosting this meeting; demoting to participant');
    _demoteFromHost();
    showToast('Another session is already hosting this meeting — you\'ve joined as a participant.', 'default');
  });

  sock.on('you-are-waiting', () => {
    console.log('[ROOM] you-are-waiting — showing waiting overlay');
    const overlay = document.getElementById('waitingOverlay');
    if (overlay) overlay.style.display = 'flex';
    ['btnMic', 'btnCam', 'btnScreen', 'btnRecord', 'btnChat', 'btnParticipants'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.disabled = true;
    });
  });

  sock.on('admitted', async ({ peers: existingPeers }) => {
    console.log('[ROOM] admitted — existing peers in room:', existingPeers.length);

    // On reconnect: clean up the old SFU session before creating a new one
    if (sfuSetupDone) {
      console.log('[ROOM] Reconnect detected — cleaning up old SFU before re-setup');
      if (typeof cleanupSfu === 'function') cleanupSfu();
      // Remove tiles for all former peers (they'll reappear via peer-sfu-ready)
      Object.keys(participants).forEach(sid => {
        if (typeof removeRemoteTile === 'function') removeRemoteTile(sid);
        delete participants[sid];
      });
    }
    sfuSetupDone = true;
    existingPeers.forEach(p =>
      console.log('  peer:', p.socketId, p.displayName,
        '| sfuSession:', p.sfuSessionId || 'NOT YET',
        '| trackNames:', JSON.stringify(p.sfuTrackNames)));

    // Hide waiting overlay and re-enable controls
    const overlay = document.getElementById('waitingOverlay');
    if (overlay) overlay.style.display = 'none';
    ['btnMic', 'btnCam', 'btnScreen', 'btnRecord', 'btnChat', 'btnParticipants'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.disabled = false;
    });

    // Register existing peers in the participants map
    existingPeers.forEach(p => {
      participants[p.socketId] = { displayName: p.displayName, userId: p.userId, photoUrl: p.photoUrl || '' };
    });
    renderParticipants();
    updateParticipantCount();

    // Connect our own session to Cloudflare SFU and publish local tracks
    try {
      console.log('[ROOM] Calling connectToSfu() …');
      const { sessionId, trackNames } = await connectToSfu();
      console.log('[ROOM] connectToSfu() done — sessionId:', sessionId, '| trackNames:', trackNames);

      // Subscribe to any peers who already have an active SFU session
      for (const p of existingPeers) {
        if (p.sfuSessionId && p.sfuTrackNames) {
          console.log('[ROOM] Subscribing to existing peer:', p.socketId);
          await subscribeToRemotePeer(
            p.socketId, p.sfuSessionId,
            p.sfuTrackNames.video, p.sfuTrackNames.audio
          );
        } else {
          console.log('[ROOM] Peer', p.socketId, 'has no SFU session yet — will subscribe via peer-sfu-ready');
        }
      }

      // Tell all room members we're ready so they can subscribe to us
      console.log('[ROOM] Emitting sfu-session-ready:', { sessionId, trackNames });
      emitSafe('sfu-session-ready', { sessionId, trackNames });
      // Broadcast current mute state so existing peers display our status correctly
      emitSafe('mute-status', { isMuted: !micEnabled });
    } catch (e) {
      console.error('[ROOM] ❌ SFU connect/subscribe failed:', e);
      showToast('Media connection failed. Please refresh.', 'error');
    }
  });

  // Another peer has published their tracks to Cloudflare SFU → subscribe to them
  sock.on('peer-sfu-ready', async ({ socketId, sessionId, trackNames }) => {
    console.log('[ROOM] peer-sfu-ready from:', socketId,
      '| sessionId:', sessionId, '| trackNames:', trackNames);
    participants[socketId] = participants[socketId] || {};
    // Ensure tile exists before we attempt the SFU subscription so the card
    // is always visible even if the subscription fails or takes time.
    if (!document.getElementById('tile-' + socketId)) {
      addRemoteTile(socketId, new MediaStream());
    }
    try {
      await subscribeToRemotePeer(socketId, sessionId, trackNames.video, trackNames.audio);
    } catch (e) {
      console.error('[ROOM] ❌ Failed to subscribe to peer', socketId, ':', e);
    }
    renderParticipants();
    updateParticipantCount();
  });

  sock.on('peer-joined', ({ socketId, userId, displayName, photoUrl }) => {
    console.log('[ROOM] peer-joined:', socketId, displayName);
    if (socketId === sock.id) return;

    // Check if this is a reconnect
    const uid = userId ? String(userId) : null;
    const reconn = uid && uid !== '0' ? reconnectingPeers[uid] : null;
    if (reconn) {
      clearTimeout(reconn.timeoutId);
      delete reconnectingPeers[uid];
      _updateReconnectingBanner();
      playSound('reconnected');
      showToast(`${displayName} reconnected`, 'success');
    } else {
      playSound('peer-join');
      showToast(`${displayName} joined`, 'default');
    }

    participants[socketId] = { displayName, userId, photoUrl: photoUrl || '' };
    addRemoteTile(socketId, new MediaStream());
    renderParticipants();
    updateParticipantCount();
  });

  sock.on('peer-left', ({ socketId, displayName, intentional }) => {
    console.log('[ROOM] peer-left:', socketId, displayName, '| intentional:', intentional);
    raisedHands.delete(socketId);
    _updateTypingIndicator(socketId, '', false);

    const pid = participants[socketId];
    const uid = pid?.userId ? String(pid.userId) : null;
    const name = displayName || pid?.displayName || 'Participant';

    if (_meetingEnding) {
      // The host already ended the meeting — the "Meeting Ended" dialog
      // explains this departure; don't imply it's a connectivity drop.
      _flashBanner(`${_meetingEndingHostName} is ending the meeting…`);
    } else if (intentional) {
      // Explicit Leave / removed-by-host / dropped-to-waiting — not a drop.
      _flashBanner(`${name} is leaving the meeting…`);
      playSound('peer-left');
    } else if (uid && uid !== '0') {
      // May be a connectivity drop — show reconnecting banner for 20 s
      const prev = reconnectingPeers[uid];
      if (prev) clearTimeout(prev.timeoutId);
      reconnectingPeers[uid] = {
        displayName: name,
        timeoutId: setTimeout(() => {
          delete reconnectingPeers[uid];
          _updateReconnectingBanner();
        }, 20000),
      };
      playSound('disconnect');
      _updateReconnectingBanner();
    } else {
      showToast(`${name} left`, 'default');
      playSound('peer-left');
    }

    removePeer(socketId);
    delete participants[socketId];
    renderParticipants();
    updateParticipantCount();
  });

  sock.on('cam-off-request', () => {
    if (!camEnabled) return;
    toggleCam();
    showToast('The host turned off your camera.', 'default');
  });

  sock.on('mute-request', () => {
    if (!micEnabled) return;
    toggleMic();
    showToast('The host muted you.', 'default');
  });

  sock.on('removed-from-meeting', () => {
    let secs = 10;
    Swal.fire({
      title: 'Removed from Meeting',
      html: `The host has removed you from this meeting.<br>You will be logged out in <b id="removeCountdown">10</b> seconds…`,
      icon: 'error',
      allowOutsideClick: false,
      allowEscapeKey: false,
      confirmButtonText: 'Leave now',
      confirmButtonColor: '#ef4444',
      didOpen: () => {
        const el = document.getElementById('removeCountdown');
        const iv = setInterval(() => {
          secs--;
          if (el) el.textContent = secs;
          if (secs <= 0) { clearInterval(iv); Swal.close(); redirectAfterMeeting(); }
        }, 1000);
        Swal.getConfirmButton().addEventListener('click', () => { clearInterval(iv); });
      },
    }).then(r => { if (r.isConfirmed) redirectAfterMeeting(); });
  });

  sock.on('dropped-to-waiting', () => {
    console.log('[ROOM] Dropped to waiting room by host');
    // Clean up SFU and remote tiles
    if (typeof cleanupSfu === 'function') cleanupSfu();
    Object.keys(participants).forEach(sid => {
      if (typeof removeRemoteTile === 'function') removeRemoteTile(sid);
      delete participants[sid];
    });
    sfuSetupDone = false;
    renderParticipants();

    // Return to waiting overlay
    const overlay = document.getElementById('waitingOverlay');
    if (overlay) overlay.style.display = 'flex';
    const msg = overlay?.querySelector('p');
    if (msg) msg.textContent = 'The host moved you back to the waiting room.';

    ['btnMic', 'btnCam', 'btnScreen', 'btnRecord', 'btnChat', 'btnParticipants'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.disabled = true;
    });
    showToast('You have been moved back to the waiting room.', 'default');
  });

  sock.on('chat-message', ({ socketId, messageId, senderName, message, timestamp, fileUrl, fileName, fileType, fileSize }) => {
    _updateTypingIndicator(socketId, '', false); // clear typing when message arrives
    appendChatMessage(socketId, senderName, message, timestamp, false, fileUrl ? { url: fileUrl, name: fileName, type: fileType, size: fileSize } : null, messageId);
    if (activePanel !== 'chat') {
      unreadChat++;
      playSound('chat');
      _showChatBadge(unreadChat);
    }
  });

  sock.on('screen-share-start', ({ socketId, displayName }) => {
    currentScreenSharerId = socketId;
    showToast(`${displayName} is sharing their screen`, 'default');
    // Auto-focus the sharer's tile (slight delay for the SFU track to arrive)
    setTimeout(() => {
      if (typeof focusTile === 'function') focusTile(socketId);
      const exitBtn = document.getElementById('spotlightExitBtn');
      if (exitBtn) exitBtn.style.display = 'none';
    }, 800);
  });

  sock.on('screen-share-stop', ({ displayName }) => {
    currentScreenSharerId = null;
    const exitBtn = document.getElementById('spotlightExitBtn');
    if (exitBtn) exitBtn.style.display = '';
    if (typeof exitSpotlight === 'function') exitSpotlight();
    hideRemoteScreenShare();
    showToast(`${displayName} stopped sharing`, 'default');
  });

  sock.on('waiting-room-update', ({ waiting }) => {
    console.log('[ROOM] waiting-room-update — count:', waiting.length,
      waiting.map(p => p.displayName));
    const badge = document.getElementById('waitingBadge');
    const prevCount = badge ? parseInt(badge.textContent || '0') : 0;

    renderWaitingList(waiting);

    ['waitingBadge', 'waitingBadge2'].forEach(id => {
      const b = document.getElementById(id);
      if (!b) return;
      b.textContent = waiting.length;
      b.classList.toggle('show', waiting.length > 0);
    });

    // Notify host when someone new enters the waiting room
    if (IS_HOST && waiting.length > prevCount) {
      const newcomer = waiting[waiting.length - 1];
      showToast(`${newcomer?.displayName || 'Someone'} is waiting to join`, 'default');
      playSound('waiting');
    }
  });

  sock.on('recording-started', ({ displayName }) => {
    showToast(`${displayName} started recording`, 'default');
    playSound('rec-start');
    if (IS_HOST) {
      const recDot = document.getElementById('recIndicator');
      if (recDot) recDot.style.display = 'flex';
    }
  });

  sock.on('recording-stopped', () => {
    playSound('rec-stop');
    const recDot = document.getElementById('recIndicator');
    if (recDot) recDot.style.display = 'none';
  });

  sock.on('peer-raise-hand', ({ socketId }) => {
    raisedHands.add(socketId);
    const name = participants[socketId]?.displayName || 'A participant';
    showToast(`✋ ${name} raised their hand`, 'default');
    playSound('hand-up');
    _updateHandBadge(socketId, true);
    renderParticipants();
  });

  sock.on('peer-lower-hand', ({ socketId }) => {
    raisedHands.delete(socketId);
    playSound('hand-down');
    _updateHandBadge(socketId, false);
    renderParticipants();
  });

  sock.on('peer-typing', ({ socketId, senderName, isTyping }) => {
    _updateTypingIndicator(socketId, senderName || '', isTyping);
  });

  sock.on('peer-mute-status', ({ socketId, isMuted }) => {
    if (participants[socketId]) participants[socketId].isMuted = isMuted;
    renderParticipants();
    if (typeof updateRemoteTileMuteStatus === 'function') updateRemoteTileMuteStatus(socketId, isMuted);
  });

  sock.on('unmute-request', async () => {
    if (micEnabled) return; // already unmuted
    const result = await Swal.fire({
      title: 'Unmute Request',
      text: 'The host is asking you to unmute your microphone.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Unmute',
      cancelButtonText: 'Stay muted',
      confirmButtonColor: '#00aeef',
      cancelButtonColor: '#64748b',
    });
    if (result.isConfirmed) toggleMic();
  });

  sock.on('poll-created', ({ pollId, question, options, creatorName }) => {
    _polls[pollId] = { question, options: options.map(o => ({ text: o, votes: 0 })), voted: -1 };
    const card = _buildPollCard(pollId, creatorName);
    const msgs = document.getElementById('chatMessages');
    if (msgs) { msgs.appendChild(card); msgs.scrollTop = msgs.scrollHeight; }
    if (activePanel !== 'chat') {
      unreadChat++;
      _showChatBadge(unreadChat);
    }
    // Floating poll popup with inline quick-vote
    _showPollNotification(pollId, question, options, creatorName);
  });

  sock.on('poll-vote-update', ({ pollId, optionIndex }) => {
    const poll = _polls[pollId];
    if (!poll) return;
    if (poll.options[optionIndex]) poll.options[optionIndex].votes++;
    _updatePollCard(pollId);
  });

  sock.on('chat-reaction-update', ({ socketId, messageId, emoji }) => {
    _applyReaction(messageId, emoji, socketId);
  });

  sock.on('peer-cam-status', ({ socketId, isCamOff }) => {
    if (participants[socketId]) participants[socketId].isCamOff = isCamOff;
    updateRemoteTileCamStatus(socketId, isCamOff);
    renderParticipants();
  });

  sock.on('remote-transcript-segment', ({ speaker, text }) => {
    if (typeof addRemoteTranscriptSegment === 'function') {
      addRemoteTranscriptSegment(speaker, text);
    }
  });

  // ── Breakout Rooms ─────────────────────────────────────────────────────

  sock.on('assigned-to-breakout', data => {
    if (typeof handleAssignedToBreakout === 'function') handleAssignedToBreakout(data);
  });

  sock.on('joined-breakout-to-observe', data => {
    if (typeof handleAssignedToBreakout === 'function') handleAssignedToBreakout(data);
  });

  sock.on('returned-to-main', data => {
    if (typeof handleReturnedToMain === 'function') handleReturnedToMain(data);
    _updateBreakoutHostUI(false, []);
  });

  sock.on('breakout-rooms-opened', ({ rooms: brs }) => {
    _brActiveRooms = brs;
    _updateBreakoutHostUI(true, brs);
    showToast(`${brs.length} breakout room${brs.length > 1 ? 's' : ''} opened.`, 'success');
  });

  sock.on('breakout-rooms-ended', () => {
    _brActiveRooms = [];
    _updateBreakoutHostUI(false, []);
  });

  sock.on('host-broadcast', ({ message }) => {
    Swal.fire({
      title: 'Message from Host',
      text: message,
      icon: 'info',
      timer: 10000,
      timerProgressBar: true,
      confirmButtonColor: '#00aeef',
      background: '#1e293b',
      color: '#fff',
    });
  });

  // ── Whiteboard ──────────────────────────────────────────────────────────

  sock.on('wb-stroke', stroke => {
    if (typeof handleRemoteWbStroke === 'function') handleRemoteWbStroke(stroke);
  });

  sock.on('wb-clear', () => {
    if (typeof handleRemoteWbClear === 'function') handleRemoteWbClear();
  });

  sock.on('wb-state', data => {
    if (typeof handleWbState === 'function') handleWbState(data);
  });

  sock.on('meeting-ended', ({ hostName } = {}) => {
    _meetingEnding = true;
    _meetingEndingHostName = hostName || 'The host';
    let secs = 10;
    Swal.fire({
      title: 'Meeting Ended',
      html: `The host has ended this meeting.<br>Redirecting in <b id="swalCountdown">10</b> seconds…`,
      icon: 'info',
      allowOutsideClick: false,
      allowEscapeKey: false,
      confirmButtonText: 'Leave now',
      confirmButtonColor: '#00aeef',
      didOpen: () => {
        const el = document.getElementById('swalCountdown');
        const iv = setInterval(() => {
          secs--;
          if (el) el.textContent = secs;
          if (secs <= 0) { clearInterval(iv); Swal.close(); redirectAfterMeeting(); }
        }, 1000);
        Swal.getConfirmButton().addEventListener('click', () => clearInterval(iv));
      },
    }).then(r => { if (r.isConfirmed) redirectAfterMeeting(); });
  });
}

/* ── Controls ──────────────────────────────────────────────── */
function toggleMic() {
  micEnabled = !micEnabled;
  applyMicState();
  emitSafe('mute-status', { isMuted: !micEnabled });
}

function applyMicState() {
  const stream = getLocalStream();
  stream?.getAudioTracks().forEach(t => { t.enabled = micEnabled; });

  const btn  = document.getElementById('btnMic');
  const icon = document.getElementById('micIcon');
  const ind  = document.getElementById('localMicIndicator');

  if (icon) icon.className = micEnabled ? 'fa-solid fa-microphone' : 'fa-solid fa-microphone-slash';
  btn?.classList.toggle('off', !micEnabled);
  if (ind) ind.style.display = micEnabled ? 'none' : 'flex';
}

function toggleCam() {
  camEnabled = !camEnabled;
  applyCamState();
  emitSafe('cam-status', { isCamOff: !camEnabled });
}

function applyCamState() {
  const stream = getLocalStream();
  stream?.getVideoTracks().forEach(t => { t.enabled = camEnabled; });
  updateLocalNoVideoOverlay();

  const btn  = document.getElementById('btnCam');
  const icon = document.getElementById('camIcon');
  if (icon) icon.className = camEnabled ? 'fa-solid fa-video' : 'fa-solid fa-video-slash';
  btn?.classList.toggle('off', !camEnabled);
}

async function toggleScreen() {
  if (screenEnabled) {
    stopScreenShare();
    screenEnabled = false;
    const btn  = document.getElementById('btnScreen');
    const icon = document.getElementById('screenIcon');
    btn?.classList.remove('active');
    if (icon) icon.className = 'fa-solid fa-display';
  } else {
    const ok = await startScreenShare();
    if (ok) {
      screenEnabled = true;
      const btn  = document.getElementById('btnScreen');
      const icon = document.getElementById('screenIcon');
      btn?.classList.add('active');
      if (icon) icon.className = 'fa-solid fa-display';
    }
  }
}

function toggleRecording() {
  if (isRecording()) {
    stopRecording();
    const btn   = document.getElementById('btnRecord');
    const label = document.getElementById('recordLabel');
    btn?.classList.remove('recording');
    if (label) label.textContent = 'Record';
    const recDot = document.getElementById('recIndicator');
    if (recDot) recDot.style.display = 'none';
    emitSafe('recording-stopped', {});
    playSound('rec-stop');
    showToast('Recording stopped — downloading…', 'default');
  } else {
    startRecording().then(ok => {
      if (!ok) return;
      const btn   = document.getElementById('btnRecord');
      const label = document.getElementById('recordLabel');
      btn?.classList.add('recording');
      if (label) label.textContent = 'Stop Rec';
      if (IS_HOST) {
        const recDot = document.getElementById('recIndicator');
        if (recDot) recDot.style.display = 'flex';
      }
      emitSafe('recording-started', { displayName: DISPLAY_NAME });
      playSound('rec-start');
      showToast('Recording started', 'default');
    });
  }
}

/* ── Reconnecting peers banner ─────────────────────────────── */
function _updateReconnectingBanner() {
  const banner = document.getElementById('peerReconnectBanner');
  const nameEl = document.getElementById('peerReconnectNames');
  if (!banner || !nameEl) return;
  const names = Object.values(reconnectingPeers).map(p => p.displayName);
  if (!names.length) { banner.style.display = 'none'; return; }
  banner.style.display = 'flex';
  nameEl.textContent = names.join(', ') + (names.length === 1 ? ' is reconnecting…' : ' are reconnecting…');
}

// Reuses the same banner slot for a one-off message (intentional leave /
// meeting ending) that isn't part of the persistent reconnectingPeers map.
let _flashBannerTimeout = null;
function _flashBanner(text, ms = 4000) {
  const banner = document.getElementById('peerReconnectBanner');
  const nameEl = document.getElementById('peerReconnectNames');
  if (!banner || !nameEl) return;
  banner.style.display = 'flex';
  nameEl.textContent = text;
  clearTimeout(_flashBannerTimeout);
  _flashBannerTimeout = setTimeout(_updateReconnectingBanner, ms);
}

/* ── Typing indicator ───────────────────────────────────────── */
function _updateTypingIndicator(socketId, name, isTyping) {
  if (isTyping && name) {
    _typingPeers[socketId] = name;
  } else {
    delete _typingPeers[socketId];
  }
  const el = document.getElementById('typingIndicator');
  if (!el) return;
  const names = Object.values(_typingPeers);
  if (!names.length) { el.style.display = 'none'; el.innerHTML = ''; return; }
  el.style.display = 'flex';
  const label = names.length === 1 ? `${names[0]} is typing…`
              : names.length === 2 ? `${names[0]} and ${names[1]} are typing…`
              : 'Several people are typing…';
  el.innerHTML = `<span class="typing-dots"><span></span><span></span><span></span></span>${label}`;
}

/* ── Raise Hand ────────────────────────────────────────────── */
function toggleHand() {
  handRaised = !handRaised;
  emitSafe(handRaised ? 'raise-hand' : 'lower-hand', {});

  _updateHandBadge('local', handRaised);

  const btn   = document.getElementById('btnHand');
  const label = document.getElementById('handLabel');
  if (btn)   btn.classList.toggle('active', handRaised);
  if (label) label.textContent = handRaised ? 'Lower Hand' : 'Hand';

  playSound(handRaised ? 'hand-up' : 'hand-down');
  showToast(handRaised ? '✋ Hand raised — the host can see this' : 'Hand lowered', 'default');
}

function _updateHandBadge(socketId, show) {
  const tileId  = socketId === 'local' ? 'localTile' : 'tile-' + socketId;
  const tile    = document.getElementById(tileId);
  if (!tile) return;

  let badge = tile.querySelector('.hand-raised-badge');
  if (!badge && show) {
    badge = document.createElement('div');
    badge.className = 'hand-raised-badge';
    badge.textContent = '✋';
    tile.appendChild(badge);
  }
  if (badge) badge.style.display = show ? 'flex' : 'none';
}

/* ── End / Leave ───────────────────────────────────────────── */
function endOrLeave() {
  endModal?.show();
}

async function confirmEnd() {
  endModal?.hide();
  if (isRecording()) stopRecording();
  screenEnabled && stopScreenShare();

  const sock = getSocket();
  clearInterval(timerInterval);

  if (IS_HOST) {
    sock?.emit('end-meeting', {});
    await new Promise(r => setTimeout(r, 600));
    try {
      await fetch(`${BASE_URL}api/meetings/${MEETING_TOKEN}/end`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Authorization': `Bearer ${API_TOKEN}` },
      });
    } catch (e) { /* ignore */ }
    sock?.disconnect();
    window.location.href = BASE_URL + 'meetings';
  } else {
    sock?.disconnect();
    redirectAfterMeeting();
  }
}

function redirectAfterMeeting() {
  window.location.href = (USER_ID === 0 || IS_GUEST)
    ? BASE_URL + 'auth/login'
    : BASE_URL + 'dashboard';
}

/* ── Panels ────────────────────────────────────────────────── */
function togglePanel(name) {
  const panels   = { waiting: 'waitingPanel', participants: 'participantsPanel', chat: 'chatPanel', settings: 'settingsPanel', ai: 'aiPanel' };
  const btns     = { waiting: 'btnWaiting', participants: 'btnParticipants', chat: 'btnChat', settings: 'btnSettings', ai: 'btnAI' };
  const mobile   = window.matchMedia('(max-width: 767px)').matches;
  const backdrop = document.getElementById('panelBackdrop');

  function closeEl(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.remove('panel-open');
    // On desktop, also hide via display; on mobile the CSS transition handles visibility
    if (!mobile) el.style.display = 'none';
  }

  function openEl(id) {
    const el = document.getElementById(id);
    if (!el) return;
    // On desktop, set display first so the element is in flow before class change
    if (!mobile) el.style.display = 'flex';
    el.offsetHeight; // force reflow so CSS transition fires from the initial state
    el.classList.add('panel-open');
  }

  const mobileBtns = { waiting: 'mbWaiting', participants: 'mbPeople', chat: 'mbChat', settings: 'mbSettings', ai: 'mbAI' };

  const roomPanelsEl = document.querySelector('.room-panels');

  if (activePanel === name) {
    // Settings panel cleanup — stop preview stream
    if (name === 'settings') {
      settingsPreviewStream?.getTracks().forEach(t => t.stop());
      settingsPreviewStream = null;
      const prev = document.getElementById('settingsPreview');
      if (prev) prev.srcObject = null;
    }
    closeEl(panels[name]);
    document.getElementById(btns[name])?.classList.remove('panel-active');
    document.getElementById(mobileBtns[name])?.classList.remove('active');
    if (backdrop) backdrop.classList.remove('active');
    if (roomPanelsEl) roomPanelsEl.classList.remove('panel-is-open');
    activePanel = null;
    return;
  }

  // Close all panels first
  Object.keys(panels).forEach(k => {
    closeEl(panels[k]);
    document.getElementById(btns[k])?.classList.remove('panel-active');
    document.getElementById(mobileBtns[k])?.classList.remove('active');
  });

  // Open requested panel
  openEl(panels[name]);
  document.getElementById(btns[name])?.classList.add('panel-active');
  document.getElementById(mobileBtns[name])?.classList.add('active');
  if (backdrop && mobile) backdrop.classList.add('active');
  if (roomPanelsEl) roomPanelsEl.classList.add('panel-is-open');
  activePanel = name;

  if (name === 'chat') {
    unreadChat = 0;
    ['chatBadge', 'chatBadge2'].forEach(id => {
      document.getElementById(id)?.classList.remove('show');
    });
    const msgs = document.getElementById('chatMessages');
    if (msgs) msgs.scrollTop = msgs.scrollHeight;
  }

  if (name === 'settings') {
    populateDevices();
    // Start camera preview
    const deviceId = document.getElementById('settingsCam')?.value;
    navigator.mediaDevices.getUserMedia({ video: deviceId ? { deviceId: { exact: deviceId } } : true })
      .then(stream => {
        settingsPreviewStream = stream;
        const prev = document.getElementById('settingsPreview');
        if (prev) prev.srcObject = stream;
      }).catch(() => {});
  }
}

/* ── Chat ──────────────────────────────────────────────────── */
function sendChat() {
  const input = document.getElementById('chatInput');
  const msg   = input?.value.trim();
  if (!msg) return;

  const messageId = `msg_${Date.now()}_${++_msgCounter}`;
  emitSafe('chat-message', { messageId, message: msg });
  appendChatMessage(null, DISPLAY_NAME + ' (You)', msg, new Date().toISOString(), true, null, messageId);
  input.value = '';
  // Dismiss virtual keyboard on mobile so iOS doesn't stay zoomed in after send
  if (window.matchMedia('(max-width: 768px)').matches) input.blur();
}

function chatKeydown(e) {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault();
    sendChat();
  }
}

async function handleFileSelect(input) {
  const file = input.files[0];
  if (!file) return;

  const MAX_MB = 10;
  if (file.size > MAX_MB * 1024 * 1024) {
    showToast(`File too large. Maximum ${MAX_MB} MB.`, 'error');
    input.value = '';
    return;
  }

  // Show an in-chat uploading bubble so the main screen is not disturbed
  const msgs = document.getElementById('chatMessages');
  const bubble = document.createElement('div');
  bubble.className = 'chat-uploading-bubble';
  bubble.innerHTML = `
    <span class="chat-msg-sender">${escapeHtml(DISPLAY_NAME)} (You)</span>
    <div class="chat-msg-text">
      <span class="chat-upload-spinner"></span>
      <span class="chat-uploading-name" title="${escapeHtml(file.name)}">${escapeHtml(file.name)}</span>
      <span class="chat-uploading-label">Uploading…</span>
    </div>`;
  if (msgs) {
    msgs.appendChild(bubble);
    msgs.scrollTop = msgs.scrollHeight;
  }

  const formData = new FormData();
  formData.append('file', file);
  formData.append('meeting_uuid', MEETING_UUID);

  try {
    const res = await fetch(`${BASE_URL}api/chat/upload`, {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${API_TOKEN}` },
      body: formData,
    });

    let data = {};
    const ct = res.headers.get('content-type') || '';
    if (ct.includes('application/json')) {
      data = await res.json();
    } else {
      const text = await res.text();
      console.error('[Upload] Non-JSON response:', res.status, text.slice(0, 300));
      data.error = `Server error ${res.status}`;
    }

    bubble.remove();

    if (!res.ok) {
      showToast(data.error || `Upload failed (${res.status})`, 'error');
      input.value = '';
      return;
    }

    const messageId = `msg_${Date.now()}_${++_msgCounter}`;
    emitSafe('chat-message', {
      messageId,
      message:  '',
      fileUrl:  data.url,
      fileName: data.name,
      fileType: data.type,
      fileSize: data.size,
    });

    appendChatMessage(null, DISPLAY_NAME + ' (You)', '', new Date().toISOString(), true,
      { url: data.url, name: data.name, type: data.type, size: data.size }, messageId);

    showToast('File sent.', 'success');
  } catch (e) {
    bubble.remove();
    showToast('Upload failed — check your connection.', 'error');
    console.error('[Upload] Error:', e);
  }

  input.value = '';
}

function appendChatMessage(socketId, senderName, message, timestamp, isOwn, file = null, messageId = null) {
  const container = document.getElementById('chatMessages');
  if (!container) return;

  const time = timestamp ? new Date(timestamp).toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'}) : '';

  // Record for export
  _chatLog.push({
    time,
    sender: isOwn ? DISPLAY_NAME + ' (You)' : (senderName || 'Guest'),
    text: message || '',
    file: file?.name || null,
  });

  const item = document.createElement('div');
  item.className = 'chat-message-item' + (isOwn ? ' own' : '');
  if (messageId) item.id = 'msg-' + messageId;

  let bodyHtml = '';
  if (message) {
    bodyHtml += `<div class="chat-msg-text">${linkifyText(message)}</div>`;
  }
  if (file) {
    const isImage = file.type?.startsWith('image/');
    if (isImage) {
      // Register in gallery so the lightbox can traverse all chat images
      const imgIdx = _chatImages.push({ src: file.url, name: file.name }) - 1;
      bodyHtml += `<img src="${file.url}" class="chat-img-preview"
        onclick="openImgLightbox(${imgIdx})" alt="${escapeHtml(file.name)}"
        data-img-idx="${imgIdx}">`;
    } else {
      bodyHtml += buildFileHtml(file);
    }
  }

  const reactBtnHtml = messageId ? `
    <button class="chat-react-trigger" onclick="toggleReactPicker('${messageId}', this)" title="React">
      <i class="fa-regular fa-face-smile"></i>
    </button>` : '';

  item.innerHTML = `
    <div class="chat-msg-sender">${isOwn ? 'You' : escapeHtml(senderName)}</div>
    <div class="chat-msg-row">
      ${bodyHtml}
      ${reactBtnHtml}
    </div>
    ${messageId ? `<div class="chat-reactions" id="reactions-${messageId}"></div>` : ''}
    <div class="chat-msg-time">${time}</div>
  `;

  container.appendChild(item);
  container.scrollTop = container.scrollHeight;
}

/* ── Reactions ─────────────────────────────────────────────── */
function _applyReaction(messageId, emoji, socketId) {
  if (!messageId || !emoji || !socketId) return;
  const bucket = _reactions[messageId] || (_reactions[messageId] = {});
  const set = bucket[emoji] || (bucket[emoji] = new Set());
  if (set.has(socketId)) set.delete(socketId); else set.add(socketId);
  if (set.size === 0) delete bucket[emoji];
  _renderReactions(messageId);
}

function reactToMessage(messageId, emoji) {
  _applyReaction(messageId, emoji, sock.id);
  emitSafe('chat-reaction', { messageId, emoji });
  document.getElementById('reactPicker-' + messageId)?.remove();
}

function _renderReactions(messageId) {
  const el = document.getElementById('reactions-' + messageId);
  if (!el) return;
  const bucket = _reactions[messageId] || {};
  const mine = sock?.id;
  el.innerHTML = Object.entries(bucket)
    .filter(([, set]) => set.size > 0)
    .map(([emoji, set]) => `
      <button class="reaction-pill${set.has(mine) ? ' mine' : ''}"
              onclick="reactToMessage('${messageId}','${emoji}')">
        ${emoji} <span>${set.size}</span>
      </button>`)
    .join('');
}

function toggleReactPicker(messageId, btn) {
  const existing = document.getElementById('reactPicker-' + messageId);
  if (existing) { existing.remove(); return; }
  document.querySelectorAll('.reaction-picker-mini').forEach(p => p.remove());

  const picker = document.createElement('div');
  picker.className = 'reaction-picker-mini';
  picker.id = 'reactPicker-' + messageId;
  picker.innerHTML = _REACTION_EMOJIS
    .map(em => `<button onclick="reactToMessage('${messageId}','${em}')">${em}</button>`)
    .join('');
  btn.closest('.chat-msg-row')?.appendChild(picker);

  // Close on next outside click
  setTimeout(() => {
    document.addEventListener('click', function onDocClick(e) {
      if (!picker.contains(e.target) && e.target !== btn) {
        picker.remove();
        document.removeEventListener('click', onDocClick);
      }
    });
  }, 0);
}

function buildFileHtml(file) {
  const isImage = file.type?.startsWith('image/');
  if (isImage) {
    return `<img src="${file.url}" class="chat-img-preview"
              onclick="openImgLightbox(this.src)" alt="${escapeHtml(file.name)}">`;
  }

  const iconMap = {
    'application/pdf':                   ['fa-file-pdf',        '#ef4444'],
    'application/msword':                ['fa-file-word',       '#2563eb'],
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                                         ['fa-file-word',       '#2563eb'],
    'application/vnd.ms-powerpoint':     ['fa-file-powerpoint', '#ea580c'],
    'application/vnd.openxmlformats-officedocument.presentationml.presentation':
                                         ['fa-file-powerpoint', '#ea580c'],
    'application/vnd.ms-excel':          ['fa-file-excel',      '#16a34a'],
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
                                         ['fa-file-excel',      '#16a34a'],
  };
  const [icon, color] = iconMap[file.type] ?? ['fa-file', '#94a3b8'];

  return `
    <div class="chat-file-attachment">
      <div class="chat-file-icon"><i class="fa-solid ${icon}" style="color:${color}"></i></div>
      <div class="chat-file-info">
        <div class="chat-file-name">${escapeHtml(file.name)}</div>
        <div class="chat-file-size">${formatFileSize(file.size)}</div>
      </div>
      <a href="${file.url}" target="_blank" download="${escapeHtml(file.name)}" class="chat-file-download">
        <i class="fa-solid fa-download"></i>
      </a>
    </div>`;
}

function formatFileSize(bytes) {
  if (!bytes) return '';
  if (bytes < 1024)       return bytes + ' B';
  if (bytes < 1048576)    return (bytes / 1024).toFixed(1) + ' KB';
  return (bytes / 1048576).toFixed(1) + ' MB';
}

function escapeHtml(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

function linkifyText(str) {
  const urlRe = /(https?:\/\/[^\s<>"]+)/g;
  return String(str).split(urlRe).map((part, i) => {
    if (i % 2 === 1) {
      const safe = escapeHtml(part);
      return `<a href="${safe}" target="_blank" rel="noopener noreferrer" class="chat-link">${safe}</a>`;
    }
    return escapeHtml(part);
  }).join('');
}

/* ── Image Lightbox Gallery — keeps meeting connected on mobile ─ */
// Opens the in-page gallery at `idx` (index into _chatImages[]).
// Supports prev/next buttons, left/right arrow keys, and swipe on mobile.
function openImgLightbox(idx) {
  const lb = document.getElementById('imgLightbox');
  if (!lb) return;
  _imgLightboxIdx = Math.max(0, Math.min(idx, _chatImages.length - 1));
  _renderImgLightbox();
  lb.style.display = 'flex';
  document.addEventListener('keydown', _lbKeyClose);
}

function closeImgLightbox() {
  const lb = document.getElementById('imgLightbox');
  if (lb) lb.style.display = 'none';
  document.removeEventListener('keydown', _lbKeyClose);
}

function prevImgLightbox() {
  if (_imgLightboxIdx > 0) { _imgLightboxIdx--; _renderImgLightbox(); }
}

function nextImgLightbox() {
  if (_imgLightboxIdx < _chatImages.length - 1) { _imgLightboxIdx++; _renderImgLightbox(); }
}

function _renderImgLightbox() {
  const entry = _chatImages[_imgLightboxIdx];
  if (!entry) return;
  const img     = document.getElementById('imgLightboxImg');
  const counter = document.getElementById('imgLightboxCounter');
  const prev    = document.getElementById('imgLightboxPrev');
  const next    = document.getElementById('imgLightboxNext');
  if (img)     img.src = entry.src;
  if (counter) counter.textContent = `${_imgLightboxIdx + 1} / ${_chatImages.length}`;
  if (prev)    prev.disabled = (_imgLightboxIdx <= 0);
  if (next)    next.disabled = (_imgLightboxIdx >= _chatImages.length - 1);
}

let _lbTouchStartX = 0;
function _lbTouchStart(e) { _lbTouchStartX = e.touches[0]?.clientX ?? 0; }
function _lbTouchEnd(e) {
  const dx = (e.changedTouches[0]?.clientX ?? 0) - _lbTouchStartX;
  if (Math.abs(dx) > 50) { dx > 0 ? prevImgLightbox() : nextImgLightbox(); }
}

function _lbKeyClose(e) {
  if (e.key === 'Escape')      closeImgLightbox();
  if (e.key === 'ArrowLeft')   prevImgLightbox();
  if (e.key === 'ArrowRight')  nextImgLightbox();
}

/* ── Waiting Room ──────────────────────────────────────────── */
function renderWaitingList(waiting) {
  const list = document.getElementById('waitingList');
  if (!list) return;

  if (!waiting.length) {
    list.innerHTML = '<div class="panel-empty">No one is waiting</div>';
    return;
  }

  list.innerHTML = waiting.map(p => {
    const initial = escapeHtml(p.displayName.charAt(0).toUpperCase());
    const avatarHtml = p.photoUrl
      ? `<img src="${p.photoUrl}" class="waiting-avatar-img" alt="${initial}" onerror="this.outerHTML='<div class=\\'waiting-avatar\\'>${initial}</div>'">`
      : `<div class="waiting-avatar">${initial}</div>`;
    return `
      <div class="waiting-item">
        ${avatarHtml}
        <div class="waiting-name">${escapeHtml(p.displayName)}</div>
        <div class="waiting-actions">
          <button class="btn-admit" onclick="admitParticipant('${escapeHtml(p.socketId)}')">Admit</button>
          <button class="btn-remove-waiting" onclick="removeParticipantPermanent('${escapeHtml(p.socketId)}','${escapeHtml(p.displayName)}')">Remove</button>
        </div>
      </div>`;
  }).join('');
}

function admitParticipant(socketId) {
  console.log('[ROOM] Admitting participant:', socketId);
  emitSafe('admit-participant', { socketId });
}

function admitAll() {
  emitSafe('admit-all', {});
}

/* ── Participants ──────────────────────────────────────────── */
function renderParticipants() {
  const list = document.getElementById('participantsList');
  if (!list) return;

  const selfInitials = DISPLAY_NAME.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
  const selfAvatarHtml = (typeof USER_AVATAR !== 'undefined' && USER_AVATAR)
    ? `<img src="${USER_AVATAR}" class="participant-avatar-img" alt="${escapeHtml(selfInitials)}" onerror="this.outerHTML='<div class=\\'participant-avatar\\'>${escapeHtml(selfInitials)}</div>'">`
    : `<div class="participant-avatar">${escapeHtml(selfInitials)}</div>`;
  const selfRole = IS_HOST ? 'Host' : isCoHost ? 'Co-host' : 'Attendee';
  const selfItem = `
    <div class="participant-item">
      ${selfAvatarHtml}
      <div class="participant-info">
        <div class="participant-name">${escapeHtml(DISPLAY_NAME)} (You)</div>
        <div class="participant-role">${selfRole}</div>
      </div>
      <div class="participant-indicators">
        <i class="fa-solid ${micEnabled ? 'fa-microphone' : 'fa-microphone-slash'} p-indicator ${micEnabled ? '' : 'muted'}"></i>
      </div>
    </div>`;

  const mySocketId = getSocketId();

  const peerItems = Object.entries(participants)
    .filter(([sid, p]) => {
      // Never show self in the peer list (guard against duplicate via reconnect or race)
      if (mySocketId && sid === mySocketId) return false;
      if (USER_ID !== 0 && Number(p.userId) === Number(USER_ID)) return false;
      return true;
    })
    .map(([sid, p]) => {
      const initials = p.displayName.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2) || p.displayName.charAt(0).toUpperCase();
      const isHostPeer  = HOST_USER_ID > 0 && Number(p.userId) === Number(HOST_USER_ID);
      const roleBadge   = isHostPeer ? ' <span class="badge-host">Host</span>'
                        : p.isCoHost ? ' <span class="badge-cohost">Co-host</span>'
                        : '';
      const roleLabel   = isHostPeer ? 'Host' : p.isCoHost ? 'Co-host' : 'Attendee';
      const hasHand     = raisedHands.has(sid);
      const canManage   = IS_HOST || isCoHost;
      const pAvatarHtml = p.photoUrl
        ? `<img src="${p.photoUrl}" class="participant-avatar-img" alt="${escapeHtml(initials)}" onerror="this.outerHTML='<div class=\\'participant-avatar\\'>${escapeHtml(initials)}</div>'">`
        : `<div class="participant-avatar">${escapeHtml(initials)}</div>`;
      return `
        <div class="participant-item" id="pitem-${sid}">
          ${pAvatarHtml}
          <div class="participant-info">
            <div class="participant-name">
              ${hasHand ? '<span class="hand-raised-indicator" title="Hand raised">✋</span> ' : ''}
              ${escapeHtml(p.displayName)}${roleBadge}
            </div>
            <div class="participant-role">${roleLabel}</div>
          </div>
          <div class="participant-indicators">
            <i class="fa-solid ${p.isMuted ? 'fa-microphone-slash' : 'fa-microphone'} participant-mic-status${p.isMuted ? ' muted' : ''}" title="${p.isMuted ? 'Muted' : 'Unmuted'}"></i>
            ${canManage && !isHostPeer ? `
              <button class="btn btn-xs btn-outline-secondary me-1" onclick="${p.isMuted ? `requestUnmute('${sid}')` : `muteParticipant('${sid}')`}" title="${p.isMuted ? 'Ask to unmute' : 'Mute'}">
                <i class="fa-solid ${p.isMuted ? 'fa-microphone' : 'fa-microphone-slash'}"></i>
              </button>
              ${!p.isCamOff ? `
                <button class="btn btn-xs btn-outline-secondary me-1" onclick="requestCamOff('${sid}')" title="Turn off camera">
                  <i class="fa-solid fa-video-slash"></i>
                </button>
              ` : ''}
              <button class="btn btn-xs btn-outline-warning me-1" onclick="dropParticipant('${sid}')" title="Move to waiting room">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
              </button>
            ` : ''}
            ${IS_HOST && !isHostPeer ? `
              <button class="btn btn-xs ${p.isCoHost ? 'btn-outline-danger' : 'btn-outline-info'} me-1"
                onclick="${p.isCoHost ? `revokeCohost('${sid}')` : `assignCohost('${sid}')`}"
                title="${p.isCoHost ? 'Remove co-host' : 'Make co-host'}">
                <i class="fa-solid ${p.isCoHost ? 'fa-shield-xmark' : 'fa-shield-halved'}"></i>
              </button>
            ` : ''}
          </div>
        </div>`;
    }).join('');

  list.innerHTML = selfItem + peerItems;
}

function updateParticipantCount() {
  const count = Object.keys(participants).length + 1;
  const el1 = document.getElementById('participantCount');
  const el2 = document.getElementById('pCount');
  if (el1) el1.textContent = count;
  if (el2) el2.textContent = count;
}

function muteParticipant(socketId) {
  emitSafe('mute-request', { to: socketId });
}

function requestUnmute(socketId) {
  emitSafe('unmute-request', { to: socketId });
}

function requestCamOff(socketId) {
  emitSafe('cam-off-request', { to: socketId });
}

async function dropParticipant(socketId) {
  const name = participants[socketId]?.displayName || 'this participant';
  const result = await Swal.fire({
    title: 'Move to Waiting Room?',
    text: `${name} will be sent back to the waiting room.`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#f59e0b',
    cancelButtonColor: '#64748b',
    confirmButtonText: 'Move to Waiting',
    cancelButtonText: 'Cancel',
    reverseButtons: true,
  });
  if (!result.isConfirmed) return;
  emitSafe('drop-to-waiting', { socketId });
}

async function removeParticipantPermanent(socketId, displayName) {
  const name = displayName || participants[socketId]?.displayName || 'this participant';
  const result = await Swal.fire({
    title: 'Remove from Meeting?',
    text: `${name} will be permanently removed.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#64748b',
    confirmButtonText: 'Yes, remove',
    cancelButtonText: 'Cancel',
    reverseButtons: true,
  });
  if (!result.isConfirmed) return;
  emitSafe('remove-participant', { socketId });
}

function toggleLockMeeting() {
  emitSafe(meetingLocked ? 'unlock-meeting' : 'lock-meeting', {});
}

function assignCohost(socketId) {
  emitSafe('assign-cohost', { socketId });
}

function revokeCohost(socketId) {
  emitSafe('revoke-cohost', { socketId });
}

function _showWaitingControls(show) {
  ['btnWaiting', 'mbWaiting'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.style.display = show ? '' : 'none';
  });
}

// Called when the server denies a claimed host status because another
// session is already live-hosting this meeting — strips the host-only
// controls that were rendered server-side from the static (and, in this
// case, stale) user_id == host_user_id check.
function _demoteFromHost() {
  IS_HOST = false;
  _showWaitingControls(false);
  ['btnRecord', 'btnLock', 'btnBreakout', 'brRoomsBar'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
  });
  document.querySelectorAll('#localTile .badge-host').forEach(el => el.remove());
  const endLabel = document.querySelector('#btnEnd .ctrl-label');
  if (endLabel) endLabel.textContent = 'Leave';
  const endModalTitle  = document.getElementById('endModalTitle');
  const endModalBody   = document.getElementById('endModalBody');
  const endModalAction = document.getElementById('endModalAction');
  if (endModalTitle)  endModalTitle.textContent  = 'Leave Meeting?';
  if (endModalBody)   endModalBody.textContent   = 'You will be disconnected from the meeting.';
  if (endModalAction) endModalAction.textContent = 'Leave';
}

/* ── Settings ──────────────────────────────────────────────── */
async function openSettings() {
  togglePanel('settings');
}

async function populateDevices() {
  try {
    const devices  = await navigator.mediaDevices.enumerateDevices();
    const mics     = devices.filter(d => d.kind === 'audioinput');
    const cams     = devices.filter(d => d.kind === 'videoinput');
    const speakers = devices.filter(d => d.kind === 'audiooutput');

    document.getElementById('settingsMic').innerHTML =
      mics.map((d, i) => `<option value="${d.deviceId}">${d.label || 'Microphone ' + (i + 1)}</option>`).join('');

    document.getElementById('settingsCam').innerHTML =
      cams.map((d, i) => `<option value="${d.deviceId}">${d.label || 'Camera ' + (i + 1)}</option>`).join('');

    const speakerGroup = document.getElementById('speakerGroup');
    if (speakers.length && 'setSinkId' in HTMLMediaElement.prototype) {
      document.getElementById('settingsSpeaker').innerHTML =
        speakers.map((d, i) => `<option value="${d.deviceId}">${d.label || 'Speaker ' + (i + 1)}</option>`).join('');
    } else {
      if (speakerGroup) speakerGroup.style.display = 'none';
    }
  } catch(e) {
    console.warn('[Settings] Device enumeration failed:', e);
  }
}

function applySpeakerDevice(deviceId) {
  document.querySelectorAll('.remote-tile video').forEach(v => {
    if ('setSinkId' in v) v.setSinkId(deviceId).catch(() => {});
  });
}

function applyVolume(value) {
  document.getElementById('volumeVal').textContent = value + '%';
  document.querySelectorAll('.remote-tile video').forEach(v => { v.volume = value / 100; });
}

/* ── Utility ───────────────────────────────────────────────── */
function copyMeetingLink() {
  const url = `${BASE_URL}join/${MEETING_TOKEN}`;
  navigator.clipboard.writeText(url).then(() => showToast('Meeting link copied!', 'success'));
}

/* ── Emoji Picker ──────────────────────────────────────────── */
const _EMOJIS = [
  '😀','😂','😍','🥰','😎','🤔','😮','😢','😡','🥳',
  '👍','👎','👏','🙌','🤝','💪','✌️','👋','🤞','🙏',
  '❤️','💙','💚','💛','🧡','💜','🔥','⭐','✅','❌',
  '🎉','🎊','🎤','📝','💡','📌','❓','❗','🚀','💻',
];

function toggleEmojiPanel() {
  const panel = document.getElementById('emojiPanel');
  if (!panel) return;
  const isOpen = panel.classList.toggle('open');
  if (isOpen && !panel.childElementCount) {
    // Populate on first open
    _EMOJIS.forEach(em => {
      const btn = document.createElement('button');
      btn.className = 'emoji-btn-item';
      btn.textContent = em;
      btn.onclick = () => insertEmoji(em);
      panel.appendChild(btn);
    });
  }
}

function insertEmoji(emoji) {
  const input = document.getElementById('chatInput');
  if (!input) return;
  const s = input.selectionStart ?? input.value.length;
  const e = input.selectionEnd   ?? input.value.length;
  input.value = input.value.slice(0, s) + emoji + input.value.slice(e);
  input.selectionStart = input.selectionEnd = s + emoji.length;
  input.focus();
}

/* ── Polling ───────────────────────────────────────────────── */
function showPollForm() {
  const msgs = document.getElementById('chatMessages');
  if (!msgs) return;
  const existing = document.getElementById('pollCreateForm');
  if (existing) { existing.remove(); return; }
  const form = document.createElement('div');
  form.id = 'pollCreateForm';
  form.className = 'poll-create-form';
  form.innerHTML = `
    <div style="font-size:12px;font-weight:700;color:#fff;margin-bottom:8px;">
      <i class="fa-solid fa-chart-bar me-1"></i>Create a Poll
    </div>
    <input id="pollQuestion" type="text" placeholder="Ask a question…" maxlength="200">
    <input id="pollOpt1" type="text" placeholder="Option 1" maxlength="100">
    <input id="pollOpt2" type="text" placeholder="Option 2" maxlength="100">
    <input id="pollOpt3" type="text" placeholder="Option 3 (optional)" maxlength="100">
    <input id="pollOpt4" type="text" placeholder="Option 4 (optional)" maxlength="100">
    <div style="display:flex;gap:6px;margin-top:4px;">
      <button class="btn-submit-poll" onclick="submitPoll()">Send Poll</button>
      <button class="btn-cancel-poll" onclick="document.getElementById('pollCreateForm')?.remove()">Cancel</button>
    </div>`;
  msgs.appendChild(form);
  msgs.scrollTop = msgs.scrollHeight;
  document.getElementById('pollQuestion')?.focus();
}

function submitPoll() {
  const question = document.getElementById('pollQuestion')?.value.trim();
  const opts = [1, 2, 3, 4]
    .map(i => document.getElementById('pollOpt' + i)?.value.trim())
    .filter(Boolean);
  if (!question)      { showToast('Please enter a question.', 'error'); return; }
  if (opts.length < 2){ showToast('Add at least 2 options.', 'error'); return; }

  const pollId = `poll_${Date.now()}_${++_pollCounter}`;
  document.getElementById('pollCreateForm')?.remove();

  emitSafe('poll-create', { pollId, question, options: opts });

  _polls[pollId] = { question, options: opts.map(o => ({ text: o, votes: 0 })), voted: -1 };
  const card = _buildPollCard(pollId, DISPLAY_NAME + ' (You)');
  const msgs = document.getElementById('chatMessages');
  if (msgs) { msgs.appendChild(card); msgs.scrollTop = msgs.scrollHeight; }
}

function votePoll(pollId, optionIndex) {
  const poll = _polls[pollId];
  if (!poll || poll.voted >= 0) return;
  poll.voted = optionIndex;
  poll.options[optionIndex].votes++;
  emitSafe('poll-vote', { pollId, optionIndex });
  _updatePollCard(pollId);
}

function _buildPollCard(pollId, creatorName) {
  const card = document.createElement('div');
  card.className = 'poll-card';
  card.id = 'poll-' + pollId;
  const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  _chatLog.push({ time, sender: creatorName || 'Participant', text: '', pollId });
  _renderPollCard(card, pollId, creatorName);
  return card;
}

function _updatePollCard(pollId) {
  const card = document.getElementById('poll-' + pollId);
  if (card) _renderPollCard(card, pollId, null);
}

function _renderPollCard(card, pollId, creatorName) {
  const poll = _polls[pollId];
  if (!poll) return;
  const total = poll.options.reduce((s, o) => s + o.votes, 0);
  const optsHtml = poll.options.map((o, i) => {
    const pct     = total > 0 ? Math.round(o.votes / total * 100) : 0;
    const isVoted = poll.voted === i;
    return `
      <div class="poll-option">
        <button class="poll-option-btn${isVoted ? ' voted' : ''}"
                onclick="votePoll('${escapeHtml(pollId)}',${i})"
                ${poll.voted >= 0 ? 'disabled' : ''}>
          <div class="poll-bar" style="width:${pct}%"></div>
          <div class="poll-label">
            <span>${escapeHtml(o.text)}</span>
            <span>${pct}% (${o.votes})</span>
          </div>
        </button>
      </div>`;
  }).join('');
  card.innerHTML = `
    ${creatorName ? `<div class="chat-msg-sender">${escapeHtml(creatorName)}</div>` : ''}
    <div class="poll-question">${escapeHtml(poll.question)}</div>
    ${optsHtml}
    <div class="poll-meta">${total} vote${total !== 1 ? 's' : ''}${poll.voted >= 0 ? ' · You voted' : ''}</div>`;
}

/* ── Badge helper ──────────────────────────────────────────── */
function _showChatBadge(count) {
  ['chatBadge', 'chatBadge2'].forEach(id => {
    const b = document.getElementById(id);
    if (!b) return;
    b.textContent = count;
    b.classList.add('show');
  });
}

/* ── Breakout Host UI helpers ──────────────────────────────── */
function _updateBreakoutHostUI(active, brs) {
  // The bar contains End Rooms + Broadcast + room list; toggle it as a whole
  const bar = document.getElementById('brRoomsBar');
  if (bar) bar.style.display = active ? 'flex' : 'none';

  // The open button hides while rooms are active
  const openBtn = document.getElementById('btnBreakout');
  if (openBtn) openBtn.style.display = active ? 'none' : '';

  const list = document.getElementById('brRoomsList');
  if (!list) return;
  if (!active || !brs.length) { list.innerHTML = ''; return; }
  list.innerHTML = brs.map(r => `
    <div class="br-room-item">
      <span><i class="fa-solid fa-door-open me-1"></i>${escapeHtml(r.name)}</span>
      <span class="br-room-count">${r.count} person${r.count !== 1 ? 's' : ''}</span>
      <button class="btn-observe-br" onclick="joinBreakoutToObserve('${r.roomKey}')">
        <i class="fa-solid fa-eye me-1"></i>Observe
      </button>
    </div>`).join('');
}

/* ── Poll notification popup ───────────────────────────────── */
function _showPollNotification(pollId, question, options, creatorName) {
  const btnsHtml = options.map((opt, i) =>
    `<button class="poll-notif-btn"
       onclick="_quickVotePoll('${escapeHtml(pollId)}',${i},this)">
       ${escapeHtml(opt)}
     </button>`
  ).join('');
  Swal.fire({
    title: '<i class="fa-solid fa-chart-bar me-2" style="color:#00aeef;font-size:18px"></i>Live Poll',
    html: `
      <p style="color:rgba(255,255,255,.55);font-size:12px;margin:0 0 10px">
        from <strong>${escapeHtml(creatorName || 'Participant')}</strong>
      </p>
      <p style="font-size:15px;font-weight:600;margin:0 0 14px;color:#fff">${escapeHtml(question)}</p>
      <div style="display:flex;flex-direction:column;gap:8px">${btnsHtml}</div>`,
    background: '#1e293b',
    color: '#fff',
    showConfirmButton: false,
    showCancelButton: true,
    cancelButtonText: 'Dismiss',
    cancelButtonColor: '#64748b',
    timer: 30000,
    timerProgressBar: true,
  });
}

function _quickVotePoll(pollId, optionIndex) {
  votePoll(pollId, optionIndex);
  Swal.close();
  showToast('Vote recorded!', 'success');
}

/* ── Export Chat Transcript ────────────────────────────────── */
function exportChatTranscript() {
  const hasPoll = Object.keys(_polls).length > 0;
  if (!_chatLog.length && !hasPoll) { showToast('No messages to export yet.', 'default'); return; }

  const header = `VTalanoa Chat Transcript\nMeeting: ${MEETING_UUID}\nExported: ${new Date().toLocaleString()}\n${'─'.repeat(50)}\n\n`;

  const lines = _chatLog.map(m => {
    if (m.pollId) {
      const poll = _polls[m.pollId];
      return `[${m.time}] ${m.sender} created a poll: "${poll ? poll.question : ''}"`;
    }
    return `[${m.time}] ${m.sender}: ${m.text || (m.file ? '[File: ' + m.file + ']' : '')}`;
  });

  let pollSection = '';
  if (hasPoll) {
    pollSection = `\n\n${'─'.repeat(50)}\nPoll Results\n${'─'.repeat(50)}`;
    Object.values(_polls).forEach(poll => {
      const total = poll.options.reduce((s, o) => s + o.votes, 0);
      pollSection += `\n\n"${poll.question}"\n`;
      poll.options.forEach((o, i) => {
        const pct = total > 0 ? Math.round(o.votes / total * 100) : 0;
        pollSection += `  ${i + 1}. ${o.text} — ${o.votes} vote${o.votes !== 1 ? 's' : ''} (${pct}%)\n`;
      });
      pollSection += `  Total: ${total} vote${total !== 1 ? 's' : ''}`;
    });
  }

  const blob = new Blob([header + lines.join('\n') + pollSection], { type: 'text/plain' });
  const url  = URL.createObjectURL(blob);
  const a    = document.createElement('a');
  a.href     = url;
  a.download = `chat-${MEETING_UUID.slice(0, 8)}.txt`;
  a.click();
  URL.revokeObjectURL(url);
  showToast('Chat transcript downloaded.', 'success');
}
