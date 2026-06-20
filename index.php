<?php
declare(strict_types=1);

// Sprawdź czy aplikacja jest zainstalowana
$configPath = __DIR__ . '/config/config.php';

if (!file_exists($configPath)) {
    // Przekieruj do instalatora
    header('Location: install/install.php');
    exit;
}

require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/SettingsRepository.php';
require_once __DIR__ . '/src/Utils.php';
require_once __DIR__ . '/src/DebugConfig.php';
require_once __DIR__ . '/src/LinkRepository.php';
require_once __DIR__ . '/src/EventRepository.php';
require_once __DIR__ . '/src/StatsRepository.php';
require_once __DIR__ . '/src/ThemeManager.php';
require_once __DIR__ . '/src/CategoryRepository.php';
require_once __DIR__ . '/src/TagRepository.php';
require_once __DIR__ . '/src/PseudoCron.php';
require_once __DIR__ . '/src/CronTasks.php';
require_once __DIR__ . '/src/LicenseInstaller.php';
require_once __DIR__ . '/src/LicenseChecker.php';

// Ustaw nagłówki bezpieczeństwa
Utils::setSecurityHeaders();

// Załaduj konfigurację
$configPath = __DIR__ . '/config/config.php';
$config = require $configPath;

try {
    $db = new Database($config);
    $pdo = $db->pdo();
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Błąd połączenia z bazą danych: ' . htmlspecialchars($e->getMessage());
    exit;
}

// Inicjalizuj konfigurację debugowania z ustawień w bazie danych
DebugConfig::init($pdo, __DIR__ . '/storage/logs');

// Sprawdź i zainstaluj plik license*.php (jeśli istnieje w rootu).
// Wywołanie po nawiązaniu połączenia DB — po udanej instalacji kasujemy cache
// weryfikacji licencji, żeby LicenseChecker od razu odpytał API z nowym kluczem.
if (LicenseInstaller::runIfNeeded(__DIR__)) {
    LicenseChecker::clearCache($pdo);
}

// === PSEUDO-CRON: Uruchom zaplanowane zadania w tle ===
// Wykonuje się przy każdym wejściu na stronę, ale nie blokuje użytkownika.
// Można wyłączyć w panelu Cron gdy używany jest zewnętrzny crontab.
try {
    $stmt = $pdo->prepare("SELECT value FROM " . Database::table('settings') . " WHERE `key` = 'pseudo_cron_enabled'");
    $stmt->execute();
    $row = $stmt->fetch();
    if ($row === false || $row['value'] !== '0') {
        $pseudoCron = new PseudoCron($pdo);
        $pseudoCron->tick();
    }
} catch (Throwable $e) {
    // Ciche niepowodzenie - nie przerywaj działania strony
    error_log('PseudoCron error: ' . $e->getMessage());
}

$settingsRepo = new SettingsRepository($pdo);
$brandingLogo = (string)$settingsRepo->get('branding_logo', '');
$brandingFavicon = (string)$settingsRepo->get('branding_favicon', '');

// Token dla beaconu eventów (autogenerowany przy pierwszym uruchomieniu)
$eventToken = (string)$settingsRepo->get('event_token', '');
if ($eventToken === '') {
    try {
        $eventToken = bin2hex(random_bytes(32));
    } catch (Throwable $e) {
        try {
            $fallbackBytes = openssl_random_pseudo_bytes(32, $strong);
            if ($fallbackBytes !== false && $strong === true) {
                $eventToken = bin2hex($fallbackBytes);
            } else {
                $eventToken = '';
                error_log('Brak bezpiecznego RNG do wygenerowania event_token — endpoint /__event pozostaje wyłączony');
            }
        } catch (Throwable $e2) {
            $eventToken = '';
            error_log('Brak RNG do wygenerowania event_token — endpoint /__event pozostaje wyłączony');
        }
    }

    try {
        $settingsRepo->set('event_token', $eventToken);
    } catch (Throwable $e) {
        error_log('Nie udało się zapisać event_token: ' . $e->getMessage());
    }
}

// Ustawienie ścieżki bazowej
$basePath = dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php');
if ($basePath === '/' || $basePath === '.') {
    $basePath = '';
}

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
if ($basePath && strpos($requestUri, $basePath) === 0) {
    $uri = substr($requestUri, strlen($basePath)) ?: '/';
} else {
    $uri = $requestUri;
}

// Ustawienie ścieżki bazowej dla aplikacji
Utils::setBasePath($basePath);

// Oddziel query string od ścieżki
$uriPath = strtok($uri, '?');
if ($uriPath === false) {
    $uriPath = $uri;
}

// Przyjazne adresy URL — przepisz ścieżki na parametry query string
$prettyUrls = (bool)$settingsRepo->get('pretty_urls', false);
if ($prettyUrls) {
    if (preg_match('#^/category/([^/]+)/?$#', $uriPath, $m)) {
        $_GET['category'] = $m[1];
        $uriPath = '/';
    } elseif (preg_match('#^/tag/([^/]+)/?$#', $uriPath, $m)) {
        $_GET['tag'] = $m[1];
        $uriPath = '/';
    } elseif (preg_match('#^/page/([^/]+)/?$#', $uriPath, $m)) {
        $_GET['page'] = $m[1];
    } elseif ($uriPath === '/contact') {
        if ((bool)$settingsRepo->get('contact_page_enabled', false)) {
            $_GET['page'] = 'contact';
        }
    }
}

// Tryb strony głównej i podstawowe URL-e
$homeMode = (string)$settingsRepo->get('home_mode', 'landing');
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseUrl = $scheme . '://' . $host . ($basePath ? $basePath : '');

// W trybie blog promuj RSS i sitemap nagłówkiem Link
if ($homeMode === 'blog' && ($uriPath === '/' || str_starts_with($uriPath, '/blog/'))) {
    header('Link: <' . $baseUrl . '/rss.xml>; rel="alternate"; type="application/rss+xml"', false);
    header('Link: <' . $baseUrl . '/sitemap.xml>; rel="sitemap"', false);
}

