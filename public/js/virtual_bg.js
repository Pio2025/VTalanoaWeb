/* NavuliMeet — Virtual Background (MediaPipe Selfie Segmentation)
 *
 * Optimised for real-time performance using the same architectural
 * choices described by Zoom's engineering team:
 *
 *  1. PRE-WARM  — model is loaded into memory in the background
 *                 immediately after the camera is ready, so toggling
 *                 "blur" pays no model-loading cost.
 *
 *  2. LOW-RES SEGMENTATION — the camera frame is downscaled to
 *                 256 × 144 before being sent to the neural network.
 *                 The network outputs a tiny mask which is then
 *                 upscaled back to 640 × 360. Running the model on
 *                 ~4× fewer pixels cuts per-frame cost dramatically.
 *
 *  3. TEMPORAL COHERENCE — a persistent mask canvas is blended
 *                 75 % new / 25 % previous each frame. The person
 *                 silhouette barely changes frame-to-frame, so this
 *                 smooths flicker and lets the system tolerate skipped
 *                 frames without visual jarring.
 *
 *  4. EDGE FEATHERING — the low-res mask is upscaled with a small
 *                 blur (blur(3px)) applied at composite time, giving
 *                 soft, anti-aliased person edges instead of a blocky
 *                 hard cutout.
 *
 *  5. LIGHTER MODEL — modelSelection: 0 (general, 256 × 144 input)
 *                 instead of 1 (landscape, 256 × 256). Matches the
 *                 low-res pipeline and avoids unnecessary model weight.
 */

// ── Constants ─────────────────────────────────────────────────────────────────
const BG_W = 640, BG_H = 360;   // output / compositing canvas resolution
const SEG_W = 256, SEG_H = 144; // segmentation input resolution (low-res)
const SEG_BLEND = 0.75;          // temporal mask weight: 75% new, 25% previous

// ── State ─────────────────────────────────────────────────────────────────────
let _bgEffect    = 'none';  // 'none' | 'blur' | 'image'
let _bgImage     = null;    // HTMLImageElement — virtual background
let _bgSeg       = null;    // SelfieSegmentation instance (once loaded, reused)
let _bgLoading   = false;
let _bgRunning   = false;
let _bgAnimId    = null;
let _bgStream    = null;    // canvas.captureStream() → outgoing video track

// Hidden video element that always reads raw localStream (never the output)
let _rawVid      = null;

// Temporal coherence: two-canvas linear alpha interpolation.
// _maskCanvas  = the blended mask used for compositing this frame.
// _prevMaskCanvas = snapshot of the previous frame's mask (for the blend).
// Using 'lighter' composite: result_alpha = 0.25*prev + 0.75*new exactly,
// which lets old positions fade out when the person moves away.
let _maskCanvas      = null;
let _maskCtx         = null;
let _prevMaskCanvas  = null;
let _prevMaskCtx     = null;

// Touch-up pipeline (active when BG effect is 'none')
let _touchUpEnabled  = false;
let _touchUpStream   = null;
let _touchUpAnimId   = null;
let _touchUpRunning  = false;
let _touchUpRawVid   = null;  // reads localStream directly — never the output

// ── Built-in background gallery ───────────────────────────────────────────────
// Each entry is [colorStop0, colorStop1] for a 135° diagonal gradient.
const _BUILTIN_BGS = [
  ['#1a2980', '#26d0ce'],  // Ocean
  ['#134e5e', '#71b280'],  // Forest
  ['#f093fb', '#f5576c'],  // Sunset
  ['#0f0c29', '#302b63'],  // Galaxy
  ['#ff6b6b', '#ffa726'],  // Coral
  ['#00b09b', '#96c93d'],  // Aurora
  ['#141e30', '#243b55'],  // Royal Navy
  ['#4b6cb7', '#182848'],  // Dusk
];
let _builtinBgImages = null;  // lazily rendered on first use

