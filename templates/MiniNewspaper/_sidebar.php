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
if (!function_exists('mn_tag_url')) {
  function mn_tag_url(string $bp, string $slug, bool $pretty): string {
    return $pretty ? $bp . '/tag/' . rawurlencode($slug) : $bp . '/?tag=' . rawurlencode($slug);
  }
}
?>

<aside class="mn-sidebar-block">
  <h3>Szukaj</h3>
  <form action="<?= htmlspecialchars($basePath) ?>/" method="get" role="search">
    <div class="mn-search-row">
      <input type="text" name="s" value="<?= htmlspecialchars((string)($_GET['s'] ?? '')) ?>" placeholder="Szukaj..." />
      <button type="submit">Go</button>
    </div>
  </form>
</aside>

<?php foreach (($sidebarData ?? []) as $_sw): ?>
  <?php if ($_sw['type'] === 'popular_posts' || $_sw['type'] === 'random_posts'): ?>
    <aside class="mn-sidebar-block">
      <h3><?= htmlspecialchars((string)($_sw['title'] ?: 'Wpisy')) ?></h3>
      <ul class="mn-clean-list">
        <?php foreach (($_sw['data'] ?? []) as $_p): ?>
          <li><a href="<?= htmlspecialchars(mn_post_url($basePath, (string)$_p['slug'], !empty($prettyUrls))) ?>"><?= htmlspecialchars((string)$_p['page_title']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </aside>
  <?php elseif ($_sw['type'] === 'categories'): ?>
    <aside class="mn-sidebar-block">
      <h3><?= htmlspecialchars((string)($_sw['title'] ?: 'Kategorie')) ?></h3>
      <ul class="mn-clean-list">
        <?php foreach (($_sw['data'] ?? []) as $_cat): ?>
          <li>
            <a href="<?= htmlspecialchars(mn_cat_url($basePath, (string)$_cat['slug'], !empty($prettyUrls))) ?>"><?= htmlspecialchars((string)$_cat['name']) ?></a>
            (<?= (int)($_cat['post_count'] ?? 0) ?>)
          </li>
        <?php endforeach; ?>
      </ul>
    </aside>
  <?php elseif ($_sw['type'] === 'tag_cloud'): ?>
    <aside class="mn-sidebar-block">
      <h3><?= htmlspecialchars((string)($_sw['title'] ?: 'Tagi')) ?></h3>
      <p class="mn-tags-row">
        <?php foreach (($_sw['data'] ?? []) as $_tag): ?>
          <a href="<?= htmlspecialchars(mn_tag_url($basePath, (string)$_tag['slug'], !empty($prettyUrls))) ?>">#<?= htmlspecialchars((string)$_tag['name']) ?></a>
        <?php endforeach; ?>
      </p>
    </aside>
  <?php elseif ($_sw['type'] === 'custom_html'): ?>
    <aside class="mn-sidebar-block">
      <h3><?= htmlspecialchars((string)($_sw['title'] ?: 'Widget')) ?></h3>
      <div><?= (string)($_sw['data'] ?? '') ?></div>
    </aside>
  <?php endif; ?>
<?php endforeach; ?>