// RSS feed (tylko w trybie blog)
if ($uriPath === '/rss.xml' || $uriPath === '/feed.xml') {
    if ($homeMode !== 'blog') {
        http_response_code(404);
        echo 'RSS dostępny tylko w trybie blog.';
        exit;
    }

    $blogDescLength = (int)$settingsRepo->get('blog_description_length', 200);
    $blogRssLimit = (int)$settingsRepo->get('blog_rss_limit', 50);
    $blogRssLimit = max(1, min($blogRssLimit, 200));
    $homeTitle = (string)$settingsRepo->get('home_title', 'RedirectCMS');
    $homeMetaDescription = (string)$settingsRepo->get('home_description', '');
    $channelDescription = $homeMetaDescription !== '' ? $homeMetaDescription : $homeTitle;

    $linkRepo = new LinkRepository($pdo);
    $posts = $linkRepo->getActiveBlogPosts($blogRssLimit, 0);

    header('Content-Type: application/rss+xml; charset=utf-8');
    header('Cache-Control: public, max-age=900');

    echo '<?xml version="1.0" encoding="UTF-8"?>';
    ?>
<rss version="2.0">
  <channel>
    <title><?= htmlspecialchars($homeTitle, ENT_XML1); ?></title>
    <link><?= htmlspecialchars($baseUrl . '/', ENT_XML1); ?></link>
    <description><?= htmlspecialchars($channelDescription, ENT_XML1); ?></description>
    <lastBuildDate><?= gmdate('r'); ?></lastBuildDate>
    <language>pl</language>
<?php foreach ($posts as $post):
    $postUrl = $baseUrl . '/blog/' . $post['slug'];
    $pubDateRaw = $post['publish_at'] ?: $post['created_at'];
    $pubDate = $pubDateRaw ? gmdate('r', strtotime((string)$pubDateRaw)) : gmdate('r');
    $title = !empty($post['page_title']) ? $post['page_title'] : $post['slug'];
    $description = trim(strip_tags((string)($post['page_description'] ?? '')));
    if ($description === '' && !empty($post['target_url'])) {
        $description = 'URL docelowy: ' . $post['target_url'];
    }
    if (mb_strlen($description) > $blogDescLength) {
        $description = mb_substr($description, 0, $blogDescLength) . '...';
    }
?>
    <item>
      <title><?= htmlspecialchars($title, ENT_XML1); ?></title>
      <link><?= htmlspecialchars($postUrl, ENT_XML1); ?></link>
      <guid isPermaLink="true"><?= htmlspecialchars($postUrl, ENT_XML1); ?></guid>
      <pubDate><?= $pubDate; ?></pubDate>
      <?php if ($description !== ''): ?>
      <description><?= htmlspecialchars($description, ENT_XML1); ?></description>
      <?php endif; ?>
    </item>
<?php endforeach; ?>
  </channel>
</rss>
<?php
    exit;
}

// Sitemap (tylko w trybie blog)
if ($uriPath === '/sitemap.xml') {
    if ($homeMode !== 'blog') {
        http_response_code(404);
        echo 'Mapa strony dostępna tylko w trybie blog.';
        exit;
    }

    $linkRepo = new LinkRepository($pdo);
    $posts = $linkRepo->getActiveBlogPosts(5000, 0);

    header('Content-Type: application/xml; charset=utf-8');
    header('Cache-Control: public, max-age=900');

    echo '<?xml version="1.0" encoding="UTF-8"?>';
    ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc><?= htmlspecialchars($baseUrl . '/', ENT_XML1); ?></loc>
    <lastmod><?= gmdate('Y-m-d'); ?></lastmod>
    <changefreq>daily</changefreq>
  </url>
<?php foreach ($posts as $post):
    $postUrl = $baseUrl . '/blog/' . $post['slug'];
    $lastmodRaw = $post['publish_at'] ?: $post['created_at'];
    $lastmod = $lastmodRaw ? gmdate('Y-m-d', strtotime((string)$lastmodRaw)) : gmdate('Y-m-d');
?>
  <url>
    <loc><?= htmlspecialchars($postUrl, ENT_XML1); ?></loc>
    <lastmod><?= $lastmod; ?></lastmod>
    <changefreq>weekly</changefreq>
  </url>
<?php endforeach; ?>
</urlset>
<?php
    exit;
}

// Event endpoint (beacon)
if ($uriPath === '/__event') {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        header('Allow: POST', true, 405);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }

    $contentType = $_SERVER['CONTENT_TYPE'] ?? ($_SERVER['HTTP_CONTENT_TYPE'] ?? '');
    if ($contentType !== '' && stripos($contentType, 'application/json') !== 0) {
        header('Content-Type: application/json');
        http_response_code(415);
        echo json_encode(['error' => 'Unsupported media type']);
        exit;
    }

    $maxBodyBytes = 65536; // 64 KB safeguard to avoid unbounded reads
    $contentLength = isset($_SERVER['CONTENT_LENGTH']) ? (int)$_SERVER['CONTENT_LENGTH'] : null;
    if ($contentLength !== null && $contentLength > $maxBodyBytes) {
        header('Content-Type: application/json');
        http_response_code(413);
        echo json_encode(['error' => 'Payload too large']);
        exit;
    }

    $raw = file_get_contents('php://input', false, null, 0, $maxBodyBytes + 1);
    if ($raw === false) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['error' => 'Invalid payload']);
        exit;
    }
    if (strlen($raw) > $maxBodyBytes) {
        header('Content-Type: application/json');
        http_response_code(413);
        echo json_encode(['error' => 'Payload too large']);
        exit;
    }

    $json = json_decode($raw ?: '', true);

    $payloadToken = is_array($json) ? (string)($json['token'] ?? '') : '';
    if ($payloadToken === '' || !hash_equals($eventToken, $payloadToken)) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['error' => 'Invalid token']);
        exit;
    }

    if (!is_array($json) || !isset($json['slug'], $json['type'])) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['error' => 'Invalid payload']);
        exit;
    }

    $slug = (string)$json['slug'];
    if ($slug === '' || strlen($slug) > 191) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['error' => 'Invalid slug']);
        exit;
    }

    $type = (string)$json['type'];
    $eventType = ($type === 'redirect') ? 'redirect' : ($type === 'click' ? 'click' : null);
    if ($eventType === null) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['error' => 'Invalid type']);
        exit;
    }

    $link = (new LinkRepository($pdo))->getBySlug($slug);
    if ($link) {
        (new EventRepository($pdo))->log(
            (int)$link['id'],
            $eventType,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            $_SERVER['HTTP_REFERER'] ?? null,
        );
    }

    header('Content-Type: application/json');
    echo json_encode(['ok' => true]);
    exit;
}

