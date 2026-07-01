<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc($title) ?></title>
<link rel="icon" href="<?= base_url('favicon.ico') ?>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<!-- Quill rich-text editor (Docs feature) -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<link href="<?= base_url('css/app.css') ?>" rel="stylesheet">
<link href="<?= base_url('css/workspace.css') ?>" rel="stylesheet">
</head>
<body class="ws-body">

<!-- Hidden data for JS -->
<input type="hidden" id="_wsToken"    value="<?= esc($token) ?>">
<input type="hidden" id="_wsUserId"   value="<?= (int)$user['user_id'] ?>">
<input type="hidden" id="_wsUserName" value="<?= esc($user['fname'] . ' ' . $user['lname']) ?>">
<input type="hidden" id="_wsUserPhoto" value="<?= esc($user['profile_photo'] ?? '') ?>">
<input type="hidden" id="_wsSigUrl"   value="<?= esc($signalingUrl) ?>">
<input type="hidden" id="_wsBaseUrl"  value="<?= base_url() ?>">

<!-- ── App Shell ─────────────────────────────────────────────────────────── -->
<div class="ws-shell">

  <!-- Left icon-nav -->
  <nav class="ws-icon-nav">
    <a href="<?= base_url('dashboard') ?>" class="ws-logo-btn" title="Back to VTalanoa">
      <img src="<?= base_url('img/logo.png') ?>" alt="MeetWave" style="height:28px;max-width:140px;object-fit:contain;">
    </a>

    <div class="ws-nav-items">
      <button class="ws-nav-btn active" id="wsNavChat" onclick="wsSwitchTool('chat')" title="Team Chat">
        <i class="fa-solid fa-message"></i>
        <span class="ws-nav-label">Chat</span>
        <span class="ws-nav-badge" id="wsNavChatBadge" style="display:none"></span>
      </button>
      <button class="ws-nav-btn" id="wsNavDocs" onclick="wsSwitchTool('docs')" title="Docs">
        <i class="fa-solid fa-file-lines"></i>
        <span class="ws-nav-label">Docs</span>
      </button>
      <button class="ws-nav-btn" id="wsNavMail" onclick="wsSwitchTool('mail')" title="Mail">
        <i class="fa-solid fa-envelope"></i>
        <span class="ws-nav-label">Mail</span>
        <span class="ws-nav-badge" id="wsNavMailBadge" style="display:none"></span>
      </button>
      <button class="ws-nav-btn" id="wsNavCalendar" onclick="wsSwitchTool('calendar')" title="Calendar">
        <i class="fa-solid fa-calendar-days"></i>
        <span class="ws-nav-label">Calendar</span>
      </button>
    </div>

    <div class="ws-nav-bottom">
      <a href="<?= base_url('meetings') ?>" class="ws-nav-btn" title="Meetings">
        <i class="fa-solid fa-video"></i>
        <span class="ws-nav-label">Meet</span>
      </a>
      <?php $photo = $user['profile_photo'] ?? ''; ?>
      <a href="<?= base_url('profile') ?>" class="ws-avatar-btn" title="<?= esc($user['fname']) ?>">
        <?php if ($photo): ?>
          <img src="<?= base_url(esc($photo)) ?>" alt="" class="ws-avatar-img">
        <?php else: ?>
          <div class="ws-avatar-initials"><?= strtoupper(substr($user['fname'],0,1).substr($user['lname'],0,1)) ?></div>
        <?php endif; ?>
      </a>
    </div>
  </nav>

  <!-- ── CHAT ─────────────────────────────────────────────────────────────── -->
  <section class="ws-section ws-section--chat" id="wsSectionChat">

    <!-- Channel sidebar -->
    <aside class="ws-channel-sidebar">
      <div class="ws-sidebar-header">
        <span class="ws-sidebar-title">Team Chat</span>
        <button class="ws-sidebar-action" onclick="wsShowNewChannel()" title="New channel">
          <i class="fa-solid fa-plus"></i>
        </button>
      </div>

      <div class="ws-sidebar-section-label">Channels</div>
      <div id="wsChannelList" class="ws-channel-list"></div>

      <div class="ws-sidebar-section-label" style="margin-top:auto;padding-top:12px;">
        <button class="ws-btn-text" onclick="wsShowNewChannel()">
          <i class="fa-solid fa-plus me-1"></i>Add channel
        </button>
      </div>
    </aside>

    <!-- Message pane -->
    <div class="ws-chat-main">
      <div class="ws-chat-header" id="wsChatHeader">
        <span id="wsChatChannelName" class="ws-chat-channel-name">Select a channel</span>
        <span id="wsChatChannelDesc" class="ws-chat-channel-desc"></span>
      </div>

      <div class="ws-messages" id="wsMessages">
        <div class="ws-messages-empty" id="wsMessagesEmpty">
          <i class="fa-solid fa-comments"></i>
          <p>Select a channel to start chatting</p>
        </div>
      </div>

      <div class="ws-typing-bar" id="wsTypingBar" style="display:none"></div>

      <div class="ws-chat-input-area" id="wsChatInputArea" style="display:none">
        <div class="ws-chat-input-wrap">
          <button class="ws-input-action" onclick="document.getElementById('wsChatFileInput').click()" title="Attach file">
            <i class="fa-solid fa-paperclip"></i>
          </button>
          <input type="file" id="wsChatFileInput" style="display:none" onchange="wsHandleChatFile(this)">
          <textarea id="wsChatInput" class="ws-chat-textarea" placeholder="Message #general…" rows="1"
            onkeydown="wsChatKeydown(event)" oninput="wsOnChatInput(this)"></textarea>
          <button class="ws-send-btn" onclick="wsSendMessage()" title="Send">
            <i class="fa-solid fa-paper-plane"></i>
          </button>
        </div>
      </div>
    </div>

  </section>

  <!-- ── DOCS ──────────────────────────────────────────────────────────────── -->
  <section class="ws-section ws-section--docs" id="wsSectionDocs" style="display:none">

    <aside class="ws-doc-sidebar">
      <div class="ws-sidebar-header">
        <span class="ws-sidebar-title">Docs</span>
        <button class="ws-sidebar-action" onclick="wsNewDoc()" title="New document">
          <i class="fa-solid fa-plus"></i>
        </button>
      </div>
      <div id="wsDocList" class="ws-doc-list"></div>
    </aside>

    <div class="ws-doc-main" id="wsDocMain">
      <div class="ws-doc-empty" id="wsDocEmpty">
        <i class="fa-solid fa-file-lines"></i>
        <p>Select a document or create a new one</p>
        <button class="ws-btn-primary" onclick="wsNewDoc()">
          <i class="fa-solid fa-plus me-2"></i>New Document
        </button>
      </div>

      <div id="wsDocEditor" style="display:none;flex-direction:column;flex:1;min-height:0;">
        <div class="ws-doc-toolbar">
          <input type="text" id="wsDocTitle" class="ws-doc-title-input" placeholder="Document title…"
            oninput="wsOnDocTitleChange()" onblur="wsSaveDoc()">
          <div class="ws-doc-toolbar-right">
            <span id="wsDocSaveStatus" class="ws-doc-save-status">Saved</span>
            <button class="ws-btn-danger-sm" onclick="wsDeleteDoc()" title="Delete document">
              <i class="fa-solid fa-trash"></i>
            </button>
          </div>
        </div>
        <div id="wsQuillEditor" class="ws-quill-wrap"></div>
      </div>
    </div>

  </section>

  <!-- ── MAIL ──────────────────────────────────────────────────────────────── -->
  <section class="ws-section ws-section--mail" id="wsSectionMail" style="display:none">

    <aside class="ws-mail-sidebar">
      <div class="ws-sidebar-header">
        <span class="ws-sidebar-title">Mail</span>
        <button class="ws-btn-compose" onclick="wsComposeModal()" title="Compose">
          <i class="fa-solid fa-pen-to-square me-1"></i>Compose
        </button>
      </div>

      <div class="ws-mail-folders">
        <button class="ws-folder-btn active" id="wsFolderInbox" onclick="wsSetMailFolder('inbox')">
          <i class="fa-solid fa-inbox me-2"></i>Inbox
          <span class="ws-folder-badge" id="wsInboxBadge" style="display:none"></span>
        </button>
        <button class="ws-folder-btn" id="wsFolderSent" onclick="wsSetMailFolder('sent')">
          <i class="fa-solid fa-paper-plane me-2"></i>Sent
        </button>
        <button class="ws-folder-btn" id="wsFolderStarred" onclick="wsSetMailFolder('starred')">
          <i class="fa-solid fa-star me-2"></i>Starred
        </button>
      </div>
    </aside>

    <div class="ws-mail-list-pane" id="wsMailListPane">
      <div id="wsMailList" class="ws-mail-list"></div>
    </div>

    <div class="ws-mail-view-pane" id="wsMailViewPane">
      <div class="ws-mail-view-empty" id="wsMailViewEmpty">
        <i class="fa-solid fa-envelope-open-text"></i>
        <p>Select a message to read</p>
      </div>
      <div id="wsMailView" style="display:none"></div>
    </div>

  </section>

  <!-- ── CALENDAR ───────────────────────────────────────────────────────────── -->
  <section class="ws-section ws-section--calendar" id="wsSectionCalendar" style="display:none">

    <div class="ws-cal-main">
      <div class="ws-cal-header">
        <button class="ws-cal-nav" onclick="wsCalNav(-1)"><i class="fa-solid fa-chevron-left"></i></button>
        <h2 id="wsCalTitle" class="ws-cal-title"></h2>
        <button class="ws-cal-nav" onclick="wsCalNav(1)"><i class="fa-solid fa-chevron-right"></i></button>
        <button class="ws-cal-today" onclick="wsCalToday()">Today</button>
        <button class="ws-btn-primary ms-auto" onclick="wsCalNewEvent()">
          <i class="fa-solid fa-plus me-1"></i>New Event
        </button>
      </div>
      <div class="ws-cal-grid-header">
        <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
      </div>
      <div id="wsCalGrid" class="ws-cal-grid"></div>
    </div>

    <aside class="ws-cal-sidebar">
      <div class="ws-sidebar-header">
        <span class="ws-sidebar-title" id="wsCalDayLabel">Events</span>
      </div>
      <div id="wsCalDayEvents" class="ws-cal-day-events">
        <div class="ws-cal-no-events">No events today</div>
      </div>
    </aside>

  </section>

