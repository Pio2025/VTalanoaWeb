/* NavuliMeet — Virtual Background (MediaPipe Selfie Segmentation)
 *
 * Supports: blur background | replace background with custom image
 * The outgoing video track is swapped to a canvas stream processed by the
 * segmenter so remote participants see the effect too, not just the local tile.
 */

const BG_W = 640, BG_H = 360;

let _bgEffect       = 'none';   // 'none' | 'blur' | 'image'
let _bgImage        = null;     // HTMLImageElement for virtual background
let _bgSeg          = null;     // SelfieSegmentation instance
let _bgLoading      = false;
let _bgRunning      = false;
let _bgAnimId       = null;
let _bgStream       = null;     // canvas.captureStream() — the outgoing video

// ── Touch Up pipeline (standalone when no BG effect is active) ─
let _touchUpEnabled = false;
let _touchUpStream  = null;
let _touchUpAnimId  = null;
let _touchUpRunning = false;

// ── Public API ──────────────────────────────────────────────────────────────

async function applyBgEffect(effect) {
  // Update button states
  ['bgNone', 'bgBlur', 'bgImage'].forEach(id => {
    document.getElementById(id)?.classList.remove('active');
  });
  const activeId = { none: 'bgNone', blur: 'bgBlur', image: 'bgImage' }[effect];
  document.getElementById(activeId)?.classList.add('active');

  const picker = document.getElementById('bgImagePicker');
  if (picker) picker.style.display = effect === 'image' ? 'block' : 'none';

  _bgEffect = effect;

  if (effect === 'none') {
    _bgStop(); // _bgStop re-enables touch-up pipeline if needed
    return;
  }

  // BG pipeline takes over — stop standalone touch-up (filter integrated in pipeline)
  _stopTouchUpPipeline();

  if (effect === 'image' && !_bgImage) {
    showToast('Upload or select a background image first.', 'default');
    return;
  }

  await _bgStart();
}

async function loadCustomBg(input) {
  const file = input.files[0];
  if (!file) return;

  const url = URL.createObjectURL(file);
  const img = new Image();
  img.onload = async () => {
    _bgImage = img;
    if (_bgEffect === 'image') await _bgStart();
  };
  img.onerror = () => showToast('Could not load image.', 'error');
  img.src = url;
}

// ── Segmenter init (lazy — loads MediaPipe on first use) ─────────────────────

async function _bgLoadSegmenter() {
  if (_bgSeg) return _bgSeg;

  // Wait if another call is already initialising
  if (_bgLoading) {
    await new Promise(res => {
      const t = setInterval(() => { if (!_bgLoading) { clearInterval(t); res(); } }, 100);
    });
    return _bgSeg;
  }

  _bgLoading = true;

  try {
    // Load MediaPipe Selfie Segmentation script dynamically
    if (typeof SelfieSegmentation === 'undefined') {
      const PRIMARY_CDN  = 'https://cdn.jsdelivr.net/npm/@mediapipe/selfie_segmentation@0.1.1675465747/selfie_segmentation.js';
      const FALLBACK_CDN = 'https://unpkg.com/@mediapipe/selfie_segmentation@0.1.1675465747/selfie_segmentation.js';
      await new Promise((res, rej) => {
        const tryLoad = (src, fallback) => {
          const s = document.createElement('script');
          s.src         = src;
          s.crossOrigin = 'anonymous';
          s.onload = res;
          s.onerror = () => {
            if (fallback) {
              console.warn('[BG] Primary CDN failed, trying fallback…');
              tryLoad(fallback, null);
            } else {
              rej(new Error('Failed to load MediaPipe script from all CDNs'));
            }
          };
          document.head.appendChild(s);
        };
        tryLoad(PRIMARY_CDN, FALLBACK_CDN);
      });
    }

    _bgSeg = new SelfieSegmentation({
      locateFile: f =>
        `https://cdn.jsdelivr.net/npm/@mediapipe/selfie_segmentation@0.1.1675465747/${f}`,
    });
    _bgSeg.setOptions({ modelSelection: 1 }); // 1 = landscape model (higher accuracy)
    await _bgSeg.initialize();
  } catch (e) {
    console.error('[BG] Segmenter failed to load:', e);
    _bgSeg    = null;
    _bgLoading = false;
    return null;
  }

  _bgLoading = false;
  return _bgSeg;
}

