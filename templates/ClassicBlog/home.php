<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($homeTitle) ?></title>
  <?php if (!empty($homeMetaDescription)): ?>
    <meta name="description" content="<?= htmlspecialchars(strip_tags($homeMetaDescription)) ?>">
  <?php endif; ?>
  <meta property="og:type"  content="website">
  <meta property="og:title" content="<?= htmlspecialchars($homeTitle) ?>">
  <?php if (!empty($homeMetaDescription)): ?>
    <meta property="og:description" content="<?= htmlspecialchars(strip_tags($homeMetaDescription)) ?>">
  <?php endif; ?>
  <?php require __DIR__ . '/_head_css.php'; ?>
</head>
<body>

<?php require __DIR__ . '/_navbar.php'; ?>

<!-- ============================================================
     HERO SLIDER
     Wyświetla pierwsze 3 wpisy z obrazem tła.
     Bootstrap carousel-fade + Ken Burns effect.
============================================================ -->
<?php
$_slides = $sliderPosts ?? [];
if (!empty($_slides)):
?>
<section class="hero-slider">
  <div id="heroCarousel" class="carousel carousel-fade slide" data-bs-ride="carousel" data-bs-interval="5500">

    <!-- Dots -->
    <div class="carousel-indicators">
      <?php foreach ($_slides as $_si => $_slide): ?>
        <button type="button"
                data-bs-target="#heroCarousel"
                data-bs-slide-to="<?= $_si ?>"
                <?= $_si === 0 ? 'class="active" aria-current="true"' : '' ?>
                aria-label="Slajd <?= $_si + 1 ?>"></button>
      <?php endforeach; ?>
    </div>

    <div class="carousel-inner">
      <?php foreach ($_slides as $_si => $_slide): ?>
        <?php
          $_sHasImg = !empty($_slide['og_image']) && !empty($blogShowImages);
          $_sImgSrc = $_sHasImg ? $basePath . '/' . ltrim((string)$_slide['og_image'], '/') : null;
          $_sBgColor = htmlspecialchars($_slide['category_color'] ?? '#1c2331');
          $_sTitle   = htmlspecialchars($_slide['page_title'] ?? $_slide['slug'] ?? '');
          $_sExcerpt = strip_tags((string)($_slide['page_description'] ?? ''));
          $_sExcerpt = mb_substr($_sExcerpt, 0, 140);
          $_sBgStyle = $_sHasImg
            ? 'background-image:url(' . htmlspecialchars($_sImgSrc) . ')'
            : 'background:linear-gradient(135deg,' . $_sBgColor . 'dd 0%,' . $_sBgColor . '99 100%)';
        ?>
        <div class="carousel-item <?= $_si === 0 ? 'active' : '' ?>">
          <div class="hero-slide">
            <div class="hero-slide-bg" style="<?= $_sBgStyle ?>"></div>
            <div class="hero-slide-overlay"></div>
            <div class="hero-slide-content">
              <div class="container">
                <?php if (!empty($_slide['category_name'])): ?>
                  <div>
                    <span class="hero-category-badge"
                          style="background:<?= $_sBgColor ?>">
                      <?= htmlspecialchars($_slide['category_name']) ?>
                    </span>
                  </div>
                <?php endif; ?>
                <h2 class="hero-slide-title"><?= $_sTitle ?></h2>
                <?php if ($_sExcerpt !== ''): ?>
                  <p class="hero-slide-excerpt"><?= htmlspecialchars($_sExcerpt) ?>…</p>
                <?php endif; ?>
                <div class="d-flex align-items-center gap-3">
                  <a href="<?= $basePath ?>/blog/<?= htmlspecialchars($_slide['slug']) ?>"
                     class="hero-slide-btn">
                    Czytaj więcej
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                      <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8"/>
                    </svg>
                  </a>
                  <span class="hero-slide-meta">
                    <?= htmlspecialchars(substr((string)($_slide['created_at'] ?? ''), 0, 10)) ?>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div><!-- /.carousel-inner -->

    <!-- Controls -->
    <button class="carousel-control-prev" type="button"
            data-bs-target="#heroCarousel" data-bs-slide="prev" aria-label="Poprzedni">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    </button>
    <button class="carousel-control-next" type="button"
            data-bs-target="#heroCarousel" data-bs-slide="next" aria-label="Następny">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
    </button>

  </div>
