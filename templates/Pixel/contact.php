<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Kontakt — <?= htmlspecialchars($homeTitle) ?></title>
  <meta name="robots" content="noindex" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@300;400;600;700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
  <?php echo $themeCss ?? ''; ?>
  <?php if (!empty($homeHeaderCode)) echo $homeHeaderCode; ?>
  <style>
    *, *::before, *::after { box-sizing: border-box; }
    * { transition: color .2s ease, background-color .2s ease, border-color .2s ease, opacity .2s ease, transform .2s ease; }

    body { background: var(--theme-body-bg, #f7f7f7); font-family: 'Open Sans', sans-serif; font-size: 14px; color: #2e2e2e; }

    .px-topbar { background: var(--theme-topbar-bg, #1a1a2e); color: var(--theme-topbar-text, #ccccdd); font-family: 'Josefin Sans', sans-serif; font-size: 12px; font-weight: 600; padding: 7px 0; }
    .px-topbar a { color: var(--theme-topbar-text, #ccccdd); text-decoration: none; opacity: .75; }
    .px-topbar a:hover { opacity: 1; }

    .px-header { background: var(--theme-header-bg, #ffffff); color: var(--theme-header-text, #2e2e2e); padding: 22px 0; border-bottom: 1px solid #e8e8e8; }
    .px-logo { height: 48px; width: auto; object-fit: contain; }
    .px-site-title { font-family: 'Josefin Sans', sans-serif; font-size: 1.9rem; font-weight: 700; letter-spacing: -.02em; color: var(--theme-header-text, #2e2e2e); text-decoration: none; }
    .px-site-title:hover { color: var(--theme-primary, #2942ee); }
    .px-site-subtitle { font-size: .8rem; color: #8e8e95; margin-top: 3px; font-family: 'Josefin Sans', sans-serif; }

    .px-nav { background: var(--theme-nav-bg, #ffffff); border-bottom: 3px solid var(--theme-primary, #2942ee); position: sticky; top: 0; z-index: 200; box-shadow: 0 2px 8px rgba(0,0,0,.06); }
    .px-nav .nav-link { font-family: 'Josefin Sans', sans-serif; font-size: 13px; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: var(--theme-nav-text, #2e2e2e); padding: 12px 14px; border-bottom: 3px solid transparent; margin-bottom: -3px; }
    .px-nav .nav-link:hover, .px-nav .nav-link.active { color: var(--theme-primary, #2942ee); border-bottom-color: var(--theme-primary, #2942ee); }

    .px-breadcrumb { background: #fff; border-bottom: 1px solid #eee; padding: 10px 0; }
    .px-breadcrumb .breadcrumb-item { font-family: 'Josefin Sans', sans-serif; font-size: .72rem; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; }
    .px-breadcrumb .breadcrumb-item a { color: var(--theme-primary, #2942ee); text-decoration: none; }
    .px-breadcrumb .breadcrumb-item.active { color: #8e8e95; }
    .px-breadcrumb .breadcrumb-item + .breadcrumb-item::before { color: #ccc; }

    .px-contact-box { background: #fff; border-radius: 8px; box-shadow: 0 1px 6px rgba(0,0,0,.07); padding: 40px 48px; max-width: 680px; margin: 0 auto; }
    .px-contact-box h1 { font-family: 'Josefin Sans', sans-serif; font-size: 1.8rem; font-weight: 700; color: #1a1a2a; margin-bottom: 8px; }
    .px-contact-intro { font-size: .88rem; color: #6b6b78; line-height: 1.7; margin-bottom: 28px; padding-bottom: 20px; border-bottom: 1px solid #f0f0f0; }

    .px-form-label {
      font-family: 'Josefin Sans', sans-serif; font-size: .72rem; font-weight: 700;
      letter-spacing: .08em; text-transform: uppercase; color: #555; margin-bottom: 6px; display: block;
    }
    .px-form-control {
      width: 100%; padding: 10px 14px; border: 1.5px solid #e0e0e0; border-radius: 5px;
      font-family: 'Open Sans', sans-serif; font-size: .9rem; color: #2e2e2e;
      background: #fafafa; outline: none;
    }
    .px-form-control:focus { border-color: var(--theme-primary, #2942ee); background: #fff; box-shadow: 0 0 0 3px rgba(41,66,238,.1); }
    .px-form-control.error { border-color: #dc3545; }
    textarea.px-form-control { resize: vertical; min-height: 140px; }

    .px-char-counter { font-size: .7rem; color: #8e8e95; text-align: right; margin-top: 4px; font-family: 'Josefin Sans', sans-serif; }

    .px-submit-btn {
      background: var(--theme-primary, #2942ee); color: #fff;
      border: none; border-radius: 5px; padding: 12px 28px;
      font-family: 'Josefin Sans', sans-serif; font-size: .82rem; font-weight: 700;
      letter-spacing: .06em; text-transform: uppercase; cursor: pointer; width: 100%;
    }
    .px-submit-btn:hover { opacity: .88; }
    .px-submit-btn:disabled { opacity: .55; cursor: not-allowed; }

    .px-alert-success { background: #ecfdf5; color: #065f46; border: 1.5px solid #6ee7b7; border-radius: 6px; padding: 14px 18px; font-size: .88rem; margin-bottom: 20px; }
    .px-alert-error   { background: #fef2f2; color: #991b1b; border: 1.5px solid #fca5a5; border-radius: 6px; padding: 14px 18px; font-size: .88rem; margin-bottom: 20px; }

    @media (max-width: 576px) { .px-contact-box { padding: 22px 18px; } }

    .px-footer { background: var(--theme-footer-bg, #1a1a2e); color: var(--theme-footer-text, #9999aa); margin-top: 60px; padding: 40px 0 0; }
    .px-footer h5 { font-family: 'Josefin Sans', sans-serif; font-size: .8rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: #fff; margin-bottom: 14px; }
    .px-footer a { color: var(--theme-footer-text, #9999aa); text-decoration: none; }
    .px-footer a:hover { color: #fff; }
    .px-footer-copy { font-size: .72rem; border-top: 1px solid rgba(255,255,255,.08); padding: 14px 0; margin-top: 28px; text-align: center; opacity: .55; }
  </style>
</head>
<body>

<?php
function px_page_url_c(string $basePath, string $slug): string {
    return htmlspecialchars($basePath . '/?page=' . $slug, ENT_QUOTES);
}
?>

<!-- TOPBAR -->
<div class="px-topbar">
  <div class="container d-flex justify-content-between align-items-center">
    <a href="<?= htmlspecialchars($basePath) ?>/" style="font-size:11px;letter-spacing:.06em;text-transform:uppercase">← Strona główna</a>
  </div>
</div>

<!-- HEADER -->
<header class="px-header">
  <div class="container">
    <a href="<?= htmlspecialchars($basePath) ?>/" class="d-flex align-items-center gap-3 text-decoration-none">
      <?php if (!empty($brandingLogo)): ?>
        <img src="<?= htmlspecialchars($basePath . '/' . $brandingLogo, ENT_QUOTES) ?>" alt="<?= htmlspecialchars($homeTitle) ?>" class="px-logo" />
      <?php endif; ?>
      <div>
        <div class="px-site-title"><?= htmlspecialchars($homeTitle) ?></div>
        <?php if (!empty($homeSubtitle)): ?>
          <div class="px-site-subtitle"><?= htmlspecialchars($homeSubtitle) ?></div>
        <?php endif; ?>
      </div>
    </a>
  </div>
</header>

<!-- NAVIGATION -->
<nav class="px-nav">
  <div class="container d-flex align-items-center">
    <a href="<?= htmlspecialchars($basePath) ?>/" class="nav-link">Strona główna</a>
    <?php foreach (($navPages ?? []) as $_np): ?>
      <a href="<?= px_page_url_c($basePath, $_np['slug']) ?>" class="nav-link"><?= htmlspecialchars($_np['title']) ?></a>
    <?php endforeach; ?>
    <a href="<?= px_page_url_c($basePath, 'contact') ?>" class="nav-link active">Kontakt</a>
  </div>
</nav>

<!-- BREADCRUMB -->
<div class="px-breadcrumb">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= htmlspecialchars($basePath) ?>/">Strona główna</a></li>
        <li class="breadcrumb-item active" aria-current="page">Kontakt</li>
      </ol>
    </nav>
  </div>
</div>

<!-- CONTACT FORM -->
<div class="container" style="margin-top:36px; margin-bottom:48px;">
  <div class="px-contact-box">
    <h1>Kontakt</h1>
    <?php if (!empty($contactIntro)): ?>
      <p class="px-contact-intro"><?= htmlspecialchars($contactIntro) ?></p>
    <?php else: ?>
      <p class="px-contact-intro">Masz pytania? Skontaktuj się z nami — odpiszemy najszybciej jak to możliwe.</p>
    <?php endif; ?>

    <!-- Alerts -->
    <?php if (!empty($formSuccess)): ?>
      <div class="px-alert-success" role="alert">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16" style="vertical-align:-2px;margin-right:6px"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
        <?= htmlspecialchars($formSuccess) ?>
      </div>
    <?php endif; ?>
    <?php if (!empty($formError)): ?>
      <div class="px-alert-error" role="alert">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16" style="vertical-align:-2px;margin-right:6px"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/></svg>
        <?= htmlspecialchars($formError) ?>
      </div>
    <?php endif; ?>

    <?php if (empty($formSuccess)): ?>
    <form method="post" action="<?= htmlspecialchars($basePath . '/?page=contact', ENT_QUOTES) ?>" id="pxContactForm" novalidate>
      <input type="hidden" name="csrf_contact" value="<?= htmlspecialchars($contactCsrf) ?>" />

      <div class="mb-4">
        <label for="contact_name" class="px-form-label">Imię i nazwisko <span style="color:#dc3545">*</span></label>
        <input type="text" id="contact_name" name="name" required maxlength="120"
               class="px-form-control"
               value="<?= htmlspecialchars($formValues['name'] ?? '') ?>"
               placeholder="Twoje imię i nazwisko" />
      </div>

      <div class="mb-4">
        <label for="contact_email" class="px-form-label">Adres e-mail <span style="color:#dc3545">*</span></label>
        <input type="email" id="contact_email" name="email" required maxlength="200"
               class="px-form-control"
               value="<?= htmlspecialchars($formValues['email'] ?? '') ?>"
               placeholder="twoj@email.pl" />
      </div>

      <div class="mb-4">
        <label for="contact_message" class="px-form-label">Wiadomość <span style="color:#dc3545">*</span></label>
        <textarea id="contact_message" name="message" required maxlength="2000" rows="6"
                  class="px-form-control"
                  placeholder="Treść wiadomości…"
                  oninput="document.getElementById('pxCharCount').textContent=this.value.length"><?= htmlspecialchars($formValues['message'] ?? '') ?></textarea>
        <div class="px-char-counter"><span id="pxCharCount"><?= mb_strlen($formValues['message'] ?? '') ?></span> / 2000</div>
      </div>

      <button type="submit" class="px-submit-btn" id="pxSubmitBtn">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16" style="vertical-align:-1px;margin-right:6px"><path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576zm6.787-8.201L1.591 6.602l4.339 2.76z"/></svg>
        Wyślij wiadomość
      </button>
    </form>
    <?php endif; ?>

    <div style="margin-top:28px;padding-top:20px;border-top:1px solid #f0f0f0;text-align:center">
      <a href="<?= htmlspecialchars($basePath) ?>/"
         style="font-family:'Josefin Sans',sans-serif;font-size:.78rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--theme-primary,#2942ee);text-decoration:none">
        ← Powrót do strony głównej
      </a>
    </div>
  </div>
</div>

<!-- FOOTER -->
<footer class="px-footer">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-6">
        <h5><?= htmlspecialchars($homeTitle) ?></h5>
        <?php if (!empty($homeSubtitle)): ?>
          <p style="font-size:.82rem;line-height:1.7"><?= htmlspecialchars($homeSubtitle) ?></p>
        <?php endif; ?>
      </div>
      <?php if (!empty($navPages) || !empty($contactEnabled)): ?>
      <div class="col-md-3">
        <h5>Strony</h5>
        <ul style="list-style:none;padding:0;margin:0">
          <li style="margin-bottom:7px"><a href="<?= htmlspecialchars($basePath) ?>/" style="font-size:.8rem">Strona główna</a></li>
          <?php foreach (($navPages ?? []) as $_np): ?>
            <li style="margin-bottom:7px"><a href="<?= px_page_url_c($basePath, $_np['slug']) ?>" style="font-size:.8rem"><?= htmlspecialchars($_np['title']) ?></a></li>
          <?php endforeach; ?>
          <li style="margin-bottom:7px"><a href="<?= px_page_url_c($basePath, 'contact') ?>" style="font-size:.8rem">Kontakt</a></li>
        </ul>
      </div>
      <?php endif; ?>
    </div>
    <?php if (!empty($homeFooter)): ?>
      <div style="margin-top:28px;padding-top:20px;border-top:1px solid rgba(255,255,255,.08);font-size:.78rem"><?= $homeFooter ?></div>
    <?php endif; ?>
    <div class="px-footer-copy">
      &copy; <?= date('Y') ?> <?= htmlspecialchars($homeTitle) ?>. Wszystkie prawa zastrzeżone.
      &nbsp;&middot;&nbsp; Powered by <a href="https://redirectcms.pl" target="_blank" rel="noopener noreferrer" style="opacity:1;color:inherit">RedirectCMS</a>
      &nbsp;&middot;&nbsp; Theme: <a href="https://github.com/puikinsh/Pixel-Blogger-Template" target="_blank" rel="noopener noreferrer" style="opacity:1;color:inherit">Pixel</a>
    </div>
  </div>
</footer>

<script>
(function() {
  var form = document.getElementById('pxContactForm');
  if (!form) return;
  form.addEventListener('submit', function() {
    var btn = document.getElementById('pxSubmitBtn');
    if (btn) { btn.disabled = true; btn.textContent = 'Wysyłanie…'; }
  });
})();
</script>
<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>
</body>
</html>
