<?php
/**
 * Hyde widgets area (rendered in content column under posts).
 */
?>
<?php if (empty($sidebarData) || !is_array($sidebarData)): ?>
  <section class="hyde-widget-box">
    <h3 class="hyde-widget-title">Widżety</h3>
    <p class="hyde-muted">Brak widgetow do wyswietlenia.</p>
  </section>
<?php else: ?>
  <?php foreach ($sidebarData as $_sw): ?>
    <section class="hyde-widget-box">
      <?php if (!empty($_sw['title'])): ?>
        <h3 class="hyde-widget-title"><?= htmlspecialchars((string)$_sw['title']) ?></h3>
      <?php endif; ?>

      <?php if ($_sw['type'] === 'popular_posts' || $_sw['type'] === 'random_posts'): ?>
        <?php if (empty($_sw['data'])): ?>
          <p class="hyde-muted">Brak wpisow.</p>
        <?php else: ?>
          <ul class="hyde-list">
            <?php foreach ($_sw['data'] as $_p): ?>
              <li>
                <a href="<?= htmlspecialchars($basePath . '/blog/' . rawurlencode((string)$_p['slug'])) ?>">
                  <?= htmlspecialchars((string)($_p['page_title'] ?? $_p['slug'])) ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

      <?php elseif ($_sw['type'] === 'categories'): ?>
        <?php if (empty($_sw['data'])): ?>
          <p class="hyde-muted">Brak kategorii.</p>
        <?php else: ?>
          <ul class="hyde-list">
            <?php foreach ($_sw['data'] as $_c): ?>
              <li>
                <a href="<?= htmlspecialchars(!empty($prettyUrls) ? $basePath . '/category/' . rawurlencode((string)$_c['slug']) : $basePath . '/?category=' . rawurlencode((string)$_c['slug'])) ?>">
                  <?= htmlspecialchars((string)$_c['name']) ?>
                  <?php if (!empty($_c['post_count'])): ?>
                    <small class="hyde-muted">(<?= (int)$_c['post_count'] ?>)</small>
                  <?php endif; ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

      <?php elseif ($_sw['type'] === 'tag_cloud'): ?>
        <?php if (empty($_sw['data'])): ?>
          <p class="hyde-muted">Brak tagow.</p>
        <?php else: ?>
          <p class="hyde-tags">
            <?php foreach ($_sw['data'] as $_t): ?>
              <a href="<?= htmlspecialchars(!empty($prettyUrls) ? $basePath . '/tag/' . rawurlencode((string)$_t['slug']) : $basePath . '/?tag=' . rawurlencode((string)$_t['slug'])) ?>">#<?= htmlspecialchars((string)$_t['name']) ?></a>
            <?php endforeach; ?>
          </p>
        <?php endif; ?>

      <?php elseif ($_sw['type'] === 'custom_html'): ?>
        <div><?= $_sw['data'] ?></div>
      <?php endif; ?>
    </section>
  <?php endforeach; ?>
<?php endif; ?>
