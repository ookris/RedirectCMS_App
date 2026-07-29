<?php

class Utils
{
    /** Czas życia sesji i ciasteczka sesyjnego (w sekundach) */
    private const SESSION_LIFETIME = 21_600; // 6 godzin

    /**
     * Ustawia nagłówki bezpieczeństwa HTTP
     */
    public static function setSecurityHeaders(): void
    {
        // Zapobiega osadzaniu strony w iframe (clickjacking)
        header('X-Frame-Options: SAMEORIGIN');

        // Wymusza MIME type sniffing
        header('X-Content-Type-Options: nosniff');

        // Włącza wbudowaną ochronę XSS przeglądarki
        header('X-XSS-Protection: 1; mode=block');

        // Kontroluje ile informacji referrer jest wysyłane
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Content Security Policy — obecnie z 'unsafe-inline' (wiele widoków używa inline JS/CSS).
        // TODO: migruj do nonce/hash i usuń 'unsafe-inline' przynajmniej w adminie.
        // Usuń nagłówki CSP ustawione wcześniej w tym samym procesie PHP (nie usuwa nagłówków dodanych przez serwer).
        header_remove('Content-Security-Policy');
        header_remove('Content-Security-Policy-Report-Only');

        $csp = "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' cdn.jsdelivr.net unpkg.com cdnjs.cloudflare.com monitor.openstats.xyz connect.facebook.net; " .
            "style-src 'self' 'unsafe-inline' cdn.jsdelivr.net unpkg.com cdnjs.cloudflare.com stackpath.bootstrapcdn.com maxcdn.bootstrapcdn.com fonts.googleapis.com; " .
            "font-src 'self' fonts.gstatic.com cdn.jsdelivr.net; " .
            "img-src 'self' data: https:; " .
            "connect-src 'self' monitor.openstats.xyz connect.facebook.net cdnjs.cloudflare.com; " .
            "frame-ancestors 'self'";
        header("Content-Security-Policy: $csp");

        // Permissions Policy - ogranicza dostęp do API przeglądarki
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

        // Strict Transport Security - tylko dla HTTPS
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }

    /**
     * Wczytuje konfigurację aplikacji
     */
    private static function loadConfig(): array
    {
        $configPath = __DIR__ . '/../config/config.php';

        if (file_exists($configPath)) {
            return require $configPath;
        }

        // Domyślna konfiguracja jeśli brak pliku
        return [
            'session' => ['secure' => false],
            'app' => ['env' => 'production']
        ];
    }

    public static function startSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            // Wczytaj konfigurację
            $config = self::loadConfig();
            // Automatycznie wymuś secure=true gdy połączenie jest HTTPS,
            // niezależnie od ustawień config.php (config może nadpisać na false tylko przy braku HTTPS)
            $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
            $isSecure = $isHttps || ($config['session']['secure'] ?? false);

            // Bezpieczne ustawienia sesji
            session_set_cookie_params([
                'lifetime' => self::SESSION_LIFETIME,
                'path' => '/',
                'domain' => '',
                'secure' => $isSecure,        // true gdy HTTPS lub wymuszony w config
                'httponly' => true,           // ochrona przed XSS
                'samesite' => 'Strict'        // ochrona przed CSRF
            ]);

            session_start();