function _ensureBuiltinBgs() {
  if (_builtinBgImages) return;
  _builtinBgImages = _BUILTIN_BGS.map(([a, b]) => {
    const c = document.createElement('canvas');
    c.width = 640; c.height = 360;
    const ctx = c.getContext('2d');
    const g = ctx.createLinearGradient(0, 0, 640, 360);
    g.addColorStop(0, a); g.addColorStop(1, b);
    ctx.fillStyle = g;
    ctx.fillRect(0, 0, 640, 360);
    const img = new Image();
    img.src = c.toDataURL('image/jpeg', 0.9);
    return img;
  });
}

function selectBuiltinBg(idx) {
  _ensureBuiltinBgs();
  document.querySelectorAll('.bg-thumb').forEach((el, i) =>
    el.classList.toggle('active', i === idx));
  _bgImage = _builtinBgImages[idx];
  applyBgEffect('image');
}

// ── Pre-warm (called from webrtc.js after initLocalStream resolves) ───────────
//
// Loads and initialises the segmentation model in the background so that
// when the user first clicks "Blur" the model is already in memory and
// the effect feels instant.

async function warmBgSegmenter() {
  if (_bgSeg || _bgLoading) return; // already warm or loading
  console.log('[BG] Pre-warming segmenter in background…');
  await _bgLoadSegmenter();
  if (_bgSeg) console.log('[BG] Segmenter warm — blur will be instant');
}

// ── Public API ────────────────────────────────────────────────────────────────

