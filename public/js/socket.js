/* NavuliMeet — Socket.IO Client Wrapper */

let socket = null;

function connectSocket() {
  if (socket && socket.connected) return socket;

  socket = io(SIGNALING_URL, {
    auth: { token: API_TOKEN },
    transports: ['websocket', 'polling'],
    reconnection: true,
    reconnectionAttempts: Infinity,
    reconnectionDelay: 2000,
    reconnectionDelayMax: 10000,
  });

  socket.on('connect', () => {
    console.log('[Socket] Connected:', socket.id);
    socket.emit('join-room', {
      meetingUuid: MEETING_UUID,
      userId: USER_ID,
      displayName: DISPLAY_NAME,
      photoUrl: (typeof USER_AVATAR !== 'undefined' && USER_AVATAR) ? USER_AVATAR : '',
      isHost: IS_HOST,
      waitingRoom: WAITING_ROOM,
      maxParticipants: (typeof MAX_PARTICIPANTS !== 'undefined') ? MAX_PARTICIPANTS : 300,
    });
    _hideReconnectBanner();
  });

  socket.on('connect_error', (err) => {
    console.warn('[Socket] Connection error:', err.message);
  });

  socket.on('disconnect', (reason) => {
    console.log('[Socket] Disconnected:', reason);
    if (reason !== 'io client disconnect') {
      _showReconnectBanner('Connection lost — reconnecting…');
    }
  });

  socket.io.on('reconnect_attempt', (n) => {
    console.log('[Socket] Reconnect attempt', n);
    _showReconnectBanner(`Reconnecting… (attempt ${n})`);
  });

  socket.io.on('reconnect', (n) => {
    console.log('[Socket] Reconnected after', n, 'attempt(s)');
    _hideReconnectBanner();
    // SFU cleanup + re-join happens automatically via the 'admitted' event
    // that the server re-sends when join-room is re-emitted from 'connect'.
    if (typeof cleanupSfu === 'function') cleanupSfu();
  });

  socket.io.on('reconnect_failed', () => {
    console.error('[Socket] All reconnect attempts failed');
    _showReconnectBanner('Could not reconnect — please refresh the page');
  });

  return socket;
}

function _showReconnectBanner(msg) {
  const banner = document.getElementById('reconnectBanner');
  const msgEl  = document.getElementById('reconnectMsg');
  if (banner) banner.style.display = 'flex';
  if (msgEl)  msgEl.textContent = msg || 'Reconnecting…';
}

function _hideReconnectBanner() {
  const banner = document.getElementById('reconnectBanner');
  if (banner) banner.style.display = 'none';
}

function getSocket()   { return socket; }
function getSocketId() { return socket?.id ?? null; }

function emitSafe(event, data) {
  if (socket && socket.connected) {
    socket.emit(event, data);
  } else {
    console.warn('[Socket] Cannot emit, not connected:', event);
  }
}
