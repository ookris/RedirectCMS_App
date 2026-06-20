<?php
function sp_sidebar_post_url(string $bp, string $slug, bool $pretty): string {
    return $pretty ? $bp . '/blog/' . rawurlencode($slug) : $bp . '/?post=' . rawurlencode($slug);
}
function sp_sidebar_cat_url(string $bp, string $slug, bool $pretty): string {
    return $pretty ? $bp . '/category/' . rawurlencode($slug) : $bp . '/?category=' . rawurlencode($slug);
}
function sp_sidebar_tag_url(string $bp, string $slug, bool $pretty): string {
    return $pretty ? $bp . '/tag/' . rawurlencode($slug) : $bp . '/?tag=' . rawurlencode($slug);
}
?>

<div class="widget-box">
  <h3 class="widget-title">Szukaj</h3>
  <div class="widget-body">
    <form action="<?= htmlspecialchars($basePath) ?>/" method="get" role="search">
      <div class="input-group input-group-sm">
        <input class="form-control" name="s" value="<?= htmlspecialchars($_GET['s'] ?? '') ?>" placeholder="Szukaj..." type="text" />
        <div class="input-group-append"><button class="btn btn-primary" type="submit">Go</button></div>
      </div>
    </form>
  </div>
</div>

<?php foreach ($sidebarData as $_sw): ?>
  <?php if ($_sw['type'] === 'popular_posts' || $_sw['type'] === 'random_posts'): ?>
    <div class="widget-box">
      <h3 class="widget-title"><?= htmlspecialchars($_sw['title'] ?: 'Wpisy') ?></h3>
      <div class="widget-body p-0">
        <ul class="list-group list-group-flush">
          <?php foreach (($_sw['data'] ?? []) as $_p): ?>
            <li class="list-group-item py-2 px-3"><a href="<?= htmlspecialchars(sp_sidebar_post_url($basePath, (string)$_p['slug'], !empty($prettyUrls))) ?>"><?= htmlspecialchars($_p['page_title']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  <?php elseif ($_sw['type'] === 'categories'): ?>
    <div class="widget-box">
      <h3 class="widget-title"><?= htmlspecialchars($_sw['title'] ?: 'Kategorie') ?></h3>
      <div class="widget-body p-0">
        <ul class="list-group list-group-flush">
          <?php foreach (($_sw['data'] ?? []) as $_cat): ?>
            <li class="list-group-item py-2 px-3 d-flex justify-content-between">
              <a href="<?= htmlspecialchars(sp_sidebar_cat_url($basePath, (string)$_cat['slug'], !empty($prettyUrls))) ?>"><?= htmlspecialchars($_cat['name']) ?></a>
              <span class="badge badge-primary badge-pill"><?= (int)($_cat['post_count'] ?? 0) ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  <?php elseif ($_sw['type'] === 'tag_cloud'): ?>
    <div class="widget-box">
      <h3 class="widget-title"><?= htmlspecialchars($_sw['title'] ?: 'Tagi') ?></h3>
      <div class="widget-body">
        <?php foreach (($_sw['data'] ?? []) as $_tag): ?>
          <a class="btn btn-primary btn-sm mb-2" href="<?= htmlspecialchars(sp_sidebar_tag_url($basePath, (string)$_tag['slug'], !empty($prettyUrls))) ?>">#<?= htmlspecialchars($_tag['name']) ?></a>
        <?php endforeach; ?>
      </div>
    </div>
  <?php elseif ($_sw['type'] === 'social_links'): ?>
    <div class="widget-box">
      <h3 class="widget-title"><?= htmlspecialchars($_sw['title'] ?: 'Social') ?></h3>
      <div class="widget-body">
        <?php foreach (($_sw['data'] ?? []) as $_k => $_u): ?>
          <?php if (!empty($_u)): ?><a class="btn btn-sm btn-light border mb-1" href="<?= htmlspecialchars($_u) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars(ucfirst((string)$_k)) ?></a><?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  <?php elseif ($_sw['type'] === 'custom_html'): ?>
    <div class="widget-box">
      <h3 class="widget-title"><?= htmlspecialchars($_sw['title'] ?: 'Widget') ?></h3>
      <div class="widget-body"><?= $_sw['data'] ?? '' ?></div>
    </div>
  <?php endif; ?>
<?php endforeach; ?>
