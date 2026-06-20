<?php
// _sidebar.php — PlusUI theme sidebar widgets
// Variables available: $sidebarData, $basePath, $prettyUrls
// Uses pu_* helpers defined in parent template (home.php / post.php)

foreach ($sidebarData as $_sw):
    $_swType  = $_sw['type']  ?? '';
    $_swTitle = $_sw['title'] ?? '';
    $_swData  = $_sw['data']  ?? [];
?>
<div class="pu-widget">
  <?php if (!empty($_swTitle)): ?>
    <div class="pu-widget-title"><?= htmlspecialchars($_swTitle) ?></div>
  <?php endif; ?>

  <?php if ($_swType === 'popular_posts' || $_swType === 'random_posts'): ?>
    <?php foreach ($_swData as $_i => $_wp): ?>
      <a href="<?= pu_post_url($basePath, $_wp['slug'], $prettyUrls ?? false) ?>"
         style="display:flex;align-items:flex-start;gap:10px;padding:10px 0;border-bottom:1px solid var(--pu-border);text-decoration:none;color:var(--pu-text);transition:color .15s"
         onmouseover="this.style.color='var(--pu-primary)'" onmouseout="this.style.color='var(--pu-text)'">
        <span style="flex-shrink:0;width:24px;height:24px;border-radius:6px;background:<?= $_i === 0 ? 'var(--pu-primary)' : 'var(--pu-border)' ?>;color:<?= $_i === 0 ? '#fff' : 'var(--pu-muted)' ?>;font-size:.7rem;font-weight:700;display:flex;align-items:center;justify-content:center">
          <?= $_i + 1 ?>
        </span>
        <span style="flex:1;min-width:0">
          <span style="display:block;font-size:.82rem;font-weight:600;line-height:1.4">
            <?= htmlspecialchars($_wp['page_title']) ?>
          </span>
          <?php if (!empty($_wp['click_count'])): ?>
            <span style="font-size:.7rem;color:var(--pu-muted);margin-top:2px;display:block">
              <?= (int)$_wp['click_count'] ?> odsłon
            </span>
          <?php endif; ?>
        </span>
      </a>
    <?php endforeach; ?>
    <?php if (empty($_swData)): ?>
      <span style="font-size:.8rem;color:var(--pu-muted)">Brak wpisów</span>
    <?php endif; ?>

  <?php elseif ($_swType === 'tag_cloud'): ?>
    <div style="display:flex;flex-wrap:wrap;gap:6px">
      <?php foreach ($_swData as $_tag): ?>
        <a href="<?= pu_tag_url($basePath, $_tag['slug'], $prettyUrls ?? false) ?>"
           style="display:inline-block;padding:4px 12px;border-radius:99px;font-size:.75rem;font-weight:500;background:var(--pu-border);color:var(--pu-text);text-decoration:none;transition:background .15s,color .15s"
           onmouseover="this.style.background='var(--pu-primary)';this.style.color='#fff'"
           onmouseout="this.style.background='var(--pu-border)';this.style.color='var(--pu-text)'">
          #<?= htmlspecialchars($_tag['name']) ?>
        </a>
      <?php endforeach; ?>
      <?php if (empty($_swData)): ?>
        <span style="font-size:.8rem;color:var(--pu-muted)">Brak tagów</span>
      <?php endif; ?>
    </div>

  <?php elseif ($_swType === 'categories'): ?>
    <?php foreach ($_swData as $_cat): ?>
      <a href="<?= pu_cat_url($basePath, $_cat['slug'], $prettyUrls ?? false) ?>"
         style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--pu-border);font-size:.84rem;color:var(--pu-text);text-decoration:none;transition:color .15s"
         onmouseover="this.style.color='var(--pu-primary)'" onmouseout="this.style.color='var(--pu-text)'">
        <span style="display:flex;align-items:center;gap:8px">
          <span style="width:7px;height:7px;border-radius:50%;background:<?= htmlspecialchars($_cat['color'] ?: 'var(--pu-primary)', ENT_QUOTES) ?>;flex-shrink:0"></span>
          <?= htmlspecialchars($_cat['name']) ?>
        </span>
        <span style="font-size:.7rem;color:var(--pu-muted);background:var(--pu-border);padding:1px 8px;border-radius:99px"><?= (int)$_cat['post_count'] ?></span>
      </a>
    <?php endforeach; ?>
    <?php if (empty($_swData)): ?>
      <span style="font-size:.8rem;color:var(--pu-muted)">Brak kategorii</span>
    <?php endif; ?>

  <?php elseif ($_swType === 'social_links'): ?>
    <?php
    $socialDefs = [
        'facebook'   => ['label' => 'Facebook',    'color' => '#1877f2', 'svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951z"/></svg>'],
        'instagram'  => ['label' => 'Instagram',   'color' => '#e4405f', 'svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.917 3.917 0 0 0-1.417.923A3.927 3.927 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.916 3.916 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.926 3.926 0 0 0-.923-1.417A3.911 3.911 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0h.003zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599.28.28.453.546.598.92.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.47 2.47 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.478 2.478 0 0 1-.92-.598 2.48 2.48 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233 0-2.136.008-2.388.046-3.231.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92.28-.28.546-.453.92-.598.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045v.002zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92zm-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217zm0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334z"/></svg>'],
        'twitter'    => ['label' => 'X / Twitter', 'color' => '#000',    'svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/></svg>'],
        'youtube'    => ['label' => 'YouTube',     'color' => '#cd201f', 'svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M8.051 1.999h.089c.822.003 4.987.033 6.11.335a2.01 2.01 0 0 1 1.415 1.42c.101.38.172.883.22 1.402l.01.104.022.26.008.104c.065.914.073 1.77.074 1.957v.075c-.001.194-.01 1.108-.082 2.06l-.008.105-.009.104c-.05.572-.124 1.14-.235 1.558a2.007 2.007 0 0 1-1.415 1.42c-1.16.312-5.569.334-6.18.335h-.142c-.309 0-1.587-.006-2.927-.052l-.17-.006-.087-.004-.171-.007-.171-.007c-1.11-.049-2.167-.128-2.654-.26a2.007 2.007 0 0 1-1.415-1.419c-.111-.417-.185-.986-.235-1.558L.09 9.82l-.008-.104A31.4 31.4 0 0 1 0 7.68v-.123c.002-.215.01-.958.064-1.778l.007-.103.003-.052.008-.104.022-.26.01-.104c.048-.519.119-1.023.22-1.402a2.007 2.007 0 0 1 1.415-1.42c.487-.13 1.544-.21 2.654-.26l.17-.007.172-.006.086-.003.171-.007A99.788 99.788 0 0 1 7.858 2h.193zM6.4 5.209v4.818l4.157-2.408L6.4 5.209z"/></svg>'],
        'linkedin'   => ['label' => 'LinkedIn',    'color' => '#0a66c2', 'svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854V1.146zm4.943 12.248V6.169H2.542v7.225h2.401zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248-.822 0-1.359.54-1.359 1.248 0 .694.521 1.248 1.327 1.248h.016zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016a5.54 5.54 0 0 1 .016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225h2.4z"/></svg>'],
        'tiktok'     => ['label' => 'TikTok',      'color' => '#000',    'svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M9 0h1.98c.144.715.54 1.617 1.235 2.512C12.895 3.389 13.797 4 15 4v2c-1.753 0-3.07-.814-4-1.829V11a5 5 0 1 1-5-5v2a3 3 0 1 0 3 3V0z"/></svg>'],
        'pinterest'  => ['label' => 'Pinterest',   'color' => '#e60023', 'svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M8 0a8 8 0 0 0-2.915 15.452c-.07-.633-.134-1.606.027-2.297.188-.69 1.279-5.42 1.279-5.42s-.327-.653-.327-1.618c0-1.517.879-2.651 1.97-2.651.93 0 1.38.698 1.38 1.535 0 .936-.594 2.338-.902 3.636-.257 1.086.542 1.969 1.608 1.969 1.93 0 3.42-2.034 3.42-4.972 0-2.599-1.87-4.418-4.537-4.418-3.089 0-4.902 2.317-4.902 4.709 0 .932.358 1.93.806 2.476a.32.32 0 0 1 .075.31c-.083.342-.266 1.088-.301 1.239-.048.2-.16.242-.369.146-1.379-.641-2.241-2.658-2.241-4.278 0-3.479 2.528-6.676 7.29-6.676 3.829 0 6.8 2.73 6.8 6.38 0 3.803-2.398 6.864-5.726 6.864-1.118 0-2.17-.581-2.53-1.265l-.688 2.566c-.248.961-.921 2.164-1.371 2.898A8 8 0 1 0 8 0z"/></svg>'],
        'custom_url' => ['label' => 'Strona',      'color' => '#555',    'svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm7.5-6.923c-.67.204-1.335.82-1.887 1.855A7.97 7.97 0 0 0 5.145 4H7.5V1.077zM4.09 4a9.267 9.267 0 0 1 .64-1.539 6.7 6.7 0 0 1 .597-.933A7.025 7.025 0 0 0 2.255 4H4.09zm-.582 3.5c.03-.877.138-1.718.312-2.5H1.674a6.958 6.958 0 0 0-.656 2.5h2.49zM4.847 5a12.5 12.5 0 0 0-.338 2.5H7.5V5H4.847zM8.5 5v2.5h2.99a12.495 12.495 0 0 0-.337-2.5H8.5zM4.51 8.5a12.5 12.5 0 0 0 .337 2.5H7.5V8.5H4.51zm3.99 0V11h2.653c.187-.765.306-1.608.338-2.5H8.5zM5.145 12c.138.386.295.744.468 1.068.552 1.035 1.218 1.65 1.887 1.855V12H5.145zm.182 2.472a6.696 6.696 0 0 1-.597-.933A9.268 9.268 0 0 1 4.09 12H2.255a7.024 7.024 0 0 0 3.072 2.472zM3.82 11a13.652 13.652 0 0 1-.312-2.5h-2.49c.062.89.291 1.733.656 2.5H3.82zm6.853 3.472A7.024 7.024 0 0 0 13.745 12H11.91a9.27 9.27 0 0 1-.64 1.539 6.688 6.688 0 0 1-.597.933zM8.5 12v2.923c.67-.204 1.335-.82 1.887-1.855.173-.324.33-.682.468-1.068H8.5zm3.68-1h2.146c.365-.767.594-1.61.655-2.5h-2.49a13.65 13.65 0 0 1-.311 2.5zm2.801-3.5a6.959 6.959 0 0 0-.656-2.5H12.18c.174.782.282 1.623.312 2.5h2.49zM11.27 2.461c.247.464.462.98.64 1.539h1.835a7.024 7.024 0 0 0-3.072-2.472c.218.284.418.598.597.933zM10.855 4a7.966 7.966 0 0 0-.468-1.068C9.835 1.897 9.17 1.282 8.5 1.077V4h2.355z"/></svg>'],
    ];
    $hasSocial = false;
    foreach ($socialDefs as $key => $_def):
        $url = $_swData[$key] ?? '';
        if (empty($url)) continue;
        $hasSocial = true;
    ?>
      <a href="<?= htmlspecialchars($url, ENT_QUOTES) ?>" target="_blank" rel="noopener noreferrer"
         style="display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid var(--pu-border);text-decoration:none;color:var(--pu-text);font-size:.84rem;font-weight:500;transition:color .15s"
         onmouseover="this.style.color='var(--pu-primary)'" onmouseout="this.style.color='var(--pu-text)'">
        <span style="width:28px;height:28px;border-radius:6px;background:<?= htmlspecialchars($_def['color'], ENT_QUOTES) ?>;color:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0">
          <?= $_def['svg'] ?>
        </span>
        <?= htmlspecialchars($_def['label']) ?>
        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="currentColor" viewBox="0 0 16 16" style="margin-left:auto;color:var(--pu-border)"><path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/></svg>
      </a>
    <?php endforeach; ?>
    <?php if (!$hasSocial): ?>
      <span style="font-size:.8rem;color:var(--pu-muted)">Brak skonfigurowanych linków</span>
    <?php endif; ?>

  <?php elseif ($_swType === 'custom_html'): ?>
    <div style="font-size:.875rem;line-height:1.7;color:var(--pu-text)">
      <?= $_swData['html'] ?? '' ?>
    </div>

  <?php else: ?>
    <span style="font-size:.8rem;color:var(--pu-muted)">Nieznany typ widgetu: <?= htmlspecialchars($_swType) ?></span>
  <?php endif; ?>
</div>
<?php endforeach; ?>
