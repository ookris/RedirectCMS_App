<?php
declare(strict_types=1);

// Sprawdź czy aplikacja jest zainstalowana
$configPath = __DIR__ . '/../config/config.php';

if (!file_exists($configPath)) {
    // Przekieruj do instalatora
    header('Location: ../install/install.php');
    exit;
}

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/SettingsRepository.php';
require_once __DIR__ . '/../src/Utils.php';
require_once __DIR__ . '/../src/DebugConfig.php';
require_once __DIR__ . '/../src/LinkRepository.php';
require_once __DIR__ . '/../src/AffiliateProgramRepository.php';
require_once __DIR__ . '/../src/EventRepository.php';
require_once __DIR__ . '/../src/LicenseInstaller.php';
require_once __DIR__ . '/../src/LicenseChecker.php';
require_once __DIR__ . '/../src/AdminController.php';

// Ustaw nagłówki bezpieczeństwa
Utils::setSecurityHeaders();

// Załaduj konfigurację
$config = require $configPath;

// Startuj bezpieczną sesję
Utils::startSession();

try {
    $db = new Database($config);
    $pdo = $db->pdo();
} catch (Throwable $e) {
    http_response_code(500);
    // Tymczasowo wyświetl błąd połączenia
    if (($config['app']['env'] ?? 'production') === 'development') {
        echo 'Błąd połączenia z bazą danych: ' . htmlspecialchars($e->getMessage());
    } else {
        echo 'Błąd połączenia z bazą danych. Skontaktuj się z administratorem.';
        error_log('Database connection error: ' . $e->getMessage());
    }
    exit;
}

// Inicjalizuj konfigurację debugowania z ustawień w bazie danych
DebugConfig::init($pdo, __DIR__ . '/../storage/logs');

// Sprawdź i zainstaluj plik license*.php (jeśli istnieje w rootu aplikacji).
// Po udanej instalacji kasujemy cache weryfikacji — LicenseChecker odpyta API z nowym kluczem.
if (LicenseInstaller::runIfNeeded(__DIR__ . '/..')) {
    LicenseChecker::clearCache($pdo);
}

// Ustawienie ścieżki bazowej
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/admin/index.php';
$basePath = dirname(dirname($scriptName)); // np. /RedirectCMS
if ($basePath === '/' || $basePath === '.') {
    $basePath = '';
}

Utils::setBasePath($basePath);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Pobierz query string action
$action = $_GET['action'] ?? 'dashboard';

$admin = new AdminController($pdo, $config, $basePath);

