<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?= htmlspecialchars($postTitle) ?> | <?= htmlspecialchars($homeTitle) ?></title>
  <meta name="description" content="<?= htmlspecialchars(mb_substr(strip_tags((string)$postDescription), 0, 160)) ?>" />

  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css?family=Google+Sans:300,400,700" rel="stylesheet" />
  <?php if (!empty($lightboxEnabled)): ?><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" /><?php endif; ?>
  <?php echo $themeCss ?? ''; ?>
  <?php if (!empty($homeHeaderCode)) echo $homeHeaderCode; ?>
  <style>
    body, html { height: 100%; }
    body { background: #e8e8e8; font-family: 'Google Sans', Arial, sans-serif; }
    #fullpage { background: #fff; min-height: 100%; }
    @media (min-width: 768px) {
      #fullpage { max-width: 1000px; margin: 0 auto; border-left: 1px solid rgba(0,0,0,.23); border-right: 1px solid rgba(0,0,0,.23); box-shadow: 0 0 10px 0 rgba(0,0,0,.24); }
      #sharebox.sticky-top { top: 1rem; }
    }
    #navlink ul { list-style: none; margin: 0; padding: 0; white-space: nowrap; overflow: auto; }
    #navlink li { display: inline-block; }
    #navlink li:not(:last-child) { margin-right: .75rem; }
    #post-content img { max-width: 100%; height: auto; }
    #sharebox > a { font-size: 20px; color: #fff; padding: 8px; margin-right: 4px; display: inline-block; }
    #sharebox > a.facebook-share-btn { background: #5d82d1; }
    #sharebox > a.twitter-share-btn { background: #40bff5; }
    #sharebox > a.google-share-btn { background: #eb5e4c; }
    #sharebox > a.whatsapp-share-btn { background: #43d854; }
    .widget-box { border: 1px solid #dee2e6; margin-bottom: 1rem; }
    .widget-title { background: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: .75rem 1rem; font-weight: 700; margin: 0; font-size: .95rem; }
    .widget-body { padding: .9rem 1rem; }
    .brand-logo { max-height: 44px; width: auto; }
    .site-title { font-size: 1.45rem; font-weight: 700; color: #111; text-decoration: none; }
    .site-subtitle { font-size: .9rem; color: #6c757d; }
    .badge-primary { background-color: var(--theme-primary, #007bff) !important; }
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
?>
<section id="fullpage" class="d-flex flex-column">
  <section class="border-bottom">
    <nav class="py-2 py-md-3">
      <div class="container-fluid">
        <div class="row align-items-center">
          <div class="col-auto"><a href="<?= htmlspecialchars($basePath) ?>/"><strong>Start</strong></a></div>
          <div class="col">
            <form action="<?= htmlspecialchars($basePath) ?>/" method="get" role="search">
              <div class="input-group">
                <input class="form-control" name="s" value="<?= htmlspecialchars($_GET['s'] ?? '') ?>" placeholder="Szukaj" type="text" />
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
  </section>

  <section>
    <nav class="border-bottom bg-white">
      <div class="py-3 container-fluid" id="navlink">
        <ul>
          <li><a href="<?= htmlspecialchars($basePath) ?>/">Blog</a></li>
          <?php foreach ($navPages as $_np): ?>
            <li><a href="<?= htmlspecialchars($basePath . '/?page=' . rawurlencode((string)$_np['slug'])) ?>"><?= htmlspecialchars($_np['title']) ?></a></li>
          <?php endforeach; ?>
          <?php if (!empty($contactEnabled)): ?><li><a href="<?= htmlspecialchars($basePath) ?>/?page=contact">Kontakt</a></li><?php endif; ?>
        </ul>
      </div>
    </nav>
  </section>

  <div class="d-flex flex-fill py-3 container-fluid flex-column">
    <div class="row">
      <div class="col-12 col-md-8 mb-3 mb-md-0">

        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= htmlspecialchars($basePath) ?>/">Start</a></li>
            <?php if (!empty($link['category']['slug'])): ?>
              <li class="breadcrumb-item"><a href="<?= htmlspecialchars(sp_cat_url($basePath, (string)$link['category']['slug'], !empty($prettyUrls))) ?>"><?= htmlspecialchars($link['category']['name'] ?? '') ?></a></li>
            <?php endif; ?>
            <li class="breadcrumb-item active text-truncate" aria-current="page"><?= htmlspecialchars($postTitle) ?></li>
          </ol>
        </nav>

        <div class="row flex-md-row-reverse">
          <div class="col-12 col-md flex-grow-1 mb-3">
            <h3 class="text-primary"><?= htmlspecialchars($postTitle) ?></h3>
            <p class="small text-muted mb-2">
              On <?= htmlspecialchars(substr((string)$postCreatedAt, 0, 10)) ?>
              <?php if (!empty($link['click_count'])): ?> <span class="mx-2">&middot;</span> <?= (int)$link['click_count'] ?> views<?php endif; ?>
            </p>

            <?php if (!empty($ogImageUrl)): ?>
              <img class="img-fluid mb-3" src="<?= htmlspecialchars($ogImageUrl) ?>" alt="<?= htmlspecialchars($postTitle) ?>" />
            <?php endif; ?>

            <div id="post-content"><?= Utils::sanitizeHtml($postDescription ?? '') ?></div>

            <?php if (!empty($link['tags'])): ?>
              <hr />
              <?php foreach ($link['tags'] as $_tag): ?>
                <a class="badge badge-primary" href="<?= htmlspecialchars($basePath . '/?tag=' . rawurlencode((string)$_tag['slug'])) ?>">#<?= htmlspecialchars($_tag['name']) ?></a>
              <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!empty($galleryImages)): ?>
              <hr />
              <div class="row">
                <?php foreach ($galleryImages as $_img): ?>
                  <?php $_src = !empty($_img['url']) ? $_img['url'] : ($basePath . '/' . ltrim((string)($_img['path'] ?? ''), '/')); ?>
                  <div class="col-6 col-md-4 mb-3">
                    <?php if (!empty($lightboxEnabled)): ?>
                      <a href="<?= htmlspecialchars($_src) ?>" data-lightbox="post-gallery" data-title="<?= htmlspecialchars($postTitle) ?>">
                        <img class="img-fluid" src="<?= htmlspecialchars($_src) ?>" alt="" />
                      </a>
                    <?php else: ?>
                      <img class="img-fluid" src="<?= htmlspecialchars($_src) ?>" alt="" />
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <!-- Reactions -->
            <?php require __DIR__ . '/../../src/PostReactions.php'; ?>

            <?php if (!empty($link['target_url'])): ?>
              <div class="alert alert-primary mt-3 mb-0 d-flex justify-content-between align-items-center flex-wrap">
                <span>Przejdź do oferty</span>
                <a class="btn btn-primary btn-sm" href="<?= htmlspecialchars($directLinkUrl, ENT_QUOTES) ?>">Sprawdź ofertę</a>
              </div>
            <?php endif; ?>
          </div>

          <div class="col-12 col-md-auto text-center pr-md-0 mb-3">
            <div class="sticky-top d-flex flex-row flex-md-column" id="sharebox">
              <a class="facebook-share-btn" href="https://www.facebook.com/sharer.php?u=<?= urlencode($shareUrl) ?>" target="_blank" rel="noopener">f</a>
              <a class="twitter-share-btn" href="https://twitter.com/intent/tweet?text=<?= urlencode($postTitle) ?>&url=<?= urlencode($shareUrl) ?>" target="_blank" rel="noopener">x</a>
              <a class="google-share-btn" href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($shareUrl) ?>" target="_blank" rel="noopener">in</a>
              <a class="whatsapp-share-btn" href="https://wa.me/?text=<?= urlencode($postTitle . ' ' . $shareUrl) ?>" target="_blank" rel="noopener">wa</a>
            </div>
          </div>
        </div>

        <?php if (!empty($relatedPosts)): ?>
          <hr />
          <div class="row">
            <?php foreach (array_slice($relatedPosts, 0, 4) as $_rp): ?>
              <?php $_thumb = !empty($_rp['og_image']) ? $basePath . '/' . ltrim((string)$_rp['og_image'], '/') : ''; ?>
              <div class="col-6 col-md-3 mb-3">
                <a class="d-block text-decoration-none" href="<?= htmlspecialchars(sp_post_url($basePath, (string)$_rp['slug'], !empty($prettyUrls))) ?>">
                  <?php if ($_thumb): ?><img class="img-fluid mb-2" src="<?= htmlspecialchars($_thumb) ?>" alt="" /><?php endif; ?>
                  <small class="d-block text-dark"><?= htmlspecialchars($_rp['page_title']) ?></small>
                </a>
              </div>
            <?php endforeach; ?>
          </div>
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

<?php if (!empty($lightboxEnabled)): ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
<?php endif; ?>
<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>
</body>
</html>
