<?php
/* Shared sidebar partial — widgety konfigurowalne przez admin panel.
 * Zmienne: $sidebarData (array), $basePath, $prettyUrls
 */
$_sbSvgPaths = [
  'facebook'   => 'M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951',
  'instagram'  => 'M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.9 3.9 0 0 0-1.417.923A3.9 3.9 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.9 3.9 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.9 3.9 0 0 0-.923-1.417A3.9 3.9 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599s.453.546.598.92c.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.5 2.5 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.5 2.5 0 0 1-.92-.598 2.5 2.5 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233s.008-2.388.046-3.231c.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92s.546-.453.92-.598c.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92m-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217m0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334',
  'twitter'    => 'M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z',
  'youtube'    => 'M8.051 1.999h.089c.822.003 4.987.033 6.11.335a2.01 2.01 0 0 1 1.415 1.42c.101.38.172.883.22 1.402l.01.104.022.26.008.104c.065.914.073 1.77.074 1.957v.075c-.001.194-.01 1.108-.082 2.06l-.008.105-.009.104c-.05.572-.124 1.14-.235 1.558a2.01 2.01 0 0 1-1.415 1.42c-1.16.312-5.569.334-6.18.335h-.142c-.309 0-1.587-.006-2.927-.052l-.17-.006-.087-.004-.171-.007-.171-.007c-1.11-.049-2.167-.128-2.654-.26a2.01 2.01 0 0 1-1.415-1.419c-.111-.417-.185-.986-.235-1.558L.09 9.82l-.008-.104A31 31 0 0 1 0 7.68v-.123c.002-.215.01-.958.064-1.778l.007-.103.003-.052.008-.104.022-.26.01-.104c.048-.519.119-1.023.22-1.402a2.01 2.01 0 0 1 1.415-1.42c.487-.13 1.544-.21 2.654-.26l.17-.007.172-.006.086-.003.171-.007A100 100 0 0 1 7.858 2zM6.4 5.209v4.818l4.157-2.408z',
  'linkedin'   => 'M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854zm4.943 12.248V6.169H2.542v7.225zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248S2.4 3.226 2.4 3.934c0 .694.521 1.248 1.327 1.248zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016l.016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225z',
  'tiktok'     => 'M9 0h1.98c.144.715.54 1.617 1.235 2.512C12.895 3.389 13.797 4 15 4v2c-1.753 0-3.07-.814-4-1.829V11a5 5 0 1 1-5-5v2a3 3 0 1 0 3 3z',
  'pinterest'  => 'M8 0a8 8 0 0 0-2.915 15.452c-.07-.633-.134-1.606.027-2.297.146-.625.984-4.171.984-4.171s-.252-.504-.252-1.25c0-1.17.68-2.046 1.524-2.046.72 0 1.068.54 1.068 1.187 0 .723-.461 1.807-.699 2.814-.198.84.42 1.524 1.246 1.524 1.494 0 2.643-1.575 2.643-3.849 0-2.01-1.445-3.415-3.51-3.415-2.388 0-3.788 1.793-3.788 3.645 0 .722.277 1.495.624 1.919a.25.25 0 0 1 .058.239c-.064.264-.205.84-.233.957-.037.15-.124.182-.285.109C3.29 10.566 2.5 9.05 2.5 7.793c0-2.885 2.095-5.536 6.042-5.536 3.17 0 5.633 2.259 5.633 5.276 0 3.147-1.98 5.676-4.733 5.676-.925 0-1.796-.48-2.093-1.046l-.569 2.123c-.206.796-.765 1.79-1.14 2.395A8 8 0 1 0 8 0',
  'custom_url' => 'M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m7.5-6.923c-.67.204-1.335.82-1.887 1.855q-.215.403-.395.872c.705.157 1.472.257 2.282.287zM4.249 3.539q.214-.577.481-1.078a7 7 0 0 1 .597-.933A7 7 0 0 0 3.051 3.05q.544.277 1.198.49zM3.509 7.5c.036-1.07.188-2.087.436-3.008a9 9 0 0 1-1.565-.667A6.96 6.96 0 0 0 1.018 7.5zm1.4-2.741a12.3 12.3 0 0 0-.4 2.741H7.5V5.091c-.91-.03-1.783-.145-2.591-.332M8.5 5.09V7.5h2.99a12.3 12.3 0 0 0-.399-2.741c-.808.187-1.681.301-2.591.332zM4.51 8.5c.035.987.176 1.914.399 2.741A13.6 13.6 0 0 1 7.5 10.91V8.5zm3.99 0v2.409c.91.03 1.783.145 2.591.332.223-.827.364-1.754.4-2.741zm-3.282 3.696q.18.469.395.872c.552 1.035 1.218 1.65 1.887 1.855V11.91c-.81.03-1.577.13-2.282.287zm.11 2.276a7 7 0 0 1-.598-.933 9 9 0 0 1-.481-1.079 8.4 8.4 0 0 0-1.198.49 7 7 0 0 0 2.276 1.522zm-1.383-2.964A13.4 13.4 0 0 1 3.508 8.5h-2.49a6.96 6.96 0 0 0 1.362 3.675c.47-.258.995-.482 1.565-.667m6.728 2.964a7 7 0 0 0 2.275-1.521 8.4 8.4 0 0 0-1.197-.49 9 9 0 0 1-.481 1.078 7 7 0 0 1-.597.933M8.5 11.909v3.014c.67-.204 1.335-.82 1.887-1.855q.216-.403.395-.872A12.6 12.6 0 0 0 8.5 11.91zm3.555-.401c.57.185 1.095.409 1.565.667A6.96 6.96 0 0 0 14.982 8.5h-2.49a13.4 13.4 0 0 1-.437 3.008M14.982 7.5a6.96 6.96 0 0 0-1.362-3.675c-.47.258-.995.482-1.565.667.248.92.4 1.938.437 3.008zM11.27 2.461q.266.502.482 1.078a8.4 8.4 0 0 0 1.196-.49 7 7 0 0 0-2.275-1.52c.218.283.418.597.597.932m-.488 1.343a8 8 0 0 0-.395-.872C9.835 1.897 9.17 1.282 8.5 1.077V4.09c.81-.03 1.577-.13 2.282-.287z',
];
?>

