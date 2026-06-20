<?php
declare(strict_types=1);

/**
 * Aktualna wersja zainstalowanej aplikacji.
 * Aktualizuj tę stałą przy każdym wydaniu.
 */
define('RCMS_VERSION', '2.0.2');

/** URL sklepu — używany w banerach licencji DEMO do linku "Kup licencję". */
if (!defined('RCMS_SHOP_URL')) {
    define('RCMS_SHOP_URL', 'https://redirectcms.pl');
}

// Wczytaj klucz licencyjny jeśli plik istnieje i stała nie jest jeszcze zdefiniowana
if (!defined('RCMS_LICENSE_KEY')) {
    $__licenseFile = __DIR__ . '/../config/license.php';
    if (is_file($__licenseFile)) {
        require_once $__licenseFile;
    }
    if (!defined('RCMS_LICENSE_KEY')) {
        define('RCMS_LICENSE_KEY', '');
    }
    unset($__licenseFile);
}

class VersionChecker
{
    private const API_URL   = 'https://redirectcms.pl/api/version.php';
    private const CACHE_KEY = 'version_check_cache';
    private const CACHE_TTL = 86400; // 24 godziny

    public function __construct(private PrefixedPDO $pdo) {}

    /**
     * Zwraca informacje o wersjach z cache lub po pobraniu z API.
     *
     * @return array{
     *   current_version: string,
     *   latest_version: string|null,
     *   latest_title: string|null,
     *   changelog_url: string|null,
     *   published_at: string|null,
     *   update_available: bool,
     *   last_checked: int|null
     * }
     */
    public function getVersionInfo(): array
    {
        $settings = new SettingsRepository($this->pdo);

        $result = [
            'current_version'  => RCMS_VERSION,
            'latest_version'   => null,
            'latest_title'     => null,
            'changelog_url'    => null,
            'published_at'     => null,
            'update_available' => false,
            'last_checked'     => null,
        ];

        $cache = $settings->get(self::CACHE_KEY, null);
        $now   = time();

        $needsRefresh = !is_array($cache)
            || !isset($cache['last_checked'])
            || ($now - (int)$cache['last_checked'] > self::CACHE_TTL);

        if (!$needsRefresh) {
            $result = array_merge($result, [
                'latest_version' => $cache['latest_version'] ?? null,
                'latest_title'   => $cache['latest_title'] ?? null,
                'changelog_url'  => $cache['changelog_url'] ?? null,
                'published_at'   => $cache['published_at'] ?? null,
                'last_checked'   => (int)$cache['last_checked'],
            ]);
        } else {
            $data = $this->fetchRemote();

            if ($data !== null) {
                $newCache = [
                    'last_checked'   => $now,
                    'latest_version' => $data['version'] ?? null,
                    'latest_title'   => $data['title'] ?? null,
                    'changelog_url'  => $data['changelog_url'] ?? null,
                    'published_at'   => $data['published_at'] ?? null,
                ];
                $settings->set(self::CACHE_KEY, $newCache);

                $result = array_merge($result, [
                    'latest_version' => $newCache['latest_version'],
                    'latest_title'   => $newCache['latest_title'],
                    'changelog_url'  => $newCache['changelog_url'],
                    'published_at'   => $newCache['published_at'],
                    'last_checked'   => $now,
                ]);
            } elseif (is_array($cache)) {
                // Fetch nieudany – używamy starego cache
                $result = array_merge($result, [
                    'latest_version' => $cache['latest_version'] ?? null,
                    'latest_title'   => $cache['latest_title'] ?? null,
                    'changelog_url'  => $cache['changelog_url'] ?? null,
                    'published_at'   => $cache['published_at'] ?? null,
                    'last_checked'   => (int)($cache['last_checked'] ?? 0),
                ]);
            }
        }

        if ($result['latest_version'] !== null) {
            $result['update_available'] = version_compare(
                $result['latest_version'],
                RCMS_VERSION,
                '>'
            );
        }

        // Sanityzacja changelog_url — dozwolone tylko https:// w domenie redirectcms.pl lub jej subdomenach
        $result['changelog_url'] = $this->sanitizeChangelogUrl($result['changelog_url']);

        return $result;
    }

