<?php
declare(strict_types=1);

/**
 * Rate limiter dla formularza kontaktowego oparty na bazie danych.
 *
 * Przechowuje liczbę wysłanych wiadomości per IP-hash w tabeli `login_attempts`.
 * Klucz ma format: "c_" + pierwsze 62 znaki SHA-256(IP) = dokładnie 64 znaki,
 * co mieści się w CHAR(64) tabeli login_attempts bez truncacji.
 * Prefiks "c_" jednoznacznie odróżnia rekordy kontaktowe od loginowych
 * (hash logowania to czysty hex [0-9a-f], nigdy nie zaczyna się od "c_").
 * Dzięki temu limit działa niezależnie od sesji PHP — bot który nie utrzymuje
 * ciasteczka sesji nie może ominąć limitu przez restart sesji.
 */
class ContactRateLimiter
{
    private const MAX_ATTEMPTS   = 3;
    private const WINDOW_SECONDS = 3600; // 1 godzina

    public function __construct(private PrefixedPDO $pdo) {}

    private function hash(string $ip): string
    {
        // 'c_' (2 chars) + 62 hex chars = 64 chars — fits exactly in CHAR(64).
        return 'c_' . substr(hash('sha256', $ip), 0, 62);
    }

    /**
     * Sprawdza czy IP może wysłać kolejną wiadomość.
     */
    public function isAllowed(string $ip): bool
    {
        $hash = $this->hash($ip);
        $now  = time();

        $stmt = $this->pdo->prepare(
            'SELECT attempts, first_attempt_at FROM login_attempts WHERE ip_hash = ?'
        );
        $stmt->execute([$hash]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return true;
        }

        // Okno czasowe wygasło — reset i zezwól
        if ($now - (int)$row['first_attempt_at'] > self::WINDOW_SECONDS) {
            $this->pdo->prepare('DELETE FROM login_attempts WHERE ip_hash = ?')
                ->execute([$hash]);
            return true;
        }

        return (int)$row['attempts'] < self::MAX_ATTEMPTS;
    }

    /**
     * Rejestruje wysłanie wiadomości przez dane IP.
     * Wywoływać po walidacji formularza, niezależnie od powodzenia wysyłki emaila.
     */
    public function record(string $ip): void
    {
        $hash = $this->hash($ip);
        $now  = time();
        $windowStart = $now - self::WINDOW_SECONDS;

        // Atomowy upsert — patrz komentarz w LoginRateLimiter::record() o powodzie.
        $this->pdo->prepare(
            'INSERT INTO login_attempts (ip_hash, attempts, first_attempt_at, blocked_until)
             VALUES (?, 1, ?, NULL)
             ON DUPLICATE KEY UPDATE
                 attempts = IF(first_attempt_at < ?, 1, attempts + 1),
                 first_attempt_at = IF(first_attempt_at < ?, ?, first_attempt_at)'
        )->execute([$hash, $now, $windowStart, $windowStart, $now]);
    }

    /**
     * Usuwa przedawnione wpisy z tabeli login_attempts dla rekordów formularza kontaktowego.
     * Wywoływać z crona, aby rekordy "c_*" nie gromadziły się bezterminowo.
     */
    public function cleanExpired(): void
    {
        $threshold = time() - self::WINDOW_SECONDS;
        // ESCAPE '\\' makes the backslash-as-escape explicit (MySQL default, but stated for clarity).
        // Pattern 'c\_%' matches the literal prefix 'c_' followed by any characters.
        $this->pdo->prepare(
            "DELETE FROM login_attempts WHERE ip_hash LIKE ? ESCAPE '\\\\' AND first_attempt_at < ?"
        )->execute(['c\_%', $threshold]);

        // Clean up legacy records written with the old 'contact_' prefix (before the hash format
        // was changed to 'c_' to fit within CHAR(64)). These were truncated/broken at INSERT time,
        // so removing them is always safe — no active rate-limiting relies on them.
        $this->pdo->prepare(
            "DELETE FROM login_attempts WHERE ip_hash LIKE ? ESCAPE '\\\\'"
        )->execute(['contact\_%']);
    }
}
