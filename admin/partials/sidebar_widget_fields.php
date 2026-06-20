<?php
/**
 * Sidebar widget fields partial — renders editable config inputs for a single widget.
 * Uses data-field attributes; JS serializes them to hidden JSON field before form submit.
 * Variables from enclosing context: $widget (array), $idx (int)
 */
$wType = $widget['type'] ?? 'custom_html';
?>
<input type="hidden" data-field="type" value="<?= htmlspecialchars($wType) ?>">

<div class="mb-2">
  <label class="form-label form-label-sm fw-medium">Tytuł widgetu</label>
  <input type="text" class="form-control form-control-sm" data-field="title"
         value="<?= htmlspecialchars($widget['title'] ?? '') ?>" placeholder="Tytuł widgetu">
</div>

<?php if ($wType === 'popular_posts'): ?>
  <div class="row g-2 mt-0">
    <div class="col-sm-4">
      <label class="form-label form-label-sm">Liczba wpisów</label>
      <input type="number" class="form-control form-control-sm" data-field="count"
             value="<?= (int)($widget['count'] ?? 5) ?>" min="1" max="10">
    </div>
    <div class="col-sm-4">
      <label class="form-label form-label-sm">Okres</label>
      <select class="form-select form-select-sm" data-field="period">
        <?php foreach (['all' => 'Wszystkie czasy', 'today' => 'Dziś', '7d' => 'Ostatnie 7 dni', '30d' => 'Ostatnie 30 dni'] as $v => $l): ?>
          <option value="<?= $v ?>" <?= ($widget['period'] ?? 'all') === $v ? 'selected' : '' ?>><?= htmlspecialchars($l) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-sm-4">
      <label class="form-label form-label-sm">ID kategorii <span class="text-muted">(0=wszystkie)</span></label>
      <input type="number" class="form-control form-control-sm" data-field="category_id"
             value="<?= (int)($widget['category_id'] ?? 0) ?>" min="0">
    </div>
  </div>

<?php elseif ($wType === 'random_posts'): ?>
  <div class="row g-2 mt-0">
    <div class="col-sm-4">
      <label class="form-label form-label-sm">Liczba wpisów</label>
      <input type="number" class="form-control form-control-sm" data-field="count"
             value="<?= (int)($widget['count'] ?? 5) ?>" min="1" max="10">
    </div>
  </div>

<?php elseif ($wType === 'tag_cloud'): ?>
  <div class="row g-2 mt-0">
    <div class="col-sm-4">
      <label class="form-label form-label-sm">Maks. liczba tagów</label>
      <input type="number" class="form-control form-control-sm" data-field="max_tags"
             value="<?= (int)($widget['max_tags'] ?? 30) ?>" min="5" max="100">
    </div>
  </div>

<?php elseif ($wType === 'categories'): ?>
  <div class="form-check mt-1">
    <input class="form-check-input" type="checkbox" data-field="show_count"
           id="sidebar_show_count_<?= $idx ?>" <?= !empty($widget['show_count']) ? 'checked' : '' ?>>
    <label class="form-check-label small" for="sidebar_show_count_<?= $idx ?>">Pokaż licznik wpisów obok kategorii</label>
  </div>

<?php elseif ($wType === 'social_links'): ?>
  <p class="text-muted small mb-0">Wyświetla linki social media skonfigurowane w zakładce „Wygląd".</p>

<?php elseif ($wType === 'custom_html'): ?>
  <div class="mb-1">
    <label class="form-label form-label-sm">Zawartość HTML</label>
    <textarea class="form-control form-control-sm font-monospace" data-field="html"
              rows="4" placeholder="<p>Własny HTML...</p>"><?= htmlspecialchars($widget['html'] ?? '') ?></textarea>
  </div>

<?php endif; ?>
