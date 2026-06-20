<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?= htmlspecialchars($homeTitle) ?></title>
  <?php if (!empty($homeMetaDescription)): ?>
    <meta name="description" content="<?= htmlspecialchars(strip_tags($homeMetaDescription)) ?>" />
  <?php endif; ?>

  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css?family=Google+Sans:300,400,700" rel="stylesheet" />
  <?php echo $themeCss ?? ''; ?>
  <?php if (!empty($homeHeaderCode)) echo $homeHeaderCode; ?>
  <style>
    body, html { height: 100%; }
    body { background-color: #e8e8e8; font-family: 'Google Sans', Arial, sans-serif; }
    #fullpage { position: relative; background: #fff; min-height: 100%; }
    @media (min-width: 768px) {
      #fullpage {
        max-width: 1000px;
        margin: 0 auto;
        border-left: 1px solid rgba(0,0,0,.23);
        border-right: 1px solid rgba(0,0,0,.23);
        box-shadow: 0 0 10px 0 rgba(0,0,0,.24);
      }
      #navcontent { position: relative; }
      #navcontent::after {
        content: '';
        position: absolute;
        top: -1rem;
        bottom: -1rem;
        left: calc(0px - .5px);
        width: 1px;
        background: #dee2e6;
      }
    }

    #navlink ul { list-style: none; margin: 0; padding: 0; }
    #navlink li { display: inline-block; }
    #navlink li:not(:last-child) { margin-right: .75rem; }

    #headline { display: flex; background: #fff; margin-bottom: 1rem; }
    #headline > span { background-color: var(--theme-primary, #007bff); color: #fff; min-width: 73px; padding: 10px; }
    #headline > div {
      padding: 10px; flex-grow: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }

    .card-post-list { margin-bottom: 1rem; }
    .card-post-list > ._thumbnail {
      position: relative;
      background-color: #dee2e6;
      background-position: center;
      background-repeat: no-repeat;
      background-size: cover;
      padding: 1rem;
      height: 170px;
    }
    .card-post-list > ._body { padding: 1rem; border: 1px solid #dee2e6; border-width: 0 1px 1px; }
    .card-post-list > ._body > h5 { font-size: 1.05rem; }
    .card-post-list > ._body > ._post { height: 57px; overflow: hidden; }

    .widget-box { border: 1px solid #dee2e6; margin-bottom: 1rem; }
    .widget-title { margin: 0; padding: .75rem 1rem; font-size: .95rem; font-weight: 700; border-bottom: 1px solid #dee2e6; background: #f8f9fa; }
    .widget-body { padding: .9rem 1rem; }

    .brand-logo { max-height: 44px; width: auto; }
    .site-title { font-size: 1.45rem; font-weight: 700; color: #111; text-decoration: none; }
    .site-subtitle { font-size: .9rem; color: #6c757d; }

    .badge-primary { background-color: var(--theme-primary, #007bff) !important; }

    @media (max-width: 767px) {
      #navlink:not(.d-none) {
        position: absolute;
        top: 50px;
        left: 0;
        right: 0;
        padding: 1rem 15px;
        background: #fff;
        border-bottom: 1px solid #dee2e6;
        z-index: 1;
        white-space: nowrap;
        overflow: auto;
      }
      #navlink:not(.d-none) > ul > li:last-child { margin-right: 15px; }
      #content-bottom .col-md-4.coba:not(:last-child) { padding-bottom: .5rem; }
    }
  </style>
</head>
<body>
<?php
function sp_post_url(string $bp, string $slug, bool $pretty): string {
    return $pretty ? $bp . '/blog/' . rawurlencode($slug) : $bp . '/?post=' . rawurlencode($slug);
}
function sp_cat_url(string $bp, string $slug, bool $pretty): string {
    return $pretty ? $bp . '/category/' . rawurlencode($slug) : $bp . '/?category=' . rawurlencode($slug);
}
function sp_tag_url(string $bp, string $slug, bool $pretty): string {
    return $pretty ? $bp . '/tag/' . rawurlencode($slug) : $bp . '/?tag=' . rawurlencode($slug);
}
?>

<section class="d-flex flex-column" id="fullpage">
  <section class="border-bottom">
    <nav class="py-2 py-md-3">
      <div class="container-fluid">
        <div class="row align-items-center">
          <div class="col-auto"><a href="<?= htmlspecialchars($basePath) ?>/">Start</a></div>
          <div class="col">
            <form action="<?= htmlspecialchars($basePath) ?>/" method="get" role="search">
              <div class="input-group">
                <input class="form-control" name="s" type="text" value="<?= htmlspecialchars($_GET['s'] ?? '') ?>" placeholder="Szukaj" />
                <div class="input-group-append"><button class="btn btn-primary" type="submit">Szukaj</button></div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </nav>
  </section>

  <section class="border-bottom py-3 py-md-4">
    <div class="container-fluid">
      <div class="row align-items-center">
        <div class="col-md-8 mb-3 mb-md-0">
          <a class="d-flex align-items-center text-decoration-none" href="<?= htmlspecialchars($basePath) ?>/">
            <?php if (!empty($brandingLogo)): ?>
              <img class="brand-logo mr-3" src="<?= htmlspecialchars($basePath . '/' . ltrim((string)$brandingLogo, '/')) ?>" alt="<?= htmlspecialchars($homeTitle) ?>" />
            <?php endif; ?>
            <span>
              <span class="site-title d-block"><?= htmlspecialchars($homeTitle) ?></span>
              <?php if (!empty($homeSubtitle)): ?><small class="site-subtitle d-block"><?= htmlspecialchars($homeSubtitle) ?></small><?php endif; ?>
            </span>
          </a>
        </div>
        <div class="col-md-4 text-md-right">
          <?php if (!empty($socialLinks) && is_array($socialLinks)): ?>
            <?php foreach ($socialLinks as $_name => $_url): ?>
              <?php if (!empty($_url)): ?><a class="btn btn-sm btn-light mb-1" target="_blank" rel="noopener noreferrer" href="<?= htmlspecialchars($_url) ?>"><?= htmlspecialchars(ucfirst((string)$_name)) ?></a><?php endif; ?>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <section>
    <nav class="border-bottom bg-white">
      <div class="py-3 container-fluid position-relative">
        <section class="d-block" id="navlink">
          <ul>
            <li><a href="<?= htmlspecialchars($basePath) ?>/">Blog</a></li>
            <?php foreach ($navPages as $_np): ?>
              <li><a href="<?= htmlspecialchars($basePath . '/?page=' . rawurlencode((string)$_np['slug'])) ?>"><?= htmlspecialchars($_np['title']) ?></a></li>
            <?php endforeach; ?>
            <?php if (!empty($contactEnabled)): ?><li><a href="<?= htmlspecialchars($basePath) ?>/?page=contact">Kontakt</a></li><?php endif; ?>
          </ul>
        </section>
      </div>
    </nav>
  </section>

  <div class="d-flex flex-fill py-3 container-fluid flex-column">
    <div class="row">
      <div class="col-12 col-md-8 mb-3 mb-md-0" id="maincontent">

        <?php if (!empty($blogPosts)): ?>
          <div id="headline">
            <span>Update</span>
            <div><?= htmlspecialchars($blogPosts[0]['page_title'] ?? 'Najnowszy wpis') ?></div>
          </div>

          <div class="row">
            <?php foreach ($blogPosts as $_post): ?>
              <?php $_thumb = !empty($_post['og_image']) ? ($basePath . '/' . ltrim((string)$_post['og_image'], '/')) : ''; ?>
              <div class="col-12 col-md-6">
                <article class="card-post-list">
                  <div class="_thumbnail" style="<?= $_thumb ? 'background-image:url(' . htmlspecialchars($_thumb, ENT_QUOTES) . ');' : '' ?>">
                    <?php if (!empty($_post['tags']) && is_array($_post['tags'])): ?>
                      <?php foreach (array_slice($_post['tags'], 0, 3) as $_tag): ?>
                        <a class="badge badge-primary" href="<?= htmlspecialchars(sp_tag_url($basePath, (string)$_tag['slug'], !empty($prettyUrls))) ?>"><?= htmlspecialchars($_tag['name']) ?></a>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                  <div class="_body">
                    <h5><a href="<?= htmlspecialchars(sp_post_url($basePath, (string)$_post['slug'], !empty($prettyUrls))) ?>"><?= htmlspecialchars($_post['page_title']) ?></a></h5>
                    <div class="small text-muted">On <?= htmlspecialchars(substr((string)($_post['created_at'] ?? ''), 0, 10)) ?></div>
                    <p class="small mb-2 _post"><?= htmlspecialchars(mb_substr(strip_tags((string)($_post['page_description'] ?? '')), 0, 100)) ?>...</p>
                    <p class="small mb-0">
                      By <?= htmlspecialchars($homeTitle) ?>
                      <span class="float-right"><a href="<?= htmlspecialchars(sp_post_url($basePath, (string)$_post['slug'], !empty($prettyUrls))) ?>#comments"><?= (int)($_post['click_count'] ?? 0) ?></a></span>
                    </p>
                  </div>
                </article>
              </div>
            <?php endforeach; ?>
          </div>

          <?php if (($totalPages ?? 1) > 1): ?>
            <?php
              $_currentQuery = $_GET;
              if (!is_array($_currentQuery)) {
                  $_currentQuery = [];
              }
              unset($_currentQuery['p']);
              $_prevQuery = $_currentQuery;
              $_nextQuery = $_currentQuery;
              $_prevQuery['p'] = max(1, ((int)$currentPage - 1));
              $_nextQuery['p'] = min((int)$totalPages, ((int)$currentPage + 1));
              $_prevUrl = $basePath . '/?' . http_build_query($_prevQuery);
              $_nextUrl = $basePath . '/?' . http_build_query($_nextQuery);
            ?>
            <ul class="pagination pagination-sm mb-0">
              <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>"><?php if ($currentPage <= 1): ?><span class="page-link" aria-disabled="true">Prev</span><?php else: ?><a class="page-link" href="<?= htmlspecialchars($_prevUrl) ?>">Prev</a><?php endif; ?></li>
              <li class="page-item disabled"><span class="page-link">Page <?= (int)$currentPage ?> / <?= (int)$totalPages ?></span></li>
              <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>"><?php if ($currentPage >= $totalPages): ?><span class="page-link" aria-disabled="true">Next</span><?php else: ?><a class="page-link" href="<?= htmlspecialchars($_nextUrl) ?>">Next</a><?php endif; ?></li>
            </ul>
          <?php endif; ?>
        <?php else: ?>
          <div class="alert alert-light border mb-0">Brak wpisów do wyświetlenia.</div>
        <?php endif; ?>
      </div>

      <aside class="col-12 col-md-4" id="navcontent">
        <?php require __DIR__ . '/_sidebar.php'; ?>
      </aside>
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
