<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../LinkRepository.php';
require_once __DIR__ . '/../CategoryRepository.php';
require_once __DIR__ . '/../TagRepository.php';
require_once __DIR__ . '/../AffiliateProgramRepository.php';
require_once __DIR__ . '/../StatsRepository.php';
require_once __DIR__ . '/../SettingsRepository.php';
require_once __DIR__ . '/../AuditService.php';
require_once __DIR__ . '/../CustomFieldService.php';
require_once __DIR__ . '/../ImageUploader.php';

class LinkController extends BaseController
{
    public function links(): void
    {
        Utils::requireLogin();

        ['sortBy' => $sortBy, 'sortOrder' => $sortOrder, 'searchQuery' => $searchQuery,
         'categoryFilter' => $categoryFilter, 'programFilter' => $programFilter] = $this->parseListingParams();
        $perPage = max(5, min(200, (int)($_GET['per_page'] ?? 25)));
        $page = max(1, (int)($_GET['page'] ?? 1));

        // Status tab filter
        $statusTab = trim((string)($_GET['status'] ?? 'published'));
        $validStatuses = ['published', 'scheduled', 'draft', 'expired', 'trashed', 'all'];
        if (!in_array($statusTab, $validStatuses, true)) {
            $statusTab = 'published';
        }

        // Pobierz wszystkie kategorie i programy afiliacyjne dla filtrów
        $categories = (new CategoryRepository($this->pdo))->list(1000, 0);
        $affiliatePrograms = (new AffiliateProgramRepository($this->pdo))->listAll();

        $linkRepo = new LinkRepository($this->pdo);

        // Get counts for all tabs
        $tabCounts = [
            'published' => $linkRepo->count($categoryFilter, null, $searchQuery, false, $programFilter, 'published'),
            'scheduled' => $linkRepo->count($categoryFilter, null, $searchQuery, false, $programFilter, 'scheduled'),
            'draft' => $linkRepo->count($categoryFilter, null, $searchQuery, false, $programFilter, 'draft'),
            'expired' => $linkRepo->count($categoryFilter, null, $searchQuery, false, $programFilter, 'expired'),
            'trashed' => $linkRepo->count($categoryFilter, null, $searchQuery, false, $programFilter, 'trashed'),
        ];

        $total = $linkRepo->count($categoryFilter, null, $searchQuery, false, $programFilter, $statusTab);
        $lastPage = max(1, (int)ceil($total / $perPage));

        if ($page > $lastPage) {
            $page = $lastPage;
        }

        $offset = ($page - 1) * $perPage;
        $links = $linkRepo->list($perPage, $offset, $categoryFilter, null, $sortBy, $sortOrder, $searchQuery, false, false, $programFilter, $statusTab);

        $stats = [];
        try {
            $stats = (new StatsRepository($this->pdo))->getQuickStats();
        } catch (\Throwable $e) {
            $stats = [];
        }

        // Reakcje — zbiorcze liczniki dla wyświetlanych linków
        $reactionStats = [];
        try {
            require_once __DIR__ . '/../ReactionRepository.php';
            $reactionRepo = new ReactionRepository($this->pdo);
            $linkIds = array_map(fn($l) => (int)$l['id'], $links);
            $reactionStats = $reactionRepo->getCountsForLinks($linkIds);
        } catch (\Throwable $e) {
            $reactionStats = [];
        }

        $settingsRepo = new SettingsRepository($this->pdo);
        $trashAutoDeleteDays = max(1, min(365, (int)$settingsRepo->get('trash_auto_delete_days', 30)));

        $this->view('links', [
            'links' => $links,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'searchQuery' => $searchQuery,
            'categoryFilter' => $categoryFilter,
            'programFilter' => $programFilter,
            'categories' => $categories,
            'affiliatePrograms' => $affiliatePrograms,
            'perPage' => $perPage,
            'page' => $page,
            'lastPage' => $lastPage,
            'total' => $total,
            'stats' => $stats,
            'reactionStats' => $reactionStats,
            'statusTab' => $statusTab,
            'tabCounts' => $tabCounts,
            'trashAutoDeleteDays' => $trashAutoDeleteDays,
        ]);
    }

    public function exportLinks(): void
    {
        Utils::requireLogin();

        $searchQuery = trim((string)($_GET['search'] ?? ''));
        $categoryFilter = !empty($_GET['category_filter']) ? (int)$_GET['category_filter'] : null;
        $programFilter = !empty($_GET['program_filter']) ? (int)$_GET['program_filter'] : null;

        // Pobierz wszystkie linki (bez paginacji) z zastosowanymi filtrami
        $linkRepo = new LinkRepository($this->pdo);
        $links = $linkRepo->list(10000, 0, $categoryFilter, null, 'created_at', 'DESC', $searchQuery, false, false, $programFilter);

        // Przygotuj nazwę pliku z datą
        $filename = 'linki_' . date('Y-m-d_H-i-s') . '.csv';

        // Ustaw nagłówki HTTP dla pobrania pliku CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Otwórz strumień wyjściowy
        $output = fopen('php://output', 'w');

        // Dodaj BOM dla UTF-8 (poprawia wyświetlanie polskich znaków w Excel)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Nagłówki kolumn
        fputcsv($output, [
            'ID',
            'Slug',
            'Tytuł strony',
            'URL docelowy',
            'Kategoria',
            'Program afiliacyjny',
            'Opóźnienie (sekundy)',
            'Data utworzenia',
            'Data publikacji',
            'Data wygaśnięcia',
            'Opis strony',
            'Tagi',
        ], ';');

        // Dane linków
        foreach ($links as $link) {
            $tags = $linkRepo->getTagsForLink((int)$link['id']);
            $tagNames = array_map(fn($t) => $t['name'], $tags);

            fputcsv($output, [
                $link['id'],
                $link['slug'],
                $link['page_title'] ?? '',
                $link['target_url'],
                $link['category_name'] ?? '',
                $link['affiliate_program_name'] ?? '',
                $link['delay_seconds'],
                !empty($link['created_at']) ? date('Y-m-d H:i:s', strtotime($link['created_at'])) : '',
                $link['publish_at'] ?? '',
                $link['expires_at'] ?? '',
                $link['page_description'] ?? '',
                implode(', ', $tagNames),
            ], ';');
        }

        fclose($output);
        exit;
    }

