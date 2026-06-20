<?php
declare(strict_types=1);

/**
 * LicenseChecker — weryfikuje klucz licencyjny RedirectCMS z API.
 *
 * Stany:
 *   active         — licencja paid poprawna, brak ograniczeń
 *   demo_active    — licencja DEMO aktywna; baner z info o pozostałych dniach
 *   demo_expired   — licencja DEMO wygasła; natychmiastowa blokada (bez okresu grace)
 *   silent         — licencja nieważna < 24h, nic nie pokazujemy
 *   warning        — licencja nieważna 24–48h, baner na dashboardzie
 *   blocked        — licencja nieważna 48h+, blokada linków + logowania
 *   api_unreachable — API niedostępne, brak ograniczeń (awaria ≠ zły klucz)
 *   no_key         — brak lub pusty klucz licencyjny
 *
 * Cache: klucz 'license_check_cache' w tabeli settings (JSON).
 * Odświeżany co 24h.
 *
 * Tylko odpowiedzi API invalid/domain_mismatch uruchamiają licznik czasu.
 * Timeout sieci → api_unreachable, licznik nie rusza.
 * demo_expired → natychmiastowe blocked bez okresu grace.
 */
class LicenseChecker
{
    private const VERIFY_URL    = 'https://redirectcms.pl/api/verify_license.php';
    private const CACHE_KEY     = 'license_check_cache';
    private const CHECK_INTERVAL = 86400;  // 24h między wywołaniami API

    // Progi czasowe od pierwszej potwierdzonej nieważnej odpowiedzi
    private const WARNING_AFTER = 86400;   // 24h → pokaż baner
    private const BLOCKED_AFTER = 172800;  // 48h → blokada dostępu

    /**
     * Zwraca bieżący stan licencji.
     *
     * @return array{state: string, message: string, since: int|null, support_until: string|null, support_active: bool|null, support_days_left: int|null}
     */
    public static function getStatus(PrefixedPDO $pdo): array
    {
        $key = defined('RCMS_LICENSE_KEY') ? RCMS_LICENSE_KEY : '';

        if (!preg_match('/^[a-f0-9]{64}$/', $key)) {
            return [
                'state'   => 'no_key',
                'message' => 'Brak klucza licencyjnego. Pobierz plik recovery z panelu klienta.',
                'since'   => null,
                'support_until' => null,
                'support_active' => null,
                'support_days_left' => null,
            ];
        }

        $repo  = new SettingsRepository($pdo);
        $cache = self::loadCache($repo);
        $now   = time();

        // Odśwież cache jeśli nieaktualny lub brak
        if ($cache === null || ($now - (int)($cache['checked_at'] ?? 0)) >= self::CHECK_INTERVAL) {
            $cache = self::fetchAndCache($key, $repo, $cache, $now);
        }

        return self::deriveState($cache, $now);
    }

    /**
     * Usuwa cache weryfikacji licencji, wymuszając świeże zapytanie do API
     * przy następnym wywołaniu getStatus(). Wywoływać po zainstalowaniu nowego klucza.
     */
    public static function clearCache(PrefixedPDO $pdo): void
    {
        try {
            $repo = new SettingsRepository($pdo);
            $repo->delete(self::CACHE_KEY);
        } catch (Throwable $e) {
            error_log('[LicenseChecker] Failed to clear cache: ' . $e->getMessage());
        }
    }

    // ── Prywatne ──────────────────────────────────────────────────────────

    private static function loadCache(SettingsRepository $repo): ?array
    {
        $raw = $repo->get(self::CACHE_KEY, null);
        if ($raw === null) {
            return null;
        }

        if (is_array($raw)) {
            return $raw;
        }

        if (!is_string($raw)) {
            return null;
        }

        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }

