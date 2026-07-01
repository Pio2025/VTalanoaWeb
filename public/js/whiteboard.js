/* VTalanoa — Collaborative Whiteboard */

let _wbOpen      = false;
let _wbCanvas    = null;
let _wbCtx       = null;
let _wbDrawing   = false;
let _wbLastX     = 0;
let _wbLastY     = 0;
let _wbTool      = 'pen';
let _wbColor     = '#ffffff';
let _wbLineWidth = 3;
let _wbStrokes   = [];

// ── Open / Close ──────────────────────────────────────────────────────────
function openWhiteboard() {
  if (_wbOpen) { closeWhiteboard(); return; }
  _wbOpen = true;

  const overlay = document.getElementById('wbOverlay');
  if (overlay) overlay.style.display = 'flex';

  _initCanvas();
  emitSafe('wb-request-state', {});

  document.getElementById('btnWhiteboard')?.classList.add('active');
  document.getElementById('mbWhiteboard')?.classList.add('active');
}

function closeWhiteboard() {
  _wbOpen = false;
  const overlay = document.getElementById('wbOverlay');
  if (overlay) overlay.style.display = 'none';
  document.getElementById('btnWhiteboard')?.classList.remove('active');
  document.getElementById('mbWhiteboard')?.classList.remove('active');
}

// ── Canvas init ───────────────────────────────────────────────────────────
function _initCanvas() {
  _wbCanvas = document.getElementById('wbCanvas');
  if (!_wbCanvas) return;
  _wbCtx = _wbCanvas.getContext('2d');
  _resizeCanvas();

  _wbCanvas.onmousedown  = e => _wbStart(e.offsetX, e.offsetY);
  _wbCanvas.onmousemove  = e => { if (_wbDrawing) _wbMove(e.offsetX, e.offsetY); };
  _wbCanvas.onmouseup    = _wbEnd;
  _wbCanvas.onmouseleave = _wbEnd;

  _wbCanvas.ontouchstart = e => {
    e.preventDefault();
    const r = _wbCanvas.getBoundingClientRect();
    const t = e.touches[0];
    _wbStart(t.clientX - r.left, t.clientY - r.top);
  };
  _wbCanvas.ontouchmove = e => {
    e.preventDefault();
    if (!_wbDrawing) return;
    const r = _wbCanvas.getBoundingClientRect();
    const t = e.touches[0];
    _wbMove(t.clientX - r.left, t.clientY - r.top);
  };
  _wbCanvas.ontouchend = _wbEnd;
}

function _resizeCanvas() {
  if (!_wbCanvas) return;
  const box = _wbCanvas.parentElement.getBoundingClientRect();
  _wbCanvas.width  = box.width;
  _wbCanvas.height = box.height;
  _replayStrokes();
}

function _wbStart(x, y) { _wbDrawing = true; _wbLastX = x; _wbLastY = y; }

function _wbMove(x, y) {
  const stroke = {
    x0: _wbLastX, y0: _wbLastY, x1: x, y1: y,
    color: _wbTool === 'eraser' ? '#0f172a' : _wbColor,
    width: _wbTool === 'eraser' ? _wbLineWidth * 6 : _wbLineWidth,
  };
  _drawStroke(stroke);
  _wbStrokes.push(stroke);
  emitSafe('wb-stroke', stroke);
  _wbLastX = x; _wbLastY = y;
}

function _wbEnd() { _wbDrawing = false; }

function _drawStroke({ x0, y0, x1, y1, color, width }) {
  if (!_wbCtx) return;
  _wbCtx.beginPath();
  _wbCtx.moveTo(x0, y0);
  _wbCtx.lineTo(x1, y1);
  _wbCtx.strokeStyle = color;
  _wbCtx.lineWidth   = width;
  _wbCtx.lineCap     = 'round';
  _wbCtx.lineJoin    = 'round';
  _wbCtx.stroke();
}

function _replayStrokes() {
  if (!_wbCtx || !_wbCanvas) return;
  _wbCtx.clearRect(0, 0, _wbCanvas.width, _wbCanvas.height);
  _wbStrokes.forEach(_drawStroke);
}

// ── Remote events ─────────────────────────────────────────────────────────
function handleRemoteWbStroke(stroke) {
  _wbStrokes.push(stroke);
  if (_wbOpen) _drawStroke(stroke);
}

function handleRemoteWbClear() {
  _wbStrokes = [];
  if (_wbOpen && _wbCtx && _wbCanvas) {
    _wbCtx.clearRect(0, 0, _wbCanvas.width, _wbCanvas.height);
  }
}

function handleWbState({ strokes }) {
  _wbStrokes = strokes || [];
  if (_wbOpen) _replayStrokes();
}

// ── Toolbar controls ──────────────────────────────────────────────────────
function setWbTool(tool) {
  _wbTool = tool;
  document.querySelectorAll('.wb-tool-btn').forEach(b => b.classList.remove('active'));
  document.getElementById(`wbTool${tool.charAt(0).toUpperCase()}${tool.slice(1)}`)?.classList.add('active');
  if (_wbCanvas) _wbCanvas.style.cursor = tool === 'eraser' ? 'cell' : 'crosshair';
}

function setWbColor(color) {
  _wbColor = color;
  document.querySelectorAll('.wb-color-swatch').forEach(b => b.classList.remove('active'));
  document.querySelector(`.wb-color-swatch[data-color="${color}"]`)?.classList.add('active');
  if (color !== '#0f172a') { _wbTool = 'pen'; setWbTool('pen'); }
}

function setWbWidth(w) {
  _wbLineWidth = parseInt(w);
  const lbl = document.getElementById('wbWidthVal');
  if (lbl) lbl.textContent = w + 'px';
}

function wbClear() {
  if (!_wbCtx || !_wbCanvas) return;
  _wbStrokes = [];
  _wbCtx.clearRect(0, 0, _wbCanvas.width, _wbCanvas.height);
  emitSafe('wb-clear', {});
}

function wbDownload() {
  if (!_wbCanvas) return;
  const link = document.createElement('a');
  link.download = `whiteboard-${MEETING_UUID.slice(0,8)}.png`;
  link.href = _wbCanvas.toDataURL();
  link.click();
}

window.addEventListener('resize', () => { if (_wbOpen) _resizeCanvas(); });
