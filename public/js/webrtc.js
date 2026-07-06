/* NavuliMeet — Cloudflare Realtime SFU WebRTC Client
 *
 * Architecture: one RTCPeerConnection per client → Cloudflare SFU.
 * Media never travels peer-to-peer; Cloudflare's edge routes all tracks.
 *
 * Pub/sub model:
 *   1. Connect to SFU → get sessionId
 *   2. Publish local tracks → tell signaling server "I'm ready" (sfu-session-ready)
 *   3. Receive other peers' sessionId+trackNames via Socket.IO (peer-sfu-ready)
 *   4. Subscribe to their tracks through the same RTCPeerConnection
 */

let sfuPc        = null;   // Single RTCPeerConnection to Cloudflare SFU
let sfuSessionId = null;   // Our session ID returned by Cloudflare
let localStream  = null;

// ── Speaking detection (shared AudioContext + RAF loop) ───────────────────
let _speakCtx   = null;
let _speakRafId = null;
const _speakNodes = {};  // socketId → { analyser, data: Uint8Array }

// ── Active speaker promotion ──────────────────────────────────────────────
// Keeps a rolling list of the most recently active speakers so the grid
// reorders live (speakers float to top) and the recorder knows who to show.
const _speakQueue    = [];     // socketIds, most-recent-speaker first
const _lastSpokAt    = {};     // socketId → ms timestamp of last promotion
const SPEAK_Q_LEN    = 6;      // max speakers remembered in the queue
const SPEAK_DEBOUNCE = 2000;   // ms — debounce so tiles don't jump around constantly

function _trackSpeaking(socketId, stream) {
  if (_speakNodes[socketId] || !stream) return;
  if (!stream.getAudioTracks().some(t => t.readyState === 'live')) return;
  try {
    if (!_speakCtx || _speakCtx.state === 'closed') {
      _speakCtx = new (window.AudioContext || window.webkitAudioContext)();
    }
    if (_speakCtx.state === 'suspended') _speakCtx.resume().catch(() => {});
    const analyser = _speakCtx.createAnalyser();
    analyser.fftSize = 64;
    analyser.smoothingTimeConstant = 0.7;
    _speakCtx.createMediaStreamSource(stream).connect(analyser);
    _speakNodes[socketId] = { analyser, data: new Uint8Array(analyser.frequencyBinCount) };
    if (!_speakRafId) _speakLoop();
  } catch (_) {}
}

function _speakLoop() {
  _speakRafId = requestAnimationFrame(_speakLoop);
  const now = Date.now();
  for (const [sid, node] of Object.entries(_speakNodes)) {
    node.analyser.getByteFrequencyData(node.data);
    const avg = node.data.reduce((a, b) => a + b, 0) / node.data.length;
    const el  = document.getElementById(sid === 'local' ? 'localTile' : 'tile-' + sid);
    if (el) el.classList.toggle('speaking', avg > 12);

    // Promote active remote speaker to top of grid (debounced)
    if (sid !== 'local' && avg > 12 && (!_lastSpokAt[sid] || now - _lastSpokAt[sid] > SPEAK_DEBOUNCE)) {
      _lastSpokAt[sid] = now;
      const qi = _speakQueue.indexOf(sid);
      if (qi !== -1) _speakQueue.splice(qi, 1);
      _speakQueue.unshift(sid);
      if (_speakQueue.length > SPEAK_Q_LEN) _speakQueue.pop();
      _applyActiveSpeakerOrder();
    }
  }
}

function _untrackSpeaking(socketId) {
  delete _speakNodes[socketId];
  const qi = _speakQueue.indexOf(socketId);
  if (qi !== -1) { _speakQueue.splice(qi, 1); _applyActiveSpeakerOrder(); }
  delete _lastSpokAt[socketId];
  if (!Object.keys(_speakNodes).length && _speakRafId) {
    cancelAnimationFrame(_speakRafId);
    _speakRafId = null;
  }
}

// Sets CSS order on grid tiles so recent speakers float to the top.
// Uses negative order values so speakers always sort before non-speakers (order:0).
function _applyActiveSpeakerOrder() {
  const grid = document.getElementById('videoGrid');
  if (!grid) return;
  grid.querySelectorAll('.video-tile').forEach(t => { t.style.order = '0'; });
  _speakQueue.forEach((sid, i) => {
    const tile = document.getElementById('tile-' + sid);
    if (tile) tile.style.order = String(i - SPEAK_Q_LEN); // -6 … -1
  });
}

// Track names we published — shared with room peers so they can subscribe
const localTrackNames = { video: null, audio: null };

// socketId → { sessionId, videoTrackName, audioTrackName, stream, videoMid, audioMid }
const remotePeers = {};

// mid (string) → { socketId, kind }  — populated when subscribing
const midToPeer = {};

// ── 50-on-screen cap ─────────────────────────────────────────────────
// 49 remote tiles + 1 local tile = 50 visible at once
const MAX_GRID_TILES = 49;
const _overflowPeers = new Map(); // socketId → MediaStream (audio-only, waiting for slot)

function _ensureHiddenAudio(socketId, stream) {
  let audio = document.getElementById('ha-' + socketId);
  if (!audio) {
    audio = document.createElement('audio');
    audio.id = 'ha-' + socketId;
    audio.autoplay = true;
    audio.style.display = 'none';
    document.body.appendChild(audio);
  }
  audio.srcObject = stream;
}

function _removeHiddenAudio(socketId) {
  document.getElementById('ha-' + socketId)?.remove();
}

function _promoteFirstOverflow() {
  if (_overflowPeers.size === 0) return;
  const [[sid, stream]] = _overflowPeers;
  _overflowPeers.delete(sid);
  _removeHiddenAudio(sid);
  addRemoteTile(sid, stream);
}

