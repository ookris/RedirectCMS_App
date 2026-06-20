<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Kontakt | <?= htmlspecialchars($homeTitle) ?></title>
  <meta name="robots" content="noindex" />

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Abril+Fatface&family=PT+Sans:wght@400;700&display=swap" rel="stylesheet">
  <?php echo $themeCss ?? ''; ?>
  <?php if (!empty($homeHeaderCode)) echo $homeHeaderCode; ?>

  <style>
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; }
    html { font-family: "PT Sans", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.5; }
    @media (min-width: 58em) { html { font-size: 20px; } }
    body { color: var(--theme-text, #515151); background: var(--theme-body_bg, #fff); }

    a { color: var(--theme-primary, #268bd2); text-decoration: none; }
    a:hover, a:focus { text-decoration: underline; }

    .sidebar {
      text-align: center;
      padding: 2rem 1rem;
      color: rgba(255,255,255,.72);
      background: var(--theme-header_bg, #202020);
    }
    .sidebar a { color: var(--theme-header-text, #fff); }
    .sidebar-about h1 {
      margin: 0 0 .35rem;
      font-family: "Abril Fatface", serif;
      font-size: 2.35rem;
      line-height: 1.08;
      color: var(--theme-header-text, #fff);
    }
    .sidebar-about .lead { margin: 0 0 1rem; font-size: 0.95rem; }
    .sidebar-nav { margin: 0 0 1.2rem; }
    .sidebar-nav-item { display: block; line-height: 1.75; }
    .sidebar-nav-item.active { font-weight: 700; }

    .content {
      padding: 2rem 1.2rem 3rem;
      max-width: 44rem;
      margin: 0 auto;
    }
    .form-box { border: 1px solid #ececec; padding: 1rem; }
    .form-title { margin: 0 0 .6rem; font-size: 1.6rem; color: #313131; }
    .field { margin-bottom: .7rem; }
    label { display: block; font-size: .8rem; margin-bottom: .2rem; color: #666; }
    input, textarea {
      width: 100%;
      border: 1px solid #ddd;
      padding: .55rem .65rem;
      font: inherit;
      font-size: .85rem;
    }
    textarea { resize: vertical; min-height: 130px; }
    .btn {
      display: inline-block;
      border: 1px solid var(--theme-primary, #268bd2);
      background: var(--theme-primary, #268bd2);
      color: #fff;
      padding: .5rem .85rem;
      font-size: .8rem;
      cursor: pointer;
    }
    .alert { border: 1px solid #ddd; padding: .6rem .7rem; margin-bottom: .7rem; font-size: .82rem; }
    .alert-success { border-color: #9fd5a4; background: #eefaf0; }
    .alert-error { border-color: #f2abab; background: #fff1f1; }
    .sidebar-foot { margin-top: 1.2rem; font-size: .8rem; }

    @media (min-width: 48em) {
      .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        width: 18rem;
        text-align: left;
      }
      .sidebar-sticky {
        position: absolute;
        right: 1rem;
        bottom: 1rem;
        left: 1rem;
      }
      .content {
        margin-left: 20rem;
        margin-right: 2rem;
        max-width: 38rem;
        padding-top: 4rem;
      }
    }
    @media (min-width: 64em) {
      .content { margin-left: 22rem; margin-right: 4rem; }
    }
  </style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-sticky">
    <div class="sidebar-about">
      <h1><a href="<?= htmlspecialchars($basePath) ?>/"><?= htmlspecialchars($homeTitle) ?></a></h1>
      <?php if (!empty($homeSubtitle)): ?><p class="lead"><?= htmlspecialchars($homeSubtitle) ?></p><?php endif; ?>
    </div>

    <nav class="sidebar-nav">
      <a class="sidebar-nav-item" href="<?= htmlspecialchars($basePath) ?>/">Start</a>
      <?php foreach ($navPages as $_np): ?>
        <a class="sidebar-nav-item" href="<?= htmlspecialchars($basePath . '/?page=' . rawurlencode((string)$_np['slug'])) ?>"><?= htmlspecialchars((string)$_np['title']) ?></a>
      <?php endforeach; ?>
      <a class="sidebar-nav-item active" href="<?= htmlspecialchars($basePath) ?>/?page=contact">Kontakt</a>
    </nav>

    <p class="sidebar-foot"><?= $homeFooter ?: ('&copy; ' . date('Y') . ' ' . htmlspecialchars($homeTitle)) ?></p>
  </div>
</aside>

<main class="content">
  <section class="form-box">
    <h1 class="form-title">Kontakt</h1>
    <?php if (!empty($contactIntro)): ?><p><?= nl2br(htmlspecialchars((string)$contactIntro)) ?></p><?php endif; ?>

    <?php if (!empty($formSuccess)): ?><div class="alert alert-success"><?= htmlspecialchars((string)$formSuccess) ?></div><?php endif; ?>
    <?php if (!empty($formError)): ?><div class="alert alert-error"><?= htmlspecialchars((string)$formError) ?></div><?php endif; ?>

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

        <button class="btn" type="submit">Wyślij wiadomość</button>
      </form>
    <?php endif; ?>
  </section>
</main>

<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>
</body>
</html>