// ── Reaction endpoint ──────────────────────────────────────────────────────
if ($uriPath === '/__react' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    // Content-Type guard
    $ct = $_SERVER['HTTP_CONTENT_TYPE'] ?? $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($ct, 'application/json') !== 0) {
        http_response_code(415);
        echo json_encode(['ok' => false, 'error' => 'unsupported_media_type']);
        exit;
    }

    // Body size guard (max 4 KB) — sprawdź zarówno nagłówek, jak i faktyczny odczyt
    $contentLength = isset($_SERVER['CONTENT_LENGTH']) ? (int)$_SERVER['CONTENT_LENGTH'] : null;
    if ($contentLength !== null && $contentLength > 4096) {
        http_response_code(413);
        echo json_encode(['ok' => false, 'error' => 'payload_too_large']);
        exit;
    }

    $raw = (string)file_get_contents('php://input', false, null, 0, 4097);
    if (strlen($raw) > 4096) {
        http_response_code(413);
        echo json_encode(['ok' => false, 'error' => 'payload_too_large']);
        exit;
    }

    $json = json_decode($raw, true);

    if (!is_array($json)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'invalid_json']);
        exit;
    }

    $linkId       = isset($json['link_id'])      ? (int)$json['link_id']          : 0;
    $reactionType = isset($json['reaction_type']) ? trim((string)$json['reaction_type']) : '';

    if ($linkId <= 0 || $reactionType === '') {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'missing_params']);
        exit;
    }

    require_once __DIR__ . '/src/ReactionRepository.php';
    $reactionRepo = new ReactionRepository($pdo);

    // Weryfikuj, że link istnieje
    $checkStmt = $pdo->prepare('SELECT id FROM ' . Database::table('links') . ' WHERE id = :id LIMIT 1');
    $checkStmt->execute([':id' => $linkId]);
    if (!$checkStmt->fetch()) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'link_not_found']);
        exit;
    }

    // Hash IP z solą (event_token jako sól)
    $ip     = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $ipHash = hash('sha256', $ip . '|react|' . $eventToken);

    try {
        $result = $reactionRepo->vote($linkId, $reactionType, $ipHash);
        echo json_encode($result);
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'db_error']);
    }
    exit;
}
if ($uriPath === '/__react') {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
    exit;
}

