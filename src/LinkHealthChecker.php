<?php
declare(strict_types=1);

/**
 * Serwis do sprawdzania dostępności linków docelowych
 */
class LinkHealthChecker
{
    private PrefixedPDO $pdo;
    private LinkHealthCheckRepository $healthRepo;

    // Konfiguracja
    private int $timeout = 10;
    private int $connectTimeout = 5;
    private int $maxRedirects = 5;

    // Statusy HTTP uznawane za OK
    private array $healthyStatuses = [200, 201, 202, 204, 301, 302, 303, 307, 308];

    public function __construct(PrefixedPDO $pdo)
    {
        $this->pdo = $pdo;
        $this->healthRepo = new LinkHealthCheckRepository($pdo);
    }

    /**
     * Sprawdź linki w batch'ach (kolejkowanie).
     * @param int $batchSize Ile linków sprawdzić w jednym wywołaniu (0 = wszystkie)
     */
    public function checkAllLinks(int $batchSize = 0): array
    {
        // Pobierz opublikowane linki do sprawdzenia (pomijając ignored),
        // priorytetowo: niesprawdzone najpierw, potem najdawniej sprawdzane
        $sql = "
            SELECT id, slug, target_url, last_health_status
            FROM links
            WHERE status = 'published'
            AND (last_health_status IS NULL OR last_health_status != 'ignored')
            ORDER BY last_health_check_at IS NULL DESC, last_health_check_at ASC
        ";

        if ($batchSize > 0) {
            $sql .= " LIMIT :limit";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':limit', $batchSize, \PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $this->pdo->query($sql);
        }
        $links = $stmt->fetchAll();

        // Policz pominięte (ignored)
        $skippedStmt = $this->pdo->query("
            SELECT COUNT(*) FROM links WHERE status = 'published' AND last_health_status = 'ignored'
        ");
        $skippedCount = (int)$skippedStmt->fetchColumn();

        // Policz ile linków jeszcze czeka (jeśli batch)
        $remainingCount = 0;
        if ($batchSize > 0) {
            $totalStmt = $this->pdo->query("
                SELECT COUNT(*) FROM links WHERE status = 'published' AND (last_health_status IS NULL OR last_health_status != 'ignored')
            ");
            $totalToCheck = (int)$totalStmt->fetchColumn();
            $remainingCount = max(0, $totalToCheck - count($links));
        }

        $results = [
            'checked' => 0, 'healthy' => 0, 'broken' => 0,
            'skipped' => $skippedCount, 'remaining' => $remainingCount, 'errors' => [],
        ];

        foreach ($links as $link) {
            $result = $this->checkSingleLink($link);
            $results['checked']++;

            if ($result['status'] === 'healthy') {
                $results['healthy']++;
            } elseif ($result['status'] === 'broken') {
                $results['broken']++;
                if ($result['error']) {
                    $results['errors'][] = [
                        'link_id' => $link['id'],
                        'slug' => $link['slug'],
                        'error' => $result['error'],
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Sprawdź pojedynczy link i zapisz wynik
     */
    public function checkSingleLink(array $link): array
    {
        $url = $link['target_url'] ?? '';
        if (empty($url)) {
            return ['status' => 'broken', 'error' => 'Brak URL docelowego'];
        }

        $startTime = microtime(true);
        $result = $this->performCheck($url);
        $responseTime = (int)((microtime(true) - $startTime) * 1000);

        // Określ status
        $status = 'broken';
        if ($result['success'] && in_array($result['http_status'], $this->healthyStatuses, true)) {
            $status = 'healthy';
        }

        // Zapisz wynik sprawdzenia
        $this->healthRepo->recordCheck(
            (int)$link['id'],
            $result['http_status'],
            $responseTime,
            $result['error'],
            $result['final_url'],
            $result['redirect_count'],
            $status
        );

        return [
            'status' => $status,
            'http_status' => $result['http_status'],
            'response_time' => $responseTime,
            'error' => $result['error'],
            'final_url' => $result['final_url'],
        ];
    }

    /**
     * Wykonaj sprawdzenie HTTP - najpierw HEAD, potem GET jako fallback
     */
    private function performCheck(string $url): array
    {
        // Walidacja URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return [
                'success' => false,
                'http_status' => null,
                'final_url' => null,
                'redirect_count' => 0,
                'error' => 'Nieprawidlowy format URL',
            ];
        }

        // Spróbuj HEAD najpierw (szybsze, mniej danych)
        $result = $this->httpRequest($url, 'HEAD');

        // Jeśli HEAD zwrócił 405 (Method Not Allowed) lub błąd, spróbuj GET
        if (!$result['success'] || $result['http_status'] === 405) {
            $result = $this->httpRequest($url, 'GET');
        }

        return $result;
    }

    /**
     * Sprawdź, czy URL wskazuje na adres publicznie routowalny — blokuje SSRF
     * do sieci prywatnych/loopback/link-local (w tym metadanych chmury) oraz
     * schematy inne niż http/https.
     */
    private function isPubliclyRoutableUrl(string $url): bool
    {
        $parts = parse_url($url);
        if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
            return false;
        }
        if (!in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return false;
        }

        $host = $parts['host'];

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
        }

        $ips = @gethostbynamel($host) ?: [];
        if (empty($ips)) {
            $records = @dns_get_record($host, DNS_AAAA) ?: [];
            $ips = array_column($records, 'ipv6');
        }
        if (empty($ips)) {
            // Nie udało się rozwiązać hosta — traktuj jako niebezpieczny.
            return false;
        }

        foreach ($ips as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Rozwiąż wartość nagłówka Location (może być względna) względem bieżącego URL.
     */
    private function resolveRedirectUrl(string $base, string $location): ?string
    {
        $location = trim($location);
        if ($location === '') {
            return null;
        }
        if (filter_var($location, FILTER_VALIDATE_URL)) {
            return $location;
        }

        $baseParts = parse_url($base);
        if (!$baseParts || empty($baseParts['scheme']) || empty($baseParts['host'])) {
            return null;
        }
        $origin = $baseParts['scheme'] . '://' . $baseParts['host']
            . (isset($baseParts['port']) ? ':' . $baseParts['port'] : '');

        if (str_starts_with($location, '/')) {
            return $origin . $location;
        }

        $basePath = $baseParts['path'] ?? '/';
        $dir = rtrim(dirname($basePath), '/');
        return $origin . $dir . '/' . $location;
    }

    /**
     * Wykonaj request HTTP używając cURL. Przekierowania są śledzone ręcznie
     * (CURLOPT_FOLLOWLOCATION wyłączony), tak aby każdy kolejny adres w łańcuchu
     * przekierowań również przeszedł walidację isPubliclyRoutableUrl() — bez tego
     * atakujący mógłby ominąć filtr wstępny przekierowując z zewnętrznego adresu
     * na wewnętrzny.
     */
    private function httpRequest(string $url, string $method): array
    {
        if (!function_exists('curl_init')) {
            return [
                'success' => false,
                'http_status' => null,
                'final_url' => null,
                'redirect_count' => 0,
                'error' => 'Rozszerzenie cURL nie jest dostępne',
            ];
        }

        $originalUrl = $url;
        $currentUrl = $url;
        $redirectCount = 0;

        while (true) {
            if (!$this->isPubliclyRoutableUrl($currentUrl)) {
                return [
                    'success' => false,
                    'http_status' => null,
                    'final_url' => null,
                    'redirect_count' => $redirectCount,
                    'error' => 'Zablokowany adres docelowy (sieć wewnętrzna lub zarezerwowana)',
                ];
            }

            $ch = curl_init();

            curl_setopt_array($ch, [
                CURLOPT_URL => $currentUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_TIMEOUT => $this->timeout,
                CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_USERAGENT => 'RedirectCMS Link Checker/1.0 (+https://github.com/redirectcms)',
                CURLOPT_NOBODY => ($method === 'HEAD'),
                CURLOPT_HTTPHEADER => [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language: pl,en;q=0.5',
                ],
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);

            if ($errno !== 0) {
                return [
                    'success' => false,
                    'http_status' => null,
                    'final_url' => null,
                    'redirect_count' => $redirectCount,
                    'error' => $this->getCurlErrorMessage($errno, $error),
                ];
            }

            $isRedirect = in_array($httpCode, [301, 302, 303, 307, 308], true);
            if ($isRedirect && $redirectCount < $this->maxRedirects) {
                $headers = is_string($response) ? substr($response, 0, $headerSize) : '';
                if (preg_match('/^Location:\s*(.+)$/mi', $headers, $m)) {
                    $nextUrl = $this->resolveRedirectUrl($currentUrl, $m[1]);
                    if ($nextUrl !== null) {
                        $currentUrl = $nextUrl;
                        $redirectCount++;
                        continue;
                    }
                }
            }

            return [
                'success' => true,
                'http_status' => $httpCode,
                'final_url' => $currentUrl !== $originalUrl ? $currentUrl : null,
                'redirect_count' => $redirectCount,
                'error' => null,
            ];
        }
    }

    /**
     * Przetłumacz kod błędu cURL na czytelny komunikat
     */
    private function getCurlErrorMessage(int $errno, string $error): string
    {
        $messages = [
            CURLE_OPERATION_TIMEDOUT => 'Timeout - serwer nie odpowiada',
            CURLE_COULDNT_RESOLVE_HOST => 'Nie mozna rozwiazac domeny DNS',
            CURLE_COULDNT_CONNECT => 'Nie mozna polaczyc z serwerem',
            CURLE_SSL_CONNECT_ERROR => 'Błąd połączenia SSL/TLS',
            CURLE_SSL_CERTPROBLEM => 'Problem z certyfikatem SSL',
            CURLE_SSL_CIPHER => 'Błąd szyfrowania SSL',
            CURLE_SSL_CACERT => 'Nieznany certyfikat CA',
            CURLE_TOO_MANY_REDIRECTS => 'Zbyt wiele przekierowan',
            CURLE_GOT_NOTHING => 'Serwer nie zwrocil zadnych danych',
        ];

        return $messages[$errno] ?? "Błąd połączenia ($errno): $error";
    }

    /**
     * Pobierz czytelny opis statusu HTTP
     */
    public static function getHttpStatusDescription(int $status): string
    {
        $descriptions = [
            200 => 'OK',
            201 => 'Utworzono',
            204 => 'Brak zawartości',
            301 => 'Przeniesiono na stale',
            302 => 'Znaleziono (przekierowanie)',
            303 => 'Zobacz inne',
            307 => 'Tymczasowe przekierowanie',
            308 => 'Stale przekierowanie',
            400 => 'Błędne żądanie',
            401 => 'Wymagana autoryzacja',
            403 => 'Dostep zabroniony',
            404 => 'Nie znaleziono',
            405 => 'Metoda niedozwolona',
            408 => 'Timeout zadania',
            410 => 'Zasób usunięty',
            429 => 'Zbyt wiele żądań',
            500 => 'Błąd serwera',
            502 => 'Błędna brama',
            503 => 'Usługa niedostępna',
            504 => 'Timeout bramy',
        ];

        return $descriptions[$status] ?? "Status $status";
    }

    /**
     * Sprawdź czy status HTTP oznacza problem
     */
    public static function isErrorStatus(int $status): bool
    {
        return $status >= 400;
    }
}