    private static function fetchAndCache(
        string $key,
        SettingsRepository $repo,
        ?array $prevCache,
        int $now
    ): array {
        // SERVER_NAME jest ustawiany przez konfigurację serwera — nie przez klienta.
        // HTTP_HOST jest nagłówkiem klienta i nie może być używany jako autorytatywne źródło domeny
        // (spreparowany Host: header mógłby zapisać api_unreachable do cache i obejść blokadę logowania).
        // Fallback do HTTP_HOST tylko gdy SERVER_NAME niedostępny (np. CLI, rzadkie konfiguracje).
        $rawHost = $_SERVER['SERVER_NAME'] ?? '';
        if ($rawHost === '') {
            $rawHost = $_SERVER['HTTP_HOST'] ?? '';
        }

        // parse_url obsługuje IPv6 ([::1]) i niestandardowe porty
        $host = strtolower((string)(parse_url('http://' . $rawHost, PHP_URL_HOST) ?? ''));

        // Walidacja hosta tym samym regexem co API — niepoprawny format lub brak domeny
        // = traktuj jak niedostępność sieci, żeby nie eskalować do stanu blocked przez
        // problem konfiguracyjny serwera.
        $unreachableCache = [
            'checked_at'            => $now,
            'status'                => 'api_unreachable',
            'license_invalid_since' => $prevCache['license_invalid_since'] ?? null,
            'domain_bound'          => $prevCache['domain_bound']          ?? null,
            'support_until'         => $prevCache['support_until']         ?? null,
        ];

        if ($host === '' || !preg_match('/^[a-zA-Z0-9.\-]{1,253}$/', $host)) {
            $repo->set(self::CACHE_KEY, json_encode($unreachableCache));
            return $unreachableCache;
        }

        $url = self::VERIFY_URL . '?' . http_build_query(['key' => $key, 'domain' => $host]);

        $ctx  = stream_context_create(['http' => [
            'timeout'       => 5,
            'ignore_errors' => true,
            'method'        => 'GET',
            'header'        => "User-Agent: RedirectCMS-LicenseChecker/1.0\r\n",
        ]]);
        $body = @file_get_contents($url, false, $ctx);

        // API nieosiągalne — zachowaj poprzedni license_invalid_since bez zmian
        if ($body === false) {
            $cache = [
                'checked_at'            => $now,
                'status'                => 'api_unreachable',
                'license_invalid_since' => $prevCache['license_invalid_since'] ?? null,
                'domain_bound'          => $prevCache['domain_bound']          ?? null,
                'support_until'         => $prevCache['support_until']         ?? null,
            ];
            $repo->set(self::CACHE_KEY, json_encode($cache));
            return $cache;
        }

        $data = json_decode($body, true);

        // Zniekształcona odpowiedź — traktuj jak brak sieci
        if (!is_array($data)) {
            $cache = [
                'checked_at'            => $now,
                'status'                => 'api_unreachable',
                'license_invalid_since' => $prevCache['license_invalid_since'] ?? null,
                'domain_bound'          => $prevCache['domain_bound']          ?? null,
                'support_until'         => $prevCache['support_until']         ?? null,
            ];
            $repo->set(self::CACHE_KEY, json_encode($cache));
            return $cache;
        }

        $isValid = (bool)($data['valid'] ?? false);
        $status  = (string)($data['status'] ?? 'invalid_key');

        if ($isValid && $status === 'demo_active') {
            // Licencja DEMO aktywna — resetuj licznik, zapisz datę wygaśnięcia
            $cache = [
                'checked_at'            => $now,
                'status'                => 'demo_active',
                'license_invalid_since' => null,
                'domain_bound'          => $data['domain_bound'] ?? null,
                'support_until'         => null,
                'demo_expires_at'       => $data['expires_at']   ?? null,
                'demo_days_left'        => $data['days_left']     ?? null,
            ];
        } elseif ($isValid) {
            // Licencja paid aktywna — resetuj licznik
            $cache = [
                'checked_at'            => $now,
                'status'                => 'active',
                'license_invalid_since' => null,
                'domain_bound'          => $data['domain_bound'] ?? null,
                'support_until'         => $data['support_until'] ?? ($prevCache['support_until'] ?? null),
                'demo_expires_at'       => null,
                'demo_days_left'        => null,
            ];
        } elseif ($status === 'demo_expired') {
            // Demo wygasło — natychmiastowa blokada, nie uruchamiamy licznika grace
            $cache = [
                'checked_at'            => $now,
                'status'                => 'demo_expired',
                'license_invalid_since' => null,
                'domain_bound'          => $data['domain_bound'] ?? ($prevCache['domain_bound'] ?? null),
                'support_until'         => null,
                'demo_expires_at'       => $data['expires_at']   ?? ($prevCache['demo_expires_at'] ?? null),
                'demo_days_left'        => 0,
            ];
        } else {
            // Weryfikacja nieudana — ustaw license_invalid_since przy pierwszym błędzie
            $prevInvalidSince = $prevCache['license_invalid_since'] ?? null;
            $cache = [
                'checked_at'            => $now,
                'status'                => $status,
                'license_invalid_since' => $prevInvalidSince ?? $now,
                'domain_bound'          => $data['domain_bound'] ?? ($prevCache['domain_bound'] ?? null),
                'support_until'         => $prevCache['support_until'] ?? null,
                'demo_expires_at'       => $prevCache['demo_expires_at'] ?? null,
                'demo_days_left'        => null,
            ];
        }

        $repo->set(self::CACHE_KEY, json_encode($cache));
        return $cache;
    }