    /**
     * Akceptuje tylko https:// URL w domenie redirectcms.pl (lub jej subdomenach).
     * Zwraca null jeśli URL nie spełnia wymagań.
     */
    private function sanitizeChangelogUrl(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }
        $parsed = parse_url($url);
        if (
            !is_array($parsed)
            || ($parsed['scheme'] ?? '') !== 'https'
            || !isset($parsed['host'])
            || !preg_match('/(?:^|\.)redirectcms\.pl$/i', $parsed['host'])
        ) {
            return null;
        }
        return $url;
    }

    /**
     * Tworzy powiadomienie systemowe o nowej wersji, jeśli jeszcze nie zostało
     * utworzone dla tej wersji (deduplication po kluczu version_check_notified).
     */
    public function createUpdateNotificationIfNeeded(array $versionInfo): void
    {
        if (!$versionInfo['update_available'] || empty($versionInfo['latest_version'])) {
            return;
        }

        $settings         = new SettingsRepository($this->pdo);
        $notifiedVersion  = (string)$settings->get('version_check_notified', '');

        if ($notifiedVersion === $versionInfo['latest_version']) {
            return;
        }

        $title   = 'Dostępna nowa wersja RedirectCMS: ' . $versionInfo['latest_version'];
        $message = '';
        if (!empty($versionInfo['latest_title'])) {
            $message .= $versionInfo['latest_title'];
        }
        if (!empty($versionInfo['changelog_url'])) {
            $message .= ($message !== '' ? "\n\n" : '') . 'Changelog: ' . $versionInfo['changelog_url'];
        }

        $context = [
            'current_version' => $versionInfo['current_version'],
            'latest_version'  => $versionInfo['latest_version'],
            'published_at'    => $versionInfo['published_at'] ?? null,
            'changelog_url'   => $versionInfo['changelog_url'] ?? null,
        ];

        $notifRepo = new NotificationRepository($this->pdo);
        $notifRepo->create(
            'system',
            $title,
            trim($message) !== '' ? trim($message) : null,
            'info',
            $context
        );

        $settings->set('version_check_notified', $versionInfo['latest_version']);
    }

    private function fetchRemote(): ?array
    {
        // Domena instalacji — wysyłana do API do celów logowania
        // HTTP_HOST może zawierać port (np. example.com:8443) — wydzielamy sam host
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if ($host !== '') {
            $hostParts = explode(':', $host, 2);
            $host      = $hostParts[0];
        }
        $domain = preg_match('/^[a-zA-Z0-9\-\.]{1,253}$/', $host) ? strtolower($host) : '';

        // Buduj query string: domain + license_key (jeśli klucz ustawiony i wygląda poprawnie)
        $licenseKey = (defined('RCMS_LICENSE_KEY') && preg_match('/^[a-f0-9]{64}$/', RCMS_LICENSE_KEY))
            ? RCMS_LICENSE_KEY
            : '';
        $params = [];
        if ($domain !== '')     $params['domain'] = $domain;
        if ($licenseKey !== '') $params['key']    = $licenseKey;
        $url = self::API_URL . ($params !== [] ? '?' . http_build_query($params) : '');

        $ctx = stream_context_create([
            'http' => [
                'timeout'       => 5,
                // Format: RedirectCMS/X.Y.Z PHP/X.Y.Z (+https://redirectcms.pl)
                'user_agent'    => 'RedirectCMS/' . RCMS_VERSION
                                 . ' PHP/' . PHP_VERSION
                                 . ' (+https://redirectcms.pl)',
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer'      => true,
                'verify_peer_name' => true,
            ],
        ]);

        $raw = @file_get_contents($url, false, $ctx);
        if ($raw === false || $raw === '') {
            return null;
        }

        $data = json_decode($raw, true);
        if (!is_array($data) || isset($data['error']) || empty($data['version'])) {
            return null;
        }

        return $data;
    }
}