async function applyBgEffect(effect) {
  ['bgNone', 'bgBlur', 'bgImage'].forEach(id =>
    document.getElementById(id)?.classList.remove('active'));
  const activeId = { none: 'bgNone', blur: 'bgBlur', image: 'bgImage' }[effect];
  document.getElementById(activeId)?.classList.add('active');

  const picker = document.getElementById('bgImagePicker');
  if (picker) picker.style.display = effect === 'image' ? 'block' : 'none';

  _bgEffect = effect;

  if (effect === 'none') {
    _bgStop(); // restarts touch-up pipeline if needed
    return;
  }

  // BG pipeline takes over — stop standalone touch-up (filter is integrated below)
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

// ── Segmenter init ────────────────────────────────────────────────────────────

async function _bgLoadSegmenter() {
  if (_bgSeg) return _bgSeg;

  if (_bgLoading) {
    // Another call is already initialising — wait for it
    await new Promise(res => {
      const t = setInterval(() => { if (!_bgLoading) { clearInterval(t); res(); } }, 100);
    });
    return _bgSeg;
  }

  _bgLoading = true;

  try {
    if (typeof SelfieSegmentation === 'undefined') {
      const PRIMARY  = 'https://cdn.jsdelivr.net/npm/@mediapipe/selfie_segmentation@0.1.1675465747/selfie_segmentation.js';
      const FALLBACK = 'https://unpkg.com/@mediapipe/selfie_segmentation@0.1.1675465747/selfie_segmentation.js';

      await new Promise((res, rej) => {
        const tryLoad = (src, fb) => {
          const s = document.createElement('script');
          s.src = src; s.crossOrigin = 'anonymous';
          s.onload = res;
          s.onerror = () => {
            if (fb) { console.warn('[BG] Primary CDN failed, trying fallback…'); tryLoad(fb, null); }
            else      rej(new Error('Failed to load MediaPipe from all CDNs'));
          };
          document.head.appendChild(s);
        };
        tryLoad(PRIMARY, FALLBACK);
      });
    }

    _bgSeg = new SelfieSegmentation({
      locateFile: f => `https://cdn.jsdelivr.net/npm/@mediapipe/selfie_segmentation@0.1.1675465747/${f}`,
    });

    // modelSelection 0 = general model (256×144 input resolution) — lighter and
    // perfectly matched to our low-res segmentation pipeline.
    // modelSelection 1 = landscape model (256×256) — heavier, no benefit at 256×144.
    _bgSeg.setOptions({ modelSelection: 0 });

    await _bgSeg.initialize();

  } catch (e) {
    console.error('[BG] Segmenter failed to initialise:', e);
    _bgSeg    = null;
    _bgLoading = false;
    return null;
  }

  _bgLoading = false;
  return _bgSeg;
}

// ── Processing pipeline ───────────────────────────────────────────────────────

async function _bgStart() {
  _bgStop(); // clean up any previous session first

  // Some mobile browsers only allow one camera consumer at a time
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

  // If the model is already warm this toast appears for only a fraction of a second
  showToast('Loading background effect…', 'default');

  const seg = await _bgLoadSegmenter();
  if (!seg) {
    showToast('Background effects could not be loaded. Check your connection.', 'error');
    _bgEffect = 'none';
    document.getElementById('bgNone')?.classList.add('active');
    return;
  }

  // ── Raw camera source ─────────────────────────────────────────────────────
  // A hidden video element permanently connected to the raw localStream.
  // We MUST NOT read from localVid for pixel data: once _bgStream is assigned
  // to localVid.srcObject (below), localVid shows the output canvas — reading
  // from it creates a feedback loop (segmenter processes its own output → grey).
  _rawVid = document.createElement('video');
  _rawVid.srcObject = (typeof localStream !== 'undefined' && localStream)
    ? localStream
    : localVid.srcObject;
  _rawVid.autoplay    = true;
  _rawVid.playsInline = true;
  _rawVid.muted       = true;
  await _rawVid.play().catch(() => {});

  // ── Canvas setup ───────────────────────────────────────────────────────────

  // Low-res input canvas: downscaled camera frame sent to the neural network.
  // The network sees 256×144 instead of 640×360 — ~4× fewer pixels to classify.
  const segCanvas = document.createElement('canvas');
  segCanvas.width = SEG_W; segCanvas.height = SEG_H;
  const segCtx = segCanvas.getContext('2d');

  // Two-canvas temporal mask: current blend + previous-frame snapshot.
  // 'lighter' composite gives exact linear alpha interpolation:
  //   result_alpha = (1-SEG_BLEND)*prev_alpha + SEG_BLEND*new_alpha
  // This lets old-position pixels fade out when the person moves, instead
  // of staying stuck at alpha=255 (which leaks real camera onto virtual bg).
  _maskCanvas = document.createElement('canvas');
  _maskCanvas.width = SEG_W; _maskCanvas.height = SEG_H;
  _maskCtx = _maskCanvas.getContext('2d');
  _maskCtx.clearRect(0, 0, SEG_W, SEG_H);

  _prevMaskCanvas = document.createElement('canvas');
  _prevMaskCanvas.width = SEG_W; _prevMaskCanvas.height = SEG_H;
  _prevMaskCtx = _prevMaskCanvas.getContext('2d');
  _prevMaskCtx.clearRect(0, 0, SEG_W, SEG_H);

  // Person canvas: full-res camera frame, cut out by the blended+feathered mask
  const personCanvas = document.createElement('canvas');
  personCanvas.width = BG_W; personCanvas.height = BG_H;
  const personCtx = personCanvas.getContext('2d');

  // Output canvas: composited result sent to the remote SFU
  const outCanvas = document.createElement('canvas');
  outCanvas.width = BG_W; outCanvas.height = BG_H;
  const outCtx = outCanvas.getContext('2d');

  let segBusy = false;

  seg.onResults(({ segmentationMask }) => {
    if (!segmentationMask) { segBusy = false; return; }

    // ── 1. TEMPORAL MASK BLENDING (proper linear alpha interpolation) ────────
    // Step A: snapshot _maskCanvas → _prevMaskCanvas before overwriting it.
    _prevMaskCtx.clearRect(0, 0, SEG_W, SEG_H);
    _prevMaskCtx.globalCompositeOperation = 'copy';
    _prevMaskCtx.drawImage(_maskCanvas, 0, 0);
    _prevMaskCtx.globalCompositeOperation = 'source-over';

    // Step B: clear the current mask canvas (reset to transparent).
    _maskCtx.clearRect(0, 0, SEG_W, SEG_H);

    // Step C: draw prev at (1-SEG_BLEND)=25% weight via source-over onto transparent.
    //   result_alpha so far = 0.25 * prev_alpha
    _maskCtx.globalAlpha = 1 - SEG_BLEND;
    _maskCtx.drawImage(_prevMaskCanvas, 0, 0);
    _maskCtx.globalAlpha = 1.0;

    // Step D: add new mask at SEG_BLEND=75% weight using 'lighter' (additive).
    //   'lighter' adds alpha values: result_alpha = 0.25*prev + 0.75*new_alpha
    //   Old positions (new_alpha=0) fade to 25% of prev → exponential decay ✓
    //   New positions (new_alpha=255) immediately appear at 75% → fade in ✓
    _maskCtx.globalCompositeOperation = 'lighter';
    _maskCtx.globalAlpha = SEG_BLEND;
    _maskCtx.drawImage(segmentationMask, 0, 0, SEG_W, SEG_H);
    _maskCtx.globalAlpha = 1.0;
    _maskCtx.globalCompositeOperation = 'source-over';

    // ── 2. PERSON ISOLATION with edge feathering ──────────────────────────
    // Draw the full-res raw camera into personCanvas, then cut using the mask.
    // _rawVid always reads localStream directly — never the output canvas.
    // Upscaling the small mask with blur(3px) feathers the person silhouette.
    personCtx.clearRect(0, 0, BG_W, BG_H);
    personCtx.drawImage(_rawVid, 0, 0, BG_W, BG_H);        // full-res raw camera
    personCtx.globalCompositeOperation = 'destination-in';
    personCtx.filter = 'blur(3px)';                         // feather edges on upscale
    personCtx.drawImage(_maskCanvas, 0, 0, BG_W, BG_H);    // 256×144 → 640×360 upscale
    personCtx.filter = 'none';
    personCtx.globalCompositeOperation = 'source-over';

    // ── 3. BACKGROUND LAYER ───────────────────────────────────────────────
    if (_bgEffect === 'blur') {
      outCtx.filter = 'blur(18px)';
      outCtx.drawImage(_rawVid, 0, 0, BG_W, BG_H);         // full-res raw camera, blurred
      outCtx.filter = 'none';
    } else if (_bgEffect === 'image' && _bgImage) {
      // Cover-fit the user's background image
      const iAr = _bgImage.naturalWidth / _bgImage.naturalHeight;
      const cAr = BG_W / BG_H;
      let sx = 0, sy = 0, sw = _bgImage.naturalWidth, sh = _bgImage.naturalHeight;
      if (iAr > cAr) { sw = sh * cAr; sx = (_bgImage.naturalWidth  - sw) / 2; }
      else            { sh = sw / cAr; sy = (_bgImage.naturalHeight - sh) / 2; }
      outCtx.drawImage(_bgImage, sx, sy, sw, sh, 0, 0, BG_W, BG_H);
    } else {
      outCtx.drawImage(_rawVid, 0, 0, BG_W, BG_H);
    }

    // ── 4. COMPOSITE PERSON ON BACKGROUND ─────────────────────────────────
    if (_touchUpEnabled) outCtx.filter = 'brightness(1.03) contrast(0.95) saturate(0.93)';
    outCtx.drawImage(personCanvas, 0, 0, BG_W, BG_H);
    outCtx.filter = 'none';

    segBusy = false;
  });

  _bgRunning = true;

  function tick() {
    if (!_bgRunning) return;

    if (!segBusy && _rawVid.videoWidth > 0) {
      segBusy = true;
      // Downscale raw camera to 256×144 for the segmenter.
      // Always read from _rawVid — never localVid (which shows the output).
      segCtx.drawImage(_rawVid, 0, 0, SEG_W, SEG_H);

      seg.send({ image: segCanvas }).catch(err => {
        console.warn('[BG] send error:', err);
        segBusy = false;
      });
    }

    _bgAnimId = requestAnimationFrame(tick);
  }

  tick();

  _bgStream = outCanvas.captureStream(30);
  const newTrack = _bgStream.getVideoTracks()[0];
  if (newTrack && typeof replaceVideoTrack === 'function') replaceVideoTrack(newTrack);

  // Show processed output in local tile — after this point localVid.srcObject
  // is _bgStream, which is why _rawVid must be used for all pixel reads above.
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

  // Release the hidden raw camera video element
  if (_rawVid) { _rawVid.srcObject = null; _rawVid = null; }

  // Discard temporal mask canvases — re-created next time _bgStart runs
  _maskCanvas = null;     _maskCtx     = null;
  _prevMaskCanvas = null; _prevMaskCtx = null;

  // Restore original camera stream to local tile and outgoing track
  if (typeof localStream !== 'undefined' && localStream) {
    const origTrack  = localStream.getVideoTracks()[0];
    const localVidEl = document.getElementById('localVideo');
    if (localVidEl) localVidEl.srcObject = localStream;
    if (origTrack && typeof replaceVideoTrack === 'function') replaceVideoTrack(origTrack);
  }

  // Restart the standalone touch-up pipeline if it was on
  if (_touchUpEnabled) setTimeout(_startTouchUpPipeline, 80);
}

// ── Touch Up ──────────────────────────────────────────────────────────────────

function setTouchUp(enabled) {
  _touchUpEnabled = enabled;
  document.getElementById('touchUpOff')?.classList.toggle('active', !enabled);
  document.getElementById('touchUpOn')?.classList.toggle('active',  enabled);

  if (_bgEffect !== 'none') {
    // BG pipeline is running — filter applied inline in onResults, nothing else needed
    return;
  }

  if (enabled) _startTouchUpPipeline(); else _stopTouchUpPipeline();
}

function _startTouchUpPipeline() {
  _stopTouchUpPipeline();

  if (typeof localStream === 'undefined' || !localStream) return;

  // Hidden video permanently connected to the raw camera (never the output canvas).
  // Without this, setting localVid.srcObject = _touchUpStream below causes
  // subsequent tick() calls to read the processed output and compound the filter
  // every frame (brightness keeps rising, contrast/saturation keep falling).
  _touchUpRawVid = document.createElement('video');
  _touchUpRawVid.srcObject  = localStream;
  _touchUpRawVid.autoplay    = true;
  _touchUpRawVid.playsInline = true;
  _touchUpRawVid.muted       = true;
  _touchUpRawVid.play().catch(() => {});

  const canvas = document.createElement('canvas');
  canvas.width = BG_W; canvas.height = BG_H;
  const ctx = canvas.getContext('2d');

  _touchUpRunning = true;

  function tick() {
    if (!_touchUpRunning) return;
    if (_touchUpRawVid && _touchUpRawVid.videoWidth > 0) {
      ctx.filter = 'brightness(1.03) contrast(0.95) saturate(0.93)';
      ctx.drawImage(_touchUpRawVid, 0, 0, BG_W, BG_H);
      ctx.filter = 'none';
    }
    _touchUpAnimId = requestAnimationFrame(tick);
  }

  tick();
  _touchUpStream = canvas.captureStream(30);

  const newTrack = _touchUpStream.getVideoTracks()[0];
  if (newTrack && typeof replaceVideoTrack === 'function') replaceVideoTrack(newTrack);

  const localVidEl = document.getElementById('localVideo');
  if (localVidEl) localVidEl.srcObject = _touchUpStream;

  showToast('Touch Up My Appearance enabled.', 'success');
}

function _stopTouchUpPipeline() {
  _touchUpRunning = false;
  if (_touchUpAnimId !== null) { cancelAnimationFrame(_touchUpAnimId); _touchUpAnimId = null; }
  if (_touchUpStream)  { _touchUpStream.getTracks().forEach(t => t.stop()); _touchUpStream = null; }
  if (_touchUpRawVid)  { _touchUpRawVid.srcObject = null; _touchUpRawVid = null; }

  if (typeof localStream !== 'undefined' && localStream) {
    const origTrack  = localStream.getVideoTracks()[0];
    const localVidEl = document.getElementById('localVideo');
    if (localVidEl) localVidEl.srcObject = localStream;
    if (origTrack && typeof replaceVideoTrack === 'function') replaceVideoTrack(origTrack);
  }
}
