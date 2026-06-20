<?php $action = $_GET['action'] ?? ''; $bp = htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>
<?php
$_licState     = $licenseStatus['state'] ?? 'active';
$_isDemoExpired = !empty($licenseStatus['is_demo_expired']);

if ($_licState === 'warning' || $_licState === 'blocked' || $_licState === 'no_key'):
    $_licMsg   = htmlspecialchars($licenseStatus['message'] ?? '', ENT_QUOTES, 'UTF-8');
    $_licClass = $_licState === 'warning' ? 'warning' : 'danger';
    $_licIcon  = $_licState === 'warning' ? '⚠️' : '🔒';
?>
<style>.rcms-lic-banner{position:fixed;top:0;left:0;right:0;z-index:9999;padding:8px 16px;font-size:13px;text-align:center}</style>
<div class="alert alert-<?= $_licClass ?> rcms-lic-banner mb-0 rounded-0 border-0 border-bottom" role="alert">
  <?= $_licIcon ?> <strong><?= $_isDemoExpired ? 'DEMO wygasło:' : 'Licencja:' ?></strong> <?= $_licMsg ?>
  <?php if ($_isDemoExpired): ?>
    <?php if (defined('RCMS_SHOP_URL') && RCMS_SHOP_URL !== ''): ?>
    &nbsp;— <a href="<?= htmlspecialchars(RCMS_SHOP_URL . '/buy.php', ENT_QUOTES) ?>" class="alert-link fw-bold" target="_blank" rel="noopener">Kup licencję →</a>
    <?php endif; ?>
  <?php elseif ($_licState === 'no_key'): ?>
    — <a href="<?= $bp ?>/admin/index.php?action=settings&amp;tab=license" class="alert-link">Jak zainstalować klucz?</a>
  <?php else: ?>
    — <a href="<?= $bp ?>/admin/index.php?action=settings&amp;tab=license" class="alert-link">Szczegóły</a>
  <?php endif; ?>
</div>
<?php endif; ?>
<?php if ($_licState === 'demo_active'):
    $_demoMsg      = htmlspecialchars($licenseStatus['message'] ?? '', ENT_QUOTES, 'UTF-8');
    $_demoDaysLeft = (int)($licenseStatus['demo_days_left'] ?? 0);
    $_dBannerClass = $_demoDaysLeft <= 3 ? 'warning' : 'info';
    $_shopBuyUrl   = defined('RCMS_SHOP_URL') ? RCMS_SHOP_URL . '/buy.php' : '';
?>
<style>.rcms-demo-banner{position:fixed;top:0;left:0;right:0;z-index:9999;padding:8px 16px;font-size:13px;text-align:center}</style>
<div class="alert alert-<?= $_dBannerClass ?> rcms-demo-banner mb-0 rounded-0 border-0 border-bottom" role="alert">
  🎯 <strong>Wersja DEMO</strong> — <?= $_demoMsg ?>
  <?php if ($_shopBuyUrl !== ''): ?>
    &nbsp;&nbsp;<a href="<?= htmlspecialchars($_shopBuyUrl, ENT_QUOTES) ?>" class="alert-link fw-semibold" target="_blank" rel="noopener">Kup pełną licencję →</a>
  <?php endif; ?>
