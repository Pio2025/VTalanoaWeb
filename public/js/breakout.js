/* VTalanoa — Breakout Rooms */

// ── State ─────────────────────────────────────────────────────────────────
let _inBreakout   = false;
let _breakoutName = '';

// ── Host: open breakout manager ────────────────────────────────────────────
function openBreakoutManager() {
  const peerList = Object.entries(participants).map(([sid, p]) => ({ sid, name: p.displayName }));
  if (peerList.length === 0) {
    showToast('No other participants to put in breakout rooms.', 'default');
    return;
  }
  Swal.fire({
    title: '<i class="fa-solid fa-door-open me-2" style="color:#00aeef"></i>Breakout Rooms',
    html: _buildBrManagerHtml(peerList),
    width: 580,
    background: '#1e293b',
    color: '#fff',
    showCancelButton: true,
    confirmButtonText: 'Open Rooms',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#00aeef',
    cancelButtonColor: '#64748b',
    didOpen: () => { updateBreakoutRoomOptions(peerList.map(p => p.sid)); },
    preConfirm: () => _collectBreakoutAssignments(peerList),
  }).then(result => {
    if (!result.isConfirmed || !result.value) return;
    emitSafe('open-breakout-rooms', { rooms: result.value.rooms });
  });
}

function _buildBrManagerHtml(peerList) {
  const n = Math.min(peerList.length, 8);
  const countOpts = Array.from({ length: n }, (_, i) =>
    `<option value="${i + 1}"${i === 1 ? ' selected' : ''}>${i + 1} Room${i > 0 ? 's' : ''}</option>`).join('');

  const rows = peerList.map(p => `
    <div class="br-peer-row">
      <span class="br-peer-name">${escapeHtml(p.name)}</span>
      <select class="br-room-select" id="bra-${p.sid}">
        <option value="">— Unassigned —</option>
      </select>
    </div>`).join('');

  return `
    <div class="br-manager">
      <div class="br-top">
        <label>Rooms:</label>
        <select id="brRoomCount" class="br-count-select"
          onchange="updateBreakoutRoomOptions(${JSON.stringify(peerList.map(p => p.sid))})">
          ${countOpts}
        </select>
        <button class="btn-br-auto"
          onclick="autoAssignBreakout(${JSON.stringify(peerList.map(p => p.sid))})">
          <i class="fa-solid fa-shuffle me-1"></i>Auto Assign
        </button>
      </div>
      <div class="br-peers-list">${rows}</div>
    </div>`;
}

function updateBreakoutRoomOptions(sids) {
  const n = parseInt(document.getElementById('brRoomCount')?.value) || 2;
  const opts = `<option value="">— Unassigned —</option>` +
    Array.from({ length: n }, (_, i) => `<option value="${i}">Room ${i + 1}</option>`).join('');
  sids.forEach(sid => {
    const sel = document.getElementById('bra-' + sid);
    if (!sel) return;
    const prev = sel.value;
    sel.innerHTML = opts;
    if (prev !== '') sel.value = prev;
  });
}

function autoAssignBreakout(sids) {
  const n = parseInt(document.getElementById('brRoomCount')?.value) || 2;
  const opts = `<option value="">— Unassigned —</option>` +
    Array.from({ length: n }, (_, i) => `<option value="${i}">Room ${i + 1}</option>`).join('');
  const shuffled = [...sids].sort(() => Math.random() - 0.5);
  shuffled.forEach((sid, idx) => {
    const sel = document.getElementById('bra-' + sid);
    if (!sel) return;
    sel.innerHTML = opts;
    sel.value = String(idx % n);
  });
}

function _collectBreakoutAssignments(peerList) {
  const n = parseInt(document.getElementById('brRoomCount')?.value) || 2;
  const rooms = Array.from({ length: n }, (_, i) => ({ name: `Room ${i + 1}`, socketIds: [] }));
  let any = false;
  peerList.forEach(p => {
    const v = document.getElementById('bra-' + p.sid)?.value;
    if (v !== '' && v !== undefined && v !== null) {
      rooms[parseInt(v)].socketIds.push(p.sid);
      any = true;
    }
  });
  const nonempty = rooms.filter(r => r.socketIds.length > 0);
  if (!any || !nonempty.length) {
    Swal.showValidationMessage('Assign at least one participant to a room.');
    return false;
  }
  return { rooms: nonempty };
}

// ── Host: end breakout rooms ───────────────────────────────────────────────
function endBreakoutRooms() {
  Swal.fire({
    title: 'End Breakout Rooms?',
    text: 'All participants will be returned to the main meeting.',
    icon: 'question',
    background: '#1e293b', color: '#fff',
    showCancelButton: true,
    confirmButtonText: 'End Rooms',
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#64748b',
  }).then(r => { if (r.isConfirmed) emitSafe('end-breakout-rooms', {}); });
}

// ── Host: broadcast to all breakout rooms ──────────────────────────────────
function broadcastToBreakouts() {
  Swal.fire({
    title: 'Broadcast to All Rooms',
    input: 'text',
    inputPlaceholder: 'Message for all breakout rooms…',
    background: '#1e293b', color: '#fff',
    showCancelButton: true,
    confirmButtonText: 'Broadcast',
    confirmButtonColor: '#00aeef',
    cancelButtonColor: '#64748b',
    inputValidator: v => v?.trim() ? null : 'Please enter a message.',
  }).then(r => {
    if (r.isConfirmed && r.value?.trim()) {
      emitSafe('broadcast-to-breakouts', { message: r.value.trim() });
      showToast('Message broadcast to all rooms.', 'success');
    }
  });
}