// Cron runner endpoint (HTTP)
if ($uriPath === '/cron') {
    header('Content-Type: text/plain; charset=utf-8');

    Utils::appendLog(__DIR__ . '/storage/logs/cron.log', '[' . date('Y-m-d H:i:s') . '] HTTP cron endpoint called from ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));

    // Weryfikacja tokena
    $cronToken = (string)$settingsRepo->get('cron_token', '');
    $providedToken = $_GET['token'] ?? $_SERVER['HTTP_X_CRON_TOKEN'] ?? '';

    if ($cronToken === '') {
        http_response_code(503);
        echo "ERROR: Token cron nie jest skonfigurowany.\n";
        exit;
    }

    if (!hash_equals($cronToken, $providedToken)) {
        http_response_code(403);
        echo "ERROR: Nieprawidlowy token.\n";
        exit;
    }

    // Zapisz timestamp wywołania cron runnera
    $settingsRepo->set('cron_last_call', date('Y-m-d H:i:s'));

    // Uruchom zadania
    $forceRun = isset($_GET['force']) && $_GET['force'] === '1';
    $specificJob = isset($_GET['job']) ? (string)$_GET['job'] : null;

    $logFile = __DIR__ . '/storage/logs/cron.log';
    $output = "[" . date('Y-m-d H:i:s') . "] === Cron Runner Start ===\n";

    try {
        $pseudoCron = new PseudoCron($pdo);

        if ($specificJob !== null) {
            $result = $pseudoCron->runJobByName($specificJob, $forceRun);
            if ($result === true) {
                $output .= "SUCCESS: {$specificJob}\n";
            } elseif ($result === false) {
                $output .= "LOCKED: {$specificJob}\n";
            } else {
                $output .= "INFO: {$result}\n";
            }
        } else {
            $results = $pseudoCron->tickAll($forceRun);
            if (empty($results)) {
                $output .= "INFO: Brak zadań do wykonania\n";
            } else {
                foreach ($results as $jobName => $status) {
                    $output .= "{$status}: {$jobName}\n";
                }
            }
        }

        $output .= "[" . date('Y-m-d H:i:s') . "] === Cron Runner End ===\n";
    } catch (Throwable $e) {
        $output .= "ERROR: " . $e->getMessage() . "\n";
    }

    echo $output;
    Utils::appendLog($logFile, trim($output));
    exit;
}

// Blog post view (when in blog mode)
if (preg_match('#^/blog/(.+)$#', $uriPath, $matches)) {
    $slug = $matches[1];
    $linkRepo = new LinkRepository($pdo);
    // Pobierz link włącznie z niepublikowanymi, aby pokazać odpowiedni komunikat błędu
    $link = $linkRepo->getBySlugWithRelations($slug, true);

    if (!$link) {
        http_response_code(404);
        include __DIR__ . '/views/404.php';
        exit;
    }

    $now = new \DateTimeImmutable('now');
    $publishAt = !empty($link['publish_at']) ? new \DateTimeImmutable((string)$link['publish_at']) : null;
    $expiresAt = !empty($link['expires_at']) ? new \DateTimeImmutable((string)$link['expires_at']) : null;
    $linkStatus = $link['status'] ?? 'published';

    // Sprawdź status linku i pokaż odpowiedni komunikat błędu
    if ($linkStatus === 'draft') {
        http_response_code(404);
        $errorType = 'draft';
        $errorBadge = 'Szkic';
        $errorTitle = 'Wpis w przygotowaniu';
        $errorMessage = 'Ten wpis nie został jeszcze opublikowany i jest niedostępny.';
        $errorDetails = null;
        include __DIR__ . '/views/link_unavailable.php';
        exit;
    }

    if ($linkStatus === 'trashed') {
        http_response_code(410);
        $errorType = 'trashed';
        $errorBadge = 'Usunięty';
        $errorTitle = 'Wpis został usunięty';
        $errorMessage = 'Ten wpis został usunięty i nie jest już dostępny.';
        $errorDetails = null;
        include __DIR__ . '/views/link_unavailable.php';
        exit;
    }

    if ($publishAt && $publishAt > $now) {
        http_response_code(404);
        $errorType = 'scheduled';
        $errorBadge = 'Zaplanowany';
        $errorTitle = 'Wpis jeszcze niedostępny';
        $errorMessage = 'Ten wpis jest zaplanowany do publikacji w przyszłości.';
        $errorDetails = '<strong>Data publikacji:</strong> ' . htmlspecialchars($publishAt->format('d.m.Y H:i'));
        include __DIR__ . '/views/link_unavailable.php';
        exit;
    }

    if ($expiresAt && $expiresAt <= $now) {
        // Jeśli ustawiony fallback_url — przekieruj na niego (po walidacji)
        if (!empty($link['fallback_url'])) {
            $fallbackUrl = trim((string)$link['fallback_url']);
            if (filter_var($fallbackUrl, FILTER_VALIDATE_URL) !== false && preg_match('#^https?://#i', $fallbackUrl)) {
                http_response_code(302);
                header('Location: ' . $fallbackUrl);
                exit;
            }
        }
        http_response_code(410);
        $errorType = 'expired';
        $errorBadge = '410 • Wygasł';
        $errorTitle = 'Wpis wygasł';
        $errorMessage = 'Ten wpis był dostępny tylko przez określony czas i już wygasł.';
        $errorDetails = '<strong>Data wygaśnięcia:</strong> ' . htmlspecialchars($expiresAt->format('d.m.Y H:i'));
        include __DIR__ . '/views/link_unavailable.php';
        exit;
    }

    // Pobierz ustawienia strony głównej
    $homeTitle = (string)$settingsRepo->get('home_title', 'RedirectCMS');
    $homeSubtitle = (string)$settingsRepo->get('home_subtitle', 'Skrócone linki z historią');
    $homeMetaDescription = (string)$settingsRepo->get('home_description', '');
    $homeFooter = (string)$settingsRepo->get('home_footer', '');
    $homeHeaderCode = (string)$settingsRepo->get('home_header_code', '');
    $homeFooterCode = (string)$settingsRepo->get('home_footer_code', '');
    
    // Dane wpisu
    $postTitle = !empty($link['page_title']) ? (string)$link['page_title'] : (string)$link['slug'];
    $postDescription = !empty($link['page_description']) ? (string)$link['page_description'] : '';
    $postCreatedAt = !empty($link['created_at']) ? date('c', strtotime($link['created_at'])) : date('c');
    
    // URL-e
    $shareUrl = $baseUrl . '/blog/' . $slug;
    $directLinkUrl = $baseUrl . '/' . $slug;
    
    $og_image_url = null;
    if (!empty($link['og_image'])) {
        $og_image_url = $baseUrl . '/' . ltrim((string)$link['og_image'], '/');
    }
    $ogImageUrl = $og_image_url;

    $galleryImages = [];
    if (!empty($link['images']) && is_array($link['images'])) {
        foreach ($link['images'] as $image) {
            $galleryImages[] = [
                'id' => (int)($image['id'] ?? 0),
                'path' => (string)($image['path'] ?? ''),
                'url' => $baseUrl . '/' . ltrim((string)($image['path'] ?? ''), '/'),
                'is_primary' => !empty($image['is_primary']),
            ];
        }
    }
    
    // Pobierz powiązane wpisy i dane do sidebarów
    $relatedPosts = $linkRepo->getRelatedPosts((int)$link['id'], 5);
    $allCategories = $linkRepo->getCategoriesWithCounts();
    $allTags = $linkRepo->getTagsWithCounts();
    
    // Pobierz wybrany szablon (fallback na domyślny)
    $themeManager = new ThemeManager(__DIR__);
    $selectedTheme = (string)$settingsRepo->get('blog_theme', 'default');
    $safeTheme = $themeManager->getSafeThemeName($selectedTheme);
    
    // Załaduj szablon z wybranego motywu
    $templatePath = $themeManager->loadTemplate($safeTheme, 'post');
    if ($templatePath === null) {
        http_response_code(500);
        echo 'Nie znaleziono szablonu wpisu';
        exit;
    }

    // Sidebar
    $sidebarData = [];
    if ($themeManager->supports($safeTheme, 'sidebar')) {
        require_once __DIR__ . '/src/SidebarService.php';
        $sidebarWidgetsRaw = $settingsRepo->get('sidebar_widgets', []);
        $sidebarWidgets = is_array($sidebarWidgetsRaw) ? $sidebarWidgetsRaw : (json_decode((string)$sidebarWidgetsRaw, true) ?: []);
        if (!empty($sidebarWidgets)) {
            $socialLinksRaw = $settingsRepo->get('social_links', []);
            $socialLinks = is_array($socialLinksRaw) ? $socialLinksRaw : (json_decode((string)$socialLinksRaw, true) ?: []);
            $sidebarService = new SidebarService($pdo, $basePath);
            $sidebarData = $sidebarService->getWidgetsData($sidebarWidgets, $socialLinks);
        }
    }

    // Lightbox
    $lightboxEnabled = $themeManager->supports($safeTheme, 'lightbox');

    // Theme CSS
    $savedColorsRaw = $settingsRepo->get('theme_colors', []);
    $savedColors = is_array($savedColorsRaw) ? $savedColorsRaw : (json_decode((string)$savedColorsRaw, true) ?: []);
    $themeCss = $themeManager->generateColorCss($safeTheme, $savedColors);

    // Dane dla layoutu (header / nav / footer) — używane przez szablony
    $brandingLogo    = (string)$settingsRepo->get('branding_logo', '');
    $contactEnabled  = (bool)$settingsRepo->get('contact_page_enabled', false);
    $socialLinksRaw2 = $settingsRepo->get('social_links', []);
    $socialLinks     = is_array($socialLinksRaw2) ? $socialLinksRaw2 : (json_decode((string)$socialLinksRaw2, true) ?: []);
    require_once __DIR__ . '/src/PageRepository.php';
    $pageRepoForNav  = new PageRepository($pdo);
    $navPages        = $pageRepoForNav->getNavPages();
    $recentPosts     = $linkRepo->getRecentPosts(4);

    // Reactions
    require_once __DIR__ . '/src/ReactionRepository.php';
    $reactionRepo = new ReactionRepository($pdo);
    try {
        $reactionCounts  = $reactionRepo->getCounts((int)$link['id']);
        $ip              = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $reactionIpHash  = hash('sha256', $ip . '|react|' . $eventToken);
        $myReaction      = $reactionRepo->getCurrentVote((int)$link['id'], $reactionIpHash);
    } catch (\Throwable $e) {
        $reactionCounts = array_fill_keys(ReactionRepository::VALID_TYPES, 0);
        $myReaction     = null;
    }

    include $templatePath;
    exit;
}

// Home page
if ($uriPath === '/' || $uriPath === '') {
    // ?page=<non-numeric string> → contact lub custom page — obsługiwane poniżej
    $skipHomeMode = isset($_GET['page']) && is_string($_GET['page']) && $_GET['page'] !== '' && !ctype_digit($_GET['page']);
    if (!$skipHomeMode) {
        // Tryb redirect - przekierowanie na inny URL
        if ($homeMode === 'redirect') {
            $redirectUrl = trim((string)$settingsRepo->get('home_redirect_url', ''));
            if ($redirectUrl !== '') {
                // Prevent infinite loop: don't redirect / back to /
                $parsed = parse_url($redirectUrl);
                $path = rtrim($parsed['path'] ?? '', '/');
                $host = $parsed['host'] ?? null;
                $isSelf = ($path === '' || $path === $uriPath)
                    && ($host === null || strcasecmp($host, $_SERVER['HTTP_HOST'] ?? '') === 0);
                if (!$isSelf) {
                    header('Location: ' . $redirectUrl, true, 302);
                    exit;
                }
            }
            // Jeśli redirect URL nie jest ustawiony, fallback do landing
            $homeMode = 'landing';
        }
        
        // Wspólne ustawienia dla wszystkich trybów
        $homeTitle = (string)$settingsRepo->get('home_title', 'RedirectCMS');
        $homeSubtitle = (string)$settingsRepo->get('home_subtitle', 'Skrócone linki z historią');
        $homeMetaDescription = (string)$settingsRepo->get('home_description', '');
        $homeFooter = (string)$settingsRepo->get('home_footer', '');
        $homeHeaderCode = (string)$settingsRepo->get('home_header_code', '');
        $homeFooterCode = (string)$settingsRepo->get('home_footer_code', '');
        
        // Tryb blog - lista linków jako wpisy bloga
        if ($homeMode === 'blog') {
            $blogDescLength = (int)$settingsRepo->get('blog_description_length', 200);
            $blogPostsPerPage = (int)$settingsRepo->get('blog_posts_per_page', 10);
            $blogShowImages = (int)$settingsRepo->get('blog_show_images', 1) === 1;
            
            // Pobierz wybrany szablon (fallback na domyślny)
            $themeManager = new ThemeManager(__DIR__);
            $selectedTheme = (string)$settingsRepo->get('blog_theme', 'default');
            $safeTheme = $themeManager->getSafeThemeName($selectedTheme);
            
            // Filtrowanie po kategorii lub tagu
            $categoryId = null;
            $tagId = null;
            $categoryRepo = new CategoryRepository($pdo);
            $tagRepo = new TagRepository($pdo);

            // Wyszukiwanie
            $searchQuery = (isset($_GET['s']) && is_string($_GET['s'])) ? trim($_GET['s']) : '';

            $activeCategory = null;
            $activeTag = null;
            if (!empty($_GET['category'])) {
                $categorySlug = (string)$_GET['category'];
                $category = $categoryRepo->getBySlug($categorySlug);
                if ($category) {
                    $categoryId = (int)$category['id'];
                    $activeCategory = $category;
                }
            }

            if (!empty($_GET['tag'])) {
                $tagSlug = (string)$_GET['tag'];
                $tag = $tagRepo->getBySlug($tagSlug);
                if ($tag) {
                    $tagId = (int)$tag['id'];
                    $activeTag = $tag;
                }
            }

            // Paginacja
            $linkRepo   = new LinkRepository($pdo);
            $totalCount = $linkRepo->countBlogPosts($categoryId, $tagId, $searchQuery);
            $totalPages = $blogPostsPerPage > 0 ? (int)ceil($totalCount / $blogPostsPerPage) : 1;
            $totalPages = max(1, $totalPages);

            // Kompatybilność wsteczna: stare linki ?page=N → 301 na ?p=N
            if (!isset($_GET['p']) && isset($_GET['page'])) {
                $legacyPage = (string)$_GET['page'];
                if ($legacyPage !== 'contact' && ctype_digit($legacyPage)) {
                    $query = $_GET;
                    unset($query['page']);
                    $query['p'] = $legacyPage;
                    $rawUri  = $_SERVER['REQUEST_URI'] ?? '/';
                    $path    = parse_url($rawUri, PHP_URL_PATH);
                    if (!is_string($path) || $path === '') { $path = '/'; }
                    $baseUri = '/' . ltrim($path, '/');
                    header('Location: ' . $baseUri . '?' . http_build_query($query), true, 301);
                    exit;
                }
            }

            $currentPage = max(1, min((int)($_GET['p'] ?? 1), $totalPages));
            $offset      = ($currentPage - 1) * $blogPostsPerPage;

            // Pobierz wpisy z właściwym offsetem
            $blogPosts = $linkRepo->list($blogPostsPerPage, $offset, $categoryId, $tagId, 'created_at', 'DESC', $searchQuery, true, true);

            // Slider — oddzielne zapytanie wg konfiguracji
            $sliderCount = max(1, min(5, (int)$settingsRepo->get('slider_count', 3)));
            $sliderType  = (string)$settingsRepo->get('slider_type', 'newest');
            $sliderDays  = max(0, (int)$settingsRepo->get('slider_days', 0));
            $sliderPosts = $linkRepo->getSliderPosts($sliderCount, $sliderType, $sliderDays);

            // Pobierz wszystkie kategorie i tagi dla menu filtrów z licznikami
            $allCategories = $linkRepo->getCategoriesWithCounts();
            $allTags = $linkRepo->getTagsWithCounts();
            
            // Załaduj szablon z wybranego motywu
            $templatePath = $themeManager->loadTemplate($safeTheme, 'home');
            if ($templatePath === null) {
                http_response_code(500);
                echo 'Nie znaleziono szablonu bloga';
                exit;
            }
    
            // Sidebar
            $sidebarData = [];
            if ($themeManager->supports($safeTheme, 'sidebar')) {
                require_once __DIR__ . '/src/SidebarService.php';
                $sidebarWidgetsRaw = $settingsRepo->get('sidebar_widgets', []);
                $sidebarWidgets = is_array($sidebarWidgetsRaw) ? $sidebarWidgetsRaw : (json_decode((string)$sidebarWidgetsRaw, true) ?: []);
                if (!empty($sidebarWidgets)) {
                    $socialLinksRaw = $settingsRepo->get('social_links', []);
                    $socialLinks = is_array($socialLinksRaw) ? $socialLinksRaw : (json_decode((string)$socialLinksRaw, true) ?: []);
                    $sidebarService = new SidebarService($pdo, $basePath);
                    $sidebarData = $sidebarService->getWidgetsData($sidebarWidgets, $socialLinks);
                }
            }
    
            // Theme CSS
            $savedColorsRaw = $settingsRepo->get('theme_colors', []);
            $savedColors = is_array($savedColorsRaw) ? $savedColorsRaw : (json_decode((string)$savedColorsRaw, true) ?: []);
            $themeCss = $themeManager->generateColorCss($safeTheme, $savedColors);
    
            // Dane dla layoutu (header / nav / footer) — używane przez szablony
            $brandingLogo    = (string)$settingsRepo->get('branding_logo', '');
            $contactEnabled  = (bool)$settingsRepo->get('contact_page_enabled', false);
            $socialLinksRaw2 = $settingsRepo->get('social_links', []);
            $socialLinks     = is_array($socialLinksRaw2) ? $socialLinksRaw2 : (json_decode((string)$socialLinksRaw2, true) ?: []);
            require_once __DIR__ . '/src/PageRepository.php';
            $pageRepoForNav  = new PageRepository($pdo);
            $navPages        = $pageRepoForNav->getNavPages();
            $recentPosts     = $linkRepo->getRecentPosts(4);

            include $templatePath;
            exit;
        }

        // Tryb landing (domyślny) - statyczna strona informacyjna
        $homeSectionTitle = (string)$settingsRepo->get('home_section_title', 'Jak używać?');
        $homeSectionText = (string)$settingsRepo->get('home_section_text', 'Aby skorzystać z systemu skróconych linków, podaj prawidłowy skrót adresu w poniższy sposób:');
        $homeCtaButtonText = (string)$settingsRepo->get('home_admin_button_text', '');
        $homeCtaButtonUrl = (string)$settingsRepo->get('home_cta_button_url', '');
        include __DIR__ . '/views/home.php';
        exit;
    } // end if (!$skipHomeMode)
}

// Strona kontaktowa (/?page=contact)
if (($_GET['page'] ?? null) === 'contact') {
    $contactEnabled = (bool)$settingsRepo->get('contact_page_enabled', false);
    if (!$contactEnabled) {
        http_response_code(404);
        include __DIR__ . '/views/404.php';
        exit;
    }

    $themeManager   = new ThemeManager(__DIR__);
    $selectedTheme  = (string)$settingsRepo->get('blog_theme', 'default');
    $safeTheme      = $themeManager->getSafeThemeName($selectedTheme);
    $homeTitle      = (string)$settingsRepo->get('home_title', 'RedirectCMS');
    $homeSubtitle   = (string)$settingsRepo->get('home_subtitle', '');
    $homeFooter     = (string)$settingsRepo->get('home_footer', '');
    $homeHeaderCode = (string)$settingsRepo->get('home_header_code', '');
    $homeFooterCode = (string)$settingsRepo->get('home_footer_code', '');
    $brandingLogo   = (string)$settingsRepo->get('branding_logo', '');
    $savedColorsRaw = $settingsRepo->get('theme_colors', []);
    $savedColors    = is_array($savedColorsRaw) ? $savedColorsRaw : (json_decode((string)$savedColorsRaw, true) ?: []);
    $themeCss       = $themeManager->generateColorCss($safeTheme, $savedColors);
    $contactIntro   = (string)$settingsRepo->get('contact_page_intro', '');
    $contactEmail   = (string)$settingsRepo->get('contact_page_email', '');

    require_once __DIR__ . '/src/PageRepository.php';
    require_once __DIR__ . '/src/ContactRateLimiter.php';
    $pageRepoForNav = new PageRepository($pdo);
    try { $pageRepoForNav->ensureTable(); } catch (\Throwable $e) {}
    $navPages = $pageRepoForNav->getNavPages();

    $formSuccess = null;
    $formError   = null;
    $formValues  = [];

    // CSRF token dla formularza kontaktowego
    Utils::startSession();
    if (empty($_SESSION['contact_csrf'])) {
        $_SESSION['contact_csrf'] = bin2hex(random_bytes(16));
    }
    $contactCsrf = $_SESSION['contact_csrf'];

    // Obsługa POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $submittedCsrf = (string)($_POST['csrf_contact'] ?? '');
        if (!hash_equals($_SESSION['contact_csrf'], $submittedCsrf)) {
            $formError = 'Błąd weryfikacji tokenu. Odśwież stronę i spróbuj ponownie.';
        } else {
            // Rate limiting: max 3 wysyłki na IP na godzinę (w bazie danych, niezależnie od sesji)
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $contactRateLimiter = new ContactRateLimiter($pdo);

            if (!$contactRateLimiter->isAllowed($ip)) {
                $formError = 'Przekroczono limit wiadomości. Spróbuj ponownie za godzinę.';
            } else {
                $name    = mb_substr(trim((string)($_POST['name'] ?? '')), 0, 50);
                $email   = trim((string)($_POST['email'] ?? ''));
                $message = trim((string)($_POST['message'] ?? ''));

                // Sanityzacja wiadomości — usuwamy HTML/JS, zostawiamy czysty tekst
                $message = strip_tags($message);
                $message = mb_substr($message, 0, 1000);

                $formValues = compact('name', 'email', 'message');

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $formError = 'Podaj poprawny adres e-mail.';
                } elseif ($message === '') {
                    $formError = 'Treść wiadomości jest wymagana.';
                } else {
                    // Wyślij e-mail
                    $sent = false;
                    if ($contactEmail !== '') {
                        try {
                            require_once __DIR__ . '/src/EmailService.php';
                            $emailService = new EmailService($pdo);
                            $subject = 'Nowa wiadomość z formularza kontaktowego';
                            $safeName   = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                            $safeEmail  = htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                            $senderInfo = $name !== '' ? "{$safeName} &lt;{$safeEmail}&gt;" : $safeEmail;
                            $htmlBody = "
                                <h2>Nowa wiadomość kontaktowa</h2>
                                <p><strong>Od:</strong> {$senderInfo}</p>
                                <p><strong>E-mail:</strong> {$safeEmail}</p>
                                " . ($name !== '' ? "<p><strong>Imię:</strong> {$safeName}</p>" : "") . "
                                <hr>
                                <p><strong>Treść:</strong></p>
                                <p>" . nl2br(htmlspecialchars($message)) . "</p>
                            ";
                            $emailService->sendEmail($contactEmail, $subject, $htmlBody);
                            $sent = true;
                        } catch (\Throwable $e) {
                            error_log('Contact form email error: ' . $e->getMessage());
                        }
                    }

                    // Regeneruj CSRF po udanym wysłaniu
                    $_SESSION['contact_csrf'] = bin2hex(random_bytes(16));
                    $contactCsrf = $_SESSION['contact_csrf'];

                    // Zarejestruj wysłanie w rate limiterze (DB)
                    $contactRateLimiter->record($ip);

                    $formSuccess = 'Dziękujemy! Twoja wiadomość została wysłana. Odpowiemy tak szybko, jak to możliwe.';
                    if (!$sent && $contactEmail === '') {
                        $formSuccess = 'Wiadomość przyjęta, jednak brak skonfigurowanego adresu e-mail — skontaktuj się z administratorem.';
                    }
                    $formValues = [];
                }
            }
        }
    }

    $templatePath = $themeManager->loadTemplate($safeTheme, 'contact');
    if ($templatePath === null) {
        http_response_code(500);
        echo 'Brak szablonu strony kontaktowej dla wybranego motywu.';
        exit;
    }
    $linkRepo    = new LinkRepository($pdo);
    $recentPosts = $linkRepo->getRecentPosts(4);
    include $templatePath;
    exit;
}

