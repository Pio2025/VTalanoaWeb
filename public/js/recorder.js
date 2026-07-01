/* NavuliMeet — MediaRecorder Wrapper (composite: all tiles + mixed audio) */

let mediaRecorder      = null;
let recordedChunks     = [];
let recordingStartTime = null;
let _recAnimFrameId    = null;
let _recAudioCtx       = null;

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

  const grid = document.getElementById('videoGrid');
  if (!grid) {
    showToast('No video grid to record.', 'error');
    return false;
  }

  // ── Composite canvas — mirrors what the host sees ──────────────────────────
  const canvas = document.createElement('canvas');
  canvas.width  = 1280;
  canvas.height = 720;
  const ctx = canvas.getContext('2d');

  function drawFrame() {
    ctx.fillStyle = '#0a0a0a';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    const tiles = grid.querySelectorAll('.video-tile');
    const n     = tiles.length;

    if (!n) { _recAnimFrameId = requestAnimationFrame(drawFrame); return; }

    const cols  = n <= 1 ? 1 : n <= 4 ? 2 : n <= 9 ? 3 : 4;
    const rows  = Math.ceil(n / cols);
    const tileW = Math.floor(canvas.width  / cols);
    const tileH = Math.floor(canvas.height / rows);

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

      // Name label at bottom of tile
      const nameEl = tile.querySelector('.tile-name');
      if (nameEl) {
        const name = Array.from(nameEl.childNodes)
          .filter(nd => nd.nodeType === Node.TEXT_NODE)
          .map(nd => nd.textContent.trim())
          .join(' ')
          .trim() || nameEl.textContent.trim().split('\n')[0].trim();

        if (name) {
          ctx.font      = '13px Inter, Arial, sans-serif';
          const lw      = Math.min(ctx.measureText(name).width + 16, tileW - 8);
          const lh      = 22;
          ctx.fillStyle = 'rgba(0,0,0,0.6)';
          ctx.fillRect(x + 4, y + tileH - lh - 4, lw, lh);
          ctx.fillStyle    = '#fff';
          ctx.textAlign    = 'left';
          ctx.textBaseline = 'middle';
          ctx.fillText(name, x + 10, y + tileH - lh / 2 - 4, lw - 12);
        }
      }

      ctx.restore();
    });

    _recAnimFrameId = requestAnimationFrame(drawFrame);
  }

  drawFrame();
  const canvasStream = canvas.captureStream(30);

  // ── Mix all audio tracks (local mic + all remote participants) ─────────────
  const audioTracks = [];

  try {
    _recAudioCtx  = new (window.AudioContext || window.webkitAudioContext)();
    const mixDest = _recAudioCtx.createMediaStreamDestination();

    // Local microphone
    const localAudio = getLocalStream()?.getAudioTracks()[0];
    if (localAudio) {
      _recAudioCtx.createMediaStreamSource(new MediaStream([localAudio])).connect(mixDest);
    }

    // Remote participants — grab audio tracks from their video srcObjects
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

    const duration = recordingStartTime ? Math.round((Date.now() - recordingStartTime) / 1000) : 0;
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
