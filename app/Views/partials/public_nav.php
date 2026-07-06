<?php
/**
 * Shared public navigation header + mobile overlay.
 * Included by every public (marketing) page.
 * Depends on: public/css/public.css
 */
$_seg = trim(service('uri')->getPath(), '/');
// Normalise: 'auth/login' → 'auth', '' → 'home', 'features' → 'features'
$_page = $_seg === '' ? 'home' : explode('/', $_seg)[0];

function _pub_active(string $check, string $current): string {
    return $check === $current ? ' class="active"' : '';
}
?>
<!-- ── Mobile nav overlay ──────────────────────────────── -->
<div class="pub-mobile-nav" id="pubMobileNav" role="dialog" aria-modal="true" aria-label="Mobile navigation">
  <button class="pub-mobile-nav-close" id="pubMobileNavClose" aria-label="Close navigation menu">&#x2715;</button>
  <a href="<?= base_url('features') ?>"<?= _pub_active('features', $_page) ?>>Features</a>
  <a href="<?= base_url('pricing') ?>"<?= _pub_active('pricing',  $_page) ?>>Pricing</a>
  <a href="<?= base_url('download') ?>"<?= _pub_active('download', $_page) ?>>Download</a>
  <a href="<?= base_url('support') ?>"<?= _pub_active('support',  $_page) ?>>Help</a>
  <div class="pub-mobile-divider"></div>
  <a href="<?= base_url('auth/login') ?>"    class="pub-btn pub-btn-ghost">Sign in</a>
  <a href="<?= base_url('auth/register') ?>" class="pub-btn pub-btn-primary">Sign up free</a>
</div>

<!-- ── Sticky header ──────────────────────────────────── -->
<header class="pub-header">
  <div class="pub-nav">
    <a class="pub-logo" href="<?= base_url('/') ?>" aria-label="VTalanoa home">
      <img src="<?= base_url('img/logo-web.png') ?>" alt="VTalanoa" id="pubSiteLogo">
    </a>

    <nav class="pub-nav-links" aria-label="Main navigation">
      <a href="<?= base_url('features') ?>"<?= _pub_active('features', $_page) ?>>Features</a>
      <a href="<?= base_url('pricing') ?>"<?= _pub_active('pricing',  $_page) ?>>Pricing</a>
      <a href="<?= base_url('download') ?>"<?= _pub_active('download', $_page) ?>>Download</a>
      <a href="<?= base_url('support') ?>"<?= _pub_active('support',  $_page) ?>>Help</a>
    </nav>

    <div class="pub-nav-cta">
      <a href="<?= base_url('auth/login') ?>"    class="pub-btn pub-btn-ghost">Sign in</a>
      <a href="<?= base_url('auth/register') ?>" class="pub-btn pub-btn-primary">Sign up free</a>
    </div>

    <button class="pub-hamburger" id="pubHamburger"
            aria-label="Open navigation menu"
            aria-expanded="false"
            aria-controls="pubMobileNav">
      <svg viewBox="0 0 24 24" aria-hidden="true">
        <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
      </svg>
    </button>
  </div>
</header>
