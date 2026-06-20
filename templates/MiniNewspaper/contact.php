<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Kontakt | <?= htmlspecialchars($homeTitle) ?></title>
  <meta name="robots" content="noindex" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;700&display=swap" rel="stylesheet" />
  <?php echo $themeCss ?? ''; ?>
  <?php if (!empty($homeHeaderCode)) echo $homeHeaderCode; ?>

  <style>
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; }
    body { font-family: "Roboto Slab", Georgia, serif; line-height: 1.5; color: var(--theme-text, #111111); background: var(--theme-body_bg, #fafafa); }
    a { color: #000; text-decoration: none; }
    a:hover { text-decoration: underline; }

    .mn-shell { max-width: 900px; margin: 0 auto; padding: 0 15px; }
    .mn-header { border-top: 5px solid var(--theme-primary, #666666); border-bottom: 4px double var(--theme-primary, #666666); text-align: center; padding: 15px 0 8px; background: var(--theme-header_bg, #ffffff); margin-bottom: 14px; }
    .mn-logo { margin: 0; line-height: 1; font-size: clamp(2rem, 5vw, 3.4rem); }
    .mn-tagline { display: block; margin-top: .55rem; color: #666; font-size: .95rem; }

    .mn-menu { margin-bottom: .9rem; }
    .mn-menu ul { list-style: none; margin: 0; padding: 0; }
    .mn-menu li { display: inline-block; font-weight: 700; margin-right: .3rem; }
    .mn-menu a { display: inline-block; padding: .35rem .55rem; }

    .mn-box { background: #fff; border: 1px solid #eee; padding: 1rem; }
    .mn-box h1 { margin-top: 0; }
    .field { margin-bottom: .7rem; }
    label { display: block; margin: 0 0 .2rem; color: #666; font-size: .9rem; }
    input, textarea { width: 100%; border: 1px solid #ddd; padding: .55rem .65rem; font: inherit; }
    textarea { min-height: 130px; resize: vertical; }
    .mn-btn { border: 1px solid #111; background: #111; color: #fff; padding: .5rem .85rem; cursor: pointer; font: inherit; }
    .mn-alert { border: 1px solid #ddd; padding: .6rem .7rem; margin-bottom: .7rem; }
    .mn-success { border-color: #9fd5a4; background: #eefaf0; }
    .mn-error { border-color: #f2abab; background: #fff1f1; }

    .mn-footer { margin-top: 1rem; background: var(--theme-footer_bg, #666666); color: var(--theme-footer_text, #ffffff); padding: 1rem 0; text-align: center; }
    .mn-copyright { color: #ddd; font-size: .9rem; }
  </style>
</head>
<body>
<header class="mn-header">
  <div class="mn-shell">
    <h1 class="mn-logo"><a href="<?= htmlspecialchars($basePath) ?>/"><?= htmlspecialchars($homeTitle) ?></a></h1>
    <span class="mn-tagline"><?= htmlspecialchars(!empty($homeSubtitle) ? $homeSubtitle : 'mini newspaper template') ?></span>
  </div>
</header>

<div class="mn-shell">
  <nav class="mn-menu">
    <ul>
      <li><a href="<?= htmlspecialchars($basePath) ?>/">Start</a></li>
      <?php foreach ($navPages as $_np): ?>
        <li><a href="<?= htmlspecialchars($basePath . '/?page=' . rawurlencode((string)$_np['slug'])) ?>"><?= htmlspecialchars((string)$_np['title']) ?></a></li>
      <?php endforeach; ?>
      <li><a href="<?= htmlspecialchars($basePath) ?>/?page=contact" style="text-decoration: underline;">Kontakt</a></li>
    </ul>
  </nav>

  <section class="mn-box">
    <h1>Kontakt</h1>
    <?php if (!empty($contactIntro)): ?><p><?= nl2br(htmlspecialchars((string)$contactIntro)) ?></p><?php endif; ?>

    <?php if (!empty($formSuccess)): ?><div class="mn-alert mn-success"><?= htmlspecialchars((string)$formSuccess) ?></div><?php endif; ?>
    <?php if (!empty($formError)): ?><div class="mn-alert mn-error"><?= htmlspecialchars((string)$formError) ?></div><?php endif; ?>

    <?php if (empty($formSuccess)): ?>
      <form action="<?= htmlspecialchars($basePath) ?>/?page=contact" method="post" novalidate>
        <input type="hidden" name="csrf_contact" value="<?= htmlspecialchars((string)$contactCsrf) ?>" />

        <div class="field">
          <label for="name">Name</label>
          <input id="name" type="text" name="name" maxlength="120" value="<?= htmlspecialchars((string)($formValues['name'] ?? '')) ?>" />
        </div>

        <div class="field">
          <label for="email">Email</label>
          <input id="email" type="email" name="email" maxlength="200" required value="<?= htmlspecialchars((string)($formValues['email'] ?? '')) ?>" />
        </div>

        <div class="field">
          <label for="message">Message</label>
          <textarea id="message" name="message" maxlength="2000" required><?= htmlspecialchars((string)($formValues['message'] ?? '')) ?></textarea>
        </div>

        <button class="mn-btn" type="submit">Wyślij wiadomość</button>
      </form>
    <?php endif; ?>
  </section>
</div>

<footer class="mn-footer">
  <div class="mn-shell">
    <div class="mn-copyright"><?= $homeFooter ?: ('&copy; ' . date('Y') . ' ' . htmlspecialchars($homeTitle)) ?></div>
  </div>
</footer>

<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>
</body>
</html>