function _updateOverflowTile() {
  const count = _overflowPeers.size;
  const grid  = document.getElementById('videoGrid');
  if (!grid) return;
  let el = document.getElementById('tileOverflow');
  if (count === 0) { el?.remove(); return; }
  if (!el) {
    el = document.createElement('div');
    el.id = 'tileOverflow';
    el.className = 'video-tile overflow-tile';
    grid.appendChild(el);
  }
  el.innerHTML = `<div class="overflow-count">+${count}</div>
    <div class="overflow-label">more participant${count !== 1 ? 's' : ''}</div>`;
}

/* ── SFU cleanup (call before reconnect) ────────────────── */
function cleanupSfu() {
  console.log('[SFU] cleanupSfu() — closing RTCPeerConnection and clearing state');
  _bwStopMonitor();
  if (sfuPc) {
    sfuPc.ontrack = null;
    sfuPc.onicecandidate = null;
    sfuPc.oniceconnectionstatechange = null;
    sfuPc.onconnectionstatechange = null;
    sfuPc.close();
    sfuPc = null;
  }
  sfuSessionId = null;
  localTrackNames.video = null;
  localTrackNames.audio = null;

  Object.keys(remotePeers).forEach(sid => {
    try { remotePeers[sid].stream.getTracks().forEach(t => t.stop()); } catch (_) {}
    delete remotePeers[sid];
  });
  Object.keys(midToPeer).forEach(k => delete midToPeer[k]);
}

/* ── Local media ──────────────────────────────────────────── */
async function initLocalStream() {
  const camPref  = sessionStorage.getItem('nm_cam') !== 'false';
  const micPref  = sessionStorage.getItem('nm_mic') !== 'false';
  const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
  console.log('[MEDIA] Requesting media — cam:', camPref, 'mic:', micPref, 'mobile:', isMobile);

  const videoConstraints = camPref
    ? (isMobile
        // Mobile: bump to 480p @ 24fps (was 360p @ 15fps) for better clarity
        ? { width: { ideal: 854, max: 1280 }, height: { ideal: 480, max: 720 },
            frameRate: { ideal: 24, max: 30 }, facingMode: 'user' }
        // Desktop: target 1080p @ 30fps, accept 720p minimum
        : { width: { ideal: 1920, min: 1280 }, height: { ideal: 1080, min: 720 },
            frameRate: { ideal: 30 } })
    : false;

  const audioConstraints = micPref
    ? { echoCancellation: true, noiseSuppression: true, autoGainControl: true,
        sampleRate: { ideal: 48000 } }
    : false;

  try {
    localStream = await navigator.mediaDevices.getUserMedia({
      video: videoConstraints,
      audio: audioConstraints,
    });
    console.log('[MEDIA] Got stream — video tracks:', localStream.getVideoTracks().length,
      '| audio tracks:', localStream.getAudioTracks().length);
  } catch (e) {
    console.warn('[MEDIA] getUserMedia failed, trying audio-only:', e.message);
    try {
      localStream = await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
      console.log('[MEDIA] Audio-only stream acquired');
    } catch (e2) {
      console.error('[MEDIA] Could not access any media:', e2.message);
      localStream = new MediaStream();
    }
  }

  const lv = document.getElementById('localVideo');
  if (lv) lv.srcObject = localStream;
  updateLocalNoVideoOverlay();

  // Start speaking detection for local mic (first click resumes AudioContext on iOS)
  document.addEventListener('click', () => _trackSpeaking('local', localStream), { once: true });

  // Resume all videos when app returns from background (mobile freeze fix)
  document.addEventListener('visibilitychange', _resumeAllVideos);

  // Pre-warm the background segmentation model so blur toggle feels instant.
  // Deferred 3 s to avoid competing with camera init and SFU connection.
  setTimeout(() => { if (typeof warmBgSegmenter === 'function') warmBgSegmenter(); }, 3000);

  return localStream;
}

function _resumeAllVideos() {
  if (document.hidden) return;
  document.querySelectorAll('video').forEach(v => {
    if (v.srcObject && v.paused) v.play().catch(() => {});
  });
}

function updateLocalNoVideoOverlay() {
  const noVid = document.getElementById('localNoVideo');
  if (!noVid) return;
  const hasVideo = localStream?.getVideoTracks().some(t => t.enabled && t.readyState === 'live');
  noVid.style.display = hasVideo ? 'none' : 'flex';
}

