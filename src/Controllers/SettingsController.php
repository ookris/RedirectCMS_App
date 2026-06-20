<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../SettingsRepository.php';
require_once __DIR__ . '/../ThemeManager.php';
require_once __DIR__ . '/../EmailService.php';
require_once __DIR__ . '/../NotificationService.php';
require_once __DIR__ . '/../StatsRepository.php';
require_once __DIR__ . '/../AuditService.php';
require_once __DIR__ . '/../ImageUploader.php';

class SettingsController extends BaseController
{
    public function settingsGet(): void
    {
        Utils::requireLogin();
        $repo = new SettingsRepository($this->pdo);
        $delay = $repo->get('redirect_delay_seconds', 5);
        $length = $repo->get('random_slug_length', 8);
        $username = $repo->get('admin_username', 'admin');

        // Ustawienia kosza
        $trashMode = (string)$repo->get('trash_mode', 'auto_delete');
        $trashAutoDeleteDays = (int)$repo->get('trash_auto_delete_days', 30);

        // Przyjazne adresy URL
        $prettyUrls = (bool)$repo->get('pretty_urls', false);

        // Limity miejsca na dysku
        $diskQuotaFilesMb = (int)$repo->get('disk_quota_files_mb', 0);
        $diskQuotaDatabaseMb = (int)$repo->get('disk_quota_database_mb', 0);

        // Ustawienia strony głównej
        $homeMode = (string)$repo->get('home_mode', 'landing');
        $homeTitle = (string)$repo->get('home_title', 'RedirectCMS');
        $homeSubtitle = (string)$repo->get('home_subtitle', 'Skrócone linki z historią');
        $homeDescription = (string)$repo->get('home_description', '');
        $homeSectionTitle = (string)$repo->get('home_section_title', 'Jak używać?');
        $homeSectionText = (string)$repo->get('home_section_text', 'Aby skorzystać z systemu skróconych linków, podaj prawidłowy skrót adresu w poniższy sposób:');
        $homeFooter = (string)$repo->get('home_footer', '');
        $homeCtaButtonText = (string)$repo->get('home_admin_button_text', '');
        $homeCtaButtonUrl = (string)$repo->get('home_cta_button_url', '');
        $homeRedirectUrl = (string)$repo->get('home_redirect_url', '');

        $brandingLogo = (string)$repo->get('branding_logo', '');
        $brandingFavicon = (string)$repo->get('branding_favicon', '');

        // Kody
        $homeHeaderCode = (string)$repo->get('home_header_code', '');
        $homeFooterCode = (string)$repo->get('home_footer_code', '');
        $redirectHeaderCode = (string)$repo->get('redirect_header_code', '');
        $redirectFooterCode = (string)$repo->get('redirect_footer_code', '');
        // Zachowanie dla botów społecznościowych
        $disableSocialRedirect = ((int)$repo->get('disable_social_redirect', 1) === 1);

        // SMTP Configuration
        $smtpHost = (string)$repo->get('smtp_host', '');
        $smtpPort = (int)$repo->get('smtp_port', 587);
        $smtpUsername = (string)$repo->get('smtp_username', '');
        $smtpEncryption = (string)$repo->get('smtp_encryption', 'tls');
        $smtpFromEmail = (string)$repo->get('smtp_from_email', '');
        $smtpFromName = (string)$repo->get('smtp_from_name', 'RedirectCMS');

        // Notification Settings
        $notificationEnabled = (bool)$repo->get('notification_enabled', false);
        $notificationEmail = (string)$repo->get('notification_email', '');
        $notificationFrequency = (string)$repo->get('notification_frequency', '1');
        $notificationCustomDays = (int)$repo->get('notification_custom_days', 7);
        $notificationTime = (string)$repo->get('notification_time', '09:00');
        $notificationTopLinks = (int)$repo->get('notification_top_links', 10);
        $notificationLastSent = $repo->get('notification_last_sent', null);
        $timezone = (string)$repo->get('timezone', 'Europe/Warsaw');

        // Pobierz aktualny czas serwera i czas w strefie użytkownika
        $serverTime = date('Y-m-d H:i:s');
        $serverTimezone = date_default_timezone_get();
        try {
            $userDateTime = new \DateTime('now', new \DateTimeZone($timezone));
            $userTime = $userDateTime->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            $userTime = $serverTime;
        }

        // 2FA Status
        $twoFactorEnabled = !empty($repo->get('two_factor_secret', ''));

        // Debug Settings
        $debugEnabled = (bool)$repo->get('debug_enabled', false);
        $debugLogErrors = (bool)$repo->get('debug_log_errors', true);
        $debugDisplayErrors = (bool)$repo->get('debug_display_errors', false);
        $debugErrorLevel = (string)$repo->get('debug_error_level', 'E_ALL');

        // Informacje o logach
        require_once __DIR__ . '/../DebugConfig.php';
        DebugConfig::init($this->pdo, dirname(__DIR__, 2) . '/storage/logs');
        $debugLogPath = DebugConfig::getLogPath();
        $debugLogSize = DebugConfig::getLogSize();
        $debugLogSizeFormatted = DebugConfig::formatSize($debugLogSize);

        $customFieldService = new CustomFieldService($this->pdo);
        $customFieldDefinitions = $customFieldService->getDefinitions();

        // ?tab= w URL ma priorytet nad sesją (np. link z banera licencji)
        // Whitelist: tylko znaki alfanumeryczne, myślniki i podkreślenia — chroni przed XSS w osadzeniu JS
        $tabFromUrlRaw = trim((string)($_GET['tab'] ?? ''));
        $tabFromUrl    = (preg_match('/^[a-zA-Z0-9_-]+$/', $tabFromUrlRaw) === 1) ? $tabFromUrlRaw : '';

        $sessionTabRaw = (string)($_SESSION['active_settings_tab'] ?? '');
        $sessionTab    = (preg_match('/^[a-zA-Z0-9_-]+$/', $sessionTabRaw) === 1) ? $sessionTabRaw : '';

        $activeTab = $tabFromUrl !== '' ? $tabFromUrl : ($sessionTab !== '' ? $sessionTab : 'general');

        $this->view('settings', [
            'csrf' => Utils::csrfToken(),
            'activeTab' => $activeTab,
            'delay' => (int)$delay,
            'length' => (int)$length,
            'username' => (string)$username,
            'prettyUrls' => $prettyUrls,
            'trashMode' => $trashMode,
            'trashAutoDeleteDays' => $trashAutoDeleteDays,
            'diskQuotaFilesMb' => $diskQuotaFilesMb,
            'diskQuotaDatabaseMb' => $diskQuotaDatabaseMb,
            'homeMode' => $homeMode,
            'homeTitle' => $homeTitle,
            'homeSubtitle' => $homeSubtitle,
            'homeDescription' => $homeDescription,
            'homeSectionTitle' => $homeSectionTitle,
            'homeSectionText' => $homeSectionText,
            'homeFooter' => $homeFooter,
            'homeCtaButtonText' => $homeCtaButtonText,
            'homeCtaButtonUrl' => $homeCtaButtonUrl,
            'homeRedirectUrl' => $homeRedirectUrl,
            'homeHeaderCode' => $homeHeaderCode,
            'homeFooterCode' => $homeFooterCode,
            'redirectHeaderCode' => $redirectHeaderCode,
            'redirectFooterCode' => $redirectFooterCode,
            'disableSocialRedirect' => $disableSocialRedirect,
            'brandingLogo' => $brandingLogo,
            'brandingFavicon' => $brandingFavicon,
            'username_error' => $_SESSION['username_error'] ?? null,
            'username_success' => $_SESSION['username_success'] ?? null,
            'password_error' => $_SESSION['password_error'] ?? null,
            'password_success' => $_SESSION['password_success'] ?? null,
            'customFieldDefinitions' => $customFieldDefinitions,
            'smtpHost' => $smtpHost,
            'smtpPort' => $smtpPort,
            'smtpUsername' => $smtpUsername,
            'smtpEncryption' => $smtpEncryption,
            'smtpFromEmail' => $smtpFromEmail,
            'smtpFromName' => $smtpFromName,
            'notificationEnabled' => $notificationEnabled,
            'notificationEmail' => $notificationEmail,
            'notificationFrequency' => $notificationFrequency,
            'notificationCustomDays' => $notificationCustomDays,
            'notificationTime' => $notificationTime,
            'notificationTopLinks' => $notificationTopLinks,
            'notificationLastSent' => $notificationLastSent,
            'timezone' => $timezone,
            'serverTime' => $serverTime,
            'serverTimezone' => $serverTimezone,
            'userTime' => $userTime,
            'twoFactorEnabled' => $twoFactorEnabled,
            'debugEnabled' => $debugEnabled,
            'debugLogErrors' => $debugLogErrors,
            'debugDisplayErrors' => $debugDisplayErrors,
            'debugErrorLevel' => $debugErrorLevel,
            'debugLogPath' => $debugLogPath,
            'debugLogSize' => $debugLogSize,
            'debugLogSizeFormatted' => $debugLogSizeFormatted,
        ]);
        unset(
            $_SESSION['password_error'],
            $_SESSION['password_success'],
            $_SESSION['username_error'],
            $_SESSION['username_success'],
            $_SESSION['active_settings_tab']
        );
    }