</div><!-- .ws-shell -->

<!-- ── Compose Mail Modal ──────────────────────────────────────────────────── -->
<div id="wsComposeModal" class="ws-modal-overlay" style="display:none" onclick="wsCloseCompose(event)">
  <div class="ws-modal" onclick="event.stopPropagation()">
    <div class="ws-modal-header">
      <span>New Message</span>
      <button class="ws-modal-close" onclick="wsCloseCompose()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="ws-modal-body">
      <div class="ws-compose-field">
        <label>To</label>
        <div id="wsComposeToWrap" class="ws-recipient-wrap">
          <div id="wsComposeToPills" class="ws-recipient-pills"></div>
          <input type="text" id="wsComposeToInput" class="ws-recipient-input" placeholder="Search people…"
            oninput="wsRecipientSearch(this, 'to')" onkeydown="wsRecipientKeydown(event, 'to')">
          <div id="wsComposeToDropdown" class="ws-recipient-dropdown" style="display:none"></div>
        </div>
      </div>
      <div class="ws-compose-field">
        <label>Cc</label>
        <div id="wsComposeCcWrap" class="ws-recipient-wrap">
          <div id="wsComposeCcPills" class="ws-recipient-pills"></div>
          <input type="text" id="wsComposeCcInput" class="ws-recipient-input" placeholder="Add cc…"
            oninput="wsRecipientSearch(this, 'cc')" onkeydown="wsRecipientKeydown(event, 'cc')">
          <div id="wsComposeCcDropdown" class="ws-recipient-dropdown" style="display:none"></div>
        </div>
      </div>
      <div class="ws-compose-field">
        <label>Subject</label>
        <input type="text" id="wsComposeSubject" class="ws-compose-input" placeholder="Subject">
      </div>
      <div class="ws-compose-field ws-compose-body-field">
        <textarea id="wsComposeBody" class="ws-compose-body" placeholder="Write your message…"></textarea>
      </div>
    </div>
    <div class="ws-modal-footer">
      <button class="ws-btn-primary" onclick="wsSendMail()">
        <i class="fa-solid fa-paper-plane me-2"></i>Send
      </button>
      <button class="ws-btn-ghost" onclick="wsCloseCompose()">Discard</button>
    </div>
  </div>