    public function importLinksGet(): void
    {
        Utils::requireLogin();

        // Pobierz kategorie i programy afiliacyjne dla informacji
        $categories = (new CategoryRepository($this->pdo))->list(1000, 0);
        $affiliatePrograms = (new AffiliateProgramRepository($this->pdo))->listAll();

        $this->view('import_links', [
            'categories' => $categories,
            'affiliatePrograms' => $affiliatePrograms,
        ]);
    }

    public function importLinksPost(): void
    {
        Utils::requireLogin();

        if ($this->isLicenseBlocked()) {
            $_SESSION['toast_message'] = 'Import linków jest zablokowany z powodu nieważnej licencji.';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php');
            exit;
        }

        if (!Utils::verifyCsrf($_POST['csrf'] ?? '')) {
            $_SESSION['toast_message'] = 'Błąd weryfikacji CSRF';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=import_links');
            exit;
        }

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['toast_message'] = 'Błąd przesyłania pliku';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=import_links');
            exit;
        }

        $file = $_FILES['csv_file'];
        $skipFirstRow = ($_POST['skip_first_row'] ?? '0') === '1';
        $updateExisting = ($_POST['update_existing'] ?? '0') === '1';

        // Column mapping from JS (JSON: {"0":"slug","1":"target_url",...})
        $columnMappingRaw = $_POST['column_mapping'] ?? '{}';
        if (!is_string($columnMappingRaw)) {
            $columnMappingRaw = '{}';
        }
        $columnMapping = json_decode($columnMappingRaw, true);

        // Whitelist allowed field names to prevent injection of invalid keys
        $allowedFields = ['slug', 'target_url', 'page_title', 'page_description', 'category', 'affiliate_program', 'delay_seconds', 'publish_at', 'expires_at', 'tags'];
        if (is_array($columnMapping)) {
            $columnMapping = array_filter($columnMapping, fn($v) => in_array($v, $allowedFields, true));
        } else {
            $columnMapping = [];
        }

        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($fileExtension !== 'csv') {
            $_SESSION['toast_message'] = 'Dozwolone są tylko pliki CSV';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=import_links');
            exit;
        }

        $handle = fopen($file['tmp_name'], 'r');
        if ($handle === false) {
            $_SESSION['toast_message'] = 'Nie można otworzyć pliku CSV';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=import_links');
            exit;
        }

        // Detect separator from first line
        $firstLine = fgets($handle);
        rewind($handle);
        $separator = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

        $linkRepo = new LinkRepository($this->pdo);
        $categoryRepo = new CategoryRepository($this->pdo);
        $affiliateRepo = new AffiliateProgramRepository($this->pdo);
        $tagRepo = new TagRepository($this->pdo);

        $allCategories = $categoryRepo->list(10000, 0);
        $allPrograms = $affiliateRepo->listAll();

        $categoryMap = [];
        foreach ($allCategories as $cat) {
            $categoryMap[strtolower($cat['name'])] = $cat['id'];
        }
        $programMap = [];
        foreach ($allPrograms as $prog) {
            $programMap[strtolower($prog['name'])] = $prog['id'];
        }

        // If no column mapping provided, use legacy fixed format
        $useMapping = !empty($columnMapping);

        $rowNumber = 0;
        $successCount = 0;
        $updateCount = 0;
        $errorCount = 0;
        $errors = [];

        if ($skipFirstRow) {
            fgetcsv($handle, 0, $separator);
            $rowNumber++;
        }

        while (($data = fgetcsv($handle, 0, $separator)) !== false) {
            $rowNumber++;

            if ($useMapping) {
                // Map columns using user-defined mapping
                $mapped = [];
                foreach ($columnMapping as $colIndex => $fieldKey) {
                    $mapped[$fieldKey] = isset($data[(int)$colIndex]) ? trim($data[(int)$colIndex]) : '';
                }

                $slug = $mapped['slug'] ?? '';
                $targetUrl = $mapped['target_url'] ?? '';
                $pageTitle = !empty($mapped['page_title']) ? $mapped['page_title'] : null;
                $pageDescription = !empty($mapped['page_description']) ? $mapped['page_description'] : null;
                $categoryName = !empty($mapped['category']) ? $mapped['category'] : null;
                $programName = !empty($mapped['affiliate_program']) ? $mapped['affiliate_program'] : null;
                $delaySeconds = !empty($mapped['delay_seconds']) ? (int)$mapped['delay_seconds'] : 5;
                $publishAt = !empty($mapped['publish_at']) ? $mapped['publish_at'] : null;
                $expiresAt = !empty($mapped['expires_at']) ? $mapped['expires_at'] : null;
                $tagsStr = !empty($mapped['tags']) ? $mapped['tags'] : null;
            } else {
                // Legacy fixed format: ID;Slug;Title;URL;Category;Program;Delay;Created;Publish;Expires;Description
                if (count($data) < 4 || empty($data[1]) || empty($data[3])) {
                    $errors[] = "Wiersz $rowNumber: Brak wymaganych pól (Alias i URL docelowy)";
                    $errorCount++;
                    continue;
                }
                $slug = trim($data[1]);
                $targetUrl = trim($data[3]);
                $pageTitle = !empty($data[2]) ? trim($data[2]) : null;
                $categoryName = !empty($data[4]) ? trim($data[4]) : null;
                $programName = !empty($data[5]) ? trim($data[5]) : null;
                $delaySeconds = !empty($data[6]) ? (int)$data[6] : 5;
                $publishAt = !empty($data[8]) ? trim($data[8]) : null;
                $expiresAt = !empty($data[9]) ? trim($data[9]) : null;
                $pageDescription = !empty($data[10]) ? trim($data[10]) : null;
                $tagsStr = null;
            }

            if (empty($slug) || empty($targetUrl)) {
                $errors[] = "Wiersz $rowNumber: Brak wymaganych pól (Alias i URL docelowy)";
                $errorCount++;
                continue;
            }

            try {
                $slug = Utils::sanitizeSlug($slug);
            } catch (\InvalidArgumentException $e) {
                $errors[] = "Wiersz $rowNumber: Nieprawidłowy alias - " . $e->getMessage();
                $errorCount++;
                continue;
            }

            if (!filter_var($targetUrl, FILTER_VALIDATE_URL)) {
                $errors[] = "Wiersz $rowNumber: Nieprawidłowy URL '$targetUrl'";
                $errorCount++;
                continue;
            }

            $categoryId = $categoryName !== null ? ($categoryMap[strtolower($categoryName)] ?? null) : null;
            $programId = $programName !== null ? ($programMap[strtolower($programName)] ?? null) : null;

            try {
                $existing = $linkRepo->findBySlug($slug);

                if ($existing && $updateExisting) {
                    $linkRepo->update((int)$existing['id'], [
                        'target_url' => $targetUrl,
                        'page_title' => $pageTitle,
                        'page_description' => $pageDescription,
                        'category_id' => $categoryId,
                        'affiliate_program_id' => $programId,
                        'delay_seconds' => $delaySeconds,
                        'publish_at' => $publishAt,
                        'expires_at' => $expiresAt,
                    ]);

                    if ($tagsStr !== null) {
                        $tagIds = [];
                        foreach (array_map('trim', explode(',', $tagsStr)) as $tagName) {
                            if ($tagName !== '') {
                                $tagIds[] = $tagRepo->findOrCreate($tagName);
                            }
                        }
                        $tagRepo->setTagsForLink((int)$existing['id'], $tagIds);
                    }

                    $updateCount++;
                } elseif (!$existing) {
                    $newId = $linkRepo->create([
                        'slug' => $slug,
                        'target_url' => $targetUrl,
                        'page_title' => $pageTitle,
                        'page_description' => $pageDescription,
                        'category_id' => $categoryId,
                        'affiliate_program_id' => $programId,
                        'delay_seconds' => $delaySeconds,
                        'publish_at' => $publishAt,
                        'expires_at' => $expiresAt,
                    ]);

                    if ($tagsStr !== null && $newId) {
                        $tagIds = [];
                        foreach (array_map('trim', explode(',', $tagsStr)) as $tagName) {
                            if ($tagName !== '') {
                                $tagIds[] = $tagRepo->findOrCreate($tagName);
                            }
                        }
                        $tagRepo->setTagsForLink((int)$newId, $tagIds);
                    }

                    $successCount++;
                } else {
                    $errors[] = "Wiersz $rowNumber: Link o aliasie '$slug' już istnieje (włącz opcję aktualizacji)";
                    $errorCount++;
                }
            } catch (\Throwable $e) {
                $errors[] = "Wiersz $rowNumber: Błąd zapisu - " . $e->getMessage();
                $errorCount++;
            }
        }

