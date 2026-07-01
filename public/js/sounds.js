/* NavuliMeet — Notification Sounds (Web Audio API, no external files) */

let _soundCtx = null;

function _getSoundCtx() {
  if (!_soundCtx || _soundCtx.state === 'closed') {
    _soundCtx = new (window.AudioContext || window.webkitAudioContext)();
  }
  if (_soundCtx.state === 'suspended') _soundCtx.resume().catch(() => {});
  return _soundCtx;
}

function _tone(freq, startTime, duration, gain, type) {
  if (gain === undefined) gain = 0.22;
  if (type === undefined) type = 'sine';
  const ctx = _getSoundCtx();
  const osc = ctx.createOscillator();
  const env = ctx.createGain();
  osc.type = type;
  osc.frequency.setValueAtTime(freq, startTime);
  env.gain.setValueAtTime(0, startTime);
  env.gain.linearRampToValueAtTime(gain, startTime + 0.01);
  env.gain.linearRampToValueAtTime(0, startTime + duration);
  osc.connect(env);
  env.connect(ctx.destination);
  osc.start(startTime);
  osc.stop(startTime + duration + 0.02);
}

function playSound(type) {
  try {
    const ctx = _getSoundCtx();
    const t = ctx.currentTime;
    switch (type) {

      case 'waiting':           // Someone joins waiting room — two rising tones
        _tone(523, t,        0.14);       // C5
        _tone(659, t + 0.18, 0.2);       // E5
        break;

      case 'peer-join':         // Admitted — ascending arpeggio
        _tone(523, t,        0.1);
        _tone(659, t + 0.13, 0.1);
        _tone(784, t + 0.26, 0.18);
        break;

      case 'peer-left':         // Peer left — soft descending
        _tone(659, t,        0.14);
        _tone(523, t + 0.18, 0.18, 0.15);
        break;

      case 'disconnect':        // Connection lost — low alert
        _tone(440, t,        0.12, 0.25);
        _tone(330, t + 0.18, 0.28, 0.2);
        break;

      case 'reconnecting':      // Reconnecting — pulsing mid tone
        _tone(494, t,        0.1, 0.15);
        _tone(494, t + 0.22, 0.1, 0.15);
        _tone(494, t + 0.44, 0.1, 0.15);
        break;

      case 'reconnected':       // Back online — happy ascending run
        _tone(523, t,        0.09);
        _tone(659, t + 0.11, 0.09);
        _tone(784, t + 0.22, 0.09);
        _tone(1047,t + 0.33, 0.18);
        break;

      case 'chat':              // New chat message — soft high ding
        _tone(1047, t,        0.07, 0.18);
        _tone(1319, t + 0.09, 0.14, 0.12);
        break;

      case 'hand-up':           // Hand raised — ascending double beep
        _tone(698, t,        0.1, 0.22);
        _tone(880, t + 0.15, 0.15, 0.22);
        break;

      case 'hand-down':         // Hand lowered — single descending
        _tone(698, t, 0.15, 0.15);
        break;

      case 'rec-start':         // Recording start — alert pulse
        _tone(880, t,        0.1, 0.28, 'square');
        _tone(880, t + 0.18, 0.1, 0.28, 'square');
        break;

      case 'rec-stop':          // Recording stop — descending
        _tone(880, t,        0.1,  0.2,  'square');
        _tone(698, t + 0.16, 0.18, 0.15, 'square');
        break;
    }
  } catch (_) {}
}