// Custom Pages (/?page=slug)
$requestedPage = $_GET['page'] ?? null;
if ($requestedPage !== null && $requestedPage !== 'contact') {
    $pageSlug = preg_replace('/[^a-z0-9\-]/', '', strtolower(trim((string)$requestedPage)));

    if ($pageSlug !== '') {
        require_once __DIR__ . '/src/PageRepository.php';
        $pageRepo = new PageRepository($pdo);
        try {
            $pageRepo->ensureTable();
            $customPage = $pageRepo->getBySlug($pageSlug);
        } catch (\Throwable $e) {
            $customPage = null;
        }

        if ($customPage) {
            $themeManager    = new ThemeManager(__DIR__);
            $selectedTheme   = (string)$settingsRepo->get('blog_theme', 'default');
            $safeTheme       = $themeManager->getSafeThemeName($selectedTheme);
            $homeTitle       = (string)$settingsRepo->get('home_title', 'RedirectCMS');
            $homeSubtitle    = (string)$settingsRepo->get('home_subtitle', '');
            $homeFooter      = (string)$settingsRepo->get('home_footer', '');
            $homeHeaderCode  = (string)$settingsRepo->get('home_header_code', '');
            $homeFooterCode  = (string)$settingsRepo->get('home_footer_code', '');
            $brandingLogo    = (string)$settingsRepo->get('branding_logo', '');
            $savedColorsRaw  = $settingsRepo->get('theme_colors', []);
            $savedColors     = is_array($savedColorsRaw) ? $savedColorsRaw : (json_decode((string)$savedColorsRaw, true) ?: []);
            $themeCss        = $themeManager->generateColorCss($safeTheme, $savedColors);
            $socialLinksRaw  = $settingsRepo->get('social_links', []);
            $socialLinks     = is_array($socialLinksRaw) ? $socialLinksRaw : (json_decode((string)$socialLinksRaw, true) ?: []);
            $contactEnabled  = (bool)$settingsRepo->get('contact_page_enabled', false);
            $navPages        = $pageRepo->getNavPages();
            $currentSlug     = $pageSlug;
            $pageTitle       = (string)$customPage['title'];
            $pageHtml        = (string)($customPage['content_html'] ?? '');
            $pageJs          = (string)($customPage['content_js'] ?? '');
            $pageMetaTitle   = (string)($customPage['meta_title'] ?? '');
            $pageMetaDescription = (string)($customPage['meta_description'] ?? '');

            if ($customPage['use_theme']) {
                $templatePath = $themeManager->loadTemplate($safeTheme, 'page');
                if ($templatePath !== null) {
                    $linkRepo    = new LinkRepository($pdo);
                    $recentPosts = $linkRepo->getRecentPosts(4);
                    include $templatePath;
                    exit;
                }
            }
            // Standalone lub brak szablonu — czysty HTML
            header('Content-Type: text/html; charset=utf-8');
            echo '<!doctype html><html lang="pl"><head><meta charset="utf-8">';
            echo '<title>' . htmlspecialchars($pageMetaTitle ?: $pageTitle) . '</title>';
            if ($pageMetaDescription) {
                echo '<meta name="description" content="' . htmlspecialchars($pageMetaDescription) . '">';
            }
            echo '</head><body>' . $pageHtml;
            if ($pageJs) echo '<script>' . $pageJs . '</script>';
            echo '</body></html>';
            exit;
        }
    }
}

