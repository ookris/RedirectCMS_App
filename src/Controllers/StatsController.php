<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../StatsRepository.php';
require_once __DIR__ . '/../LinkRepository.php';
require_once __DIR__ . '/../EventRepository.php';
require_once __DIR__ . '/../CategoryRepository.php';
require_once __DIR__ . '/../AffiliateProgramRepository.php';
require_once __DIR__ . '/../SettingsRepository.php';
require_once __DIR__ . '/../StorageService.php';
require_once __DIR__ . '/../VersionChecker.php';

class StatsController extends BaseController
{
    public function dashboard(): void
    {
        Utils::requireLogin();

        ['sortBy' => $sortBy, 'sortOrder' => $sortOrder, 'searchQuery' => $searchQuery,
         'categoryFilter' => $categoryFilter, 'programFilter' => $programFilter] = $this->parseListingParams();

        // Pobierz wszystkie kategorie i programy afiliacyjne dla filtrów
        $categories = (new CategoryRepository($this->pdo))->list(1000, 0);
        $affiliatePrograms = (new AffiliateProgramRepository($this->pdo))->listAll();

        // Pobierz linki z zastosowaniem sortowania, wyszukiwania i filtrowania (tylko aktywne/published)
        $links = (new LinkRepository($this->pdo))->list(10, 0, $categoryFilter, null, $sortBy, $sortOrder, $searchQuery, false, false, $programFilter, 'published');

        $statsRepo = new StatsRepository($this->pdo);
        try {
            $quickStats = $statsRepo->getQuickStats();
        } catch (\Throwable $e) {
            $quickStats = [];
        }
        try {
            $systemStatus = $statsRepo->getSystemStatus();
        } catch (\Throwable $e) {
            $systemStatus = ['statistics' => [], 'resources' => [], 'technical' => []];
        }

        // Widgety dashboard
        $recentLinks = [];
        $topPerformers = [];
        $anomalies = [];

        try {
            $recentLinks = $statsRepo->getRecentLinks(5);
        } catch (\Throwable $e) {
            // Ignoruj błędy
        }

        try {
            $topPerformers = $statsRepo->getTopPerformingLinks(5, 30);
        } catch (\Throwable $e) {
            // Ignoruj błędy
        }

        try {
            $anomalies = $statsRepo->detectAnomalies();
        } catch (\Throwable $e) {
            // Ignoruj błędy
        }

        // Pobierz dane dla wykresów
        $chartData = [];
        try {
            $chartData = $statsRepo->getDashboardChartData(30, false);
        } catch (\Throwable $e) {
            // Ignoruj błędy
        }

        // Pobierz dane dla programów afiliacyjnych
        $affiliateProgramStats = [];
        try {
            $affiliateProgramStats = $statsRepo->getAffiliateProgramData(10);
        } catch (\Throwable $e) {
            // Ignoruj błędy
        }

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
                    $storageService = new StorageService($this->pdo);
                    $storageStats = $storageService->getAllStorageStats();
                    $currentFilesMb = round($storageStats['total_size'] / 1024 / 1024, 2);
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
            // Ignoruj błędy
            error_log('Error getting disk quota data: ' . $e->getMessage());
        }

        // Sprawdzanie nowej wersji
        $versionInfo = ['current_version' => defined('RCMS_VERSION') ? RCMS_VERSION : '?', 'update_available' => false];
        try {
            $checker     = new VersionChecker($this->pdo);
            $versionInfo = $checker->getVersionInfo();
            $checker->createUpdateNotificationIfNeeded($versionInfo);
        } catch (\Throwable $e) {
            error_log('Error checking version: ' . $e->getMessage());
        }

