<?php
declare(strict_types=1);

/**
 * Repozytorium do zarządzania wynikami sprawdzania linków
 */
class LinkHealthCheckRepository
{
    public function __construct(private $pdo) {}

    /**
     * Zapisz wynik sprawdzenia linku
     */
    public function recordCheck(
        int $linkId,
        ?int $httpStatus,
        ?int $responseTimeMs,
        ?string $errorMessage,
        ?string $finalUrl,
        int $redirectCount,
        string $status
    ): int {
        $stmt = $this->pdo->prepare('
            INSERT INTO link_health_checks (link_id, http_status, response_time_ms, error_message, final_url, redirect_count, status)
            VALUES (:lid, :status_code, :time, :error, :final_url, :redirects, :status)
        ');
        $stmt->execute([
            ':lid' => $linkId,
            ':status_code' => $httpStatus,
            ':time' => $responseTimeMs,
            ':error' => $errorMessage,
            ':final_url' => $finalUrl,
            ':redirects' => $redirectCount,
            ':status' => $status,
        ]);

        // Zaktualizuj cache statusu w tabeli links
        $updateStmt = $this->pdo->prepare('
            UPDATE links SET last_health_status = :status, last_health_check_at = NOW() WHERE id = :lid
        ');
        $updateStmt->execute([':status' => $status, ':lid' => $linkId]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Pobierz ostatni wynik sprawdzenia dla linku
     */
    public function getLatestForLink(int $linkId): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM link_health_checks WHERE link_id = :lid ORDER BY checked_at DESC LIMIT 1
        ');
        $stmt->execute([':lid' => $linkId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Pobierz wszystkie uszkodzone linki z detalami ostatniego sprawdzenia
     */
    public function getBrokenLinks(int $limit = 100, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare("
            SELECT l.id, l.slug, l.target_url, l.page_title, l.last_health_status, l.last_health_check_at,
                   h.http_status, h.error_message, h.final_url, h.response_time_ms, h.redirect_count
            FROM links l
            LEFT JOIN link_health_checks h ON h.id = (
                SELECT MAX(id) FROM link_health_checks WHERE link_id = l.id
            )
            WHERE l.last_health_status = 'broken'
            AND l.status = 'published'
            ORDER BY l.last_health_check_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Pobierz wszystkie opublikowane linki z detalami ostatniego sprawdzenia
     */
    public function getAllLinksHealth(int $limit = 1000, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare("
            SELECT l.id, l.slug, l.target_url, l.page_title, l.last_health_status, l.last_health_check_at,
                   h.http_status, h.error_message, h.final_url, h.response_time_ms, h.redirect_count
            FROM links l
            LEFT JOIN link_health_checks h ON h.id = (
                SELECT MAX(id) FROM link_health_checks WHERE link_id = l.id
            )
            WHERE l.status = 'published'
            ORDER BY
                CASE l.last_health_status
                    WHEN 'broken' THEN 0
                    WHEN 'ignored' THEN 3
                    WHEN 'resolved' THEN 2
                    WHEN 'healthy' THEN 4
                    ELSE 1
                END ASC,
                l.last_health_check_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Pobierz linki z konkretnym statusem health
     */
    public function getLinksByHealthStatus(string $status, int $limit = 100, int $offset = 0): array
    {
        if (!in_array($status, ['healthy', 'broken', 'ignored', 'resolved'], true)) {
            throw new InvalidArgumentException('Nieprawidlowy status');
        }

        $stmt = $this->pdo->prepare("
            SELECT l.id, l.slug, l.target_url, l.page_title, l.last_health_status, l.last_health_check_at,
                   h.http_status, h.error_message, h.response_time_ms
            FROM links l
            LEFT JOIN link_health_checks h ON h.id = (
                SELECT MAX(id) FROM link_health_checks WHERE link_id = l.id
            )
            WHERE l.last_health_status = :status
            AND l.status = 'published'
            ORDER BY l.last_health_check_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Zaktualizuj status linku (resolved, ignored)
     */
    public function updateLinkStatus(int $linkId, string $status): void
    {
        if (!in_array($status, ['healthy', 'broken', 'ignored', 'resolved'], true)) {
            throw new InvalidArgumentException('Nieprawidlowy status');
        }

        $stmt = $this->pdo->prepare('UPDATE links SET last_health_status = :status WHERE id = :lid');
        $stmt->execute([':status' => $status, ':lid' => $linkId]);
    }

    /**
     * Pobierz historię sprawdzeń dla linku
     */
    public function getHistoryForLink(int $linkId, int $limit = 20): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM link_health_checks WHERE link_id = :lid ORDER BY checked_at DESC LIMIT :limit
        ');
        $stmt->bindValue(':lid', $linkId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Pobierz podsumowanie statystyk
     */
    public function getSummary(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                COUNT(*) as total_links,
                SUM(CASE WHEN last_health_status = 'healthy' THEN 1 ELSE 0 END) as healthy,
                SUM(CASE WHEN last_health_status = 'broken' THEN 1 ELSE 0 END) as broken,
                SUM(CASE WHEN last_health_status = 'ignored' THEN 1 ELSE 0 END) as ignored,
                SUM(CASE WHEN last_health_status = 'resolved' THEN 1 ELSE 0 END) as resolved,
                SUM(CASE WHEN last_health_status IS NULL THEN 1 ELSE 0 END) as unchecked
            FROM links WHERE status = 'published'
        ");
        $raw = $stmt->fetch();
        $summary = array_map('intval', $raw);

        // Linki niesprawdzane: wygasłe, szkice, w koszu
        $extStmt = $this->pdo->query("
            SELECT
                COALESCE(SUM(CASE WHEN status = 'trashed' THEN 1 ELSE 0 END), 0) as trashed,
                COALESCE(SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END), 0) as draft,
                COALESCE(SUM(CASE WHEN status = 'published' AND expires_at IS NOT NULL AND expires_at <= NOW() THEN 1 ELSE 0 END), 0) as expired
            FROM links
        ");
        $ext = $extStmt->fetch();
        $summary['trashed'] = (int)$ext['trashed'];
        $summary['draft'] = (int)$ext['draft'];
        $summary['expired'] = (int)$ext['expired'];

        // Ignorowane = oznaczone jako ignored + w koszu + wygasłe
        $summary['ignored'] += $summary['trashed'] + $summary['expired'];

        return $summary;
    }

    /**
     * Policz uszkodzone linki
     */
    public function countBroken(): int
    {
        $stmt = $this->pdo->query("
            SELECT COUNT(*) FROM links WHERE last_health_status = 'broken' AND status = 'published'
        ");
        return (int)$stmt->fetchColumn();
    }

    /**
     * Wyczyść starą historię sprawdzeń (zachowaj ostatnie N na link)
     */
    public function cleanOldHistory(int $keepPerLink = 10): int
    {
        // Pobierz ID do zachowania
        $stmt = $this->pdo->prepare("
            SELECT id FROM link_health_checks h1
            WHERE (
                SELECT COUNT(*) FROM link_health_checks h2
                WHERE h2.link_id = h1.link_id AND h2.id >= h1.id
            ) <= :keep
        ");
        $stmt->execute([':keep' => $keepPerLink]);
        $keepIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($keepIds)) {
            return 0;
        }

        // Usuń wszystko poza zachowanymi
        $placeholders = implode(',', array_fill(0, count($keepIds), '?'));
        $deleteStmt = $this->pdo->prepare("DELETE FROM link_health_checks WHERE id NOT IN ($placeholders)");
        $deleteStmt->execute($keepIds);

        return $deleteStmt->rowCount();
    }

    /**
     * Usuń historię sprawdzeń dla linku
     */
    public function deleteForLink(int $linkId): int
    {
        $stmt = $this->pdo->prepare('DELETE FROM link_health_checks WHERE link_id = :lid');
        $stmt->execute([':lid' => $linkId]);
        return $stmt->rowCount();
    }

    /**
     * Pobierz linki do sprawdzenia (aktywne, opublikowane, nie sprawdzane od X godzin)
     */
    public function getLinksToCheck(int $minHoursSinceLastCheck = 24, int $limit = 100): array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, slug, target_url, last_health_status
            FROM links
            WHERE status = 'published'
            AND (
                last_health_check_at IS NULL
                OR last_health_check_at < DATE_SUB(NOW(), INTERVAL :hours HOUR)
            )
            AND (last_health_status IS NULL OR last_health_status != 'ignored')
            ORDER BY (last_health_check_at IS NULL) DESC, last_health_check_at ASC
            LIMIT :limit
        ");
        $stmt->bindValue(':hours', $minHoursSinceLastCheck, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