/* ── Connect to SFU and publish local tracks ─────────────── */
async function connectToSfu() {
  console.log('[SFU] connectToSfu() — creating RTCPeerConnection');
  console.log('[SFU] ICE servers:', JSON.stringify(ICE_SERVERS));
  console.log('[SFU] SFU proxy base:', SFU_PROXY_BASE);

  sfuPc = new RTCPeerConnection(ICE_SERVERS);
  sfuPc.ontrack = _handleIncomingTrack;

  // ── ICE / STUN / TURN diagnostics ────────────────────────
  sfuPc.onicecandidate = (event) => {
    if (!event.candidate) {
      console.log('[ICE] Local candidate gathering complete');
      return;
    }
    const { candidate } = event;
    const type   = candidate.type;     // host | srflx | relay | prflx
    const proto  = candidate.protocol; // udp | tcp
    const addr   = candidate.address || '?';
    const label  = type === 'relay'  ? 'TURN (relay)'             :
                   type === 'srflx'  ? 'STUN (server-reflexive)'  :
                   type === 'host'   ? 'host (direct/LAN)'        : type;
    console.log(`[ICE] local candidate | ${label} | proto=${proto} | addr=${addr}`);
  };

  sfuPc.onicecandidateerror = (event) => {
    console.warn('[ICE] candidate error | code:', event.errorCode,
      '| text:', event.errorText, '| url:', event.url);
  };

  sfuPc.oniceconnectionstatechange = () => {
    const state = sfuPc.iceConnectionState;
    console.log('[SFU] ICE state:', state);
    if (state === 'connected' || state === 'completed') {
      sfuPc.getStats().then(stats => {
        stats.forEach(report => {
          if (report.type !== 'candidate-pair' || report.state !== 'succeeded') return;
          const local  = stats.get(report.localCandidateId);
          const remote = stats.get(report.remoteCandidateId);
          if (!local) return;
          const pathType = local.candidateType === 'relay'  ? 'TURN (relay)'           :
                           local.candidateType === 'srflx'  ? 'STUN (server-reflexive)' :
                           'host (direct/LAN)';
          console.log(`[ICE] ✅ Active path: LOCAL=${pathType} (${local.protocol}/${local.ip || local.address}:${local.port})` +
            ` → REMOTE=${remote?.candidateType || '?'} (${remote?.ip || remote?.address}:${remote?.port})`);
        });
      }).catch(() => {});
    }
  };

  sfuPc.onconnectionstatechange = () =>
    console.log('[SFU] Connection state:', sfuPc.connectionState);

  // Add local tracks as sendonly transceivers
  const videoTrack = localStream?.getVideoTracks()[0];
  const audioTrack = localStream?.getAudioTracks()[0];
  const _isMobile  = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);

  console.log('[SFU] Local tracks — video:', videoTrack?.label || 'none',
    '| audio:', audioTrack?.label || 'none');

  if (videoTrack) {
    const vt = sfuPc.addTransceiver(videoTrack, { direction: 'sendonly' });

    // Prefer VP9 — better quality per bit than VP8; H.264 as fallback
    try {
      const caps = RTCRtpSender.getCapabilities?.('video');
      if (caps?.codecs && vt.setCodecPreferences) {
        const pref = [
          ...caps.codecs.filter(c => c.mimeType === 'video/VP9'),
          ...caps.codecs.filter(c => c.mimeType === 'video/H264'),
          ...caps.codecs.filter(c => c.mimeType !== 'video/VP9' && c.mimeType !== 'video/H264'),
        ];
        if (pref.length) vt.setCodecPreferences(pref);
      }
    } catch (_) {}

    // Bitrate target: 600 kbps mobile, 2.5 Mbps desktop (allows 1080p VP9)
    const params = vt.sender.getParameters();
    if (!params.encodings?.length) params.encodings = [{}];
    params.encodings[0].maxBitrate = _isMobile ? 600_000 : 2_500_000;
    vt.sender.setParameters(params).catch(() => {});
  }

  if (audioTrack) {
    const at = sfuPc.addTransceiver(audioTrack, { direction: 'sendonly' });
    // Prefer Opus (48 kHz, ~128 kbps) for voice clarity
    try {
      const caps = RTCRtpSender.getCapabilities?.('audio');
      if (caps?.codecs && at.setCodecPreferences) {
        const pref = [
          ...caps.codecs.filter(c => c.mimeType === 'audio/opus'),
          ...caps.codecs.filter(c => c.mimeType !== 'audio/opus'),
        ];
        if (pref.length) at.setCodecPreferences(pref);
      }
    } catch (_) {}
  }

  // Create offer
  const offer = await sfuPc.createOffer();
  await sfuPc.setLocalDescription(offer);
  console.log('[SFU] Offer created, calling /sessions/new …');

  // Create session with Cloudflare SFU (CI4 proxy keeps the App Secret)
  const sessionData = await _sfuFetch('POST', '/sessions/new', {
    sessionDescription: { type: 'offer', sdp: sfuPc.localDescription.sdp },
  });

  console.log('[SFU] Session created:', sessionData.sessionId);
  sfuSessionId = sessionData.sessionId;
  await sfuPc.setRemoteDescription(sessionData.sessionDescription);
  console.log('[SFU] Remote description set (answer from SFU)');

  // Publish local tracks — grab MIDs from transceivers after SDP exchange
  const tracks = [];
  for (const t of sfuPc.getTransceivers()) {
    if (!t.sender.track || !t.mid) continue;
    const kind      = t.sender.track.kind;
    const trackName = `${kind}-${sfuSessionId}`;
    localTrackNames[kind] = trackName;
    tracks.push({ location: 'local', trackName, mid: t.mid });
  }

  console.log('[SFU] Publishing tracks:', tracks.map(t => t.trackName));

  if (tracks.length) {
    const tracksData = await _sfuFetch('POST', `/sessions/${sfuSessionId}/tracks/new`, { tracks });
    console.log('[SFU] tracks/new response:', JSON.stringify(tracksData).slice(0, 200));
    await _handleRenegotiation(tracksData, false);
  } else {
    console.warn('[SFU] No local tracks to publish — stream may be empty');
  }

  console.log('[SFU] ✅ Session ready | id:', sfuSessionId, '| trackNames:', localTrackNames);
  _bwStartMonitor();
  return { sessionId: sfuSessionId, trackNames: localTrackNames };
}

