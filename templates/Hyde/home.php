<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($homeTitle) ?></title>
  <?php if (!empty($homeMetaDescription)): ?>
    <meta name="description" content="<?= htmlspecialchars(strip_tags((string)$homeMetaDescription)) ?>" />
  <?php endif; ?>

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
    .page-title {
      font-size: 1.45rem;
      margin: 0 0 1.2rem;
      color: #313131;
    }
    .post {
      margin-bottom: 2.2rem;
      padding-bottom: 2rem;
      border-bottom: 1px solid #ececec;
    }
    .post:last-child { border-bottom: 0; }
    .post-title { margin: 0; font-size: 2rem; line-height: 1.25; color: #313131; }
    .post-date { display: block; margin: .25rem 0 .9rem; color: #9a9a9a; font-size: .82rem; }
    .post-excerpt { font-size: .95rem; }
    .post-thumb { width: 100%; height: auto; border-radius: 4px; margin: .35rem 0 .9rem; display: block; }

    .pagination { display: flex; justify-content: space-between; gap: .8rem; margin-top: 2rem; }
    .pagination a, .pagination span {
      border: 1px solid #ddd;
      padding: .45rem .75rem;
      font-size: .78rem;
      color: #666;
      text-decoration: none;
    }
    .pagination a:hover { background: #f7f7f7; }
    .pagination .disabled { opacity: .45; }

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
function hyde_tag_url(string $bp, string $slug, bool $pretty): string {
    return $pretty ? $bp . '/tag/' . rawurlencode($slug) : $bp . '/?tag=' . rawurlencode($slug);
}
?>

<aside class="sidebar">
  <div class="sidebar-sticky">
    <div class="sidebar-about">
      <h1><a href="<?= htmlspecialchars($basePath) ?>/"><?= htmlspecialchars($homeTitle) ?></a></h1>
      <?php if (!empty($homeSubtitle)): ?><p class="lead"><?= htmlspecialchars($homeSubtitle) ?></p><?php endif; ?>
    </div>

    <nav class="sidebar-nav">
      <a class="sidebar-nav-item active" href="<?= htmlspecialchars($basePath) ?>/">Start</a>
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
  <?php if (!empty($activeCategory) || !empty($activeTag)): ?>
    <h2 class="page-title">
      <?php if (!empty($activeCategory)): ?>Category: <?= htmlspecialchars((string)$activeCategory['name']) ?><?php else: ?>Tag: #<?= htmlspecialchars((string)$activeTag['name']) ?><?php endif; ?>
    </h2>
  <?php endif; ?>

  <?php if (empty($blogPosts)): ?>
    <article class="post">
      <h2 class="post-title">Brak wpisów</h2>
      <p class="post-excerpt">Brak wpisow do wyswietlenia dla aktualnego filtra.</p>
    </article>
  <?php else: ?>
    <?php foreach ($blogPosts as $_post): ?>
      <?php
        $_title = (string)($_post['page_title'] ?? $_post['slug']);
        $_slug = (string)($_post['slug'] ?? '');
        $_date = (string)($_post['created_at'] ?? '');
        $_desc = trim(strip_tags((string)($_post['page_description'] ?? '')));
        $_desc = mb_strlen($_desc) > (int)$blogDescLength ? mb_substr($_desc, 0, (int)$blogDescLength) . '...' : $_desc;
        $_thumb = !empty($_post['og_image']) && !empty($blogShowImages) ? $basePath . '/' . ltrim((string)$_post['og_image'], '/') : '';
      ?>
      <article class="post">
        <h2 class="post-title">
          <a href="<?= htmlspecialchars(hyde_post_url($basePath, $_slug, !empty($prettyUrls))) ?>"><?= htmlspecialchars($_title) ?></a>
        </h2>
        <span class="post-date">
          <?= htmlspecialchars(substr($_date, 0, 10)) ?>
          <?php if (!empty($_post['category_slug'])): ?>
            &middot; <a href="<?= htmlspecialchars(hyde_cat_url($basePath, (string)$_post['category_slug'], !empty($prettyUrls))) ?>"><?= htmlspecialchars((string)($_post['category_name'] ?? 'Category')) ?></a>
          <?php endif; ?>
        </span>

        <?php if ($_thumb !== ''): ?>
          <a href="<?= htmlspecialchars(hyde_post_url($basePath, $_slug, !empty($prettyUrls))) ?>">
            <img class="post-thumb" src="<?= htmlspecialchars($_thumb) ?>" alt="<?= htmlspecialchars($_title) ?>" loading="lazy" />
          </a>
        <?php endif; ?>

        <?php if ($_desc !== ''): ?><p class="post-excerpt"><?= htmlspecialchars($_desc) ?></p><?php endif; ?>

        <?php if (!empty($_post['tags']) && is_array($_post['tags'])): ?>
          <p class="hyde-tags hyde-muted">
            <?php foreach (array_slice($_post['tags'], 0, 5) as $_tag): ?>
              <a href="<?= htmlspecialchars(hyde_tag_url($basePath, (string)$_tag['slug'], !empty($prettyUrls))) ?>">#<?= htmlspecialchars((string)$_tag['name']) ?></a>
            <?php endforeach; ?>
          </p>
        <?php endif; ?>
      </article>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php if (($totalPages ?? 1) > 1): ?>
    <?php
      $_cp = (int)$currentPage;
      $_tp = (int)$totalPages;
      $_qs = [];
      if (!empty($_GET['category']) && is_string($_GET['category'])) $_qs['category'] = $_GET['category'];
      if (!empty($_GET['tag']) && is_string($_GET['tag'])) $_qs['tag'] = $_GET['tag'];
      $_base = $basePath . '/?' . (!empty($_qs) ? http_build_query($_qs) . '&' : '');
    ?>
    <nav class="pagination" aria-label="Paginacja">
      <?php if ($_cp > 1): ?>
        <a href="<?= htmlspecialchars($_base . 'p=' . ($_cp - 1)) ?>">&larr; Nowsze</a>
      <?php else: ?>
        <span class="disabled">&larr; Nowsze</span>
      <?php endif; ?>

      <?php if ($_cp < $_tp): ?>
        <a href="<?= htmlspecialchars($_base . 'p=' . ($_cp + 1)) ?>">Starsze &rarr;</a>
      <?php else: ?>
        <span class="disabled">Starsze &rarr;</span>
      <?php endif; ?>
    </nav>
  <?php endif; ?>

  <section class="widgets-wrap">
    <?php require __DIR__ . '/_sidebar.php'; ?>
  </section>
</main>

<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>
</body>
</html>
