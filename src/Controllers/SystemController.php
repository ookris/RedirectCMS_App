<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../StorageService.php';
require_once __DIR__ . '/../SettingsRepository.php';
require_once __DIR__ . '/../StatsRepository.php';
require_once __DIR__ . '/../AuditLogRepository.php';
require_once __DIR__ . '/../AuditService.php';
require_once __DIR__ . '/../NotificationRepository.php';
require_once __DIR__ . '/../LinkHealthCheckRepository.php';
require_once __DIR__ . '/../LinkHealthChecker.php';
require_once __DIR__ . '/../FileManager.php';

class SystemController extends BaseController
{
    public function logsGet(): void
    {
        Utils::requireLogin();

        // Parametry
        $lines = max(10, min(1000, (int)($_GET['lines'] ?? 100)));

        // Automatyczne wykrywanie wszystkich plików .log w katalogu
        $logsDir = __DIR__ . '/../../storage/logs';
        $availableLogs = [];

        if (is_dir($logsDir)) {
            $files = glob($logsDir . '/*.log');

            foreach ($files as $filePath) {
                $filename = basename($filePath);
                $key = pathinfo($filename, PATHINFO_FILENAME); // bez rozszerzenia

                // Ładna nazwa: zamień podkreślenia na spacje i capitalize
                $niceName = str_replace('_', ' ', $key);
                $niceName = ucwords($niceName);

                $availableLogs[$key] = [
                    'name' => $niceName . ' (' . $filename . ')',
                    'path' => $filePath,
                    'exists' => true,
                    'size' => $this->formatBytes(filesize($filePath)),
                ];
            }
        }

        // Sortuj alfabetycznie po kluczu
        ksort($availableLogs);

        // Wybierz pierwszy log jeśli nie podano parametru
        $selectedLog = (string)($_GET['log'] ?? '');
        if ($selectedLog === '' || !isset($availableLogs[$selectedLog])) {
            $selectedLog = !empty($availableLogs) ? array_key_first($availableLogs) : 'email';
        }

        // Sprawdź czy wybrany log istnieje
        if (!isset($availableLogs[$selectedLog])) {
            // Fallback - utwórz pustą definicję
            $availableLogs[$selectedLog] = [
                'name' => ucwords(str_replace('_', ' ', $selectedLog)) . ' (' . $selectedLog . '.log)',
                'path' => $logsDir . '/' . $selectedLog . '.log',
                'exists' => false,
                'size' => '0 B',
            ];
        }

        $logInfo = $availableLogs[$selectedLog];
        $logFilePath = $logInfo['path'];
        $logFileExists = $logInfo['exists'];
        $logFileSize = $logInfo['size'];
        $logFileModified = $logFileExists ? date('Y-m-d H:i:s', filemtime($logFilePath)) : 'N/A';

        // Odczytaj logi
        $logContent = [];
        if ($logFileExists) {
            $rawLines = Utils::tailFile($logFilePath, $lines);

            // Koloruj każdą linię
            foreach ($rawLines as $line) {
                $logContent[] = $this->colorizeLogLine(htmlspecialchars($line));
            }
        }

        $this->view('logs', [
            'availableLogs' => $availableLogs,
            'selectedLog' => $selectedLog,
            'lines' => $lines,
            'logContent' => $logContent,
            'logFilePath' => $logFilePath,
            'logFileExists' => $logFileExists,
            'logFileSize' => $logFileSize,
            'logFileModified' => $logFileModified,
        ]);
    }