switch ($action) {
    case 'update_slug':
        // AJAX endpoint do zmiany slugu
        Utils::requireLogin();
        header('Content-Type: application/json');
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
            exit;
        }
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Błędny CSRF token']);
            exit;
        }
        $linkId = (int)($_POST['id'] ?? 0);
        $newSlug = Utils::sanitizeSlug(trim((string)($_POST['slug'] ?? '')));
        if ($linkId <= 0 || $newSlug === '') {
            echo json_encode(['success' => false, 'error' => 'Nieprawidłowe dane']);
            exit;
        }
        $linkRepo = new LinkRepository($pdo);
        $link = $linkRepo->getById($linkId);
        if (!$link) {
            echo json_encode(['success' => false, 'error' => 'Link nie istnieje']);
            exit;
        }
        if ($newSlug !== $link['slug'] && $linkRepo->slugExists($newSlug)) {
            echo json_encode(['success' => false, 'error' => 'Slug "' . $newSlug . '" już istnieje']);
            exit;
        }
        $linkRepo->update($linkId, ['slug' => $newSlug, 'target_url' => $link['target_url'], 'delay_seconds' => $link['delay_seconds']]);
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host = htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost', ENT_QUOTES, 'UTF-8');
        echo json_encode([
            'success' => true,
            'slug' => $newSlug,
            'redirect_url' => $scheme . '://' . $host . $basePath . '/' . $newSlug,
            'blog_url' => $scheme . '://' . $host . $basePath . '/blog/' . $newSlug,
        ]);
        exit;

    case 'qr_code':
        // AJAX endpoint do generowania i pobierania QR codes
        Utils::requireLogin();
        $linkId = (int)($_GET['id'] ?? 0);
        if ($linkId <= 0) {
            http_response_code(400);
            echo 'Nieprawidłowe ID linku';
            exit;
        }
        $mode = (string)($_GET['mode'] ?? 'display'); // display, download
        $admin->generateQRCode($linkId, $mode);
        exit;
    
    case 'search_tags':
        // AJAX endpoint do wyszukiwania tagów
        Utils::requireLogin();
        header('Content-Type: application/json');
        $query = trim((string)($_GET['q'] ?? ''));
        if ($query === '') {
            echo json_encode([]);
            exit;
        }
        $tagRepo = new TagRepository($pdo);
        $tags = $tagRepo->searchTags($query, 10);
        echo json_encode($tags);
        exit;

    case 'reveal_license_key':
        Utils::requireLogin();
        header('Content-Type: application/json');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');

        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
            exit;
        }

        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Błędny CSRF token']);
            exit;
        }

        $now = time();
        $lastReveal = isset($_SESSION['license_reveal_last_at']) ? (int)$_SESSION['license_reveal_last_at'] : 0;
        if ($lastReveal > 0 && ($now - $lastReveal) < 2) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Zbyt wiele prób. Spróbuj ponownie za chwilę.']);
            exit;
        }
        $_SESSION['license_reveal_last_at'] = $now;

        $licenseKey = '';

        if (!defined('RCMS_LICENSE_KEY')) {
            $licenseFile = __DIR__ . '/../config/license.php';
            if (is_file($licenseFile)) {
                require_once $licenseFile;
            }
        }

        $fileKey = defined('RCMS_LICENSE_KEY') ? RCMS_LICENSE_KEY : '';
        if (is_string($fileKey) && preg_match('/^[a-f0-9]{64}$/', $fileKey) === 1) {
            $licenseKey = $fileKey;
        }

        if ($licenseKey === '') {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Brak poprawnego klucza licencyjnego']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'license_key' => $licenseKey,
        ]);
        exit;

    case 'upload_image_ajax':
        if ($method === 'POST') {
            $admin->uploadImageAjax();
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
        }
        exit;

    case 'delete_uploaded_image':
        if ($method === 'POST') {
            $admin->deleteUploadedImageAjax();
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
        }
        exit;
    
    case 'login':
        if ($method === 'POST') {
            $admin->loginPost();
        } else {
            $admin->loginGet();
        }
        break;
    
    case 'logout':
        $admin->logout();
        break;

    case 'two_factor_setup':
        $admin->twoFactorSetupGet();
        break;

    case 'two_factor_enable':
        if ($method === 'POST') { $admin->twoFactorEnablePost(); } else { header('Location: index.php?action=two_factor_setup'); }
        break;

    case 'two_factor_disable':
        if ($method === 'POST') { $admin->twoFactorDisablePost(); } else { header('Location: index.php?action=two_factor_setup'); }
        break;

    case 'two_factor_regenerate_backup':
        if ($method === 'POST') { $admin->twoFactorRegenerateBackupPost(); } else { header('Location: index.php?action=two_factor_setup'); }
        break;

    case 'two_factor_verify':
        if ($method === 'POST') { $admin->twoFactorVerifyPost(); } else { $admin->twoFactorVerifyGet(); }
        break;

    case 'backup':
        $admin->backupGet();
        break;
    case 'backup_create':
        if ($method === 'POST') { $admin->backupCreatePost(); } else { header('Location: index.php?action=backup'); }
        break;
    case 'backup_download':
        if ($method === 'POST') {
            $admin->backupDownload();
        } else {
            http_response_code(405);
            echo 'Method Not Allowed';
        }
        break;
    case 'backup_restore':
        if ($method === 'POST') { $admin->backupRestorePost(); } else { header('Location: index.php?action=backup'); }
        break;
    case 'backup_restore_upload':
        if ($method === 'POST') { $admin->backupRestoreUploadPost(); } else { header('Location: index.php?action=backup'); }
        break;
    case 'backup_delete':
        if ($method === 'POST') { $admin->backupDeletePost(); } else { header('Location: index.php?action=backup'); }
        break;
    case 'backup_auto_settings':
        if ($method === 'POST') { $admin->backupAutoSettingsPost(); } else { header('Location: index.php?action=backup'); }
        break;

    case 'settings':
        if ($method === 'POST') {
            $admin->settingsPost();
        } else {
            $admin->settingsGet();
        }
        break;

    case 'appearance':
        if ($method === 'POST') {
            $admin->appearancePost();
        } else {
            $admin->appearanceGet();
        }
        break;

    case 'image_optimizer':
        if ($method === 'POST') {
            $admin->imageOptimizerPost();
        } else {
            $admin->imageOptimizerGet();
        }
        break;

    case 'campaigns':
        $admin->campaignsGet();
        break;
    case 'campaign_new':
        if ($method === 'POST') { $admin->campaignNewPost(); } else { $admin->campaignNewGet(); }
        break;
    case 'campaign_edit':
        $id = (int)($_GET['id'] ?? 0);
        if ($method === 'POST') { $admin->campaignEditPost($id); } else { $admin->campaignEditGet($id); }
        break;
    case 'campaign_detail':
        $admin->campaignDetailGet((int)($_GET['id'] ?? 0));
        break;
    case 'campaign_delete':
        if ($method === 'POST') { $admin->campaignDeletePost((int)($_GET['id'] ?? 0)); } else { header('Location: index.php?action=campaigns'); }
        break;

    case 'pages':
        $admin->pagesGet();
        break;
    case 'page_new':
        if ($method === 'POST') { $admin->pageNewPost(); } else { $admin->pageNewGet(); }
        break;
    case 'page_edit':
        $id = (int)($_GET['id'] ?? 0);
        if ($method === 'POST') { $admin->pageEditPost($id); } else { $admin->pageEditGet($id); }
        break;
    case 'page_delete':
        if ($method === 'POST') { $admin->pageDeletePost((int)($_GET['id'] ?? 0)); } else { header('Location: index.php?action=pages'); }
        break;

    case 'testSmtp':
        $admin->testSmtpPost();
        break;

    case 'testNotification':
        $admin->testNotificationPost();
        break;

    case 'regenerateImages':
        $admin->regenerateImagesPost();
        break;

    case 'new':
        if ($method === 'POST') {
            $admin->linkNewPost();
        } else {
            $admin->linkNewGet();
        }
        break;
    
    case 'edit':
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'Nieprawidłowe ID';
            exit;
        }
        if ($method === 'POST') {
            $admin->linkEditPost($id);
        } else {
            $admin->linkEditGet($id);
        }
        break;
    
    case 'delete':
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'Nieprawidlowe ID';
            exit;
        }
        if ($method === 'POST') {
            $admin->linkDeletePost($id);
        } else {
            http_response_code(405);
            echo 'Method Not Allowed';
        }
        break;

    case 'restore':
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'Nieprawidlowe ID';
            exit;
        }
        if ($method === 'POST') {
            $admin->linkRestorePost($id);
        } else {
            http_response_code(405);
            echo 'Method Not Allowed';
        }
        break;

    case 'permanent_delete':
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'Nieprawidlowe ID';
            exit;
        }
        if ($method === 'POST') {
            $admin->linkPermanentDeletePost($id);
        } else {
            http_response_code(405);
            echo 'Method Not Allowed';
        }
        break;

    case 'empty_trash':
        if ($method === 'POST') {
            $admin->emptyTrashPost();
        } else {
            http_response_code(405);
            echo 'Method Not Allowed';
        }
        break;

    case 'duplicate':
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'Nieprawidłowe ID';
            exit;
        }
        if ($method === 'POST') {
            $admin->linkDuplicatePost($id);
        } else {
            http_response_code(405);
            echo 'Method Not Allowed';
        }
        break;
    
    case 'stats':
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'Nieprawidłowe ID';
            exit;
        }
        $admin->stats($id);
        break;
    case 'export_stats':
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'Nieprawidłowe ID';
            exit;
        }
        $admin->exportStats($id);
        break;
    
    case 'geo_cache':
        $admin->geoCacheGet();
        break;
    
    case 'geo_cache_cleanup':
        if ($method === 'POST') {
            $admin->geoCacheCleanup();
        } else {
            http_response_code(405);
            echo 'Method Not Allowed';
        }
        break;
    
    case 'global_stats':
        $admin->globalStats();
        break;
    
    case 'refresh_global_cache':
        if ($method === 'POST') {
            $admin->refreshGlobalStatsCache();
        } else {
            http_response_code(405);
            echo 'Method Not Allowed';
        }
        break;
    
    case 'categories':
        $admin->categoriesGet();
        break;
    
    case 'category_new':
        if ($method === 'POST') {
            $admin->categoryNewPost();
        } else {
            $admin->categoryNewGet();
        }
        break;
    
    case 'category_edit':
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'Nieprawidłowe ID';
            exit;
        }
        if ($method === 'POST') {
            $admin->categoryEditPost($id);
        } else {
            $admin->categoryEditGet($id);
        }
        break;
    
    case 'category_delete':
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'Nieprawidłowe ID';
            exit;
        }
        if ($method === 'POST') {
            $admin->categoryDeletePost($id);
        } else {
            http_response_code(405);
            echo 'Method Not Allowed';
        }
        break;
    
    case 'tags':
        $admin->tagsGet();
        break;
    
    case 'tag_new':
        if ($method === 'POST') {
            $admin->tagNewPost();
        } else {
            $admin->tagNewGet();
        }
        break;
    
    case 'tag_edit':
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'Nieprawidłowe ID';
            exit;
        }
        if ($method === 'POST') {
            $admin->tagEditPost($id);
        } else {
            $admin->tagEditGet($id);
        }
        break;
    
    case 'tag_delete':
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'Nieprawidłowe ID';
            exit;
        }
        if ($method === 'POST') {
            $admin->tagDeletePost($id);
        } else {
            http_response_code(405);
            echo 'Method Not Allowed';
        }
        break;

    case 'affiliate_programs':
        $admin->affiliateProgramsGet();
        break;

    case 'affiliate_program_new':
        if ($method === 'POST') {
            $admin->affiliateProgramNewPost();
        } else {
            $admin->affiliateProgramNewGet();
        }
        break;

    case 'affiliate_program_edit':
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'Nieprawidłowe ID';
            exit;
        }
        if ($method === 'POST') {
            $admin->affiliateProgramEditPost($id);
        } else {
            $admin->affiliateProgramEditGet($id);
        }
        break;

    case 'affiliate_program_delete':
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'Nieprawidłowe ID';
            exit;
        }
        if ($method === 'POST') {
            $admin->affiliateProgramDeletePost($id);
        } else {
            http_response_code(405);
            echo 'Method Not Allowed';
        }
        break;
    
    case 'cron_jobs':
        $admin->cronJobsGet();
        break;
    
    case 'cron_register_defaults':
        $admin->cronRegisterDefaults();
        break;
    
    case 'cron_toggle':
        if ($method === 'POST') {
            $admin->cronToggle();
        } else {
            http_response_code(405);
            echo 'Method Not Allowed';
        }
        break;
    
    case 'cron_run_now':
        if ($method === 'POST') {
            $admin->cronRunNow();
        } else {
            http_response_code(405);
            echo 'Method Not Allowed';
        }
        break;
    
    case 'cron_update_interval':
        if ($method === 'POST') {
            $admin->cronUpdateInterval();
        } else {
            http_response_code(405);
            echo 'Method Not Allowed';
        }
        break;

    case 'cron_delete':
        if ($method === 'POST') {
            $admin->cronDelete();
        } else {
            http_response_code(405);
            echo 'Method Not Allowed';
        }
        break;
    
    case 'cron_logs':
        $admin->cronLogs();
        break;

    case 'cron_generate_token':
        if ($method === 'POST') {
            $admin->cronGenerateToken();
        } else {
            http_response_code(405);
            echo 'Method Not Allowed';
        }
        break;

    case 'cron_pseudo_toggle':
        if ($method === 'POST') {
            $admin->cronPseudoCronPost();
        } else {
            http_response_code(405);
            echo 'Method Not Allowed';
        }
        break;

    case 'logs':
        $admin->logsGet();
        break;

    case 'resources':
        $admin->resourcesGet();
        break;

    case 'recalculate_storage':
        $admin->recalculateStoragePost();
        break;

    case 'audit_logs':
        $admin->auditLogsGet();
        break;

    case 'broken_links':
        $admin->brokenLinksGet();
        break;

    case 'broken_link_action':
        $admin->brokenLinkAction();
        break;

    case 'broken_links_run_check':
        if ($method === 'POST') {
            $admin->brokenLinksRunCheck();
        } else {
            http_response_code(405);
            echo 'Method Not Allowed';
        }
        break;

    case 'file_manager':
        $admin->fileManagerGet();
        break;

    case 'file_manager_delete':
        if ($method === 'POST') {
            $admin->fileManagerDeletePost();
        }
        break;

    case 'file_details':
        $admin->fileDetailsGet();
        break;

    case 'notifications':
        $admin->notificationsGet();
        break;

    case 'notification_mark_read':
        if ($method === 'POST') {
            $admin->notificationMarkRead();
        } else {
            http_response_code(405);
            echo 'Method Not Allowed';
        }
        break;

    case 'links':
        $admin->links();
        break;

    case 'export_links':
        $admin->exportLinks();
        break;

    case 'import_links':
        if ($method === 'POST') {
            $admin->importLinksPost();
        } else {
            $admin->importLinksGet();
        }
        break;

    case 'bulk_action':
        if ($method === 'POST') {
            $admin->bulkAction();
        } else {
            http_response_code(405);
            echo 'Method Not Allowed';
        }
        break;

    case 'dashboard':
    default:
        $admin->dashboard();
        break;
}