        fclose($handle);

        $message = "Import zakończony. ";
        if ($successCount > 0) $message .= "Dodano: $successCount. ";
        if ($updateCount > 0) $message .= "Zaktualizowano: $updateCount. ";
        if ($errorCount > 0) $message .= "Błędy: $errorCount. ";

        $_SESSION['toast_message'] = $message;
        $_SESSION['toast_type'] = $errorCount === 0 ? 'success' : 'warning';

        if (!empty($errors)) {
            $_SESSION['import_errors'] = $errors;
        }

        header('Location: ' . $this->basePath . '/admin/index.php?action=import_links');
        exit;
    }

    public function bulkAction(): void
    {
        Utils::requireLogin();

        // Walidacja CSRF
        if (!Utils::verifyCsrf($_POST['csrf'] ?? '')) {
            $_SESSION['toast_message'] = 'Błąd weryfikacji CSRF';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=links');
            exit;
        }

        $action = $_POST['bulk_action'] ?? '';

        // Przy zablokowanej licencji tylko usuwanie/przywracanie jest dozwolone
        $writeActions = ['change_status', 'change_delay', 'change_category', 'change_program'];
        if ($this->isLicenseBlocked() && in_array($action, $writeActions, true)) {
            $_SESSION['toast_message'] = 'Edycja linków jest zablokowana z powodu nieważnej licencji.';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=links');
            exit;
        }
        $selectedIdsStr = $_POST['selected_ids'] ?? '';
        $selectedIds = array_filter(array_map('intval', explode(',', $selectedIdsStr)));

        if (empty($selectedIds) || empty($action)) {
            $_SESSION['toast_message'] = 'Nie wybrano linków lub akcji';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=links');
            exit;
        }

        $linkRepo = new LinkRepository($this->pdo);
        $successCount = 0;

        try {
            $settingsRepo = new SettingsRepository($this->pdo);
            $trashMode = (string)$settingsRepo->get('trash_mode', 'auto_delete');

            switch ($action) {
                case 'delete':
                    if ($trashMode === 'hard_delete') {
                        $this->deleteLinksWithImages($selectedIds, $linkRepo);
                        $successCount = count($selectedIds);
                        $_SESSION['toast_message'] = "Trwale usunięto $successCount linków";
                    } else {
                        foreach ($selectedIds as $id) {
                            try {
                                $linkRepo->delete($id, false);
                                $successCount++;
                            } catch (\Throwable $e) {}
                        }
                        $_SESSION['toast_message'] = "Przeniesiono $successCount z " . count($selectedIds) . " linków do kosza";
                    }
                    $_SESSION['toast_type'] = 'success';
                    break;

                case 'restore':
                    foreach ($selectedIds as $id) {
                        try {
                            $linkRepo->restore($id);
                            $successCount++;
                        } catch (\Throwable $e) {}
                    }
                    $_SESSION['toast_message'] = "Przywrócono $successCount z " . count($selectedIds) . " linków";
                    $_SESSION['toast_type'] = 'success';
                    break;

                case 'permanent_delete':
                    $this->deleteLinksWithImages($selectedIds, $linkRepo);
                    $successCount = count($selectedIds);
                    $_SESSION['toast_message'] = "Trwale usunięto $successCount linków";
                    $_SESSION['toast_type'] = 'success';
                    break;

                case 'change_category':
                    $categoryId = !empty($_POST['bulk_category']) ? (int)$_POST['bulk_category'] : null;
                    foreach ($selectedIds as $id) {
                        try {
                            $link = $linkRepo->getById($id);
                            if ($link) {
                                $linkRepo->update(
                                    $id,
                                    $link['slug'],
                                    $link['target_url'],
                                    (int)$link['delay_seconds'],
                                    $link['page_title'],
                                    $link['page_description'],
                                    $link['og_image'],
                                    $categoryId,
                                    !empty($link['affiliate_program_id']) ? (int)$link['affiliate_program_id'] : null,
                                    $link['publish_at'],
                                    $link['expires_at'],
                                    $link['password_hash'],
                                    $link['password_hint']
                                );
                                $successCount++;
                            }
                        } catch (\Throwable $e) {
                            // Kontynuuj aktualizowanie pozostałych
                        }
                    }
                    $_SESSION['toast_message'] = "Zaktualizowano kategorię dla $successCount z " . count($selectedIds) . " linków";
                    $_SESSION['toast_type'] = 'success';
                    break;

                case 'change_program':
                    $programId = !empty($_POST['bulk_program']) ? (int)$_POST['bulk_program'] : null;
                    foreach ($selectedIds as $id) {
                        try {
                            $link = $linkRepo->getById($id);
                            if ($link) {
                                $linkRepo->update(
                                    $id,
                                    $link['slug'],
                                    $link['target_url'],
                                    (int)$link['delay_seconds'],
                                    $link['page_title'],
                                    $link['page_description'],
                                    $link['og_image'],
                                    !empty($link['category_id']) ? (int)$link['category_id'] : null,
                                    $programId,
                                    $link['publish_at'],
                                    $link['expires_at'],
                                    $link['password_hash'],
                                    $link['password_hint']
                                );
                                $successCount++;
                            }
                        } catch (\Throwable $e) {
                            // Kontynuuj aktualizowanie pozostałych
                        }
                    }
                    $_SESSION['toast_message'] = "Zaktualizowano program afiliacyjny dla $successCount z " . count($selectedIds) . " linków";
                    $_SESSION['toast_type'] = 'success';
                    break;

                case 'change_status':
                    $newStatus = in_array($_POST['bulk_status'] ?? '', ['published', 'draft'], true)
                        ? $_POST['bulk_status'] : 'published';
                    foreach ($selectedIds as $id) {
                        try {
                            $linkRepo->update($id, ['status' => $newStatus]);
                            $successCount++;
                        } catch (\Throwable $e) {}
                    }
                    $statusLabel = $newStatus === 'published' ? 'Opublikowany' : 'Szkic';
                    $_SESSION['toast_message'] = "Zmieniono status na \"$statusLabel\" dla $successCount z " . count($selectedIds) . " linków";
                    $_SESSION['toast_type'] = 'success';
                    break;

                case 'change_delay':
                    $newDelay = max(0, min(300, (int)($_POST['bulk_delay'] ?? 5)));
                    foreach ($selectedIds as $id) {
                        try {
                            $linkRepo->update($id, ['delay_seconds' => $newDelay]);
                            $successCount++;
                        } catch (\Throwable $e) {}
                    }
                    $_SESSION['toast_message'] = "Zmieniono opóźnienie na {$newDelay}s dla $successCount z " . count($selectedIds) . " linków";
                    $_SESSION['toast_type'] = 'success';
                    break;

                default:
                    $_SESSION['toast_message'] = 'Nieznana akcja';
                    $_SESSION['toast_type'] = 'error';
            }
        } catch (\Throwable $e) {
            $_SESSION['toast_message'] = 'Błąd podczas wykonywania akcji: ' . $e->getMessage();
            $_SESSION['toast_type'] = 'error';
        }

        $statusTab = $_POST['status_tab'] ?? 'published';
        header('Location: ' . $this->basePath . '/admin/index.php?action=links&status=' . urlencode($statusTab));
        exit;
    }

    public function linkNewGet(): void
    {
        Utils::requireLogin();

        if ($this->isLicenseBlocked()) {
            $_SESSION['toast_message'] = 'Dodawanie linków jest zablokowane z powodu nieważnej licencji.';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php');
            exit;
        }

        $repo = new SettingsRepository($this->pdo);
        $delay = (int)$repo->get('redirect_delay_seconds', 5);
        $length = (int)$repo->get('random_slug_length', 8);

        $categoryRepo = new CategoryRepository($this->pdo);
        $tagRepo = new TagRepository($this->pdo);
        $affiliateProgramRepo = new AffiliateProgramRepository($this->pdo);
        $customFieldService = new CustomFieldService($this->pdo);

        $this->view('link_form', [
            'csrf' => Utils::csrfToken(),
            'mode' => 'create',
            'link' => [
                'slug' => '',
                'target_url' => '',
                'delay_seconds' => $delay,
                'page_title' => '',
                'page_description' => '',
                'og_image' => null,
                'images' => [],
                'category_id' => null,
                'affiliate_program_id' => null,
                'publish_at' => null,
                'expires_at' => null,
                'fallback_url' => null,
                'password_hash' => null,
                'password_hint' => '',
                'tags' => []
            ],
            'random_length' => $length,
            'categories' => $categoryRepo->list(),
            'affiliatePrograms' => $affiliateProgramRepo->listAll(),
            'all_tags' => $tagRepo->list(),
            'top_tags' => $tagRepo->getTopTags(20),
            'customFieldDefinitions' => $customFieldService->getDefinitions(),
            'customFieldValues' => [],
        ]);
    }

    public function linkNewPost(): void
    {
        Utils::requireLogin();

        if ($this->isLicenseBlocked()) {
            $_SESSION['toast_message'] = 'Dodawanie linków jest zablokowane z powodu nieważnej licencji.';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php');
            exit;
        }

        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Błędny CSRF token.';
            return;
        }
        $links = new LinkRepository($this->pdo);
        $settings = new SettingsRepository($this->pdo);
        $length = (int)$settings->get('random_slug_length', 8);
        $affiliateProgramRepo = new AffiliateProgramRepository($this->pdo);
        $customFieldService = new CustomFieldService($this->pdo);
        $customFieldDefinitions = $customFieldService->getDefinitions();
        $customFieldValues = [];

        $useRandom = isset($_POST['use_random']);
        try {
            $slug = $useRandom ? Utils::generateSlug($length) : Utils::sanitizeSlug((string)($_POST['slug'] ?? ''));
            if ($links->slugExists($slug)) {
                throw new \InvalidArgumentException('Alias już istnieje');
            }
            $target = Utils::sanitizeUrl((string)($_POST['target_url'] ?? ''));
            $delay = max(0, (int)($_POST['delay_seconds'] ?? 5));

            $publishAt = $this->normalizeDateTime($_POST['publish_at'] ?? null);
            $expiresAt = $this->normalizeDateTime($_POST['expires_at'] ?? null);
            if ($publishAt !== null && $expiresAt !== null && $expiresAt <= $publishAt) {
                throw new \InvalidArgumentException('Data wygaśnięcia musi być po dacie publikacji');
            }

            // Obsługa meta tagów
            $pageTitle = trim((string)($_POST['page_title'] ?? ''));
            $pageDescription = trim((string)($_POST['page_description'] ?? ''));
            $pageTitle = $pageTitle !== '' ? $pageTitle : null;
            $pageDescription = $pageDescription !== '' ? $pageDescription : null;

            $customFieldValues = $this->extractCustomFieldValues($customFieldDefinitions, (array)($_POST['custom_fields'] ?? []));
            $uploader = new ImageUploader(__DIR__ . '/../../uploads');
            $galleryFiles = $this->collectGalleryFiles($_FILES['gallery_images'] ?? null);
            $pendingImages = [];
            foreach ($galleryFiles as $file) {
                $pendingImages[] = $uploader->uploadWithThumbnail($file);
            }

            $preuploadedImages = $this->parsePreuploadedImages((array)($_POST['preuploaded_images'] ?? []));
            if (!empty($preuploadedImages)) {
                $pendingImages = array_merge($pendingImages, $preuploadedImages);
            }

            $primaryChoice = (string)($_POST['primary_image'] ?? 'new');

            // Kategoria
            $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
            $affiliateProgramId = !empty($_POST['affiliate_program_id']) ? (int)$_POST['affiliate_program_id'] : null;
            if ($affiliateProgramId !== null && !$affiliateProgramRepo->getById($affiliateProgramId)) {
                throw new \InvalidArgumentException('Wybrany program afiliacyjny nie istnieje');
            }

            // Hasło (jeśli podane)
            $passwordHash = null;
            $passwordHint = null;
            $password = trim((string)($_POST['password'] ?? ''));
            if ($password !== '') {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $passwordHint = trim((string)($_POST['password_hint'] ?? ''));
                $passwordHint = $passwordHint !== '' ? $passwordHint : null;
            }

            // Notatki
            $notes = trim((string)($_POST['notes'] ?? ''));
            $notes = $notes !== '' ? $notes : null;

            // Status (szkic lub opublikowany)
            $status = isset($_POST['save_as_draft']) ? 'draft' : 'published';

            // URL zapasowy po wygaśnięciu
            $fallbackUrl = trim((string)($_POST['fallback_url'] ?? ''));
            $fallbackUrl = $fallbackUrl !== '' ? $fallbackUrl : null;

            $id = $links->create($slug, $target, $delay, $pageTitle, $pageDescription, null, $categoryId, $affiliateProgramId, $publishAt, $expiresAt, $passwordHash, $passwordHint, $notes, $status, $fallbackUrl);

            $imageIds = [];
            $newImageKeyMap = [];
            foreach ($pendingImages as $img) {
                $path = (string)($img['path'] ?? '');
                if ($path === '') {
                    continue;
                }
                $thumbPath = isset($img['thumb_path']) && $img['thumb_path'] !== '' ? (string)$img['thumb_path'] : null;
                $newId = $links->addImage($id, $path, $thumbPath);
                $imageIds[] = $newId;
                if (!empty($img['key'])) {
                    $newImageKeyMap[(string)$img['key']] = $newId;
                }
            }

            if (!empty($imageIds)) {
                $primaryImageId = null;
                if (isset($newImageKeyMap[$primaryChoice])) {
                    $primaryImageId = $newImageKeyMap[$primaryChoice];
                } elseif ($primaryChoice === 'new' || $primaryChoice === '') {
                    $primaryImageId = $imageIds[0];
                } elseif (ctype_digit($primaryChoice)) {
                    $primaryImageId = (int)$primaryChoice;
                }
                $links->setPrimaryImage($id, $primaryImageId);
            } else {
                $links->setPrimaryImage($id, null);
            }

            if (!empty($customFieldDefinitions)) {
                $links->setCustomFields($id, $customFieldValues, $customFieldDefinitions);
            }

            // Tagi
            $tagRepo = new TagRepository($this->pdo);
            $tagIds = [];
            if (!empty($_POST['tags'])) {
                $tagNames = array_map('trim', explode(',', (string)$_POST['tags']));
                foreach ($tagNames as $tagName) {
                    if ($tagName !== '') {
                        $tagIds[] = $tagRepo->findOrCreate($tagName);
                    }
                }
            }
            if (!empty($tagIds)) {
                $tagRepo->setTagsForLink($id, $tagIds);
            }

            // Loguj utworzenie linku
            $audit = new AuditService($this->pdo);
            $createdLink = $links->getById($id);
            $audit->logCreate('link', $id, $slug, $createdLink ? $audit->extractLinkFields($createdLink) : null);

            Utils::startSession();
            $_SESSION['toast_message'] = $status === 'draft' ? 'Szkic został zapisany' : 'Link został utworzony pomyślnie';
            $_SESSION['toast_type'] = 'success';
            header('Location: ' . $this->basePath . '/admin/index.php?action=edit&id=' . $id);
            exit;
        } catch (\Throwable $e) {
            $this->cleanupUploadedPaths($pendingImages ?? []);
            $categoryRepo = new CategoryRepository($this->pdo);
            $tagRepo = new TagRepository($this->pdo);
            $this->view('link_form', [
                'csrf' => Utils::csrfToken(),
                'mode' => 'create',
                'error' => $e->getMessage(),
                'link' => [
                    'slug' => (string)($_POST['slug'] ?? ''),
                    'target_url' => (string)($_POST['target_url'] ?? ''),
                    'delay_seconds' => (int)($_POST['delay_seconds'] ?? 5),
                    'page_title' => (string)($_POST['page_title'] ?? ''),
                    'page_description' => (string)($_POST['page_description'] ?? ''),
                    'og_image' => null,
                    'publish_at' => (string)($_POST['publish_at'] ?? ''),
                    'expires_at' => (string)($_POST['expires_at'] ?? ''),
                    'fallback_url' => (string)($_POST['fallback_url'] ?? ''),
                    'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
                    'affiliate_program_id' => !empty($_POST['affiliate_program_id']) ? (int)$_POST['affiliate_program_id'] : null,
                ],
                'random_length' => $length,
                'categories' => $categoryRepo->list(),
                'affiliatePrograms' => $affiliateProgramRepo->listAll(),
                'all_tags' => $tagRepo->list(),
                'top_tags' => $tagRepo->getTopTags(20),
                'customFieldDefinitions' => $customFieldDefinitions,
                'customFieldValues' => (array)($_POST['custom_fields'] ?? []),
            ]);
        }
    }

    public function linkEditGet(int $id): void
    {
        Utils::requireLogin();

        if ($this->isLicenseBlocked()) {
            $_SESSION['toast_message'] = 'Edycja linków jest zablokowana z powodu nieważnej licencji.';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=links');
            exit;
        }

        $repo = new LinkRepository($this->pdo);
        $link = $repo->getByIdWithRelations($id);
        if (!$link) {
            http_response_code(404);
            echo 'Nie znaleziono linku';
            return;
        }

        $categoryRepo = new CategoryRepository($this->pdo);
        $tagRepo = new TagRepository($this->pdo);
        $affiliateProgramRepo = new AffiliateProgramRepository($this->pdo);
        $customFieldService = new CustomFieldService($this->pdo);
        $customFieldDefinitions = $customFieldService->getDefinitions();
        $customFieldValues = [];

        if (!empty($link['custom_fields']) && is_array($link['custom_fields'])) {
            foreach ($link['custom_fields'] as $field) {
                if (!empty($field['key'])) {
                    $customFieldValues[$field['key']] = $field['value'] ?? '';
                }
            }
        }

        $this->view('link_form', [
            'csrf' => Utils::csrfToken(),
            'mode' => 'edit',
            'link' => $link,
            'random_length' => null,
            'categories' => $categoryRepo->list(),
            'affiliatePrograms' => $affiliateProgramRepo->listAll(),
            'all_tags' => $tagRepo->list(),
            'top_tags' => $tagRepo->getTopTags(20),
            'customFieldDefinitions' => $customFieldDefinitions,
            'customFieldValues' => $customFieldValues,
        ]);
    }

    public function linkEditPost(int $id): void
    {
        Utils::requireLogin();

        if ($this->isLicenseBlocked()) {
            $_SESSION['toast_message'] = 'Edycja linków jest zablokowana z powodu nieważnej licencji.';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=links');
            exit;
        }

        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Błędny CSRF token.';
            return;
        }
        $repo = new LinkRepository($this->pdo);
        $existing = $repo->getById($id);
        if (!$existing) {
            http_response_code(404);
            echo 'Nie znaleziono linku';
            return;
        }
        $affiliateProgramRepo = new AffiliateProgramRepository($this->pdo);
        $customFieldService = new CustomFieldService($this->pdo);
        $customFieldDefinitions = $customFieldService->getDefinitions();
        $customFieldValues = [];
        $existingImages = $repo->getImages($id);
        $currentPrimaryId = null;
        foreach ($existingImages as $img) {
            if (!empty($img['is_primary'])) {
                $currentPrimaryId = (int)$img['id'];
                break;
            }
        }
        try {
            $slug = Utils::sanitizeSlug((string)($_POST['slug'] ?? $existing['slug']));
            if ($slug !== $existing['slug'] && $repo->slugExists($slug)) {
                throw new \InvalidArgumentException('Alias już istnieje');
            }
            $target = Utils::sanitizeUrl((string)($_POST['target_url'] ?? $existing['target_url']));
            $delay = max(0, (int)($_POST['delay_seconds'] ?? $existing['delay_seconds']));

            $publishAt = $this->normalizeDateTime($_POST['publish_at'] ?? $existing['publish_at'] ?? null);
            $expiresAt = $this->normalizeDateTime($_POST['expires_at'] ?? $existing['expires_at'] ?? null);
            if ($publishAt !== null && $expiresAt !== null && $expiresAt <= $publishAt) {
                throw new \InvalidArgumentException('Data wygaśnięcia musi być po dacie publikacji');
            }

            // Obsługa meta tagów
            $pageTitle = trim((string)($_POST['page_title'] ?? ''));
            $pageDescription = trim((string)($_POST['page_description'] ?? ''));
            $pageTitle = $pageTitle !== '' ? $pageTitle : null;
            $pageDescription = $pageDescription !== '' ? $pageDescription : null;

            $customFieldValues = $this->extractCustomFieldValues($customFieldDefinitions, (array)($_POST['custom_fields'] ?? []));
            $uploader = new ImageUploader(__DIR__ . '/../../uploads');
            $galleryFiles = $this->collectGalleryFiles($_FILES['gallery_images'] ?? null);
            $pendingImages = [];
            foreach ($galleryFiles as $file) {
                $pendingImages[] = $uploader->uploadWithThumbnail($file);
            }

            $preuploadedImages = $this->parsePreuploadedImages((array)($_POST['preuploaded_images'] ?? []));
            if (!empty($preuploadedImages)) {
                $pendingImages = array_merge($pendingImages, $preuploadedImages);
            }

            $removeImages = array_map('intval', $_POST['remove_images'] ?? []);
            $removedRecords = $repo->deleteImages($id, $removeImages);
            foreach ($removedRecords as $removed) {
                $uploader->delete($removed['path']);
                if (!empty($removed['thumb_path'])) {
                    $uploader->delete((string)$removed['thumb_path']);
                }
            }
            $primaryWasRemoved = !empty(array_filter($removedRecords, fn($row) => !empty($row['is_primary'])));

            $newImageIds = [];
            $newImageKeyMap = [];
            foreach ($pendingImages as $img) {
                $path = (string)($img['path'] ?? '');
                if ($path === '') {
                    continue;
                }
                $thumbPath = isset($img['thumb_path']) && $img['thumb_path'] !== '' ? (string)$img['thumb_path'] : null;
                $newId = $repo->addImage($id, $path, $thumbPath);
                $newImageIds[] = $newId;
                if (!empty($img['key'])) {
                    $newImageKeyMap[(string)$img['key']] = $newId;
                }
            }

            $primaryChoice = (string)($_POST['primary_image'] ?? '');
            $primaryImageId = null;
            if (isset($newImageKeyMap[$primaryChoice])) {
                $primaryImageId = $newImageKeyMap[$primaryChoice];
            } elseif ($primaryChoice === 'new' && !empty($newImageIds)) {
                $primaryImageId = $newImageIds[0];
            } elseif (ctype_digit($primaryChoice)) {
                $primaryImageId = (int)$primaryChoice;
            } elseif ($primaryChoice === '' && $currentPrimaryId !== null && !$primaryWasRemoved) {
                $primaryImageId = $currentPrimaryId;
            }

            // Kategoria
            $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
            $affiliateProgramId = !empty($_POST['affiliate_program_id']) ? (int)$_POST['affiliate_program_id'] : null;
            if ($affiliateProgramId !== null && !$affiliateProgramRepo->getById($affiliateProgramId)) {
                throw new \InvalidArgumentException('Wybrany program afiliacyjny nie istnieje');
            }

            // Hasło (aktualizuj tylko jeśli podano nowe)
            $passwordHash = $existing['password_hash'] ?? null;
            $passwordHint = $existing['password_hint'] ?? null;
            $password = trim((string)($_POST['password'] ?? ''));
            if ($password !== '') {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $passwordHint = trim((string)($_POST['password_hint'] ?? ''));
                $passwordHint = $passwordHint !== '' ? $passwordHint : null;
            } elseif (isset($_POST['remove_password']) && $_POST['remove_password'] === '1') {
                // Usuń hasło jeśli zaznaczono checkbox
                $passwordHash = null;
                $passwordHint = null;
            }

            // Notatki
            $notes = trim((string)($_POST['notes'] ?? ''));
            $notes = $notes !== '' ? $notes : null;

            // Status - zmiana tylko jesli kliknieto odpowiedni przycisk
            $currentStatus = $existing['status'] ?? 'published';
            if (isset($_POST['publish'])) {
                $status = 'published';
            } elseif (isset($_POST['save_as_draft'])) {
                $status = 'draft';
            } else {
                $status = $currentStatus;
            }

            // URL zapasowy po wygaśnięciu
            $fallbackUrl = trim((string)($_POST['fallback_url'] ?? ''));
            $fallbackUrl = $fallbackUrl !== '' ? $fallbackUrl : null;

            $repo->update($id, [
                'slug' => $slug,
                'target_url' => $target,
                'delay_seconds' => $delay,
                'page_title' => $pageTitle,
                'page_description' => $pageDescription,
                'og_image' => $existing['og_image'] ?? null,
                'category_id' => $categoryId,
                'affiliate_program_id' => $affiliateProgramId,
                'publish_at' => $publishAt,
                'expires_at' => $expiresAt,
                'fallback_url' => $fallbackUrl,
                'password_hash' => $passwordHash,
                'password_hint' => $passwordHint,
                'notes' => $notes,
                'status' => $status,
            ]);

            $shouldUpdatePrimary = !empty($existingImages) || !empty($pendingImages) || !empty($removeImages);
            if ($shouldUpdatePrimary) {
                $repo->setPrimaryImage($id, $primaryImageId);
            }

            if (!empty($customFieldDefinitions)) {
                $repo->setCustomFields($id, $customFieldValues, $customFieldDefinitions);
            }

            // Tagi
            $tagRepo = new TagRepository($this->pdo);
            $tagIds = [];
            if (!empty($_POST['tags'])) {
                $tagNames = array_map('trim', explode(',', (string)$_POST['tags']));
                foreach ($tagNames as $tagName) {
                    if ($tagName !== '') {
                        $tagIds[] = $tagRepo->findOrCreate($tagName);
                    }
                }
            }
            $tagRepo->setTagsForLink($id, $tagIds);

            // Loguj aktualizację linku
            $audit = new AuditService($this->pdo);
            $updatedLink = $repo->getById($id);
            $audit->logUpdate('link', $id, $slug,
                $audit->extractLinkFields($existing),
                $updatedLink ? $audit->extractLinkFields($updatedLink) : null
            );

            Utils::startSession();
            if (isset($_POST['publish']) && $currentStatus === 'draft') {
                $_SESSION['toast_message'] = 'Link został opublikowany';
            } elseif (isset($_POST['save_as_draft'])) {
                $_SESSION['toast_message'] = 'Szkic został zapisany';
            } else {
                $_SESSION['toast_message'] = 'Link został zaktualizowany pomyślnie';
            }
            $_SESSION['toast_type'] = 'success';
            header('Location: ' . $this->basePath . '/admin/index.php?action=edit&id=' . $id);
            exit;
        } catch (\Throwable $e) {
            $this->cleanupUploadedPaths($pendingImages ?? []);
            $categoryRepo = new CategoryRepository($this->pdo);
            $tagRepo = new TagRepository($this->pdo);
            $affiliateProgramRepo = new AffiliateProgramRepository($this->pdo);
            $this->view('link_form', [
                'csrf' => Utils::csrfToken(),
                'mode' => 'edit',
                'error' => $e->getMessage(),
                'link' => [
                    'id' => $id,
                    'slug' => (string)($_POST['slug'] ?? $existing['slug']),
                    'target_url' => (string)($_POST['target_url'] ?? $existing['target_url']),
                    'delay_seconds' => (int)($_POST['delay_seconds'] ?? $existing['delay_seconds']),
                    'publish_at' => (string)($_POST['publish_at'] ?? $existing['publish_at'] ?? ''),
                    'expires_at' => (string)($_POST['expires_at'] ?? $existing['expires_at'] ?? ''),
                    'fallback_url' => (string)($_POST['fallback_url'] ?? $existing['fallback_url'] ?? ''),
                    'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
                    'affiliate_program_id' => !empty($_POST['affiliate_program_id']) ? (int)$_POST['affiliate_program_id'] : null,
                    'images' => $repo->getImages($id),
                    'page_title' => (string)($_POST['page_title'] ?? $existing['page_title'] ?? ''),
                    'page_description' => (string)($_POST['page_description'] ?? $existing['page_description'] ?? ''),
                ],
                'random_length' => null,
                'categories' => $categoryRepo->list(),
                'affiliatePrograms' => $affiliateProgramRepo->listAll(),
                'all_tags' => $tagRepo->list(),
                'top_tags' => $tagRepo->getTopTags(20),
                'customFieldDefinitions' => $customFieldDefinitions,
                'customFieldValues' => (array)($_POST['custom_fields'] ?? []),
            ]);
        }
    }

    public function linkDeletePost(int $id): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Bledny CSRF token.';
            return;
        }

        $repo = new LinkRepository($this->pdo);
        $settingsRepo = new SettingsRepository($this->pdo);
        $trashMode = (string)$settingsRepo->get('trash_mode', 'auto_delete');

        $link = $repo->getById($id);
        if (!$link) {
            header('Location: ' . $this->basePath . '/admin/index.php');
            exit;
        }

        Utils::startSession();

        // Loguj usunięcie linku
        $audit = new AuditService($this->pdo);

        if ($trashMode === 'hard_delete') {
            // Trwale usun - usun obrazki i link
            $audit->logPermanentDelete('link', $id, $link['slug']);
            $this->deleteLinksWithImages([$id], $repo);
            $_SESSION['toast_message'] = 'Link został trwale usunięty';
        } else {
            // Soft delete - przenies do kosza
            $audit->logDelete('link', $id, $link['slug'], $audit->extractLinkFields($link));
            $repo->delete($id, false);
            $_SESSION['toast_message'] = 'Link zostal przeniesiony do kosza';
        }

        $_SESSION['toast_type'] = 'success';
        header('Location: ' . $this->basePath . '/admin/index.php?action=links');
        exit;
    }

    /**
     * Przywraca link z kosza
     */
    public function linkRestorePost(int $id): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Bledny CSRF token.';
            return;
        }

        $repo = new LinkRepository($this->pdo);
        $link = $repo->getById($id);
        $repo->restore($id);

        // Loguj przywrócenie linku
        $audit = new AuditService($this->pdo);
        $audit->logRestore('link', $id, $link ? $link['slug'] : null);

        Utils::startSession();
        $_SESSION['toast_message'] = 'Link został przywrócony';
        $_SESSION['toast_type'] = 'success';
        header('Location: ' . $this->basePath . '/admin/index.php?action=links&status=trashed');
        exit;
    }

    /**
     * Trwale usuwa link z kosza
     */
    public function linkPermanentDeletePost(int $id): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Bledny CSRF token.';
            return;
        }

        $repo = new LinkRepository($this->pdo);
        $link = $repo->getById($id);

        // Loguj trwałe usunięcie linku
        $audit = new AuditService($this->pdo);
        $audit->logPermanentDelete('link', $id, $link ? $link['slug'] : null);

        $this->deleteLinksWithImages([$id], $repo);

        Utils::startSession();
        $_SESSION['toast_message'] = 'Link został trwale usunięty';
        $_SESSION['toast_type'] = 'success';
        header('Location: ' . $this->basePath . '/admin/index.php?action=links&status=trashed');
        exit;
    }

    /**
     * Oproznia caly kosz
     */
    public function emptyTrashPost(): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Bledny CSRF token.';
            return;
        }

        $repo = new LinkRepository($this->pdo);

        // Pobierz wszystkie linki z kosza
        $trashedLinks = $repo->list(10000, 0, null, null, 'id', 'ASC', '', false, false, null, 'trashed');
        $ids = array_column($trashedLinks, 'id');

        $this->deleteLinksWithImages($ids, $repo);

        Utils::startSession();
        $_SESSION['toast_message'] = 'Kosz zostal oprozniony';
        $_SESSION['toast_type'] = 'success';
        header('Location: ' . $this->basePath . '/admin/index.php?action=links&status=trashed');
        exit;
    }

    public function linkDuplicatePost(int $id): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Błędny CSRF token.';
            return;
        }

        $repo = new LinkRepository($this->pdo);

        // Pobierz oryginalny link
        $originalLink = $repo->getById($id);
        if (!$originalLink) {
            Utils::startSession();
            $_SESSION['toast_message'] = 'Link nie został znaleziony';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=links');
            exit;
        }

        // Wygeneruj nowy unikalny slug
        $originalSlug = $originalLink['slug'];
        $newSlug = $originalSlug . '-kopia';
        $counter = 1;

        // Sprawdź czy slug jest unikalny, jeśli nie - dodaj licznik
        while ($repo->findBySlug($newSlug)) {
            $counter++;
            $newSlug = $originalSlug . '-kopia-' . $counter;
        }

        // Przygotuj dane nowego linku (wszystkie pola oprócz ID i slug)
        $newLinkData = [
            'slug' => $newSlug,
            'target_url' => $originalLink['target_url'],
            'delay_seconds' => $originalLink['delay_seconds'],
            'category_id' => $originalLink['category_id'],
            'affiliate_program_id' => $originalLink['affiliate_program_id'],
            'page_title' => $originalLink['page_title'] ? $originalLink['page_title'] . ' (kopia)' : null,
            'page_description' => $originalLink['page_description'],
            'og_image' => null, // Obrazki nie są kopiowane
            'og_image_thumb' => null,
            'publish_at' => $originalLink['publish_at'],
            'expires_at' => $originalLink['expires_at'],
            'fallback_url' => $originalLink['fallback_url'] ?? null,
            'password_hash' => $originalLink['password_hash'],
            'password_hint' => $originalLink['password_hint'],
            'notes' => $originalLink['notes'],
            'custom_html_head' => $originalLink['custom_html_head'] ?? null,
            'custom_html_body' => $originalLink['custom_html_body'] ?? null,
            'custom_css' => $originalLink['custom_css'] ?? null,
            'custom_js' => $originalLink['custom_js'] ?? null,
            'redirect_type' => $originalLink['redirect_type'] ?? null,
            'noindex' => $originalLink['noindex'] ?? null,
            'nofollow' => $originalLink['nofollow'] ?? null,
        ];

        try {
            // Utwórz nowy link
            $newLinkId = $repo->create($newLinkData);

            // Skopiuj tagi
            $originalTags = $repo->getTags($id);
            if (!empty($originalTags)) {
                $tagIds = array_column($originalTags, 'id');
                $repo->setTags($newLinkId, $tagIds);
            }

            // Skopiuj pola niestandardowe
            $originalCustomFields = $repo->getCustomFields($id);
            if (!empty($originalCustomFields)) {
                foreach ($originalCustomFields as $field) {
                    $repo->saveCustomField($newLinkId, $field['field_key'], $field['field_value']);
                }
            }

            Utils::startSession();
            $_SESSION['toast_message'] = "Link został zduplikowany pomyślnie jako '$newSlug'";
            $_SESSION['toast_type'] = 'success';

            // Przekieruj do edycji nowego linku
            header('Location: ' . $this->basePath . '/admin/index.php?action=edit&id=' . $newLinkId);
            exit;
        } catch (\Throwable $e) {
            Utils::startSession();
            $_SESSION['toast_message'] = 'Błąd podczas duplikowania linku: ' . $e->getMessage();
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=links');
            exit;
        }
    }
}