    public function resourcesGet(): void
    {
        Utils::requireLogin();

        // Użyj StorageService do obliczenia statystyk (z cache w bazie)
        $storageService = new StorageService($this->pdo);
        $allStats = $storageService->getAllStorageStats();

        // Pobierz statystyki podkatalogów dla uploads
        $uploadsSubdirs = $storageService->getSubdirectoryStats($storageService->getUploadsPath());

        // Pobierz limity miejsca na dysku i aktualne wykorzystanie
        $diskQuotaData = null;
        try {
            $settingsRepo = new SettingsRepository($this->pdo);
            $diskQuotaFilesMb = (int)$settingsRepo->get('disk_quota_files_mb', 0);
            $diskQuotaDatabaseMb = (int)$settingsRepo->get('disk_quota_database_mb', 0);

            if ($diskQuotaFilesMb > 0 || $diskQuotaDatabaseMb > 0) {
                $diskQuotaData = [];

                // Pliki - jeśli limit jest ustawiony
                if ($diskQuotaFilesMb > 0) {
                    $currentFilesMb = round($allStats['total_size'] / 1024 / 1024, 2);
                    $filesPercentage = min(100, round(($currentFilesMb / $diskQuotaFilesMb) * 100, 1));

                    $diskQuotaData['files'] = [
                        'current_mb' => $currentFilesMb,
                        'limit_mb' => $diskQuotaFilesMb,
                        'percentage' => $filesPercentage,
                        'color_class' => $filesPercentage >= 90 ? 'danger' : ($filesPercentage >= 70 ? 'warning' : 'success'),
                    ];
                }

                // Baza danych - jeśli limit jest ustawiony
                if ($diskQuotaDatabaseMb > 0) {
                    $statsRepo = new StatsRepository($this->pdo);
                    $currentDatabaseBytes = $statsRepo->getDatabaseSize();
                    $currentDatabaseMb = round($currentDatabaseBytes / 1024 / 1024, 2);
                    $databasePercentage = min(100, round(($currentDatabaseMb / $diskQuotaDatabaseMb) * 100, 1));

                    $diskQuotaData['database'] = [
                        'current_mb' => $currentDatabaseMb,
                        'limit_mb' => $diskQuotaDatabaseMb,
                        'percentage' => $databasePercentage,
                        'color_class' => $databasePercentage >= 90 ? 'danger' : ($databasePercentage >= 70 ? 'warning' : 'success'),
                    ];
                }
            }
        } catch (\Throwable $e) {
            error_log('Error getting disk quota data: ' . $e->getMessage());
        }

        $this->view('resources', [
            'uploadsSize' => $allStats['uploads']['total_size'],
            'uploadsSizeFormatted' => $allStats['uploads']['size_formatted'],
            'uploadsCount' => $allStats['uploads']['file_count'],
            'logsSize' => $allStats['logs']['total_size'],
            'logsSizeFormatted' => $allStats['logs']['size_formatted'],
            'logsCount' => $allStats['logs']['file_count'],
            'cacheSize' => $allStats['cache']['total_size'],
            'cacheSizeFormatted' => $allStats['cache']['size_formatted'],
            'cacheCount' => $allStats['cache']['file_count'],
            'backupsSize' => $allStats['backups']['total_size'] ?? 0,
            'backupsSizeFormatted' => $allStats['backups']['size_formatted'] ?? '0 B',
            'backupsCount' => $allStats['backups']['file_count'] ?? 0,
            'totalSize' => $allStats['total_size'],
            'totalSizeFormatted' => $allStats['total_size_formatted'],
            'uploadsSubdirs' => $uploadsSubdirs,
            'lastCalculated' => $allStats['last_calculated'] ?? null,
            'diskQuotaData' => $diskQuotaData,
            'csrf' => Utils::csrfToken(),
        ]);
    }

    /**
     * Ręczne przeliczenie rozmiarów katalogów storage
     */
    public function recalculateStoragePost(): void
    {
        Utils::requireLogin();

        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            $_SESSION['toast_message'] = 'Błąd weryfikacji CSRF';
            $_SESSION['toast_type'] = 'error';
            header('Location: ' . $this->basePath . '/admin/index.php?action=resources');
            exit;
        }

