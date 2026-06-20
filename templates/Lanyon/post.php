<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($postTitle) ?> | <?= htmlspecialchars($homeTitle) ?></title>
  <meta name="description" content="<?= htmlspecialchars(mb_substr(strip_tags((string)$postDescription), 0, 160)) ?>" />

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=PT+Sans:wght@400;700&display=swap" rel="stylesheet">
  <?php if (!empty($lightboxEnabled)): ?><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" /><?php endif; ?>
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

    .post-title { margin-top: 0; color: #303030; }
    .post-date { display: block; margin-top: -.5rem; margin-bottom: 1rem; color: #9a9a9a; font-size: .9rem; }
    .post-cover { width: 100%; border-radius: 6px; margin: .35rem 0 1rem; border: 1px solid #ececec; }
    .post-body img { max-width: 100%; height: auto; border-radius: 4px; }
    .tag-list a { margin-right: .45rem; white-space: nowrap; }

    .cta-row { margin-top: 1.2rem; display: flex; flex-wrap: wrap; gap: .6rem; }
    .btn { display: inline-block; border: 1px solid #ddd; color: #666; padding: .45rem .75rem; font-size: .78rem; text-decoration: none; }
    .btn-primary { border-color: var(--theme-primary, #268bd2); background: var(--theme-primary, #268bd2); color: #fff; }

    .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: .55rem; margin-top: 1rem; }
    .gallery-grid img { width: 100%; aspect-ratio: 4 / 3; object-fit: cover; border-radius: 4px; }

    .related-wrap { margin-top: 2rem; border-top: 1px solid #ececec; padding-top: 1rem; }
    .related-wrap h3 { margin: 0 0 .6rem; font-size: 1rem; color: #313131; }

    .widgets-wrap { margin-top: 1.5rem; }
    .ly-widget-box { border-top: 1px solid #eee; padding-top: 1rem; margin-top: 1rem; }
    .ly-widget-title { margin: 0 0 .6rem; font-size: 1rem; color: #313131; }
    .ly-list { list-style: none; margin: 0; padding: 0; }
    .ly-list li { margin: 0 0 .35rem; }
    .ly-tags a { margin-right: .45rem; white-space: nowrap; }
    .ly-muted { color: #8a8a8a; }

    @media (min-width: 30.1rem) { .sidebar-toggle { position: fixed; } }
    @media (min-width: 48rem) {
      .sidebar-item { padding: 1.5rem; }
      .sidebar-nav-item { padding-left: 1.5rem; padding-right: 1.5rem; }
    }
  </style>
</head>
<body>
<?php
function ly_post_url(string $bp, string $slug, bool $pretty): string {
    return $pretty ? $bp . '/blog/' . rawurlencode($slug) : $bp . '/?post=' . rawurlencode($slug);
}
function ly_cat_url(string $bp, string $slug, bool $pretty): string {
    return $pretty ? $bp . '/category/' . rawurlencode($slug) : $bp . '/?category=' . rawurlencode($slug);
}
?>

<input class="sidebar-checkbox" id="sidebar-checkbox" type="checkbox" />

<div class="sidebar" id="sidebar">
  <div class="sidebar-item">
    <?php if (!empty($homeSubtitle)): ?><p><?= htmlspecialchars($homeSubtitle) ?></p><?php endif; ?>
  </div>
  <nav class="sidebar-nav">
    <a class="sidebar-nav-item" href="<?= htmlspecialchars($basePath) ?>/">Start</a>
    <?php foreach ($navPages as $_np): ?>
      <a class="sidebar-nav-item" href="<?= htmlspecialchars($basePath . '/?page=' . rawurlencode((string)$_np['slug'])) ?>"><?= htmlspecialchars((string)$_np['title']) ?></a>
    <?php endforeach; ?>
    <?php if (!empty($contactEnabled)): ?><a class="sidebar-nav-item" href="<?= htmlspecialchars($basePath) ?>/?page=contact">Kontakt</a><?php endif; ?>
  </nav>
  <div class="sidebar-item"><p><?= $homeFooter ?: ('&copy; ' . date('Y') . ' ' . htmlspecialchars($homeTitle)) ?></p></div>
</div>

<div class="wrap">
  <div class="masthead">
    <div class="container">
      <h3 class="masthead-title">
        <a href="<?= htmlspecialchars($basePath) ?>/" title="Start"><?= htmlspecialchars($homeTitle) ?></a>
        <?php if (!empty($homeSubtitle)): ?><small><?= htmlspecialchars($homeSubtitle) ?></small><?php endif; ?>
      </h3>
    </div>
  </div>

  <main class="content container">
    <article>
      <h1 class="post-title"><?= htmlspecialchars($postTitle) ?></h1>
      <span class="post-date">
        <?= htmlspecialchars(substr((string)$postCreatedAt, 0, 10)) ?>
        <?php if (!empty($link['category']['slug'])): ?>
          &middot; <a href="<?= htmlspecialchars(ly_cat_url($basePath, (string)$link['category']['slug'], !empty($prettyUrls))) ?>"><?= htmlspecialchars((string)($link['category']['name'] ?? 'Category')) ?></a>
        <?php endif; ?>
      </span>

      <?php if (!empty($ogImageUrl)): ?><img class="post-cover" src="<?= htmlspecialchars($ogImageUrl) ?>" alt="<?= htmlspecialchars($postTitle) ?>" /><?php endif; ?>

      <div class="post-body"><?= Utils::sanitizeHtml((string)$postDescription) ?></div>

      <?php if (!empty($link['tags']) && is_array($link['tags'])): ?>
        <p class="tag-list ly-tags ly-muted">
          <?php foreach ($link['tags'] as $_tag): ?>
            <a href="<?= htmlspecialchars(!empty($prettyUrls) ? $basePath . '/tag/' . rawurlencode((string)$_tag['slug']) : $basePath . '/?tag=' . rawurlencode((string)$_tag['slug'])) ?>">#<?= htmlspecialchars((string)$_tag['name']) ?></a>
          <?php endforeach; ?>
        </p>
      <?php endif; ?>

      <?php if (!empty($galleryImages) && is_array($galleryImages)): ?>
        <div class="gallery-grid">
          <?php foreach ($galleryImages as $_img): ?>
            <?php $_src = !empty($_img['url']) ? (string)$_img['url'] : ($basePath . '/' . ltrim((string)($_img['path'] ?? ''), '/')); ?>
            <?php if (!empty($lightboxEnabled)): ?>
              <a href="<?= htmlspecialchars($_src) ?>" data-lightbox="post-gallery" data-title="<?= htmlspecialchars($postTitle) ?>"><img src="<?= htmlspecialchars($_src) ?>" alt="" loading="lazy" /></a>
            <?php else: ?>
              <img src="<?= htmlspecialchars($_src) ?>" alt="" loading="lazy" />
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Reactions -->
      <?php require __DIR__ . '/../../src/PostReactions.php'; ?>

      <div class="cta-row">
        <?php if (!empty($link['target_url'])): ?><a class="btn btn-primary" href="<?= htmlspecialchars($directLinkUrl, ENT_QUOTES) ?>">Przejdź do oferty</a><?php endif; ?>
        <a class="btn" href="<?= htmlspecialchars($basePath) ?>/">Wróć do bloga</a>
      </div>
    </article>

    <?php if (!empty($relatedPosts)): ?>
      <section class="related-wrap">
        <h3>Powiązane wpisy</h3>
        <ul>
          <?php foreach (array_slice($relatedPosts, 0, 5) as $_rp): ?>
            <li><a href="<?= htmlspecialchars(ly_post_url($basePath, (string)$_rp['slug'], !empty($prettyUrls))) ?>"><?= htmlspecialchars((string)($_rp['page_title'] ?? $_rp['slug'])) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </section>
    <?php endif; ?>

    <section class="widgets-wrap"><?php require __DIR__ . '/_sidebar.php'; ?></section>
  </main>
</div>

<label for="sidebar-checkbox" class="sidebar-toggle"></label>
<?php if (!empty($lightboxEnabled)): ?><script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script><script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script><?php endif; ?>
<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>
</body>
</html>
