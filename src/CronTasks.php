<?php
declare(strict_types=1);

/**
 * Klasa zawierająca callbacki dla zadań cron
 */
class CronTasks
{
    private PrefixedPDO $pdo;

    public function __construct(PrefixedPDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Zadanie: Odświeżenie cache statusu licencji i wsparcia
     * Wykonywane: Co 24 godziny
     */
    public function refreshLicenseSupportCache(): void
    {
        require_once __DIR__ . '/LicenseChecker.php';

        $status = LicenseChecker::getStatus($this->pdo);

        $logMessage = sprintf(
            '[%s] CRON | refresh_license_support_cache | state=%s | support_until=%s | support_active=%s',
            date('Y-m-d H:i:s'),
            (string)($status['state'] ?? 'unknown'),
            (string)($status['support_until'] ?? 'null'),
            isset($status['support_active']) ? ((bool)$status['support_active'] ? '1' : '0') : 'null'
        );
        Utils::appendLog(__DIR__ . '/../storage/logs/cron.log', $logMessage);
    }
    
    /**
     * Zadanie: Czyszczenie starego cache geolokalizacji
     * Wykonywane: Co 24 godziny
     */
    public function cleanGeoCache(): void
    {
        $statsRepo = new StatsRepository($this->pdo);
        $deleted = $statsRepo->cleanOldGeoCache(30);
        
        $logMessage = sprintf(
            '[%s] CRON | clean_geo_cache | deleted=%d records',
            date('Y-m-d H:i:s'),
            $deleted
        );
        Utils::appendLog(__DIR__ . '/../storage/logs/geo_cache.log', $logMessage);
    }
    
    /**
     * Zadanie: Odświeżenie cache statystyk globalnych
     * Wykonywane: Co 30 minut
     */
    public function refreshGlobalStatsCache(): void
    {
        $statsRepo = new StatsRepository($this->pdo);
        
        // Odśwież cache dla różnych kombinacji
        $configs = [
            ['days' => 7, 'excludeBots' => false],
            ['days' => 7, 'excludeBots' => true],
            ['days' => 30, 'excludeBots' => false],
            ['days' => 30, 'excludeBots' => true],
            ['days' => 90, 'excludeBots' => false],
            ['days' => 90, 'excludeBots' => true],
        ];
        
        $refreshed = 0;
        foreach ($configs as $config) {
            try {
                $statsRepo->refreshGlobalStatsCache($config['days'], $config['excludeBots']);
                $refreshed++;
            } catch (Throwable $e) {
                // Kontynuuj mimo błędu
            }
        }
        
        $logMessage = sprintf(
            '[%s] CRON | refresh_global_stats | refreshed=%d caches',
            date('Y-m-d H:i:s'),
            $refreshed
        );
        Utils::appendLog(__DIR__ . '/../storage/logs/cron.log', $logMessage);
    }
    
    /**
     * Zadanie: Czyszczenie starych logów cron
     * Wykonywane: Co 7 dni
     */
    public function cleanCronLogs(): void
    {
        require_once __DIR__ . '/PseudoCron.php';
        
        $pseudoCron = new PseudoCron($this->pdo);
        $deleted = $pseudoCron->cleanOldLogs(30);
        
        $logMessage = sprintf(
            '[%s] CRON | clean_cron_logs | deleted=%d logs',
            date('Y-m-d H:i:s'),
            $deleted
        );
        Utils::appendLog(__DIR__ . '/../storage/logs/cron.log', $logMessage);
    }
    
    /**
     * Zadanie: Czyszczenie starych wydarzeń (opcjonalne)
     * Wykonywane: Co 30 dni
     */
    public function cleanOldEvents(): void
    {
        // Usuń wydarzenia starsze niż 90 dni
        $stmt = $this->pdo->prepare('
            DELETE FROM events
            WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)
        ');
        $stmt->execute();
        $deleted = $stmt->rowCount();
        
        $logMessage = sprintf(
            '[%s] CRON | clean_old_events | deleted=%d events',
            date('Y-m-d H:i:s'),
            $deleted
        );
        Utils::appendLog(__DIR__ . '/../storage/logs/cron.log', $logMessage);
    }

    /**
     * Zadanie: Czyszczenie osieroconych plików w uploads/ (nieużywane w bazie)
     * Wykonywane: domyślnie co 6 godzin
     */
    public function cleanOrphanUploads(): void
    {
        $uploadsRoot = realpath(__DIR__ . '/../uploads');
        if ($uploadsRoot === false || !is_dir($uploadsRoot)) {
            return;
        }

        $referenced = $this->collectReferencedUploadPaths();
        $referencedMap = array_fill_keys($referenced, true);

        $now = time();
        $minAgeSeconds = 6 * 3600; // nie usuwaj bardzo świeżych plików
        $checked = 0;
        $deleted = 0;
        $failed = 0;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($uploadsRoot, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }
            $checked++;
            $filePath = $fileInfo->getPathname();
            $relative = 'uploads/' . str_replace(['\\', DIRECTORY_SEPARATOR], '/', ltrim(str_replace($uploadsRoot, '', $filePath), '/\\'));

            // Jeśli plik jest referencjonowany w bazie, pomiń
            if (isset($referencedMap[$relative])) {
                continue;
            }

            // Nie usuwaj nowych plików (bezpieczny bufor czasu)
            if (($now - $fileInfo->getMTime()) < $minAgeSeconds) {
                continue;
            }

            if (@unlink($filePath)) {
                $deleted++;
            } else {
                $failed++;
            }
        }

        $logMessage = sprintf(
            '[%s] CRON | clean_orphan_uploads | checked=%d | deleted=%d | failed=%d',
            date('Y-m-d H:i:s'),
            $checked,
            $deleted,
            $failed
        );
        Utils::appendLog(__DIR__ . '/../storage/logs/cron.log', $logMessage);
    }