// ── Processing pipeline ──────────────────────────────────────────────────────

async function _bgStart() {
  _bgStop(); // clean up any previous session

  // Stop settings preview before starting BG effect to free the camera
  // (some mobile browsers only allow one camera consumer at a time)
  if (typeof settingsPreviewStream !== 'undefined' && settingsPreviewStream) {
    settingsPreviewStream.getTracks().forEach(t => t.stop());
    settingsPreviewStream = null;
    const prev = document.getElementById('settingsPreview');
    if (prev) prev.srcObject = null;
  }

  const localVid = document.getElementById('localVideo');
  if (!localVid?.srcObject) {
    showToast('Camera not ready for background effects.', 'error');
    return;
  }

  showToast('Loading background effect…', 'default');

  const seg = await _bgLoadSegmenter();
  if (!seg) {
    showToast('Background effects could not be loaded. Check your internet connection.', 'error');
    _bgEffect = 'none';
    document.getElementById('bgNone')?.classList.add('active');
    return;
  }

  // Output canvas — its captureStream becomes the outgoing video track
  const outCanvas = document.createElement('canvas');
  outCanvas.width = BG_W; outCanvas.height = BG_H;
  const outCtx = outCanvas.getContext('2d');

  // Person canvas — original frame clipped by the segmentation mask
  const personCanvas = document.createElement('canvas');
  personCanvas.width = BG_W; personCanvas.height = BG_H;
  const personCtx = personCanvas.getContext('2d');

  let segBusy = false;

  seg.onResults(({ image, segmentationMask }) => {
    if (!outCtx || !segmentationMask) { segBusy = false; return; }

    // 1. Isolate person pixels using the mask (white = person, black = background)
    personCtx.clearRect(0, 0, BG_W, BG_H);
    personCtx.drawImage(image, 0, 0, BG_W, BG_H);
    personCtx.globalCompositeOperation = 'destination-in';
    personCtx.drawImage(segmentationMask, 0, 0, BG_W, BG_H);
    personCtx.globalCompositeOperation = 'source-over';

    // 2. Draw background layer
    if (_bgEffect === 'blur') {
      outCtx.filter = 'blur(18px)';
      outCtx.drawImage(image, 0, 0, BG_W, BG_H);
      outCtx.filter = 'none';
    } else if (_bgEffect === 'image' && _bgImage) {
      // Letterbox/cover the background image to fill the canvas
      const imgAr    = _bgImage.naturalWidth / _bgImage.naturalHeight;
      const canvasAr = BG_W / BG_H;
      let sx = 0, sy = 0, sw = _bgImage.naturalWidth, sh = _bgImage.naturalHeight;
      if (imgAr > canvasAr) {
        sw = sh * canvasAr;
        sx = (_bgImage.naturalWidth - sw) / 2;
      } else {
        sh = sw / canvasAr;
        sy = (_bgImage.naturalHeight - sh) / 2;
      }
      outCtx.drawImage(_bgImage, sx, sy, sw, sh, 0, 0, BG_W, BG_H);
    } else {
      outCtx.drawImage(image, 0, 0, BG_W, BG_H);
    }

    // 3. Person on top of background (with optional touch-up softening)
    if (_touchUpEnabled) outCtx.filter = 'brightness(1.03) contrast(0.95) saturate(0.93)';
    outCtx.drawImage(personCanvas, 0, 0, BG_W, BG_H);
    outCtx.filter = 'none';

    segBusy = false;
  });

  _bgRunning = true;

  function tick() {
    if (!_bgRunning) return;

    if (!segBusy && localVid.videoWidth > 0) {
      segBusy = true;
      seg.send({ image: localVid }).catch(err => {
        console.warn('[BG] send error:', err);
        segBusy = false;
      });
    }

    _bgAnimId = requestAnimationFrame(tick);
  }

  tick();

  _bgStream = outCanvas.captureStream(30);
  const newTrack = _bgStream.getVideoTracks()[0];
  if (newTrack && typeof replaceVideoTrack === 'function') {
    replaceVideoTrack(newTrack);
  }

  // Show processed output in the local tile so the user sees the effect
  const localVidEl = document.getElementById('localVideo');
  if (localVidEl) localVidEl.srcObject = _bgStream;

  showToast(
    _bgEffect === 'blur' ? 'Background blur active' : 'Virtual background active',
    'success',
  );
}