// Redirect by slug
$slug = ltrim($uriPath, '/');
$linkRepo = new LinkRepository($pdo);

// Pobierz link włącznie z niepublikowanymi, aby pokazać odpowiedni komunikat błędu
$link = $linkRepo->getBySlug($slug, true);
if (!$link) {
    http_response_code(404);
    include __DIR__ . '/views/404.php';
    exit;
}

$now = new \DateTimeImmutable('now');
$publishAt = !empty($link['publish_at']) ? new \DateTimeImmutable((string)$link['publish_at']) : null;
$expiresAt = !empty($link['expires_at']) ? new \DateTimeImmutable((string)$link['expires_at']) : null;
$linkStatus = $link['status'] ?? 'published';

// Sprawdź status linku i pokaż odpowiedni komunikat błędu
if ($linkStatus === 'draft') {
    http_response_code(404);
    $errorType = 'draft';
    $errorBadge = 'Szkic';
    $errorTitle = 'Link w przygotowaniu';
    $errorMessage = 'Ten link nie został jeszcze opublikowany i jest niedostępny.';
    $errorDetails = null;
    include __DIR__ . '/views/link_unavailable.php';
    exit;
}

if ($linkStatus === 'trashed') {
    http_response_code(410);
    $errorType = 'trashed';
    $errorBadge = 'Usunięty';
    $errorTitle = 'Link został usunięty';
    $errorMessage = 'Ten link został usunięty i nie jest już dostępny.';
    $errorDetails = null;
    include __DIR__ . '/views/link_unavailable.php';
    exit;
}