    public function settingsPost(): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Błędny CSRF token.';
            return;
        }
        $delay = max(0, (int)($_POST['delay'] ?? 5));
        $length = max(4, min(64, (int)($_POST['length'] ?? 8)));
        $repo = new SettingsRepository($this->pdo);
        $repo->set('redirect_delay_seconds', $delay);
        $repo->set('random_slug_length', $length);

        // Ustawienia kosza
        $trashMode = trim((string)($_POST['trash_mode'] ?? 'auto_delete'));
        if (!in_array($trashMode, ['auto_delete', 'keep_forever', 'hard_delete'], true)) {
            $trashMode = 'auto_delete';
        }
        $trashAutoDeleteDays = max(1, min(365, (int)($_POST['trash_auto_delete_days'] ?? 30)));
        $repo->set('trash_mode', $trashMode);
        $repo->set('trash_auto_delete_days', $trashAutoDeleteDays);

        // Limity miejsca na dysku (przeliczenie z wybranej jednostki na MB)
        $filesValue = (float)($_POST['disk_quota_files_value'] ?? 0);
        $filesUnit = $_POST['disk_quota_files_unit'] ?? 'MB';
        $diskQuotaFilesMb = $this->convertToMb($filesValue, $filesUnit);

        $dbValue = (float)($_POST['disk_quota_database_value'] ?? 0);
        $dbUnit = $_POST['disk_quota_database_unit'] ?? 'MB';
        $diskQuotaDatabaseMb = $this->convertToMb($dbValue, $dbUnit);

        $repo->set('disk_quota_files_mb', $diskQuotaFilesMb > 0 ? $diskQuotaFilesMb : '');
        $repo->set('disk_quota_database_mb', $diskQuotaDatabaseMb > 0 ? $diskQuotaDatabaseMb : '');

        // Przyjazne adresy URL
        $repo->set('pretty_urls', isset($_POST['pretty_urls']) ? 1 : 0);

        // Ustawienia strony głównej
        $homeMode = trim((string)($_POST['home_mode'] ?? 'landing'));
        if (!in_array($homeMode, ['landing', 'redirect', 'blog'], true)) {
            $homeMode = 'landing';
        }
        $repo->set('home_mode', $homeMode);

        $homeTitle = trim((string)($_POST['home_title'] ?? 'RedirectCMS'));
        $homeSubtitle = trim((string)($_POST['home_subtitle'] ?? 'Skrócone linki z historią'));
        $homeDescription = trim((string)($_POST['home_description'] ?? ''));
        $homeSectionTitle = trim((string)($_POST['home_section_title'] ?? 'Jak używać?'));
        $homeSectionText = trim((string)($_POST['home_section_text'] ?? ''));
        $homeFooter = (string)($_POST['home_footer'] ?? '');
        $homeCtaButtonText = trim((string)($_POST['home_admin_button_text'] ?? ''));
        $homeCtaButtonUrl = trim((string)($_POST['home_cta_button_url'] ?? ''));
        $homeRedirectUrl = trim((string)($_POST['home_redirect_url'] ?? ''));

        $repo->set('home_title', $homeTitle ?: 'RedirectCMS');
        $repo->set('home_subtitle', $homeSubtitle ?: 'Skrócone linki z historią');
        $repo->set('home_description', $homeDescription);
        $repo->set('home_section_title', $homeSectionTitle ?: 'Jak używać?');
        $repo->set('home_section_text', $homeSectionText ?: 'Aby skorzystać z systemu skróconych linków, podaj prawidłowy skrót adresu w poniższy sposób:');
        $repo->set('home_footer', $homeFooter);
        $repo->set('home_admin_button_text', $homeCtaButtonText);
        $repo->set('home_cta_button_url', $homeCtaButtonUrl);
        $repo->set('home_redirect_url', $homeRedirectUrl);

        // Zapisz kody wyświetlane w nagłówku i stopce strony głównej
        $homeHeaderCode = (string)($_POST['home_header_code'] ?? '');
        $homeFooterCode = (string)($_POST['home_footer_code'] ?? '');
        $repo->set('home_header_code', $homeHeaderCode);
        $repo->set('home_footer_code', $homeFooterCode);

        // Zapisz kody wyświetlane w nagłówku i stopce strony przekierowania
        $redirectHeaderCode = (string)($_POST['redirect_header_code'] ?? '');
        $redirectFooterCode = (string)($_POST['redirect_footer_code'] ?? '');
        $repo->set('redirect_header_code', $redirectHeaderCode);
        $repo->set('redirect_footer_code', $redirectFooterCode);

        // Branding: logo (jpg/png) - optional
        $existingLogo = (string)$repo->get('branding_logo', '');
        $existingFavicon = (string)$repo->get('branding_favicon', '');

        // Usunięcie logo
        if (isset($_POST['delete_logo']) && $_POST['delete_logo'] === '1' && $existingLogo) {
            $uploader = new ImageUploader(__DIR__ . '/../../uploads', null, ['image/jpeg', 'image/png'], 80, 80);
            $uploader->delete($existingLogo);
            $repo->set('branding_logo', '');
            $existingLogo = '';
        }

        // Usunięcie favikony
        if (isset($_POST['delete_favicon']) && $_POST['delete_favicon'] === '1' && $existingFavicon) {
            $uploader = new ImageUploader(__DIR__ . '/../../uploads', null, ['image/jpeg', 'image/png'], 16, 16);
            $uploader->delete($existingFavicon);
            $repo->set('branding_favicon', '');
            $existingFavicon = '';
        }

        // Upload logo (jpg/png, min 80x80)
        if (isset($_FILES['branding_logo']) && $_FILES['branding_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $logoUploader = new ImageUploader(__DIR__ . '/../../uploads', null, ['image/jpeg', 'image/png'], 80, 80);
            $newLogoPath = $logoUploader->upload($_FILES['branding_logo']);
            $repo->set('branding_logo', $newLogoPath);
            if ($existingLogo && $existingLogo !== $newLogoPath) {
                $logoUploader->delete($existingLogo);
            }
            $existingLogo = $newLogoPath;
        }

        // Upload favikony (jpg/png, min 16x16)
        if (isset($_FILES['branding_favicon']) && $_FILES['branding_favicon']['error'] !== UPLOAD_ERR_NO_FILE) {
            $faviconUploader = new ImageUploader(__DIR__ . '/../../uploads', null, ['image/jpeg', 'image/png'], 16, 16);
            $newFaviconPath = $faviconUploader->upload($_FILES['branding_favicon']);
            $repo->set('branding_favicon', $newFaviconPath);
            if ($existingFavicon && $existingFavicon !== $newFaviconPath) {
                $faviconUploader->delete($existingFavicon);
            }
        }

        // Przełącznik: wyłącz przekierowanie dla botów społecznościowych
        $disableSocialRedirect = isset($_POST['disable_social_redirect']) ? 1 : 0;
        $repo->set('disable_social_redirect', $disableSocialRedirect);

        // Definicje pól niestandardowych dla linków
        $customFieldService = new CustomFieldService($this->pdo);
        $definitionsInput = [];
        $keys = (array)($_POST['custom_field_key'] ?? []);
        $labels = (array)($_POST['custom_field_label'] ?? []);
        $types = (array)($_POST['custom_field_type'] ?? []);
        $placeholders = (array)($_POST['custom_field_placeholder'] ?? []);
        $steps = (array)($_POST['custom_field_step'] ?? []);

        foreach ($keys as $i => $key) {
            $definitionsInput[] = [
                'key' => $key,
                'label' => $labels[$i] ?? '',
                'type' => $types[$i] ?? 'text',
                'placeholder' => $placeholders[$i] ?? '',
                'step' => $steps[$i] ?? '',
            ];
        }

        try {
            $customFieldService->saveDefinitions($definitionsInput);
        } catch (\Throwable $e) {
            error_log('Nie udało się zapisać pól niestandardowych: ' . $e->getMessage());
        }

        // Zmiana nazwy użytkownika
        $newUsername = trim((string)($_POST['new_username'] ?? ''));
        if ($newUsername !== '') {
            if (strlen($newUsername) < 3) {
                Utils::startSession();
                $_SESSION['username_error'] = 'Nazwa użytkownika musi mieć minimum 3 znaki';
            } else {
                $repo->set('admin_username', $newUsername);
                Utils::startSession();
                $_SESSION['username_success'] = 'Nazwa użytkownika została zmieniona pomyślnie';
            }
        }

        // Zmiana hasła administratora
        $newPassword = trim((string)($_POST['new_password'] ?? ''));
        $newPasswordConfirm = trim((string)($_POST['new_password_confirm'] ?? ''));

        if ($newPassword !== '') {
            if ($newPassword !== $newPasswordConfirm) {
                Utils::startSession();
                $_SESSION['password_error'] = 'Hasła nie są identyczne';
            } elseif ($error = $this->validatePasswordStrength($newPassword)) {
                Utils::startSession();
                $_SESSION['password_error'] = $error;
            } else {
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $repo->set('admin_password_hash', $newHash);
                Utils::startSession();
                // Usuń flagę wymuszającą zmianę hasła
                unset($_SESSION['must_change_password'], $_SESSION['password_warning']);
                $_SESSION['password_success'] = 'Hasło zostało zmienione pomyślnie';
            }
        }

        // === SMTP CONFIGURATION ===
        $repo->set('smtp_host', trim((string)($_POST['smtp_host'] ?? '')));
        $repo->set('smtp_port', max(1, (int)($_POST['smtp_port'] ?? 587)));
        $repo->set('smtp_username', trim((string)($_POST['smtp_username'] ?? '')));
        $repo->set('smtp_encryption', (string)($_POST['smtp_encryption'] ?? 'tls'));
        $repo->set('smtp_from_email', trim((string)($_POST['smtp_from_email'] ?? '')));
        $repo->set('smtp_from_name', trim((string)($_POST['smtp_from_name'] ?? 'RedirectCMS')));

        // SMTP Password - szyfruj tylko jeśli podano nowe
        $smtpPassword = trim((string)($_POST['smtp_password'] ?? ''));
        if ($smtpPassword !== '') {
            try {
                $emailService = new EmailService($this->pdo);
                $emailService->saveSmtpPassword($smtpPassword);
                $repo->set('smtp_enabled', true); // Włącz SMTP po zapisaniu hasła
            } catch (\Throwable $e) {
                // Loguj błąd ale nie przerywaj zapisu innych ustawień
                error_log('SMTP password encryption failed: ' . $e->getMessage());
            }
        }

        // === NOTIFICATION SETTINGS ===
        $repo->set('notification_enabled', isset($_POST['notification_enabled']) ? 1 : 0);
        $repo->set('notification_email', trim((string)($_POST['notification_email'] ?? '')));
        $repo->set('notification_frequency', (string)($_POST['notification_frequency'] ?? '1'));
        $repo->set('notification_custom_days', max(1, min(365, (int)($_POST['notification_custom_days'] ?? 7))));
        $repo->set('notification_time', (string)($_POST['notification_time'] ?? '09:00'));
        $repo->set('notification_top_links', max(1, min(50, (int)($_POST['notification_top_links'] ?? 10))));

        // Timezone - walidacja
        $timezoneInput = trim((string)($_POST['timezone'] ?? 'Europe/Warsaw'));
        if (in_array($timezoneInput, \DateTimeZone::listIdentifiers(), true)) {
            $repo->set('timezone', $timezoneInput);
        }

        // === DEBUG SETTINGS ===
        $repo->set('debug_enabled', isset($_POST['debug_enabled']) ? 1 : 0);
        $repo->set('debug_log_errors', isset($_POST['debug_log_errors']) ? 1 : 0);
        $repo->set('debug_display_errors', isset($_POST['debug_display_errors']) ? 1 : 0);

        // Walidacja poziomu błędów
        $allowedErrorLevels = ['E_ALL', 'E_ERROR', 'E_WARNING', 'E_ALL_NO_DEPRECATED', 'E_ALL_NO_NOTICE', 'E_ERROR_WARNING'];
        $errorLevel = (string)($_POST['debug_error_level'] ?? 'E_ALL');
        if (in_array($errorLevel, $allowedErrorLevels, true)) {
            $repo->set('debug_error_level', $errorLevel);
        }

        // Zapisz aktywną zakładkę do sesji
        $activeTab = trim((string)($_POST['active_tab'] ?? 'general'));
        $_SESSION['active_settings_tab'] = $activeTab;

        header('Location: ' . $this->basePath . '/admin/index.php?action=settings');
    }

    public function testSmtpPost(): void
    {
        Utils::requireLogin();

        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Błędny CSRF token']);
            return;
        }

        header('Content-Type: application/json');

        try {
            // Zapisz tymczasowo konfigurację do testu
            $repo = new SettingsRepository($this->pdo);
            $repo->set('smtp_host', trim((string)($_POST['smtp_host'] ?? '')));
            $repo->set('smtp_port', max(1, (int)($_POST['smtp_port'] ?? 587)));
            $repo->set('smtp_username', trim((string)($_POST['smtp_username'] ?? '')));
            $repo->set('smtp_encryption', (string)($_POST['smtp_encryption'] ?? 'tls'));
            $repo->set('smtp_enabled', true);

            // Hasło (jeśli podano)
            $password = trim((string)($_POST['smtp_password'] ?? ''));
            if ($password !== '') {
                $emailService = new EmailService($this->pdo);
                $emailService->saveSmtpPassword($password);
            }

            // Test połączenia
            $emailService = new EmailService($this->pdo);
            $result = $emailService->testConnection();

            echo json_encode($result);

        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Błąd: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Test wysyłania powiadomienia email z podsumowaniem statystyk
     */
    public function testNotificationPost(): void
    {
        Utils::requireLogin();

        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Błędny CSRF token']);
            return;
        }

        header('Content-Type: application/json');

        try {
            $email = trim((string)($_POST['notification_email'] ?? ''));
            $topLinks = max(1, min(50, (int)($_POST['notification_top_links'] ?? 10)));
            $frequency = (string)($_POST['notification_frequency'] ?? '1');
            $customDays = max(1, min(365, (int)($_POST['notification_custom_days'] ?? 7)));

            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Nieprawidłowy adres email'
                ]);
                return;
            }

            // Sprawdź czy SMTP jest skonfigurowane
            $repo = new SettingsRepository($this->pdo);
            $smtpEnabled = (bool)$repo->get('smtp_enabled', false);
            $smtpHost = (string)$repo->get('smtp_host', '');

            if (!$smtpEnabled || $smtpHost === '') {
                echo json_encode([
                    'success' => false,
                    'message' => 'SMTP nie jest skonfigurowane. Najpierw skonfiguruj i przetestuj połączenie SMTP.'
                ]);
                return;
            }

            // Oblicz liczbę dni dla statystyk
            $days = ($frequency === 'custom') ? $customDays : (int)$frequency;

            // Pobierz statystyki
            $statsRepo = new StatsRepository($this->pdo);
            $topPerforming = $statsRepo->getTopPerformingLinks($topLinks, $days);
            $globalStats = $statsRepo->getGlobalStats($days);

            if (empty($topPerforming)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Brak statystyk do wysłania. Utwórz linki i wygeneruj kilka kliknięć, aby zobaczyć podsumowanie.'
                ]);
                return;
            }

            // Wygeneruj email
            $notificationService = new NotificationService($this->pdo);
            $htmlBody = $notificationService->generateHtmlEmail($topPerforming, $globalStats, $days);

            // Wyślij
            $emailService = new EmailService($this->pdo);
            $subject = 'RedirectCMS - Testowe podsumowanie statystyk';
            $emailService->sendEmail($email, $subject, $htmlBody);

            echo json_encode([
                'success' => true,
                'message' => 'Testowe podsumowanie zostało wysłane na adres ' . htmlspecialchars($email)
            ]);

        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Błąd wysyłania: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * AJAX: Regenerate cropped image variants for all images.
     * Returns JSON with stats.
     */
    public function regenerateImagesPost(): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Błędny CSRF token']);
            return;
        }

        header('Content-Type: application/json');

        try {
            require_once __DIR__ . '/../ThemeManager.php';
            require_once __DIR__ . '/../ImageCropService.php';
            require_once __DIR__ . '/../SettingsRepository.php';

            $repo = new SettingsRepository($this->pdo);
            $blogTheme = (string)$repo->get('blog_theme', 'default');
            $themeManager = new ThemeManager(dirname(__DIR__, 2));
            $safeTheme = $themeManager->getSafeThemeName($blogTheme);
            $imageSizes = $themeManager->getImageSizes($safeTheme);

            if (empty($imageSizes)) {
                echo json_encode(['success' => true, 'message' => 'Aktywny szablon nie definiuje rozmiarów obrazków.', 'processed' => 0]);
                return;
            }

            $uploadsRoot    = dirname(__DIR__, 2) . '/uploads';
            $cropService    = new ImageCropService($uploadsRoot);
            $driver         = (string)$repo->get('image_optimization_driver', 'none');
            $defaults       = ImageCropService::defaultSettings();
            $saved          = $repo->get('image_optimization_settings', null);
            $driverSettings = is_array($saved)
                ? array_replace_recursive($defaults, $saved)
                : $defaults;

            $force = !empty($_POST['force']);
            $sizeFilter = [];
            if (!empty($_POST['size_keys']) && is_array($_POST['size_keys'])) {
                $allowedKeys = array_keys($imageSizes);
                $sizeFilter = array_values(array_filter(
                    (array)$_POST['size_keys'],
                    fn($k) => in_array($k, $allowedKeys, true)
                ));
            }

            $stats = $cropService->regenerateForLinks($this->pdo, $imageSizes, $driver, $driverSettings, $force, $sizeFilter);

            echo json_encode([
                'success'   => true,
                'message'   => sprintf(
                    'Gotowe. Przetworzono: %d, pominięto: %d, błędów: %d.',
                    $stats['processed'],
                    $stats['skipped'],
                    $stats['failed']
                ),
                'processed' => $stats['processed'],
                'skipped'   => $stats['skipped'],
                'failed'    => $stats['failed'],
            ]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Błąd: ' . $e->getMessage()]);
        }
    }

    public function appearanceGet(): void
    {
        Utils::requireLogin();
        $repo = new SettingsRepository($this->pdo);
        $themeManager = new ThemeManager(dirname(__DIR__, 2));

        $blogTheme = (string)$repo->get('blog_theme', 'default');
        $safeTheme = $themeManager->getSafeThemeName($blogTheme);

        $availableThemes  = $themeManager->getAvailableThemes();
        $themeColors      = $themeManager->getThemeColors($safeTheme);
        $savedThemeColorsRaw = $repo->get('theme_colors', []);
        $savedThemeColors = is_array($savedThemeColorsRaw) ? $savedThemeColorsRaw : (json_decode((string)$savedThemeColorsRaw, true) ?: []);
        $themeImageSizes  = $themeManager->getImageSizes($safeTheme);

        $contactPageEnabled = (bool)$repo->get('contact_page_enabled', false);
        $contactPageEmail   = (string)$repo->get('contact_page_email', '');
        $contactPageIntro   = (string)$repo->get('contact_page_intro', '');

        $socialLinksRaw = $repo->get('social_links', []);
        $socialLinks = is_array($socialLinksRaw) ? $socialLinksRaw : (json_decode((string)$socialLinksRaw, true) ?: []);

        $themeSuportsSidebar = $themeManager->supports($safeTheme, 'sidebar');
        $sidebarWidgetsRaw   = $repo->get('sidebar_widgets', []);
        $sidebarWidgets      = is_array($sidebarWidgetsRaw) ? $sidebarWidgetsRaw : (json_decode((string)$sidebarWidgetsRaw, true) ?: []);

        // Ustawienia bloga (przeniesione z zakładki Strona główna)
        $blogDescLength   = (int)$repo->get('blog_description_length', 200);
        $blogPostsPerPage = (int)$repo->get('blog_posts_per_page', 10);
        $blogShowImages   = ((int)$repo->get('blog_show_images', 1) === 1);
        $sliderCount      = (int)$repo->get('slider_count', 3);
        $sliderType       = (string)$repo->get('slider_type', 'newest');
        $sliderDays       = (int)$repo->get('slider_days', 0);

        $successMsg = $_SESSION['appearance_success'] ?? null;
        unset($_SESSION['appearance_success']);

        $this->view('appearance', [
            'csrf'               => Utils::csrfToken(),
            'blogTheme'          => $safeTheme,
            'availableThemes'    => $availableThemes,
            'themeColors'        => $themeColors,
            'savedThemeColors'   => $savedThemeColors,
            'themeImageSizes'    => $themeImageSizes,
            'contactPageEnabled' => $contactPageEnabled,
            'contactPageEmail'   => $contactPageEmail,
            'contactPageIntro'   => $contactPageIntro,
            'socialLinks'        => $socialLinks,
            'themeSuportsSidebar' => $themeSuportsSidebar,
            'sidebarWidgets'     => $sidebarWidgets,
            'blogDescLength'     => $blogDescLength,
            'blogPostsPerPage'   => $blogPostsPerPage,
            'blogShowImages'     => $blogShowImages,
            'sliderCount'        => $sliderCount,
            'sliderType'         => $sliderType,
            'sliderDays'         => $sliderDays,
            'successMsg'         => $successMsg,
        ]);
    }

    public function appearancePost(): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Błędny CSRF token.';
            return;
        }

        $repo = new SettingsRepository($this->pdo);
        $themeManager = new ThemeManager(dirname(__DIR__, 2));

        // Wybór szablonu
        $blogTheme = trim((string)($_POST['blog_theme'] ?? 'default'));
        $safeTheme = $themeManager->getSafeThemeName($blogTheme ?: 'default');
        $repo->set('blog_theme', $safeTheme);

        // Kolory szablonu
        $themeColors = $themeManager->getThemeColors($safeTheme);
        $savedColors = [];
        foreach ($themeColors as $color) {
            $key   = $color['key'];
            $value = trim((string)($_POST['theme_color_' . $key] ?? $color['default']));
            if (preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value)) {
                $savedColors[$key] = $value;
            } else {
                $savedColors[$key] = $color['default'];
            }
        }
        $repo->set('theme_colors', json_encode($savedColors));

        // Strona kontaktowa
        $repo->set('contact_page_enabled', isset($_POST['contact_page_enabled']) ? 1 : 0);
        $contactEmail = trim((string)($_POST['contact_page_email'] ?? ''));
        if ($contactEmail !== '' && !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            $contactEmail = '';
        }
        $repo->set('contact_page_email', $contactEmail);
        $repo->set('contact_page_intro', trim((string)($_POST['contact_page_intro'] ?? '')));

        // Profile społecznościowe
        $allowedSocial = ['facebook', 'instagram', 'twitter', 'youtube', 'tiktok', 'linkedin', 'pinterest', 'custom_url'];
        $socialLinks = [];
        foreach ($allowedSocial as $network) {
            $url = trim((string)($_POST['social_' . $network] ?? ''));
            if ($url !== '' && filter_var($url, FILTER_VALIDATE_URL)) {
                $socialLinks[$network] = $url;
            }
        }
        $repo->set('social_links', json_encode($socialLinks));

        // Sidebar widgets
        $sidebarWidgetsRaw = json_decode(trim((string)($_POST['sidebar_widgets_json'] ?? '[]')), true);
        if (!is_array($sidebarWidgetsRaw)) {
            $sidebarWidgetsRaw = [];
        }
        $allowedWidgetTypes = ['popular_posts', 'random_posts', 'tag_cloud', 'categories', 'social_links', 'custom_html'];
        $cleanSidebarWidgets = [];
        foreach ($sidebarWidgetsRaw as $w) {
            if (!isset($w['type']) || !in_array($w['type'], $allowedWidgetTypes, true)) {
                continue;
            }
            $clean = [
                'type'  => $w['type'],
                'title' => mb_substr(strip_tags((string)($w['title'] ?? '')), 0, 100),
            ];
            switch ($w['type']) {
                case 'popular_posts':
                    $clean['count'] = max(1, min(10, (int)($w['count'] ?? 5)));
                    $allowedPeriods = ['today', '7d', '30d', 'all'];
                    $clean['period'] = in_array($w['period'] ?? 'all', $allowedPeriods, true) ? $w['period'] : 'all';
                    $clean['category_id'] = max(0, (int)($w['category_id'] ?? 0));
                    break;
                case 'random_posts':
                    $clean['count'] = max(1, min(10, (int)($w['count'] ?? 5)));
                    break;
                case 'tag_cloud':
                    $clean['max_tags'] = max(5, min(100, (int)($w['max_tags'] ?? 30)));
                    break;
                case 'categories':
                    $clean['show_count'] = !empty($w['show_count']) ? 1 : 0;
                    break;
                case 'custom_html':
                    $clean['html'] = (string)($w['html'] ?? '');
                    break;
            }
            $cleanSidebarWidgets[] = $clean;
        }
        $repo->set('sidebar_widgets', json_encode($cleanSidebarWidgets));

        // Ustawienia bloga
        $blogDescLength = max(50, min(1000, (int)($_POST['blog_description_length'] ?? 200)));
        $blogPostsPerPage = max(1, min(50, (int)($_POST['blog_posts_per_page'] ?? 10)));
        $blogShowImages = isset($_POST['blog_show_images']) ? 1 : 0;
        $repo->set('blog_description_length', $blogDescLength);
        $repo->set('blog_posts_per_page', $blogPostsPerPage);
        $repo->set('blog_show_images', $blogShowImages);

        // Ustawienia slidera
        $sliderCount = max(1, min(5, (int)($_POST['slider_count'] ?? 3)));
        $sliderType  = in_array($_POST['slider_type'] ?? '', ['newest', 'random', 'popular'], true)
            ? (string)$_POST['slider_type'] : 'newest';
        $sliderDays  = max(0, min(365, (int)($_POST['slider_days'] ?? 0)));
        $repo->set('slider_count', $sliderCount);
        $repo->set('slider_type',  $sliderType);
        $repo->set('slider_days',  $sliderDays);

        $_SESSION['appearance_success'] = true;
        header('Location: ' . $this->basePath . '/admin/index.php?action=appearance');
    }

    // ── Image optimizer ──────────────────────────────────────────────────────

    public function imageOptimizerGet(): void
    {
        Utils::requireLogin();
        require_once __DIR__ . '/../ImageOptimizerDetector.php';
        require_once __DIR__ . '/../ImageCropService.php';
        require_once __DIR__ . '/../ThemeManager.php';
        $repo          = new SettingsRepository($this->pdo);
        $capabilities  = ImageOptimizerDetector::detect();
        $bestDriver    = ImageOptimizerDetector::getBest($capabilities);
        $currentDriver = (string)$repo->get('image_optimization_driver', 'none');

        $defaults       = ImageCropService::defaultSettings();
        $saved          = $repo->get('image_optimization_settings', null);
        $driverSettings = is_array($saved)
            ? array_replace_recursive($defaults, $saved)
            : $defaults;

        $themeManager = new ThemeManager(dirname(__DIR__, 2));
        $blogTheme    = (string)$repo->get('blog_theme', 'default');
        $safeTheme    = $themeManager->getSafeThemeName($blogTheme);
        $imageSizes   = $themeManager->getImageSizes($safeTheme);

        $this->view('image_optimizer', [
            'capabilities'  => $capabilities,
            'bestDriver'    => $bestDriver,
            'currentDriver' => $currentDriver,
            'driverSettings'=> $driverSettings,
            'imageSizes'    => $imageSizes,
            'csrf'          => Utils::csrfToken(),
            'success'       => $_SESSION['optimizer_success'] ?? null,
        ]);
        unset($_SESSION['optimizer_success']);
    }

    public function imageOptimizerPost(): void
    {
        Utils::requireLogin();
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Błędny CSRF token.';
            return;
        }

        require_once __DIR__ . '/../ImageOptimizerDetector.php';
        require_once __DIR__ . '/../ImageCropService.php';
        $capabilities   = ImageOptimizerDetector::detect();
        $allowedDrivers = array_keys(array_filter($capabilities, fn($c) => $c['available']));
        $driver = in_array($_POST['driver'] ?? '', $allowedDrivers, true)
            ? (string)$_POST['driver']
            : 'none';

        $repo     = new SettingsRepository($this->pdo);
        $defaults = ImageCropService::defaultSettings();

        $pngMin = max(0, min(100, (int)($_POST['exec_pngquant_min'] ?? $defaults['exec_tools']['pngquant_min'])));
        $pngMax = max(0, min(100, (int)($_POST['exec_pngquant_max'] ?? $defaults['exec_tools']['pngquant_max'])));
        if ($pngMin > $pngMax) {
            $pngMin = $pngMax;
        }

        $driverSettings = [
            'gd' => [
                'jpeg_quality'    => max(1, min(100, (int)($_POST['gd_jpeg_quality']    ?? $defaults['gd']['jpeg_quality']))),
                'png_compression' => max(0, min(9,   (int)($_POST['gd_png_compression'] ?? $defaults['gd']['png_compression']))),
            ],
            'imagick' => [
                'jpeg_quality' => max(1, min(100, (int)($_POST['imagick_jpeg_quality'] ?? $defaults['imagick']['jpeg_quality']))),
                'png_quality'  => max(1, min(100, (int)($_POST['imagick_png_quality']  ?? $defaults['imagick']['png_quality']))),
                'webp_quality' => max(1, min(100, (int)($_POST['imagick_webp_quality'] ?? $defaults['imagick']['webp_quality']))),
            ],
            'exec_tools' => [
                'jpegoptim_max' => max(1, min(100, (int)($_POST['exec_jpegoptim_max'] ?? $defaults['exec_tools']['jpegoptim_max']))),
                'pngquant_min'  => $pngMin,
                'pngquant_max'  => $pngMax,
                'optipng_level' => max(0, min(7,   (int)($_POST['exec_optipng_level'] ?? $defaults['exec_tools']['optipng_level']))),
            ],
        ];

        $repo->set('image_optimization_driver', $driver);
        $repo->set('image_optimization_settings', $driverSettings);

        $_SESSION['optimizer_success'] = 'Ustawienia optymalizacji zostały zapisane.';
        header('Location: ' . $this->basePath . '/admin/index.php?action=image_optimizer');
        exit;
    }

    private function validatePasswordStrength(string $password): ?string
    {
        if (strlen($password) < 12) {
            return 'Hasło musi mieć minimum 12 znaków';
        }
        if (!preg_match('/[a-z]/', $password)) {
            return 'Hasło musi zawierać małą literę';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return 'Hasło musi zawierać wielką literę';
        }
        if (!preg_match('/[0-9]/', $password)) {
            return 'Hasło musi zawierać cyfrę';
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            return 'Hasło musi zawierać znak specjalny';
        }
        return null; // Hasło OK
    }
}
