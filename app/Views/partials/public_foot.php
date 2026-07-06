<!-- ── Footer ───────────────────────────────────────────── -->
<footer class="pub-footer">
  <div class="pub-foot">
    <a class="pub-foot-logo" href="<?= base_url('/') ?>" aria-label="VTalanoa home">
      <img src="<?= base_url('img/logo-web.png') ?>" alt="VTalanoa">
    </a>

    <nav class="pub-foot-links" aria-label="Footer navigation">
      <a href="<?= base_url('features') ?>">Features</a>
      <a href="<?= base_url('pricing') ?>">Pricing</a>
      <a href="<?= base_url('download') ?>">Download</a>
      <a href="<?= base_url('support') ?>">Help</a>
      <a href="<?= base_url('contact') ?>">Contact</a>
      <a href="<?= base_url('privacy') ?>">Privacy</a>
      <a href="<?= base_url('terms') ?>">Terms</a>
    </nav>

    <span class="pub-copy">&copy; <?= date('Y') ?> VTalanoa. All rights reserved.</span>
  </div>
</footer>

<!-- ── Mobile nav JS (shared across all public pages) ─── -->
<script>
(function () {
  var hamburger = document.getElementById('pubHamburger');
  var nav       = document.getElementById('pubMobileNav');
  var closeBtn  = document.getElementById('pubMobileNavClose');

  if (!hamburger || !nav) return;

  function openNav() {
    nav.classList.add('open');
    hamburger.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
  }
  function closeNav() {
    nav.classList.remove('open');
    hamburger.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
  }

  hamburger.addEventListener('click', openNav);
  if (closeBtn) closeBtn.addEventListener('click', closeNav);
  nav.addEventListener('click', function (e) { if (e.target === nav) closeNav(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeNav(); });
})();
</script>