</section>
<?php endif; ?>

<!-- Active filter / search info -->
<?php if (!empty($activeCategory) || !empty($activeTag) || $searchQuery !== ''): ?>
<div class="active-filter-bar">
  <div class="container d-flex align-items-center justify-content-between">
    <span>
      <?php if ($searchQuery !== ''): ?>
        Wyniki wyszukiwania: <strong>&ldquo;<?= htmlspecialchars($searchQuery) ?>&rdquo;</strong>
        <?php if (!empty($totalCount)): ?>
          <span style="opacity:.6;font-size:.85em">(<?= (int)$totalCount ?>)</span>
        <?php endif; ?>
      <?php elseif (!empty($activeCategory)): ?>
        Kategoria:
        <strong style="color:<?= htmlspecialchars($activeCategory['color'] ?? 'inherit') ?>">
          <?= htmlspecialchars($activeCategory['name']) ?>
        </strong>
      <?php else: ?>
        Tag: <strong>#<?= htmlspecialchars($activeTag['name']) ?></strong>
      <?php endif; ?>
    </span>
    <a href="<?= $basePath ?>/" class="text-secondary small">✕ Wyczyść</a>
  </div>
</div>
<?php endif; ?>

<!-- ============================================================
     MAIN CONTENT — Posts + Sidebar