    private function collectReferencedUploadPaths(): array
    {
        $paths = [];

        // Link główny (og_image + thumb)
        $stmt = $this->pdo->query('SELECT og_image, og_image_thumb FROM links');
        foreach ($stmt->fetchAll() as $row) {
            $this->maybeAddPath($paths, $row['og_image'] ?? null);
            $this->maybeAddPath($paths, $row['og_image_thumb'] ?? null);
        }

        // Galerie
        $stmt = $this->pdo->query('SELECT path, thumb_path FROM link_images');
        foreach ($stmt->fetchAll() as $row) {
            $this->maybeAddPath($paths, $row['path'] ?? null);
            $this->maybeAddPath($paths, $row['thumb_path'] ?? null);
        }

        // Kategorie (ikony) - tylko categories ma kolumny icon_image
        $stmt = $this->pdo->query('SELECT icon_image, icon_image_thumb FROM categories');
        foreach ($stmt->fetchAll() as $row) {
            $this->maybeAddPath($paths, $row['icon_image'] ?? null);
            $this->maybeAddPath($paths, $row['icon_image_thumb'] ?? null);
        }

        // Branding (logo/fav)
        $stmt = $this->pdo->prepare('SELECT value FROM settings WHERE `key` IN ("branding_logo", "branding_favicon")');
        $stmt->execute();
        foreach ($stmt->fetchAll() as $row) {
            $this->maybeAddPath($paths, $row['value'] ?? null);
        }

        return array_values(array_unique($paths));
    }

    private function maybeAddPath(array &$paths, $value): void
    {
        $normalized = $this->normalizeUploadPath($value);
        if ($normalized !== null) {
            $paths[] = $normalized;
        }
    }

    private function normalizeUploadPath($raw): ?string
    {
        if ($raw === null) {
            return null;
        }
        $trimmed = ltrim((string)$raw, '/');
        if ($trimmed === '' || str_starts_with($trimmed, '.')) {
            return null;
        }
        // Akceptuj tylko ścieżki w uploads/
        if (strpos($trimmed, 'uploads/') !== 0) {
            return null;
        }
        return str_replace(['\\', '//'], '/', $trimmed);
    }