</div>

<!-- ── New Channel Modal ──────────────────────────────────────────────────── -->
<div id="wsNewChannelModal" class="ws-modal-overlay" style="display:none" onclick="wsCloseNewChannel(event)">
  <div class="ws-modal ws-modal--sm" onclick="event.stopPropagation()">
    <div class="ws-modal-header">
      <span>Create Channel</span>
      <button class="ws-modal-close" onclick="wsCloseNewChannel()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="ws-modal-body">
      <div class="ws-compose-field">
        <label>Channel name</label>
        <input type="text" id="wsNewChannelName" class="ws-compose-input" placeholder="e.g. marketing"
          onkeydown="if(event.key==='Enter') wsCreateChannel()">
      </div>
      <div class="ws-compose-field">
        <label>Description <span style="font-weight:400;color:rgba(255,255,255,.4)">(optional)</span></label>
        <input type="text" id="wsNewChannelDesc" class="ws-compose-input" placeholder="What's this channel for?">
      </div>
      <div class="ws-compose-field">
        <label>Visibility</label>
        <select id="wsNewChannelType" class="ws-compose-select">
          <option value="public">Public — anyone can join</option>
          <option value="private">Private — invite only</option>
        </select>
      </div>
    </div>
    <div class="ws-modal-footer">
      <button class="ws-btn-primary" onclick="wsCreateChannel()">Create Channel</button>
      <button class="ws-btn-ghost" onclick="wsCloseNewChannel()">Cancel</button>
    </div>
  </div>