<!-- SIDEBAR: wyszukiwarka (zawsze na górze) -->
<div class="sidebar-widget">
  <div class="sidebar-widget-header">
    <h3 class="sidebar-widget-title">Szukaj</h3>
  </div>
  <div class="sidebar-widget-body">
    <form class="sidebar-search-row" action="<?= $basePath ?>/" method="get" role="search">
      <input type="search" name="s"
             placeholder="Wpisz szukaną frazę…"
             value="<?= htmlspecialchars($_GET['s'] ?? '') ?>"
             aria-label="Szukaj">
      <button type="submit" class="sidebar-search-btn" aria-label="Szukaj">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
          <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
        </svg>
      </button>
    </form>
  </div>
</div>

<?php foreach ($sidebarData as $_sw): ?>

  <!-- ---- POPULARNE / LOSOWE WPISY ---- -->
  <?php if ($_sw['type'] === 'popular_posts' || $_sw['type'] === 'random_posts'): ?>
  <div class="sidebar-widget">
    <?php if (!empty($_sw['title'])): ?>
      <div class="sidebar-widget-header">
        <h3 class="sidebar-widget-title"><?= htmlspecialchars($_sw['title']) ?></h3>
      </div>
    <?php endif; ?>
    <div class="sidebar-widget-body">
      <?php if (empty($_sw['data'])): ?>
        <p class="small text-muted mb-0">Brak wpisów.</p>
      <?php else: ?>
        <?php foreach ($_sw['data'] as $_sp): ?>
          <?php
            $_spImg = !empty($_sp['og_image']) ? $basePath . '/' . ltrim((string)$_sp['og_image'], '/') : null;
            $_spLetter = mb_strtoupper(mb_substr((string)($_sp['page_title'] ?? 'P'), 0, 1));
            $_spColor  = htmlspecialchars($_sp['category_color'] ?? '#9ca3af');
          ?>
          <a href="<?= $basePath ?>/blog/<?= htmlspecialchars($_sp['slug']) ?>"
             class="sidebar-post">
            <?php if ($_spImg): ?>
              <img src="<?= htmlspecialchars($_spImg) ?>"
                   alt="<?= htmlspecialchars($_sp['page_title'] ?? '') ?>"
                   class="sidebar-post-thumb" loading="lazy">
            <?php else: ?>
              <div class="sidebar-post-thumb-placeholder"
                   style="color:<?= $_spColor ?>;background:<?= $_spColor ?>18">
                <?= htmlspecialchars($_spLetter) ?>
              </div>
            <?php endif; ?>
            <div>
              <div class="sidebar-post-title">
                <?= htmlspecialchars(mb_substr((string)($_sp['page_title'] ?? $_sp['slug']), 0, 56)) ?>
              </div>
              <div class="sidebar-post-meta">
                <?php if ($_sw['type'] === 'popular_posts' && !empty($_sp['click_count'])): ?>
                  <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" viewBox="0 0 16 16" style="vertical-align:-1px">
                    <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                    <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
                  </svg>
                  <?= (int)$_sp['click_count'] ?> odsłon
                <?php elseif (!empty($_sp['created_at'])): ?>
                  <?= htmlspecialchars(substr((string)$_sp['created_at'], 0, 10)) ?>
                <?php endif; ?>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- ---- KATEGORIE ---- -->
  <?php elseif ($_sw['type'] === 'categories'): ?>
  <div class="sidebar-widget">
    <?php if (!empty($_sw['title'])): ?>
      <div class="sidebar-widget-header">
        <h3 class="sidebar-widget-title"><?= htmlspecialchars($_sw['title']) ?></h3>
      </div>
    <?php endif; ?>
    <div class="sidebar-widget-body">
      <?php if (empty($_sw['data'])): ?>
        <p class="small text-muted mb-0">Brak kategorii.</p>
      <?php else: ?>
        <?php foreach ($_sw['data'] as $_cat): ?>
          <a href="<?= !empty($prettyUrls) ? $basePath . '/category/' . urlencode($_cat['slug']) : $basePath . '/?category=' . urlencode($_cat['slug']) ?>"
             class="sidebar-cat">
            <span class="sidebar-cat-left">
              <span class="sidebar-cat-dot"
                    style="background:<?= htmlspecialchars($_cat['color'] ?? '#6c757d') ?>"></span>
              <?= htmlspecialchars($_cat['name']) ?>
            </span>
            <?php if (!empty($_cat['post_count'])): ?>
              <span class="sidebar-cat-count"><?= (int)$_cat['post_count'] ?></span>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- ---- CHMURA TAGÓW ---- -->
  <?php elseif ($_sw['type'] === 'tag_cloud'): ?>
  <div class="sidebar-widget">
    <?php if (!empty($_sw['title'])): ?>
      <div class="sidebar-widget-header">
        <h3 class="sidebar-widget-title"><?= htmlspecialchars($_sw['title']) ?></h3>
      </div>
    <?php endif; ?>
    <div class="sidebar-widget-body">
      <?php if (empty($_sw['data'])): ?>
        <p class="small text-muted mb-0">Brak tagów.</p>
      <?php else: ?>
        <div class="tag-cloud">
          <?php foreach ($_sw['data'] as $_tag): ?>
            <a href="<?= !empty($prettyUrls) ? $basePath . '/tag/' . urlencode($_tag['slug']) : $basePath . '/?tag=' . urlencode($_tag['slug']) ?>"
               class="tag-pill">
              #<?= htmlspecialchars($_tag['name']) ?>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ---- SOCIAL LINKS ---- -->
  <?php elseif ($_sw['type'] === 'social_links'): ?>
  <div class="sidebar-widget">
    <?php if (!empty($_sw['title'])): ?>
      <div class="sidebar-widget-header">
        <h3 class="sidebar-widget-title"><?= htmlspecialchars($_sw['title']) ?></h3>
      </div>
    <?php endif; ?>
    <div class="sidebar-widget-body">
      <?php if (empty($_sw['data'])): ?>
        <p class="small text-muted mb-0">Brak linków.</p>
      <?php else: ?>
        <div class="sidebar-social-links">
          <?php foreach ($_sw['data'] as $_net => $_url): ?>
            <?php if (!empty($_url) && isset($_sbSvgPaths[$_net])): ?>
              <a href="<?= htmlspecialchars($_url) ?>"
                 target="_blank" rel="noopener noreferrer"
                 class="sidebar-social-link"
                 title="<?= htmlspecialchars(ucfirst((string)$_net)) ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                  <path d="<?= $_sbSvgPaths[$_net] ?>"/>
                </svg>
              </a>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ---- CUSTOM HTML ---- -->
  <?php elseif ($_sw['type'] === 'custom_html'): ?>
  <div class="sidebar-widget">
    <?php if (!empty($_sw['title'])): ?>
      <div class="sidebar-widget-header">
        <h3 class="sidebar-widget-title"><?= htmlspecialchars($_sw['title']) ?></h3>
      </div>
    <?php endif; ?>
    <div class="sidebar-widget-body">
      <?= $_sw['data'] ?>
    </div>
  </div>

  <?php endif; ?>
<?php endforeach; ?>
