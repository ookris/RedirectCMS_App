<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($homeTitle) ?></title>
  <?php if (!empty($homeMetaDescription)): ?>
    <meta name="description" content="<?= htmlspecialchars(strip_tags((string)$homeMetaDescription)) ?>" />
  <?php endif; ?>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;700&display=swap" rel="stylesheet" />
  <?php echo $themeCss ?? ''; ?>
  <?php if (!empty($homeHeaderCode)) echo $homeHeaderCode; ?>

  <style>
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; }
    body {
      font-family: "Roboto Slab", Georgia, serif;
      line-height: 1.5;
      color: var(--theme-text, #111111);
      background: var(--theme-body_bg, #fafafa);
    }
    a { color: #000; text-decoration: none; }
    a:hover { text-decoration: underline; }

    .mn-shell { max-width: 1100px; margin: 0 auto; padding: 0 15px; }
    .mn-header {
      border-top: 5px solid var(--theme-primary, #666666);
      border-bottom: 4px double var(--theme-primary, #666666);
      text-align: center;
      padding: 15px 0 8px;
      background: var(--theme-header_bg, #ffffff);
      margin-bottom: 14px;
    }
    .mn-logo { margin: 0; line-height: 1; font-size: clamp(2rem, 5vw, 3.7rem); }
    .mn-tagline { display: block; margin-top: .55rem; color: #666; font-size: .95rem; }

    .mn-head-meta,
    .mn-menu {
      display: flex;
      justify-content: space-between;
      gap: .7rem;
      flex-wrap: wrap;
      align-items: center;
      margin-bottom: .7rem;
    }
    .mn-menu ul { list-style: none; margin: 0; padding: 0; }
    .mn-menu li { display: inline-block; font-weight: 700; margin-right: .3rem; }
    .mn-menu a { display: inline-block; padding: .35rem .55rem; }
    .mn-menu a:hover { color: #000; background: #fff; }

    .mn-search input {
      border: 1px solid #ddd;
      padding: .45rem .55rem;
      min-width: 180px;
      font: inherit;
    }
    .mn-search button {
      border: 1px solid #ddd;
      padding: .45rem .55rem;
      background: #fff;
      cursor: pointer;
      font: inherit;
    }

    .mn-status { margin: .25rem 0 1rem; color: #666; font-size: .95rem; }

    .mn-grid {
      display: flex;
      flex-wrap: wrap;
      margin-left: -15px;
    }
    .mn-card {
      flex: 1 0 350px;
      max-width: calc(100% - 15px);
      margin: 1em 0 0 15px;
      padding: 1em;
      background: #fff;
      overflow: hidden;
    }
    @media (min-width: 1080px) {
      .mn-card { max-width: calc(33.33333% - 15px); }
    }

    .mn-categories { margin-bottom: .4rem; }
    .mn-chip {
      display: inline-block;
      padding: 4px 8px;
      color: #fff;
      font-size: .78rem;
      font-weight: 700;
      text-transform: uppercase;
      margin-right: .25rem;
      margin-bottom: .25rem;
    }

    .mn-card h2 { margin: .2em auto .2em 0; line-height: 1.2em; font-size: 1.2rem; }
    .mn-summary { margin-top: .45rem; }
    .mn-summary p { margin: 0; }

    .thumbnail {
      height: 5.1em;
      width: 6.8em;
      float: left;
      overflow: hidden;
      margin: 5px 8px 5px 0;
      box-shadow: 0 0 8px #666;
    }
    .thumbnail img { width: 100%; height: 100%; object-fit: cover; }

    .mn-date { color: #666; font-size: .86rem; display: inline-block; margin-top: .4rem; }

    .mn-pagination { text-align: center; margin: 1.2rem 0 .7rem; }
    .mn-pagination a,
    .mn-pagination span {
      display: inline-block;
      padding: 0 .4rem;
      color: #222;
    }
    .mn-pagination .disabled { color: #9b9b9b; }

    .mn-sidezone { margin-top: 1rem; }
    .mn-sidebar-block { background: #fff; border: 1px solid #eee; border-radius: 4px; padding: .8rem; margin: 0 0 .8rem; }
    .mn-sidebar-block h3 { margin: 0 0 .45rem; font-size: 1rem; }
    .mn-clean-list { list-style: none; margin: 0; padding: 0; }
    .mn-clean-list li { margin: 0 0 .35rem; }
    .mn-tags-row a { margin-right: .45rem; white-space: nowrap; }
    .mn-search-row { display: flex; gap: .45rem; }
    .mn-search-row input, .mn-search-row button { font: inherit; }
    .mn-search-row input { width: 100%; border: 1px solid #ddd; padding: .45rem .55rem; }
    .mn-search-row button { border: 1px solid #ddd; background: #fff; padding: .45rem .65rem; cursor: pointer; }

    .mn-footer {
      margin-top: 1rem;
      background: var(--theme-footer_bg, #666666);
      color: var(--theme-footer_text, #ffffff);
      padding: 1rem 0;
      text-align: center;
    }
    .mn-footer a { color: #fff; }
    .mn-footer-menu { list-style: none; margin: 0 0 .5rem; padding: 0; }
    .mn-footer-menu li { display: inline-block; margin: 0 .2rem; font-weight: 700; }
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
if (!function_exists('mn_tag_color')) {
  function mn_tag_color(string $name): string {
    $map = [
      'tech' => '#000000',
      'nature' => 'green',
      'news' => 'brown',
      'mag' => 'pink',
      'finance' => 'orange',
      'sport' => 'cadetblue',
      'world' => 'gray',
    ];
    $k = strtolower(trim($name));
    return $map[$k] ?? '#5f6c7b';
  }
}
?>

<header class="mn-header">
  <div class="mn-shell">
    <h1 class="mn-logo"><a href="<?= htmlspecialchars($basePath) ?>/"><?= htmlspecialchars($homeTitle) ?></a></h1>
    <span class="mn-tagline"><?= htmlspecialchars(!empty($homeSubtitle) ? $homeSubtitle : 'blogger/blogspot mini newspaper template - theme') ?></span>
  </div>
</header>

<div class="mn-shell">
  <div class="mn-head-meta">
    <span>
      <?php if (!empty($activeCategory)): ?>Category: <?= htmlspecialchars((string)$activeCategory['name']) ?>
      <?php elseif (!empty($activeTag)): ?>Tag: #<?= htmlspecialchars((string)$activeTag['name']) ?>
      <?php else: ?>Najnowsze wiadomości<?php endif; ?>
    </span>
    <span><?= date('l d F Y') ?></span>
  </div>

  <nav class="mn-menu">
    <ul>
      <li><a href="<?= htmlspecialchars($basePath) ?>/">Start</a></li>
      <?php foreach ($navPages as $_np): ?>
        <li><a href="<?= htmlspecialchars($basePath . '/?page=' . rawurlencode((string)$_np['slug'])) ?>"><?= htmlspecialchars((string)$_np['title']) ?></a></li>
      <?php endforeach; ?>
      <?php if (!empty($contactEnabled)): ?><li><a href="<?= htmlspecialchars($basePath) ?>/?page=contact">Kontakt</a></li><?php endif; ?>
    </ul>

    <form class="mn-search" action="<?= htmlspecialchars($basePath) ?>/" method="get" role="search">
      <input type="text" name="s" value="<?= htmlspecialchars((string)($_GET['s'] ?? '')) ?>" placeholder="Szukaj" />
      <button type="submit">Go</button>
    </form>
  </nav>

  <?php if (empty($blogPosts)): ?>
    <p class="mn-status">Brak wpisów.</p>
  <?php else: ?>
    <div class="mn-grid">
      <?php foreach ($blogPosts as $_post): ?>
        <?php
          $_title = (string)($_post['page_title'] ?? $_post['slug']);
          $_slug = (string)($_post['slug'] ?? '');
          $_desc = trim(strip_tags((string)($_post['page_description'] ?? '')));
          $_desc = mb_strlen($_desc) > (int)$blogDescLength ? mb_substr($_desc, 0, (int)$blogDescLength) . '...' : $_desc;
          $_thumb = !empty($_post['og_image']) && !empty($blogShowImages) ? $basePath . '/' . ltrim((string)$_post['og_image'], '/') : '';
        ?>
        <section class="mn-card article-list" id="section">
          <?php if (!empty($_post['tags']) && is_array($_post['tags'])): ?>
            <div class="mn-categories">
              <?php foreach (array_slice($_post['tags'], 0, 2) as $_tag): ?>
                <a class="mn-chip" style="background-color: <?= htmlspecialchars(mn_tag_color((string)$_tag['name'])) ?>;" href="<?= htmlspecialchars(!empty($prettyUrls) ? $basePath . '/tag/' . rawurlencode((string)$_tag['slug']) : $basePath . '/?tag=' . rawurlencode((string)$_tag['slug'])) ?>"><?= htmlspecialchars((string)$_tag['name']) ?></a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <h2><a href="<?= htmlspecialchars(mn_post_url($basePath, $_slug, !empty($prettyUrls))) ?>"><?= htmlspecialchars($_title) ?></a></h2>

          <div class="mn-summary">
            <?php if ($_thumb !== ''): ?>
              <a class="thumbnail" href="<?= htmlspecialchars(mn_post_url($basePath, $_slug, !empty($prettyUrls))) ?>">
                <img src="<?= htmlspecialchars($_thumb) ?>" alt="<?= htmlspecialchars($_title) ?>" loading="lazy" />
              </a>
            <?php endif; ?>
            <p><?= htmlspecialchars($_desc) ?></p>
          </div>

          <span class="mn-date"><?= htmlspecialchars(substr((string)($_post['created_at'] ?? ''), 0, 10)) ?></span>
        </section>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (($totalPages ?? 1) > 1): ?>
    <?php
      $_cp = (int)$currentPage;
      $_tp = (int)$totalPages;
      $_query = $_GET;
      if (!is_array($_query)) {
          $_query = [];
      }
      $_olderQ = $_query;
      $_newerQ = $_query;
      $_olderQ['p'] = $_cp + 1;
      $_newerQ['p'] = max(1, $_cp - 1);
      $_olderUrl = $basePath . '/?' . http_build_query($_olderQ);
      $_newerUrl = $basePath . '/?' . http_build_query($_newerQ);
    ?>
    <nav class="mn-pagination" aria-label="Paginacja">
      <?php if ($_cp > 1): ?>
        <a href="<?= htmlspecialchars($_newerUrl) ?>">&larr; Nowsze</a>
      <?php else: ?>
        <span class="disabled">&larr; Nowsze</span>
      <?php endif; ?>

      <span>Page <?= $_cp ?> / <?= $_tp ?></span>

      <?php if ($_cp < $_tp): ?>
        <a href="<?= htmlspecialchars($_olderUrl) ?>">Starsze &rarr;</a>
      <?php else: ?>
        <span class="disabled">Starsze &rarr;</span>
      <?php endif; ?>
    </nav>
  <?php endif; ?>

  <div class="mn-sidezone">
    <?php require __DIR__ . '/_sidebar.php'; ?>
  </div>
</div>

<footer class="mn-footer">
  <div class="mn-shell">
    <ul class="mn-footer-menu">
      <li><a href="<?= htmlspecialchars($basePath) ?>/">Start</a></li>
      <?php foreach (array_slice($navPages, 0, 3) as $_np): ?>
        <li><a href="<?= htmlspecialchars($basePath . '/?page=' . rawurlencode((string)$_np['slug'])) ?>"><?= htmlspecialchars((string)$_np['title']) ?></a></li>
      <?php endforeach; ?>
      <?php if (!empty($contactEnabled)): ?><li><a href="<?= htmlspecialchars($basePath) ?>/?page=contact">Kontakt</a></li><?php endif; ?>
    </ul>
    <div class="mn-copyright"><?= $homeFooter ?: ('&copy; ' . date('Y') . ' ' . htmlspecialchars($homeTitle)) ?></div>
  </div>
</footer>

<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>
</body>
</html>
