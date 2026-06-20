<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($postTitle) ?> — <?= htmlspecialchars($homeTitle ?? '') ?></title>
  <meta name="description" content="<?= htmlspecialchars(mb_substr(strip_tags((string)($postDescription ?? '')), 0, 160)) ?>">

  <!-- Open Graph -->
  <meta property="og:type"        content="article">
  <meta property="og:title"       content="<?= htmlspecialchars($postTitle) ?>">
  <meta property="og:description" content="<?= htmlspecialchars(mb_substr(strip_tags((string)($postDescription ?? '')), 0, 200)) ?>">
  <meta property="og:url"         content="<?= htmlspecialchars($shareUrl) ?>">
  <?php if (!empty($ogImageUrl)): ?>
    <meta property="og:image"        content="<?= htmlspecialchars($ogImageUrl) ?>">
    <meta property="og:image:width"  content="1200">
    <meta property="og:image:height" content="630">
  <?php endif; ?>
  <?php if (!empty($postCreatedAt)): ?>
    <meta property="article:published_time" content="<?= htmlspecialchars($postCreatedAt) ?>">
  <?php endif; ?>

  <!-- Twitter Card -->
  <meta name="twitter:card"        content="summary_large_image">
  <meta name="twitter:title"       content="<?= htmlspecialchars($postTitle) ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars(mb_substr(strip_tags((string)($postDescription ?? '')), 0, 200)) ?>">
  <?php if (!empty($ogImageUrl)): ?>
    <meta name="twitter:image" content="<?= htmlspecialchars($ogImageUrl) ?>">
  <?php endif; ?>

  <?php require __DIR__ . '/_head_css.php'; ?>
</head>
<body>

<?php require __DIR__ . '/_navbar.php'; ?>

<!-- BREADCRUMB BAR -->
<div class="post-breadcrumb-bar">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a href="<?= $basePath ?>/">Strona główna</a>
        </li>
        <li class="breadcrumb-item">
          <a href="<?= $basePath ?>/">Blog</a>
        </li>
        <?php if (!empty($link['category'])): ?>
          <li class="breadcrumb-item">
            <a href="<?= !empty($prettyUrls) ? $basePath . '/category/' . urlencode($link['category']['slug']) : $basePath . '/?category=' . urlencode($link['category']['slug']) ?>">
              <?= htmlspecialchars($link['category']['name']) ?>
            </a>
          </li>
        <?php endif; ?>
        <li class="breadcrumb-item active" aria-current="page">
          <?= htmlspecialchars(mb_substr($postTitle, 0, 55)) ?>…
        </li>
      </ol>
    </nav>
  </div>
</div>

