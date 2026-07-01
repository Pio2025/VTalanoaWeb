/* VTalanoa Workplace */

// ── Global State ────────────────────────────────────────────────────────────
let _wsToken    = '';
let _wsUserId   = 0;
let _wsUserName = '';
let _wsBaseUrl  = '';
let _wsSigUrl   = '';
let _wsSocket   = null;
let _wsAllUsers = [];

// Chat
let _wsChannels      = [];
let _wsActiveChannel = null;
let _wsOldestMsgId   = null;
let _wsTypingPeers   = {};
let _wsTypingTimer   = null;

// Docs
let _wsDocs          = [];
let _wsActiveDocId   = null;
let _wsQuill         = null;
let _wsSaveTimer     = null;

// Mail
let _wsMails         = [];
let _wsMailFolder    = 'inbox';
let _wsActiveMailId  = null;
let _wsComposeTo     = [];
let _wsComposeCc     = [];

// Calendar
let _wsCalDate       = new Date();
let _wsCalEvents     = [];
let _wsCalColor      = '#00aeef';

// ── Init ─────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', async () => {
  _wsToken    = document.getElementById('_wsToken').value;
  _wsUserId   = parseInt(document.getElementById('_wsUserId').value);
  _wsUserName = document.getElementById('_wsUserName').value;
  _wsBaseUrl  = document.getElementById('_wsBaseUrl').value;
  _wsSigUrl   = document.getElementById('_wsSigUrl').value;

  _wsInitSocket();
  _wsAllUsers = await _wsApi('GET', 'workspace/users');

  await wsSwitchTool('chat');
});

