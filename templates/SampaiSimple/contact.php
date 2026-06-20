<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Kontakt | <?= htmlspecialchars($homeTitle) ?></title>
  <meta name="robots" content="noindex" />

  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css?family=Google+Sans:300,400,700" rel="stylesheet" />
  <?php echo $themeCss ?? ''; ?>
  <?php if (!empty($homeHeaderCode)) echo $homeHeaderCode; ?>
  <style>
    body, html { height: 100%; }
    body { background: #e8e8e8; font-family: 'Google Sans', Arial, sans-serif; }
    #fullpage { background: #fff; min-height: 100%; }
    @media (min-width: 768px) { #fullpage { max-width: 1000px; margin: 0 auto; border-left: 1px solid rgba(0,0,0,.23); border-right: 1px solid rgba(0,0,0,.23); box-shadow: 0 0 10px 0 rgba(0,0,0,.24);} }
    .brand-logo { max-height:44px; width:auto; }
    .site-title { font-size:1.45rem; font-weight:700; color:#111; text-decoration:none; }
    .site-subtitle { font-size:.9rem; color:#6c757d; }
    .card-contact { border:1px solid #dee2e6; }
  </style>
</head>
<body>
<section id="fullpage" class="d-flex flex-column">
  <section class="border-bottom py-2 py-md-3">
    <div class="container-fluid d-flex justify-content-between align-items-center">
      <a href="<?= htmlspecialchars($basePath) ?>/">Start</a>
      <a href="<?= htmlspecialchars($basePath) ?>/?page=contact">Kontakt</a>
    </div>
  </section>

  <section class="border-bottom py-3 py-md-4">
    <div class="container-fluid">
      <a class="d-flex align-items-center text-decoration-none" href="<?= htmlspecialchars($basePath) ?>/">
        <?php if (!empty($brandingLogo)): ?><img class="brand-logo mr-3" src="<?= htmlspecialchars($basePath . '/' . ltrim((string)$brandingLogo, '/')) ?>" alt="<?= htmlspecialchars($homeTitle) ?>" /><?php endif; ?>
        <span>
          <span class="site-title d-block"><?= htmlspecialchars($homeTitle) ?></span>
          <?php if (!empty($homeSubtitle)): ?><small class="site-subtitle d-block"><?= htmlspecialchars($homeSubtitle) ?></small><?php endif; ?>
        </span>
      </a>
    </div>
  </section>

  <div class="container-fluid py-3 flex-fill">
    <div class="card card-contact">
      <div class="card-body p-4">
        <h3 class="text-primary mb-3">Kontakt</h3>
        <?php if (!empty($contactIntro)): ?><p class="text-muted"><?= nl2br(htmlspecialchars($contactIntro)) ?></p><?php endif; ?>

        <?php if (!empty($formSuccess)): ?><div class="alert alert-success"><?= htmlspecialchars($formSuccess) ?></div><?php endif; ?>
        <?php if (!empty($formError)): ?><div class="alert alert-danger"><?= htmlspecialchars($formError) ?></div><?php endif; ?>

        <?php if (empty($formSuccess)): ?>
          <form action="<?= htmlspecialchars($basePath) ?>/?page=contact" method="post" novalidate>
            <input type="hidden" name="csrf_contact" value="<?= htmlspecialchars($contactCsrf) ?>" />
            <div class="form-group">
              <label for="name">Imie i nazwisko</label>
              <input id="name" class="form-control" name="name" type="text" maxlength="120" value="<?= htmlspecialchars($formValues['name'] ?? '') ?>" />
            </div>
            <div class="form-group">
              <label for="email">Email</label>
              <input id="email" class="form-control" name="email" type="email" maxlength="200" required value="<?= htmlspecialchars($formValues['email'] ?? '') ?>" />
            </div>
            <div class="form-group">
              <label for="message">Wiadomosc</label>
              <textarea id="message" class="form-control" name="message" rows="6" maxlength="2000" required><?= htmlspecialchars($formValues['message'] ?? '') ?></textarea>
            </div>
            <button class="btn btn-primary" type="submit">Wyslij</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <footer class="border-top py-3 mt-auto">
    <div class="container-fluid small text-muted d-flex justify-content-between flex-wrap">
      <span><?= $homeFooter ?: ('&copy; ' . date('Y') . ' ' . htmlspecialchars($homeTitle)) ?></span>
      <span>Theme: SampaiSimple</span>
    </div>
  </footer>
</section>
<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>
</body>
</html>
