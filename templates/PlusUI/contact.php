<!doctype html>
<html lang="pl">
<head>
  <script>
  (function(){
    var t=localStorage.getItem('pu-theme')||(window.matchMedia&&window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light');
    if(t==='dark')document.documentElement.setAttribute('data-theme','dark');
  }());
  </script>

  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Kontakt — <?= htmlspecialchars($homeTitle) ?></title>
  <meta name="robots" content="noindex" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet" />
  <?php echo $themeCss ?? ''; ?>
  <?php if (!empty($homeHeaderCode)) echo $homeHeaderCode; ?>
  <style>
    :root {
      --pu-primary:   var(--theme-primary, #1976d2);
      --pu-accent:    var(--theme-accent, #e91e63);
      --pu-text:      var(--theme-text, #08102b);
      --pu-muted:     var(--theme-muted, #989b9f);
      --pu-border:    var(--theme-border, #e8ecf0);
      --pu-bg:        var(--theme-body-bg, #fdfcff);
      --pu-card:      var(--theme-card-bg, #ffffff);
      --pu-header-bg: #ffffff;
      --pu-radius:    8px;
      --pu-shadow:    0 2px 8px rgba(0,0,0,.06);
    }
    [data-theme="dark"] {
      --pu-primary:   var(--theme-primary-d, #8775f5);
      --pu-bg:        var(--theme-dark-bg, #1e1e1e);
      --pu-card:      var(--theme-dark-card, #2a2a2a);
      --pu-text:      #f0eeff;
      --pu-muted:     #9a9a9a;
      --pu-border:    #3a3a3a;
      --pu-header-bg: #242424;
      --pu-shadow:    0 2px 8px rgba(0,0,0,.35);
    }
    *, *::before, *::after { box-sizing: border-box; }
    body {
      margin: 0; padding: 0;
      background: var(--pu-bg); font-family: 'Roboto', system-ui, sans-serif;
      font-size: 15px; color: var(--pu-text);
      -webkit-font-smoothing: antialiased; transition: background .2s, color .2s;
    }
    a { color: inherit; text-decoration: none; }

    /* HEADER */
    .pu-header {
      position: sticky; top: 0; z-index: 200;
      height: 60px; background: var(--pu-header-bg);
      border-bottom: 1px solid var(--pu-border);
      box-shadow: var(--pu-shadow); transition: background .2s, border-color .2s;
    }
    .pu-header-inner {
      max-width: 1280px; margin: 0 auto; padding: 0 20px; height: 100%;
      display: flex; align-items: center; gap: 16px;
    }
    .pu-logo {
      display: flex; align-items: center; gap: 10px;
      font-size: .98rem; font-weight: 700; color: var(--pu-text);
      flex-shrink: 0; transition: color .2s;
    }
    .pu-logo:hover { color: var(--pu-primary); }
    .pu-logo img { max-height: 32px; width: auto; }
    .pu-nav { flex: 1; display: flex; align-items: center; gap: 2px; overflow: hidden; }
    .pu-nav a {
      font-size: .83rem; font-weight: 500; padding: 5px 10px; border-radius: 6px;
      color: var(--pu-text); white-space: nowrap; transition: background .15s, color .15s;
    }
    .pu-nav a:hover { background: var(--pu-border); }
    .pu-nav a.active { color: var(--pu-primary); }
    .pu-header-actions { display: flex; align-items: center; gap: 4px; flex-shrink: 0; }
    .pu-icon-btn {
      width: 36px; height: 36px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      background: none; border: none; cursor: pointer;
      color: var(--pu-muted); transition: background .15s, color .15s;
    }
    .pu-icon-btn:hover { background: var(--pu-border); color: var(--pu-text); }
    .pu-sun-icon { display: none; }
    [data-theme="dark"] .pu-moon-icon { display: none; }
    [data-theme="dark"] .pu-sun-icon  { display: block; }
    @media (max-width: 767px) { .pu-nav { display: none; } .pu-header-inner { padding: 0 16px; } }

    /* BREADCRUMB */
    .pu-breadcrumb {
      border-bottom: 1px solid var(--pu-border); padding: 10px 0; background: var(--pu-card);
    }
    .pu-breadcrumb-inner {
      max-width: 1280px; margin: 0 auto; padding: 0 20px;
      display: flex; align-items: center; gap: 6px;
      font-size: .75rem; color: var(--pu-muted);
    }
    .pu-breadcrumb-inner a { color: var(--pu-primary); }
    .pu-breadcrumb-inner a:hover { text-decoration: underline; }
    @media (max-width: 767px) { .pu-breadcrumb-inner { padding: 0 16px; } }

    /* CONTACT FORM */
    .pu-container { max-width: 680px; margin: 0 auto; padding: 36px 20px 56px; }
    .pu-contact-card {
      background: var(--pu-card); border-radius: var(--pu-radius);
      box-shadow: var(--pu-shadow); padding: 40px 48px;
      border: 1px solid var(--pu-border);
    }
    .pu-contact-title {
      font-size: 1.7rem; font-weight: 700; margin: 0 0 8px; line-height: 1.2;
    }
    .pu-contact-intro {
      font-size: .88rem; color: var(--pu-muted); line-height: 1.7;
      margin-bottom: 28px; padding-bottom: 20px;
      border-bottom: 1px solid var(--pu-border);
    }

    .pu-form-group { margin-bottom: 20px; }
    .pu-form-label {
      display: block; font-size: .73rem; font-weight: 700;
      letter-spacing: .06em; text-transform: uppercase;
      color: var(--pu-muted); margin-bottom: 6px;
    }
    .pu-form-control {
      display: block; width: 100%; padding: 10px 14px;
      border: 1.5px solid var(--pu-border); border-radius: var(--pu-radius);
      font-family: 'Roboto', sans-serif; font-size: .9rem;
      color: var(--pu-text); background: var(--pu-bg); outline: none;
      transition: border-color .15s, box-shadow .15s;
    }
    .pu-form-control:focus {
      border-color: var(--pu-primary);
      box-shadow: 0 0 0 3px color-mix(in srgb, var(--pu-primary) 18%, transparent);
    }
    .pu-form-control.error { border-color: #e53e3e; }
    textarea.pu-form-control { resize: vertical; min-height: 140px; }

    .pu-char-counter { font-size: .7rem; color: var(--pu-muted); text-align: right; margin-top: 4px; }

    .pu-submit-btn {
      display: block; width: 100%;
      padding: 12px 28px; background: var(--pu-primary); color: #fff;
      border: none; border-radius: var(--pu-radius);
      font-family: 'Roboto', sans-serif; font-size: .9rem; font-weight: 700;
      letter-spacing: .02em; cursor: pointer; transition: opacity .15s, box-shadow .15s;
    }
    .pu-submit-btn:hover { opacity: .88; box-shadow: 0 4px 12px color-mix(in srgb, var(--pu-primary) 40%, transparent); }
    .pu-submit-btn:disabled { opacity: .5; cursor: not-allowed; box-shadow: none; }

    .pu-alert { padding: 12px 16px; border-radius: var(--pu-radius); font-size: .875rem; margin-bottom: 20px; }
    .pu-alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
    .pu-alert-error   { background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; }
    [data-theme="dark"] .pu-alert-success { background: #052e16; color: #6ee7b7; border-color: #065f46; }
    [data-theme="dark"] .pu-alert-error   { background: #2d0a0a; color: #fca5a5; border-color: #7f1d1d; }

    .pu-back-link {
      display: inline-flex; align-items: center; gap: 6px;
      margin-top: 24px; font-size: .84rem; font-weight: 600;
      color: var(--pu-primary); transition: opacity .15s;
    }
    .pu-back-link:hover { opacity: .75; }

    @media (max-width: 767px) {
      .pu-container { padding: 20px 16px 40px; }
      .pu-contact-card { padding: 24px 20px; }
    }

    /* FOOTER */
    .pu-footer {
      background: var(--pu-card); border-top: 1px solid var(--pu-border);
      padding: 40px 0 0; margin-top: 20px;
    }
    .pu-footer-copy {
      max-width: 1280px; margin: 24px auto 0;
      padding: 14px 20px; border-top: 1px solid var(--pu-border);
      display: flex; align-items: center; justify-content: space-between;
      flex-wrap: wrap; gap: 8px; font-size: .74rem; color: var(--pu-muted);
    }
    .pu-footer-copy a { color: var(--pu-primary); }
    .pu-footer-copy a:hover { opacity: .8; }
    @media (max-width: 767px) { .pu-footer-copy { flex-direction: column; padding: 14px 16px; text-align: center; gap: 4px; } }
  </style>
</head>
<body>

<?php
function pu_page_url_ct(string $bp, string $slug): string {
    return htmlspecialchars($bp . '/?page=' . $slug, ENT_QUOTES);
}
?>

<header class="pu-header">
  <div class="pu-header-inner">
    <a href="<?= htmlspecialchars($basePath) ?>/" class="pu-logo">
      <?php if (!empty($brandingLogo)): ?>
        <img src="<?= htmlspecialchars($basePath . '/' . $brandingLogo, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($homeTitle) ?>" />
      <?php endif; ?>
      <span><?= htmlspecialchars($homeTitle) ?></span>
    </a>
    <nav class="pu-nav">
      <a href="<?= htmlspecialchars($basePath) ?>/">Strona główna</a>
      <?php foreach (($navPages ?? []) as $_np): ?>
        <a href="<?= pu_page_url_ct($basePath, $_np['slug']) ?>"><?= htmlspecialchars($_np['title']) ?></a>
      <?php endforeach; ?>
      <a href="<?= pu_page_url_ct($basePath, 'contact') ?>" class="active">Kontakt</a>
    </nav>
    <div class="pu-header-actions">
      <button class="pu-icon-btn" id="puDarkToggle" aria-label="Tryb ciemny">
        <svg class="pu-moon-icon" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        <svg class="pu-sun-icon"  width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
      </button>
    </div>
  </div>
</header>

<div class="pu-breadcrumb">
  <div class="pu-breadcrumb-inner">
    <a href="<?= htmlspecialchars($basePath) ?>/">Strona główna</a>
    <span>›</span>
    <span>Kontakt</span>
  </div>
</div>

<div class="pu-container">
  <div class="pu-contact-card">
    <h1 class="pu-contact-title">Kontakt</h1>
    <?php if (!empty($contactIntro)): ?>
      <p class="pu-contact-intro"><?= htmlspecialchars($contactIntro) ?></p>
    <?php else: ?>
      <p class="pu-contact-intro">Masz pytania lub chcesz się z nami skontaktować? Wypełnij poniższy formularz.</p>
    <?php endif; ?>

    <?php if (!empty($formSuccess)): ?>
      <div class="pu-alert pu-alert-success"><?= htmlspecialchars($formSuccess) ?></div>
    <?php endif; ?>
    <?php if (!empty($formError)): ?>
      <div class="pu-alert pu-alert-error"><?= htmlspecialchars($formError) ?></div>
    <?php endif; ?>

    <?php if (empty($formSuccess)): ?>
    <form method="post" action="<?= htmlspecialchars($basePath) ?>/?page=contact" novalidate id="puContactForm">
      <input type="hidden" name="csrf_contact" value="<?= htmlspecialchars($contactCsrf) ?>" />

      <div class="pu-form-group">
        <label class="pu-form-label" for="pu_name">Imię i nazwisko</label>
        <input type="text" id="pu_name" name="name" class="pu-form-control"
               placeholder="Jan Kowalski"
               value="<?= htmlspecialchars($formValues['name'] ?? '') ?>"
               maxlength="100" required />
      </div>

      <div class="pu-form-group">
        <label class="pu-form-label" for="pu_email">Adres e-mail</label>
        <input type="email" id="pu_email" name="email" class="pu-form-control"
               placeholder="jan@przykład.pl"
               value="<?= htmlspecialchars($formValues['email'] ?? '') ?>"
               maxlength="200" required />
      </div>

      <div class="pu-form-group">
        <label class="pu-form-label" for="pu_message">Wiadomość</label>
        <textarea id="pu_message" name="message" class="pu-form-control"
                  placeholder="Treść wiadomości…"
                  maxlength="2000" required><?= htmlspecialchars($formValues['message'] ?? '') ?></textarea>
        <div class="pu-char-counter"><span id="puMsgCount">0</span> / 2000</div>
      </div>

      <button type="submit" class="pu-submit-btn" id="puSubmitBtn">Wyślij wiadomość</button>
    </form>
    <?php endif; ?>

    <a href="<?= htmlspecialchars($basePath) ?>/" class="pu-back-link">← Wróć do strony głównej</a>
  </div>
</div>

<footer class="pu-footer">
  <div class="pu-footer-copy">
    <span>&copy; <?= date('Y') ?> <?= htmlspecialchars($homeTitle) ?>. Wszystkie prawa zastrzeżone.</span>
    <span>
      Powered by <a href="https://redirectcms.pl" target="_blank" rel="noopener noreferrer">RedirectCMS</a>
      &middot; Theme: <a href="https://github.com/blogger-templates/Plus-UI-V3.7.0" target="_blank" rel="noopener noreferrer">Plus UI</a>
    </span>
  </div>
</footer>

<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>
<script>
(function () {
  document.getElementById('puDarkToggle').addEventListener('click', function () {
    var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('pu-theme', next);
  });

  var msg  = document.getElementById('pu_message');
  var cnt  = document.getElementById('puMsgCount');
  var form = document.getElementById('puContactForm');
  var btn  = document.getElementById('puSubmitBtn');

  if (msg && cnt) {
    msg.addEventListener('input', function () { cnt.textContent = msg.value.length; });
  }
  if (form && btn) {
    form.addEventListener('submit', function () { btn.disabled = true; btn.textContent = 'Wysyłanie…'; });
  }
}());
</script>
</body>
</html>