</div>

<!-- ── New Calendar Event Modal ──────────────────────────────────────────── -->
<div id="wsCalModal" class="ws-modal-overlay" style="display:none" onclick="wsCloseCalModal(event)">
  <div class="ws-modal ws-modal--sm" onclick="event.stopPropagation()">
    <div class="ws-modal-header">
      <span id="wsCalModalTitle">New Event</span>
      <button class="ws-modal-close" onclick="wsCloseCalModal()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="ws-modal-body">
      <div class="ws-compose-field">
        <label>Title</label>
        <input type="text" id="wsCalEvtTitle" class="ws-compose-input" placeholder="Event title">
      </div>
      <div class="ws-compose-field">
        <label>Start</label>
        <input type="datetime-local" id="wsCalEvtStart" class="ws-compose-input">
      </div>
      <div class="ws-compose-field">
        <label>End</label>
        <input type="datetime-local" id="wsCalEvtEnd" class="ws-compose-input">
      </div>
      <div class="ws-compose-field">
        <label>Color</label>
        <div class="ws-cal-color-picker" id="wsCalColorPicker">
          <?php foreach (['#00aeef','#22c55e','#f59e0b','#ef4444','#8b5cf6','#ec4899','#0ea5e9','#64748b'] as $c): ?>
          <button class="ws-cal-color-btn" data-color="<?= $c ?>" style="background:<?= $c ?>"
            onclick="wsPickCalColor('<?= $c ?>')"></button>
          <?php endforeach; ?>
        </div>
      </div>
      <input type="hidden" id="wsCalEvtColor" value="#00aeef">
      <input type="hidden" id="wsCalEvtId" value="">
    </div>
    <div class="ws-modal-footer">
      <button class="ws-btn-primary" onclick="wsSaveCalEvent()">Save Event</button>
      <button id="wsCalDeleteBtn" class="ws-btn-danger-sm" onclick="wsDeleteCalEvent()" style="display:none">Delete</button>
      <button class="ws-btn-ghost" onclick="wsCloseCalModal()">Cancel</button>
    </div>
  </div>
</div>

<!-- ── Toast container ────────────────────────────────────────────────────── -->
<div id="wsToastContainer" class="ws-toast-container"></div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script src="<?= base_url('js/workspace.js') ?>"></script>
</body>
</html>
