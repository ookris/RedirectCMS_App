<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Kontakt | <?= htmlspecialchars($homeTitle) ?></title>
  <meta name="robots" content="noindex" />

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=PT+Sans:wght@400;700&display=swap" rel="stylesheet">
  <?php echo $themeCss ?? ''; ?>
  <?php if (!empty($homeHeaderCode)) echo $homeHeaderCode; ?>

  <style>
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; }
    html { font-family: "PT Sans", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.5; }
    body { color: var(--theme-text, #515151); background: var(--theme-body_bg, #fff); }
    a { color: var(--theme-primary, #268bd2); text-decoration: none; }
    a:hover, a:focus { text-decoration: underline; }

    .sidebar { position: fixed; top: 0; bottom: 0; left: -14rem; width: 14rem; visibility: hidden; overflow-y: auto; font-size: .875rem; color: rgba(255,255,255,.6); background: var(--theme-header_bg, #202020); transition: all .3s ease-in-out; z-index: 30; }
    .sidebar a { color: var(--theme-header_text, #fff); }
    .sidebar-item { padding: 1rem; }
    .sidebar-item p:last-child { margin-bottom: 0; }
    .sidebar-nav { border-bottom: 1px solid rgba(255,255,255,.1); }
    .sidebar-nav-item { display: block; padding: .5rem 1rem; border-top: 1px solid rgba(255,255,255,.1); }
    .sidebar-nav-item.active, .sidebar-nav-item:hover, .sidebar-nav-item:focus { text-decoration: none; background: rgba(255,255,255,.1); border-color: transparent; }

    .sidebar-checkbox { display: none; }
    .sidebar-toggle { position: absolute; top: 1rem; left: 1rem; display: block; width: 2.2rem; padding: .5rem .65rem; color: #505050; background: #fff; border-radius: 4px; cursor: pointer; z-index: 31; }
    .sidebar-toggle:before { display: block; content: ""; width: 100%; padding-bottom: .125rem; border-top: .375rem double; border-bottom: .125rem solid; box-sizing: border-box; }

    .wrap, .sidebar, .sidebar-toggle { backface-visibility: hidden; }
    .wrap, .sidebar-toggle { transition: transform .3s ease-in-out; }
    #sidebar-checkbox:checked + .sidebar { visibility: visible; }
    #sidebar-checkbox:checked ~ .sidebar,
    #sidebar-checkbox:checked ~ .wrap,
    #sidebar-checkbox:checked ~ .sidebar-toggle { transform: translateX(14rem); }

    .container { max-width: 38rem; padding-left: 1rem; padding-right: 1rem; margin: 0 auto; }
    .masthead { padding-top: 1rem; padding-bottom: 1rem; margin-bottom: 2rem; }
    .masthead-title { margin: 0; color: #505050; }
    .masthead-title a { color: #505050; }
    .masthead-title small { font-size: 75%; font-weight: 400; color: #c0c0c0; }

    .form-box { border: 1px solid #ececec; padding: 1rem; }
    .form-title { margin: 0 0 .6rem; font-size: 1.6rem; color: #313131; }
    .field { margin-bottom: .7rem; }
    label { display: block; font-size: .8rem; margin-bottom: .2rem; color: #666; }
    input, textarea { width: 100%; border: 1px solid #ddd; padding: .55rem .65rem; font: inherit; font-size: .85rem; }
    textarea { resize: vertical; min-height: 130px; }
    .btn { display: inline-block; border: 1px solid var(--theme-primary, #268bd2); background: var(--theme-primary, #268bd2); color: #fff; padding: .5rem .85rem; font-size: .8rem; cursor: pointer; }
    .alert { border: 1px solid #ddd; padding: .6rem .7rem; margin-bottom: .7rem; font-size: .82rem; }
    .alert-success { border-color: #9fd5a4; background: #eefaf0; }
    .alert-error { border-color: #f2abab; background: #fff1f1; }

    @media (min-width: 30.1rem) { .sidebar-toggle { position: fixed; } }
    @media (min-width: 48rem) {
      .sidebar-item { padding: 1.5rem; }
      .sidebar-nav-item { padding-left: 1.5rem; padding-right: 1.5rem; }
    }
  </style>
</head>
<body>
<input class="sidebar-checkbox" id="sidebar-checkbox" type="checkbox" />

<div class="sidebar" id="sidebar">
  <div class="sidebar-item"><?php if (!empty($homeSubtitle)): ?><p><?= htmlspecialchars($homeSubtitle) ?></p><?php endif; ?></div>
  <nav class="sidebar-nav">
    <a class="sidebar-nav-item" href="<?= htmlspecialchars($basePath) ?>/">Start</a>
    <?php foreach ($navPages as $_np): ?>
      <a class="sidebar-nav-item" href="<?= htmlspecialchars($basePath . '/?page=' . rawurlencode((string)$_np['slug'])) ?>"><?= htmlspecialchars((string)$_np['title']) ?></a>
    <?php endforeach; ?>
    <a class="sidebar-nav-item active" href="<?= htmlspecialchars($basePath) ?>/?page=contact">Kontakt</a>
  </nav>
  <div class="sidebar-item"><p><?= $homeFooter ?: ('&copy; ' . date('Y') . ' ' . htmlspecialchars($homeTitle)) ?></p></div>
</div>

<div class="wrap">
  <div class="masthead">
    <div class="container">
      <h3 class="masthead-title"><a href="<?= htmlspecialchars($basePath) ?>/" title="Start"><?= htmlspecialchars($homeTitle) ?></a><?php if (!empty($homeSubtitle)): ?><small><?= htmlspecialchars($homeSubtitle) ?></small><?php endif; ?></h3>
    </div>
  </div>

  <main class="content container">
    <section class="form-box">
      <h1 class="form-title">Kontakt</h1>
      <?php if (!empty($contactIntro)): ?><p><?= nl2br(htmlspecialchars((string)$contactIntro)) ?></p><?php endif; ?>
      <?php if (!empty($formSuccess)): ?><div class="alert alert-success"><?= htmlspecialchars((string)$formSuccess) ?></div><?php endif; ?>
      <?php if (!empty($formError)): ?><div class="alert alert-error"><?= htmlspecialchars((string)$formError) ?></div><?php endif; ?>

      <?php if (empty($formSuccess)): ?>
        <form action="<?= htmlspecialchars($basePath) ?>/?page=contact" method="post" novalidate>
          <input type="hidden" name="csrf_contact" value="<?= htmlspecialchars((string)$contactCsrf) ?>" />
          <div class="field"><label for="name">Name</label><input id="name" type="text" name="name" maxlength="120" value="<?= htmlspecialchars((string)($formValues['name'] ?? '')) ?>" /></div>
          <div class="field"><label for="email">Email</label><input id="email" type="email" name="email" maxlength="200" required value="<?= htmlspecialchars((string)($formValues['email'] ?? '')) ?>" /></div>
          <div class="field"><label for="message">Message</label><textarea id="message" name="message" maxlength="2000" required><?= htmlspecialchars((string)($formValues['message'] ?? '')) ?></textarea></div>
          <button class="btn" type="submit">Wyślij wiadomość</button>
        </form>
      <?php endif; ?>
    </section>
  </main>
</div>

<label for="sidebar-checkbox" class="sidebar-toggle"></label>
<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>
</body>
</html>
