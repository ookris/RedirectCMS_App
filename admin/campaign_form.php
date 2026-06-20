<?php
  $pageTitle = ($isEdit ? 'Edycja kampanii' : 'Nowa kampania') . ' — RedirectCMS';
  require __DIR__ . '/static/head.php';
  $campaignLinkIds = array_column($campaignLinks, 'id');
?>
<body class="bg-light">
  <?php require_once __DIR__ . '/static/navbar.php'; ?>

  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-9">
        <div class="card shadow-sm">
          <div class="card-header bg-primary text-white">
            <h2 class="mb-0"><?= $isEdit ? 'Edycja kampanii' : 'Nowa kampania' ?></h2>
          </div>
          <div class="card-body">
            <form method="post" action="<?= $basePath ?>/admin/index.php?action=<?= $isEdit ? 'campaign_edit&id=' . (int)$campaign['id'] : 'campaign_new' ?>">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars(Utils::csrfToken()) ?>" />

              <div class="row g-3 mb-3">
                <div class="col-md-8">
                  <label for="name" class="form-label">Nazwa kampanii *</label>
                  <input type="text" class="form-control" id="name" name="name" required value="<?= htmlspecialchars($campaign['name'] ?? '') ?>" />
                </div>
                <div class="col-md-4">
                  <label for="color" class="form-label">Kolor</label>
                  <input type="color" class="form-control form-control-color w-100" id="color" name="color" value="<?= htmlspecialchars($campaign['color'] ?? '#2196F3') ?>" />
                </div>
              </div>

              <div class="mb-3">
                <label for="description" class="form-label">Opis (opcjonalnie)</label>
                <textarea class="form-control" id="description" name="description" rows="2"><?= htmlspecialchars($campaign['description'] ?? '') ?></textarea>
              </div>

              <div class="mb-4">
                <label class="form-label">Linki w kampanii</label>
                <div class="mb-2">
                  <input type="text" class="form-control form-control-sm" id="linkSearch" placeholder="Szukaj linku po aliasie lub tytule..." />
                </div>
                <div class="border rounded p-2" style="max-height: 300px; overflow-y: auto;" id="linkList">
                  <?php foreach ($allLinks as $link): ?>
                    <div class="form-check link-item" data-search="<?= htmlspecialchars(strtolower($link['slug'] . ' ' . ($link['page_title'] ?? ''))) ?>">
                      <input class="form-check-input" type="checkbox" name="link_ids[]" value="<?= (int)$link['id'] ?>" id="link_<?= (int)$link['id'] ?>" <?= in_array((int)$link['id'], $campaignLinkIds) ? 'checked' : '' ?> />
                      <label class="form-check-label" for="link_<?= (int)$link['id'] ?>">
                        <code><?= htmlspecialchars($link['slug']) ?></code>
                        <?php if (!empty($link['page_title'])): ?>
                          <span class="text-muted ms-1">— <?= htmlspecialchars(mb_substr($link['page_title'], 0, 50)) ?></span>
                        <?php endif; ?>
                      </label>
                    </div>
                  <?php endforeach; ?>
                </div>
                <small class="text-muted">Zaznaczono: <strong id="checkedCount"><?= count($campaignLinkIds) ?></strong> linków</small>
              </div>

              <div class="d-flex justify-content-between">
                <a href="<?= $basePath ?>/admin/index.php?action=campaigns" class="btn btn-secondary">Anuluj</a>
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Zapisz zmiany' : 'Utwórz kampanię' ?></button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require __DIR__ . '/static/footer.php'; ?>
  <script>
    // Link search filter
    document.getElementById('linkSearch').addEventListener('input', function() {
      const query = this.value.toLowerCase();
      document.querySelectorAll('.link-item').forEach(item => {
        item.style.display = item.dataset.search.includes(query) ? '' : 'none';
      });
    });

    // Update checked count
    document.getElementById('linkList').addEventListener('change', function() {
      document.getElementById('checkedCount').textContent =
        document.querySelectorAll('#linkList input:checked').length;
    });
  </script>
</body>
</html>