/* ── Subscribe to a remote peer's tracks ─────────────────── */
async function subscribeToRemotePeer(socketId, peerSessionId, videoTrackName, audioTrackName) {
  console.log('[SFU] subscribeToRemotePeer()', { socketId, peerSessionId, videoTrackName, audioTrackName });

  if (!sfuSessionId) {
    console.warn('[SFU] subscribeToRemotePeer: local session not ready yet — sfuSessionId is null');
    return;
  }

  const tracks = [];
  if (videoTrackName) tracks.push({ location: 'remote', sessionId: peerSessionId, trackName: videoTrackName });
  if (audioTrackName) tracks.push({ location: 'remote', sessionId: peerSessionId, trackName: audioTrackName });

  if (!tracks.length) {
    console.warn('[SFU] subscribeToRemotePeer: no tracks to subscribe to (both null) — skipping');
    return;
  }

  if (!remotePeers[socketId]) {
    remotePeers[socketId] = {
      sessionId: peerSessionId,
      videoTrackName,
      audioTrackName,
      stream: new MediaStream(),
    };
  }

  // Snapshot existing mids before the subscribe call so we can detect new ones later
  const existingMids = new Set(sfuPc.getTransceivers().map(t => t.mid).filter(Boolean));

  console.log('[SFU] Calling tracks/new to subscribe:', tracks);
  const resp = await _sfuFetch('POST', `/sessions/${sfuSessionId}/tracks/new`, { tracks });
  console.log('[SFU] tracks/new subscribe response:', JSON.stringify(resp).slice(0, 300));

  // Primary: map mids from the response tracks array
  if (resp.tracks) {
    for (const t of resp.tracks) {
      if (!t.mid) continue;
      const kind = t.trackName === videoTrackName ? 'video' : 'audio';
      midToPeer[t.mid] = { socketId, kind };
      if (kind === 'video') remotePeers[socketId].videoMid = t.mid;
      else                  remotePeers[socketId].audioMid = t.mid;
      console.log('[SFU] mid mapped (primary):', t.mid, '→', socketId, kind);
    }
  } else {
    console.warn('[SFU] Response has no tracks array — will rely on fallback mid mapping');
  }

  if (resp.requiresImmediateRenegotiation && resp.sessionDescription) {
    console.log('[SFU] SFU sent offer — starting renegotiation');
    await sfuPc.setRemoteDescription(resp.sessionDescription);
    console.log('[SFU] Remote description set (SFU offer)');

    // Fallback: scan new recvonly transceivers added by the SFU offer
    sfuPc.getTransceivers().forEach(t => {
      if (!t.mid || existingMids.has(t.mid) || midToPeer[t.mid]) return;
      const kind = t.receiver?.track?.kind;
      if (kind) {
        midToPeer[t.mid] = { socketId, kind };
        if (kind === 'video') remotePeers[socketId].videoMid = t.mid;
        else                  remotePeers[socketId].audioMid = t.mid;
        console.log('[SFU] mid mapped (fallback):', t.mid, '→', socketId, kind);
      } else {
        console.warn('[SFU] New transceiver has no track kind yet:', t.mid);
      }
    });

    const answer = await sfuPc.createAnswer();
    await sfuPc.setLocalDescription(answer);
    console.log('[SFU] Answer created, sending PUT /renegotiate …');
    await _sfuFetch('PUT', `/sessions/${sfuSessionId}/renegotiate`, {
      sessionDescription: { type: 'answer', sdp: sfuPc.localDescription.sdp },
    });
    console.log('[SFU] Renegotiation complete for', socketId);
  } else if (resp.requiresImmediateRenegotiation) {
    console.log('[SFU] requiresImmediateRenegotiation but no sessionDescription — sending offer');
    await _handleRenegotiation(resp, false);
  } else {
    console.log('[SFU] No renegotiation required for', socketId);
  }

  console.log('[SFU] ✅ Subscribed to', socketId, '| midToPeer now:', JSON.stringify(midToPeer));
  // Show tile immediately (avatar shown until the actual video track arrives)
  addRemoteTile(socketId, remotePeers[socketId].stream);
}

/* ── Remove a remote peer ────────────────────────────────── */
function removePeer(socketId) {
  _untrackSpeaking(socketId);
  const peer = remotePeers[socketId];
  if (peer) {
    for (const mid of Object.keys(midToPeer)) {
      if (midToPeer[mid].socketId === socketId) delete midToPeer[mid];
    }
    peer.stream.getTracks().forEach(t => t.stop());
    delete remotePeers[socketId];
  }
  removeRemoteTile(socketId);

  // If this peer was spotlighted, exit spotlight
  if (spotlightPeerId === socketId) exitSpotlight();
}

/* ── Renegotiation helper ────────────────────────────────── */
async function _handleRenegotiation(resp, isSfuOffer) {
  if (!resp.requiresImmediateRenegotiation) return;

  if (isSfuOffer && resp.sessionDescription) {
    // SFU sends us an offer (adding remote receive tracks) → we answer
    await sfuPc.setRemoteDescription(resp.sessionDescription);
    const answer = await sfuPc.createAnswer();
    await sfuPc.setLocalDescription(answer);
    await _sfuFetch('PUT', `/sessions/${sfuSessionId}/renegotiate`, {
      sessionDescription: { type: 'answer', sdp: sfuPc.localDescription.sdp },
    });
  } else {
    // We offer (e.g. after publishing tracks) → SFU answers
    const reOffer = await sfuPc.createOffer();
    await sfuPc.setLocalDescription(reOffer);
    await _sfuFetch('PUT', `/sessions/${sfuSessionId}/renegotiate`, {
      sessionDescription: { type: 'offer', sdp: sfuPc.localDescription.sdp },
    });
  }
}