        $this->view('dashboard', [
            'links' => $links,
            'stats' => $quickStats,
            'systemStatus' => $systemStatus,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'searchQuery' => $searchQuery,
            'categoryFilter' => $categoryFilter,
            'programFilter' => $programFilter,
            'categories' => $categories,
            'affiliatePrograms' => $affiliatePrograms,
            'recentLinks' => $recentLinks,
            'topPerformers' => $topPerformers,
            'anomalies' => $anomalies,
            'chartData' => $chartData,
            'affiliateProgramStats' => $affiliateProgramStats,
            'diskQuotaData' => $diskQuotaData,
            'versionInfo' => $versionInfo,
        ]);
    }

    public function stats(int $id): void
    {
        Utils::requireLogin();
        $repo = new LinkRepository($this->pdo);
        $link = $repo->getById($id);

        if (!$link) {
            http_response_code(404);
            echo 'Link nie istnieje';
            return;
        }

        $days = (int)($_GET['days'] ?? 30);
        $allowedDays = [7, 30, 90];
        if (!in_array($days, $allowedDays, true)) {
            $days = 30;
        }
        $excludeBots = isset($_GET['exclude_bots']) && (int)$_GET['exclude_bots'] === 1;

        $statsRepo = new StatsRepository($this->pdo);
        $stats = $statsRepo->getLinkStats($id, $days, $excludeBots);

        $this->view('stats', [
            'link' => $link,
            'stats' => $stats,
            'days' => $days,
            'excludeBots' => $excludeBots,
        ]);
    }

    public function exportStats(int $id): void
    {
        Utils::requireLogin();
        $repo = new LinkRepository($this->pdo);
        $link = $repo->getById($id);
        if (!$link) {
            http_response_code(404);
            echo 'Link nie istnieje';
            return;
        }

        $days = (int)($_GET['days'] ?? 30);
        $allowedDays = [7, 30, 90];
        if (!in_array($days, $allowedDays, true)) {
            $days = 30;
        }
        $excludeBots = isset($_GET['exclude_bots']) && (int)$_GET['exclude_bots'] === 1;
        $type = (string)($_GET['type'] ?? 'daily');
        $format = (string)($_GET['format'] ?? 'csv');

        $statsRepo = new StatsRepository($this->pdo);
        $stats = $statsRepo->getLinkStats($id, $days, $excludeBots);

        $filenameBase = 'stats_' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$link['slug']) . '_' . $type . '_' . $days . 'd' . ($excludeBots ? '_nobots' : '');

        if ($format === 'json') {
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="' . $filenameBase . '.json"');
            switch ($type) {
                case 'daily':
                    echo json_encode($stats['daily_chart']);
                    break;
                case 'referer':
                    echo json_encode($stats['by_referer']);
                    break;
                case 'device':
                    echo json_encode($stats['by_device']);
                    break;
                case 'browser':
                    echo json_encode($stats['by_browser']);
                    break;
                case 'hourly':
                    echo json_encode($stats['hourly_distribution']);
                    break;
                default:
                    echo json_encode(['error' => 'Nieznany typ']);
            }
            return;
        }

        // CSV eksport
        // Wyłącz HTML błędów, aby nie zanieczyszczać CSV
        @ini_set('display_errors', '0');
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filenameBase . '.csv"');
        $out = fopen('php://output', 'w');
        // BOM dla Excel (UTF-8)
        fwrite($out, "\xEF\xBB\xBF");

        switch ($type) {
            case 'daily':
                fputcsv($out, ['Data', 'Kliknięcia'], ',', '"', '\\');
                foreach ($stats['daily_chart'] as $row) {
                    fputcsv($out, [Utils::csvSafe($row['date']), $row['count']], ',', '"', '\\');
                }
                break;
            case 'referer':
                // Referer pochodzi z nagłówka HTTP kontrolowanego przez odwiedzającego —
                // bez csvSafe() mógłby zawierać formułę wykonywaną po otwarciu w Excelu.
                fputcsv($out, ['Źródło', 'Kliknięcia'], ',', '"', '\\');
                foreach ($stats['by_referer'] as $row) {
                    fputcsv($out, [Utils::csvSafe($row['referer']), $row['count']], ',', '"', '\\');
                }
                break;
            case 'device':
                fputcsv($out, ['Urządzenie', 'Kliknięcia'], ',', '"', '\\');
                foreach ($stats['by_device'] as $row) {
                    fputcsv($out, [Utils::csvSafe($row['device_type']), $row['count']], ',', '"', '\\');
                }
                break;
            case 'browser':
                fputcsv($out, ['Przeglądarka', 'Kliknięcia'], ',', '"', '\\');
                foreach ($stats['by_browser'] as $row) {
                    fputcsv($out, [Utils::csvSafe($row['browser']), $row['count']], ',', '"', '\\');
                }
                break;
            case 'hourly':
                fputcsv($out, ['Godzina', 'Kliknięcia'], ',', '"', '\\');
                foreach ($stats['hourly_distribution'] as $hour => $count) {
                    fputcsv($out, [$hour, $count], ',', '"', '\\');
                }
                break;
            default:
                fputcsv($out, ['Błąd', 'Nieznany typ eksportu'], ',', '"', '\\');
        }

        fclose($out);
    }

    public function geoCacheGet(): void
    {
        Utils::requireLogin();
        $statsRepo = new StatsRepository($this->pdo);
        $cacheStats = $statsRepo->getGeoCacheStats();
        $logFile = dirname(__DIR__, 2) . '/storage/logs/geo_cache.log';
        $logs = Utils::tailFile($logFile, 10);

        $this->view('geo_cache', [
            'csrf' => Utils::csrfToken(),
            'stats' => $cacheStats,
            'logs' => $logs,
            'success' => $_SESSION['cache_success'] ?? null,
        ]);
        unset($_SESSION['cache_success']);
    }

    public function geoCacheCleanup(): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Błędny CSRF token.';
            return;
        }

        $action = (string)($_POST['action'] ?? '');
        $statsRepo = new StatsRepository($this->pdo);

        Utils::startSession();

        if ($action === 'clean_old') {
            $days = max(30, (int)($_POST['days'] ?? 60));
            $deleted = $statsRepo->cleanOldGeoCache($days);
            $_SESSION['cache_success'] = "Usunięto {$deleted} starych wpisów (>{$days} dni)";
            $msg = sprintf('[%s] ADMIN | clean_old days=%d | deleted=%d | ip=%s', date('Y-m-d H:i:s'), $days, $deleted, $_SERVER['REMOTE_ADDR'] ?? 'unknown');
            Utils::appendLog(dirname(__DIR__, 2) . '/storage/logs/geo_cache.log', $msg);
        } elseif ($action === 'clear_all') {
            $deleted = $statsRepo->clearAllGeoCache();
            $_SESSION['cache_success'] = "Wyczyszczono cały cache ({$deleted} wpisów)";
            $msg = sprintf('[%s] ADMIN | clear_all | deleted=%d | ip=%s', date('Y-m-d H:i:s'), $deleted, $_SERVER['REMOTE_ADDR'] ?? 'unknown');
            Utils::appendLog(dirname(__DIR__, 2) . '/storage/logs/geo_cache.log', $msg);
        }

        header('Location: ' . $this->basePath . '/admin/index.php?action=geo_cache');
    }

    public function globalStats(): void
    {
        Utils::requireLogin();
        $days = (int)($_GET['days'] ?? 30);
        $allowedDays = [7, 30, 90];
        if (!in_array($days, $allowedDays, true)) {
            $days = 30;
        }
        $excludeBots = isset($_GET['exclude_bots']) && (int)$_GET['exclude_bots'] === 1;

        $statsRepo = new StatsRepository($this->pdo);

        // Sprawdź czy cache istnieje i czy jest aktualny
        $cacheKey = $statsRepo->getGlobalStatsCacheKey($days, $excludeBots);
        $cacheStatus = $statsRepo->checkGlobalStatsCacheStatus($cacheKey);

        // Jeśli cache nie istnieje lub jest przestarzały - pokaż loader i wygeneruj
        if (!$cacheStatus['exists'] || $cacheStatus['expired']) {
            $this->view('global_stats_loading', [
                'days' => $days,
                'excludeBots' => $excludeBots,
                'csrf' => Utils::csrfToken(),
                'reason' => !$cacheStatus['exists'] ? 'brak' : 'przestarzały',
            ]);
            return;
        }

        // Cache jest aktualny - pobierz statystyki
        $stats = $statsRepo->getGlobalStats($days, $excludeBots);

        // Pobierz statystyki programów afiliacyjnych
        $affiliateProgramStats = $statsRepo->getAffiliateProgramData(10);

        // Pobierz informacje o cache
        $cacheInfo = $statsRepo->getGlobalStatsCacheInfo();
        $currentCache = null;
        foreach ($cacheInfo as $cache) {
            if ($cache['cache_key'] === $cacheKey) {
                $currentCache = $cache;
                break;
            }
        }

        $this->view('global_stats', [
            'stats' => $stats,
            'days' => $days,
            'excludeBots' => $excludeBots,
            'cacheInfo' => $currentCache,
            'csrf' => Utils::csrfToken(),
            'refreshSuccess' => $_SESSION['refresh_success'] ?? null,
            'affiliateProgramStats' => $affiliateProgramStats,
        ]);
        unset($_SESSION['refresh_success']);
    }

    public function refreshGlobalStatsCache(): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Błędny CSRF token']);
            return;
        }

        $days = (int)($_POST['days'] ?? 30);
        $excludeBots = isset($_POST['exclude_bots']) && (int)$_POST['exclude_bots'] === 1;

        try {
            $statsRepo = new StatsRepository($this->pdo);
            $startTime = microtime(true);
            $statsRepo->refreshGlobalStatsCache($days, $excludeBots);
            $duration = round(microtime(true) - $startTime, 2);

            // Jeśli to request AJAX - zwróć JSON
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'duration' => $duration,
                    'message' => "Cache statystyk został odświeżony pomyślnie ({$duration}s)"
                ]);
                return;
            }

            // Standardowy request - redirect
            Utils::startSession();
            $_SESSION['refresh_success'] = "Cache statystyk został odświeżony pomyślnie ({$duration}s)";

            $query = http_build_query([
                'action' => 'global_stats',
                'days' => $days,
                'exclude_bots' => $excludeBots ? 1 : 0
            ]);
            header('Location: ' . $this->basePath . '/admin/index.php?' . $query);
        } catch (\Throwable $e) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
            } else {
                http_response_code(500);
                echo 'Błąd: ' . htmlspecialchars($e->getMessage());
            }
        }
    }
}
