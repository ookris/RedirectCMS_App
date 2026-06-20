<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($postTitle) ?></title>
  <meta name="description" content="<?= htmlspecialchars(mb_substr(strip_tags((string)($postDescription ?? '')), 0, 160)) ?>" />

  <!-- Open Graph -->
  <meta property="og:type"        content="article" />
  <meta property="og:title"       content="<?= htmlspecialchars($postTitle) ?>" />
  <meta property="og:description" content="<?= htmlspecialchars(mb_substr(strip_tags((string)($postDescription ?? '')), 0, 200)) ?>" />
  <meta property="og:url"         content="<?= htmlspecialchars($shareUrl) ?>" />
  <?php if (!empty($ogImageUrl)): ?>
    <meta property="og:image"     content="<?= htmlspecialchars($ogImageUrl) ?>" />
    <meta property="og:image:width"  content="1200" />
    <meta property="og:image:height" content="630" />
  <?php endif; ?>
  <?php if (!empty($postCreatedAt)): ?>
    <meta property="article:published_time" content="<?= htmlspecialchars($postCreatedAt) ?>" />
  <?php endif; ?>

  <!-- Twitter Card -->
  <meta name="twitter:card"        content="summary_large_image" />
  <meta name="twitter:title"       content="<?= htmlspecialchars($postTitle) ?>" />
  <meta name="twitter:description" content="<?= htmlspecialchars(mb_substr(strip_tags((string)($postDescription ?? '')), 0, 200)) ?>" />
  <?php if (!empty($ogImageUrl)): ?>
    <meta name="twitter:image"     content="<?= htmlspecialchars($ogImageUrl) ?>" />
  <?php endif; ?>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
  <?php if (!empty($lightboxEnabled)): ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" />
  <?php endif; ?>
  <?php echo $themeCss ?? ''; ?>
  <?php if (!empty($homeHeaderCode)) echo $homeHeaderCode; ?>
  <style>
    :root { --transition: .2s ease; }
    body { background: var(--theme-body-bg, #f4f6f9); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }

    /* HEADER */
    .site-header { background: var(--theme-header-bg, #2d3748); color: var(--theme-header-text, #fff); padding: 20px 0; }
    .site-header a { color: inherit; text-decoration: none; }
    .site-logo { height: 44px; width: auto; object-fit: contain; }
    .site-title { font-size: 1.4rem; font-weight: 700; line-height: 1.2; }
    .site-subtitle { font-size: .85rem; opacity: .75; margin-top: 2px; }

    /* NAV */
    .site-nav { background: #fff; border-bottom: 2px solid var(--theme-primary, #4a90d9); position: sticky; top: 0; z-index: 100; box-shadow: 0 1px 6px rgba(0,0,0,.06); }
    .site-nav .nav-link { color: #495057; font-size: .9rem; padding: 10px 14px; font-weight: 500; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: color var(--transition), border-color var(--transition); }
    .site-nav .nav-link:hover { color: var(--theme-primary, #4a90d9); border-bottom-color: var(--theme-primary, #4a90d9); }

    /* POST HERO */
    .post-hero { width: 100%; max-height: 440px; object-fit: cover; border-radius: 12px; margin-bottom: 28px; box-shadow: 0 4px 20px rgba(0,0,0,.12); }

    /* ARTICLE */
    .post-article { background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.07); padding: 36px; }
    .post-article h1 { font-size: 1.75rem; font-weight: 800; line-height: 1.3; color: #1a202c; }
    @media (max-width: 576px) { .post-article { padding: 20px; } .post-article h1 { font-size: 1.4rem; } }

    /* META */
    .post-meta { font-size: .8rem; color: #94a3b8; display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin: 12px 0 20px; }
    .post-meta svg { vertical-align: -2px; }
    .cat-badge { display: inline-block; padding: 2px 10px; border-radius: 10px; font-size: .72rem; font-weight: 700; color: #fff; }
    .tag-pill { display: inline-block; padding: 3px 9px; border-radius: 10px; background: #f1f5f9; color: #475569; font-size: .75rem; text-decoration: none; transition: background var(--transition); }
    .tag-pill:hover { background: var(--theme-accent, #e83e8c); color: #fff; }

    /* DESCRIPTION */
    .post-description { font-size: .975rem; line-height: 1.8; color: #374151; }
    .post-description img { max-width: 100%; border-radius: 8px; }
    .post-description a { color: var(--theme-primary, #4a90d9); }

    /* GALLERY */
    .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 10px; margin: 24px 0; }
    .gallery-thumb-wrap { border-radius: 8px; overflow: hidden; aspect-ratio: 4/3; display: block; }
    .gallery-thumb-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform var(--transition); }
    .gallery-thumb-wrap:hover img { transform: scale(1.05); }

    /* Gallery without lightbox */
    .gallery-viewer { margin: 24px 0; }
    .gallery-main-img { width: 100%; border-radius: 10px; aspect-ratio: 16/9; object-fit: cover; margin-bottom: 10px; }
    .gallery-thumbs { display: flex; gap: 8px; flex-wrap: wrap; }
    .gallery-thumb-btn { cursor: pointer; border: 2px solid transparent; border-radius: 6px; overflow: hidden; padding: 0; background: none; width: 70px; height: 52px; }
    .gallery-thumb-btn img { width: 100%; height: 100%; object-fit: cover; }
    .gallery-thumb-btn.active { border-color: var(--theme-primary, #4a90d9); }

    /* ACTIONS */
    .post-actions { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; margin-top: 28px; padding-top: 24px; border-top: 1px solid #e9ecef; }
    .btn-offer {
      display: inline-flex; align-items: center; gap: 8px; padding: 10px 22px;
      background: var(--theme-primary, #4a90d9); color: #fff; font-weight: 700;
      border-radius: 8px; text-decoration: none; font-size: .9rem;
      transition: opacity var(--transition), transform var(--transition);
    }
    .btn-offer:hover { opacity: .88; color: #fff; transform: translateY(-1px); }
    .btn-back { display: inline-flex; align-items: center; gap: 6px; padding: 10px 18px; border: 1.5px solid #cbd5e1; color: #475569; border-radius: 8px; text-decoration: none; font-size: .9rem; transition: background var(--transition); }
    .btn-back:hover { background: #f1f5f9; color: #334155; }

    /* RELATED POSTS */
    .related-section { margin-top: 48px; }
    .related-section h3 { font-size: 1.1rem; font-weight: 700; color: #1e293b; margin-bottom: 16px; }
    .related-card { border: none; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,.07); height: 100%; background: #fff; transition: transform var(--transition), box-shadow var(--transition); }
    .related-card:hover { transform: translateY(-3px); box-shadow: 0 6px 18px rgba(0,0,0,.1); }
    .related-card-img { height: 140px; object-fit: cover; width: 100%; }
    .related-card-placeholder { height: 140px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 800; }
    .related-card-body { padding: 12px; }
    .related-card-title { font-size: .87rem; font-weight: 700; color: #1a202c; text-decoration: none; line-height: 1.35; }
    .related-card-title:hover { color: var(--theme-primary, #4a90d9); }

    /* FOOTER */
    .site-footer { background: var(--theme-footer-bg, #2d3748); color: var(--theme-footer-text, #a0aec0); padding: 36px 0 0; margin-top: 56px; }
    .site-footer a { color: var(--theme-footer-text, #a0aec0); text-decoration: none; }
    .site-footer a:hover { color: #fff; }
    .site-footer-social a { opacity: .7; transition: opacity var(--transition); display: inline-block; }
    .site-footer-social a:hover { opacity: 1; }
    .site-footer-copy { font-size: .78rem; border-top: 1px solid rgba(255,255,255,.1); padding: 12px 0; margin-top: 24px; text-align: center; opacity: .55; }
  </style>
</head>
<body>

<!-- HEADER -->
<header class="site-header">
  <div class="container">
    <div class="d-flex align-items-center gap-3">
      <?php if (!empty($brandingLogo)): ?>
        <a href="<?= $basePath ?>/">
          <img src="<?= htmlspecialchars($basePath . '/' . $brandingLogo) ?>" alt="<?= htmlspecialchars($homeTitle ?? '') ?>" class="site-logo" />
        </a>
      <?php endif; ?>
      <div>
        <a href="<?= $basePath ?>/" class="site-title d-block"><?= htmlspecialchars($homeTitle ?? '') ?></a>
        <?php if (!empty($homeSubtitle)): ?>
          <div class="site-subtitle"><?= htmlspecialchars($homeSubtitle) ?></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</header>

<!-- NAV -->
<?php if (!empty($navPages) || !empty($contactEnabled)): ?>
<nav class="site-nav">
  <div class="container">
    <div class="d-flex flex-wrap">
      <a href="<?= $basePath ?>/" class="nav-link">Blog</a>
      <?php foreach ($navPages as $_np): ?>
        <a href="<?= $basePath ?>/?page=<?= htmlspecialchars($_np['slug']) ?>" class="nav-link">
          <?= htmlspecialchars($_np['title']) ?>
        </a>
      <?php endforeach; ?>
      <?php if (!empty($contactEnabled)): ?>
        <a href="<?= $basePath ?>/?page=contact" class="nav-link">Kontakt</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
<?php endif; ?>

<!-- MAIN -->
<div class="container py-4">
  <div class="row g-4">

    <!-- Article column -->
    <div class="col-lg-8">

      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0 small">
          <li class="breadcrumb-item"><a href="<?= $basePath ?>/" style="color:var(--theme-primary,#4a90d9)">Strona główna</a></li>
          <li class="breadcrumb-item"><a href="<?= $basePath ?>/" style="color:var(--theme-primary,#4a90d9)">Blog</a></li>
          <?php if (!empty($link['category'])): ?>
            <li class="breadcrumb-item">
              <a href="<?= !empty($prettyUrls) ? $basePath . '/category/' . htmlspecialchars($link['category']['slug']) : $basePath . '/?category=' . htmlspecialchars($link['category']['slug']) ?>"
                 style="color:var(--theme-primary,#4a90d9)">
                <?= htmlspecialchars($link['category']['name']) ?>
              </a>
            </li>
          <?php endif; ?>
          <li class="breadcrumb-item active text-muted" aria-current="page">
            <?= htmlspecialchars(mb_substr($postTitle, 0, 50)) ?>…
          </li>
        </ol>
      </nav>

      <!-- Hero image -->
      <?php if (!empty($ogImageUrl)): ?>
        <img src="<?= htmlspecialchars($ogImageUrl) ?>" alt="<?= htmlspecialchars($postTitle) ?>" class="post-hero" />
      <?php endif; ?>

      <!-- Article -->
      <article class="post-article">
        <h1><?= htmlspecialchars($postTitle) ?></h1>

        <!-- Post meta -->
        <div class="post-meta">
          <?php if (!empty($link['category'])): ?>
            <span class="cat-badge" style="background:<?= htmlspecialchars($link['category']['color'] ?? '#6c757d') ?>">
              <?= htmlspecialchars($link['category']['name']) ?>
            </span>
          <?php endif; ?>
          <?php if (!empty($postCreatedAt)): ?>
            <span>
              <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16">
                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
              </svg>
              <?= htmlspecialchars(substr($postCreatedAt, 0, 10)) ?>
            </span>
          <?php endif; ?>
          <?php if (!empty($link['click_count'])): ?>
            <span>
              <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16">
                <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
              </svg>
              <?= (int)$link['click_count'] ?> odsłon
            </span>
          <?php endif; ?>
        </div>

        <!-- Tags -->
        <?php if (!empty($link['tags'])): ?>
          <div class="mb-4 d-flex flex-wrap gap-2">
            <?php foreach ($link['tags'] as $_tag): ?>
              <a href="<?= !empty($prettyUrls) ? $basePath . '/tag/' . urlencode($_tag['slug']) : $basePath . '/?tag=' . urlencode($_tag['slug']) ?>"
                 class="tag-pill">#<?= htmlspecialchars($_tag['name']) ?></a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- Description -->
        <?php if (!empty($postDescription)): ?>
          <div class="post-description mb-4">
            <?= Utils::sanitizeHtml($postDescription) ?>
          </div>
        <?php endif; ?>

        <!-- Gallery -->
        <?php if (!empty($galleryImages)): ?>
          <?php if (!empty($lightboxEnabled)): ?>
            <!-- Lightbox gallery -->
            <div class="gallery-grid mb-4">
              <?php foreach ($galleryImages as $_img): ?>
                <?php
                  $thumbUrl = !empty($_img['url']) ? $_img['url'] : $basePath . '/' . ltrim((string)$_img['path'], '/');
                ?>
                <a href="<?= htmlspecialchars($thumbUrl) ?>" data-lightbox="post-gallery"
                   data-title="<?= htmlspecialchars($postTitle) ?>" class="gallery-thumb-wrap">
                  <img src="<?= htmlspecialchars($thumbUrl) ?>" alt="<?= htmlspecialchars($postTitle) ?>" loading="lazy" />
                </a>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <!-- Simple gallery viewer -->
            <div class="gallery-viewer mb-4">
              <?php $firstImg = $galleryImages[0]; $firstUrl = !empty($firstImg['url']) ? $firstImg['url'] : $basePath . '/' . ltrim((string)$firstImg['path'], '/'); ?>
              <img id="galleryMain" src="<?= htmlspecialchars($firstUrl) ?>" alt="<?= htmlspecialchars($postTitle) ?>" class="gallery-main-img" />
              <?php if (count($galleryImages) > 1): ?>
                <div class="gallery-thumbs">
                  <?php foreach ($galleryImages as $_i => $_img): ?>
                    <?php $tUrl = !empty($_img['url']) ? $_img['url'] : $basePath . '/' . ltrim((string)$_img['path'], '/'); ?>
                    <button type="button" class="gallery-thumb-btn <?= $_i === 0 ? 'active' : '' ?>"
                            onclick="document.getElementById('galleryMain').src='<?= htmlspecialchars($tUrl) ?>'; this.closest('.gallery-thumbs').querySelectorAll('.gallery-thumb-btn').forEach(b=>b.classList.remove('active')); this.classList.add('active');">
                      <img src="<?= htmlspecialchars($tUrl) ?>" alt="" loading="lazy" />
                    </button>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>

        <!-- Reactions -->
        <?php require __DIR__ . '/../../src/PostReactions.php'; ?>

        <!-- Actions -->
        <div class="post-actions">
          <a href="<?= $basePath . '/' . htmlspecialchars($link['slug']) ?>" class="btn-offer">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
              <path d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5"/>
              <path d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0z"/>
            </svg>
            Przejdź do oferty
          </a>
          <a href="<?= $basePath ?>/" class="btn-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
              <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
            </svg>
            Powrót do bloga
          </a>
        </div>

      </article>

      <!-- Related posts -->
      <?php if (!empty($relatedPosts)): ?>
        <section class="related-section">
          <h3>Podobne wpisy</h3>
          <div class="row row-cols-1 row-cols-sm-3 g-3">
            <?php foreach ($relatedPosts as $_rp): ?>
              <?php
                $rHasImg = !empty($_rp['og_image']);
                $rImgSrc = $rHasImg ? $basePath . '/' . ltrim((string)$_rp['og_image'], '/') : null;
                $rLetter = mb_strtoupper(mb_substr((string)($_rp['page_title'] ?? 'P'), 0, 1));
                $rBg     = htmlspecialchars($_rp['category_color'] ?? '#7952b3');
              ?>
              <div class="col">
                <div class="related-card">
                  <?php if ($rHasImg): ?>
                    <img src="<?= htmlspecialchars($rImgSrc) ?>" alt="<?= htmlspecialchars($_rp['page_title'] ?? '') ?>" class="related-card-img" loading="lazy" />
                  <?php else: ?>
                    <div class="related-card-placeholder" style="background:<?= $rBg ?>18;color:<?= $rBg ?>;">
                      <?= htmlspecialchars($rLetter) ?>
                    </div>
                  <?php endif; ?>
                  <div class="related-card-body">
                    <a href="<?= $basePath ?>/blog/<?= htmlspecialchars($_rp['slug']) ?>" class="related-card-title d-block">
                      <?= htmlspecialchars($_rp['page_title'] ?? $_rp['slug']) ?>
                    </a>
                    <?php if (!empty($_rp['category_name'])): ?>
                      <div class="mt-1">
                        <span class="cat-badge" style="background:<?= htmlspecialchars($_rp['category_color'] ?? '#6c757d') ?>; font-size:.65rem">
                          <?= htmlspecialchars($_rp['category_name']) ?>
                        </span>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>

    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
      <?php if (!empty($sidebarData)): ?>
        <?php require __DIR__ . '/_sidebar.php'; ?>
      <?php endif; ?>
    </div>

  </div>
</div>

<!-- FOOTER -->
<footer class="site-footer">
  <div class="container">
    <div class="row align-items-start g-4">
      <div class="col-md-6">
        <div class="fw-bold fs-6 mb-1"><?= htmlspecialchars($homeTitle ?? '') ?></div>
        <?php if (!empty($homeSubtitle)): ?>
          <div class="small opacity-75 mb-2"><?= htmlspecialchars($homeSubtitle) ?></div>
        <?php endif; ?>
        <?php if (!empty($homeFooter)): ?>
          <div class="small mt-1" style="opacity:.65"><?= $homeFooter ?></div>
        <?php endif; ?>
      </div>
      <?php if (!empty($socialLinks) && is_array($socialLinks)): ?>
      <div class="col-md-6 text-md-end site-footer-social">
        <?php
          $_svgPaths = [
            'facebook'  => 'M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951',
            'instagram' => 'M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.9 3.9 0 0 0-1.417.923A3.9 3.9 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.9 3.9 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.9 3.9 0 0 0-.923-1.417A3.9 3.9 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599s.453.546.598.92c.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.5 2.5 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.5 2.5 0 0 1-.92-.598 2.5 2.5 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233s.008-2.388.046-3.231c.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92s.546-.453.92-.598c.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92m-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217m0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334',
            'twitter'   => 'M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z',
            'youtube'   => 'M8.051 1.999h.089c.822.003 4.987.033 6.11.335a2.01 2.01 0 0 1 1.415 1.42c.101.38.172.883.22 1.402l.01.104.022.26.008.104c.065.914.073 1.77.074 1.957v.075c-.001.194-.01 1.108-.082 2.06l-.008.105-.009.104c-.05.572-.124 1.14-.235 1.558a2.01 2.01 0 0 1-1.415 1.42c-1.16.312-5.569.334-6.18.335h-.142c-.309 0-1.587-.006-2.927-.052l-.17-.006-.087-.004-.171-.007-.171-.007c-1.11-.049-2.167-.128-2.654-.26a2.01 2.01 0 0 1-1.415-1.419c-.111-.417-.185-.986-.235-1.558L.09 9.82l-.008-.104A31 31 0 0 1 0 7.68v-.123c.002-.215.01-.958.064-1.778l.007-.103.003-.052.008-.104.022-.26.01-.104c.048-.519.119-1.023.22-1.402a2.01 2.01 0 0 1 1.415-1.42c.487-.13 1.544-.21 2.654-.26l.17-.007.172-.006.086-.003.171-.007A100 100 0 0 1 7.858 2zM6.4 5.209v4.818l4.157-2.408z',
            'linkedin'  => 'M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854zm4.943 12.248V6.169H2.542v7.225zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248S2.4 3.226 2.4 3.934c0 .694.521 1.248 1.327 1.248zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016l.016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225z',
            'tiktok'    => 'M9 0h1.98c.144.715.54 1.617 1.235 2.512C12.895 3.389 13.797 4 15 4v2c-1.753 0-3.07-.814-4-1.829V11a5 5 0 1 1-5-5v2a3 3 0 1 0 3 3z',
            'pinterest' => 'M8 0a8 8 0 0 0-2.915 15.452c-.07-.633-.134-1.606.027-2.297.146-.625.984-4.171.984-4.171s-.252-.504-.252-1.25c0-1.17.68-2.046 1.524-2.046.72 0 1.068.54 1.068 1.187 0 .723-.461 1.807-.699 2.814-.198.84.42 1.524 1.246 1.524 1.494 0 2.643-1.575 2.643-3.849 0-2.01-1.445-3.415-3.51-3.415-2.388 0-3.788 1.793-3.788 3.645 0 .722.277 1.495.624 1.919a.25.25 0 0 1 .058.239c-.064.264-.205.84-.233.957-.037.15-.124.182-.285.109C3.29 10.566 2.5 9.05 2.5 7.793c0-2.885 2.095-5.536 6.042-5.536 3.17 0 5.633 2.259 5.633 5.276 0 3.147-1.98 5.676-4.733 5.676-.925 0-1.796-.48-2.093-1.046l-.569 2.123c-.206.796-.765 1.79-1.14 2.395A8 8 0 1 0 8 0',
          ];
        ?>
        <?php foreach ($socialLinks as $_net => $_url): ?>
          <?php if (!empty($_url) && isset($_svgPaths[$_net])): ?>
            <a href="<?= htmlspecialchars($_url) ?>" target="_blank" rel="noopener noreferrer"
               class="ms-3" title="<?= htmlspecialchars(ucfirst((string)$_net)) ?>">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                <path d="<?= $_svgPaths[$_net] ?>"/>
              </svg>
            </a>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <div class="site-footer-copy">
      &copy; <?= date('Y') ?> <?= htmlspecialchars($homeTitle ?? '') ?>
    </div>
  </div>
</footer>

<?php if (!empty($homeFooterCode)) echo $homeFooterCode; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($lightboxEnabled)): ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
<?php endif; ?>
</body>
</html>