/* ── Incoming remote track handler ──────────────────────── */
function _handleIncomingTrack(event) {
  const transceiver = sfuPc.getTransceivers().find(t => t.receiver === event.receiver);
  const mid         = transceiver?.mid;
  const peerInfo    = mid ? midToPeer[mid] : null;

  console.log('[SFU] ontrack event | kind:', event.track.kind,
    '| mid:', mid, '| mapped peer:', peerInfo ? peerInfo.socketId : 'NONE');
  console.log('[SFU] midToPeer map at ontrack:', JSON.stringify(midToPeer));

  if (!peerInfo) {
    console.error('[SFU] ❌ ontrack: no peer mapped for mid', mid,
      '— track will be dropped. All transceivers:',
      sfuPc.getTransceivers().map(t => ({ mid: t.mid, dir: t.direction })));
    return;
  }

  const { socketId, kind } = peerInfo;
  const peer = remotePeers[socketId];
  if (!peer) {
    console.error('[SFU] ❌ ontrack: remotePeers has no entry for', socketId);
    return;
  }

  // Remove any existing track of the same kind before adding the new one
  peer.stream.getTracks()
    .filter(t => t.kind === kind)
    .forEach(t => peer.stream.removeTrack(t));

  peer.stream.addTrack(event.track);
  console.log('[SFU] ✅ Track added to peer stream | peer:', socketId, '| kind:', kind);
  addRemoteTile(socketId, peer.stream);

  // Update spotlight if this peer is currently spotlighted
  if (spotlightPeerId === socketId) _refreshSpotlightVideo(socketId);
}