// ── Participant: assigned to a breakout room ───────────────────────────────
async function handleAssignedToBreakout({ roomKey, roomId, roomName, peers }) {
  _inBreakout   = true;
  _breakoutName = roomName;

  _showBreakoutTransition(roomName);

  // Teardown existing SFU and tiles
  if (typeof cleanupSfu === 'function') cleanupSfu();
  Object.keys(participants).forEach(sid => {
    if (typeof removeRemoteTile === 'function') removeRemoteTile(sid);
    delete participants[sid];
  });

  _updateBreakoutBanner(true, roomName);

  await new Promise(r => setTimeout(r, 700));
  _hideBreakoutTransition();

  // Register breakout peers
  peers.forEach(p => {
    participants[p.socketId] = { displayName: p.displayName, userId: p.userId, photoUrl: p.photoUrl || '' };
  });

  // Reconnect SFU in the new room context
  try {
    const { sessionId, trackNames } = await connectToSfu();
    for (const p of peers) {
      if (p.sfuSessionId && p.sfuTrackNames) {
        await subscribeToRemotePeer(p.socketId, p.sfuSessionId, p.sfuTrackNames.video, p.sfuTrackNames.audio);
      } else {
        addRemoteTile(p.socketId, new MediaStream());
      }
    }
    emitSafe('sfu-session-ready', { sessionId, trackNames });
    emitSafe('mute-status', { isMuted: !micEnabled });
  } catch (e) {
    console.error('[Breakout] SFU reconnect error:', e);
    showToast('Media reconnection failed. Try refreshing.', 'error');
  }

  renderParticipants();
  updateParticipantCount();
  showToast(`You joined ${roomName}.`, 'default');
}

// ── Participant/Host: returned to main session ─────────────────────────────
async function handleReturnedToMain({ peers }) {
  _inBreakout   = false;
  _breakoutName = '';

  _showBreakoutTransition('Main Session');

  if (typeof cleanupSfu === 'function') cleanupSfu();
  Object.keys(participants).forEach(sid => {
    if (typeof removeRemoteTile === 'function') removeRemoteTile(sid);
    delete participants[sid];
  });

  _updateBreakoutBanner(false);

  await new Promise(r => setTimeout(r, 700));
  _hideBreakoutTransition();

  peers.forEach(p => {
    participants[p.socketId] = { displayName: p.displayName, userId: p.userId, photoUrl: p.photoUrl || '' };
  });

  try {
    const { sessionId, trackNames } = await connectToSfu();
    for (const p of peers) {
      if (p.sfuSessionId && p.sfuTrackNames) {
        await subscribeToRemotePeer(p.socketId, p.sfuSessionId, p.sfuTrackNames.video, p.sfuTrackNames.audio);
      } else {
        addRemoteTile(p.socketId, new MediaStream());
      }
    }
    emitSafe('sfu-session-ready', { sessionId, trackNames });
    emitSafe('mute-status', { isMuted: !micEnabled });
  } catch (e) {
    console.error('[Breakout] Return SFU error:', e);
  }

  renderParticipants();
  updateParticipantCount();
  showToast('Returned to the main meeting.', 'default');
}

// ── Participant: leave breakout voluntarily ────────────────────────────────
function returnToMainSession() {
  emitSafe('return-from-breakout', {});
}

// ── Host: observe a specific breakout room ────────────────────────────────
function joinBreakoutToObserve(roomKey) {
  emitSafe('join-breakout-to-observe', { roomKey });
}

function leaveObservedBreakout() {
  emitSafe('leave-observed-breakout', {});
}

// ── Transition overlay ────────────────────────────────────────────────────
function _showBreakoutTransition(name) {
  let el = document.getElementById('brTransitionOverlay');
  if (!el) {
    el = document.createElement('div');
    el.id = 'brTransitionOverlay';
    el.className = 'br-transition-overlay';
    document.body.appendChild(el);
  }
  el.innerHTML = `
    <div class="br-transition-spinner"></div>
    <p class="br-transition-label">Joining ${escapeHtml(name)}…</p>`;
  el.style.display = 'flex';
}
function _hideBreakoutTransition() {
  document.getElementById('brTransitionOverlay')?.remove();
}

// ── Breakout banner ────────────────────────────────────────────────────────
function _updateBreakoutBanner(show, name) {
  let banner = document.getElementById('breakoutBanner');
  if (!show) { banner?.remove(); return; }
  if (!banner) {
    banner = document.createElement('div');
    banner.id = 'breakoutBanner';
    banner.className = 'breakout-banner';
    const header = document.querySelector('.room-header');
    if (header) header.insertAdjacentElement('afterend', banner);
    else document.body.prepend(banner);
  }
  banner.innerHTML = `
    <i class="fa-solid fa-door-open me-2"></i>
    <span>Breakout Room: <strong>${escapeHtml(name)}</strong></span>
    <button class="btn-return-main" onclick="returnToMainSession()">
      <i class="fa-solid fa-arrow-left me-1"></i>Return to Main
    </button>`;
}