if ($publishAt && $publishAt > $now) {
    http_response_code(404);
    $errorType = 'scheduled';
    $errorBadge = 'Zaplanowany';
    $errorTitle = 'Link jeszcze niedostępny';
    $errorMessage = 'Ten link jest zaplanowany do publikacji w przyszłości.';
    $errorDetails = '<strong>Data publikacji:</strong> ' . htmlspecialchars($publishAt->format('d.m.Y H:i'));
    include __DIR__ . '/views/link_unavailable.php';
    exit;
}

if ($expiresAt && $expiresAt <= $now) {
    // Jeśli ustawiony fallback_url — przekieruj na niego (po walidacji)
    if (!empty($link['fallback_url'])) {
        $fallbackUrl = trim((string)$link['fallback_url']);
        if (filter_var($fallbackUrl, FILTER_VALIDATE_URL) !== false && preg_match('#^https?://#i', $fallbackUrl)) {
            http_response_code(302);
            header('Location: ' . $fallbackUrl);
            exit;
        }
    }
    http_response_code(410);
    $errorType = 'expired';
    $errorBadge = '410 • Wygasł';
    $errorTitle = 'Link wygasł';
    $errorMessage = 'Ten link był dostępny tylko przez określony czas i już wygasł.';
    $errorDetails = '<strong>Data wygaśnięcia:</strong> ' . htmlspecialchars($expiresAt->format('d.m.Y H:i'));
    include __DIR__ . '/views/link_unavailable.php';
    exit;
}