/* ── SFU fetch proxy (routes through CI4 with App Secret) ── */
async function _sfuFetch(method, path, body = null) {
  const url = SFU_PROXY_BASE + path;
  console.log(`[SFU] ▶ ${method} ${url}`);

  const ctrl = new AbortController();
  const timer = setTimeout(() => ctrl.abort(), 15000);

  const init = {
    method,
    signal: ctrl.signal,
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${API_TOKEN}`,
    },
  };
  if (body) init.body = JSON.stringify(body);

  let resp, data;
  try {
    resp = await fetch(url, init);
    data = await resp.json().catch(() => ({}));
  } catch (netErr) {
    const msg = netErr.name === 'AbortError'
      ? `Timeout after 15 s on ${method} ${url}`
      : netErr.message;
    console.error(`[SFU] ❌ ${msg}`);
    throw netErr;
  } finally {
    clearTimeout(timer);
  }

  if (!resp.ok) {
    console.error(`[SFU] ❌ ${resp.status} on ${method} ${url}`, data);
    throw new Error(`SFU proxy ${resp.status}: ${JSON.stringify(data)}`);
  }

  console.log(`[SFU] ◀ ${resp.status} ${method} ${url}`);
  return data;
}

/* ── Track controls ──────────────────────────────────────── */
function getLocalStream() { return localStream; }

function replaceVideoTrack(newTrack) {
  if (!sfuPc) return;
  const sender = sfuPc.getSenders().find(s => s.track?.kind === 'video');
  if (sender) sender.replaceTrack(newTrack).catch(e => console.warn('[SFU] replaceTrack error:', e));
}

async function switchMicDevice(deviceId) {
  try {
    const stream   = await navigator.mediaDevices.getUserMedia({
      audio: deviceId ? { deviceId: { exact: deviceId } } : true,
      video: false,
    });
    const newTrack = stream.getAudioTracks()[0];
    if (sfuPc) {
      const sender = sfuPc.getSenders().find(s => s.track?.kind === 'audio');
      if (sender) await sender.replaceTrack(newTrack);
    }
    const old = localStream?.getAudioTracks()[0];
    if (old) { localStream.removeTrack(old); old.stop(); }
    localStream?.addTrack(newTrack);
  } catch (e) {
    console.warn('[SFU] Mic switch failed:', e);
  }
}

async function switchCamDevice(deviceId) {
  try {
    const stream   = await navigator.mediaDevices.getUserMedia({
      video: deviceId ? { deviceId: { exact: deviceId } } : true,
      audio: false,
    });
    const newTrack = stream.getVideoTracks()[0];
    replaceVideoTrack(newTrack);
    const old = localStream?.getVideoTracks()[0];
    if (old) { localStream.removeTrack(old); old.stop(); }
    localStream?.addTrack(newTrack);
    const lv = document.getElementById('localVideo');
    if (lv) lv.srcObject = localStream;
    const sp = document.getElementById('settingsPreview');
    if (sp) {
      sp.srcObject?.getTracks().forEach(t => t.stop());
      const previewStream = await navigator.mediaDevices.getUserMedia({
        video: deviceId ? { deviceId: { exact: deviceId } } : true,
      });
      sp.srcObject = previewStream;
    }
  } catch (e) {
    console.warn('[SFU] Camera switch failed:', e);
  }
}

/* ── Tile management ─────────────────────────────────────── */
function addRemoteTile(socketId, stream) {
  const grid = document.getElementById('videoGrid');
  if (!grid) return;

  let tile = document.getElementById('tile-' + socketId);

  if (!tile) {
    // If already in overflow pool, just update the audio stream and return
    if (_overflowPeers.has(socketId)) {
      _overflowPeers.set(socketId, stream);
      _ensureHiddenAudio(socketId, stream);
      _trackSpeaking(socketId, stream);
      return;
    }

    // Enforce 50-on-screen cap
    const visibleRemote = grid.querySelectorAll('.remote-tile').length;
    if (visibleRemote >= MAX_GRID_TILES) {
      _overflowPeers.set(socketId, stream);
      _ensureHiddenAudio(socketId, stream);
      _trackSpeaking(socketId, stream);
      _updateOverflowTile();
      return;
    }

    tile = document.createElement('div');
    tile.className = 'video-tile remote-tile';
    tile.id = 'tile-' + socketId;
    tile.style.cursor = 'pointer';
    tile.title = 'Click to focus';
    tile.onclick = () => focusTile(socketId);

    const video   = document.createElement('video');
    video.autoplay   = true;
    video.playsinline = true;

    // ── Mobile video freeze fixes ─────────────────────────
    video.addEventListener('stalled', () => {
      setTimeout(() => {
        if (video.srcObject && video.readyState < 3) {
          const src = video.srcObject;
          video.srcObject = null;
          video.srcObject = src;
          video.play().catch(() => {});
        }
      }, 2000);
    });
    video.addEventListener('suspend', () => { video.play().catch(() => {}); });

    const overlay = document.createElement('div');
    overlay.className = 'tile-overlay';

    const info       = (typeof participants !== 'undefined') ? participants[socketId] : null;
    const name       = info?.displayName || 'Participant';
    const initials   = name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
    const photoUrl   = info?.photoUrl || '';
    const isHostTile = (typeof HOST_USER_ID !== 'undefined' && HOST_USER_ID > 0 && Number(info?.userId) === Number(HOST_USER_ID));
    const hostBadge  = isHostTile ? ' <span class="badge-host" style="font-size:9px;padding:1px 6px;border-radius:4px;background:rgba(0,174,239,.2);color:#00aeef;font-weight:600;margin-left:4px;">Host</span>' : '';

    overlay.innerHTML = `
      <div class="tile-name" id="name-${socketId}">${name}${hostBadge}</div>
      <div class="tile-indicators">
        <span class="tile-indicator" id="mic-ind-${socketId}" title="Mic muted" style="display:none">
          <i class="fa-solid fa-microphone-slash"></i>
        </span>
      </div>
    `;

    const noVid = document.createElement('div');
    noVid.className  = 'tile-no-video';
    noVid.id         = 'novid-' + socketId;
    noVid.innerHTML  = photoUrl
      ? `<img src="${photoUrl}" class="tile-avatar-img" alt="${initials}">`
      : `<div class="tile-avatar">${initials}</div>`;
    noVid.style.display = 'flex';

    tile.appendChild(video);
    tile.appendChild(overlay);
    tile.appendChild(noVid);

    // Host-only: remove button on the tile (top-right corner)
    if (typeof IS_HOST !== 'undefined' && IS_HOST) {
      const removeBtn = document.createElement('button');
      removeBtn.className = 'tile-remove-btn';
      removeBtn.title     = 'Remove participant';
      removeBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
      removeBtn.addEventListener('click', (e) => {
        e.stopPropagation(); // don't trigger focusTile
        const pInfo = (typeof participants !== 'undefined') ? participants[socketId] : null;
        if (typeof removeParticipantPermanent === 'function') {
          removeParticipantPermanent(socketId, pInfo?.displayName || 'Participant');
        }
      });
      tile.appendChild(removeBtn);
    }

    grid.appendChild(tile);
    updateGridLayout();
  }

  const video   = tile.querySelector('video');
  video.srcObject = stream;
  video.play().catch(() => {
    document.addEventListener('click', () => video.play().catch(() => {}), { once: true });
  });

  const noVidEl  = tile.querySelector('.tile-no-video');
  const showVideo = () => { noVidEl.style.display = 'none'; };
  const hideVideo = () => { noVidEl.style.display = 'flex'; };

  const vTracks = stream.getVideoTracks();
  if (vTracks.some(t => t.readyState === 'live' && !t.muted)) showVideo();
  vTracks.forEach(t => {
    t.onunmute = showVideo;
    t.onmute   = hideVideo;
    t.onended  = () => {
      if (!stream.getVideoTracks().some(t2 => t2.readyState === 'live' && !t2.muted)) hideVideo();
    };
  });
  video.addEventListener('playing', showVideo, { once: false });

  // Start speaking detection when audio is present
  _trackSpeaking(socketId, stream);

  // Keep spotlight strip and screen-share thumbs current as participants join
  if (spotlightPeerId && spotlightPeerId !== socketId) _buildSpotlightStrip(spotlightPeerId);
  if (document.getElementById('screenShareArea')?.style.display === 'flex') {
    if (typeof _buildScreenShareThumbs === 'function') _buildScreenShareThumbs();
  }
}

function updateRemoteTileName(socketId, name) {
  const nameEl = document.getElementById('name-' + socketId);
  if (nameEl) nameEl.textContent = name;

  const avatar = document.querySelector(`#novid-${socketId} .tile-avatar`);
  if (avatar) {
    const initials = name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
    avatar.textContent = initials || name.charAt(0).toUpperCase();
  }
}

function removeRemoteTile(socketId) {
  // Peer was in the overflow pool (not a visible tile)
  if (_overflowPeers.has(socketId)) {
    _overflowPeers.delete(socketId);
    _removeHiddenAudio(socketId);
    _updateOverflowTile();
    updateParticipantCount();
    return;
  }

  // Clean up speaker queue before removing tile
  const leaveQi = _speakQueue.indexOf(socketId);
  if (leaveQi !== -1) { _speakQueue.splice(leaveQi, 1); }
  delete _lastSpokAt[socketId];

  document.getElementById('tile-' + socketId)?.remove();
  _applyActiveSpeakerOrder(); // re-apply with updated queue
  _promoteFirstOverflow(); // move one overflow peer into the vacated slot
  _updateOverflowTile();
  updateGridLayout();
  updateParticipantCount();

  // Keep spotlight strip and screen-share thumbs current as participants leave
  if (spotlightPeerId && spotlightPeerId !== socketId) _buildSpotlightStrip(spotlightPeerId);
  if (document.getElementById('screenShareArea')?.style.display === 'flex') {
    if (typeof _buildScreenShareThumbs === 'function') _buildScreenShareThumbs();
  }
}

function updateRemoteTileCamStatus(socketId, isCamOff) {
  const noVid = document.getElementById('novid-' + socketId);
  if (noVid) noVid.style.display = isCamOff ? 'flex' : 'none';
}

function updateRemoteTileMuteStatus(socketId, isMuted) {
  const ind = document.getElementById('mic-ind-' + socketId);
  if (ind) ind.style.display = isMuted ? 'flex' : 'none';
}

/* ── Grid layout ─────────────────────────────────────────── */
function updateGridLayout() {
  const grid = document.getElementById('videoGrid');
  if (!grid) return;

  // Only remote tiles — local tile lives in the host pane
  const n = grid.querySelectorAll('.video-tile:not(.overflow-tile)').length;

  // Show / hide the "Waiting for participants" empty state
  const emptyEl = document.getElementById('participantsEmpty');
  if (emptyEl) emptyEl.classList.toggle('visible', n === 0);

  const w = window.innerWidth;

  // ── Mobile: simple layout, stack vertically ──────────────
  if (w <= 767) {
    grid.style.gridTemplateColumns = `repeat(${n <= 1 ? 1 : 2}, 1fr)`;
    grid.classList.remove('scrollable');
    grid.style.gridAutoRows = '1fr';
    return;
  }

  if (n === 0) return;

  // ── Desktop: calculate optimal cols to maximise tile area ─
  // Use actual pane dimensions for accuracy (panel may be open)
  const pane   = document.querySelector('.participants-pane');
  const paneW  = pane ? pane.offsetWidth  : w * 0.5;
  const paneH  = window.innerHeight - 56 - 76; // subtract header + ctrl bar

  const MIN_W  = 130; // px — smallest comfortable tile width
  const MIN_H  = 100; // px — smallest comfortable tile height
  const maxCols = Math.max(1, Math.floor(paneW / MIN_W));
  const maxRows = Math.max(1, Math.floor(paneH / MIN_H));

  // Try every column count; pick the one that maximises tile area
  // while still fitting all tiles on screen without scrolling
  let bestCols = 1, bestArea = 0;
  for (let c = 1; c <= maxCols; c++) {
    const r = Math.ceil(n / c);
    if (r > maxRows) continue;           // would need to scroll
    const area = (paneW / c) * (paneH / r);
    if (area > bestArea) { bestArea = area; bestCols = c; }
  }

  const fitsOnScreen = bestArea > 0;

  if (fitsOnScreen) {
    // All participants fit — fill the pane height with equal-size tiles
    grid.style.gridTemplateColumns = `repeat(${bestCols}, 1fr)`;
    grid.classList.remove('scrollable');
    grid.style.gridAutoRows = '1fr';
  } else {
    // Too many participants: fixed tile size + vertical scrollbar
    // Use as many columns as physically fit at min width (cap at 6)
    const scrollCols = Math.min(6, maxCols);
    grid.style.gridTemplateColumns = `repeat(${scrollCols}, 1fr)`;
    grid.classList.add('scrollable');
    grid.style.gridAutoRows = `minmax(${MIN_H}px, 14vh)`;
  }
}

/* ── Spotlight / Focus mode ──────────────────────────────── */
let spotlightPeerId = null;

function focusTile(socketId) {
  const overlay = document.getElementById('spotlightOverlay');
  if (!overlay) return;

  spotlightPeerId = socketId;
  const isLocal   = socketId === 'local';
  const stream    = isLocal ? localStream : remotePeers[socketId]?.stream;
  const name      = isLocal ? DISPLAY_NAME : ((typeof participants !== 'undefined' && participants[socketId]?.displayName) || 'Participant');
  const initials  = name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);

  document.getElementById('spotlightName').textContent = name;

  const sv = document.getElementById('spotlightVideo');
  sv.srcObject = stream || null;
  sv.muted = isLocal;
  sv.play().catch(() => {});

  const hasVideo   = stream?.getVideoTracks().some(t => t.readyState === 'live' && t.enabled);
  const noVid      = document.getElementById('spotlightNoVideo');
  const avatarEl   = document.getElementById('spotlightAvatar');
  if (noVid)    noVid.style.display    = hasVideo ? 'none' : 'flex';
  if (avatarEl) avatarEl.textContent   = initials;

  _buildSpotlightStrip(socketId);

  overlay.style.display = 'flex';
}

