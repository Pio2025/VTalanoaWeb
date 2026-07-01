/* NavuliMeet — Screen Share */

let screenStream = null;
let originalVideoTrack = null;

async function startScreenShare() {
  // Only one screen share at a time
  if (typeof currentScreenSharerId !== 'undefined' && currentScreenSharerId) {
    const sharerName = (typeof participants !== 'undefined' && participants[currentScreenSharerId]?.displayName) || 'A participant';
    showToast(`${sharerName} is already sharing. Only one screen share at a time.`, 'error');
    return false;
  }

  try {
    screenStream = await navigator.mediaDevices.getDisplayMedia({
      video: { cursor: 'always' },
      audio: false,
    });
  } catch (e) {
    if (e.name !== 'NotAllowedError') {
      showToast('Could not start screen share.', 'error');
    }
    return false;
  }

  const screenTrack = screenStream.getVideoTracks()[0];
  if (!screenTrack) return false;

  // Save original cam track and replace with screen track for all peers
  const ls = getLocalStream();
  if (ls) {
    originalVideoTrack = ls.getVideoTracks()[0] || null;
  }
  replaceVideoTrack(screenTrack);

  // Show screen in local tile
  const localVideo = document.getElementById('localVideo');
  if (localVideo) localVideo.srcObject = screenStream;

  // Show screen share overlay for self
  const area = document.getElementById('screenShareArea');
  const sv   = document.getElementById('screenShareVideo');
  if (area && sv) {
    sv.srcObject = screenStream;
    area.style.display = 'flex';
    document.getElementById('screenSharerName').textContent = 'You (screen)';
    _buildScreenShareThumbs();
  }

  emitSafe('screen-share-start', { displayName: DISPLAY_NAME });

  // Auto-stop when user clicks browser's "Stop sharing"
  screenTrack.onended = () => stopScreenShare();

  return true;
}

function stopScreenShare() {
  if (screenStream) {
    screenStream.getTracks().forEach(t => t.stop());
    screenStream = null;
  }

  // Restore original video track
  if (originalVideoTrack && originalVideoTrack.readyState === 'live') {
    replaceVideoTrack(originalVideoTrack);
    const localVideo = document.getElementById('localVideo');
    const ls = getLocalStream();
    if (localVideo && ls) localVideo.srcObject = ls;
  }
  originalVideoTrack = null;

  // Hide screen share overlay
  const area = document.getElementById('screenShareArea');
  if (area) area.style.display = 'none';

  emitSafe('screen-share-stop', {});
}

function isScreenSharing() {
  return screenStream !== null;
}

function showRemoteScreenShare(sharerName, stream) {
  const area = document.getElementById('screenShareArea');
  const sv   = document.getElementById('screenShareVideo');
  if (!area || !sv) return;
  sv.srcObject = stream;
  area.style.display = 'flex';
  document.getElementById('screenSharerName').textContent = sharerName + ' (screen)';
}

function hideRemoteScreenShare() {
  const area = document.getElementById('screenShareArea');
  if (area) area.style.display = 'none';
}

function _buildScreenShareThumbs() {
  const strip = document.getElementById('thumbsStrip');
  if (!strip) return;
  strip.innerHTML = '';
  if (typeof remotePeers === 'undefined') return;
  Object.entries(remotePeers).forEach(([socketId, peer]) => {
    const name = (typeof participants !== 'undefined' && participants[socketId]?.displayName) || 'Participant';
    const tile = document.createElement('div');
    tile.className = 'video-tile';
    tile.style.cssText = 'flex-shrink:0;width:140px;height:104px;';

    const v = document.createElement('video');
    v.autoplay = true; v.playsinline = true; v.muted = true;
    v.style.cssText = 'width:100%;height:100%;object-fit:cover;';
    v.srcObject = peer.stream || null;
    if (v.srcObject) v.play().catch(() => {});

    const nameEl = document.createElement('div');
    nameEl.className = 'thumb-name';
    nameEl.textContent = name;

    tile.appendChild(v);
    tile.appendChild(nameEl);
    strip.appendChild(tile);
  });
}

// Called from room.js DOMContentLoaded — disables the button on browsers that
// don't support getDisplayMedia (iOS Safari, older Android, some in-app browsers).
function _checkScreenShareSupport() {
  if (navigator.mediaDevices?.getDisplayMedia) return; // supported — nothing to do

  console.log('[Screen] getDisplayMedia not available — disabling screen share button');

  const btn = document.getElementById('btnScreen');
  if (btn) {
    btn.disabled      = true;
    btn.title         = 'Screen sharing is not supported in this browser';
    btn.style.opacity = '0.4';
    btn.style.cursor  = 'not-allowed';
  }
}