</div>
<?php endif; ?>
<script>
(function(){
  var b = document.querySelector('.rcms-lic-banner,.rcms-demo-banner');
  if (!b) return;
  document.documentElement.style.setProperty('--rcms-banner-h', b.offsetHeight + 'px');
}());
<?php
$_isBlocked = ($_licState === 'blocked' || $_isDemoExpired);
?>
window.rcmsLicenseBlocked = <?= $_isBlocked ? 'true' : 'false' ?>;
window.rcmsLicenseBlockMsg = <?= json_encode($licenseStatus['message'] ?? 'Funkcja zablokowana z powodu nieważnej licencji.', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<!-- ===== SIDEBAR ===== -->
<div id="sb-sidebar">
  <div class="sb-brand">
    <a href="<?= $bp ?>/admin/index.php" class="text-decoration-none d-flex align-items-center gap-2">
      <img src="<?= $bp ?>/admin/static/img/logo_ikona_full.png" alt="RedirectCMS" class="sb-brand-logo" />
      <span class="fw-semibold text-white" style="font-size:1.05rem;">RedirectCMS</span>
    </a>
  </div>
  <nav class="sb-nav">

    <!-- Dashboard -->
    <a href="<?= $bp ?>/admin/index.php" class="nav-link<?= (empty($action) || $action === 'dashboard') ? ' active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M8 2a.5.5 0 0 1 .5.5V4a.5.5 0 0 1-1 0V2.5A.5.5 0 0 1 8 2M3.732 3.732a.5.5 0 0 1 .707 0l.915.914a.5.5 0 1 1-.708.708l-.914-.915a.5.5 0 0 1 0-.707M2 8a.5.5 0 0 1 .5-.5h1.586a.5.5 0 0 1 0 1H2.5A.5.5 0 0 1 2 8m9.5 0a.5.5 0 0 1 .5-.5h1.5a.5.5 0 0 1 0 1H12a.5.5 0 0 1-.5-.5m.754-4.246a.39.39 0 0 0-.527-.02L7.547 7.31A.91.91 0 1 0 8.85 8.569l3.434-4.297a.39.39 0 0 0-.029-.518z"/><path fill-rule="evenodd" d="M6.664 15.889A8 8 0 1 1 9.336.11a8 8 0 0 1-2.672 15.78zm-4.665-4.283A11.95 11.95 0 0 1 8 10c2.186 0 4.236.585 6.001 1.606a7 7 0 1 0-12.002 0"/></svg>
      Dashboard
    </a>

    <!-- LINKI -->
    <small class="sidebar-section-label">Linki</small>
    <a href="<?= $bp ?>/admin/index.php?action=links" class="nav-link<?= $action === 'links' ? ' active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M4.715 6.542 3.343 7.914a3 3 0 1 0 4.243 4.243l1.828-1.829A3 3 0 0 0 8.586 5.5L8 6.086a1 1 0 0 0-.154.199 2 2 0 0 1 .861 3.337L6.88 11.45a2 2 0 1 1-2.83-2.83l.793-.792a4 4 0 0 1-.128-1.287z"/><path d="M6.586 4.672A3 3 0 0 0 7.414 9.5l.775-.776a2 2 0 0 1-.896-3.346L9.12 3.55a2 2 0 1 1 2.83 2.83l-.793.792c.112.42.155.855.128 1.287l1.372-1.372a3 3 0 1 0-4.243-4.243z"/></svg>
      Linki
    </a>
    <a href="<?= $bp ?>/admin/index.php?action=new" class="nav-link<?= $action === 'new' ? ' active' : '' ?>"<?= $_isBlocked ? ' data-license-block="' . htmlspecialchars((string)($licenseStatus['message'] ?? 'Dodawanie linków jest zablokowane z powodu nieważnej licencji.'), ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z"/><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/></svg>
      Nowy link
    </a>
    <a href="<?= $bp ?>/admin/index.php?action=import_links" class="nav-link<?= $action === 'import_links' ? ' active' : '' ?>"<?= $_isBlocked ? ' data-license-block="' . htmlspecialchars((string)($licenseStatus['message'] ?? 'Import linków jest zablokowany z powodu nieważnej licencji.'), ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/></svg>
      Import CSV
    </a>
    <a href="<?= $bp ?>/admin/index.php?action=broken_links" class="nav-link<?= $action === 'broken_links' ? ' active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M10.146.146a.5.5 0 0 1 .708 0l5 5a.5.5 0 0 1 0 .708l-5 5a.5.5 0 0 1-.708-.708L14.293 5.5 10.146 1.354a.5.5 0 0 1 0-.708M5.854 15.854a.5.5 0 0 1-.708 0l-5-5a.5.5 0 0 1 0-.708l5-5a.5.5 0 0 1 .708.708L1.707 10.5l4.147 4.146a.5.5 0 0 1 0 .708M8 1a.5.5 0 0 1 .5.5v13a.5.5 0 0 1-1 0v-13A.5.5 0 0 1 8 1"/></svg>
      Sprawdzanie linków
    </a>

    <!-- TREŚCI -->
    <small class="sidebar-section-label">Treści</small>
    <a href="<?= $bp ?>/admin/index.php?action=pages" class="nav-link<?= in_array($action, ['pages', 'page_new', 'page_edit']) ? ' active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/><path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 0 1-1z"/></svg>
      Strony
    </a>
    <a href="<?= $bp ?>/admin/index.php?action=categories" class="nav-link<?= in_array($action, ['categories', 'category_new', 'category_edit']) ? ' active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="m.5 3 .04.87a2 2 0 0 0-.342 1.311l.637 7A2 2 0 0 0 2.826 14H9v-1H2.826a1 1 0 0 1-.995-.91l-.637-7A1 1 0 0 1 2.19 4h11.62a1 1 0 0 1 .996 1.09L14.54 8h1.005l.256-2.819A2 2 0 0 0 13.81 3H9.828a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 6.172 1H2.5a2 2 0 0 0-2 2m5.672-1a1 1 0 0 1 .707.293L7.586 3H2.19q-.362.002-.683.12L1.5 2.98a1 1 0 0 1 1-.98z"/><path d="M15.854 10.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.707 0l-1.5-1.5a.5.5 0 0 1 .707-.708l1.146 1.147 2.646-2.647a.5.5 0 0 1 .708 0"/></svg>
      Kategorie
    </a>
    <a href="<?= $bp ?>/admin/index.php?action=tags" class="nav-link<?= in_array($action, ['tags', 'tag_new', 'tag_edit']) ? ' active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M3 2v4.586l7 7L14.586 9l-7-7zM2 2a1 1 0 0 1 1-1h4.586a1 1 0 0 1 .707.293l7 7a1 1 0 0 1 0 1.414l-4.586 4.586a1 1 0 0 1-1.414 0l-7-7A1 1 0 0 1 2 6.586z"/><path d="M5.5 5a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1m0 1a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3M1 7.086a1 1 0 0 0 .293.707L8.75 15.25l-.043.043a1 1 0 0 1-1.414 0l-7-7A1 1 0 0 1 0 7.586V3a1 1 0 0 1 1-1z"/></svg>
      Tagi
    </a>

    <!-- MARKETING -->
    <small class="sidebar-section-label">Marketing</small>
    <a href="<?= $bp ?>/admin/index.php?action=campaigns" class="nav-link<?= in_array($action, ['campaigns', 'campaign_new', 'campaign_edit', 'campaign_detail']) ? ' active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5zM2.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5zm6.5.5A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5zM1 10.5A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5zm6.5.5A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5z"/></svg>
      Kampanie
    </a>
    <a href="<?= $bp ?>/admin/index.php?action=affiliate_programs" class="nav-link<?= in_array($action, ['affiliate_programs', 'affiliate_program_new', 'affiliate_program_edit']) ? ' active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M11.354 6.354a.5.5 0 0 0-.708-.708L8 8.293 6.854 7.146a.5.5 0 1 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0z"/><path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1zm3.915 10L3.102 4h10.796l-1.313 7zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0m7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/></svg>
      Programy afiliacyjne
    </a>

    <!-- ANALITYKA -->
    <small class="sidebar-section-label">Analityka</small>
    <a href="<?= $bp ?>/admin/index.php?action=global_stats" class="nav-link<?= in_array($action, ['global_stats', 'global_stats_loading']) ? ' active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M11 2a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v12h.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1 0-1H1v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h1V7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7h1zm1 12h2V2h-2zm-3 0V7H7v7zm-5 0v-3H2v3z"/></svg>
      Statystyki
    </a>

    <!-- SYSTEM -->
    <small class="sidebar-section-label">System</small>
    <a href="<?= $bp ?>/admin/index.php?action=settings" class="nav-link<?= $action === 'settings' ? ' active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492M5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0"/><path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115z"/></svg>
      Ustawienia
    </a>
    <a href="<?= $bp ?>/admin/index.php?action=appearance" class="nav-link<?= $action === 'appearance' ? ' active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M8 5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3m4 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3M5.5 7a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m.5 6a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/><path d="M16 8c0 3.15-1.866 2.585-3.567 2.07C11.42 9.763 10.465 9.473 10 10c-.603.683-.475 1.819-.351 2.92C9.826 14.495 9.996 16 8 16a8 8 0 1 1 8-8m-8 7c.611 0 .654-.171.655-.176.078-.146.124-.464.07-1.119-.014-.168-.037-.37-.061-.591-.052-.464-.112-1.005-.118-1.462-.01-.707.083-1.61.704-2.314.369-.417.845-.578 1.272-.618.404-.038.812.026 1.16.104.343.077.702.186 1.025.284l.028.008c.346.105.658.199.953.266.653.148.904.083.991.024C14.717 9.38 15 9.161 15 8a7 7 0 1 0-7 7"/></svg>
      Wygląd
    </a>
    <a href="<?= $bp ?>/admin/index.php?action=image_optimizer" class="nav-link<?= $action === 'image_optimizer' ? ' active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/><path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1z"/></svg>
      Optymalizacja obrazów
    </a>
    <a href="<?= $bp ?>/admin/index.php?action=backup" class="nav-link<?= $action === 'backup' ? ' active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M6.5 7.5a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v.938l.4 1.599a1 1 0 0 1-.416 1.074l-.93.62a1 1 0 0 1-1.109 0l-.93-.62a1 1 0 0 1-.415-1.074l.4-1.599zm2 0h-1v.938a1 1 0 0 1-.03.243l-.4 1.598.93.62.93-.62-.4-1.598a1 1 0 0 1-.03-.243z"/><path d="M2 2v9.528c0 .223.152.424.37.484l6.13 1.7a.5.5 0 0 0 .267 0l6.13-1.7a.5.5 0 0 0 .37-.484V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1m1 0h10v9.528l-5.63 1.563a.5.5 0 0 1-.267 0L3 11.528z"/></svg>
      Backup
    </a>
    <a href="<?= $bp ?>/admin/index.php?action=cron_jobs" class="nav-link<?= in_array($action, ['cron_jobs', 'cron']) ? ' active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5M3.854 2.146a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708L2 3.293l1.146-1.147a.5.5 0 0 1 .708 0m0 4a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708L2 7.293l1.146-1.147a.5.5 0 0 1 .708 0m0 4a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0"/></svg>
      Cron
    </a>
    <a href="<?= $bp ?>/admin/index.php?action=geo_cache" class="nav-link<?= $action === 'geo_cache' ? ' active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M12.5 9a3.5 3.5 0 1 1 0 7 3.5 3.5 0 0 1 0-7m.354 5.854 1.5-1.5a.5.5 0 0 0-.708-.708l-.646.647V10.5a.5.5 0 0 0-1 0v2.793l-.646-.647a.5.5 0 0 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0"/><path d="M12.096 6.223A5 5 0 0 0 13 5.698V7c0 .289-.213.654-.753 1.007a4.5 4.5 0 0 1 1.753.25V4c0-1.007-.875-1.755-1.904-2.223C11.022 1.289 9.573 1 8 1s-3.022.289-4.096.777C2.875 2.245 2 2.993 2 4v9c0 1.007.875 1.755 1.904 2.223C4.978 15.71 6.427 16 8 16c.536 0 1.058-.034 1.555-.097a4.5 4.5 0 0 1-.813-.927Q8.378 15 8 15c-1.464 0-2.766-.27-3.682-.687C3.356 13.875 3 13.373 3 13v-1.302c.271.202.58.378.904.525C4.978 12.71 6.427 13 8 13h.027a4.6 4.6 0 0 1 0-1H8c-1.464 0-2.766-.27-3.682-.687C3.356 10.875 3 10.373 3 10V8.698c.271.202.58.378.904.525C4.978 9.71 6.427 10 8 10q.393 0 .774-.024a4.5 4.5 0 0 1 1.102-1.132C9.298 8.944 8.666 9 8 9c-1.464 0-2.766-.27-3.682-.687C3.356 7.875 3 7.373 3 7V5.698c.271.202.58.378.904.525C4.978 6.711 6.427 7 8 7s3.022-.289 4.096-.777M3 4c0-.374.356-.875 1.318-1.313C5.234 2.271 6.536 2 8 2s2.766.27 3.682.687C12.644 3.125 13 3.627 13 4c0 .374-.356.875-1.318 1.313C10.766 5.729 9.464 6 8 6s-2.766-.27-3.682-.687C3.356 4.875 3 4.373 3 4"/></svg>
      Cache geo
    </a>
    <a href="<?= $bp ?>/admin/index.php?action=resources" class="nav-link<?= $action === 'resources' ? ' active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M4.5 11a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1M3 10.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0"/><path d="M16 11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V9.51c0-.418.105-.83.305-1.197l2.472-4.531A1.5 1.5 0 0 1 4.094 3h7.812a1.5 1.5 0 0 1 1.317.782l2.472 4.53c.2.368.305.78.305 1.198zM3.655 4.26 1.592 8.043C1.724 8.014 1.86 8 2 8h12c.14 0 .276.014.408.042L12.345 4.26a.5.5 0 0 0-.439-.26H4.094a.5.5 0 0 0-.44.26M1 10v1a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-1a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1"/></svg>
      Zasoby serwera
    </a>

    <!-- NARZĘDZIA -->
    <small class="sidebar-section-label">Narzędzia</small>
    <a href="<?= $bp ?>/admin/index.php?action=logs" class="nav-link<?= $action === 'logs' ? ' active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M5 4a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1zm-.5 2.5A.5.5 0 0 1 5 6h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5M5 8a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1z"/><path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm10-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1"/></svg>
      Logi
    </a>

    <a href="<?= $bp ?>/admin/index.php?action=audit_logs" class="nav-link<?= $action === 'audit_logs' ? ' active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/><path d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0"/></svg>
      Audit Log
    </a>
    <a href="<?= $bp ?>/admin/index.php?action=file_manager" class="nav-link<?= $action === 'file_manager' ? ' active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M.54 3.87.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.826a2 2 0 0 1-1.991-1.819l-.637-7a2 2 0 0 1 .342-1.31zM2.19 4a1 1 0 0 0-.996 1.09l.637 7a1 1 0 0 0 .995.91h10.348a1 1 0 0 0 .995-.91l.637-7A1 1 0 0 0 13.81 4zm4.69-1.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139q.323-.119.684-.12h5.396z"/></svg>
      Menedżer plików
    </a>

    <!-- KONTO -->
    <small class="sidebar-section-label">Konto</small>
    <a href="<?= $bp ?>/admin/index.php?action=notifications" class="nav-link<?= $action === 'notifications' ? ' active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"/></svg>
      Powiadomienia
      <?php if (!empty($unreadNotifications)): ?>
        <span class="badge bg-danger rounded-pill ms-auto"><?= (int)$unreadNotifications ?></span>
      <?php endif; ?>
    </a>
    <a href="<?= $bp ?>/admin/index.php?action=two_factor_setup" class="nav-link<?= $action === 'two_factor_setup' ? ' active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2m3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2"/></svg>
      Bezpieczeństwo (2FA)
    </a>

  </nav>

  <!-- Stopka sidebara -->
  <div class="sb-footer">
    <div class="sb-footer-version">
      <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-box me-1 opacity-75" viewBox="0 0 16 16">
        <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5 8 5.961 14.154 3.5zm3.5 3.6-7.5 2.5v8.384l7.5-2.5zm-8 8.384-7.5-2.5V5.213l7.5 2.5zM7.5.439a1.5 1.5 0 0 1 1 0l7 2.333A.5.5 0 0 1 16 3.25v9.5a.5.5 0 0 1-.342.474l-7.5 2.5a.5.5 0 0 1-.316 0l-7.5-2.5A.5.5 0 0 1 0 12.75v-9.5a.5.5 0 0 1 .342-.474z"/>
      </svg>
      RedirectCMS
      <span class="sb-footer-ver-badge">v<?= htmlspecialchars(defined('RCMS_VERSION') ? RCMS_VERSION : '?') ?></span>
    </div>
    <?php if (!empty($versionInfo['update_available'])): ?>
      <?php if (!empty($versionInfo['changelog_url'])): ?>
    <a href="<?= htmlspecialchars($versionInfo['changelog_url']) ?>" target="_blank" rel="noopener noreferrer" class="sb-footer-update">
      <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" class="bi bi-arrow-up-circle me-1" viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8m15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-7.5 3.5a.5.5 0 0 1-1 0V5.707L5.354 7.854a.5.5 0 1 1-.708-.708l3-3a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 5.707z"/>
      </svg>
      Nowa wersja: v<?= htmlspecialchars($versionInfo['latest_version'] ?? '') ?>
    </a>
      <?php else: ?>
    <span class="sb-footer-update" style="cursor:default;">
      Nowa wersja: v<?= htmlspecialchars($versionInfo['latest_version'] ?? '') ?>
    </span>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<!-- Overlay (mobile) -->
<div id="sb-overlay"></div>

<!-- Content wrapper -->
<div id="sb-content-wrapper">
  <!-- Topbar -->
  <nav id="sb-topbar">
    <button id="sb-toggle" class="btn btn-sm btn-outline-secondary border-0" title="Rozwiń/Zwiń menu" aria-label="Toggle sidebar">
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5"/></svg>
    </button>
    <div class="spacer"></div>
    <a href="<?= $bp ?>/docs/index.html" class="btn btn-sm btn-outline-secondary border-0" title="Dokumentacja" target="_blank">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783"/></svg>
      Pomoc
    </a>
    <?php if (!empty($unreadNotifications)): ?>
      <a href="<?= $bp ?>/admin/index.php?action=notifications" class="btn btn-sm btn-outline-danger border-0 position-relative" title="Powiadomienia">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"/></svg>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem;"><?= (int)$unreadNotifications ?></span>
      </a>
    <?php endif; ?>
    <a href="<?= $bp ?>/admin/index.php?action=logout" class="btn btn-sm btn-danger">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z"/><path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z"/></svg>
      Wyloguj
    </a>
  </nav>