    /**
     * Zadanie: Czyszczenie kosza - automatyczne usuwanie starych linków
     * Wykonywane: Co 24 godziny
     */
    public function cleanTrash(): void
    {
        require_once __DIR__ . '/SettingsRepository.php';
        require_once __DIR__ . '/LinkRepository.php';

        $settingsRepo = new SettingsRepository($this->pdo);
        $linkRepo = new LinkRepository($this->pdo);

        $trashMode = $settingsRepo->get('trash_mode', 'auto_delete');

        // Jesli tryb nie jest auto_delete, nie robimy nic
        if ($trashMode !== 'auto_delete') {
            $logMessage = sprintf(
                '[%s] CRON | clean_trash | skipped (trash_mode=%s)',
                date('Y-m-d H:i:s'),
                $trashMode
            );
            Utils::appendLog(__DIR__ . '/../storage/logs/cron.log', $logMessage);
            return;
        }

        $days = (int)$settingsRepo->get('trash_auto_delete_days', '30');
        if ($days < 1) {
            $days = 30;
        }

        // Uzupelnij deleted_at dla legacy wierszy gdzie status='trashed' ale brak timestampa
        $linkRepo->backfillTrashedDeletedAt();

        // Pobierz linki do usuniecia
        $trashedLinks = $linkRepo->getTrashedOlderThan($days);

        if (empty($trashedLinks)) {
            $logMessage = sprintf(
                '[%s] CRON | clean_trash | no links to delete (older than %d days)',
                date('Y-m-d H:i:s'),
                $days
            );
            Utils::appendLog(__DIR__ . '/../storage/logs/cron.log', $logMessage);
            return;
        }

        $deleted = 0;
        $imagesDeleted = 0;

        foreach ($trashedLinks as $link) {
            $linkId = (int)$link['id'];

            // Usuń obrazy powiązane z linkiem
            $imagesDeleted += $this->deleteLinkImages($linkId);

            // Usuń link trwale
            $linkRepo->permanentDelete($linkId);
            $deleted++;
        }

        $logMessage = sprintf(
            '[%s] CRON | clean_trash | deleted=%d links, %d images (older than %d days)',
            date('Y-m-d H:i:s'),
            $deleted,
            $imagesDeleted,
            $days
        );
        Utils::appendLog(__DIR__ . '/../storage/logs/cron.log', $logMessage);
    }

    /**
     * Usuwa obrazy powiązane z linkiem
     */
    private function deleteLinkImages(int $linkId): int
    {
        $deleted = 0;
        $uploadsDir = realpath(__DIR__ . '/../uploads');
        if ($uploadsDir === false) {
            return 0;
        }

        // Pobierz obrazy z galerii
        $stmt = $this->pdo->prepare('SELECT path, thumb_path FROM link_images WHERE link_id = :id');
        $stmt->execute([':id' => $linkId]);
        $images = $stmt->fetchAll();

        foreach ($images as $img) {
            if (!empty($img['path'])) {
                $deleted += $this->safeDeleteFile($img['path'], $uploadsDir);
            }
            if (!empty($img['thumb_path'])) {
                $deleted += $this->safeDeleteFile($img['thumb_path'], $uploadsDir);
            }
        }

        // Pobierz og_image z linku
        $stmt = $this->pdo->prepare('SELECT og_image, og_image_thumb FROM links WHERE id = :id');
        $stmt->execute([':id' => $linkId]);
        $link = $stmt->fetch();

        if ($link) {
            if (!empty($link['og_image'])) {
                $deleted += $this->safeDeleteFile($link['og_image'], $uploadsDir);
            }
            if (!empty($link['og_image_thumb'])) {
                $deleted += $this->safeDeleteFile($link['og_image_thumb'], $uploadsDir);
            }
        }

        return $deleted;
    }

    /**
     * Bezpiecznie usuwa plik, walidując że ścieżka jest w katalogu uploads
     */
    private function safeDeleteFile(string $dbPath, string $uploadsDir): int
    {
        $relativePath = ltrim(str_replace('uploads/', '', $dbPath), '/');
        $fullPath = $uploadsDir . '/' . $relativePath;
        $realPath = realpath($fullPath);

        // Walidacja: sprawdź czy rozwiązana ścieżka jest wewnątrz uploads/
        if ($realPath === false) {
            return 0;
        }
        if (strpos($realPath, $uploadsDir) !== 0) {
            return 0;
        }

        if (file_exists($realPath) && @unlink($realPath)) {
            return 1;
        }
        return 0;
    }

