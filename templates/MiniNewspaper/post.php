<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($postTitle) ?> | <?= htmlspecialchars($homeTitle) ?></title>
  <meta name="description" content="<?= htmlspecialchars(mb_substr(strip_tags((string)$postDescription), 0, 160)) ?>" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;700&display=swap" rel="stylesheet" />
  <?php if (!empty($lightboxEnabled)): ?><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" /><?php endif; ?>
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

    .mn-menu { display: flex; justify-content: space-between; gap: .7rem; flex-wrap: wrap; align-items: center; margin-bottom: .9rem; }
    .mn-menu ul { list-style: none; margin: 0; padding: 0; }
    .mn-menu li { display: inline-block; font-weight: 700; margin-right: .3rem; }
    .mn-menu a { display: inline-block; padding: .35rem .55rem; }

    .mn-breadcrumb { margin: .3rem 0 1rem; color: #666; font-size: .9rem; }

    .article-meta { text-align: center; padding: 5px; border-radius: 5px; }
    .title { font-size: 2rem; line-height: 1.2em; }
    .mn-post-date { color: #666; font-size: .9rem; margin: .35rem 0 0; }

    .mn-chip { display: inline-block; padding: 4px 8px; color: #fff; font-size: .78rem; font-weight: 700; text-transform: uppercase; margin-right: .25rem; margin-bottom: .25rem; background: #5f6c7b; }

    main { margin: 1rem 0 15px; background: #fff; padding: 1rem; }
    main img, main iframe, main video { max-width: 100%; height: auto; }
    pre { border: 1px solid #ddd; box-shadow: 5px 5px 5px #eee; overflow-x: auto; }
    code { background: #f9f9f9; }
    pre code { background: none; padding: .5em; display: block; }
    blockquote { background: #f9f9f9; border-left: 5px solid #ccc; padding: 3px 1em; margin-left: 0; }

    .mn-cover { width: 100%; margin-bottom: 1rem; box-shadow: 0 0 8px #666; }

    .mn-cta { margin-top: 1rem; border: 1px solid #e4e4e4; background: #fff; padding: .8rem; display: flex; justify-content: space-between; align-items: center; gap: .7rem; flex-wrap: wrap; }
    .mn-btn { display: inline-block; border: 1px solid #333; padding: .4rem .75rem; text-decoration: none; }
    .mn-btn:hover { background: #111; color: #fff; text-decoration: none; }

    .mn-gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: .6rem; margin-top: 1rem; }
    .mn-gallery img { width: 100%; aspect-ratio: 4 / 3; object-fit: cover; box-shadow: 0 0 8px #666; }

    .mn-related { margin-top: 1rem; background: #fff; padding: .8rem; }
    .mn-related h3 { margin: 0 0 .6rem; }

    .mn-sidezone { margin-top: 1rem; }
    .mn-sidebar-block { background: #fff; border: 1px solid #eee; border-radius: 4px; padding: .8rem; margin: 0 0 .8rem; }
    .mn-sidebar-block h3 { margin: 0 0 .45rem; font-size: 1rem; }
    .mn-clean-list { list-style: none; margin: 0; padding: 0; }
    .mn-clean-list li { margin: 0 0 .35rem; }
    .mn-tags-row a { margin-right: .45rem; white-space: nowrap; }
    .mn-search-row { display: flex; gap: .45rem; }
    .mn-search-row input { width: 100%; border: 1px solid #ddd; padding: .45rem .55rem; font: inherit; }
    .mn-search-row button { border: 1px solid #ddd; background: #fff; padding: .45rem .65rem; font: inherit; cursor: pointer; }

    .mn-footer { margin-top: 1rem; background: var(--theme-footer_bg, #666666); color: var(--theme-footer_text, #ffffff); padding: 1rem 0; text-align: center; }
    .mn-footer a { color: #fff; }
    .mn-copyright { color: #ddd; font-size: .9rem; }
  </style>
</head>
<body>
<?php
if (!function_exists('mn_post_url')) {
  function mn_post_url(string $bp, string $slug, bool $pretty): string {
    return $pretty ? $bp . '/blog/' . rawurlencode($slug) : $bp . '/?post=' . rawurlencode($slug);
  }
}
if (!function_exists('mn_cat_url')) {
  function mn_cat_url(string $bp, string $slug, bool $pretty): string {
    return $pretty ? $bp . '/category/' . rawurlencode($slug) : $bp . '/?category=' . rawurlencode($slug);
  }
}
?>

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
      <?php if (!empty($contactEnabled)): ?><li><a href="<?= htmlspecialchars($basePath) ?>/?page=contact">Kontakt</a></li><?php endif; ?>
    </ul>
  </nav>

  <div class="mn-breadcrumb">
    <a href="<?= htmlspecialchars($basePath) ?>/">Start</a>
    <?php if (!empty($link['category']['slug'])): ?>
      / <a href="<?= htmlspecialchars(mn_cat_url($basePath, (string)$link['category']['slug'], !empty($prettyUrls))) ?>"><?= htmlspecialchars((string)($link['category']['name'] ?? 'Category')) ?></a>
    <?php endif; ?>
    / <?= htmlspecialchars($postTitle) ?>
  </div>

  <article class="article-meta">
    <?php if (!empty($link['tags']) && is_array($link['tags'])): ?>
      <p>
        <?php foreach ($link['tags'] as $_tag): ?>
          <a class="mn-chip" href="<?= htmlspecialchars(!empty($prettyUrls) ? $basePath . '/tag/' . rawurlencode((string)$_tag['slug']) : $basePath . '/?tag=' . rawurlencode((string)$_tag['slug'])) ?>"><?= htmlspecialchars((string)$_tag['name']) ?></a>
        <?php endforeach; ?>
      </p>
    <?php endif; ?>
    <h1><span class="title"><?= htmlspecialchars($postTitle) ?></span></h1>
    <p class="mn-post-date"><?= htmlspecialchars(substr((string)$postCreatedAt, 0, 10)) ?></p>
  </article>

  <main>
    <?php if (!empty($ogImageUrl)): ?><img class="mn-cover" src="<?= htmlspecialchars($ogImageUrl) ?>" alt="<?= htmlspecialchars($postTitle) ?>" /><?php endif; ?>
    <div><?= Utils::sanitizeHtml((string)$postDescription) ?></div>

    <?php if (!empty($galleryImages) && is_array($galleryImages)): ?>
      <div class="mn-gallery">
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

    <?php if (!empty($link['target_url'])): ?>
      <div class="mn-cta">
        <span>Przejdź do oferty</span>
        <a class="mn-btn" href="<?= htmlspecialchars($directLinkUrl, ENT_QUOTES) ?>">Otwórz ofertę</a>
      </div>
    <?php endif; ?>
  </main>

  <?php if (!empty($relatedPosts)): ?>
    <section class="mn-related">
      <h3>Powiązane wpisy</h3>
      <ul>
        <?php foreach (array_slice($relatedPosts, 0, 5) as $_rp): ?>
          <li><a href="<?= htmlspecialchars(mn_post_url($basePath, (string)$_rp['slug'], !empty($prettyUrls))) ?>"><?= htmlspecialchars((string)($_rp['page_title'] ?? $_rp['slug'])) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </section>
  <?php endif; ?>

  <div class="mn-sidezone">
    <?php require __DIR__ . '/_sidebar.php'; ?>
  </div>
</div>

<footer class="mn-footer">
  <div class="mn-shell">
    <div class="mn-copyright"><?= $homeFooter ?: ('&copy; ' . date('Y') . ' ' . htmlspecialchars($homeTitle)) ?></div>
  </div>
</footer>

<?php if (!empty($lightboxEnabled)): ?><script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script><script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script><?php endif; ?>
<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>
</body>
</html>
