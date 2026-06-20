<?php
  $pageTitle = 'Linki — RedirectCMS';
  require __DIR__ . '/static/head.php';
?>
<body class="bg-light">
  <?php require_once __DIR__ . '/static/navbar.php'; ?>


  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
      <div>
        <h2 class="mb-0">Lista linków</h2>
        <p class="text-muted mb-0">Paginowana lista wszystkich linków z możliwością sortowania i filtrowania.</p>
      </div>
      <div class="d-flex gap-2">
        <?php
          // Przygotuj parametry dla eksportu (zachowaj filtry)
          $exportParams = ['action' => 'export_links'];
          if (!empty($searchQuery)) $exportParams['search'] = $searchQuery;
          if (!empty($categoryFilter)) $exportParams['category_filter'] = $categoryFilter;
          if (!empty($programFilter)) $exportParams['program_filter'] = $programFilter;
          $exportUrl = $basePath . '/admin/index.php?' . http_build_query($exportParams);
        ?>
        <a href="<?= $basePath ?>/admin/index.php?action=import_links" class="btn btn-outline-primary"<?= (($licenseStatus['state'] ?? '') === 'blocked') ? ' data-license-block="' . htmlspecialchars((string)($licenseStatus['message'] ?? 'Import linków jest zablokowany z powodu nieważnej licencji.'), ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
            <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z"/>
          </svg> Importuj z CSV
        </a>
        <a href="<?= $exportUrl ?>" class="btn btn-success">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
            <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
          </svg> Eksportuj do CSV
        </a>
        <a href="<?= $basePath ?>/admin/index.php?action=new" class="btn btn-primary"<?= (($licenseStatus['state'] ?? '') === 'blocked') ? ' data-license-block="' . htmlspecialchars((string)($licenseStatus['message'] ?? 'Dodawanie linków jest zablokowane z powodu nieważnej licencji.'), ENT_QUOTES, 'UTF-8') . '"' : '' ?>>+ Nowy link</a>
      </div>
    </div>

    <div class="card mb-4 shadow-sm">
      <div class="card-body">
        <form class="row g-3" method="get" action="<?= $basePath ?>/admin/index.php">
          <input type="hidden" name="action" value="links" />
          <div class="col-md-4">
            <label for="search" class="form-label">Szukaj</label>
            <div class="input-group">
              <span class="input-group-text">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                </svg>
              </span>
              <input type="text" class="form-control" id="search" name="search" value="<?= htmlspecialchars($searchQuery ?? '') ?>" placeholder="Alias, URL, kategoria lub program" />
            </div>
          </div>
          <div class="col-md-3">
            <label for="category_filter" class="form-label">Kategoria</label>
            <select class="form-select" id="category_filter" name="category_filter">
              <option value="">Wszystkie kategorie</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= (int)$cat['id'] ?>" <?= (isset($categoryFilter) && (int)$categoryFilter === (int)$cat['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cat['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label for="program_filter" class="form-label">Program afiliacyjny</label>
            <select class="form-select" id="program_filter" name="program_filter">
              <option value="">Wszystkie programy</option>
              <?php foreach ($affiliatePrograms as $prog): ?>
                <option value="<?= (int)$prog['id'] ?>" <?= (isset($programFilter) && (int)$programFilter === (int)$prog['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($prog['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2">
            <label for="per_page" class="form-label">Na stronę</label>
            <select class="form-select" id="per_page" name="per_page">
              <?php foreach ([10,25,50,100,150] as $size): ?>
                <option value="<?= $size ?>" <?= (int)$perPage === $size ? 'selected' : '' ?>><?= $size ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <input type="hidden" name="status" value="<?= htmlspecialchars($statusTab ?? 'published') ?>" />
          <div class="col-12">
            <button type="submit" class="btn btn-primary">Filtruj</button>
            <?php if (!empty($searchQuery) || !empty($categoryFilter) || !empty($programFilter)): ?>
              <a href="<?= $basePath ?>/admin/index.php?action=links&per_page=<?= $perPage ?>&status=<?= htmlspecialchars($statusTab ?? 'published') ?>" class="btn btn-outline-secondary">Wyczysc filtry</a>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </div>

    <!-- Status tabs -->
    <ul class="nav nav-tabs">
      <?php
        $tabs = [
          'published' => ['label' => 'Aktywne', 'icon' => 'check-circle', 'color' => 'success'],
          'scheduled' => ['label' => 'Zaplanowane', 'icon' => 'clock', 'color' => 'warning'],
          'draft' => ['label' => 'Szkice', 'icon' => 'file-earmark', 'color' => 'secondary'],
          'expired' => ['label' => 'Wygasle', 'icon' => 'x-circle', 'color' => 'secondary'],
          'trashed' => ['label' => 'Kosz', 'icon' => 'trash', 'color' => 'danger'],
        ];
        foreach ($tabs as $tabKey => $tab):
          $isActive = ($statusTab ?? 'published') === $tabKey;
          $count = $tabCounts[$tabKey] ?? 0;
          $tabUrl = $basePath . '/admin/index.php?' . http_build_query([
            'action' => 'links',
            'status' => $tabKey,
            'per_page' => $perPage,
          ]);
      ?>
        <li class="nav-item">
          <a class="nav-link border <?= $isActive ? 'active' : '' ?>" href="<?= $tabUrl ?>">
            <?= htmlspecialchars($tab['label']) ?>
            <span class="badge bg-<?= $count > 0 ? $tab['color'] : 'light text-dark border border-dark-subtle' ?> ms-1"><?= $count ?></span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>

    <?php if (($statusTab ?? 'published') === 'trashed' && ($tabCounts['trashed'] ?? 0) > 0): ?>
      <div class="alert bg-warning-subtle border border-warning text-warning-emphasis d-flex justify-content-between align-items-center mt-2 mb-4">
        <span>Linki w koszu mogą być automatycznie usuwane zgodnie z ustawieniami.</span>
        <form method="post" action="<?= $basePath ?>/admin/index.php?action=empty_trash" class="d-inline">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(Utils::csrfToken()) ?>" />
          <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Czy na pewno chcesz trwale usunąć wszystkie linki z kosza?')">
            Opróżnij kosz
          </button>
        </form>
      </div>
    <?php endif; ?>

    <?php if (empty($links)): ?>
      <div class="alert bg-info-subtle border border-info text-info-emphasis mt-2">Brak wyników. Spróbuj zmienić kryteria lub <a href="<?= $basePath ?>/admin/index.php?action=new">dodaj nowy link</a>.</div>
    <?php else: ?>
      <?php
        $stats = $stats ?? [];
        $currentSort = $sortBy ?? 'created_at';
        $currentOrder = $sortOrder ?? 'DESC';
        $currentStatusTab = $statusTab ?? 'published';
        function linksSortUrl($column, $currentSort, $currentOrder, $basePath, $searchQuery, $perPage, $page, $categoryFilter, $programFilter, $statusTab) {
          $newOrder = ($column === $currentSort && $currentOrder === 'ASC') ? 'DESC' : 'ASC';
          $params = [
            'action' => 'links',
            'sort' => $column,
            'order' => $newOrder,
            'per_page' => $perPage,
            'page' => $page,
            'status' => $statusTab,
          ];
          if (!empty($searchQuery)) {
            $params['search'] = $searchQuery;
          }
          if (!empty($categoryFilter)) {
            $params['category_filter'] = $categoryFilter;
          }
          if (!empty($programFilter)) {
            $params['program_filter'] = $programFilter;
          }
          return $basePath . '/admin/index.php?' . http_build_query($params);
        }
        function linksSortIcon($column, $currentSort, $currentOrder) {
          if ($column !== $currentSort) {
            return '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16" style="opacity: 0.35;"><path d="M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z"/></svg>';
          }
          if ($currentOrder === 'ASC') {
            return '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path d="m7.247 4.86-4.796 5.481c-.566.647-.106 1.659.753 1.659h9.592a1 1 0 0 0 .753-1.659l-4.796-5.48a1 1 0 0 0-1.506 0z"/></svg>';
          }
          return '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path d="M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z"/></svg>';
        }
        function linksPageUrl($page, $currentSort, $currentOrder, $basePath, $searchQuery, $perPage, $categoryFilter, $programFilter, $statusTab) {
          $params = [
            'action' => 'links',
            'sort' => $currentSort,
            'order' => $currentOrder,
            'per_page' => $perPage,
            'page' => $page,
            'status' => $statusTab,
          ];
          if (!empty($searchQuery)) {
            $params['search'] = $searchQuery;
          }
          if (!empty($categoryFilter)) {
            $params['category_filter'] = $categoryFilter;
          }
          if (!empty($programFilter)) {
            $params['program_filter'] = $programFilter;
          }
          return $basePath . '/admin/index.php?' . http_build_query($params);
        }
        function linksPlannedDeleteDate(array $link, int $trashAutoDeleteDays): ?string {
          if (($link['status'] ?? '') !== 'trashed' || empty($link['deleted_at'])) {
            return null;
          }
          try {
            return (new DateTimeImmutable((string)$link['deleted_at']))
              ->modify('+' . $trashAutoDeleteDays . ' days')
              ->format('d.m.Y');
          } catch (Throwable $e) {
            return null;
          }
        }
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $offset = ($page - 1) * $perPage;
      ?>

      <!-- Akcje masowe -->
      <div class="card shadow-sm mb-3" id="bulkActionsCard" style="display: none;">
        <div class="card-body">
          <form method="post" action="<?= $basePath ?>/admin/index.php?action=bulk_action" id="bulkActionForm">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(Utils::csrfToken()) ?>" />
            <input type="hidden" name="selected_ids" id="selectedIdsInput" value="" />
            <input type="hidden" name="status_tab" value="<?= htmlspecialchars($currentStatusTab) ?>" />
            <div class="row g-3 align-items-end">
              <div class="col-md-3">
                <label for="bulk_action" class="form-label">Akcja dla <span id="selectedCount">0</span> wybranych</label>
                <select class="form-select" id="bulk_action" name="bulk_action" required>
                  <option value="">Wybierz akcje...</option>
                  <?php if ($currentStatusTab === 'trashed'): ?>
                    <option value="restore">Przywróć z kosza</option>
                    <option value="permanent_delete">Usuń trwale</option>
                  <?php else: ?>
                    <option value="delete">Usuń wybrane</option>
                    <option value="change_category">Zmień kategorię</option>
                    <option value="change_program">Zmień program afiliacyjny</option>
                    <option value="change_status">Zmień status</option>
                    <option value="change_delay">Zmień opóźnienie</option>
                  <?php endif; ?>
                </select>
              </div>
              <div class="col-md-3" id="categorySelectWrapper" style="display: none;">
                <label for="bulk_category" class="form-label">Nowa kategoria</label>
                <select class="form-select" id="bulk_category" name="bulk_category">
                  <option value="">Brak kategorii</option>
                  <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3" id="programSelectWrapper" style="display: none;">
                <label for="bulk_program" class="form-label">Nowy program</label>
                <select class="form-select" id="bulk_program" name="bulk_program">
                  <option value="">Brak programu</option>
                  <?php foreach ($affiliatePrograms as $prog): ?>
                    <option value="<?= (int)$prog['id'] ?>"><?= htmlspecialchars($prog['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3" id="statusSelectWrapper" style="display: none;">
                <label for="bulk_status" class="form-label">Nowy status</label>
                <select class="form-select" id="bulk_status" name="bulk_status">
                  <option value="published">Opublikowany</option>
                  <option value="draft">Szkic</option>
                </select>
              </div>
              <div class="col-md-3" id="delayInputWrapper" style="display: none;">
                <label for="bulk_delay" class="form-label">Opoznienie (sekundy)</label>
                <input type="number" class="form-control" id="bulk_delay" name="bulk_delay" min="0" max="300" value="5" />
              </div>
              <div class="col-md d-grid gap-2 d-md-block">
                <button type="submit" class="btn btn-primary">Wykonaj</button>
                <button type="button" class="btn btn-outline-secondary" onclick="clearSelection()">Anuluj</button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Widok desktopowy - tabela (ukryty na mobile) -->
      <div class="card shadow-sm d-none d-md-block">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-fixed mb-0">
              <thead class="table-info">
                <tr>
                  <th style="width: 30px;">
                    <input type="checkbox" id="selectAll" class="form-check-input" onchange="toggleAllCheckboxes(this)" />
                  </th>
                  <th style="width: 100px;"> <a href="<?= linksSortUrl('id', $currentSort, $currentOrder, $basePath, $searchQuery ?? '', $perPage, $page, $categoryFilter ?? null, $programFilter ?? null, $currentStatusTab) ?>" class="text-white text-decoration-none">ID <?= linksSortIcon('id', $currentSort, $currentOrder) ?></a></th>
                  <th style="width: 450px;"> <a href="<?= linksSortUrl('slug', $currentSort, $currentOrder, $basePath, $searchQuery ?? '', $perPage, $page, $categoryFilter ?? null, $programFilter ?? null, $currentStatusTab) ?>" class="text-white text-decoration-none">Tytuł / alias <?= linksSortIcon('slug', $currentSort, $currentOrder) ?></a></th>
                  <th> <a href="<?= linksSortUrl('target_url', $currentSort, $currentOrder, $basePath, $searchQuery ?? '', $perPage, $page, $categoryFilter ?? null, $programFilter ?? null, $currentStatusTab) ?>" class="text-white text-decoration-none">URL <?= linksSortIcon('target_url', $currentSort, $currentOrder) ?></a></th>
                  <th style="width: 100px;">Status</th>
                  <th style="width: 100px;">Utworzono</th>
                  <th style="width: 225px;">Akcje</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($links as $link):
                  $fullUrl = $protocol . '://' . $host . $basePath . '/' . htmlspecialchars($link['slug']);
                  $publishAt = !empty($link['publish_at']) ? strtotime((string)$link['publish_at']) : null;
                  $expiresAt = !empty($link['expires_at']) ? strtotime((string)$link['expires_at']) : null;
                  $nowTs = time();
                  $linkStats = $stats[(int)$link['id']] ?? ['total' => 0, 'today' => 0, 'week' => 0];
                  $linkStatus = $link['status'] ?? 'published';
                  $isTrashed = ($linkStatus === 'trashed');
                  $isDraft = ($linkStatus === 'draft');
                  $plannedDeleteAt = linksPlannedDeleteDate($link, (int)$trashAutoDeleteDays);

                  // Determine status badge
                  if ($isTrashed) {
                    $statusBadge = '<span class="badge bg-danger">W koszu</span>';
                  } elseif ($isDraft) {
                    $statusBadge = '<span class="badge bg-secondary-subtle border border-secondary text-secondary-emphasis">Szkic</span>';
                  } elseif ($publishAt && $publishAt > $nowTs) {
                    $statusBadge = '<span class="badge bg-warning-subtle border border-warning text-warning-emphasis">Zaplanowany</span>';
                  } elseif ($expiresAt && $expiresAt <= $nowTs) {
                    $statusBadge = '<span class="badge bg-secondary-subtle border border-secondary text-secondary-emphasis">Wygasły</span>';
                  } else {
                    $statusBadge = '<span class="badge bg-success-subtle border border-success text-success-emphasis">Aktywny</span>';
                  }
                ?>
                  <tr>
                    <td>
                      <input type="checkbox" class="form-check-input link-checkbox" data-link-id="<?= (int)$link['id'] ?>" onchange="updateBulkActions()" />
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-secondary-subtle border border-secondary text-secondary-emphasis">#<?= (int)$link['id'] ?></span>
                        <?php if (!empty($link['og_image']) || !empty($link['og_image_thumb'])): ?>
                          <?php $thumbSrc = !empty($link['og_image_thumb']) ? $link['og_image_thumb'] : $link['og_image']; ?>
                          <img src="<?= htmlspecialchars($basePath . '/' . $thumbSrc) ?>" alt="Miniatura" class="thumbnail-preview" loading="lazy" width="42" height="42" />
                        <?php endif; ?>
                      </div>
                    </td>
                    <td>
                      <?php $title = isset($link['page_title']) ? trim((string)$link['page_title']) : ''; ?>
                      <?php if ($title !== ''): ?>
                        <div class="fw-semibold"><?= htmlspecialchars($title) ?></div>
                        <div><small class="text-muted"><code><?= htmlspecialchars($link['slug']) ?></code></small></div>
                      <?php else: ?>
                        <code><?= htmlspecialchars($link['slug']) ?></code>
                      <?php endif; ?>
                      <?php if (!empty($link['affiliate_program_name']) || !empty($link['category_name'])): ?>
                        <div class="mt-1 d-flex flex-wrap gap-1">
                          <?php if (!empty($link['affiliate_program_name'])): ?>
                            <span class="badge" style="--badge-color: <?= htmlspecialchars($link['affiliate_program_color'] ?? '#4CAF50') ?>;">
                              Program: <?= htmlspecialchars($link['affiliate_program_name']) ?>
                            </span>
                          <?php endif; ?>
                          <?php if (!empty($link['category_name'])): ?>
                            <span class="badge" style="--badge-color: <?= htmlspecialchars($link['category_color'] ?? '#3A3F45') ?>;">
                              Kategoria: <?= htmlspecialchars($link['category_name']) ?>
                            </span>
                          <?php endif; ?>
                        </div>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div class="text-break"><small><?= htmlspecialchars(substr((string)$link['target_url'], 0, 110)) ?><?= strlen((string)$link['target_url']) > 110 ? '…' : '' ?></small></div>
                      <div class="mt-2 d-flex flex-wrap gap-2">
                        <span class="badge bg-secondary-subtle border border-secondary text-body-emphasis">D: <?= (int)$linkStats['today'] ?></span>
                        <span class="badge bg-secondary-subtle border border-secondary text-body-emphasis">7: <?= (int)$linkStats['week'] ?></span>
                        <span class="badge bg-secondary-subtle border border-secondary text-body-emphasis">Ł: <?= (int)$linkStats['total'] ?></span>
                        <span class="badge bg-warning-subtle border border-warning text-warning-emphasis">R: <?= (int)$link['delay_seconds'] ?>s</span>
                      </div>
                      <?php
                        $_rxAdmin = $reactionStats[(int)$link['id']] ?? null;
                        $_rxAdminKeys = ['happy','love','laugh','surprised','cry','anger'];
                        $_rxAdminTotal = $_rxAdmin ? array_sum($_rxAdmin) : 0;
                        if ($_rxAdminTotal > 0):
                      ?>
                      <div class="mt-1 d-flex flex-wrap gap-1 align-items-center" title="Oceny czytelników">
                        <?php foreach ($_rxAdminKeys as $_rxKey): ?>
                          <?php $_rxVal = (int)($_rxAdmin[$_rxKey] ?? 0); if ($_rxVal > 0): ?>
                            <span class="d-inline-flex align-items-center gap-1" style="font-size:.75rem; color:#555;" title="<?= htmlspecialchars(ucfirst($_rxKey)) ?>">
                              <img src="<?= htmlspecialchars($basePath . '/assets/post_react/' . $_rxKey . '.svg') ?>" width="16" height="16" alt="<?= htmlspecialchars($_rxKey) ?>" loading="lazy" style="vertical-align:middle">
                              <?= $_rxVal ?>
                            </span>
                          <?php endif; ?>
                        <?php endforeach; ?>
                      </div>
                      <?php endif; ?>
                    </td>
                    <td><?= $statusBadge ?></td>
                    <td class="nowrap">
                      <?= !empty($link['created_at']) ? date('d.m.Y', strtotime((string)$link['created_at'])) : '—' ?>
                      <?php if ($isTrashed): ?>
                        <br>
                        <small class="text-muted">
                          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                            <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                          </svg>
                        <?= $plannedDeleteAt ?? '—' ?>
                      </small>
                      <?php endif; ?>
                    </td>
                    <td class="text-end">
                      <div class="d-flex gap-1 flex-nowrap">
                        <?php if ($isTrashed): ?>
                          <!-- Akcje dla linków w koszu -->
                          <form method="post" action="<?= $basePath ?>/admin/index.php?action=restore&id=<?= (int)$link['id'] ?>" class="d-inline">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars(Utils::csrfToken()) ?>" />
                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Przywrócić link z kosza?')" data-bs-toggle="tooltip" data-bs-title="Przywroc link">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2v1z"/>
                                <path d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466z"/>
                              </svg>
                            </button>
                          </form>
                          <a href="<?= $basePath ?>/admin/index.php?action=edit&id=<?= (int)$link['id'] ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-title="Edytuj link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                              <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z" />
                            </svg>
                          </a>
                          <form method="post" action="<?= $basePath ?>/admin/index.php?action=permanent_delete&id=<?= (int)$link['id'] ?>" class="d-inline">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars(Utils::csrfToken()) ?>" />
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Czy na pewno chcesz TRWALE usunąć ten link? Ta operacja jest nieodwracalna!')" data-bs-toggle="tooltip" data-bs-title="Usuń trwale">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z" />
                                <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z" />
                              </svg>
                            </button>
                          </form>
                        <?php else: ?>
                          <!-- Standardowe akcje -->
                          <button class="btn btn-sm btn-primary copy-btn" onclick="copyToClipboard('<?= $fullUrl ?>', this)" data-bs-toggle="tooltip" data-bs-title="Kopiuj pelny link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                              <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z" />
                              <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z" />
                            </svg>
                          </button>
                          <a href="<?= $basePath ?>/admin/index.php?action=stats&id=<?= (int)$link['id'] ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-title="Szczegolowe statystyki">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                              <path d="M4 11H2v3h2v-3zm5-4H7v7h2V7zm5-5v12h-2V2h2zm-2-1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1h-2zM6 7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7zm-5 4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3z" />
                            </svg>
                          </a>
                          <a href="<?= $basePath ?>/admin/index.php?action=edit&id=<?= (int)$link['id'] ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-title="Edytuj link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                              <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z" />
                            </svg>
                          </a>
                          <form method="post" action="<?= $basePath ?>/admin/index.php?action=duplicate&id=<?= (int)$link['id'] ?>" class="d-inline">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars(Utils::csrfToken()) ?>" />
                            <button type="submit" class="btn btn-sm btn-info" onclick="return confirm('Czy na pewno chcesz zduplikowac ten link?')" data-bs-toggle="tooltip" data-bs-title="Duplikuj link">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-copy" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M4 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1h1v1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1v1z"/>
                              </svg>
                            </button>
                          </form>
                          <button class="btn btn-sm btn-success" onclick="showQRModal(<?= (int)$link['id'] ?>, '<?= htmlspecialchars(addslashes($link['slug'])) ?>')" data-bs-toggle="tooltip" data-bs-title="Pokaz kod QR">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-qr-code" viewBox="0 0 16 16">
                              <path d="M2 2h2v2H2z" />
                              <path d="M6 0v6H0V0zM5 1H1v4h4zM4 12H2v2h2z" />
                              <path d="M6 10v6H0v-6zm-5 1v4h4v-4zm11-9h2v2h-2z" />
                              <path d="M10 0v6h6V0zm5 1v4h-4V1zM8 1V0h1v2H8v2H7V1zm0 5V4h1v2zM6 8V7h1V6h1v2h1V7h5v1h-4v1H7V8zm0 0v1H2V8H1v1H0V7h3v1zm10 1h-1V7h1zm-1 0h-1v2h2v-1h-1zm-4 0h2v1h-1v1h-1zm2 3v-1h-1v1h-1v1H9v1h3v-2zm0 0h3v1h-2v1h-1zm-4-1v1h1v-2H7v1z" />
                              <path d="M7 12h1v3h4v1H7zm9 2v2h-3v-1h2v-1z" />
                            </svg>
                          </button>
                          <form method="post" action="<?= $basePath ?>/admin/index.php?action=delete&id=<?= (int)$link['id'] ?>" class="d-inline">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars(Utils::csrfToken()) ?>" />
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Usunąć link?')" data-bs-toggle="tooltip" data-bs-title="Usuń link">
                              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z" />
                                <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z" />
                              </svg>
                            </button>
                          </form>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Widok mobilny - karty (ukryty na desktop) -->
      <div class="d-md-none">
        <?php foreach ($links as $link):
          $fullUrl = $protocol . '://' . $host . $basePath . '/' . htmlspecialchars($link['slug']);
          $publishAt = !empty($link['publish_at']) ? strtotime((string)$link['publish_at']) : null;
          $expiresAt = !empty($link['expires_at']) ? strtotime((string)$link['expires_at']) : null;
          $nowTs = time();
          $linkStats = $stats[(int)$link['id']] ?? ['total' => 0, 'today' => 0, 'week' => 0];
          $linkStatus = $link['status'] ?? 'published';
          $isTrashed = ($linkStatus === 'trashed');
          $isDraft = ($linkStatus === 'draft');
          $plannedDeleteAt = linksPlannedDeleteDate($link, (int)$trashAutoDeleteDays);

          // Determine status badge
          if ($isTrashed) {
            $statusBadge = '<span class="badge bg-danger">W koszu</span>';
          } elseif ($isDraft) {
            $statusBadge = '<span class="badge bg-secondary-subtle border border-secondary text-secondary-emphasis">Szkic</span>';
          } elseif ($publishAt && $publishAt > $nowTs) {
            $statusBadge = '<span class="badge bg-warning-subtle border border-warning text-warning-emphasis">Zaplanowany</span>';
          } elseif ($expiresAt && $expiresAt <= $nowTs) {
            $statusBadge = '<span class="badge bg-secondary-subtle border border-secondary text-secondary-emphasis">Wygasły</span>';
          } else {
            $statusBadge = '<span class="badge bg-success-subtle border border-success text-success-emphasis">Aktywny</span>';
          }
          $title = isset($link['page_title']) ? trim((string)$link['page_title']) : '';
        ?>
          <div class="card mb-3 shadow-sm">
            <div class="card-body">
              <!-- Checkbox dla akcji masowych -->
              <div class="form-check mb-2">
                <input class="form-check-input link-checkbox" type="checkbox" data-link-id="<?= (int)$link['id'] ?>" id="check_mobile_<?= (int)$link['id'] ?>" onchange="updateBulkActions()" />
                <label class="form-check-label fw-bold" for="check_mobile_<?= (int)$link['id'] ?>">
                  Zaznacz do akcji masowej
                </label>
              </div>
              <!-- Nagłówek z ID i obrazem -->
              <div class="d-flex align-items-start gap-3 mb-2">
                <div class="d-flex flex-column align-items-center gap-2">
                  <span class="badge bg-secondary-subtle border border-secondary text-secondary-emphasis">#<?= (int)$link['id'] ?></span>
                  <?php if (!empty($link['og_image']) || !empty($link['og_image_thumb'])): ?>
                    <?php $thumbSrc = !empty($link['og_image_thumb']) ? $link['og_image_thumb'] : $link['og_image']; ?>
                    <img src="<?= htmlspecialchars($basePath . '/' . $thumbSrc) ?>" alt="Miniatura" class="rounded" style="width: 60px; height: 60px; object-fit: cover;" loading="lazy" />
                  <?php endif; ?>
                </div>
                <div class="flex-grow-1">
                  <?php if ($title !== ''): ?>
                    <h6 class="mb-1"><?= htmlspecialchars($title) ?></h6>
                    <small class="text-muted"><code><?= htmlspecialchars($link['slug']) ?></code></small>
                  <?php else: ?>
                    <h6 class="mb-1"><code><?= htmlspecialchars($link['slug']) ?></code></h6>
                  <?php endif; ?>
                  <div class="mt-2">
                    <?= $statusBadge ?>
                  </div>
                  <?php if (!empty($link['affiliate_program_name']) || !empty($link['category_name'])): ?>
                    <div class="mt-2 d-flex flex-wrap gap-1">
                      <?php if (!empty($link['affiliate_program_name'])): ?>
                        <span class="badge" style="--badge-color: <?= htmlspecialchars($link['affiliate_program_color'] ?? '#4CAF50') ?>;">
                          Program: <?= htmlspecialchars($link['affiliate_program_name']) ?>
                        </span>
                      <?php endif; ?>
                      <?php if (!empty($link['category_name'])): ?>
                        <span class="badge" style="--badge-color: <?= htmlspecialchars($link['category_color'] ?? '#3A3F45') ?>;">
                          Kategoria: <?= htmlspecialchars($link['category_name']) ?>
                        </span>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>

              <!-- URL docelowy -->
              <div class="mb-2">
                <small class="text-muted d-block mb-1">Docelowy URL:</small>
                <small class="text-break"><?= htmlspecialchars(substr((string)$link['target_url'], 0, 60)) ?><?= strlen((string)$link['target_url']) > 60 ? '...' : '' ?></small>
              </div>

              <!-- Data utworzenia -->
              <div class="mb-2">
                <small class="text-muted">Utworzono: <?= !empty($link['created_at']) ? date('d.m.Y', strtotime((string)$link['created_at'])) : '—' ?></small>
                <?php if ($isTrashed): ?>
                  <br><small class="text-muted">Planowane usunięcie: <?= $plannedDeleteAt ?? '—' ?></small>
                <?php endif; ?>
              </div>

              <!-- Statystyki -->
              <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge bg-secondary-subtle border border-secondary text-body-emphasis">Dzisiaj: <?= $linkStats['today'] ?></span>
                <span class="badge bg-secondary-subtle border border-secondary text-body-emphasis">7 dni: <?= $linkStats['week'] ?></span>
                <span class="badge bg-secondary-subtle border border-secondary text-body-emphasis">Łącznie: <?= $linkStats['total'] ?></span>
                <span class="badge bg-warning-subtle border border-warning text-warning-emphasis">Opóźnienie: <?= (int)$link['delay_seconds'] ?>s</span>
              </div>

              <!-- Akcje -->
              <?php if ($isTrashed): ?>
                <!-- Akcje dla linków w koszu (mobile) -->
                <div class="d-flex gap-2 flex-wrap">
                  <form method="post" action="<?= $basePath ?>/admin/index.php?action=restore&id=<?= (int)$link['id'] ?>" class="flex-fill">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(Utils::csrfToken()) ?>" />
                    <button type="submit" class="btn btn-sm btn-success w-100" onclick="return confirm('Przywrócić link z kosza?')">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2v1z"/>
                        <path d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466z"/>
                      </svg> Przywroc
                    </button>
                  </form>
                  <a href="<?= $basePath ?>/admin/index.php?action=edit&id=<?= (int)$link['id'] ?>" class="btn btn-sm btn-primary flex-fill">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                      <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/>
                    </svg> Edytuj
                  </a>
                  <form method="post" action="<?= $basePath ?>/admin/index.php?action=permanent_delete&id=<?= (int)$link['id'] ?>" class="flex-fill">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(Utils::csrfToken()) ?>" />
                    <button type="submit" class="btn btn-sm btn-danger w-100" onclick="return confirm('Czy na pewno chcesz TRWALE usunąć ten link? Ta operacja jest nieodwracalna!')">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z"/>
                        <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z"/>
                      </svg> Usuń trwale
                    </button>
                  </form>
                </div>
              <?php else: ?>
                <!-- Standardowe akcje (mobile) -->
                <div class="d-flex gap-2 flex-wrap">
                  <button class="btn btn-sm btn-primary flex-fill" onclick="copyToClipboard('<?= $fullUrl ?>', this)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                      <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/>
                      <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/>
                    </svg> Kopiuj
                  </button>
                  <a href="<?= $basePath ?>/admin/index.php?action=edit&id=<?= (int)$link['id'] ?>" class="btn btn-sm btn-primary flex-fill">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                      <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/>
                    </svg> Edytuj
                  </a>
                  <a href="<?= $basePath ?>/admin/index.php?action=stats&id=<?= (int)$link['id'] ?>" class="btn btn-sm btn-info flex-fill">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                      <path d="M4 11H2v3h2v-3zm5-4H7v7h2V7zm5-5v12h-2V2h2zm-2-1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1h-2zM6 7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7zm-5 4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3z"/>
                    </svg> Statystyki
                  </a>
                </div>
                <div class="d-flex gap-2 mt-2">
                  <button class="btn btn-sm btn-success flex-fill" onclick="showQRModal(<?= (int)$link['id'] ?>, '<?= htmlspecialchars(addslashes($link['slug'])) ?>')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-qr-code" viewBox="0 0 16 16">
                      <path d="M2 2h2v2H2z"/>
                      <path d="M6 0v6H0V0zM5 1H1v4h4zM4 12H2v2h2z"/>
                      <path d="M6 10v6H0v-6zm-5 1v4h4v-4zm11-9h2v2h-2z"/>
                      <path d="M10 0v6h6V0zm5 1v4h-4V1zM8 1V0h1v2H8v2H7V1zm0 5V4h1v2zM6 8V7h1V6h1v2h1V7h5v1h-4v1H7V8zm0 0v1H2V8H1v1H0V7h3v1zm10 1h-1V7h1zm-1 0h-1v2h2v-1h-1zm-4 0h2v1h-1v1h-1zm2 3v-1h-1v1h-1v1H9v1h3v-2zm0 0h3v1h-2v1h-1zm-4-1v1h1v-2H7v1z"/>
                      <path d="M7 12h1v3h4v1H7zm9 2v2h-3v-1h2v-1z"/>
                    </svg> QR
                  </button>
                  <form method="post" action="<?= $basePath ?>/admin/index.php?action=duplicate&id=<?= (int)$link['id'] ?>" class="flex-fill">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(Utils::csrfToken()) ?>" />
                    <button type="submit" class="btn btn-sm btn-info w-100" onclick="return confirm('Czy na pewno chcesz zduplikowac ten link?')">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/>
                        <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/>
                      </svg> Duplikuj
                    </button>
                  </form>
                  <form method="post" action="<?= $basePath ?>/admin/index.php?action=delete&id=<?= (int)$link['id'] ?>" class="flex-fill">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(Utils::csrfToken()) ?>" />
                    <button type="submit" class="btn btn-sm btn-danger w-100" onclick="return confirm('Usunąć link?')">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z"/>
                        <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z"/>
                      </svg> Usun
                    </button>
                  </form>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="card shadow-sm mt-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center px-3 py-3">
          <div class="text-muted">Wyświetlono <?= ($total === 0) ? 0 : $offset + 1 ?>–<?= min($offset + count($links), $total) ?> z <?= $total ?></div>
          <nav aria-label="Paginacja linków">
            <ul class="pagination mb-0">
              <?php $prevPage = max(1, $page - 1); $nextPage = min($lastPage, $page + 1); ?>
              <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= linksPageUrl($prevPage, $currentSort, $currentOrder, $basePath, $searchQuery ?? '', $perPage, $categoryFilter ?? null, $programFilter ?? null, $currentStatusTab) ?>" aria-label="Poprzednia">&laquo;</a>
              </li>
              <?php
                $range = range(max(1, $page - 2), min($lastPage, $page + 2));
                foreach ($range as $p):
              ?>
                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                  <a class="page-link" href="<?= linksPageUrl($p, $currentSort, $currentOrder, $basePath, $searchQuery ?? '', $perPage, $categoryFilter ?? null, $programFilter ?? null, $currentStatusTab) ?>"><?= $p ?></a>
                </li>
              <?php endforeach; ?>
              <li class="page-item <?= $page >= $lastPage ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= linksPageUrl($nextPage, $currentSort, $currentOrder, $basePath, $searchQuery ?? '', $perPage, $categoryFilter ?? null, $programFilter ?? null, $currentStatusTab) ?>" aria-label="Następna">&raquo;</a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <?php require __DIR__ . '/static/footer.php'; ?>
  <script>
    function copyToClipboard(text, button) {
      navigator.clipboard.writeText(text).then(function() {
        const originalHTML = button.innerHTML;
        button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/></svg>';
        button.classList.remove('btn-primary');
        button.classList.add('btn-success');

        setTimeout(function() {
          button.innerHTML = originalHTML;
          button.classList.remove('btn-success');
          button.classList.add('btn-primary');
        }, 2000);
      }).catch(function(err) {
        alert('Nie udało się skopiować linku');
        console.error('Błąd kopiowania:', err);
      });
    }

    function showQRModal(linkId, slug) {
      fetch('<?= $basePath ?>/admin/index.php?action=qr_code&id=' + linkId + '&mode=display')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            let modalHTML = `
              <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="qrModalLabel">Kod QR: <code>${slug}</code></h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                      <img src="${data.qr_url}" alt="QR Code" class="img-fluid" style="max-width: 600px; width: 100%; height: auto; border: 1px solid #D0D7DE; padding: 10px; border-radius: 4px;" />
                      <div class="mt-3">
                        <small class="text-muted d-block mb-2">URL: <code style="word-break: break-all;">${data.url}</code></small>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zamknij</button>
                      <button type="button" class="btn btn-primary" onclick="printQR('${slug}')">Drukuj</button>
                      <a href="${data.qr_url}" class="btn btn-success" download="qr-${slug}.png">Pobierz PNG</a>
                    </div>
                  </div>
                </div>
              </div>
            `;

            const oldModal = document.getElementById('qrModal');
            if (oldModal) {
              oldModal.remove();
            }

            document.body.insertAdjacentHTML('beforeend', modalHTML);
            const newModal = new bootstrap.Modal(document.getElementById('qrModal'));
            newModal.show();

            document.getElementById('qrModal').addEventListener('hidden.bs.modal', function() {
              this.remove();
            });
          } else {
            alert('Błąd: ' + data.error);
          }
        })
        .catch(error => {
          console.error('Błąd:', error);
          alert('Nie udało się załadować QR code');
        });
    }

    function printQR(slug) {
      const modal = document.getElementById('qrModal');
      const img = modal.querySelector('img');
      const printWindow = window.open('', '', 'height=400,width=600');
      printWindow.document.write('<img src="' + img.src + '" style="max-width: 100%;" />');
      printWindow.document.write('<p style="text-align: center; margin-top: 20px;">QR Code: ' + slug + '</p>');
      printWindow.document.close();
      printWindow.print();
    }

    const tooltipTriggerList = Array.from(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));

    // Funkcje dla akcji masowych
    function toggleAllCheckboxes(checkbox) {
      const checkboxes = document.querySelectorAll('.link-checkbox');
      checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
      });
      updateBulkActions();
    }

    function updateBulkActions() {
      const checkboxes = document.querySelectorAll('.link-checkbox:checked');
      const count = checkboxes.length;
      const bulkCard = document.getElementById('bulkActionsCard');
      const countSpan = document.getElementById('selectedCount');
      const selectedIdsInput = document.getElementById('selectedIdsInput');

      if (count > 0) {
        bulkCard.style.display = 'block';
        countSpan.textContent = count;

        // Zbierz IDs wybranych linków
        const ids = Array.from(checkboxes).map(cb => cb.getAttribute('data-link-id'));
        selectedIdsInput.value = ids.join(',');
      } else {
        bulkCard.style.display = 'none';
        selectedIdsInput.value = '';
      }
    }

    function clearSelection() {
      const checkboxes = document.querySelectorAll('.link-checkbox');
      checkboxes.forEach(cb => cb.checked = false);
      document.getElementById('selectAll').checked = false;
      updateBulkActions();
    }

    // Pokaż/ukryj dodatkowe pola w zależności od wybranej akcji
    document.getElementById('bulk_action')?.addEventListener('change', function() {
      document.getElementById('categorySelectWrapper').style.display = this.value === 'change_category' ? 'block' : 'none';
      document.getElementById('programSelectWrapper').style.display = this.value === 'change_program' ? 'block' : 'none';
      document.getElementById('statusSelectWrapper').style.display = this.value === 'change_status' ? 'block' : 'none';
      document.getElementById('delayInputWrapper').style.display = this.value === 'change_delay' ? 'block' : 'none';
    });

    // Potwierdzenie przed wykonaniem akcji masowej
    document.getElementById('bulkActionForm')?.addEventListener('submit', function(e) {
      const action = document.getElementById('bulk_action').value;
      const count = document.getElementById('selectedCount').textContent;

      const messages = {
        'delete': `Czy na pewno chcesz usunąć ${count} wybranych linków?`,
        'restore': `Czy na pewno chcesz przywrócić ${count} wybranych linków z kosza?`,
        'permanent_delete': `Czy na pewno chcesz TRWALE usunąć ${count} wybranych linków? Ta operacja jest nieodwracalna!`,
        'change_category': `Czy na pewno chcesz zmienić kategorię dla ${count} wybranych linków?`,
        'change_program': `Czy na pewno chcesz zmienić program afiliacyjny dla ${count} wybranych linków?`,
        'change_status': `Czy na pewno chcesz zmienić status dla ${count} wybranych linków?`,
        'change_delay': `Czy na pewno chcesz zmienić opóźnienie dla ${count} wybranych linków?`,
      };

      if (messages[action] && !confirm(messages[action])) {
        e.preventDefault();
      }
    });
  </script>
</body>
</html>