// Zapisz statystykę kliknięcia
$statsRepo = new StatsRepository($pdo);
$statsRepo->recordClick(
    (int)$link['id'],
    $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
    $_SERVER['HTTP_USER_AGENT'] ?? '',
    $_SERVER['HTTP_REFERER'] ?? null
);

// Password protection logic
Utils::startSession();
$hasPassword = !empty($link['password_hash']);
$passwordGranted = false;
$password_error = null;
$csrf_token = Utils::csrfToken();

if ($hasPassword) {
    $sessionKey = 'granted_link_' . (int)$link['id'];
    
    // Check if already granted in session
    if (!empty($_SESSION[$sessionKey])) {
        $passwordGranted = true;
    }
    
    // Handle password verification attempt
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_password'])) {
        if (!Utils::verifyCsrf($_POST['csrf'] ?? null)) {
            $password_error = 'Błąd CSRF. Odśwież stronę i spróbuj ponownie.';
        } else {
            // Rate limiting: max 5 attempts per IP per 5 minutes
            $rateLimitKey = 'pwd_attempts_' . Utils::anonymizeIp($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
            $attempts = $_SESSION[$rateLimitKey] ?? ['count' => 0, 'time' => time()];
            
            // Reset counter if 5 minutes passed
            if (time() - $attempts['time'] > 300) {
                $attempts = ['count' => 0, 'time' => time()];
            }
            
            if ($attempts['count'] >= 5) {
                $password_error = 'Zbyt wiele prób. Spróbuj ponownie za kilka minut.';
            } else {
                $inputPassword = (string)($_POST['password'] ?? '');
                
                if (password_verify($inputPassword, $link['password_hash'])) {
                    // Success - grant access
                    $_SESSION[$sessionKey] = true;
                    $passwordGranted = true;
                    
                    // Reset attempts counter
                    unset($_SESSION[$rateLimitKey]);
                    
                    // Redirect to same URL to clear POST data
                    header('Location: ' . $_SERVER['REQUEST_URI']);
                    exit;
                } else {
                    // Failed attempt
                    $attempts['count']++;
                    $_SESSION[$rateLimitKey] = $attempts;
                    
                    $remaining = 5 - $attempts['count'];
                    if ($remaining > 0) {
                        $password_error = "Nieprawidłowe hasło. Pozostało prób: {$remaining}";
                    } else {
                        $password_error = 'Zbyt wiele prób. Spróbuj ponownie za kilka minut.';
                    }
                }
            }
        }
    }
}

$target_url = $link['target_url'];
$delay_seconds = (int)$link['delay_seconds'];
$page_title = $link['page_title'] ?? null;
$page_description = $link['page_description'] ?? null;
$page_description_plain = null;
if (!empty($page_description)) {
    // Og meta nie powinny zawierać HTML — usuń tagi, zachowaj tekst
    $page_description_plain = trim(strip_tags((string)$page_description));
}
$og_image = $link['og_image'] ?? null;
$password_hint = $link['password_hint'] ?? null;
$redirectHeaderCode = (string)$settingsRepo->get('redirect_header_code', '');
$redirectFooterCode = (string)$settingsRepo->get('redirect_footer_code', '');

// Detekcja botów społecznościowych; sterowane ustawieniem
$disableSocial = ((int)$settingsRepo->get('disable_social_redirect', 1) === 1);
$isCrawler = $disableSocial && Utils::isSocialCrawler($_SERVER['HTTP_USER_AGENT'] ?? '');

// Zbuduj absolutne URL dla og:url/twitter:url oraz obrazu
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$share_url = $scheme . '://' . $host . ($basePath ? $basePath : '') . '/' . $slug;
$og_image_url = null;
if (!empty($og_image)) {
    $og_image_url = $scheme . '://' . $host . ($basePath ? $basePath : '') . '/' . ltrim((string)$og_image, '/');
}
include __DIR__ . '/views/redirect.php';

