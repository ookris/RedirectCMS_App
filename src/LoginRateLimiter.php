<?php
declare(strict_types=1);

/**
 * Rate limiter dla prób logowania oparty na bazie danych.
 *
 * Przechowuje liczbę nieudanych prób per IP-hash w tabeli `login_attempts`,
 * dzięki czemu blokada działa niezależnie od sesji PHP — atakujący
 * nie może obejść jej przez usunięcie ciasteczka sesji.
 */
class LoginRateLimiter
{
    private const MAX_ATTEMPTS = 5;
    private const WINDOW_SECONDS = 900;  // 15 minut
    private const BLOCK_SECONDS  = 900;  // 15 minut blokady

    public function __construct(private PrefixedPDO $pdo) {}

    /**
     * Sprawdza czy IP jest aktualnie zablokowane.
     * Zwraca komunikat błędu jeśli zablokowane, null jeśli OK.
     */
    public function check(string $ip): ?string
    {
        $hash = hash('sha256', $ip);

        $stmt = $this->pdo->prepare(
            'SELECT attempts, first_attempt_at, blocked_until FROM login_attempts WHERE ip_hash = ?'
        );
        $stmt->execute([$hash]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $now = time();

        // Aktywna blokada
        if ($row['blocked_until'] !== null && (int)$row['blocked_until'] > $now) {
            $remaining = (int)ceil(((int)$row['blocked_until'] - $now) / 60);
            return "Zbyt wiele nieudanych prób logowania. Spróbuj ponownie za {$remaining} minut.";
        }

        // Okno wygasło — resetuj rekord
        if ($now - (int)$row['first_attempt_at'] > self::WINDOW_SECONDS) {
            $this->reset($ip);
            return null;
        }

        // Przekroczona liczba prób (bez blokady w DB — ustaw ją teraz)
        if ((int)$row['attempts'] >= self::MAX_ATTEMPTS) {
            $blockedUntil = $now + self::BLOCK_SECONDS;
            $this->pdo->prepare('UPDATE login_attempts SET blocked_until = ? WHERE ip_hash = ?')
                ->execute([$blockedUntil, $hash]);
            return 'Zbyt wiele nieudanych prób logowania. Spróbuj ponownie za 15 minut.';
        }

        return null;
    }

    /**
     * Rejestruje nieudaną próbę logowania dla danego IP.
     */
    public function record(string $ip): void
    {
        $hash = hash('sha256', $ip);
        $now  = time();

        $stmt = $this->pdo->prepare(
            'SELECT attempts, first_attempt_at FROM login_attempts WHERE ip_hash = ?'
        );
        $stmt->execute([$hash]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            $this->pdo->prepare(
                'INSERT INTO login_attempts (ip_hash, attempts, first_attempt_at, blocked_until) VALUES (?, 1, ?, NULL)'
            )->execute([$hash, $now]);
            return;
        }

        // Okno wygasło — zacznij od nowa
        if ($now - (int)$row['first_attempt_at'] > self::WINDOW_SECONDS) {
            $this->pdo->prepare(
                'UPDATE login_attempts SET attempts = 1, first_attempt_at = ?, blocked_until = NULL WHERE ip_hash = ?'
            )->execute([$now, $hash]);
            return;
        }

        $newAttempts  = (int)$row['attempts'] + 1;
        $blockedUntil = $newAttempts >= self::MAX_ATTEMPTS ? $now + self::BLOCK_SECONDS : null;

        $this->pdo->prepare(
            'UPDATE login_attempts SET attempts = ?, blocked_until = ? WHERE ip_hash = ?'
        )->execute([$newAttempts, $blockedUntil, $hash]);
    }

    /**
     * Resetuje licznik prób dla danego IP (po udanym logowaniu).
     */
    public function reset(string $ip): void
    {
        $hash = hash('sha256', $ip);
        $this->pdo->prepare('DELETE FROM login_attempts WHERE ip_hash = ?')->execute([$hash]);
    }

    /**
     * Usuwa wygasłe rekordy starsze niż 24 godziny.
     * Wywoływane sporadycznie (np. z crona) aby trzymać tabelę czystą.
     */
    public function cleanExpired(): void
    {
        $cutoff = time() - 86400; // 24 godziny
        $this->pdo->prepare(
            'DELETE FROM login_attempts WHERE first_attempt_at < ? AND (blocked_until IS NULL OR blocked_until < ?)'
        )->execute([$cutoff, time()]);
    }
}
