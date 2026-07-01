/* NavuliMeet — Base App JS */

// Sidebar toggle
(function () {
  const sidebar  = document.getElementById('sidebar');
  const overlay  = document.getElementById('sidebarOverlay');
  const menuBtn  = document.getElementById('menuBtn');

  function openSidebar() {
    sidebar?.classList.add('open');
    overlay?.classList.add('active');
    document.body.style.overflow = 'hidden';
  }
  function closeSidebar() {
    sidebar?.classList.remove('open');
    overlay?.classList.remove('active');
    document.body.style.overflow = '';
  }

  menuBtn?.addEventListener('click', () => {
    sidebar?.classList.contains('open') ? closeSidebar() : openSidebar();
  });
  overlay?.addEventListener('click', closeSidebar);
})();

// Toast notifications
function showToast(message, type = 'default', duration = 3000) {
  const existing = document.querySelector('.nm-toast');
  existing?.remove();

  const toast = document.createElement('div');
  toast.className = `nm-toast${type !== 'default' ? ' ' + type : ''}`;
  toast.textContent = message;
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), duration);
}

// Password toggle
function togglePassword(inputId) {
  const input = document.getElementById(inputId);
  const eye   = document.getElementById(inputId + '-eye');
  if (!input) return;
  input.type = input.type === 'password' ? 'text' : 'password';
  if (eye) eye.className = input.type === 'password' ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
}

// Copy to clipboard
function copyToClipboard(text, message = 'Copied!') {
  navigator.clipboard.writeText(text).then(() => showToast(message, 'success'));
}

// Flash message auto-dismiss
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.alert.auto-dismiss').forEach(function (el) {
    setTimeout(() => {
      const bsAlert = bootstrap?.Alert?.getOrCreateInstance(el);
      bsAlert ? bsAlert.close() : el.remove();
    }, 4000);
  });
});