function _refreshSpotlightVideo(socketId) {
  if (spotlightPeerId !== socketId) return;
  const stream   = remotePeers[socketId]?.stream;
  const sv       = document.getElementById('spotlightVideo');
  if (sv && stream) { sv.srcObject = stream; sv.play().catch(() => {}); }
  const hasVideo = stream?.getVideoTracks().some(t => t.readyState === 'live' && t.enabled);
  const noVid    = document.getElementById('spotlightNoVideo');
  if (noVid) noVid.style.display = hasVideo ? 'none' : 'flex';
  _buildSpotlightStrip(socketId);
}

function exitSpotlight() {
  const overlay = document.getElementById('spotlightOverlay');
  if (overlay) overlay.style.display = 'none';
  const sv = document.getElementById('spotlightVideo');
  if (sv) { sv.pause(); sv.srcObject = null; }
  spotlightPeerId = null;
}

function _buildSpotlightStrip(focusedId) {
  const strip = document.getElementById('spotlightStrip');
  if (!strip) return;
  strip.innerHTML = '';

  const allIds = ['local', ...Object.keys(remotePeers)];
  allIds.forEach(sid => {
    if (sid === focusedId) return;
    const isLocal = sid === 'local';
    const stream  = isLocal ? localStream : remotePeers[sid]?.stream;
    const name    = isLocal
      ? DISPLAY_NAME
      : ((typeof participants !== 'undefined' && participants[sid]?.displayName) || 'Participant');

    const thumb = document.createElement('div');
    thumb.className = 'spotlight-thumb';
    thumb.onclick = () => focusTile(sid);

    const v = document.createElement('video');
    v.autoplay = true; v.playsinline = true; v.muted = true;
    v.style.cssText = 'width:100%;height:100%;object-fit:cover;';
    v.srcObject = stream || null;
    v.play().catch(() => {});

    const nameEl = document.createElement('div');
    nameEl.className = 'thumb-name';
    nameEl.textContent = name;

    thumb.appendChild(v);
    thumb.appendChild(nameEl);
    strip.appendChild(thumb);
  });
}