            // Timeout sesji po braku aktywności
            if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > self::SESSION_LIFETIME)) {
                session_unset();
                session_destroy();
                session_start();
            }
            $_SESSION['last_activity'] = time();
        }
    }

    public static function setBasePath(string $basePath): void
    {
        self::$basePath = $basePath;
    }

    private static string $basePath = '/';

    public static function getBasePath(): string
    {
        return self::$basePath;
    }

    public static function csrfToken(): string
    {
        self::startSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(?string $token): bool
    {
        self::startSession();
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
    }

    public static function generateSlug(int $length = 8): string
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $out = '';
        for ($i = 0; $i < $length; $i++) {
            $out .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        return $out;
    }

    public static function sanitizeUrl(string $url): string
    {
        $url = trim($url);
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Nieprawidłowy URL');
        }
        // FILTER_VALIDATE_URL akceptuje dowolny schemat (np. javascript:), więc
        // dodatkowo wymagamy http/https — inaczej zapisany adres mógłby wykonać
        // się jako stored XSS wszędzie tam, gdzie jest renderowany jako <a href>.
        $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new InvalidArgumentException('Dozwolone są tylko adresy zaczynające się od http:// lub https://');
        }
        return $url;
    }

    /**
     * Sprawdza, czy zapisany wcześniej URL jest bezpieczny do wyrenderowania jako <a href>
     * (obrona w głąb dla danych zapisanych zanim sanitizeUrl() zaczął odrzucać schemat).
     */
    public static function isSafeHttpUrl(?string $url): bool
    {
        if (!$url) return false;
        $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
        return in_array($scheme, ['http', 'https'], true);
    }

    /**
     * Neutralizuje wartość przed zapisem do CSV, aby arkusz kalkulacyjny (Excel/Sheets)
     * nie potraktował jej jako formuły (CSV/formula injection przez =, +, -, @).
     */
    public static function csvSafe(mixed $value): string
    {
        $value = (string)$value;
        return preg_match('/^\s*[=+\-@\t\r]/', $value) ? "'" . $value : $value;
    }

    public static function sanitizeSlug(string $slug, int $maxLength = 190): string
    {
        $slug = trim($slug);
        if ($slug === '') throw new InvalidArgumentException('Alias nie może być pusty');

        // Zabezpieczenie przed path traversal - blokuj .. / \ null bytes
        if (preg_match('/\.\.|[\/\\\\\x00]/', $slug)) {
            throw new InvalidArgumentException('Alias zawiera niedozwolone znaki (., /, \\)');
        }

        // Sprawdź czy slug nie jest zarezerwowaną nazwą
        $reserved = ['admin', 'api', '__event', '__react', 'index', 'login', 'logout', 'config'];
        if (in_array(strtolower($slug), $reserved, true)) {
            throw new InvalidArgumentException('Alias nie może być zarezerwowaną nazwą');
        }

        // Usuń polskie znaki diakrytyczne i oczyść slug do dozwolonego zestawu znaków
        $map = [
            'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ż' => 'z', 'ź' => 'z',
            'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'E', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'O', 'Ś' => 'S', 'Ż' => 'Z', 'Ź' => 'Z',
        ];
        $slug = strtr($slug, $map);

        // Zamień spacje na myślniki i usuń inne znaki specjalne (kropki też będą usunięte)
        $slug = preg_replace('/\s+/', '-', $slug);
        $slug = preg_replace('/[^a-zA-Z0-9_-]/', '', $slug);

        // Zredukuj wielokrotne myślniki i usuń je z początku/końca
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-_');

        if ($slug === '') {
            throw new InvalidArgumentException('Alias po oczyszczeniu jest pusty');
        }

        if (strlen($slug) > $maxLength) {
            throw new InvalidArgumentException("Alias jest za długi (max $maxLength znaków)");
        }

        return $slug;
    }

    public static function isLoggedIn(): bool
    {
        self::startSession();
        if (!empty($_SESSION['admin_logged'])) {
            return true;
        }

        // Sprawdź cookie "Zapamiętaj mnie"
        if (self::checkRememberMeCookie()) {
            return true;
        }

        return false;
    }

    public static function requireLogin(): void
    {
        self::startSession();

        // Sprawdź czy zalogowany lub ma ważne cookie "Zapamiętaj mnie"
        if (!($_SESSION['admin_logged'] ?? false)) {
            // Spróbuj zalogować przez remember me cookie
            if (!self::checkRememberMeCookie()) {
                header('Location: ' . self::$basePath . '/admin/index.php?action=login');
                exit;
            }
        }

        // Wymuszaj zmianę domyślnego hasła (z wyjątkiem strony settings)
        $action = $_GET['action'] ?? 'dashboard';
        if (!empty($_SESSION['must_change_password']) && !in_array($action, ['settings', 'settings_post', 'logout'])) {
            header('Location: ' . self::$basePath . '/admin/index.php?action=settings');
            exit;
        }
    }

    /**
     * Sprawdź cookie "Zapamiętaj mnie" i zaloguj automatycznie
     * Uwaga: System obsługuje tylko jednego admina (single-user)
     */
    private static function checkRememberMeCookie(): bool
    {
        $cookieName = 'remember_token';

        if (empty($_COOKIE[$cookieName])) {
            return false;
        }

        $token = $_COOKIE[$cookieName];
        $parts = explode(':', $token);
        if (count($parts) !== 2) {
            self::clearRememberMeCookie();
            return false;
        }

        [$selector, $validator] = $parts;

        // Pobierz dane z bazy
        try {
            $config = self::loadConfig();
            $db = new Database($config);
            $pdo = $db->pdo();
            $repo = new SettingsRepository($pdo);

            $storedSelector = (string)$repo->get('remember_me_selector', '');
            $storedValidatorHash = (string)$repo->get('remember_me_validator_hash', '');
            $storedExpiry = (int)$repo->get('remember_me_expiry', 0);

            // Sprawdź selector (timing-safe)
            if (!hash_equals($storedSelector, $selector)) {
                self::clearRememberMeCookie();
                return false;
            }

            // Sprawdź ważność
            if (time() > $storedExpiry) {
                self::clearRememberMeCookie();
                return false;
            }

            // Sprawdź validator
            if (!password_verify($validator, $storedValidatorHash)) {
                self::clearRememberMeCookie();
                return false;
            }

            // Token OK - zaloguj użytkownika
            self::startSession();
            session_regenerate_id(true);
            $_SESSION['admin_logged'] = true;

            // Rotacja tokena dla bezpieczeństwa
            self::rotateRememberMeToken($repo, $selector);

            return true;
        } catch (Throwable $e) {
            error_log('Remember me check error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Rotacja tokena "Zapamiętaj mnie" (nowy validator, ten sam selector)
     */
    private static function rotateRememberMeToken(SettingsRepository $repo, string $selector): void
    {
        $validator = bin2hex(random_bytes(32));
        $expiry = time() + (30 * 24 * 60 * 60); // 30 dni

        $repo->set('remember_me_validator_hash', password_hash($validator, PASSWORD_DEFAULT));
        $repo->set('remember_me_expiry', $expiry);

        $token = $selector . ':' . $validator;
        $isSecure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

        setcookie(
            'remember_token',
            $token,
            [
                'expires' => $expiry,
                'path' => '/',
                'domain' => '',
                'secure' => $isSecure,
                'httponly' => true,
                'samesite' => 'Strict'
            ]
        );
    }

    /**
     * Usuń cookie "Zapamiętaj mnie"
     */
    private static function clearRememberMeCookie(): void
    {
        if (isset($_COOKIE['remember_token'])) {
            setcookie(
                'remember_token',
                '',
                [
                    'expires' => time() - 3600,
                    'path' => '/',
                    'domain' => '',
                    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]
            );
        }
    }

    /**
     * Upewnij się, że katalog istnieje (twórz rekurencyjnie)
     */
    public static function ensureDir(string $dir): void
    {
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
    }

    /**
     * Dopisz wpis do pliku logów (tworzy katalog jeśli potrzebny)
     */
    public static function appendLog(string $file, string $message): void
    {
        $dir = dirname($file);
        self::ensureDir($dir);
        $line = rtrim($message) . PHP_EOL;
        @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Odczytaj ostatnie N linii pliku (tail)
     * Zwraca tablicę linii (od najstarszej do najnowszej z wycinka)
     */
    public static function tailFile(string $file, int $lines = 10): array
    {
        if (!is_file($file) || $lines <= 0) return [];
        $fp = @fopen($file, 'r');
        if (!$fp) return [];
        $buffer = '';
        $chunkSize = 4096;
        $pos = -1;
        $lineCount = 0;
        fseek($fp, 0, SEEK_END);
        $fileSize = ftell($fp);
        while ($lineCount <= $lines && abs($pos) < $fileSize) {
            $seek = max($fileSize + $pos - $chunkSize + 1, 0);
            $readLen = min($chunkSize, $fileSize - $seek);
            fseek($fp, $seek);
            $chunk = fread($fp, $readLen);
            $buffer = $chunk . $buffer;
            $lineCount = substr_count($buffer, "\n");
            $pos -= $chunkSize;
        }
        fclose($fp);
        $linesArr = explode("\n", trim($buffer));
        return array_slice($linesArr, max(0, count($linesArr) - $lines));
    }

    /**
     * Anonimizuj adres IP (maskowanie ostatniego oktetu dla IPv4, ostatnich 80 bitów dla IPv6)
     */
    public static function anonymizeIp(string $ip): string
    {
        // IPv4
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            $parts[3] = '0';
            return implode('.', $parts);
        }
        
        // IPv6
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ip);
            // Maskuj ostatnie 5 segmentów (80 bitów)
            for ($i = 3; $i < 8; $i++) {
                if (isset($parts[$i])) {
                    $parts[$i] = '0';
                }
            }
            return implode(':', $parts);
        }
        
        return $ip;
    }

    /**
     * Pobierz dane geolokalizacji z ip-api.com (darmowe, 45 req/min)
     * Zwraca ['country_code' => 'PL', 'city' => 'Warsaw'] lub null przy błędzie
     */
    public static function getGeoLocation(string $ip): ?array
    {
        // Pomiń lokalne IP
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return null;
        }
        
        // HTTPS aby nie wysyłać IP w plaintext
        $url = "https://ip-api.com/json/{$ip}?fields=status,countryCode,city";

        $context = stream_context_create([
            'http' => [
                'timeout' => 2,
                'ignore_errors' => true
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            return null;
        }
        
        $data = json_decode($response, true);
        
        if (!$data || ($data['status'] ?? '') !== 'success') {
            return null;
        }
        
        return [
            'country_code' => $data['countryCode'] ?? null,
            'city' => $data['city'] ?? null
        ];
    }

    /**
     * Oczyść HTML z Trixa - usuń potencjalnie niebezpieczne tagi, zachowaj formatowanie
     */
    public static function sanitizeHtml(string $html): string
    {
        // Dozwolone tagi: b, i, u, strong, em, br, p, div, ul, ol, li, a, blockquote
        $allowed = '<b><i><u><strong><em><br><p><div><ul><ol><li><a><blockquote>';
        $cleaned = strip_tags($html, $allowed);

        // Przeprocesuj WSZYSTKIE tagi <a> (niezależnie od cudzysłowów i dodatkowych atrybutów):
        // wyodrębnij href (double/single/unquoted), zweryfikuj protokół, zrekonstruuj WYŁĄCZNIE z bezpiecznym href.
        // Wszelkie inne atrybuty (onclick, onmouseover, target itp.) są usuwane.
        $cleaned = preg_replace_callback(
            '/<a\b[^>]*>/i',
            static function (array $m): string {
                $tag = $m[0];
                // Wyodrębnij href w dowolnym formacie cytowania (", ', lub bez cudzysłowów)
                if (!preg_match('/\bhref\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]*))/i', $tag, $hrefMatch)) {
                    return '<a>';
                }
                // Tylko jedna gałąź alternacji jest dopasowana — konkatenacja daje właściwą wartość
                $href = ($hrefMatch[1] ?? '') . ($hrefMatch[2] ?? '') . ($hrefMatch[3] ?? '');

                // Usuń białe znaki i zdekoduj encje HTML przed sprawdzeniem protokołu
                $normalized = strtolower(preg_replace('/[\s\x00-\x1f]+/', '', html_entity_decode($href, ENT_QUOTES)));
                if (preg_match('/^(javascript|data|vbscript):/i', $normalized)) {
                    return '<a>';
                }
                if ($href === '') {
                    return '<a>';
                }
                return '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">';
            },
            $cleaned
        );

        // Usuń wszystkie pozostałe atrybuty z innych dozwolonych tagów (z wyjątkiem <a>, który jest już czysty)
        $cleaned = preg_replace('/<(?!a\b)([a-z]+)[^>]*>/i', '<$1>', $cleaned);

        return $cleaned;
    }

    /**
     * Wykryj crawlera Facebooka (facebookexternalhit / Facebot / WhatsApp)
     */
    public static function isFacebookCrawler(?string $userAgent = null): bool
    {
        $ua = $userAgent ?? ($_SERVER['HTTP_USER_AGENT'] ?? '');
        return (bool)preg_match('/facebookexternalhit|facebot|whatsapp/i', $ua);
    }

    /**
     * Ogólne wykrywanie popularnych crawlerów społecznościowych
     * (Facebook, Twitter, LinkedIn, Slack, Telegram) — pomocne na przyszłość
     */
    public static function isSocialCrawler(?string $userAgent = null): bool
    {
        $ua = $userAgent ?? ($_SERVER['HTTP_USER_AGENT'] ?? '');
        return self::isFacebookCrawler($ua)
            || (bool)preg_match('/twitterbot|linkedinbot|slackbot|telegrambot/i', $ua);
    }

    public static function getMainImageUrl(array $galleryImages, array $link, string $basePath): ?string
    {
        $primaryPath = null;
        foreach ($galleryImages as $image) {
            if (!empty($image['is_primary']) && !empty($image['path'])) {
                $primaryPath = (string)$image['path'];
                break;
            }
        }

        if ($primaryPath === null) {
            foreach ($galleryImages as $image) {
                if (!empty($image['path'])) {
                    $primaryPath = (string)$image['path'];
                    break;
                }
            }
        }

        if ($primaryPath === null && !empty($link['og_image'])) {
            $primaryPath = (string)$link['og_image'];
        }

        if ($primaryPath === null || $primaryPath === '') {
            return null;
        }

        return rtrim($basePath, '/') . '/' . ltrim($primaryPath, '/');
    }

    /**
     * Formatuje czas trwania w sekundach do czytelnej postaci
     */
    public static function formatDuration(int $seconds): string
    {
        if ($seconds >= 86400) {
            $days = (int)round($seconds / 86400);
            if ($days === 1) {
                return '1 dzień';
            }
            return $days . ' dni';
        }
        if ($seconds >= 3600) {
            $hours = (int)round($seconds / 3600);
            if ($hours === 1) {
                return '1 godz';
            }
            return $hours . ' godz';
        }
        if ($seconds >= 60) {
            $minutes = (int)round($seconds / 60);
            return $minutes . ' min';
        }
        return $seconds . ' sek';
    }
}