// ── Tool switcher ─────────────────────────────────────────────────────────────
async function wsSwitchTool(tool) {
  document.querySelectorAll('.ws-nav-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('wsNav' + _wsCap(tool))?.classList.add('active');
  document.querySelectorAll('.ws-section').forEach(s => s.style.display = 'none');
  document.getElementById('wsSection' + _wsCap(tool)).style.display = 'flex';

  if (tool === 'chat')     await _wsLoadChat();
  if (tool === 'docs')     await _wsLoadDocs();
  if (tool === 'mail')     await _wsLoadMail();
  if (tool === 'calendar') await _wsLoadCalendar();
}
function _wsCap(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

// ── Socket.IO ─────────────────────────────────────────────────────────────────
function _wsInitSocket() {
  try {
    _wsSocket = io(_wsSigUrl, { auth: { token: _wsToken }, transports: ['websocket','polling'] });
    _wsSocket.emit('ws-subscribe-user', { userId: _wsUserId });

    _wsSocket.on('ws-message', ({ channelId, message }) => {
      if (channelId === _wsActiveChannel?.channel_id) {
        _wsAppendMessage(message);
      } else {
        // Increment unread badge for that channel
        const ch = _wsChannels.find(c => c.channel_id === channelId);
        if (ch) {
          ch.unread_count = (ch.unread_count || 0) + 1;
          _wsRenderChannels();
        }
        _wsShowNavBadge('Chat');
      }
    });

    _wsSocket.on('ws-typing', ({ channelId, userName }) => {
      if (channelId !== _wsActiveChannel?.channel_id) return;
      _wsTypingPeers[userName] = true;
      _wsShowTyping();
      clearTimeout(_wsTypingPeers['__t_' + userName]);
      _wsTypingPeers['__t_' + userName] = setTimeout(() => {
        delete _wsTypingPeers[userName];
        delete _wsTypingPeers['__t_' + userName];
        _wsShowTyping();
      }, 3000);
    });

    _wsSocket.on('ws-mail-notify', ({ subject, fromName }) => {
      _wsToast(`New mail from ${fromName}: "${subject}"`, 'info');
      _wsShowNavBadge('Mail');
    });
  } catch (e) {
    console.warn('[WS] Socket init failed:', e);
  }
}

// ── Chat ──────────────────────────────────────────────────────────────────────
async function _wsLoadChat() {
  _wsChannels = await _wsApi('GET', 'workspace/channels');
  _wsRenderChannels();
  if (_wsChannels.length && !_wsActiveChannel) {
    await wsSelectChannel(_wsChannels[0]);
  }
}

function _wsRenderChannels() {
  const list = document.getElementById('wsChannelList');
  if (!list) return;
  list.innerHTML = _wsChannels.map(ch => {
    const active  = _wsActiveChannel?.channel_id === ch.channel_id;
    const unread  = parseInt(ch.unread_count) || 0;
    return `<button class="ws-channel-item${active ? ' active' : ''}" onclick="wsSelectChannelById(${ch.channel_id})">
      <span class="ws-channel-hash">#</span>
      <span class="ws-channel-name">${_wsEsc(ch.name)}</span>
      ${unread > 0 ? `<span class="ws-channel-unread">${unread}</span>` : ''}
    </button>`;
  }).join('');
}

async function wsSelectChannelById(id) {
  const ch = _wsChannels.find(c => c.channel_id === id);
  if (ch) await wsSelectChannel(ch);
}

async function wsSelectChannel(ch) {
  // Leave old channel socket room
  if (_wsActiveChannel) {
    _wsSocket?.emit('ws-leave-channel', { channelId: _wsActiveChannel.channel_id });
  }

  _wsActiveChannel = ch;
  _wsOldestMsgId   = null;

  // Join new channel socket room
  _wsSocket?.emit('ws-join-channel', { channelId: ch.channel_id });

  // Update header
  document.getElementById('wsChatChannelName').textContent = '#' + ch.name;
  document.getElementById('wsChatChannelDesc').textContent  = ch.description || '';
  document.getElementById('wsChatInputArea').style.display  = 'flex';

  // Set placeholder
  const inp = document.getElementById('wsChatInput');
  if (inp) inp.placeholder = `Message #${ch.name}…`;

  // Clear and reload messages
  const msgEl = document.getElementById('wsMessages');
  msgEl.innerHTML = '<div class="ws-msg-loading"><div class="ws-spinner"></div></div>';
  document.getElementById('wsMessagesEmpty').style.display = 'none';

  const msgs = await _wsApi('GET', `workspace/channels/${ch.channel_id}/messages`);
  ch.unread_count = 0;
  _wsRenderChannels();
  _wsClearNavBadge('Chat');

  msgEl.innerHTML = '';
  msgs.forEach(m => _wsAppendMessage(m, false));
  _wsScrollMsgs();

  // Load more on scroll up
  msgEl.onscroll = () => { if (msgEl.scrollTop < 80) _wsLoadMoreMsgs(); };
}

async function _wsLoadMoreMsgs() {
  if (!_wsActiveChannel || !_wsOldestMsgId) return;
  const older = await _wsApi('GET', `workspace/channels/${_wsActiveChannel.channel_id}/messages?before=${_wsOldestMsgId}`);
  if (!older.length) return;
  const msgEl = document.getElementById('wsMessages');
  const prev  = msgEl.scrollHeight;
  older.forEach(m => _wsPrependMessage(m));
  msgEl.scrollTop = msgEl.scrollHeight - prev;
}

function _wsAppendMessage(msg, scroll = true) {
  const el  = document.getElementById('wsMessages');
  const div = _wsBuildMsgEl(msg);
  el.appendChild(div);
  if (!_wsOldestMsgId || msg.message_id < _wsOldestMsgId) _wsOldestMsgId = msg.message_id;
  if (scroll) _wsScrollMsgs();
}

function _wsPrependMessage(msg) {
  const el  = document.getElementById('wsMessages');
  const div = _wsBuildMsgEl(msg);
  el.insertBefore(div, el.firstChild);
  if (!_wsOldestMsgId || msg.message_id < _wsOldestMsgId) _wsOldestMsgId = msg.message_id;
}

function _wsBuildMsgEl(msg) {
  const isSelf    = msg.user_id === _wsUserId;
  const name      = (msg.fname || '') + ' ' + (msg.lname || '');
  const initials  = ((msg.fname || '?').charAt(0) + (msg.lname || '').charAt(0)).toUpperCase();
  const time      = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  const photo     = msg.profile_photo
    ? `<img src="${_wsBaseUrl}${msg.profile_photo}" class="ws-msg-avatar-img" alt="${_wsEsc(initials)}">`
    : `<div class="ws-msg-avatar">${_wsEsc(initials)}</div>`;

  let body = '';
  if (msg.content) body += `<div class="ws-msg-text">${_wsLinkify(_wsEsc(msg.content))}</div>`;
  if (msg.file_url) {
    const isImg = /\.(jpg|jpeg|png|gif|webp)$/i.test(msg.file_name || '');
    body += isImg
      ? `<img src="${_wsEsc(msg.file_url)}" class="ws-msg-img" onclick="window.open('${_wsEsc(msg.file_url)}','_blank')" alt="">`
      : `<a href="${_wsEsc(msg.file_url)}" target="_blank" class="ws-msg-file">
           <i class="fa-solid fa-file me-2"></i>${_wsEsc(msg.file_name || 'File')}
         </a>`;
  }

  const div = document.createElement('div');
  div.className = `ws-message${isSelf ? ' ws-message--self' : ''}`;
  div.id = `wsmsg-${msg.message_id}`;
  div.innerHTML = `
    ${photo}
    <div class="ws-msg-body">
      <div class="ws-msg-meta">
        <span class="ws-msg-name">${_wsEsc(name.trim() || 'Unknown')}</span>
        <span class="ws-msg-time">${time}</span>
      </div>
      ${body}
    </div>`;
  return div;
}

function _wsScrollMsgs() {
  const el = document.getElementById('wsMessages');
  if (el) el.scrollTop = el.scrollHeight;
}

function _wsShowTyping() {
  const bar   = document.getElementById('wsTypingBar');
  const names = Object.keys(_wsTypingPeers).filter(k => !k.startsWith('__t_'));
  if (!bar) return;
  if (!names.length) { bar.style.display = 'none'; return; }
  bar.style.display = 'flex';
  const label = names.length === 1 ? `${names[0]} is typing…` : `${names.join(', ')} are typing…`;
  bar.innerHTML = `<span class="ws-typing-dots"><span></span><span></span><span></span></span>${_wsEsc(label)}`;
}

async function wsSendMessage() {
  if (!_wsActiveChannel) return;
  const inp     = document.getElementById('wsChatInput');
  const content = inp?.value.trim();
  if (!content) return;

  inp.value = '';
  inp.style.height = '';

  const saved = await _wsApi('POST', `workspace/channels/${_wsActiveChannel.channel_id}/messages`, { content });
  if (saved) {
    _wsAppendMessage(saved);
    // Relay via socket so others receive it instantly
    _wsSocket?.emit('ws-message', { channelId: _wsActiveChannel.channel_id, message: saved });
  }
}

function wsChatKeydown(e) {
  if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); wsSendMessage(); return; }
}