    /**
     * Zadanie: Wysylanie powiadomien email z podsumowaniem statystyk
     * Wykonywane: Co 1 godzine (sprawdza czy czas na wyslanie)
     */
    public function sendEmailNotifications(): void
    {
        require_once __DIR__ . '/NotificationService.php';
        require_once __DIR__ . '/EmailService.php';

        $notificationService = new NotificationService($this->pdo);

        // Sprawdź czy należy wysłać
        if (!$notificationService->shouldSendNotification()) {
            $logMessage = sprintf(
                '[%s] CRON | email_notifications | skipped (not time yet)',
                date('Y-m-d H:i:s')
            );
            Utils::appendLog(__DIR__ . '/../storage/logs/cron.log', $logMessage);
            return;
        }

        // Wyślij
        try {
            $notificationService->sendNotification();

            $logMessage = sprintf(
                '[%s] CRON | email_notifications | sent successfully',
                date('Y-m-d H:i:s')
            );
            Utils::appendLog(__DIR__ . '/../storage/logs/email.log', $logMessage);

        } catch (Throwable $e) {
            $logMessage = sprintf(
                '[%s] CRON | email_notifications | error=%s',
                date('Y-m-d H:i:s'),
                $e->getMessage()
            );
            Utils::appendLog(__DIR__ . '/../storage/logs/email.log', $logMessage);
        }
    }

    /**
     * Zadanie: Sprawdzanie dostępności linków docelowych
     * Wykonywane: Co 24 godziny
     */
    public function checkBrokenLinks(): void
    {
        require_once __DIR__ . '/LinkHealthChecker.php';
        require_once __DIR__ . '/LinkHealthCheckRepository.php';
        require_once __DIR__ . '/LinkRepository.php';

        $checker = new LinkHealthChecker($this->pdo);
        $results = $checker->checkAllLinks();

        $logMessage = sprintf(
            '[%s] CRON | check_broken_links | checked=%d | healthy=%d | broken=%d | skipped=%d',
            date('Y-m-d H:i:s'),
            $results['checked'],
            $results['healthy'],
            $results['broken'],
            $results['skipped']
        );
        Utils::appendLog(__DIR__ . '/../storage/logs/link_health.log', $logMessage);

        // Utwórz powiadomienie jeśli wykryto uszkodzone linki
        if ($results['broken'] > 0) {
            try {
                require_once __DIR__ . '/NotificationRepository.php';
                $notifRepo = new NotificationRepository($this->pdo);
                $notifRepo->create(
                    'broken_links',
                    "Wykryto {$results['broken']} uszkodzonych linków",
                    "Sprawdzenie wykryło {$results['broken']} uszkodzonych linków z {$results['checked']} sprawdzonych.",
                    $results['broken'] > 5 ? 'critical' : 'warning',
                    ['broken_count' => $results['broken'], 'checked_count' => $results['checked'], 'healthy_count' => $results['healthy']]
                );
            } catch (Throwable $ignored) {
                // Celowo pomijamy — powiadomienie o uszkodzonych linkach jest best-effort;
                // wynik sprawdzenia został już zalogowany do pliku logu powyżej.
            }
        }
    }

    /**
     * Zadanie: Czyszczenie starej historii sprawdzeń linków
     * Wykonywane: Co 7 dni
     */
    public function cleanLinkHealthHistory(): void
    {
        require_once __DIR__ . '/LinkHealthCheckRepository.php';

        $repo = new LinkHealthCheckRepository($this->pdo);
        $deleted = $repo->cleanOldHistory(10); // Zachowaj ostatnie 10 sprawdzeń na link

        $logMessage = sprintf(
            '[%s] CRON | clean_link_health_history | deleted=%d records',
            date('Y-m-d H:i:s'),
            $deleted
        );
        Utils::appendLog(__DIR__ . '/../storage/logs/link_health.log', $logMessage);
    }

    /**
     * Zadanie: Czyszczenie starych logów audytu
     * Wykonywane: Co 30 dni
     */
    public function cleanAuditLogs(): void
    {
        require_once __DIR__ . '/AuditLogRepository.php';

        $repo = new AuditLogRepository($this->pdo);
        $deleted = $repo->cleanOldLogs(90); // Zachowaj 90 dni

        $logMessage = sprintf(
            '[%s] CRON | clean_audit_logs | deleted=%d records',
            date('Y-m-d H:i:s'),
            $deleted
        );
        Utils::appendLog(__DIR__ . '/../storage/logs/cron.log', $logMessage);
    }

