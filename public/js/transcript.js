/* VTalanoa — Live Transcription Engine (Web Speech API)
 *
 * Each participant transcribes their own microphone. Finalized segments are
 * broadcast via Socket.IO so all clients build a shared meeting transcript.
 */

let _recognition  = null;
let _isTranscribing = false;
let _transcript   = [];   // { speaker, text, timestamp }[]
let _segmentCount = 0;

function isTranscriptionSupported() {
  return !!(window.SpeechRecognition || window.webkitSpeechRecognition);
}

function startTranscribing() {
  if (!isTranscriptionSupported()) {
    showToast('Live transcription needs Chrome or Edge — not supported in this browser.', 'error');
    return false;
  }
  if (_isTranscribing) return true;

  const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
  _recognition = new SpeechRecognition();
  _recognition.continuous       = true;
  _recognition.interimResults   = true;
  _recognition.maxAlternatives  = 1;
  _recognition.lang             = 'en-US';

  _recognition.onresult = (event) => {
    let interim = '';
    for (let i = event.resultIndex; i < event.results.length; i++) {
      const alt = event.results[i][0].transcript;
      if (event.results[i].isFinal) {
        const text = alt.trim();
        if (text) _commitSegment(DISPLAY_NAME, text);
      } else {
        interim += alt;
      }
    }
    _updateInterimDisplay(interim);
  };

  _recognition.onerror = (e) => {
    console.warn('[Transcript] error:', e.error);
    if (e.error === 'not-allowed' || e.error === 'service-not-allowed') {
      showToast('Microphone access denied — transcription stopped.', 'error');
      _setTranscribing(false);
    }
  };

  // Auto-restart on end (browser stops after silence)
  _recognition.onend = () => {
    if (_isTranscribing) {
      try { _recognition.start(); } catch (_) {}
    }
  };

  try {
    _recognition.start();
    _setTranscribing(true);
    return true;
  } catch (e) {
    console.error('[Transcript] start failed:', e);
    return false;
  }
}

function stopTranscribing() {
  _setTranscribing(false);
  _recognition?.stop();
  _recognition = null;
  _updateInterimDisplay('');
}

function toggleTranscription() {
  if (_isTranscribing) {
    stopTranscribing();
    showToast('Transcription stopped.', 'default');
  } else {
    if (startTranscribing()) {
      showToast('Live transcription started. Your speech is being captured.', 'default');
    }
  }
}

function addRemoteTranscriptSegment(speaker, text) {
  _commitSegment(speaker, text, false);
}

function getTranscriptText() {
  return _transcript
    .map(e => `[${e.timestamp}] ${e.speaker}: ${e.text}`)
    .join('\n');
}

function getTranscriptCount() {
  return _transcript.length;
}

function clearTranscript() {
  _transcript = [];
  _segmentCount = 0;
  const panel = document.getElementById('transcriptContent');
  if (panel) panel.innerHTML = '<div class="transcript-empty">Transcript cleared.</div>';
  _updateAiCount(0);
}

// ── Internal ──────────────────────────────────────────────────────────────────

function _commitSegment(speaker, text, share = true) {
  const ts = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  const entry = { speaker, text, timestamp: ts };
  _transcript.push(entry);
  _segmentCount++;
  _renderEntry(entry);
  _updateAiCount(_segmentCount);

  if (share) {
    // Broadcast to all other participants
    if (typeof emitSafe === 'function') emitSafe('transcript-segment', { speaker, text });
  }
}

function _renderEntry(entry) {
  const panel = document.getElementById('transcriptContent');
  if (!panel) return;

  // Remove empty placeholder on first segment
  const placeholder = panel.querySelector('.transcript-empty');
  if (placeholder) placeholder.remove();

  const el = document.createElement('div');
  el.className = 'transcript-item';
  el.innerHTML =
    `<span class="transcript-time">${entry.timestamp}</span>` +
    `<span class="transcript-speaker">${escapeHtml(entry.speaker)}:</span>` +
    `<span class="transcript-text">${escapeHtml(entry.text)}</span>`;
  panel.appendChild(el);
  panel.scrollTop = panel.scrollHeight;
}

function _updateInterimDisplay(text) {
  const el = document.getElementById('transcriptInterim');
  if (el) el.textContent = text;
}

function _updateAiCount(n) {
  const el = document.getElementById('aiSegmentCount');
  if (el) el.textContent = n;
}

function _setTranscribing(on) {
  _isTranscribing = on;
  const btn   = document.getElementById('btnTranscribe');
  const label = document.getElementById('transcribeLabel');
  if (btn)   btn.classList.toggle('active', on);
  if (label) label.textContent = on ? 'Stop' : 'Start';
  // Show mic-active dot
  const dot = document.getElementById('transcribeDot');
  if (dot) dot.style.display = on ? 'inline-block' : 'none';
}
