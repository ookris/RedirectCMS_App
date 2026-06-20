<?php
declare(strict_types=1);

Utils::requireLogin();

// Przygotowanie breadcrumbs
$breadcrumbs = [];
if (!empty($currentPath)) {
    $parts = explode('/', trim($currentPath, '/'));
    $accumulated = '';
    foreach ($parts as $part) {
        $accumulated .= ($accumulated ? '/' : '') . $part;
        $breadcrumbs[] = [
            'name' => $part,
            'path' => $accumulated
        ];
    }
}

$pageTitle = 'Menadżer Plików — RedirectCMS';
require __DIR__ . '/static/head.php';
?>
<body class="bg-light">
<?php require __DIR__ . '/static/navbar.php'; ?>

<div class="container my-4">
  <h1 class="h3 mb-3">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-folder-open me-2" viewBox="0 0 16 16">
      <path d="M.54 3.87.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.826a2 2 0 0 1-1.991-1.819l-.637-7a2 2 0 0 1 .342-1.31zM2.19 4a1 1 0 0 0-.996 1.09l.637 7a1 1 0 0 0 .995.91h10.348a1 1 0 0 0 .995-.91l.637-7A1 1 0 0 0 13.81 4zm4.69-1.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139q.323-.119.684-.12h5.396z"/>
    </svg>
    Menadżer Plików
  </h1>

  <ul class="nav nav-tabs mb-4">
    <li class="nav-item">
      <a class="nav-link <?= $section === 'uploads' ? 'active' : '' ?>" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/admin/index.php?action=file_manager&section=uploads">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cloud-upload me-1" viewBox="0 0 16 16">
          <path fill-rule="evenodd" d="M4.406 1.342A5.53 5.53 0 0 1 8 0c2.69 0 4.923 2 5.166 4.579C14.758 4.804 16 6.137 16 7.773 16 9.569 14.502 11 12.687 11H10a.5.5 0 0 1 0-1h2.688C13.979 10 15 8.988 15 7.773c0-1.216-1.02-2.228-2.313-2.228h-.5v-.5C12.188 2.825 10.328 1 8 1a4.53 4.53 0 0 0-2.941 1.1c-.757.652-1.153 1.438-1.153 2.055v.448l-.445.049C2.064 4.805 1 5.952 1 7.318 1 8.785 2.23 10 3.781 10H6a.5.5 0 0 1 0 1H3.781C1.708 11 0 9.366 0 7.318c0-1.763 1.266-3.223 2.942-3.593.143-.863.698-1.723 1.464-2.383"/>
          <path fill-rule="evenodd" d="M7.646 4.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 5.707V14.5a.5.5 0 0 1-1 0V5.707L5.354 7.854a.5.5 0 1 1-.708-.708z"/>
        </svg>
        Przesłane pliki
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= $section === 'storage' ? 'active' : '' ?>" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/admin/index.php?action=file_manager&section=storage">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-archive me-1" viewBox="0 0 16 16">
          <path d="M0 2a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 12.5V5a1 1 0 0 1-1-1zm2 3v7.5A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5V5zm13-3H1v2h14zM5 7.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/>
        </svg>
        Magazyn
      </a>
    </li>
  </ul>

  <!-- Statystyki dysku -->
  <div class="row mb-4">
    <div class="col-md-4">
      <div class="card">
        <div class="card-body">
          <h6 class="card-subtitle mb-2 text-muted">Całkowity rozmiar</h6>
          <p class="card-text h5"><?= htmlspecialchars(FileManager::formatSize($stats['total_size'])) ?></p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card">
        <div class="card-body">
          <h6 class="card-subtitle mb-2 text-muted">Liczba plików</h6>
          <p class="card-text h5"><?= number_format($stats['file_count'], 0, ',', ' ') ?></p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card">
        <div class="card-body">
          <h6 class="card-subtitle mb-2 text-muted">Katalog główny</h6>
          <p class="card-text h5">/<?= htmlspecialchars($section) ?></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Breadcrumbs -->
  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">
        <a href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/admin/index.php?action=file_manager&section=<?= urlencode($section) ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house-door" viewBox="0 0 16 16">
            <path d="M8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4.5a.5.5 0 0 0 .5-.5v-4h2v4a.5.5 0 0 0 .5.5H14a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM2.5 14V7.707l5.5-5.5 5.5 5.5V14H10v-4a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5v4z"/>
          </svg>
          <?= htmlspecialchars($section) ?>
        </a>
      </li>
      <?php foreach ($breadcrumbs as $crumb): ?>
        <li class="breadcrumb-item">
          <a href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/admin/index.php?action=file_manager&section=<?= urlencode($section) ?>&path=<?= urlencode($crumb['path']) ?>">
            <?= htmlspecialchars($crumb['name']) ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ol>
  </nav>

  <?php if (!empty($_SESSION['toast'])): ?>
    <div class="alert alert-<?= htmlspecialchars($_SESSION['toast']['type'] ?? 'info') ?> alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($_SESSION['toast']['message'] ?? '') ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['toast']); ?>
  <?php endif; ?>

  <!-- Tabela plików -->
  <div class="card">
    <div class="card-header">
      <h5 class="card-title mb-0">Pliki i foldery</h5>
    </div>
    <div class="card-body p-0">
      <?php if (empty($files)): ?>
        <div class="text-center p-4 text-muted">
          <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-inbox mb-3" viewBox="0 0 16 16">
            <path d="M4.98 4a.5.5 0 0 0-.39.188L1.54 8H6a.5.5 0 0 1 .5.5 1.5 1.5 0 1 0 3 0A.5.5 0 0 1 10 8h4.46l-3.05-3.812A.5.5 0 0 0 11.02 4zm-1.17-.437A1.5 1.5 0 0 1 4.98 3h6.04a1.5 1.5 0 0 1 1.17.563l3.7 4.625a.5.5 0 0 1 .106.374l-.39 3.124A1.5 1.5 0 0 1 14.117 13H1.883a1.5 1.5 0 0 1-1.489-1.314l-.39-3.124a.5.5 0 0 1 .106-.374z"/>
          </svg>
          <p>Brak plików w tym katalogu</p>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover table-striped mb-0">
            <thead>
              <tr>
                <th style="width: 40px;"></th>
                <th>Nazwa</th>
                <th style="width: 120px;">Rozmiar</th>
                <th style="width: 180px;">Data modyfikacji</th>
                <?php if (!$readonly): ?>
                <th style="width: 140px;">Status</th>
                <?php endif; ?>
                <th style="width: 200px;" class="text-end">Akcje</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($files as $file): ?>
                <tr>
                  <!-- Ikona typu -->
                  <td class="text-center">
                    <?php if ($file['type'] === 'directory'): ?>
                      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-folder-fill text-warning" viewBox="0 0 16 16">
                        <path d="M9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.825a2 2 0 0 1-1.991-1.819l-.637-7a2 2 0 0 1 .342-1.31L.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3m-8.322.12q-.322 0-.684.12L1.5 2.98a1 1 0 0 1 1-.98h3.672a1 1 0 0 1 .707.293L7.586 3z"/>
                      </svg>
                    <?php elseif (in_array(strtolower($file['extension'] ?? ''), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])): ?>
                      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-file-earmark-image text-primary" viewBox="0 0 16 16">
                        <path d="M6.502 7a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/>
                        <path d="M14 14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5L14 4.5zM4 1a1 1 0 0 0-1 1v10l2.224-2.224a.5.5 0 0 1 .61-.075L8 11l2.157-3.02a.5.5 0 0 1 .76-.063L13 10V4.5h-2A1.5 1.5 0 0 1 9.5 3V1z"/>
                      </svg>
                    <?php else: ?>
                      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-file-earmark text-secondary" viewBox="0 0 16 16">
                        <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5z"/>
                      </svg>
                    <?php endif; ?>
                  </td>

                  <!-- Nazwa -->
                  <td>
                    <?php if ($file['type'] === 'directory'): ?>
                      <a href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/admin/index.php?action=file_manager&section=<?= urlencode($section) ?>&path=<?= urlencode($file['path']) ?>" class="text-decoration-none fw-semibold">
                        <?= htmlspecialchars($file['name']) ?>
                      </a>
                    <?php else: ?>
                      <div class="d-flex align-items-center gap-2">
                        <?php if (!$readonly && in_array(strtolower($file['extension'] ?? ''), ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                          <img src="<?= htmlspecialchars($basePath . '/' . $section . '/' . $file['path'], ENT_QUOTES, 'UTF-8') ?>"
                            alt="<?= htmlspecialchars($file['name']) ?>"
                            class="rounded"
                            style="width: 40px; height: 40px; object-fit: cover;">
                        <?php endif; ?>
                        <span class="text-truncate" style="max-width: 300px;" title="<?= htmlspecialchars($file['name']) ?>">
                          <?= htmlspecialchars($file['name']) ?>
                        </span>
                      </div>
                    <?php endif; ?>
                  </td>

                  <!-- Rozmiar -->
                  <td>
                    <?php if ($file['type'] === 'file'): ?>
                      <?= htmlspecialchars(FileManager::formatSize($file['size'])) ?>
                    <?php else: ?>
                      <span class="text-muted">—</span>
                    <?php endif; ?>
                  </td>

                  <!-- Data modyfikacji -->
                  <td>
                    <small class="text-muted">
                      <?= date('Y-m-d H:i:s', $file['modified']) ?>
                    </small>
                  </td>

                  <?php if (!$readonly): ?>
                  <!-- Status referencji -->
                  <td>
                    <?php if ($file['type'] === 'file'): ?>
                      <?php if ($file['is_referenced']): ?>
                        <span class="badge bg-success-subtle border border-success text-success-emphasis" title="Plik jest używany w bazie danych">
                          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-database-check" viewBox="0 0 16 16">
                            <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m1.679-4.493-1.335 2.226a.75.75 0 0 1-1.174.144l-.774-.773a.5.5 0 0 1 .708-.708l.547.548 1.17-1.951a.5.5 0 1 1 .858.514"/>
                            <path d="M12.096 6.223A5 5 0 0 0 13 5.698V7c0 .289-.213.654-.753 1.007a4.5 4.5 0 0 1 1.753.25V4c0-1.007-.875-1.755-1.904-2.223C11.022 1.289 9.573 1 8 1s-3.022.289-4.096.777C2.875 2.245 2 2.993 2 4v9c0 1.007.875 1.755 1.904 2.223C4.978 15.71 6.427 16 8 16c.536 0 1.058-.034 1.555-.097a4.5 4.5 0 0 1-.813-.927Q8.378 15 8 15c-1.464 0-2.766-.27-3.682-.687C3.356 13.875 3 13.373 3 13v-1.302c.271.202.58.378.904.525C4.978 12.71 6.427 13 8 13h.027a4.6 4.6 0 0 1 0-1H8c-1.464 0-2.766-.27-3.682-.687C3.356 10.875 3 10.373 3 10V8.698c.271.202.58.378.904.525C4.978 9.71 6.427 10 8 10q.393 0 .774-.024a4.5 4.5 0 0 1 1.102-1.132C9.298 8.944 8.666 9 8 9c-1.464 0-2.766-.27-3.682-.687C3.356 7.875 3 7.373 3 7V5.698c.271.202.58.378.904.525C4.978 6.711 6.427 7 8 7s3.022-.289 4.096-.777M3 4c0-.374.356-.875 1.318-1.313C5.234 2.271 6.536 2 8 2s2.766.27 3.682.687C12.644 3.125 13 3.627 13 4c0 .374-.356.875-1.318 1.313C10.766 5.729 9.464 6 8 6s-2.766-.27-3.682-.687C3.356 4.875 3 4.373 3 4"/>
                          </svg>
                          W użyciu
                        </span>
                      <?php else: ?>
                        <span class="badge bg-warning-subtle border border-warning text-warning-emphasis text-dark" title="Plik nie jest używany w bazie danych">
                          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
                            <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"/>
                            <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/>
                          </svg>
                          Nieużywany
                        </span>
                      <?php endif; ?>
                    <?php else: ?>
                      <span class="text-muted">—</span>
                    <?php endif; ?>
                  </td>
                  <?php endif; ?>

                  <!-- Akcje -->
                  <td class="text-end">
                    <?php if ($file['type'] === 'file'): ?>
                      <button type="button" class="btn btn-sm btn-outline-primary me-1"
                        data-bs-toggle="modal"
                        data-bs-target="#detailsModal"
                        data-file-path="<?= htmlspecialchars($file['path']) ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-info-circle" viewBox="0 0 16 16">
                          <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                          <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
                        </svg>
                        Szczegóły
                      </button>
                      <?php if (!$readonly && !$file['is_referenced']): ?>
                        <button type="button" class="btn btn-sm btn-outline-danger"
                          data-bs-toggle="modal"
                          data-bs-target="#deleteModal"
                          data-file-path="<?= htmlspecialchars($file['path']) ?>"
                          data-file-name="<?= htmlspecialchars($file['name']) ?>">
                          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                            <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                          </svg>
                          Usuń
                        </button>
                      <?php endif; ?>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Modal szczegółów pliku -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailsModalLabel">Szczegóły pliku</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="detailsContent">
          <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Ładowanie...</span>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zamknij</button>
      </div>
    </div>
  </div>
</div>

<?php if (!$readonly): ?>
<!-- Modal potwierdzenia usunięcia -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalLabel">Potwierdź usunięcie</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Czy na pewno chcesz usunąć plik <strong id="fileNameDisplay"></strong>?</p>
        <p class="text-muted small mb-0">Ta operacja jest nieodwracalna.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
        <form method="POST" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/admin/index.php?action=file_manager_delete" id="deleteForm">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
          <input type="hidden" name="file_path" id="filePathInput" value="">
          <input type="hidden" name="section" value="<?= htmlspecialchars($section) ?>">
          <input type="hidden" name="return_path" value="<?= htmlspecialchars($currentPath) ?>">
          <button type="submit" class="btn btn-danger">Usuń plik</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const basePath = '<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>';

  // Obsługa modala szczegółów
  const detailsModal = document.getElementById('detailsModal');
  if (detailsModal) {
    detailsModal.addEventListener('show.bs.modal', function(event) {
      const button = event.relatedTarget;
      const filePath = button.getAttribute('data-file-path');
      const detailsContent = document.getElementById('detailsContent');

      // Pokaż spinner
      detailsContent.innerHTML = `
        <div class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Ładowanie...</span>
          </div>
        </div>
      `;

      // Pobierz szczegóły pliku
      fetch(basePath + '/admin/index.php?action=file_details&section=<?= urlencode($section) ?>&path=' + encodeURIComponent(filePath))
        .then(response => response.json())
        .then(data => {
          if (data.error) {
            detailsContent.innerHTML = `<div class="alert bg-danger-subtle border border-danger text-danger-emphasis">${data.error}</div>`;
            return;
          }

          let html = '';

          // Podgląd obrazka (tylko dla uploads — storage jest zablokowany przez .htaccess)
          <?php if (!$readonly): ?>
          if (data.is_image) {
            html += `
              <div class="text-center mb-4">
                <img src="${basePath}/<?= $section ?>/${data.path}" alt="${data.name}"
                  class="img-fluid rounded border" style="max-height: 400px;">
              </div>
            `;
          }
          <?php endif; ?>

          // Podstawowe informacje
          html += `
            <h6 class="mb-3">Informacje o pliku</h6>
            <table class="table table-sm">
              <tr>
                <th style="width: 180px;">Nazwa pliku:</th>
                <td>${data.name}</td>
              </tr>
              <tr>
                <th>Ścieżka:</th>
                <td><code>${data.path}</code></td>
              </tr>
              <tr>
                <th>Rozmiar:</th>
                <td>${data.size_formatted}</td>
              </tr>
              <tr>
                <th>Data modyfikacji:</th>
                <td>${data.modified}</td>
              </tr>
              <tr>
                <th>Rozszerzenie:</th>
                <td>${data.extension || '—'}</td>
              </tr>
              <?php if (!$readonly): ?>
              <tr>
                <th>Status:</th>
                <td>
                  ${data.is_referenced
                    ? '<span class="badge bg-success-subtle border border-success text-success-emphasis">W użyciu</span>'
                    : '<span class="badge bg-warning-subtle border border-warning text-warning-emphasis text-dark">Nieużywany</span>'}
                </td>
              </tr>
              <?php endif; ?>
            </table>
          `;

          <?php if (!$readonly): ?>
          // Gdzie plik jest używany
          const usage = data.usage;
          const hasUsage = Object.keys(usage).length > 0;

          if (hasUsage) {
            html += `<h6 class="mt-4 mb-3">Plik jest używany w:</h6>`;

            if (usage.links && usage.links.length > 0) {
              html += `
                <div class="mb-3">
                  <strong>Linki (${usage.links.length}):</strong>
                  <ul class="mb-0 mt-1">
              `;
              usage.links.forEach(link => {
                html += `<li><a href="${basePath}/admin/index.php?action=link_edit&id=${link.id}" target="_blank">${link.slug}</a></li>`;
              });
              html += `</ul></div>`;
            }

            if (usage.galleries && usage.galleries.length > 0) {
              html += `
                <div class="mb-3">
                  <strong>Galerie (${usage.galleries.length}):</strong>
                  <ul class="mb-0 mt-1">
              `;
              usage.galleries.forEach(gallery => {
                html += `<li>Link ID: ${gallery.link_id}</li>`;
              });
              html += `</ul></div>`;
            }

            if (usage.categories && usage.categories.length > 0) {
              html += `
                <div class="mb-3">
                  <strong>Kategorie (${usage.categories.length}):</strong>
                  <ul class="mb-0 mt-1">
              `;
              usage.categories.forEach(cat => {
                html += `<li><a href="${basePath}/admin/index.php?action=category_edit&id=${cat.id}" target="_blank">${cat.name}</a></li>`;
              });
              html += `</ul></div>`;
            }

            if (usage.branding && usage.branding.length > 0) {
              html += `
                <div class="mb-3">
                  <strong>Branding (${usage.branding.length}):</strong>
                  <ul class="mb-0 mt-1">
              `;
              usage.branding.forEach(item => {
                const label = item.key === 'branding_logo' ? 'Logo' : 'Favicon';
                html += `<li>${label}</li>`;
              });
              html += `</ul></div>`;
            }
          } else {
            html += `
              <div class="alert bg-info-subtle border border-info text-info-emphasis mt-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle me-2" viewBox="0 0 16 16">
                  <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                  <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
                </svg>
                Ten plik nie jest obecnie używany w systemie.
              </div>
            `;
          }
          <?php endif; ?>

          detailsContent.innerHTML = html;
        })
        .catch(error => {
          detailsContent.innerHTML = `<div class="alert bg-danger-subtle border border-danger text-danger-emphasis">Błąd podczas ładowania danych: ${error}</div>`;
        });
    });
  }

  <?php if (!$readonly): ?>
  // Obsługa modala usuwania
  const deleteModal = document.getElementById('deleteModal');
  if (deleteModal) {
    deleteModal.addEventListener('show.bs.modal', function(event) {
      const button = event.relatedTarget;
      const filePath = button.getAttribute('data-file-path');
      const fileName = button.getAttribute('data-file-name');

      document.getElementById('filePathInput').value = filePath;
      document.getElementById('fileNameDisplay').textContent = fileName;
    });
  }
  <?php endif; ?>
});
</script>

<?php require __DIR__ . '/static/footer.php'; ?>
</body>
</html>
