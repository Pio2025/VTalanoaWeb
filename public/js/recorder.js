/* NavuliMeet — MediaRecorder (split layout: participants left, host right)
 *
 * Recording layout mirrors what the user sees on screen:
 *   Left  60% — participant grid, capped at REC_MAX_TILES, active speakers first
 *   Right 40% — local (host/self) tile
 *
 * Active speakers (detected by _speakQueue in webrtc.js) are always drawn
 * at the top-left of the participant grid so they are visible regardless of
 * how many participants are in the meeting.
 */

let mediaRecorder      = null;
let recordedChunks     = [];
let recordingStartTime = null;
let _recAnimFrameId    = null;
let _recAudioCtx       = null;

const REC_W         = 1280;
const REC_H         = 720;
const REC_HOST_FRAC = 0.40;   // host pane width as fraction of canvas width
const REC_MAX_TILES = 12;     // max participant tiles rendered per frame

// ── Tile selection — active speakers first, capped at REC_MAX_TILES ──────────
function _getRecTiles() {
  const grid = document.getElementById('videoGrid');
  if (!grid) return [];

  const all = Array.from(grid.querySelectorAll('.video-tile:not(.overflow-tile)'));
  if (!all.length) return [];
  if (all.length <= REC_MAX_TILES) {
    // Still order by speaker queue even when all fit
    return _orderByQueue(all);
  }

  // More tiles than the cap — pick speaker tiles first, then fill from the rest
  return _orderByQueue(all).slice(0, REC_MAX_TILES);
}

function _orderByQueue(tiles) {
  // _speakQueue is declared in webrtc.js (loaded before recorder.js)
  const queue = (typeof _speakQueue !== 'undefined') ? _speakQueue : [];
  const qMap  = {};
  queue.forEach((sid, i) => { qMap['tile-' + sid] = i; });

  const speakers = new Array(queue.length);
  const others   = [];

  tiles.forEach(t => {
    const qi = qMap[t.id];
    if (qi !== undefined) speakers[qi] = t;
    else others.push(t);
  });

  return [...speakers.filter(Boolean), ...others];
}

// ── Name label helper ─────────────────────────────────────────────────────────
function _recDrawLabel(ctx, name, x, y, maxW) {
  if (!name) return;
  ctx.save();
  ctx.font         = '600 13px Inter, Arial, sans-serif';
  ctx.textAlign    = 'left';
  ctx.textBaseline = 'middle';
  const tw = Math.min(ctx.measureText(name).width + 18, maxW);
  const th = 22;
  ctx.fillStyle = 'rgba(0,0,0,0.65)';
  ctx.fillRect(x, y, tw, th);
  ctx.fillStyle = '#ffffff';
  ctx.fillText(name, x + 9, y + th / 2, tw - 14);
  ctx.restore();
}

// ── Extract plain text from a tile-name element (strips badge HTML) ──────────
function _tileName(tile) {
  const nameEl = tile.querySelector('.tile-name');
  if (!nameEl) return '';
  return Array.from(nameEl.childNodes)
    .filter(nd => nd.nodeType === Node.TEXT_NODE)
    .map(nd => nd.textContent.trim())
    .join(' ')
    .trim() || nameEl.textContent.trim().split('\n')[0].trim();
}