function wsOnChatInput(el) {
  // Auto-grow textarea
  el.style.height = 'auto';
  el.style.height = Math.min(el.scrollHeight, 120) + 'px';
  // Typing indicator
  _wsSocket?.emit('ws-typing', { channelId: _wsActiveChannel?.channel_id });
}

async function wsHandleChatFile(input) {
  const file = input.files[0];
  if (!file || !_wsActiveChannel) return;
  if (file.size > 10 * 1024 * 1024) { _wsToast('File too large (max 10 MB)', 'error'); return; }

  const fd = new FormData();
  fd.append('file', file);
  fd.append('meeting_uuid', 'workspace');

  try {
    const res  = await fetch(`${_wsBaseUrl}api/chat/upload`, {
      method: 'POST', headers: { Authorization: `Bearer ${_wsToken}` }, body: fd,
    });
    const data = await res.json();
    if (!res.ok) { _wsToast(data.error || 'Upload failed', 'error'); return; }
    const saved = await _wsApi('POST', `workspace/channels/${_wsActiveChannel.channel_id}/messages`, {
      content: '', file_url: data.url, file_name: data.name,
    });
    if (saved) {
      _wsAppendMessage(saved);
      _wsSocket?.emit('ws-message', { channelId: _wsActiveChannel.channel_id, message: saved });
    }
  } catch (e) { _wsToast('Upload failed', 'error'); }
  input.value = '';
}

// ── Channels ──────────────────────────────────────────────────────────────────
function wsShowNewChannel() {
  document.getElementById('wsNewChannelModal').style.display = 'flex';
  setTimeout(() => document.getElementById('wsNewChannelName')?.focus(), 100);
}
function wsCloseNewChannel(e) {
  if (e && e.target !== document.getElementById('wsNewChannelModal')) return;
  document.getElementById('wsNewChannelModal').style.display = 'none';
}
async function wsCreateChannel() {
  const name = document.getElementById('wsNewChannelName')?.value.trim();
  const desc = document.getElementById('wsNewChannelDesc')?.value.trim();
  const type = document.getElementById('wsNewChannelType')?.value;
  if (!name) { _wsToast('Channel name required', 'error'); return; }
  const ch = await _wsApi('POST', 'workspace/channels', { name, description: desc, type });
  if (ch?.channel_id) {
    _wsChannels.push({ ...ch, unread_count: 0 });
    _wsRenderChannels();
    wsCloseNewChannel({});
    await wsSelectChannel(ch);
    document.getElementById('wsNewChannelName').value = '';
    document.getElementById('wsNewChannelDesc').value = '';
  }
}

// ── Docs ──────────────────────────────────────────────────────────────────────
async function _wsLoadDocs() {
  _wsDocs = await _wsApi('GET', 'workspace/docs');
  _wsRenderDocList();
  if (!_wsQuill) _wsInitQuill();
}

function _wsRenderDocList() {
  const list = document.getElementById('wsDocList');
  if (!list) return;
  if (!_wsDocs.length) {
    list.innerHTML = '<div class="ws-doc-list-empty">No documents yet</div>';
    return;
  }
  list.innerHTML = _wsDocs.map(d => {
    const active = _wsActiveDocId === d.doc_id;
    const date   = new Date(d.updated_at).toLocaleDateString([], { month: 'short', day: 'numeric' });
    return `<button class="ws-doc-item${active ? ' active' : ''}" onclick="wsOpenDoc(${d.doc_id})">
      <i class="fa-solid fa-file-lines ws-doc-icon"></i>
      <div class="ws-doc-item-info">
        <span class="ws-doc-item-title">${_wsEsc(d.title || 'Untitled')}</span>
        <span class="ws-doc-item-date">${date}</span>
      </div>
    </button>`;
  }).join('');
}