<!-- MAIN -->
<div class="container py-4">
  <div class="row g-4">

    <!-- ARTICLE COLUMN (col-lg-8) -->
    <div class="col-lg-8">

      <!-- Hero image -->
      <?php if (!empty($ogImageUrl)): ?>
        <img src="<?= htmlspecialchars($ogImageUrl) ?>"
             alt="<?= htmlspecialchars($postTitle) ?>"
             class="post-hero">
      <?php endif; ?>

      <!-- Article card -->
      <article class="post-article">

        <!-- Category badge -->
        <?php if (!empty($link['category'])): ?>
          <div class="mb-3">
            <span class="cat-badge"
                  style="background:<?= htmlspecialchars($link['category']['color'] ?? '#6c757d') ?>">
              <a href="<?= !empty($prettyUrls) ? $basePath . '/category/' . urlencode($link['category']['slug']) : $basePath . '/?category=' . urlencode($link['category']['slug']) ?>"
                 style="color:#fff; text-decoration:none">
                <?= htmlspecialchars($link['category']['name']) ?>
              </a>
            </span>
          </div>
        <?php endif; ?>

        <!-- Title -->
        <h1><?= htmlspecialchars($postTitle) ?></h1>

        <!-- Lead (pierwsze zdanie jako intro) -->
        <?php
          $_leadText = strip_tags((string)($postDescription ?? ''));
          $_leadText = mb_substr($_leadText, 0, 180);
          if ($_leadText !== '' && mb_strlen($postDescription ?? '') > 10):
        ?>
          <p class="post-lead"><?= htmlspecialchars($_leadText) ?>…</p>
        <?php endif; ?>

        <!-- Meta bar -->
        <div class="post-meta-bar">
          <?php if (!empty($postCreatedAt)): ?>
            <span>
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16">
                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
              </svg>
              <?= htmlspecialchars(substr($postCreatedAt, 0, 10)) ?>
            </span>
          <?php endif; ?>
          <?php if (!empty($link['click_count'])): ?>
            <span>
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16">
                <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
              </svg>
              <?= (int)$link['click_count'] ?> odsłon
            </span>
          <?php endif; ?>
          <?php if (!empty($link['category'])): ?>
            <span>
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16">
                <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5zm2-1a1 1 0 0 0-1 1v12.566l4.723-2.482a.5.5 0 0 1 .554 0L13 14.566V2a1 1 0 0 0-1-1z"/>
              </svg>
              <?= htmlspecialchars($link['category']['name']) ?>
            </span>
          <?php endif; ?>
        </div>

        <!-- Tags -->
        <?php if (!empty($link['tags'])): ?>
          <div class="mb-4 d-flex flex-wrap gap-2">
            <?php foreach ($link['tags'] as $_tag): ?>
              <a href="<?= !empty($prettyUrls) ? $basePath . '/tag/' . urlencode($_tag['slug']) : $basePath . '/?tag=' . urlencode($_tag['slug']) ?>"
                 class="tag-pill">
                #<?= htmlspecialchars($_tag['name']) ?>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- Post body / description -->
        <?php if (!empty($postDescription)): ?>
          <div class="post-body">
            <?= Utils::sanitizeHtml($postDescription) ?>
          </div>
        <?php endif; ?>

        <!-- Gallery -->
        <?php if (!empty($galleryImages)): ?>
          <?php if (!empty($lightboxEnabled)): ?>
            <div class="gallery-grid">
              <?php foreach ($galleryImages as $_img): ?>
                <?php $gUrl = !empty($_img['url']) ? $_img['url'] : $basePath . '/' . ltrim((string)$_img['path'], '/'); ?>
                <a href="<?= htmlspecialchars($gUrl) ?>"
                   data-lightbox="post-gallery"
                   data-title="<?= htmlspecialchars($postTitle) ?>"
                   class="gallery-thumb-wrap">
                  <img src="<?= htmlspecialchars($gUrl) ?>"
                       alt="<?= htmlspecialchars($postTitle) ?>" loading="lazy">
                </a>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="gallery-viewer">
              <?php $gFirst = $galleryImages[0]; $gFirstUrl = !empty($gFirst['url']) ? $gFirst['url'] : $basePath . '/' . ltrim((string)$gFirst['path'], '/'); ?>
              <img id="galleryMain" src="<?= htmlspecialchars($gFirstUrl) ?>"
                   alt="<?= htmlspecialchars($postTitle) ?>"
                   class="gallery-main-img">
              <?php if (count($galleryImages) > 1): ?>
                <div class="gallery-thumbs">
                  <?php foreach ($galleryImages as $_gi => $_gImg): ?>
                    <?php $gTUrl = !empty($_gImg['url']) ? $_gImg['url'] : $basePath . '/' . ltrim((string)$_gImg['path'], '/'); ?>
                    <button type="button"
                            class="gallery-thumb-btn <?= $_gi === 0 ? 'active' : '' ?>"
                            onclick="document.getElementById('galleryMain').src='<?= htmlspecialchars($gTUrl) ?>';this.closest('.gallery-thumbs').querySelectorAll('.gallery-thumb-btn').forEach(b=>b.classList.remove('active'));this.classList.add('active')">
                      <img src="<?= htmlspecialchars($gTUrl) ?>" alt="" loading="lazy">
                    </button>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>

        <!-- Reactions -->
        <?php require __DIR__ . '/../../src/PostReactions.php'; ?>

        <!-- CTA: Przejdź do oferty -->
        <div class="d-flex flex-wrap gap-3 mt-4 pt-3" style="border-top:1px solid var(--clr-border)">
          <a href="<?= $basePath . '/' . htmlspecialchars($link['slug']) ?>"
             class="btn btn-sm px-4 py-2 fw-bold text-white"
             style="background:var(--clr-primary);border-radius:var(--radius)">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="me-1" viewBox="0 0 16 16">
              <path d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5"/>
              <path d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0z"/>
            </svg>
            Przejdź do oferty
          </a>
          <a href="<?= $basePath ?>/" class="post-back-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16">
              <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
            </svg>
            Powrót do bloga
          </a>
        </div>

        <!-- Share buttons -->
        <?php if (!empty($shareUrl)): ?>
          <div class="post-share">
            <span class="small text-muted me-2 fw-600">Udostępnij:</span>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($shareUrl) ?>"
               target="_blank" rel="noopener noreferrer"
               class="btn btn-sm me-1"
               style="background:#1877f2;color:#fff;border-radius:var(--radius);font-size:.78rem">
              Facebook
            </a>
            <a href="https://twitter.com/intent/tweet?url=<?= urlencode($shareUrl) ?>&text=<?= urlencode($postTitle) ?>"
               target="_blank" rel="noopener noreferrer"
               class="btn btn-sm me-1"
               style="background:#000;color:#fff;border-radius:var(--radius);font-size:.78rem">
              X / Twitter
            </a>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($shareUrl) ?>"
               target="_blank" rel="noopener noreferrer"
               class="btn btn-sm"
               style="background:#0077b5;color:#fff;border-radius:var(--radius);font-size:.78rem">
              LinkedIn
            </a>
          </div>
        <?php endif; ?>

      </article><!-- /.post-article -->

      <!-- RELATED POSTS -->
      <?php if (!empty($relatedPosts)): ?>
        <section class="related-section">
          <h3 class="section-heading">Podobne wpisy</h3>
          <div class="row row-cols-1 row-cols-sm-3 g-3">
            <?php foreach ($relatedPosts as $_rp): ?>
              <?php
                $_rpHasImg = !empty($_rp['og_image']);
                $_rpImgSrc = $_rpHasImg ? $basePath . '/' . ltrim((string)$_rp['og_image'], '/') : null;
                $_rpLetter = mb_strtoupper(mb_substr((string)($_rp['page_title'] ?? 'P'), 0, 1));
                $_rpBg     = htmlspecialchars($_rp['category_color'] ?? '#6c757d');
              ?>
              <div class="col">
                <div class="related-card">
                  <?php if ($_rpHasImg): ?>
                    <img src="<?= htmlspecialchars($_rpImgSrc) ?>"
                         alt="<?= htmlspecialchars($_rp['page_title'] ?? '') ?>"
                         loading="lazy">
                  <?php else: ?>
                    <div class="related-card-placeholder"
                         style="background:<?= $_rpBg ?>18; color:<?= $_rpBg ?>">
                      <?= htmlspecialchars($_rpLetter) ?>
                    </div>
                  <?php endif; ?>
                  <div class="related-card-body">
                    <a href="<?= $basePath ?>/blog/<?= htmlspecialchars($_rp['slug']) ?>"
                       class="related-card-title d-block">
                      <?= htmlspecialchars($_rp['page_title'] ?? $_rp['slug']) ?>
                    </a>
                    <?php if (!empty($_rp['category_name'])): ?>
                      <div class="mt-2">
                        <span class="cat-badge"
                              style="background:<?= htmlspecialchars($_rp['category_color'] ?? '#6c757d') ?>; font-size:.63rem">
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

    </div><!-- /.col-lg-8 -->

    <!-- SIDEBAR (col-lg-4) -->
    <div class="col-lg-4">
      <?php if (!empty($sidebarData)): ?>
        <?php require __DIR__ . '/_sidebar.php'; ?>
      <?php endif; ?>
    </div>

  </div><!-- /.row -->
</div><!-- /.container -->

<?php require __DIR__ . '/_footer.php'; ?>
