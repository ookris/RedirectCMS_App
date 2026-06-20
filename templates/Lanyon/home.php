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

    .sidebar {
      position: fixed;
      top: 0;
      bottom: 0;
      left: -14rem;
      width: 14rem;
      visibility: hidden;
      overflow-y: auto;
      font-size: .875rem;
      color: rgba(255,255,255,.6);
      background: var(--theme-header_bg, #202020);
      transition: all .3s ease-in-out;
      z-index: 30;
    }
    .sidebar a { color: var(--theme-header_text, #fff); }
    .sidebar-item { padding: 1rem; }
    .sidebar-item p:last-child { margin-bottom: 0; }
    .sidebar-nav { border-bottom: 1px solid rgba(255,255,255,.1); }
    .sidebar-nav-item {
      display: block;
      padding: .5rem 1rem;
      border-top: 1px solid rgba(255,255,255,.1);
    }
    .sidebar-nav-item.active,
    .sidebar-nav-item:hover,
    .sidebar-nav-item:focus {
      text-decoration: none;
      background: rgba(255,255,255,.1);
      border-color: transparent;
    }

    .sidebar-checkbox { display: none; }
    .sidebar-toggle {
      position: absolute;
      top: 1rem;
      left: 1rem;
      display: block;
      width: 2.2rem;
      padding: .5rem .65rem;
      color: #505050;
      background: #fff;
      border-radius: 4px;
      cursor: pointer;
      z-index: 31;
    }
    .sidebar-toggle:before {
      display: block;
      content: "";
      width: 100%;
      padding-bottom: .125rem;
      border-top: .375rem double;
      border-bottom: .125rem solid;
      box-sizing: border-box;
    }

    .wrap,
    .sidebar,
    .sidebar-toggle {
      backface-visibility: hidden;
    }
    .wrap,
    .sidebar-toggle {
      transition: transform .3s ease-in-out;
    }

    #sidebar-checkbox:checked + .sidebar { visibility: visible; }
    #sidebar-checkbox:checked ~ .sidebar,
    #sidebar-checkbox:checked ~ .wrap,
    #sidebar-checkbox:checked ~ .sidebar-toggle {
      transform: translateX(14rem);
    }

    .masthead {
      padding-top: 1rem;
      padding-bottom: 1rem;
      margin-bottom: 2rem;
    }
    .container {
      max-width: 38rem;
      padding-left: 1rem;
      padding-right: 1rem;
      margin: 0 auto;
    }
    .masthead-title { margin: 0; color: #505050; }
    .masthead-title a { color: #505050; }
    .masthead-title small { font-size: 75%; font-weight: 400; color: #c0c0c0; }

    .post { margin-bottom: 2rem; }
    .post-title, .post-title a { color: #303030; margin-top: 0; }
    .post-date {
      display: block;
      margin-top: -.5rem;
      margin-bottom: 1rem;
      color: #9a9a9a;
      font-size: .9rem;
    }

    .home-thumb-wrap { margin: .5rem 0 1rem; }
    .home-thumb {
      display: block;
      width: 100%;
      border-radius: 6px;
      max-height: 320px;
      object-fit: cover;
      border: 1px solid #ececec;
    }

    .post-excerpt { margin: 0; }

    .pagination {
      overflow: hidden;
      margin-left: -1rem;
      margin-right: -1rem;
      color: #ccc;
      text-align: center;
      border: 1px solid #eee;
      border-radius: 4px;
    }
    .pagination-item {
      display: block;
      padding: 1rem;
      border-top: 1px solid #eee;
    }
    .pagination-item:first-child { border-top: 0; }

    .widgets-wrap { margin-top: 1.5rem; }
    .ly-widget-box { border-top: 1px solid #eee; padding-top: 1rem; margin-top: 1rem; }
    .ly-widget-title { margin: 0 0 .6rem; font-size: 1rem; color: #313131; }
    .ly-list { list-style: none; margin: 0; padding: 0; }
    .ly-list li { margin: 0 0 .35rem; }
    .ly-tags a { margin-right: .45rem; white-space: nowrap; }
    .ly-muted { color: #8a8a8a; }

    @media (min-width: 30.1rem) {
      .sidebar-toggle { position: fixed; }
    }

    @media (min-width: 48rem) {
      .pagination { margin: 3rem 0; }
      .pagination-item {
        float: left;
        width: 50%;
        border-top: 0;
      }
      .pagination-item:first-child { border-right: 1px solid #eee; }
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
function ly_tag_url(string $bp, string $slug, bool $pretty): string {
    return $pretty ? $bp . '/tag/' . rawurlencode($slug) : $bp . '/?tag=' . rawurlencode($slug);
}
?>

<input class="sidebar-checkbox" id="sidebar-checkbox" type="checkbox" />

<div class="sidebar" id="sidebar">
  <div class="sidebar-item">
    <?php if (!empty($homeSubtitle)): ?>
      <p><?= htmlspecialchars($homeSubtitle) ?></p>
    <?php else: ?>
      <p>Simple content-first layout inspired by Lanyon.</p>
    <?php endif; ?>
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

  <div class="sidebar-item">
    <p><?= $homeFooter ?: ('&copy; ' . date('Y') . ' ' . htmlspecialchars($homeTitle)) ?></p>
  </div>
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
    <?php if (!empty($activeCategory) || !empty($activeTag)): ?>
      <h2 class="post-title" style="font-size:1.2rem; margin-bottom:1rem;">
        <?php if (!empty($activeCategory)): ?>Category: <?= htmlspecialchars((string)$activeCategory['name']) ?><?php else: ?>Tag: #<?= htmlspecialchars((string)$activeTag['name']) ?><?php endif; ?>
      </h2>
    <?php endif; ?>

    <?php if (empty($blogPosts)): ?>
      <article class="post">
        <h2 class="post-title">Brak wpisów</h2>
        <p class="post-excerpt">Brak wpisow do wyswietlenia.</p>
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
          <h2 class="post-title"><a href="<?= htmlspecialchars(ly_post_url($basePath, $_slug, !empty($prettyUrls))) ?>"><?= htmlspecialchars($_title) ?></a></h2>
          <span class="post-date">
            <?= htmlspecialchars(substr($_date, 0, 10)) ?>
            <?php if (!empty($_post['category_slug'])): ?>
              &middot; <a href="<?= htmlspecialchars(ly_cat_url($basePath, (string)$_post['category_slug'], !empty($prettyUrls))) ?>"><?= htmlspecialchars((string)($_post['category_name'] ?? 'Category')) ?></a>
            <?php endif; ?>
          </span>

          <?php if ($_thumb !== ''): ?>
            <a class="home-thumb-wrap" href="<?= htmlspecialchars(ly_post_url($basePath, $_slug, !empty($prettyUrls))) ?>">
              <img class="home-thumb" src="<?= htmlspecialchars($_thumb) ?>" alt="<?= htmlspecialchars($_title) ?>" loading="lazy" />
            </a>
          <?php endif; ?>

          <?php if ($_desc !== ''): ?><p class="post-excerpt"><?= htmlspecialchars($_desc) ?></p><?php endif; ?>

          <?php if (!empty($_post['tags']) && is_array($_post['tags'])): ?>
            <p class="ly-tags ly-muted">
              <?php foreach (array_slice($_post['tags'], 0, 5) as $_tag): ?>
                <a href="<?= htmlspecialchars(ly_tag_url($basePath, (string)$_tag['slug'], !empty($prettyUrls))) ?>">#<?= htmlspecialchars((string)$_tag['name']) ?></a>
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
        $_qs = array_filter(
          $_GET,
          static fn ($value, $key): bool => $key !== 'p' && is_scalar($value) && $value !== '',
          ARRAY_FILTER_USE_BOTH
        );
        $_base = $basePath . '/?' . (!empty($_qs) ? http_build_query($_qs) . '&' : '');
      ?>
      <nav class="pagination" aria-label="Paginacja">
        <?php if ($_cp > 1): ?>
          <a class="pagination-item" href="<?= htmlspecialchars($_base . 'p=' . ($_cp - 1)) ?>">&larr; Nowsze</a>
        <?php else: ?>
          <span class="pagination-item">&larr; Nowsze</span>
        <?php endif; ?>

        <?php if ($_cp < $_tp): ?>
          <a class="pagination-item" href="<?= htmlspecialchars($_base . 'p=' . ($_cp + 1)) ?>">Starsze &rarr;</a>
        <?php else: ?>
          <span class="pagination-item">Starsze &rarr;</span>
        <?php endif; ?>
      </nav>
    <?php endif; ?>

    <section class="widgets-wrap">
      <?php require __DIR__ . '/_sidebar.php'; ?>
    </section>
  </main>
</div>

<label for="sidebar-checkbox" class="sidebar-toggle"></label>

<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>
</body>
</html>