    private static function deriveState(array $cache, int $now): array
    {
        $status = $cache['status'] ?? 'api_unreachable';

        $supportUntil = null;
        if (!empty($cache['support_until']) && is_string($cache['support_until'])) {
            $supportUntil = $cache['support_until'];
        }

        $supportActive = null;
        $supportDaysLeft = null;
        if ($supportUntil !== null) {
            $supportTs = strtotime($supportUntil . ' UTC');
            if ($supportTs !== false) {
                $supportActive = ($now <= $supportTs);
                $supportDaysLeft = (int)ceil(max(0, $supportTs - $now) / 86400);
            }
        }

        $demoExpiresAt = $cache['demo_expires_at'] ?? null;
        $demoDaysLeft  = isset($cache['demo_days_left']) ? (int)$cache['demo_days_left'] : null;

        if ($status === 'active') {
            return [
                'state'             => 'active',
                'message'           => '',
                'since'             => null,
                'support_until'     => $supportUntil,
                'support_active'    => $supportActive,
                'support_days_left' => $supportDaysLeft,
                'demo_expires_at'   => null,
                'demo_days_left'    => null,
            ];
        }

        if ($status === 'demo_active') {
            $msg = $demoDaysLeft !== null
                ? 'Pracujesz na licencji DEMO — pozostało <strong>' . $demoDaysLeft . '</strong> ' . self::daysWord($demoDaysLeft) . '.'
                : 'Pracujesz na licencji DEMO.';
            return [
                'state'             => 'demo_active',
                'message'           => $msg,
                'since'             => null,
                'support_until'     => null,
                'support_active'    => null,
                'support_days_left' => null,
                'demo_expires_at'   => $demoExpiresAt,
                'demo_days_left'    => $demoDaysLeft,
            ];
        }

        if ($status === 'demo_expired') {
            return [
                'state'             => 'blocked',
                'message'           => 'Wersja DEMO RedirectCMS wygasła. Kup pełną licencję, aby kontynuować.',
                'since'             => null,
                'support_until'     => null,
                'support_active'    => null,
                'support_days_left' => null,
                'demo_expires_at'   => $demoExpiresAt,
                'demo_days_left'    => 0,
                'is_demo_expired'   => true,
            ];
        }

        if ($status === 'api_unreachable') {
            return [
                'state'             => 'api_unreachable',
                'message'           => '',
                'since'             => null,
                'support_until'     => $supportUntil,
                'support_active'    => $supportActive,
                'support_days_left' => $supportDaysLeft,
                'demo_expires_at'   => $demoExpiresAt,
                'demo_days_left'    => $demoDaysLeft,
            ];
        }

        // invalid / domain_mismatch / not_found — sprawdź ile czasu minęło
        $invalidSince = (int)($cache['license_invalid_since'] ?? $now);
        $age          = $now - $invalidSince;

        if ($age < self::WARNING_AFTER) {
            // 0–24h: cisza, nic nie pokazujemy
            return [
                'state'             => 'silent',
                'message'           => '',
                'since'             => $invalidSince,
                'support_until'     => $supportUntil,
                'support_active'    => $supportActive,
                'support_days_left' => $supportDaysLeft,
                'demo_expires_at'   => null,
                'demo_days_left'    => null,
            ];
        }

        if ($status === 'domain_mismatch') {
            $boundDomain = $cache['domain_bound'] ? ' (przypisany do: ' . $cache['domain_bound'] . ')' : '';
            $msgWarning  = 'Klucz licencyjny jest przypisany do innej domeny' . $boundDomain . '. Skontaktuj się z pomocą techniczną.';
            $msgBlocked  = 'Licencja RedirectCMS jest przypisana do innej domeny' . $boundDomain . '. Skontaktuj się z pomocą techniczną.';
        } else {
            $msgWarning = 'Nie można zweryfikować licencji RedirectCMS. Sprawdź konfigurację lub skontaktuj się z pomocą techniczną.';
            $msgBlocked = 'Licencja RedirectCMS jest nieważna lub wygasła. Skontaktuj się z pomocą techniczną.';
        }

        if ($age < self::BLOCKED_AFTER) {
            // 24–48h: baner ostrzegawczy
            return [
                'state'             => 'warning',
                'message'           => $msgWarning,
                'since'             => $invalidSince,
                'support_until'     => $supportUntil,
                'support_active'    => $supportActive,
                'support_days_left' => $supportDaysLeft,
                'demo_expires_at'   => null,
                'demo_days_left'    => null,
            ];
        }

        // 48h+: pełna blokada
        return [
            'state'             => 'blocked',
            'message'           => $msgBlocked,
            'since'             => $invalidSince,
            'support_until'     => $supportUntil,
            'support_active'    => $supportActive,
            'support_days_left' => $supportDaysLeft,
            'demo_expires_at'   => null,
            'demo_days_left'    => null,
        ];
    }

    private static function daysWord(int $n): string
    {
        $abs = abs($n);
        if ($abs % 100 >= 11 && $abs % 100 <= 14) { return 'dni'; }
        if ($abs % 10 === 1)                        { return 'dzień'; }
        if ($abs % 10 >= 2 && $abs % 10 <= 4)      { return 'dni'; }
        return 'dni';
    }
}
