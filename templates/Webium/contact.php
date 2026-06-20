<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Kontakt — <?= htmlspecialchars($homeTitle) ?></title>
  <meta name="robots" content="noindex" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <?php echo $themeCss ?? ''; ?>
  <?php if (!empty($homeHeaderCode)) echo $homeHeaderCode; ?>
  <style>
    *, *::before, *::after { box-sizing: border-box; }

    body {
      margin: 0; padding: 0;
      background: var(--theme-body-bg, #fff);
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      font-size: 16px;
      color: var(--theme-text, #292929);
      -webkit-font-smoothing: antialiased;
    }
    a { color: inherit; text-decoration: none; }

    /* ─── HEADER ─────────────────────────────────────────────── */
    .wb-header {
      position: fixed; top: 0; left: 0; right: 0; z-index: 100;
      min-height: 57px;
      background: var(--theme-primary, #ffc017);
      border-bottom: 1.5px solid #000;
      display: flex; align-items: center;
    }
    .wb-header-inner {
      max-width: 1192px; width: 100%; margin: 0 auto;
      padding: 0 24px;
      display: flex; align-items: center; justify-content: space-between;
      gap: 16px;
    }
    .wb-logo-link {
      display: inline-flex; align-items: center; gap: 10px;
      color: #000; text-decoration: none; flex-shrink: 0;
    }
    .wb-logo-img { max-height: 28px; width: auto; }
    .wb-site-title {
      font-size: 1.1rem; font-weight: 600; color: #000;
      letter-spacing: -.01em; white-space: nowrap;
    }
    .wb-nav {
      display: flex; align-items: center; gap: 20px;
    }
    .wb-nav a {
      font-size: .875rem; font-weight: 500; color: #292929;
      transition: opacity .15s;
    }
    .wb-nav a:hover { opacity: .65; }
    @media (max-width: 767px) {
      .wb-nav { display: none; }
      .wb-header-inner { padding: 0 16px; }
    }

    /* ─── BREADCRUMB ─────────────────────────────────────────── */
    .wb-breadcrumb {
      border-bottom: 1px solid var(--theme-border, #e6e6e6);
      padding: 10px 0;
    }
    .wb-breadcrumb-inner {
      max-width: 1192px; margin: 0 auto; padding: 0 24px;
      display: flex; align-items: center; gap: 6px;
      font-size: .75rem; color: var(--theme-muted, #757575);
    }
    .wb-breadcrumb-inner a { color: var(--theme-accent, #1a8917); }
    .wb-breadcrumb-inner a:hover { text-decoration: underline; }
    .wb-breadcrumb-sep { color: var(--theme-border, #e6e6e6); }
    @media (max-width: 767px) {
      .wb-breadcrumb-inner { padding: 0 16px; }
    }

    /* ─── LAYOUT ─────────────────────────────────────────────── */
    .wb-container {
      max-width: 640px; margin: 0 auto;
      padding: 0 24px;
    }
    .wb-contact-wrap {
      padding-top: 72px;
      padding-bottom: 60px;
    }
    @media (max-width: 767px) {
      .wb-container { padding: 0 16px; }
      .wb-contact-wrap { padding-top: 60px; }
    }

    /* ─── CONTACT FORM ────────────────────────────────────────── */
    .wb-contact-title {
      font-size: 1.75rem; font-weight: 700;
      color: var(--theme-text, #292929);
      margin: 28px 0 8px;
      line-height: 1.2;
    }
    .wb-contact-intro {
      font-size: .9rem; color: var(--theme-muted, #757575);
      line-height: 1.7; margin-bottom: 28px;
      padding-bottom: 20px;
      border-bottom: 1px solid var(--theme-border, #e6e6e6);
    }

    .wb-form-group { margin-bottom: 20px; }
    .wb-form-label {
      display: block;
      font-size: .78rem; font-weight: 600;
      letter-spacing: .04em; text-transform: uppercase;
      color: var(--theme-muted, #757575);
      margin-bottom: 6px;
    }
    .wb-form-control {
      display: block; width: 100%;
      padding: 10px 16px;
      border: 1.5px solid var(--theme-border, #e6e6e6);
      border-radius: 99px; /* pill inputs */
      font-family: 'Inter', sans-serif; font-size: .9rem;
      color: var(--theme-text, #292929);
      background: #fff; outline: none;
      transition: border-color .15s, box-shadow .15s;
    }
    .wb-form-control:focus {
      border-color: var(--theme-primary, #ffc017);
      box-shadow: 0 0 0 3px rgba(255,192,23,.18);
    }
    .wb-form-control.error { border-color: #e53e3e; }
    textarea.wb-form-control {
      border-radius: 12px; /* pill doesn't fit textarea, use rounded rect */
      resize: vertical; min-height: 140px;
    }

    .wb-char-counter {
      font-size: .7rem; color: var(--theme-muted, #757575);
      text-align: right; margin-top: 4px;
    }

    .wb-submit-btn {
      display: block; width: 100%;
      padding: 12px 28px;
      background: var(--theme-accent, #1a8917); color: #fff;
      border: none; border-radius: 99px;
      font-family: 'Inter', sans-serif; font-size: .9rem; font-weight: 600;
      letter-spacing: .01em; cursor: pointer;
      transition: opacity .15s;
    }
    .wb-submit-btn:hover { opacity: .85; }
    .wb-submit-btn:disabled { opacity: .5; cursor: not-allowed; }

    .wb-alert {
      padding: 12px 18px; border-radius: 8px;
      font-size: .875rem; margin-bottom: 20px;
    }
    .wb-alert-success {
      background: #f0fdf4; color: #166534;
      border: 1px solid #bbf7d0;
    }
    .wb-alert-error {
      background: #fef2f2; color: #991b1b;
      border: 1px solid #fca5a5;
    }

    .wb-back-link {
      display: inline-flex; align-items: center; gap: 6px;
      font-size: .82rem; font-weight: 600;
      color: var(--theme-accent, #1a8917);
      margin-top: 28px;
      text-decoration: none;
      border-bottom: 1px solid transparent;
      transition: border-color .15s;
    }
    .wb-back-link:hover { border-bottom-color: var(--theme-accent, #1a8917); }

    /* ─── FOOTER ─────────────────────────────────────────────── */
    .wb-footer {
      border-top: 1px solid var(--theme-border, #e6e6e6);
      padding: 24px 0;
    }
    .wb-footer-inner {
      max-width: 1192px; margin: 0 auto; padding: 0 24px;
      display: flex; align-items: center; justify-content: space-between;
      flex-wrap: wrap; gap: 12px;
    }
    .wb-footer-copy {
      font-size: .75rem; color: var(--theme-muted, #757575);
    }
    .wb-footer-copy a {
      color: var(--theme-accent, #1a8917);
      transition: opacity .15s;
    }
    .wb-footer-copy a:hover { opacity: .75; }
    .wb-footer-links {
      display: flex; gap: 16px;
    }
    .wb-footer-links a {
      font-size: .75rem; color: var(--theme-muted, #757575);
      transition: color .15s;
    }
    .wb-footer-links a:hover { color: var(--theme-text, #292929); }
    @media (max-width: 767px) {
      .wb-footer-inner { flex-direction: column; align-items: flex-start; gap: 8px; padding: 0 16px; }
      .wb-footer { padding: 20px 0; }
    }
  </style>
</head>
<body>

<?php
function wb_page_url_c(string $bp, string $slug): string {
    return htmlspecialchars($bp . '/?page=' . $slug, ENT_QUOTES);
}
?>

<!-- HEADER -->
<header class="wb-header">
  <div class="wb-header-inner">
    <a href="<?= htmlspecialchars($basePath) ?>/" class="wb-logo-link">
      <?php if (!empty($brandingLogo)): ?>
        <img src="<?= htmlspecialchars($basePath . '/' . $brandingLogo, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($homeTitle) ?>" class="wb-logo-img" />
      <?php endif; ?>
      <span class="wb-site-title"><?= htmlspecialchars($homeTitle) ?></span>
    </a>
    <nav class="wb-nav">
      <a href="<?= htmlspecialchars($basePath) ?>/">Strona główna</a>
      <?php foreach (($navPages ?? []) as $_np): ?>
        <a href="<?= wb_page_url_c($basePath, $_np['slug']) ?>"><?= htmlspecialchars($_np['title']) ?></a>
      <?php endforeach; ?>
      <a href="<?= wb_page_url_c($basePath, 'contact') ?>" style="font-weight:700">Kontakt</a>
    </nav>
  </div>
</header>

<!-- BREADCRUMB -->
<div class="wb-breadcrumb">
  <div class="wb-breadcrumb-inner">
    <a href="<?= htmlspecialchars($basePath) ?>/">Strona główna</a>
    <span class="wb-breadcrumb-sep">›</span>
    <span>Kontakt</span>
  </div>
</div>

<!-- CONTACT CONTENT -->
<div class="wb-contact-wrap">
  <div class="wb-container">
    <h1 class="wb-contact-title">Kontakt</h1>
    <?php if (!empty($contactIntro)): ?>
      <p class="wb-contact-intro"><?= htmlspecialchars($contactIntro) ?></p>
    <?php else: ?>
      <p class="wb-contact-intro">Masz pytania lub chcesz się skontaktować? Wypełnij poniższy formularz.</p>
    <?php endif; ?>

    <?php if (!empty($formSuccess)): ?>
      <div class="wb-alert wb-alert-success">
        <?= htmlspecialchars($formSuccess) ?>
      </div>
    <?php endif; ?>
    <?php if (!empty($formError)): ?>
      <div class="wb-alert wb-alert-error">
        <?= htmlspecialchars($formError) ?>
      </div>
    <?php endif; ?>

    <?php if (empty($formSuccess)): ?>
    <form method="post" action="<?= htmlspecialchars($basePath) ?>/?page=contact" novalidate id="wbContactForm">
      <input type="hidden" name="csrf_contact" value="<?= htmlspecialchars($contactCsrf) ?>" />

      <div class="wb-form-group">
        <label class="wb-form-label" for="wb_name">Imię i nazwisko</label>
        <input type="text" id="wb_name" name="name" class="wb-form-control"
               placeholder="Jan Kowalski"
               value="<?= htmlspecialchars($formValues['name'] ?? '') ?>"
               maxlength="100" required />
      </div>

      <div class="wb-form-group">
        <label class="wb-form-label" for="wb_email">Adres e-mail</label>
        <input type="email" id="wb_email" name="email" class="wb-form-control"
               placeholder="jan@przykład.pl"
               value="<?= htmlspecialchars($formValues['email'] ?? '') ?>"
               maxlength="200" required />
      </div>

      <div class="wb-form-group">
        <label class="wb-form-label" for="wb_message">Wiadomość</label>
        <textarea id="wb_message" name="message" class="wb-form-control"
                  placeholder="Treść wiadomości…"
                  maxlength="2000" required><?= htmlspecialchars($formValues['message'] ?? '') ?></textarea>
        <div class="wb-char-counter"><span id="wbMsgCount">0</span> / 2000</div>
      </div>

      <button type="submit" class="wb-submit-btn" id="wbSubmitBtn">
        Wyślij wiadomość
      </button>
    </form>
    <?php endif; ?>

    <a href="<?= htmlspecialchars($basePath) ?>/" class="wb-back-link">
      ← Wróć do strony głównej
    </a>
  </div>
</div>

<!-- FOOTER -->
<footer class="wb-footer">
  <div class="wb-footer-inner">
    <div class="wb-footer-copy">
      &copy; <?= date('Y') ?> <?= htmlspecialchars($homeTitle) ?>.
      Powered by <a href="https://redirectcms.pl" target="_blank" rel="noopener noreferrer">RedirectCMS</a>
      &middot; Theme: <a href="https://github.com/elhakimyasya/Webium-Blogger-Theme" target="_blank" rel="noopener noreferrer">Webium</a>
    </div>
    <div class="wb-footer-links">
      <a href="<?= htmlspecialchars($basePath) ?>/">Strona główna</a>
      <?php foreach (array_slice($navPages ?? [], 0, 3) as $_np): ?>
        <a href="<?= wb_page_url_c($basePath, $_np['slug']) ?>"><?= htmlspecialchars($_np['title']) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</footer>

<script>
(function () {
  var msg  = document.getElementById('wb_message');
  var cnt  = document.getElementById('wbMsgCount');
  var form = document.getElementById('wbContactForm');
  var btn  = document.getElementById('wbSubmitBtn');

  if (msg && cnt) {
    msg.addEventListener('input', function () {
      cnt.textContent = msg.value.length;
    });
  }

  if (form && btn) {
    form.addEventListener('submit', function () {
      btn.disabled = true;
      btn.textContent = 'Wysyłanie…';
    });
  }
}());
</script>
<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>
</body>
</html>