============================================================ -->
<div class="container py-5">
  <div class="row g-4">

    <!-- POSTS COLUMN (col-lg-8) -->
    <div class="col-lg-8">

      <?php if (empty($blogPosts)): ?>
        <!-- Empty state -->
        <div class="empty-state">
          <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" fill="currentColor" viewBox="0 0 16 16">
            <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783"/>
          </svg>
          <h3 class="h5 mt-2">Brak wpisów do wyświetlenia</h3>
          <p class="small">
            <?php if (!empty($activeCategory) || !empty($activeTag)): ?>
              W tej kategorii / z tym tagiem nie ma jeszcze wpisów.
            <?php else: ?>
              Dodaj pierwszy wpis w panelu admina.
            <?php endif; ?>
          </p>
          <?php if (!empty($activeCategory) || !empty($activeTag)): ?>
            <a href="<?= $basePath ?>/" class="btn btn-sm btn-outline-secondary mt-2">Pokaż wszystkie wpisy</a>
          <?php endif; ?>
        </div>

      <?php else: ?>

        <?php
          /* ----- Wyróżniony (duży) wpis: pierwszy z listy ----- */
          $_featuredPost = $blogPosts[0];
          $_restPosts    = array_slice($blogPosts, 1);

          $_fHasImg = !empty($_featuredPost['og_image']) && !empty($blogShowImages);
          $_fImgSrc = $_fHasImg ? $basePath . '/' . ltrim((string)$_featuredPost['og_image'], '/') : null;
          $_fLetter = mb_strtoupper(mb_substr((string)($_featuredPost['page_title'] ?? 'P'), 0, 1));
          $_fBgColor = htmlspecialchars($_featuredPost['category_color'] ?? '#1c2331');
          $_fDesc    = strip_tags((string)($_featuredPost['page_description'] ?? ''));
          $_fDesc    = mb_strlen($_fDesc) > 200 ? mb_substr($_fDesc, 0, 200) . '…' : $_fDesc;
        ?>

        <!-- Heading -->
        <h2 class="section-heading">
          <?php if (!empty($searchQuery)): ?>
            Wyniki wyszukiwania
          <?php elseif (!empty($activeCategory)): ?>
            <?= htmlspecialchars($activeCategory['name']) ?>
          <?php elseif (!empty($activeTag)): ?>
            #<?= htmlspecialchars($activeTag['name']) ?>
          <?php else: ?>
            Najnowsze wpisy
          <?php endif; ?>
        </h2>

        <!-- Featured large card -->
        <div class="featured-card mb-4">
          <div class="featured-card-img-wrap">
            <?php if ($_fHasImg): ?>
              <img src="<?= htmlspecialchars($_fImgSrc) ?>"
                   alt="<?= htmlspecialchars($_featuredPost['page_title'] ?? '') ?>"
                   loading="lazy">
            <?php else: ?>
              <div class="featured-card-placeholder"
                   style="background:<?= $_fBgColor ?>18; color:<?= $_fBgColor ?>">
                <?= htmlspecialchars($_fLetter) ?>
              </div>
            <?php endif; ?>
          </div>
          <div class="featured-card-body">
            <?php if (!empty($_featuredPost['category_name'])): ?>
              <div class="mb-2">
                <span class="cat-badge" style="background:<?= $_fBgColor ?>">
                  <?= htmlspecialchars($_featuredPost['category_name']) ?>
                </span>
              </div>
            <?php endif; ?>
            <h3 class="post-card-title" style="font-size:1.2rem; margin-bottom:10px">
              <a href="<?= $basePath ?>/blog/<?= htmlspecialchars($_featuredPost['slug']) ?>">
                <?= htmlspecialchars($_featuredPost['page_title'] ?? $_featuredPost['slug']) ?>
              </a>
            </h3>
            <?php if ($_fDesc !== ''): ?>
              <p class="post-card-excerpt mb-0"><?= htmlspecialchars($_fDesc) ?></p>
            <?php endif; ?>
            <div class="post-card-meta mt-auto">
              <span>
                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" viewBox="0 0 16 16" style="vertical-align:-1px">
                  <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
                </svg>
                <?= htmlspecialchars(substr((string)($_featuredPost['created_at'] ?? ''), 0, 10)) ?>
              </span>
              <?php if (!empty($_featuredPost['click_count'])): ?>
                <span>
                  <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" viewBox="0 0 16 16" style="vertical-align:-1px">
                    <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/><path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
                  </svg>
                  <?= (int)$_featuredPost['click_count'] ?> odsłon
                </span>
              <?php endif; ?>
              <a href="<?= $basePath ?>/blog/<?= htmlspecialchars($_featuredPost['slug']) ?>"
                 class="read-more ms-auto">Czytaj więcej →</a>
            </div>
          </div>
        </div>

        <!-- Grid pozostałych wpisów (2 kolumny) -->
        <?php if (!empty($_restPosts)): ?>
          <div class="row row-cols-1 row-cols-sm-2 g-4">
            <?php foreach ($_restPosts as $_post): ?>
              <?php
                $_pHasImg = !empty($_post['og_image']) && !empty($blogShowImages);
                $_pImgSrc = $_pHasImg ? $basePath . '/' . ltrim((string)$_post['og_image'], '/') : null;
                $_pLetter = mb_strtoupper(mb_substr((string)($_post['page_title'] ?? 'P'), 0, 1));
                $_pBg     = htmlspecialchars($_post['category_color'] ?? '#6c757d');
                $_pDesc   = strip_tags((string)($_post['page_description'] ?? ''));
                $_pDesc   = mb_strlen($_pDesc) > $blogDescLength
                             ? mb_substr($_pDesc, 0, $blogDescLength) . '…'
                             : $_pDesc;
              ?>
              <div class="col d-flex">
                <div class="post-card w-100">
                  <?php if ($_pHasImg): ?>
                    <img src="<?= htmlspecialchars($_pImgSrc) ?>"
                         alt="<?= htmlspecialchars($_post['page_title'] ?? '') ?>"
                         class="post-card-img" loading="lazy">
                  <?php else: ?>
                    <div class="post-card-placeholder"
                         style="background:<?= $_pBg ?>18; color:<?= $_pBg ?>">
                      <?= htmlspecialchars($_pLetter) ?>
                    </div>
                  <?php endif; ?>
                  <div class="post-card-body">
                    <?php if (!empty($_post['category_name'])): ?>
                      <div class="mb-2">
                        <span class="cat-badge" style="background:<?= htmlspecialchars($_post['category_color'] ?? '#6c757d') ?>">
                          <?= htmlspecialchars($_post['category_name']) ?>
                        </span>
                      </div>
                    <?php endif; ?>
                    <h3 class="post-card-title">
                      <a href="<?= $basePath ?>/blog/<?= htmlspecialchars($_post['slug']) ?>">
                        <?= htmlspecialchars($_post['page_title'] ?? $_post['slug']) ?>
                      </a>
                    </h3>
                    <?php if ($_pDesc !== ''): ?>
                      <p class="post-card-excerpt"><?= htmlspecialchars($_pDesc) ?></p>
                    <?php endif; ?>
                    <div class="post-card-meta">
                      <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" viewBox="0 0 16 16" style="vertical-align:-1px">
                          <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
                        </svg>
                        <?= htmlspecialchars(substr((string)($_post['created_at'] ?? ''), 0, 10)) ?>
                      </span>
                      <a href="<?= $basePath ?>/blog/<?= htmlspecialchars($_post['slug']) ?>"
                         class="read-more ms-auto">Czytaj →</a>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- Pagination -->
        <?php if (!empty($totalPages) && $totalPages > 1): ?>
          <?php
            $_rPParams = [];
            if (isset($_GET['category']) && is_string($_GET['category']) && $_GET['category'] !== '') {
              $_rPParams['category'] = $_GET['category'];
            }
            if (isset($_GET['tag']) && is_string($_GET['tag']) && $_GET['tag'] !== '') {
              $_rPParams['tag'] = $_GET['tag'];
            }
            if (!empty($searchQuery)) {
              $_rPParams['s'] = $searchQuery;
            }
            $_rPBase = $basePath . '/?' . (!empty($_rPParams) ? http_build_query($_rPParams) . '&' : '');
            $_rCp    = (int)$currentPage;
            $_rTp    = (int)$totalPages;
          ?>
          <div class="pagination-wrap">
            <nav aria-label="Paginacja">
              <ul class="pagination">
                <?php if ($_rCp > 1): ?>
                  <li class="page-item">
                    <a class="page-link" href="<?= htmlspecialchars($_rPBase . 'p=' . ($_rCp - 1), ENT_QUOTES) ?>">
                      &laquo; Poprzednia
                    </a>
                  </li>
                <?php endif; ?>
                <?php for ($_rPn = max(1, $_rCp - 2); $_rPn <= min($_rTp, $_rCp + 2); $_rPn++): ?>
                  <li class="page-item <?= $_rPn === $_rCp ? 'active' : '' ?>">
                    <a class="page-link" href="<?= htmlspecialchars($_rPBase . 'p=' . $_rPn, ENT_QUOTES) ?>">
                      <?= $_rPn ?>
                    </a>
                  </li>
                <?php endfor; ?>
                <?php if ($_rCp < $_rTp): ?>
                  <li class="page-item">
                    <a class="page-link" href="<?= htmlspecialchars($_rPBase . 'p=' . ($_rCp + 1), ENT_QUOTES) ?>">
                      Następna &raquo;
                    </a>
                  </li>
                <?php endif; ?>
              </ul>
            </nav>
          </div>
        <?php endif; ?>

      <?php endif; /* empty($blogPosts) */ ?>
    </div><!-- /.col-lg-8 -->

    <!-- SIDEBAR (col-lg-4) -->
    <div class="col-lg-4">
      <?php require __DIR__ . '/_sidebar.php'; ?>
    </div>

  </div><!-- /.row -->
</div><!-- /.container -->

<?php require __DIR__ . '/_footer.php'; ?>
