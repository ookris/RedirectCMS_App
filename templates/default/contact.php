<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Kontakt — <?= htmlspecialchars($homeTitle) ?></title>
  <meta name="description" content="Skontaktuj się z nami" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
  <?php echo $themeCss ?? ''; ?>
  <?php if (!empty($homeHeaderCode)) echo $homeHeaderCode; ?>
  <style>
    :root { --transition: .2s ease; }
    body { background: var(--theme-body-bg, #f4f6f9); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }

    /* HEADER */
    .site-header { background: var(--theme-header-bg, #2d3748); color: var(--theme-header-text, #fff); padding: 20px 0; }
    .site-header a { color: inherit; text-decoration: none; }
    .site-logo { height: 44px; width: auto; object-fit: contain; }
    .site-title { font-size: 1.4rem; font-weight: 700; line-height: 1.2; }
    .site-subtitle { font-size: .85rem; opacity: .75; margin-top: 2px; }

    /* NAV */
    .site-nav { background: #fff; border-bottom: 2px solid var(--theme-primary, #4a90d9); position: sticky; top: 0; z-index: 100; box-shadow: 0 1px 6px rgba(0,0,0,.06); }
    .site-nav .nav-link { color: #495057; font-size: .9rem; padding: 10px 14px; font-weight: 500; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: color var(--transition), border-color var(--transition); }
    .site-nav .nav-link:hover { color: var(--theme-primary, #4a90d9); border-bottom-color: var(--theme-primary, #4a90d9); }
    .site-nav .nav-link.active { color: var(--theme-primary, #4a90d9); border-bottom-color: var(--theme-primary, #4a90d9); }

    /* CONTACT CARD */
    .contact-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.08); padding: 40px; }
    .btn-send { background: var(--theme-primary, #7952b3); border-color: var(--theme-primary, #7952b3); color: #fff; padding: 10px 32px; }
    .btn-send:hover { opacity: .9; color: #fff; }

    /* FOOTER */
    .site-footer { background: var(--theme-footer-bg, #2d3748); color: var(--theme-footer-text, #a0aec0); padding: 36px 0 0; margin-top: 56px; }
    .site-footer a { color: var(--theme-footer-text, #a0aec0); text-decoration: none; }
    .site-footer a:hover { color: #fff; }
    .site-footer-social a { opacity: .7; transition: opacity var(--transition); display: inline-block; }
    .site-footer-social a:hover { opacity: 1; }
    .site-footer-copy { font-size: .78rem; border-top: 1px solid rgba(255,255,255,.1); padding: 12px 0; margin-top: 24px; text-align: center; opacity: .55; }
  </style>
</head>
<body>

<!-- HEADER -->
<header class="site-header">
  <div class="container">
    <div class="d-flex align-items-center gap-3">
      <?php if (!empty($brandingLogo)): ?>
        <a href="<?= $basePath ?>/">
          <img src="<?= htmlspecialchars($basePath . '/' . $brandingLogo) ?>" alt="<?= htmlspecialchars($homeTitle) ?>" class="site-logo" />
        </a>
      <?php endif; ?>
      <div>
        <a href="<?= $basePath ?>/" class="site-title d-block"><?= htmlspecialchars($homeTitle) ?></a>
        <?php if (!empty($homeSubtitle)): ?>
          <div class="site-subtitle"><?= htmlspecialchars($homeSubtitle) ?></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</header>

<!-- NAV -->
<?php if (!empty($navPages) || !empty($contactEnabled)): ?>
<nav class="site-nav">
  <div class="container">
    <div class="d-flex flex-wrap">
      <a href="<?= $basePath ?>/" class="nav-link">Strona główna</a>
      <?php foreach ($navPages as $_np): ?>
        <a href="<?= $basePath ?>/?page=<?= htmlspecialchars($_np['slug']) ?>" class="nav-link">
          <?= htmlspecialchars($_np['title']) ?>
        </a>
      <?php endforeach; ?>
      <?php if (!empty($contactEnabled)): ?>
        <a href="<?= $basePath ?>/?page=contact" class="nav-link active">Kontakt</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
<?php endif; ?>

<!-- FORMULARZ -->
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-7">
      <div class="contact-card">

        <h1 class="h3 mb-3">Kontakt</h1>

        <?php if (!empty($contactIntro)): ?>
          <p class="text-muted mb-4"><?= nl2br(htmlspecialchars($contactIntro)) ?></p>
        <?php endif; ?>

        <?php if (!empty($formSuccess)): ?>
          <div class="alert alert-success">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-check-circle me-2" viewBox="0 0 16 16">
              <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
              <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
            </svg>
            <?= htmlspecialchars($formSuccess) ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($formError)): ?>
          <div class="alert alert-danger">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-exclamation-circle me-2" viewBox="0 0 16 16">
              <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
              <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/>
            </svg>
            <?= htmlspecialchars($formError) ?>
          </div>
        <?php endif; ?>

        <?php if (empty($formSuccess)): ?>
        <form method="post" action="<?= $basePath ?>/?page=contact" novalidate>
          <input type="hidden" name="csrf_contact" value="<?= htmlspecialchars($contactCsrf) ?>">

          <div class="mb-3">
            <label for="contact_name" class="form-label">Imię <span class="text-muted small">(opcjonalnie)</span></label>
            <input type="text" class="form-control" id="contact_name" name="name"
                   value="<?= htmlspecialchars($formValues['name'] ?? '') ?>"
                   maxlength="50" placeholder="Twoje imię">
          </div>

          <div class="mb-3">
            <label for="contact_email" class="form-label">Adres e-mail <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="contact_email" name="email"
                   value="<?= htmlspecialchars($formValues['email'] ?? '') ?>"
                   maxlength="254" placeholder="twoj@email.pl" required>
          </div>

          <div class="mb-4">
            <label for="contact_message" class="form-label">Wiadomość <span class="text-danger">*</span></label>
            <textarea class="form-control" id="contact_message" name="message"
                      rows="6" maxlength="1000" required
                      placeholder="Treść wiadomości..."><?= htmlspecialchars($formValues['message'] ?? '') ?></textarea>
            <div class="form-text text-end">
              <span id="charCount">0</span>/1000 znaków
            </div>
          </div>

          <button type="submit" class="btn btn-send">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send me-2" viewBox="0 0 16 16">
              <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576zm6.787-8.201L1.591 6.602l4.339 2.76z"/>
            </svg>
            Wyślij wiadomość
          </button>
        </form>
        <?php endif; ?>

      </div>

      <div class="mt-4">
        <a href="<?= $basePath ?>/" class="btn btn-outline-secondary btn-sm">&larr; Strona główna</a>
      </div>
    </div>
  </div>
</div>

<!-- FOOTER -->
<footer class="site-footer">
  <div class="container">
    <div class="row align-items-start g-4">
      <div class="col-md-6">
        <div class="fw-bold fs-6 mb-1"><?= htmlspecialchars($homeTitle) ?></div>
        <?php if (!empty($homeSubtitle)): ?>
          <div class="small opacity-75 mb-2"><?= htmlspecialchars($homeSubtitle) ?></div>
        <?php endif; ?>
        <?php if (!empty($homeFooter)): ?>
          <div class="small mt-1" style="opacity:.65"><?= $homeFooter ?></div>
        <?php endif; ?>
      </div>
      <?php if (!empty($socialLinks) && is_array($socialLinks)): ?>
      <div class="col-md-6 text-md-end site-footer-social">
        <?php
          $_svgPaths = [
            'facebook'  => 'M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951',
            'instagram' => 'M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.9 3.9 0 0 0-1.417.923A3.9 3.9 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.9 3.9 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.9 3.9 0 0 0-.923-1.417A3.9 3.9 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599s.453.546.598.92c.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.5 2.5 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.5 2.5 0 0 1-.92-.598 2.5 2.5 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233s.008-2.388.046-3.231c.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92s.546-.453.92-.598c.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92m-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217m0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334',
            'twitter'   => 'M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z',
            'youtube'   => 'M8.051 1.999h.089c.822.003 4.987.033 6.11.335a2.01 2.01 0 0 1 1.415 1.42c.101.38.172.883.22 1.402l.01.104.022.26.008.104c.065.914.073 1.77.074 1.957v.075c-.001.194-.01 1.108-.082 2.06l-.008.105-.009.104c-.05.572-.124 1.14-.235 1.558a2.01 2.01 0 0 1-1.415 1.42c-1.16.312-5.569.334-6.18.335h-.142c-.309 0-1.587-.006-2.927-.052l-.17-.006-.087-.004-.171-.007-.171-.007c-1.11-.049-2.167-.128-2.654-.26a2.01 2.01 0 0 1-1.415-1.419c-.111-.417-.185-.986-.235-1.558L.09 9.82l-.008-.104A31 31 0 0 1 0 7.68v-.123c.002-.215.01-.958.064-1.778l.007-.103.003-.052.008-.104.022-.26.01-.104c.048-.519.119-1.023.22-1.402a2.01 2.01 0 0 1 1.415-1.42c.487-.13 1.544-.21 2.654-.26l.17-.007.172-.006.086-.003.171-.007A100 100 0 0 1 7.858 2zM6.4 5.209v4.818l4.157-2.408z',
            'linkedin'  => 'M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854zm4.943 12.248V6.169H2.542v7.225zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248S2.4 3.226 2.4 3.934c0 .694.521 1.248 1.327 1.248zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016l.016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225z',
            'tiktok'    => 'M9 0h1.98c.144.715.54 1.617 1.235 2.512C12.895 3.389 13.797 4 15 4v2c-1.753 0-3.07-.814-4-1.829V11a5 5 0 1 1-5-5v2a3 3 0 1 0 3 3z',
            'pinterest' => 'M8 0a8 8 0 0 0-2.915 15.452c-.07-.633-.134-1.606.027-2.297.146-.625.984-4.171.984-4.171s-.252-.504-.252-1.25c0-1.17.68-2.046 1.524-2.046.72 0 1.068.54 1.068 1.187 0 .723-.461 1.807-.699 2.814-.198.84.42 1.524 1.246 1.524 1.494 0 2.643-1.575 2.643-3.849 0-2.01-1.445-3.415-3.51-3.415-2.388 0-3.788 1.793-3.788 3.645 0 .722.277 1.495.624 1.919a.25.25 0 0 1 .058.239c-.064.264-.205.84-.233.957-.037.15-.124.182-.285.109C3.29 10.566 2.5 9.05 2.5 7.793c0-2.885 2.095-5.536 6.042-5.536 3.17 0 5.633 2.259 5.633 5.276 0 3.147-1.98 5.676-4.733 5.676-.925 0-1.796-.48-2.093-1.046l-.569 2.123c-.206.796-.765 1.79-1.14 2.395A8 8 0 1 0 8 0',
          ];
        ?>
        <?php foreach ($socialLinks as $_net => $_url): ?>
          <?php if (!empty($_url) && isset($_svgPaths[$_net])): ?>
            <a href="<?= htmlspecialchars($_url) ?>" target="_blank" rel="noopener noreferrer"
               class="ms-3" title="<?= htmlspecialchars(ucfirst((string)$_net)) ?>">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                <path d="<?= $_svgPaths[$_net] ?>"/>
              </svg>
            </a>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <div class="site-footer-copy">
      &copy; <?= date('Y') ?> <?= htmlspecialchars($homeTitle) ?>
    </div>
  </div>
</footer>

<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const msgArea   = document.getElementById('contact_message');
  const charCount = document.getElementById('charCount');
  if (msgArea && charCount) {
    const update = () => { charCount.textContent = msgArea.value.length; };
    msgArea.addEventListener('input', update);
    update();
  }
</script>
</body>
</html>
