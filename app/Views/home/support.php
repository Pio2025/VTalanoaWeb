<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Help &amp; Support — VTalanoa</title>
<link rel="icon" href="<?= base_url('favicon.ico') ?>">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --navy: #262262;
    --blue: #1c75bc;
    --blue-light: #eff6ff;
    --text: #1e293b;
    --muted: #64748b;
    --line: #e2e8f0;
    --bg: #f8fafc;
  }
  body { font-family: 'Inter', sans-serif; color: var(--text); background: #fff; line-height: 1.6; }

  /* Nav */
  header { position: sticky; top: 0; z-index: 100; background: rgba(255,255,255,.95); backdrop-filter: blur(8px); border-bottom: 1px solid var(--line); }
  .wrap-nav { max-width: 1140px; margin: 0 auto; padding: 0 24px; display: flex; align-items: center; justify-content: space-between; height: 64px; }
  .nav-links { display: flex; gap: 28px; list-style: none; align-items: center; }
  .nav-links a { text-decoration: none; color: var(--muted); font-size: 14px; font-weight: 500; transition: color .15s; }
  .nav-links a:hover { color: var(--blue); }
  .btn-nav { background: var(--blue); color: #fff !important; padding: 8px 20px; border-radius: 8px; }
  .btn-nav:hover { background: #1560a0 !important; }

  /* Hero with search */
  .hero { background: linear-gradient(135deg, #262262 0%, #3d3797 55%, #1c75bc 100%); padding: 72px 24px 80px; text-align: center; color: #fff; }
  .hero h1 { font-size: clamp(28px, 5vw, 48px); font-weight: 800; margin-bottom: 12px; }
  .hero p { font-size: 16px; opacity: .85; margin-bottom: 36px; }
  .search-wrap { max-width: 580px; margin: 0 auto; position: relative; }
  .search-wrap input { width: 100%; padding: 18px 56px 18px 22px; border-radius: 14px; border: none; font-size: 16px; font-family: inherit; outline: none; box-shadow: 0 8px 32px rgba(0,0,0,.18); }
  .search-wrap input::placeholder { color: #94a3b8; }
  .search-btn { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: var(--blue); border: none; border-radius: 10px; width: 42px; height: 42px; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 17px; }
  .search-suggestions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: center; margin-top: 18px; }
  .search-suggestions a { background: rgba(255,255,255,.15); color: #fff; font-size: 13px; padding: 6px 14px; border-radius: 20px; text-decoration: none; border: 1px solid rgba(255,255,255,.25); transition: background .15s; }
  .search-suggestions a:hover { background: rgba(255,255,255,.28); }

  /* Categories */
  .section { max-width: 1100px; margin: 0 auto; padding: 72px 24px 0; }
  .section-title { font-size: 26px; font-weight: 700; color: var(--navy); margin-bottom: 8px; }
  .section-sub { color: var(--muted); font-size: 15px; margin-bottom: 40px; }
  .categories { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px; }
  .cat-card { border: 1.5px solid var(--line); border-radius: 16px; padding: 28px 24px; text-decoration: none; color: var(--text); background: #fff; transition: border-color .2s, box-shadow .2s, transform .2s; display: flex; flex-direction: column; gap: 12px; }
  .cat-card:hover { border-color: var(--blue); box-shadow: 0 8px 28px rgba(28,117,188,.12); transform: translateY(-2px); }
  .cat-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
  .cat-card h3 { font-size: 16px; font-weight: 700; color: var(--navy); }
  .cat-card p { font-size: 13px; color: var(--muted); line-height: 1.5; }
  .cat-articles { font-size: 12px; color: var(--blue); font-weight: 600; margin-top: auto; }

  /* Popular articles */
  .articles-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px; }
  .article-link { display: flex; align-items: center; gap: 14px; padding: 16px 20px; border: 1.5px solid var(--line); border-radius: 12px; text-decoration: none; color: var(--text); background: #fff; transition: border-color .2s, box-shadow .2s; }
  .article-link:hover { border-color: var(--blue); box-shadow: 0 4px 16px rgba(28,117,188,.1); }
  .article-icon { font-size: 20px; flex-shrink: 0; }
  .article-link span { font-size: 14px; font-weight: 500; color: var(--text); }
  .article-arrow { margin-left: auto; color: var(--muted); font-size: 14px; }

  /* FAQ accordion */
  .faq-list { margin-top: 8px; }
  .faq-group { margin-bottom: 16px; border: 1.5px solid var(--line); border-radius: 14px; overflow: hidden; }
  .faq-group-header { background: var(--bg); padding: 14px 20px; font-size: 13px; font-weight: 700; color: var(--navy); text-transform: uppercase; letter-spacing: .4px; border-bottom: 1px solid var(--line); }
  .faq-item { border-bottom: 1px solid var(--line); }
  .faq-item:last-child { border-bottom: none; }
  .faq-q { width: 100%; background: none; border: none; padding: 18px 22px; text-align: left; font-family: inherit; font-size: 15px; font-weight: 600; color: var(--text); cursor: pointer; display: flex; justify-content: space-between; align-items: center; gap: 12px; transition: background .15s; }
  .faq-q:hover { background: var(--bg); }
  .faq-q .chevron { font-size: 12px; color: var(--muted); transition: transform .25s; flex-shrink: 0; }
  .faq-q.open .chevron { transform: rotate(180deg); }
  .faq-a { display: none; padding: 0 22px 20px; font-size: 14px; color: #374151; line-height: 1.75; }
  .faq-a.open { display: block; }
  .faq-a ul { padding-left: 20px; margin-top: 8px; }
  .faq-a li { margin-bottom: 6px; }
  .faq-a a { color: var(--blue); }

  /* Video guides */
  .guides-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px; }
  .guide-card { border: 1.5px solid var(--line); border-radius: 16px; overflow: hidden; }
  .guide-thumb { background: linear-gradient(135deg, var(--navy), var(--blue)); aspect-ratio: 16/9; display: flex; align-items: center; justify-content: center; font-size: 40px; color: rgba(255,255,255,.9); }
  .guide-body { padding: 16px 18px; }
  .guide-body h4 { font-size: 14px; font-weight: 700; color: var(--text); margin-bottom: 4px; }
  .guide-body p { font-size: 13px; color: var(--muted); }

  /* CTA banner */
  .cta-banner { background: linear-gradient(135deg, var(--navy), var(--blue)); border-radius: 20px; padding: 48px 40px; display: flex; align-items: center; justify-content: space-between; gap: 32px; flex-wrap: wrap; margin: 72px 24px 0; max-width: 1100px; margin-left: auto; margin-right: auto; }
  .cta-banner h2 { font-size: 24px; font-weight: 700; color: #fff; margin-bottom: 8px; }
  .cta-banner p { color: rgba(255,255,255,.82); font-size: 15px; }
  .cta-actions { display: flex; gap: 14px; flex-wrap: wrap; }
  .btn-cta-white { background: #fff; color: var(--navy); padding: 13px 28px; border-radius: 10px; font-size: 15px; font-weight: 700; text-decoration: none; transition: opacity .2s; }
  .btn-cta-white:hover { opacity: .9; }
  .btn-cta-outline { border: 2px solid rgba(255,255,255,.6); color: #fff; padding: 13px 28px; border-radius: 10px; font-size: 15px; font-weight: 700; text-decoration: none; transition: background .2s; }
  .btn-cta-outline:hover { background: rgba(255,255,255,.1); }

  /* Footer */
  footer { border-top: 1px solid var(--line); background: var(--bg); margin-top: 80px; }
  .foot { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; padding: 28px 24px; max-width: 1140px; margin: 0 auto; }
  .foot-links { display: flex; gap: 24px; }
  .foot-links a { color: var(--muted); text-decoration: none; font-size: 13px; }
  .foot-links a:hover { color: var(--blue); }
  .copy { font-size: 13px; color: var(--muted); }

  /* Search results */
  #searchResults { display: none; position: absolute; top: 100%; left: 0; right: 0; background: #fff; border-radius: 14px; box-shadow: 0 12px 40px rgba(0,0,0,.15); margin-top: 8px; overflow: hidden; z-index: 50; text-align: left; max-height: 360px; overflow-y: auto; }
  .sr-item { padding: 14px 20px; display: flex; gap: 12px; align-items: flex-start; cursor: pointer; border-bottom: 1px solid var(--line); text-decoration: none; color: var(--text); }
  .sr-item:last-child { border-bottom: none; }
  .sr-item:hover { background: var(--blue-light); }
  .sr-item-icon { font-size: 16px; flex-shrink: 0; margin-top: 1px; }
  .sr-item-title { font-size: 14px; font-weight: 600; margin-bottom: 2px; }
  .sr-item-cat { font-size: 12px; color: var(--muted); }
  .sr-no-results { padding: 24px 20px; text-align: center; color: var(--muted); font-size: 14px; }
</style>
</head>
<body>

<header>
  <div class="wrap-nav">
    <a href="<?= base_url('/') ?>">
      <img src="<?= base_url('img/logo-web.png') ?>" alt="VTalanoa" style="height:36px;max-width:180px;object-fit:contain;display:block;">
    </a>
    <nav>
      <ul class="nav-links">
        <li><a href="<?= base_url('features') ?>">Features</a></li>
        <li><a href="<?= base_url('pricing') ?>">Pricing</a></li>
        <li><a href="<?= base_url('contact') ?>">Contact</a></li>
        <li><a href="<?= base_url('auth/login') ?>">Sign In</a></li>
        <li><a href="<?= base_url('auth/register') ?>" class="btn-nav">Get Started</a></li>
      </ul>
    </nav>
  </div>
</header>

<!-- Hero Search -->
<div class="hero">
  <h1>How can we help?</h1>
  <p>Search our knowledge base or browse the topics below.</p>
  <div class="search-wrap">
    <input type="text" id="searchInput" placeholder="Search for answers… e.g. &quot;join a meeting&quot;" autocomplete="off">
    <button class="search-btn" onclick="runSearch()">&#128269;</button>
    <div id="searchResults"></div>
  </div>
  <div class="search-suggestions">
    <a href="#meetings">Host a meeting</a>
    <a href="#audio">Fix audio issues</a>
    <a href="#account">Reset password</a>
    <a href="#billing">Upgrade plan</a>
    <a href="#security">Waiting room</a>
    <a href="#getting-started">Getting started</a>
  </div>
</div>

<!-- Categories -->
<div class="section" id="getting-started">
  <p class="section-title">Browse by topic</p>
  <p class="section-sub">Find answers grouped by the area you need help with.</p>
  <div class="categories">
    <a href="#faq-getting-started" class="cat-card">
      <div class="cat-icon" style="background:#eff6ff;">&#128640;</div>
      <h3>Getting Started</h3>
      <p>Set up your account, explore the dashboard, and host your first meeting.</p>
      <span class="cat-articles">6 articles</span>
    </a>
    <a href="#faq-meetings" class="cat-card" id="meetings">
      <div class="cat-icon" style="background:#f0fdf4;">&#127909;</div>
      <h3>Meetings</h3>
      <p>Schedule, host, join, and manage video meetings with all their features.</p>
      <span class="cat-articles">8 articles</span>
    </a>
    <a href="#faq-audio" class="cat-card" id="audio">
      <div class="cat-icon" style="background:#fff7ed;">&#127908;</div>
      <h3>Audio &amp; Video</h3>
      <p>Camera and microphone setup, troubleshooting, and quality tips.</p>
      <span class="cat-articles">5 articles</span>
    </a>
    <a href="#faq-account" class="cat-card" id="account">
      <div class="cat-icon" style="background:#fdf4ff;">&#128100;</div>
      <h3>Account &amp; Profile</h3>
      <p>Update your profile, change password, manage connected apps.</p>
      <span class="cat-articles">5 articles</span>
    </a>
    <a href="#faq-billing" class="cat-card" id="billing">
      <div class="cat-icon" style="background:#fefce8;">&#128179;</div>
      <h3>Plans &amp; Billing</h3>
      <p>Understand pricing, upgrade or downgrade, and manage subscriptions.</p>
      <span class="cat-articles">4 articles</span>
    </a>
    <a href="#faq-security" class="cat-card" id="security">
      <div class="cat-icon" style="background:#fff1f2;">&#128274;</div>
      <h3>Security &amp; Privacy</h3>
      <p>Waiting rooms, meeting passwords, recording consent, and data safety.</p>
      <span class="cat-articles">5 articles</span>
    </a>
  </div>
</div>

<!-- Popular Articles -->
<div class="section" style="padding-top:64px;">
  <p class="section-title">Popular articles</p>
  <p class="section-sub">The most frequently asked questions from VTalanoa users.</p>
  <div class="articles-grid">
    <a href="#faq-getting-started" class="article-link"><span class="article-icon">&#128640;</span><span>How do I create an account?</span><span class="article-arrow">›</span></a>
    <a href="#faq-meetings" class="article-link"><span class="article-icon">&#127909;</span><span>How do I host my first meeting?</span><span class="article-arrow">›</span></a>
    <a href="#faq-meetings" class="article-link"><span class="article-icon">&#128279;</span><span>How do I share a meeting link?</span><span class="article-arrow">›</span></a>
    <a href="#faq-audio" class="article-link"><span class="article-icon">&#127908;</span><span>My microphone isn't working</span><span class="article-arrow">›</span></a>
    <a href="#faq-audio" class="article-link"><span class="article-icon">&#128247;</span><span>My camera isn't showing</span><span class="article-arrow">›</span></a>
    <a href="#faq-account" class="article-link"><span class="article-icon">&#128272;</span><span>How do I reset my password?</span><span class="article-arrow">›</span></a>
    <a href="#faq-security" class="article-link"><span class="article-icon">&#128274;</span><span>How does the waiting room work?</span><span class="article-arrow">›</span></a>
    <a href="#faq-billing" class="article-link"><span class="article-icon">&#11014;</span><span>How do I upgrade my plan?</span><span class="article-arrow">›</span></a>
    <a href="#faq-meetings" class="article-link"><span class="article-icon">&#127897;</span><span>How do I record a meeting?</span><span class="article-arrow">›</span></a>
    <a href="#faq-meetings" class="article-link"><span class="article-icon">&#128100;</span><span>How do I add participants?</span><span class="article-arrow">›</span></a>
    <a href="#faq-security" class="article-link"><span class="article-icon">&#128275;</span><span>How do I set a meeting password?</span><span class="article-arrow">›</span></a>
    <a href="#faq-getting-started" class="article-link"><span class="article-icon">&#127760;</span><span>Which browsers are supported?</span><span class="article-arrow">›</span></a>
  </div>
</div>

<!-- FAQ Section -->
<div class="section" style="padding-top:64px; padding-bottom:0;">
  <p class="section-title">Frequently Asked Questions</p>
  <p class="section-sub">Detailed answers to the most common questions.</p>

  <div class="faq-list">

    <!-- Getting Started -->
    <div class="faq-group" id="faq-getting-started">
      <div class="faq-group-header">&#128640; Getting Started</div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">How do I create a VTalanoa account? <span class="chevron">&#9660;</span></button>
        <div class="faq-a">Visit <a href="<?= base_url('auth/register') ?>">vtalanoa.com/auth/register</a> and fill in your name, email, username, and password. Once registered, you'll be logged in automatically and taken to your dashboard.</div>
      </div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">How do I host my very first meeting? <span class="chevron">&#9660;</span></button>
        <div class="faq-a">
          After signing in:
          <ul>
            <li>Click <strong>Schedule</strong> in the Meetings section of your dashboard.</li>
            <li>Enter a title, description, start and end time, and an optional password.</li>
            <li>Click <strong>Create Meeting</strong>. Your meeting link is generated immediately.</li>
            <li>Share the link with participants and click <strong>Start</strong> when ready.</li>
          </ul>
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">Which browsers does VTalanoa support? <span class="chevron">&#9660;</span></button>
        <div class="faq-a">VTalanoa runs entirely in the browser — no download required. We support:
          <ul>
            <li><strong>Google Chrome</strong> (recommended) — version 80+</li>
            <li><strong>Microsoft Edge</strong> — version 80+</li>
            <li><strong>Mozilla Firefox</strong> — version 75+</li>
            <li><strong>Safari</strong> — version 14+ (macOS/iOS)</li>
          </ul>
          For the best experience, keep your browser up to date. Chrome or Edge give the most reliable audio/video performance.
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">Do participants need an account to join? <span class="chevron">&#9660;</span></button>
        <div class="faq-a">No. Guests can join any meeting using just the meeting link. They will be asked to enter their name before entering. A VTalanoa account is only required to <em>host</em> meetings.</div>
      </div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">What is the dashboard? <span class="chevron">&#9660;</span></button>
        <div class="faq-a">Your dashboard is the home screen after login. It shows your upcoming scheduled meetings, quick actions to start or join a meeting, and recent activity. From here you can navigate to Meetings, Recordings, Profile, and Workspace.</div>
      </div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">How do I join a meeting as a guest? <span class="chevron">&#9660;</span></button>
        <div class="faq-a">Open the meeting link provided by the host. Enter your name in the join page, allow camera/microphone access, preview your video, then click <strong>Join Meeting</strong>. If the host has enabled a waiting room, you will wait until admitted.</div>
      </div>
    </div>

    <!-- Meetings -->
    <div class="faq-group" id="faq-meetings">
      <div class="faq-group-header">&#127909; Meetings</div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">How do I schedule a meeting in advance? <span class="chevron">&#9660;</span></button>
        <div class="faq-a">Go to <strong>Meetings → Schedule a Meeting</strong>. Set a title, date/time, duration, optional description, and password. Once created, the meeting appears in your upcoming list and you can copy the join link to share with participants.</div>
      </div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">How do I share my meeting link? <span class="chevron">&#9660;</span></button>
        <div class="faq-a">From the Meetings list, click a meeting and copy the <strong>Join Link</strong>. Share it via email, messaging app, or calendar invite. If the meeting has a password, share the password separately for security.</div>
      </div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">How do I record a meeting? <span class="chevron">&#9660;</span></button>
        <div class="faq-a">During an active meeting, click the <strong>Record</strong> button in the control bar at the bottom. Recording starts immediately. Click <strong>Stop Recording</strong> to end it. Recordings are saved and can be found under <strong>Meetings → Recordings</strong>. Note: recording requires all participants' consent — please inform them before recording.</div>
      </div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">How do I mute or remove a participant? <span class="chevron">&#9660;</span></button>
        <div class="faq-a">As the host, open the <strong>Participants</strong> panel during the meeting. Click on a participant's name to see options to <strong>Mute</strong> or <strong>Remove</strong> them. Removed participants cannot rejoin unless you re-admit them.</div>
      </div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">How do I share my screen? <span class="chevron">&#9660;</span></button>
        <div class="faq-a">Click the <strong>Share Screen</strong> button in the control bar. Your browser will ask which window, tab, or full screen to share. Select the content and click <strong>Share</strong>. Click <strong>Stop Sharing</strong> when done. Screen sharing is available on Pro and Business plans.</div>
      </div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">How many participants can join a meeting? <span class="chevron">&#9660;</span></button>
        <div class="faq-a">
          <ul>
            <li><strong>Basic (Free):</strong> Up to 100 participants</li>
            <li><strong>Pro:</strong> Up to 300 participants</li>
            <li><strong>Business:</strong> Up to 500 participants</li>
          </ul>
          You can view and change the limit when scheduling a meeting.
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">Can I edit or cancel a scheduled meeting? <span class="chevron">&#9660;</span></button>
        <div class="faq-a">Yes. Go to <strong>Meetings</strong>, find the scheduled meeting and click <strong>Edit</strong>. Update the details and save. To cancel, click <strong>Delete Meeting</strong>. Participants who received the link should be notified manually as VTalanoa does not currently auto-notify on cancellation.</div>
      </div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">What happens when the meeting ends? <span class="chevron">&#9660;</span></button>
        <div class="faq-a">When the host clicks <strong>End Meeting</strong>, all participants are removed and the meeting status changes to <em>Ended</em>. Any recordings are saved and accessible from the Meetings page. Participants are returned to the VTalanoa home page.</div>
      </div>
    </div>

    <!-- Audio & Video -->
    <div class="faq-group" id="faq-audio">
      <div class="faq-group-header">&#127908; Audio &amp; Video</div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">My microphone isn't working. What should I do? <span class="chevron">&#9660;</span></button>
        <div class="faq-a">
          <ul>
            <li>Make sure your browser has microphone permission. Click the lock icon in your browser's address bar and set Microphone to <strong>Allow</strong>.</li>
            <li>Check that the correct input device is selected in your device/OS audio settings.</li>
            <li>Close other apps that may be using the microphone (Teams, Zoom, Discord).</li>
            <li>Try refreshing the page or switching to Chrome/Edge.</li>
            <li>Restart your browser if the issue persists.</li>
          </ul>
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">My camera isn't showing video. What should I do? <span class="chevron">&#9660;</span></button>
        <div class="faq-a">
          <ul>
            <li>Check browser camera permissions (lock icon in address bar → Camera → Allow).</li>
            <li>Make sure no other app is using the camera simultaneously.</li>
            <li>If you're on a laptop, check whether a physical camera shutter or privacy switch is closed.</li>
            <li>On Windows, go to <strong>Settings → Privacy → Camera</strong> and ensure your browser is allowed.</li>
            <li>Try a different browser or incognito/private window to rule out extension conflicts.</li>
          </ul>
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">Other participants can't hear me clearly. <span class="chevron">&#9660;</span></button>
        <div class="faq-a">Try these steps in order:
          <ul>
            <li>Use a headset or headphones instead of built-in speakers to prevent echo.</li>
            <li>Move closer to your microphone or speak louder.</li>
            <li>Check your internet connection — poor bandwidth causes choppy audio.</li>
            <li>Close background apps and browser tabs to free up resources.</li>
            <li>Ask others to check they are not on mute on their end.</li>
          </ul>
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">The video is lagging or freezing. <span class="chevron">&#9660;</span></button>
        <div class="faq-a">Video lag is usually caused by a weak internet connection. Try:
          <ul>
            <li>Moving closer to your Wi-Fi router or switching to a wired ethernet connection.</li>
            <li>Turning off your camera and using audio-only to reduce bandwidth.</li>
            <li>Closing other programs and browser tabs using the internet.</li>
            <li>Restarting your router if speeds seem low.</li>
          </ul>
          A minimum of 3 Mbps upload/download is recommended for HD video.
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">Can I test my camera and mic before joining? <span class="chevron">&#9660;</span></button>
        <div class="faq-a">Yes. On the join/preview page before entering a meeting, you can see your camera feed and test your microphone. If your devices don't appear, check browser permissions and try refreshing the page.</div>
      </div>
    </div>

    <!-- Account & Profile -->
    <div class="faq-group" id="faq-account">
      <div class="faq-group-header">&#128100; Account &amp; Profile</div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">How do I reset my password? <span class="chevron">&#9660;</span></button>
        <div class="faq-a">Go to <a href="<?= base_url('auth/forgot-password') ?>">vtalanoa.com/auth/forgot-password</a>. Enter the email address on your account. We'll send a reset link that's valid for 1 hour. Click the link in the email, enter your new password, and you're done.</div>
      </div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">How do I update my name or profile photo? <span class="chevron">&#9660;</span></button>
        <div class="faq-a">Sign in and go to your <a href="<?= base_url('profile') ?>">Profile page</a> (click your avatar in the sidebar). You can update your first name, last name, timezone, and profile photo. Changes save immediately.</div>
      </div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">How do I change my email address? <span class="chevron">&#9660;</span></button>
        <div class="faq-a">Email address changes currently require contacting support. Email <a href="mailto:support@vtalanoa.com">support@vtalanoa.com</a> from your current registered address with your requested new email, and we'll process it within 2 business days.</div>
      </div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">How do I sign in with Google or Microsoft? <span class="chevron">&#9660;</span></button>
        <div class="faq-a">On the <a href="<?= base_url('auth/login') ?>">login page</a>, click <strong>Continue with Google</strong> or <strong>Continue with Microsoft</strong>. If your social email matches an existing VTalanoa account, you'll be logged in and the two accounts will be linked automatically.</div>
      </div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">How do I delete my account? <span class="chevron">&#9660;</span></button>
        <div class="faq-a">To request account deletion, email <a href="mailto:privacy@vtalanoa.com">privacy@vtalanoa.com</a> from your registered email address. Your data will be removed within 30 days. Note that any meetings you hosted will also be deleted.</div>
      </div>
    </div>

    <!-- Plans & Billing -->
    <div class="faq-group" id="faq-billing">
      <div class="faq-group-header">&#128179; Plans &amp; Billing</div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">What plans does VTalanoa offer? <span class="chevron">&#9660;</span></button>
        <div class="faq-a">
          <ul>
            <li><strong>Basic (Free):</strong> Up to 100 participants, 40-minute meeting limit, core features.</li>
            <li><strong>Pro ($30/month):</strong> Up to 300 participants, unlimited duration, screen sharing, recording, AI companion.</li>
            <li><strong>Business ($50/month):</strong> Up to 500 participants, all Pro features, priority support, advanced admin tools.</li>
          </ul>
          See the full <a href="<?= base_url('pricing') ?>">Pricing page</a> for a feature comparison.
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">How do I upgrade my plan? <span class="chevron">&#9660;</span></button>
        <div class="faq-a">Visit the <a href="<?= base_url('pricing') ?>">Pricing page</a> and click <strong>Get Started</strong> on the plan you want, or email <a href="mailto:billing@vtalanoa.com">billing@vtalanoa.com</a> for assisted upgrades. Pro-rated charges apply for mid-cycle upgrades.</div>
      </div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">Can I cancel my subscription at any time? <span class="chevron">&#9660;</span></button>
        <div class="faq-a">Yes. You can cancel at any time. Your plan will remain active until the end of the current billing period. After that, your account reverts to the Basic (Free) plan. No refunds are issued for partial months, but contact us at <a href="mailto:billing@vtalanoa.com">billing@vtalanoa.com</a> if you believe an error occurred.</div>
      </div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">What payment methods do you accept? <span class="chevron">&#9660;</span></button>
        <div class="faq-a">We accept major credit and debit cards (Visa, Mastercard, American Express). All payments are processed securely. We do not store your full card details on our servers.</div>
      </div>
    </div>

    <!-- Security & Privacy -->
    <div class="faq-group" id="faq-security">
      <div class="faq-group-header">&#128274; Security &amp; Privacy</div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">How does the waiting room work? <span class="chevron">&#9660;</span></button>
        <div class="faq-a">When waiting room is enabled (default), participants who join are held in a virtual lobby until the host admits them. As host, you see a notification and a list of waiting participants. You can admit them one by one or all at once. This prevents uninvited guests from entering automatically.</div>
      </div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">How do I set a meeting password? <span class="chevron">&#9660;</span></button>
        <div class="faq-a">When scheduling a meeting, there is a <strong>Password</strong> field. Enter a password (or leave blank for no password). Participants will need to enter this password on the join page. Share the password securely — for example, don't include it in the same message as the meeting link.</div>
      </div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">Is my meeting data encrypted? <span class="chevron">&#9660;</span></button>
        <div class="faq-a">All data transmitted between your browser and VTalanoa servers is encrypted using HTTPS/TLS. Meeting video and audio streams are transmitted via secure WebRTC connections. We do not intercept or record your meetings without your explicit request to do so.</div>
      </div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">Someone is disrupting my meeting — what can I do? <span class="chevron">&#9660;</span></button>
        <div class="faq-a">As host, you can:
          <ul>
            <li><strong>Mute</strong> the disruptive participant.</li>
            <li><strong>Remove</strong> them from the meeting via the Participants panel.</li>
            <li><strong>Lock</strong> the meeting so no new participants can join.</li>
            <li>Enable the <strong>waiting room</strong> so you must approve anyone who tries to rejoin.</li>
          </ul>
          If harassment continues, report the incident to <a href="mailto:support@vtalanoa.com">support@vtalanoa.com</a>.
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">How is my personal data used? <span class="chevron">&#9660;</span></button>
        <div class="faq-a">We only use your data to provide the Service. We do not sell your data to third parties. For full details, see our <a href="<?= base_url('privacy') ?>">Privacy Policy</a>.</div>
      </div>
    </div>

  </div><!-- /faq-list -->
</div>

<!-- Quick Video Guides -->
<div class="section" style="padding-top:64px;">
  <p class="section-title">Quick guides</p>
  <p class="section-sub">Step-by-step visual walkthroughs for common tasks.</p>
  <div class="guides-grid">
    <div class="guide-card">
      <div class="guide-thumb">&#127909;</div>
      <div class="guide-body"><h4>Host your first meeting</h4><p>From sign-up to your first live video call in under 5 minutes.</p></div>
    </div>
    <div class="guide-card">
      <div class="guide-thumb">&#128247;</div>
      <div class="guide-body"><h4>Camera &amp; mic setup</h4><p>Test and configure your devices before joining a meeting.</p></div>
    </div>
    <div class="guide-card">
      <div class="guide-thumb">&#128101;</div>
      <div class="guide-body"><h4>Managing participants</h4><p>Admit from waiting room, mute, remove, and assign co-hosts.</p></div>
    </div>
    <div class="guide-card">
      <div class="guide-thumb">&#127897;</div>
      <div class="guide-body"><h4>Recording meetings</h4><p>Start, stop, and download meeting recordings.</p></div>
    </div>
  </div>
</div>

<!-- Contact CTA -->
<div class="cta-banner">
  <div>
    <h2>Still need help?</h2>
    <p>Our support team typically responds within 1–2 business days.</p>
  </div>
  <div class="cta-actions">
    <a href="<?= base_url('contact') ?>" class="btn-cta-white">&#128140; Contact Support</a>
    <a href="mailto:support@vtalanoa.com" class="btn-cta-outline">&#9993; Email Us</a>
  </div>
</div>

<footer>
  <div class="foot">
    <a href="<?= base_url('/') ?>">
      <img src="<?= base_url('img/logo-web.png') ?>" alt="VTalanoa" style="height:30px;max-width:140px;object-fit:contain;display:block;">
    </a>
    <nav class="foot-links" aria-label="Footer">
      <a href="<?= base_url('support') ?>">Help</a>
      <a href="<?= base_url('contact') ?>">Contact</a>
      <a href="<?= base_url('privacy') ?>">Privacy Policy</a>
      <a href="<?= base_url('terms') ?>">Terms of Service</a>
    </nav>
    <span class="copy">&copy; <?= date('Y') ?> VTalanoa. All rights reserved.</span>
  </div>
</footer>

<script>
// FAQ accordion
function toggleFaq(btn) {
  const answer = btn.nextElementSibling;
  const isOpen = btn.classList.contains('open');
  // close all
  document.querySelectorAll('.faq-q.open').forEach(b => {
    b.classList.remove('open');
    b.nextElementSibling.classList.remove('open');
  });
  if (!isOpen) {
    btn.classList.add('open');
    answer.classList.add('open');
    btn.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }
}

// Client-side search
const articles = [
  { title: 'How do I create an account?',       anchor: '#faq-getting-started', cat: 'Getting Started',   icon: '&#128640;' },
  { title: 'How do I host my first meeting?',   anchor: '#faq-meetings',        cat: 'Meetings',          icon: '&#127909;' },
  { title: 'Which browsers are supported?',     anchor: '#faq-getting-started', cat: 'Getting Started',   icon: '&#127760;' },
  { title: 'Do participants need an account?',  anchor: '#faq-getting-started', cat: 'Getting Started',   icon: '&#128100;' },
  { title: 'How do I schedule a meeting?',      anchor: '#faq-meetings',        cat: 'Meetings',          icon: '&#128197;' },
  { title: 'How do I share a meeting link?',    anchor: '#faq-meetings',        cat: 'Meetings',          icon: '&#128279;' },
  { title: 'How do I record a meeting?',        anchor: '#faq-meetings',        cat: 'Meetings',          icon: '&#127897;' },
  { title: 'How do I mute or remove a participant?', anchor: '#faq-meetings',   cat: 'Meetings',          icon: '&#128101;' },
  { title: 'How do I share my screen?',         anchor: '#faq-meetings',        cat: 'Meetings',          icon: '&#128444;' },
  { title: 'My microphone isn\'t working',      anchor: '#faq-audio',           cat: 'Audio & Video',     icon: '&#127908;' },
  { title: 'My camera isn\'t showing',          anchor: '#faq-audio',           cat: 'Audio & Video',     icon: '&#128247;' },
  { title: 'Video is lagging or freezing',      anchor: '#faq-audio',           cat: 'Audio & Video',     icon: '&#128246;' },
  { title: 'How do I reset my password?',       anchor: '#faq-account',         cat: 'Account & Profile', icon: '&#128272;' },
  { title: 'How do I update my profile?',       anchor: '#faq-account',         cat: 'Account & Profile', icon: '&#128100;' },
  { title: 'How do I sign in with Google?',     anchor: '#faq-account',         cat: 'Account & Profile', icon: '&#128272;' },
  { title: 'How do I delete my account?',       anchor: '#faq-account',         cat: 'Account & Profile', icon: '&#128465;' },
  { title: 'What plans does VTalanoa offer?',   anchor: '#faq-billing',         cat: 'Plans & Billing',   icon: '&#128179;' },
  { title: 'How do I upgrade my plan?',         anchor: '#faq-billing',         cat: 'Plans & Billing',   icon: '&#11014;'  },
  { title: 'Can I cancel my subscription?',     anchor: '#faq-billing',         cat: 'Plans & Billing',   icon: '&#10060;'  },
  { title: 'How does the waiting room work?',   anchor: '#faq-security',        cat: 'Security & Privacy','icon': '&#128274;' },
  { title: 'How do I set a meeting password?',  anchor: '#faq-security',        cat: 'Security & Privacy','icon': '&#128274;' },
  { title: 'Is my meeting data encrypted?',     anchor: '#faq-security',        cat: 'Security & Privacy','icon': '&#128272;' },
  { title: 'Someone is disrupting my meeting',  anchor: '#faq-security',        cat: 'Security & Privacy','icon': '&#128274;' },
];

const searchInput   = document.getElementById('searchInput');
const searchResults = document.getElementById('searchResults');

searchInput.addEventListener('input', debounce(runSearch, 200));
searchInput.addEventListener('keydown', e => { if (e.key === 'Escape') closeSearch(); });
document.addEventListener('click', e => { if (!e.target.closest('.search-wrap')) closeSearch(); });

function runSearch() {
  const q = searchInput.value.trim().toLowerCase();
  if (q.length < 2) { closeSearch(); return; }
  const hits = articles.filter(a => a.title.toLowerCase().includes(q)).slice(0, 8);
  if (hits.length === 0) {
    searchResults.innerHTML = '<div class="sr-no-results">No results for "<strong>' + escHtml(q) + '</strong>". Try different keywords or <a href="<?= base_url('contact') ?>">contact support</a>.</div>';
  } else {
    searchResults.innerHTML = hits.map(a =>
      `<a class="sr-item" href="${a.anchor}">
        <span class="sr-item-icon">${a.icon}</span>
        <div><div class="sr-item-title">${highlightMatch(a.title, q)}</div><div class="sr-item-cat">${a.cat}</div></div>
       </a>`
    ).join('');
    searchResults.querySelectorAll('a').forEach(link => link.addEventListener('click', () => closeSearch()));
  }
  searchResults.style.display = 'block';
}

function closeSearch() { searchResults.style.display = 'none'; }

function highlightMatch(text, q) {
  const idx = text.toLowerCase().indexOf(q);
  if (idx < 0) return escHtml(text);
  return escHtml(text.slice(0, idx)) + '<strong>' + escHtml(text.slice(idx, idx + q.length)) + '</strong>' + escHtml(text.slice(idx + q.length));
}

function escHtml(s) { return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function debounce(fn, ms) { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; }
</script>

</body>
</html>