    /**
     * Zadanie: Czyszczenie starych powiadomień systemowych
     * Wykonywane: Co 30 dni
     */
    public function cleanNotifications(): void
    {
        require_once __DIR__ . '/NotificationRepository.php';

        $repo = new NotificationRepository($this->pdo);
        $deleted = $repo->cleanOldNotifications(90); // Zachowaj 90 dni

        $logMessage = sprintf(
            '[%s] CRON | clean_notifications | deleted=%d records',
            date('Y-m-d H:i:s'),
            $deleted
        );
        Utils::appendLog(__DIR__ . '/../storage/logs/cron.log', $logMessage);
    }

    /**
     * Zadanie: Czyszczenie wygasłych rekordów prób logowania i formularza kontaktowego
     * Wykonywane: Co 24 godziny
     */
    public function cleanLoginAttempts(): void
    {
        require_once __DIR__ . '/LoginRateLimiter.php';
        require_once __DIR__ . '/ContactRateLimiter.php';

        $limiter = new LoginRateLimiter($this->pdo);
        $limiter->cleanExpired();

        $contactLimiter = new ContactRateLimiter($this->pdo);
        $contactLimiter->cleanExpired();

        $logMessage = sprintf(
            '[%s] CRON | clean_login_attempts | expired login and contact rate-limit records removed',
            date('Y-m-d H:i:s')
        );
        Utils::appendLog(__DIR__ . '/../storage/logs/cron.log', $logMessage);
    }

    /**
     * Zadanie: Aktualizacja rozmiarów katalogów storage
     * Wykonywane: Co 6 godzin
     */
    public function updateStorageSizes(): void
    {
        require_once __DIR__ . '/StorageService.php';

        try {
            $storageService = new StorageService($this->pdo);
            $stats = $storageService->recalculateAndStore();

            $logMessage = sprintf(
                '[%s] CRON | update_storage_sizes | uploads=%s logs=%s cache=%s total=%s',
                date('Y-m-d H:i:s'),
                $stats['uploads']['size_formatted'],
                $stats['logs']['size_formatted'],
                $stats['cache']['size_formatted'],
                $stats['total_size_formatted']
            );
            Utils::appendLog(__DIR__ . '/../storage/logs/cron.log', $logMessage);
        } catch (Exception $e) {
            $logMessage = sprintf(
                '[%s] CRON | update_storage_sizes | ERROR: %s',
                date('Y-m-d H:i:s'),
                $e->getMessage()
            );
            Utils::appendLog(__DIR__ . '/../storage/logs/cron.log', $logMessage);
        }
    }

    /**
     * Zadanie: Automatyczne tworzenie kopii zapasowych (SQL + pliki uploads).
     * Wykonuje się według skonfigurowanego interwału, jeśli backup_auto_enabled = true.
     */
    public function createAutoBackup(): void
    {
        require_once __DIR__ . '/SettingsRepository.php';
        require_once __DIR__ . '/BackupService.php';

        $repo = new SettingsRepository($this->pdo);
        if (!(bool)$repo->get('backup_auto_enabled', false)) {
            return;
        }

        try {
            $service  = new BackupService($this->pdo, dirname(__DIR__));
            $filename = $service->createBackup(true);

            // Adjust next_run to respect preferred backup hour
            $preferredHourRaw = $repo->get('backup_preferred_hour', '');
            if ($preferredHourRaw !== '' && $preferredHourRaw !== null) {
                $preferredHour = max(0, min(23, (int)$preferredHourRaw));
                $stmt = $this->pdo->prepare("SELECT id, interval_seconds FROM cron_jobs WHERE name = 'auto_backup'");
                $stmt->execute();
                $cronJob = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($cronJob) {
                    $nextRun = BackupService::calcNextRunWithPreferredHour((int)$cronJob['interval_seconds'], $preferredHour);
                    $stmt = $this->pdo->prepare('UPDATE cron_jobs SET next_run = ? WHERE id = ?');
                    $stmt->execute([$nextRun, (int)$cronJob['id']]);
                }
            }

            $logMessage = sprintf(
                '[%s] CRON | auto_backup | created=%s',
                date('Y-m-d H:i:s'),
                $filename
            );
            Utils::appendLog(__DIR__ . '/../storage/logs/cron.log', $logMessage);
        } catch (\Throwable $e) {
            $logMessage = sprintf(
                '[%s] CRON | auto_backup | ERROR: %s',
                date('Y-m-d H:i:s'),
                $e->getMessage()
            );
            Utils::appendLog(__DIR__ . '/../storage/logs/cron.log', $logMessage);
        }
    }
}