        try {
            $storageService = new StorageService($this->pdo);
            $storageService->recalculateAndStore();

            $_SESSION['toast_message'] = 'Rozmiary katalogów zostały przeliczone i zaktualizowane.';
            $_SESSION['toast_type'] = 'success';
        } catch (\Exception $e) {
            $_SESSION['toast_message'] = 'Błąd podczas przeliczania rozmiarów: ' . $e->getMessage();
            $_SESSION['toast_type'] = 'error';
        }

        header('Location: ' . $this->basePath . '/admin/index.php?action=resources');
        exit;
    }

    /**
     * Wyświetla logi audytu
     */
    public function auditLogsGet(): void
    {
        Utils::requireLogin();

        $perPage = max(10, min(100, (int)($_GET['per_page'] ?? 50)));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $actionType = !empty($_GET['action_type']) ? trim($_GET['action_type']) : null;
        $entityType = !empty($_GET['entity_type']) ? trim($_GET['entity_type']) : null;
        $dateFrom = !empty($_GET['date_from']) ? trim($_GET['date_from']) : null;
        $dateTo = !empty($_GET['date_to']) ? trim($_GET['date_to']) : null;
        $search = !empty($_GET['search']) ? trim($_GET['search']) : null;

        $repo = new AuditLogRepository($this->pdo);
        $total = $repo->count($actionType, $entityType, $dateFrom, $dateTo, $search);
        $lastPage = max(1, (int)ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $logs = $repo->list($perPage, $offset, $actionType, $entityType, $dateFrom, $dateTo, $search);
        $actionTypes = $repo->getActionTypes();
        $entityTypes = $repo->getEntityTypes();

        $this->view('audit_logs', [
            'csrf' => Utils::csrfToken(),
            'logs' => $logs,
            'actionTypes' => $actionTypes,
            'entityTypes' => $entityTypes,
            'filters' => compact('actionType', 'entityType', 'dateFrom', 'dateTo', 'search'),
            'page' => $page,
            'lastPage' => $lastPage,
            'perPage' => $perPage,
            'total' => $total,
        ]);
    }

    /**
     * Wyświetla listę powiadomień systemowych
     */
    public function notificationsGet(): void
    {
        Utils::requireLogin();

        $perPage = max(10, min(100, (int)($_GET['per_page'] ?? 50)));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $component = !empty($_GET['component']) ? trim($_GET['component']) : null;
        $severity = !empty($_GET['severity']) ? trim($_GET['severity']) : null;
        $readStatus = !empty($_GET['status']) ? trim($_GET['status']) : null;

        $repo = new NotificationRepository($this->pdo);
        $total = $repo->count($component, $severity, $readStatus);
        $lastPage = max(1, (int)ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $notifications = $repo->list($perPage, $offset, $component, $severity, $readStatus);
        $components = $repo->getComponents();
        $unreadCount = $repo->countUnread();

        // Obsługa komunikatów sesyjnych
        Utils::startSession();
        $successMsg = $_SESSION['success_notifications'] ?? null;
        unset($_SESSION['success_notifications']);

        $this->view('notifications', [
            'csrf' => Utils::csrfToken(),
            'notifications' => $notifications,
            'components' => $components,
            'filters' => compact('component', 'severity', 'readStatus'),
            'page' => $page,
            'lastPage' => $lastPage,
            'perPage' => $perPage,
            'total' => $total,
            'unreadCount' => $unreadCount,
            'successMsg' => $successMsg,
        ]);
    }

    public function notificationMarkRead(): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Nieprawidłowy token CSRF.';
            return;
        }

        Utils::startSession();
        $repo = new NotificationRepository($this->pdo);
        $markAll = (bool)($_POST['mark_all'] ?? false);

        if ($markAll) {
            $repo->markAllAsRead();
            $_SESSION['success_notifications'] = 'Wszystkie powiadomienia oznaczono jako przeczytane.';
        } else {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $repo->markAsRead($id);
            }
        }

        header('Location: ' . $this->basePath . '/admin/index.php?action=notifications');
        exit;
    }

    /**
     * Wyświetla listę uszkodzonych linków
     */
    public function brokenLinksGet(): void
    {
        Utils::requireLogin();

        $healthRepo = new LinkHealthCheckRepository($this->pdo);
        $summary = $healthRepo->getSummary();
        $brokenLinks = $healthRepo->getBrokenLinks(1000, 0);
        $allLinks = $healthRepo->getAllLinksHealth(1000, 0);

        $this->view('broken_links', [
            'csrf' => Utils::csrfToken(),
            'summary' => $summary,
            'brokenLinks' => $brokenLinks,
            'allLinks' => $allLinks,
        ]);
    }

    /**
     * Akcje na uszkodzonych linkach (ignoruj, napraw, sprawdź ponownie)
     */
    public function brokenLinkAction(): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Błędny CSRF token.';
            return;
        }

        $linkId = (int)($_POST['link_id'] ?? 0);
        $action = trim($_POST['action'] ?? '');

        if ($linkId <= 0) {
            header('Location: ' . $this->basePath . '/admin/index.php?action=broken_links');
            exit;
        }

        $healthRepo = new LinkHealthCheckRepository($this->pdo);
        $linkRepo = new LinkRepository($this->pdo);

        Utils::startSession();

        if ($action === 'ignore') {
            $healthRepo->updateLinkStatus($linkId, 'ignored');
            $_SESSION['toast_message'] = 'Link oznaczony jako ignorowany';
            $_SESSION['toast_type'] = 'success';
        } elseif ($action === 'resolve') {
            $healthRepo->updateLinkStatus($linkId, 'resolved');
            $_SESSION['toast_message'] = 'Link oznaczony jako naprawiony';
            $_SESSION['toast_type'] = 'success';
        } elseif ($action === 'recheck') {
            $link = $linkRepo->getById($linkId);
            if ($link) {
                $checker = new LinkHealthChecker($this->pdo);
                $result = $checker->checkSingleLink($link);
                if ($result['status'] === 'healthy') {
                    $_SESSION['toast_message'] = 'Link działa prawidłowo (HTTP ' . $result['http_status'] . ')';
                    $_SESSION['toast_type'] = 'success';
                } else {
                    $_SESSION['toast_message'] = 'Link nadal jest uszkodzony: ' . ($result['error'] ?? 'HTTP ' . $result['http_status']);
                    $_SESSION['toast_type'] = 'error';
                }
            }
        }

        header('Location: ' . $this->basePath . '/admin/index.php?action=broken_links');
        exit;
    }

    /**
     * Uruchom sprawdzanie linków w batchu z poziomu strony broken_links
     */
    public function brokenLinksRunCheck(): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Błędny CSRF token.';
            return;
        }

        $checker = new LinkHealthChecker($this->pdo);
        $results = $checker->checkAllLinks(10);

        Utils::startSession();
        $msg = sprintf(
            'Sprawdzono %d linków: %d zdrowych, %d uszkodzonych',
            $results['checked'], $results['healthy'], $results['broken']
        );
        if ($results['remaining'] > 0) {
            $msg .= sprintf('. Pozostało %d linków do sprawdzenia — kliknij ponownie', $results['remaining']);
        }
        $_SESSION['toast_message'] = $msg;
        $_SESSION['toast_type'] = $results['broken'] > 0 ? 'warning' : 'success';

        header('Location: ' . $this->basePath . '/admin/index.php?action=broken_links');
        exit;
    }

    // ========================================
    // File Manager
    // ========================================

    public function fileManagerGet(): void
    {
        Utils::requireLogin();

        $section = $_GET['section'] ?? 'uploads';
        if (!in_array($section, ['uploads', 'storage'])) $section = 'uploads';
        $readonly = ($section === 'storage');

        $fileManager = new FileManager($this->pdo, $section);
        $currentPath = $_GET['path'] ?? '';

        // Pobierz listę plików
        $files = $fileManager->listFiles($currentPath);

        // Dodaj informację o referencjach dla każdego pliku
        foreach ($files as &$file) {
            if ($file['type'] === 'file') {
                $file['is_referenced'] = $fileManager->isFileReferenced($file['path']);
            } else {
                $file['is_referenced'] = false;
            }
        }
        unset($file);

        // Pobierz statystyki
        $stats = $fileManager->getDiskStats();

        // Breadcrumbs
        $breadcrumbs = [];
        if ($currentPath) {
            $parts = explode('/', $currentPath);
            $path = '';
            foreach ($parts as $part) {
                $path .= ($path ? '/' : '') . $part;
                $breadcrumbs[] = ['name' => $part, 'path' => $path];
            }
        }

        $basePath = $this->basePath;
        $csrf = Utils::csrfToken();
        require __DIR__ . '/../../admin/file_manager.php';
    }

    public function fileManagerDeletePost(): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            $_SESSION['toast'] = ['message' => 'Błąd CSRF', 'type' => 'danger'];
            header('Location: ' . $this->basePath . '/admin/index.php?action=file_manager');
            exit;
        }

        $section = $_POST['section'] ?? 'uploads';
        if ($section !== 'uploads') {
            $_SESSION['toast'] = ['message' => 'Usuwanie plików z tej sekcji jest zablokowane', 'type' => 'danger'];
            header('Location: ' . $this->basePath . '/admin/index.php?action=file_manager&section=' . urlencode($section));
            exit;
        }

        $filePath = $_POST['file_path'] ?? '';
        $returnPath = $_POST['return_path'] ?? '';

        if ($filePath) {
            $fileManager = new FileManager($this->pdo);

            // Sprawdź czy plik jest używany
            if ($fileManager->isFileReferenced($filePath)) {
                $_SESSION['toast'] = ['message' => 'Nie można usunąć pliku - jest używany w systemie', 'type' => 'danger'];
            } elseif ($fileManager->deleteFile($filePath)) {
                $_SESSION['toast'] = ['message' => 'Plik został usunięty', 'type' => 'success'];

                // Audit log
                $audit = new AuditService($this->pdo);
                $audit->log('delete', 'file', null, $filePath);
            } else {
                $_SESSION['toast'] = ['message' => 'Nie udało się usunąć pliku', 'type' => 'danger'];
            }
        }

        header('Location: ' . $this->basePath . '/admin/index.php?action=file_manager' . ($returnPath ? '&path=' . urlencode($returnPath) : ''));
        exit;
    }

    public function fileDetailsGet(): void
    {
        Utils::requireLogin();
        header('Content-Type: application/json');

        $filePath = $_GET['path'] ?? '';
        if (!$filePath) {
            echo json_encode(['error' => 'Brak ścieżki pliku']);
            return;
        }

        $section = $_GET['section'] ?? 'uploads';
        if (!in_array($section, ['uploads', 'storage'])) $section = 'uploads';

        $fileManager = new FileManager($this->pdo, $section);

        // Pobierz informacje o użyciu pliku
        $usage = $fileManager->getFileUsage($filePath);

        // Pobierz szczegóły pliku
        $files = $fileManager->listFiles(dirname($filePath));
        $fileDetails = null;
        foreach ($files as $file) {
            if ($file['path'] === $filePath) {
                $fileDetails = $file;
                break;
            }
        }

        if (!$fileDetails) {
            echo json_encode(['error' => 'Nie znaleziono pliku']);
            return;
        }

        // Sprawdź czy to obrazek
        $isImage = in_array(strtolower($fileDetails['extension'] ?? ''), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);

        echo json_encode([
            'name' => $fileDetails['name'],
            'path' => $fileDetails['path'],
            'size' => $fileDetails['size'] ?? 0,
            'size_formatted' => FileManager::formatSize($fileDetails['size'] ?? 0),
            'modified' => date('Y-m-d H:i:s', $fileDetails['modified']),
            'extension' => $fileDetails['extension'] ?? '',
            'is_image' => $isImage,
            'is_referenced' => $fileManager->isFileReferenced($filePath),
            'usage' => $usage,
        ]);
    }
}