/* ── Adaptive Bandwidth Manager ──────────────────────────────────────────
 *
 * Polls WebRTC Stats every 8 s to gauge outgoing network quality.
 * When bandwidth is constrained the video bitrate is reduced first —
 * audio is never capped below its codec default so call clarity is
 * preserved even under severely congested conditions.
 *
 * Quality levels and thresholds:
 *   good  — loss < 3%, kbps > 350, RTT < 250 ms   → normal bitrate
 *   fair  — loss 3-8%, kbps 120-350, RTT 250-500 ms → cap video at 600 kbps
 *   poor  — loss > 8%, kbps < 120, RTT > 500 ms    → cap video at 150 kbps
 *
 * Hysteresis: requires 2 consecutive low readings to enter constrained/low
 * mode, and 3 consecutive good readings to restore normal bitrate.
 *
 * Emits window event "nm:networkQuality" with { quality: 'good'|'fair'|'poor' }
 * so the room UI can update quality indicators in real time.
 */

const _BW_POLL_MS           = 8000;
const _BW_MOBILE            = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
const _BW_VIDEO_NORMAL_BPS  = _BW_MOBILE ? 600_000 : 2_500_000;
const _BW_VIDEO_FAIR_BPS    = 600_000;
const _BW_VIDEO_POOR_BPS    = 150_000;

let _bwMonitorId  = null;
let _bwPrevStats  = null;
let _bwLowCount   = 0;
let _bwGoodCount  = 0;
let _bwMode       = 'normal'; // 'normal' | 'fair' | 'poor'

function _bwStartMonitor() {
  if (_bwMonitorId) return;
  _bwMonitorId = setInterval(_bwCheckQuality, _BW_POLL_MS);
}

function _bwStopMonitor() {
  clearInterval(_bwMonitorId);
  _bwMonitorId = null;
  _bwPrevStats  = null;
  _bwLowCount   = 0;
  _bwGoodCount  = 0;
  _bwMode       = 'normal';
}

async function _bwCheckQuality() {
  if (!sfuPc) return;
  let stats;
  try { stats = await sfuPc.getStats(); } catch (_) { return; }

  let videoOut = null;
  let pair     = null;
  stats.forEach(r => {
    if (r.type === 'outbound-rtp' && r.kind === 'video') videoOut = r;
    if (r.type === 'candidate-pair' && r.state === 'succeeded') pair = r;
  });

  const now  = Date.now();
  let quality = 'good';

  if (videoOut && _bwPrevStats) {
    const dtMs    = Math.max(now - _bwPrevStats.ts, 1);
    const dtBytes = (videoOut.bytesSent              || 0) - _bwPrevStats.bytesSent;
    const dtPkts  = (videoOut.packetsSent             || 0) - _bwPrevStats.packetsSent;
    const dtRetx  = (videoOut.retransmittedPacketsSent || 0) - _bwPrevStats.retx;
    const kbps    = (dtBytes * 8) / dtMs;
    const loss    = dtPkts > 0 ? dtRetx / dtPkts : 0;
    const rttMs   = (pair?.currentRoundTripTime ?? 0) * 1000;

    console.log(`[BW] video ${kbps.toFixed(0)} kbps | loss ${(loss * 100).toFixed(1)}% | rtt ${rttMs.toFixed(0)} ms`);

    if (loss > 0.08 || kbps < 120 || rttMs > 500)      quality = 'poor';
    else if (loss > 0.03 || kbps < 350 || rttMs > 250) quality = 'fair';
  }

  _bwPrevStats = {
    bytesSent: videoOut?.bytesSent              || 0,
    packetsSent: videoOut?.packetsSent           || 0,
    retx: videoOut?.retransmittedPacketsSent     || 0,
    ts: now,
  };

  _bwApplyPolicy(quality);
  window.dispatchEvent(new CustomEvent('nm:networkQuality', { detail: { quality } }));
}

function _bwApplyPolicy(quality) {
  const sender = sfuPc?.getSenders().find(s => s.track?.kind === 'video');
  if (!sender) return;

  let targetBps;

  if (quality === 'poor') {
    _bwGoodCount = 0;
    _bwLowCount++;
    if (_bwLowCount >= 2 && _bwMode !== 'poor') {
      targetBps = _BW_VIDEO_POOR_BPS;
      _bwMode   = 'poor';
      console.log('[BW] ⚠ Low-bandwidth mode — video capped to 150 kbps; audio fully protected');
    }
  } else if (quality === 'fair') {
    _bwGoodCount = 0;
    _bwLowCount++;
    if (_bwLowCount >= 2 && _bwMode === 'normal') {
      targetBps = _BW_VIDEO_FAIR_BPS;
      _bwMode   = 'fair';
      console.log('[BW] ~ Constrained mode — video capped to 600 kbps');
    }
  } else {
    _bwLowCount = 0;
    _bwGoodCount++;
    if (_bwGoodCount >= 3 && _bwMode !== 'normal') {
      targetBps = _BW_VIDEO_NORMAL_BPS;
      _bwMode   = 'normal';
      console.log('[BW] ✅ Restored normal video bitrate:', targetBps / 1000, 'kbps');
    }
  }

  if (targetBps !== undefined) {
    try {
      const params = sender.getParameters();
      if (!params.encodings?.length) params.encodings = [{}];
      params.encodings[0].maxBitrate = targetBps;
      sender.setParameters(params).catch(() => {});
    } catch (_) {}
  }
}