// ── Start recording ───────────────────────────────────────────────────────────
async function startRecording() {
  const mimeType = MediaRecorder.isTypeSupported('video/webm;codecs=vp9,opus')
    ? 'video/webm;codecs=vp9,opus'
    : MediaRecorder.isTypeSupported('video/webm')
    ? 'video/webm'
    : '';

  if (!mimeType) {
    showToast('Recording not supported in this browser.', 'error');
    return false;
  }

  // ── Composite canvas — 1280 × 720 split layout ──────────────────────────────
  const canvas = document.createElement('canvas');
  canvas.width  = REC_W;
  canvas.height = REC_H;
  const ctx = canvas.getContext('2d');

  const hostPaneW = Math.floor(REC_W * REC_HOST_FRAC);  // 512 px
  const partPaneW = REC_W - hostPaneW;                   // 768 px
  const hostPaneX = partPaneW;                           // starts at x=768

  function drawFrame() {
    // Clear background
    ctx.fillStyle = '#0a0a0a';
    ctx.fillRect(0, 0, REC_W, REC_H);

    // ── Right pane: local (host/self) tile ─────────────────────────────────
    ctx.fillStyle = '#1e293b';
    ctx.fillRect(hostPaneX, 0, hostPaneW, REC_H);

    const localVid = document.getElementById('localVideo');
    if (localVid && localVid.readyState >= 2) {
      try {
        ctx.drawImage(localVid, hostPaneX, 0, hostPaneW, REC_H);
      } catch (_) { /* canvas taint — skip frame */ }
    }

    // Local name label
    const localName = (typeof DISPLAY_NAME !== 'undefined') ? DISPLAY_NAME : 'You';
    _recDrawLabel(ctx, localName, hostPaneX + 8, REC_H - 30, hostPaneW - 16);

    // Thin separator between panes
    ctx.fillStyle = 'rgba(255,255,255,0.1)';
    ctx.fillRect(hostPaneX, 0, 1, REC_H);

    // ── Left pane: participant tiles (speakers first, capped) ───────────────
    const tiles = _getRecTiles();
    const n = tiles.length;

    if (n > 0) {
      // Adaptive column count
      const cols  = n === 1 ? 1 : n <= 4 ? 2 : 3;
      const rows  = Math.ceil(n / cols);
      const tileW = Math.floor(partPaneW / cols);
      const tileH = Math.floor(REC_H / rows);

      tiles.forEach((tile, i) => {
        const col   = i % cols;
        const row   = Math.floor(i / cols);
        const x     = col * tileW;
        const y     = row * tileH;
        const video = tile.querySelector('video');

        ctx.save();
        ctx.beginPath();
        ctx.rect(x, y, tileW, tileH);
        ctx.clip();

        // Video or placeholder
        if (video && video.srcObject && video.readyState >= 2) {
          try {
            ctx.drawImage(video, x, y, tileW, tileH);
          } catch (_) {
            ctx.fillStyle = '#1e2228';
            ctx.fillRect(x, y, tileW, tileH);
          }
        } else {
          ctx.fillStyle = '#1e2228';
          ctx.fillRect(x, y, tileW, tileH);
        }

        // Green border for active speaker
        if (tile.classList.contains('speaking')) {
          ctx.strokeStyle = '#22c55e';
          ctx.lineWidth   = 3;
          ctx.strokeRect(x + 2, y + 2, tileW - 4, tileH - 4);
        }

        // Name label
        _recDrawLabel(ctx, _tileName(tile), x + 4, y + tileH - 28, tileW - 8);

        ctx.restore();
      });
    }

    _recAnimFrameId = requestAnimationFrame(drawFrame);
  }

  drawFrame();
  const canvasStream = canvas.captureStream(30);

  // ── Mix all audio tracks (local mic + all remote participants) ──────────────
  const audioTracks = [];

  try {
    _recAudioCtx  = new (window.AudioContext || window.webkitAudioContext)();
    const mixDest = _recAudioCtx.createMediaStreamDestination();

    // Local microphone
    const localAudio = getLocalStream()?.getAudioTracks()[0];
    if (localAudio) {
      _recAudioCtx.createMediaStreamSource(new MediaStream([localAudio])).connect(mixDest);
    }

    // Remote participants — grab audio from their tile videos
    document.querySelectorAll('.remote-tile video').forEach(v => {
      if (!v.srcObject) return;
      v.srcObject.getAudioTracks().forEach(track => {
        try {
          _recAudioCtx.createMediaStreamSource(new MediaStream([track])).connect(mixDest);
        } catch (_) {}
      });
    });

    audioTracks.push(...mixDest.stream.getAudioTracks());
  } catch (e) {
    console.warn('[Recorder] Audio mix setup failed, falling back to local audio only:', e);
    _recAudioCtx = null;
    const fallback = getLocalStream()?.getAudioTracks()[0];
    if (fallback) audioTracks.push(fallback);
  }

  const compositeStream = new MediaStream([
    ...canvasStream.getVideoTracks(),
    ...audioTracks,
  ]);

  recordedChunks = [];

  try {
    mediaRecorder = new MediaRecorder(compositeStream, { mimeType });
  } catch (e) {
    console.warn('[Recorder] MediaRecorder init failed:', e);
    cancelAnimationFrame(_recAnimFrameId);
    _recAnimFrameId = null;
    _recAudioCtx?.close().catch(() => {});
    _recAudioCtx = null;
    showToast('Recording not supported in this browser.', 'error');
    return false;
  }

  mediaRecorder.ondataavailable = (e) => {
    if (e.data.size > 0) recordedChunks.push(e.data);
  };

  mediaRecorder.onstop = async () => {
    cancelAnimationFrame(_recAnimFrameId);
    _recAnimFrameId = null;
    if (_recAudioCtx) { _recAudioCtx.close().catch(() => {}); _recAudioCtx = null; }

    const blob = new Blob(recordedChunks, { type: mimeType });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = `navulimeet-${MEETING_UUID}-${Date.now()}.webm`;
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);

    const duration = recordingStartTime
      ? Math.round((Date.now() - recordingStartTime) / 1000)
      : 0;
    await notifyRecordingStop(duration);
  };

  mediaRecorder.start(1000);
  recordingStartTime = Date.now();

  await notifyRecordingStart();
  return true;
}

function stopRecording() {
  if (mediaRecorder && mediaRecorder.state !== 'inactive') {
    mediaRecorder.stop();
    mediaRecorder = null;
  }
  // Canvas/audio cleanup runs in onstop callback
}

function isRecording() {
  return mediaRecorder !== null && mediaRecorder.state === 'recording';
}

async function notifyRecordingStart() {
  try {
    await fetch(`${BASE_URL}api/meetings/${MEETING_TOKEN}/recordings`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'Authorization': `Bearer ${API_TOKEN}`,
      },
      body: JSON.stringify({ action: 'start' }),
    });
  } catch (e) {
    console.warn('[Recorder] Could not notify recording start:', e);
  }
}

async function notifyRecordingStop(duration) {
  try {
    await fetch(`${BASE_URL}api/meetings/${MEETING_TOKEN}/recordings/stop`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'Authorization': `Bearer ${API_TOKEN}`,
      },
      body: JSON.stringify({ duration_seconds: duration }),
    });
  } catch (e) {
    console.warn('[Recorder] Could not notify recording stop:', e);
  }
}