function _bgStop() {
  _bgRunning = false;
  if (_bgAnimId !== null) { cancelAnimationFrame(_bgAnimId); _bgAnimId = null; }
  if (_bgStream) { _bgStream.getTracks().forEach(t => t.stop()); _bgStream = null; }

  // Restore original camera stream to local tile and outgoing track
  if (typeof localStream !== 'undefined' && localStream) {
    const origTrack = localStream.getVideoTracks()[0];
    const localVidEl = document.getElementById('localVideo');
    if (localVidEl) localVidEl.srcObject = localStream;
    if (origTrack && typeof replaceVideoTrack === 'function') replaceVideoTrack(origTrack);
  }

  // If touch-up was on, restart its standalone pipeline now that BG is gone
  if (_touchUpEnabled) setTimeout(_startTouchUpPipeline, 80);
}

// ── Touch Up public API ──────────────────────────────────────────────────────

function setTouchUp(enabled) {
  _touchUpEnabled = enabled;

  document.getElementById('touchUpOff')?.classList.toggle('active', !enabled);
  document.getElementById('touchUpOn')?.classList.toggle('active',  enabled);

  if (_bgEffect !== 'none') {
    // BG pipeline is running — the filter is applied inline, nothing else to do
    return;
  }

  if (enabled) {
    _startTouchUpPipeline();
  } else {
    _stopTouchUpPipeline();
  }
}

function _startTouchUpPipeline() {
  _stopTouchUpPipeline();

  const localVid = document.getElementById('localVideo');
  if (!localVid?.srcObject) return;

  const canvas = document.createElement('canvas');
  canvas.width = BG_W; canvas.height = BG_H;
  const ctx = canvas.getContext('2d');

  _touchUpRunning = true;

  function tick() {
    if (!_touchUpRunning) return;
    if (localVid.videoWidth > 0) {
      ctx.filter = 'brightness(1.03) contrast(0.95) saturate(0.93)';
      ctx.drawImage(localVid, 0, 0, BG_W, BG_H);
      ctx.filter = 'none';
    }
    _touchUpAnimId = requestAnimationFrame(tick);
  }

  tick();
  _touchUpStream = canvas.captureStream(30);

  const newTrack = _touchUpStream.getVideoTracks()[0];
  if (newTrack && typeof replaceVideoTrack === 'function') replaceVideoTrack(newTrack);
  if (localVid) localVid.srcObject = _touchUpStream;

  showToast('Touch Up My Appearance enabled.', 'success');
}

function _stopTouchUpPipeline() {
  _touchUpRunning = false;
  if (_touchUpAnimId !== null) { cancelAnimationFrame(_touchUpAnimId); _touchUpAnimId = null; }
  if (_touchUpStream) { _touchUpStream.getTracks().forEach(t => t.stop()); _touchUpStream = null; }

  // Restore original stream
  if (typeof localStream !== 'undefined' && localStream) {
    const origTrack  = localStream.getVideoTracks()[0];
    const localVidEl = document.getElementById('localVideo');
    if (localVidEl) localVidEl.srcObject = localStream;
    if (origTrack && typeof replaceVideoTrack === 'function') replaceVideoTrack(origTrack);
  }
}