function _wsInitQuill() {
  _wsQuill = new Quill('#wsQuillEditor', {
    theme: 'snow',
    placeholder: 'Start writing…',
    modules: {
      toolbar: [
        [{ heading: [1, 2, 3, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        ['blockquote', 'code-block'],
        [{ list: 'ordered' }, { list: 'bullet' }],
        ['link'],
        ['clean'],
      ],
    },
  });
  _wsQuill.on('text-change', () => {
    clearTimeout(_wsSaveTimer);
    document.getElementById('wsDocSaveStatus').textContent = 'Unsaved…';
    _wsSaveTimer = setTimeout(wsSaveDoc, 1500);
  });
}

async function wsNewDoc() {
  const doc = await _wsApi('POST', 'workspace/docs', { title: 'Untitled Document', content: '' });
  if (doc?.doc_id) {
    _wsDocs.unshift(doc);
    _wsRenderDocList();
    await wsOpenDoc(doc.doc_id);
  }
}

async function wsOpenDoc(id) {
  _wsActiveDocId = id;
  _wsRenderDocList();

  const doc = await _wsApi('GET', `workspace/docs/${id}`);
  if (!doc) return;

  document.getElementById('wsDocEmpty').style.display  = 'none';
  document.getElementById('wsDocEditor').style.display = 'flex';
  document.getElementById('wsDocTitle').value          = doc.title;
  document.getElementById('wsDocSaveStatus').textContent = 'Saved';

  if (_wsQuill) {
    try {
      const delta = JSON.parse(doc.content || 'null');
      if (delta && delta.ops) {
        _wsQuill.setContents(delta, 'silent');
      } else {
        _wsQuill.setText(doc.content || '', 'silent');
      }
    } catch {
      _wsQuill.setText(doc.content || '', 'silent');
    }
  }

  // Socket presence
  _wsSocket?.emit('ws-join-doc', { docId: id });
}

function wsOnDocTitleChange() {
  clearTimeout(_wsSaveTimer);
  document.getElementById('wsDocSaveStatus').textContent = 'Unsaved…';
  _wsSaveTimer = setTimeout(wsSaveDoc, 1000);
}

async function wsSaveDoc() {
  if (!_wsActiveDocId) return;
  const title   = document.getElementById('wsDocTitle')?.value.trim() || 'Untitled Document';
  const content = _wsQuill ? JSON.stringify(_wsQuill.getContents()) : '';
  await _wsApi('PUT', `workspace/docs/${_wsActiveDocId}`, { title, content });
  document.getElementById('wsDocSaveStatus').textContent = 'Saved';
  // Update list title
  const d = _wsDocs.find(x => x.doc_id === _wsActiveDocId);
  if (d) { d.title = title; _wsRenderDocList(); }
}

async function wsDeleteDoc() {
  if (!_wsActiveDocId) return;
  const confirmed = await Swal.fire({
    title: 'Delete Document?',
    text: 'This cannot be undone.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Delete',
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#64748b',
    background: '#1e293b', color: '#fff',
  });
  if (!confirmed.isConfirmed) return;
  await _wsApi('DELETE', `workspace/docs/${_wsActiveDocId}`);
  _wsDocs = _wsDocs.filter(d => d.doc_id !== _wsActiveDocId);
  _wsActiveDocId = null;
  document.getElementById('wsDocEmpty').style.display  = 'flex';
  document.getElementById('wsDocEditor').style.display = 'none';
  _wsRenderDocList();
}

// ── Mail ──────────────────────────────────────────────────────────────────────
async function _wsLoadMail() {
  await wsSetMailFolder(_wsMailFolder);
}

async function wsSetMailFolder(folder) {
  _wsMailFolder   = folder;
  _wsActiveMailId = null;

  document.querySelectorAll('.ws-folder-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('wsFolder' + _wsCap(folder))?.classList.add('active');

  document.getElementById('wsMailViewEmpty').style.display = 'flex';
  document.getElementById('wsMailView').style.display      = 'none';

  let mails;
  if (folder === 'starred') {
    const inbox = await _wsApi('GET', 'workspace/mail?folder=inbox');
    mails = inbox.filter(m => m.is_starred);
  } else {
    mails = await _wsApi('GET', `workspace/mail?folder=${folder}`);
  }
  _wsMails = mails || [];

  const unread = _wsMails.filter(m => !m.is_read).length;
  const badge  = document.getElementById('wsInboxBadge');
  if (badge) { badge.textContent = unread; badge.style.display = unread > 0 ? 'flex' : 'none'; }
  if (unread === 0) _wsClearNavBadge('Mail');

  _wsRenderMailList();
}

function _wsRenderMailList() {
  const list = document.getElementById('wsMailList');
  if (!list) return;
  if (!_wsMails.length) {
    list.innerHTML = `<div class="ws-mail-empty"><i class="fa-solid fa-inbox"></i><p>No messages</p></div>`;
    return;
  }
  list.innerHTML = _wsMails.map(m => {
    const sender  = _wsMailFolder === 'sent'
      ? (m.to_names || 'Recipients')
      : ((m.sender_fname || '') + ' ' + (m.sender_lname || '')).trim() || m.sender_email || 'Unknown';
    const date   = _wsFriendlyDate(m.created_at);
    const unread = !m.is_read && _wsMailFolder !== 'sent';
    const star   = m.is_starred ? 'fa-solid fa-star ws-star-active' : 'fa-regular fa-star';
    return `<div class="ws-mail-item${unread ? ' unread' : ''}${_wsActiveMailId === m.mail_id ? ' active' : ''}"
                 onclick="wsOpenMail(${m.mail_id})">
      <div class="ws-mail-item-star" onclick="event.stopPropagation();wsToggleStar(${m.mail_id})">
        <i class="${star}"></i>
      </div>
      <div class="ws-mail-item-body">
        <div class="ws-mail-item-from">${_wsEsc(sender)}</div>
        <div class="ws-mail-item-subject">${_wsEsc(m.subject || '(no subject)')}</div>
      </div>
      <div class="ws-mail-item-date">${date}</div>
    </div>`;
  }).join('');
}

async function wsOpenMail(id) {
  _wsActiveMailId = id;
  _wsRenderMailList();

  const mail = await _wsApi('GET', `workspace/mail/${id}`);
  if (!mail) return;

  document.getElementById('wsMailViewEmpty').style.display = 'none';
  document.getElementById('wsMailView').style.display      = 'block';

  const sender   = mail.sender ? `${mail.sender.fname} ${mail.sender.lname} &lt;${mail.sender.email}&gt;` : 'Unknown';
  const toNames  = (mail.recipients || []).filter(r => r.type === 'to')
    .map(r => `${_wsEsc(r.fname)} ${_wsEsc(r.lname)}`).join(', ');
  const date     = new Date(mail.created_at).toLocaleString();
  const starIcon = mail.is_starred ? 'fa-solid fa-star ws-star-active' : 'fa-regular fa-star';

  document.getElementById('wsMailView').innerHTML = `
    <div class="ws-mail-view-header">
      <h3 class="ws-mail-view-subject">${_wsEsc(mail.subject)}</h3>
      <div class="ws-mail-view-actions">
        <button class="ws-mail-action-btn" onclick="wsToggleStar(${id})" title="Star">
          <i id="wsStarIcon${id}" class="${starIcon}"></i>
        </button>
        <button class="ws-mail-action-btn" onclick="wsTrashMail(${id})" title="Delete">
          <i class="fa-solid fa-trash"></i>
        </button>
      </div>
    </div>
    <div class="ws-mail-view-meta">
      <span><strong>From:</strong> ${_wsEsc(sender)}</span>
      <span><strong>To:</strong> ${toNames}</span>
      <span class="ws-mail-view-date">${date}</span>
    </div>
    <div class="ws-mail-view-body">${mail.body.replace(/\n/g, '<br>')}</div>
    <div class="ws-mail-reply-bar">
      <button class="ws-btn-primary" onclick="wsReply(${JSON.stringify(mail.from_user_id)}, ${JSON.stringify(_wsEsc(mail.subject))})">
        <i class="fa-solid fa-reply me-2"></i>Reply
      </button>
    </div>`;

  // Mark as read in local list
  const local = _wsMails.find(m => m.mail_id === id);
  if (local) { local.is_read = true; _wsRenderMailList(); }
}

async function wsToggleStar(id) {
  const data = await _wsApi('PATCH', `workspace/mail/${id}/star`);
  const local = _wsMails.find(m => m.mail_id === id);
  if (local && data) {
    local.is_starred = data.starred;
    _wsRenderMailList();
    const icon = document.getElementById('wsStarIcon' + id);
    if (icon) icon.className = data.starred ? 'fa-solid fa-star ws-star-active' : 'fa-regular fa-star';
  }
}

async function wsTrashMail(id) {
  await _wsApi('DELETE', `workspace/mail/${id}`);
  _wsMails = _wsMails.filter(m => m.mail_id !== id);
  _wsRenderMailList();
  document.getElementById('wsMailViewEmpty').style.display = 'flex';
  document.getElementById('wsMailView').style.display      = 'none';
  _wsToast('Message moved to trash', 'default');
}

// ── Compose ───────────────────────────────────────────────────────────────────
function wsComposeModal(replyToId, replySubject) {
  _wsComposeTo = []; _wsComposeCc = [];
  document.getElementById('wsComposeToPills').innerHTML  = '';
  document.getElementById('wsComposeCcPills').innerHTML  = '';
  document.getElementById('wsComposeToInput').value      = '';
  document.getElementById('wsComposeCcInput').value      = '';
  document.getElementById('wsComposeSubject').value      = replySubject || '';
  document.getElementById('wsComposeBody').value         = '';
  document.getElementById('wsComposeModal').style.display = 'flex';
  setTimeout(() => document.getElementById('wsComposeToInput')?.focus(), 80);
}

function wsCloseCompose(e) {
  if (e && e.target !== document.getElementById('wsComposeModal')) return;
  document.getElementById('wsComposeModal').style.display = 'none';
}

function wsReply(fromUserId, subject) {
  wsComposeModal(null, 'Re: ' + subject);
  // Add original sender as recipient
  const user = _wsAllUsers.find(u => u.user_id === fromUserId);
  if (user) _wsAddRecipient('to', user);
}

function wsRecipientSearch(input, type) {
  const q    = input.value.toLowerCase();
  const drop = document.getElementById('wsCompose' + _wsCap(type) + 'Dropdown');
  if (!q) { drop.style.display = 'none'; return; }
  const currentIds = type === 'to' ? _wsComposeTo.map(u => u.user_id) : _wsComposeCc.map(u => u.user_id);
  const hits = _wsAllUsers.filter(u =>
    !currentIds.includes(u.user_id) &&
    ((u.fname + ' ' + u.lname).toLowerCase().includes(q) || u.email.toLowerCase().includes(q))
  ).slice(0, 6);
  if (!hits.length) { drop.style.display = 'none'; return; }
  drop.innerHTML = hits.map(u =>
    `<div class="ws-recipient-option" onclick="_wsAddRecipient('${type}', ${JSON.stringify(u).replace(/"/g,'&quot;')})">
       <span>${_wsEsc(u.fname + ' ' + u.lname)}</span>
       <span class="ws-recipient-email">${_wsEsc(u.email)}</span>
     </div>`
  ).join('');
  drop.style.display = 'block';
}

function wsRecipientKeydown(e, type) {
  if (e.key === 'Escape') document.getElementById('wsCompose' + _wsCap(type) + 'Dropdown').style.display = 'none';
}

function _wsAddRecipient(type, user) {
  const arr  = type === 'to' ? _wsComposeTo : _wsComposeCc;
  if (arr.find(u => u.user_id === user.user_id)) return;
  arr.push(user);
  const pills = document.getElementById('wsCompose' + _wsCap(type) + 'Pills');
  const pill  = document.createElement('span');
  pill.className = 'ws-recipient-pill';
  pill.innerHTML = `${_wsEsc(user.fname)} <button onclick="this.parentElement.remove();_wsRemoveRecipient('${type}',${user.user_id})">&times;</button>`;
  pills.appendChild(pill);
  document.getElementById('wsCompose' + _wsCap(type) + 'Input').value = '';
  document.getElementById('wsCompose' + _wsCap(type) + 'Dropdown').style.display = 'none';
}

function _wsRemoveRecipient(type, userId) {
  if (type === 'to') _wsComposeTo = _wsComposeTo.filter(u => u.user_id !== userId);
  else _wsComposeCc = _wsComposeCc.filter(u => u.user_id !== userId);
}

async function wsSendMail() {
  const subject = document.getElementById('wsComposeSubject').value.trim();
  const body    = document.getElementById('wsComposeBody').value.trim();
  if (!_wsComposeTo.length) { _wsToast('Add at least one recipient', 'error'); return; }
  if (!body) { _wsToast('Message body required', 'error'); return; }
  const res = await _wsApi('POST', 'workspace/mail', {
    subject, body,
    to: _wsComposeTo.map(u => u.user_id),
    cc: _wsComposeCc.map(u => u.user_id),
  });
  if (res?.ok) {
    wsCloseCompose({});
    _wsToast('Message sent!', 'success');
    // Notify recipients
    const fromName = _wsUserName;
    _wsSocket?.emit('ws-mail-notify', {
      toUserIds: _wsComposeTo.map(u => u.user_id),
      subject, fromName,
    });
    if (_wsMailFolder === 'sent') await wsSetMailFolder('sent');
  }
}

// ── Calendar ──────────────────────────────────────────────────────────────────
async function _wsLoadCalendar() {
  _wsRenderCalGrid();
  await _wsFetchCalEvents();
}

function _wsRenderCalGrid() {
  const y    = _wsCalDate.getFullYear();
  const m    = _wsCalDate.getMonth();
  const first= new Date(y, m, 1).getDay();
  const days = new Date(y, m + 1, 0).getDate();
  const today= new Date();

  document.getElementById('wsCalTitle').textContent =
    _wsCalDate.toLocaleDateString('en', { month: 'long', year: 'numeric' });

  const grid = document.getElementById('wsCalGrid');
  grid.innerHTML = '';

  // Empty cells before first day
  for (let i = 0; i < first; i++) {
    const cell = document.createElement('div');
    cell.className = 'ws-cal-cell ws-cal-cell--empty';
    grid.appendChild(cell);
  }

  for (let d = 1; d <= days; d++) {
    const date   = new Date(y, m, d);
    const isToday= date.toDateString() === today.toDateString();
    const cell   = document.createElement('div');
    cell.className = `ws-cal-cell${isToday ? ' ws-cal-cell--today' : ''}`;
    cell.onclick   = () => wsCalSelectDay(date);
    cell.innerHTML = `<span class="ws-cal-day-num">${d}</span><div class="ws-cal-dots" id="wsDots${d}"></div>`;
    grid.appendChild(cell);
  }

  _wsPlaceEventDots();
}

function _wsPlaceEventDots() {
  const y = _wsCalDate.getFullYear();
  const m = _wsCalDate.getMonth();
  _wsCalEvents.forEach(ev => {
    const d = new Date(ev.start_time);
    if (d.getFullYear() === y && d.getMonth() === m) {
      const dots = document.getElementById('wsDots' + d.getDate());
      if (dots) {
        const dot  = document.createElement('span');
        dot.className = 'ws-cal-dot';
        dot.style.background = ev.color || '#00aeef';
        dots.appendChild(dot);
      }
    }
  });
}

async function _wsFetchCalEvents() {
  const y   = _wsCalDate.getFullYear();
  const m   = _wsCalDate.getMonth();
  const s   = `${y}-${String(m+1).padStart(2,'0')}-01`;
  const e   = `${y}-${String(m+1).padStart(2,'0')}-${new Date(y,m+1,0).getDate()} 23:59:59`;
  _wsCalEvents = await _wsApi('GET', `workspace/calendar?start=${s}&end=${e}`) || [];
  _wsPlaceEventDots();
  if (_wsSelectedDay) wsCalSelectDay(_wsSelectedDay);
}

function wsCalNav(dir) {
  _wsCalDate.setMonth(_wsCalDate.getMonth() + dir);
  _wsCalDate = new Date(_wsCalDate.getFullYear(), _wsCalDate.getMonth(), 1);
  _wsRenderCalGrid();
  _wsFetchCalEvents();
}

function wsCalToday() {
  _wsCalDate = new Date();
  _wsRenderCalGrid();
  _wsFetchCalEvents();
  wsCalSelectDay(new Date());
}

function wsCalSelectDay(date) {
  _wsSelectedDay = date;
  const label = date.toLocaleDateString('en', { weekday:'long', month:'long', day:'numeric' });
  document.getElementById('wsCalDayLabel').textContent = label;

  const ds = date.toDateString();
  const evs = _wsCalEvents.filter(ev => new Date(ev.start_time).toDateString() === ds);
  const pane = document.getElementById('wsCalDayEvents');
  if (!evs.length) {
    pane.innerHTML = '<div class="ws-cal-no-events">No events</div>';
    return;
  }
  pane.innerHTML = evs.map(ev => {
    const t = new Date(ev.start_time).toLocaleTimeString([], { hour:'2-digit', minute:'2-digit' });
    const isMeeting = ev.is_meeting;
    return `<div class="ws-cal-event-item" style="border-left-color:${ev.color || '#00aeef'}">
      <div class="ws-cal-event-time">${t}</div>
      <div class="ws-cal-event-title">${_wsEsc(ev.title)}</div>
      ${!isMeeting ? `<button class="ws-cal-event-edit" onclick="wsEditCalEvent(${JSON.stringify(ev).replace(/"/g,'&quot;')})">
        <i class="fa-solid fa-pen"></i>
      </button>` : '<span class="ws-cal-event-type">Meeting</span>'}
    </div>`;
  }).join('');
}

function wsCalNewEvent() {
  const now = _wsSelectedDay || new Date();
  const yr  = now.getFullYear();
  const mo  = String(now.getMonth()+1).padStart(2,'0');
  const dy  = String(now.getDate()).padStart(2,'0');
  document.getElementById('wsCalEvtId').value    = '';
  document.getElementById('wsCalEvtTitle').value  = '';
  document.getElementById('wsCalEvtStart').value  = `${yr}-${mo}-${dy}T09:00`;
  document.getElementById('wsCalEvtEnd').value    = `${yr}-${mo}-${dy}T10:00`;
  document.getElementById('wsCalDeleteBtn').style.display = 'none';
  document.getElementById('wsCalModalTitle').textContent  = 'New Event';
  wsPickCalColor('#00aeef');
  document.getElementById('wsCalModal').style.display = 'flex';
  setTimeout(() => document.getElementById('wsCalEvtTitle')?.focus(), 80);
}

function wsEditCalEvent(ev) {
  document.getElementById('wsCalEvtId').value    = ev.event_id;
  document.getElementById('wsCalEvtTitle').value  = ev.title;
  document.getElementById('wsCalEvtStart').value  = ev.start_time?.replace(' ','T').slice(0,16);
  document.getElementById('wsCalEvtEnd').value    = ev.end_time?.replace(' ','T').slice(0,16);
  document.getElementById('wsCalDeleteBtn').style.display = '';
  document.getElementById('wsCalModalTitle').textContent  = 'Edit Event';
  wsPickCalColor(ev.color || '#00aeef');
  document.getElementById('wsCalModal').style.display = 'flex';
}

function wsCloseCalModal(e) {
  if (e && e.target !== document.getElementById('wsCalModal')) return;
  document.getElementById('wsCalModal').style.display = 'none';
}

function wsPickCalColor(color) {
  _wsCalColor = color;
  document.getElementById('wsCalEvtColor').value = color;
  document.querySelectorAll('.ws-cal-color-btn').forEach(b => {
    b.classList.toggle('active', b.dataset.color === color);
  });
}

async function wsSaveCalEvent() {
  const id    = document.getElementById('wsCalEvtId').value;
  const title = document.getElementById('wsCalEvtTitle').value.trim();
  const start = document.getElementById('wsCalEvtStart').value;
  const end   = document.getElementById('wsCalEvtEnd').value || start;
  const color = document.getElementById('wsCalEvtColor').value;
  if (!title) { _wsToast('Title required', 'error'); return; }

  if (id) {
    await _wsApi('PUT', `workspace/calendar/${id}`, { title, start_time: start, end_time: end, color });
  } else {
    await _wsApi('POST', 'workspace/calendar', { title, start_time: start, end_time: end, color });
  }
  wsCloseCalModal({});
  _wsRenderCalGrid();
  await _wsFetchCalEvents();
}

async function wsDeleteCalEvent() {
  const id = document.getElementById('wsCalEvtId').value;
  if (!id) return;
  await _wsApi('DELETE', `workspace/calendar/${id}`);
  wsCloseCalModal({});
  _wsRenderCalGrid();
  await _wsFetchCalEvents();
}

// ── Utilities ──────────────────────────────────────────────────────────────────
async function _wsApi(method, path, body) {
  try {
    const opts = {
      method,
      headers: { Authorization: `Bearer ${_wsToken}`, 'Content-Type': 'application/json' },
    };
    if (body) opts.body = JSON.stringify(body);
    const res  = await fetch(`${_wsBaseUrl}api/${path}`, opts);
    const ct   = res.headers.get('content-type') || '';
    if (!ct.includes('application/json')) return null;
    const data = await res.json();
    if (!res.ok) { _wsToast(data.error || `Error ${res.status}`, 'error'); return null; }
    return data;
  } catch (e) {
    _wsToast('Network error', 'error');
    return null;
  }
}

function _wsEsc(s) {
  return String(s ?? '')
    .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function _wsLinkify(s) {
  return s.replace(/(https?:\/\/[^\s<>"]+)/g, url => `<a href="${url}" target="_blank" rel="noopener" class="ws-link">${url}</a>`);
}

function _wsFriendlyDate(iso) {
  const d    = new Date(iso);
  const now  = new Date();
  const diff = now - d;
  if (diff < 60000)    return 'just now';
  if (diff < 3600000)  return Math.floor(diff/60000) + 'm';
  if (diff < 86400000) return d.toLocaleTimeString([], { hour:'2-digit', minute:'2-digit' });
  if (diff < 604800000)return d.toLocaleDateString([], { weekday:'short' });
  return d.toLocaleDateString([], { month:'short', day:'numeric' });
}

function _wsToast(msg, type = 'default') {
  const c   = document.getElementById('wsToastContainer');
  if (!c) return;
  const el  = document.createElement('div');
  el.className = `ws-toast ws-toast--${type}`;
  el.textContent = msg;
  c.appendChild(el);
  setTimeout(() => el.classList.add('ws-toast--visible'), 10);
  setTimeout(() => { el.classList.remove('ws-toast--visible'); setTimeout(() => el.remove(), 300); }, 3500);
}

function _wsShowNavBadge(tool) {
  const b = document.getElementById('wsNav' + tool + 'Badge');
  if (b) b.style.display = 'flex';
}
function _wsClearNavBadge(tool) {
  const b = document.getElementById('wsNav' + tool + 'Badge');
  if (b) b.style.display = 'none';
}
