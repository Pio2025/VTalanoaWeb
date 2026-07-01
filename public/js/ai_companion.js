/* VTalanoa — AI Companion
 *
 * Sends the meeting transcript + a prompt type to the CI4 AI proxy,
 * which forwards to Anthropic Claude and returns a response.
 */

let _aiLoading = false;

const _AI_TYPE_LABELS = {
  summary: 'Summarize meeting',
  actions: 'Find action items',
  email:   'Draft follow-up email',
};

// ── Public API ────────────────────────────────────────────────────────────────

async function askAI(type) {
  if (_aiLoading) return;

  const transcript = typeof getTranscriptText === 'function' ? getTranscriptText() : '';
  if (!transcript) {
    showToast('No transcript yet. Start transcription first, then try again.', 'default');
    return;
  }

  _appendAiMessage('user', _AI_TYPE_LABELS[type] || type);
  await _callAI({ type, transcript });
}

async function sendAIQuestion() {
  if (_aiLoading) return;
  const input    = document.getElementById('aiInput');
  const question = input?.value.trim();
  if (!question) return;
  if (input) input.value = '';

  const transcript = typeof getTranscriptText === 'function' ? getTranscriptText() : '';
  _appendAiMessage('user', question);
  await _callAI({ type: 'qa', transcript, question });
}

function aiInputKeydown(e) {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault();
    sendAIQuestion();
  }
}

// ── Internal ──────────────────────────────────────────────────────────────────

async function _callAI(body) {
  _setAiLoading(true);
  try {
    const res  = await fetch(`${BASE_URL}api/ai/chat`, {
      method:  'POST',
      headers: {
        'Content-Type':  'application/json',
        'Authorization': `Bearer ${API_TOKEN}`,
      },
      body: JSON.stringify(body),
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || `HTTP ${res.status}`);
    _appendAiMessage('assistant', data.response);
  } catch (e) {
    console.error('[AI]', e);
    _appendAiMessage('error', 'Could not reach the AI assistant. Please try again.');
  } finally {
    _setAiLoading(false);
  }
}

function _appendAiMessage(role, text) {
  const container = document.getElementById('aiMessages');
  if (!container) return;

  // Remove welcome placeholder on first real message
  const welcome = container.querySelector('.ai-welcome');
  if (welcome) welcome.remove();

  const msg = document.createElement('div');
  msg.className = `ai-message ai-${role}`;

  if (role === 'user') {
    msg.innerHTML =
      `<div class="ai-msg-label">You</div>` +
      `<div class="ai-msg-text">${escapeHtml(text)}</div>`;
  } else if (role === 'assistant') {
    const formatted = _formatAiText(text);
    msg.innerHTML =
      `<div class="ai-msg-label"><i class="fa-solid fa-robot me-1"></i>AI Companion</div>` +
      `<div class="ai-msg-text">${formatted}</div>` +
      `<button class="ai-copy-btn" onclick="_copyAiMessage(this)" title="Copy to clipboard">` +
        `<i class="fa-solid fa-copy"></i>` +
      `</button>`;
  } else {
    msg.innerHTML =
      `<div class="ai-msg-text ai-error"><i class="fa-solid fa-triangle-exclamation me-1"></i>${escapeHtml(text)}</div>`;
  }

  container.appendChild(msg);
  container.scrollTop = container.scrollHeight;
}

function _copyAiMessage(btn) {
  const textEl = btn.parentElement?.querySelector('.ai-msg-text');
  if (!textEl) return;
  const plain = textEl.innerText || textEl.textContent;
  navigator.clipboard.writeText(plain).then(() => {
    btn.innerHTML = '<i class="fa-solid fa-check"></i>';
    setTimeout(() => { btn.innerHTML = '<i class="fa-solid fa-copy"></i>'; }, 1800);
  });
}

function _formatAiText(raw) {
  return escapeHtml(raw)
    .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
    .replace(/^#{1,3} (.+)$/gm, '<strong>$1</strong>')
    .replace(/^[•\-] /gm, '• ')
    .replace(/\n{2,}/g, '\n')
    .replace(/\n/g, '<br>');
}

function _setAiLoading(loading) {
  _aiLoading = loading;
  const indicator = document.getElementById('aiLoadingIndicator');
  if (indicator) indicator.style.display = loading ? 'flex' : 'none';
  ['btnAiSummarize', 'btnAiActions', 'btnAiEmail', 'aiSendBtn'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.disabled = loading;
  });
}
