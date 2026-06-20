<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($postTitle) ?> | <?= htmlspecialchars($homeTitle) ?></title>
  <meta name="description" content="<?= htmlspecialchars(mb_substr(strip_tags((string)$postDescription), 0, 160)) ?>" />

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Abril+Fatface&family=PT+Sans:wght@400;700&display=swap" rel="stylesheet">
  <?php if (!empty($lightboxEnabled)): ?><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" /><?php endif; ?>
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

    .post-title { margin: 0 0 .2rem; font-size: 2rem; line-height: 1.25; color: #313131; }
    .post-date { display: block; margin: 0 0 1rem; color: #9a9a9a; font-size: .82rem; }
    .post-cover { width: 100%; border-radius: 4px; margin: .35rem 0 1rem; }
    .post-body { font-size: .95rem; }
    .post-body img { max-width: 100%; height: auto; border-radius: 4px; }

    .tag-list a { margin-right: .45rem; white-space: nowrap; }
    .cta-row { margin-top: 1.2rem; display: flex; flex-wrap: wrap; gap: .6rem; }
    .btn {
      display: inline-block;
      border: 1px solid #ddd;
      color: #666;
      padding: .45rem .75rem;
      font-size: .78rem;
      text-decoration: none;
    }
    .btn-primary {
      border-color: var(--theme-primary, #268bd2);
      background: var(--theme-primary, #268bd2);
      color: #fff;
    }
    .btn:hover { text-decoration: none; opacity: .9; }

    .gallery-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
      gap: .55rem;
      margin-top: 1rem;
    }
    .gallery-grid img { width: 100%; aspect-ratio: 4 / 3; object-fit: cover; border-radius: 4px; }

    .related-wrap { margin-top: 2rem; border-top: 1px solid #ececec; padding-top: 1rem; }
    .related-wrap h3 { margin: 0 0 .6rem; font-size: 1rem; color: #313131; }
    .related-wrap ul { margin: 0; padding-left: 1rem; }

    .widgets-wrap { margin-top: 1.4rem; }
    .hyde-widget-box { border-top: 1px solid #eee; padding-top: 1rem; margin-top: 1rem; }
    .hyde-widget-title { margin: 0 0 .6rem; font-size: 1rem; color: #313131; }
    .hyde-list { list-style: none; padding: 0; margin: 0; }
    .hyde-list li { margin: 0 0 .35rem; }
    .hyde-tags a { margin-right: .45rem; white-space: nowrap; }
    .hyde-muted { color: #8a8a8a; }

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
<?php
function hyde_post_url(string $bp, string $slug, bool $pretty): string {
    return $pretty ? $bp . '/blog/' . rawurlencode($slug) : $bp . '/?post=' . rawurlencode($slug);
}
function hyde_cat_url(string $bp, string $slug, bool $pretty): string {
    return $pretty ? $bp . '/category/' . rawurlencode($slug) : $bp . '/?category=' . rawurlencode($slug);
}
?>

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
      <?php if (!empty($contactEnabled)): ?>
        <a class="sidebar-nav-item" href="<?= htmlspecialchars($basePath) ?>/?page=contact">Kontakt</a>
      <?php endif; ?>
    </nav>

    <p class="sidebar-foot"><?= $homeFooter ?: ('&copy; ' . date('Y') . ' ' . htmlspecialchars($homeTitle)) ?></p>
  </div>
</aside>

<main class="content">
  <article>
    <h1 class="post-title"><?= htmlspecialchars($postTitle) ?></h1>
    <span class="post-date">
      <?= htmlspecialchars(substr((string)$postCreatedAt, 0, 10)) ?>
      <?php if (!empty($link['category']['slug'])): ?>
        &middot; <a href="<?= htmlspecialchars(hyde_cat_url($basePath, (string)$link['category']['slug'], !empty($prettyUrls))) ?>"><?= htmlspecialchars((string)($link['category']['name'] ?? 'Category')) ?></a>
      <?php endif; ?>
      <?php if (!empty($link['click_count'])): ?>
        &middot; <?= (int)$link['click_count'] ?> views
      <?php endif; ?>
    </span>

    <?php if (!empty($ogImageUrl)): ?>
      <img class="post-cover" src="<?= htmlspecialchars($ogImageUrl) ?>" alt="<?= htmlspecialchars($postTitle) ?>" />
    <?php endif; ?>

    <div class="post-body"><?= Utils::sanitizeHtml((string)$postDescription) ?></div>

    <?php if (!empty($link['tags']) && is_array($link['tags'])): ?>
      <p class="tag-list hyde-tags hyde-muted">
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
            <a href="<?= htmlspecialchars($_src) ?>" data-lightbox="post-gallery" data-title="<?= htmlspecialchars($postTitle) ?>">
              <img src="<?= htmlspecialchars($_src) ?>" alt="" loading="lazy" />
            </a>
          <?php else: ?>
            <img src="<?= htmlspecialchars($_src) ?>" alt="" loading="lazy" />
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Reactions -->
    <?php require __DIR__ . '/../../src/PostReactions.php'; ?>

    <div class="cta-row">
      <?php if (!empty($link['target_url'])): ?>
        <a class="btn btn-primary" href="<?= htmlspecialchars($directLinkUrl, ENT_QUOTES) ?>">Przejdź do oferty</a>
      <?php endif; ?>
      <a class="btn" href="<?= htmlspecialchars($basePath) ?>/">Wróć do bloga</a>
    </div>
  </article>

  <?php if (!empty($relatedPosts)): ?>
    <section class="related-wrap">
      <h3>Powiązane wpisy</h3>
      <ul>
        <?php foreach (array_slice($relatedPosts, 0, 5) as $_rp): ?>
          <li><a href="<?= htmlspecialchars(hyde_post_url($basePath, (string)$_rp['slug'], !empty($prettyUrls))) ?>"><?= htmlspecialchars((string)($_rp['page_title'] ?? $_rp['slug'])) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </section>
  <?php endif; ?>

  <section class="widgets-wrap">
    <?php require __DIR__ . '/_sidebar.php'; ?>
  </section>
</main>

<?php if (!empty($lightboxEnabled)): ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
<?php endif; ?>
<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>
</body>
</html>
