<?php
declare(strict_types=1);

class ReactionRepository
{
    public const VALID_TYPES = ['happy', 'love', 'laugh', 'surprised', 'cry', 'anger'];

    public function __construct(private PrefixedPDO $pdo) {}

    /**
     * Oddaj głos. Jeśli ten sam użytkownik głosował w ciągu 24h:
     *  – ta sama reakcja → usuwa głos (toggle off)
     *  – inna reakcja    → zmienia głos
     *
     * @return array{ok: bool, my_reaction: string|null, counts: array<string,int>}
     */
    public function vote(int $linkId, string $reactionType, string $ipHash): array
    {
        if (!in_array($reactionType, self::VALID_TYPES, true)) {
            return ['ok' => false, 'error' => 'invalid_type', 'my_reaction' => null, 'counts' => []];
        }

        // Blokada aplikacyjna MySQL serializująca "sprawdź, potem zapisz" dla tej
        // pary (link, IP) — bez niej dwa równoległe żądania mogłyby oba odczytać
        // "brak głosu" i oba wstawić wiersz, zawyżając licznik reakcji.
        $lockName = 'rxn:' . md5($linkId . ':' . $ipHash);
        $lockStmt = $this->pdo->prepare('SELECT GET_LOCK(?, 5)');
        $lockStmt->execute([$lockName]);
        $acquired = (bool)$lockStmt->fetchColumn();

        if (!$acquired) {
            return ['ok' => false, 'error' => 'busy', 'my_reaction' => null, 'counts' => $this->getCounts($linkId)];
        }

        try {
            $existing = $this->pdo->prepare(
                'SELECT id, reaction_type FROM ' . Database::table('link_reactions') .
                ' WHERE link_id = :lid AND ip_hash = :ip AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)'
            );
            $existing->execute([':lid' => $linkId, ':ip' => $ipHash]);
            $row = $existing->fetch(\PDO::FETCH_ASSOC);

            if ($row) {
                if ($row['reaction_type'] === $reactionType) {
                    // Toggle off — usuń głos
                    $del = $this->pdo->prepare(
                        'DELETE FROM ' . Database::table('link_reactions') . ' WHERE id = :id'
                    );
                    $del->execute([':id' => $row['id']]);
                    return ['ok' => true, 'my_reaction' => null, 'counts' => $this->getCounts($linkId)];
                }
                // Zmiana reakcji
                $upd = $this->pdo->prepare(
                    'UPDATE ' . Database::table('link_reactions') .
                    ' SET reaction_type = :rt, created_at = NOW() WHERE id = :id'
                );
                $upd->execute([':rt' => $reactionType, ':id' => $row['id']]);
            } else {
                $ins = $this->pdo->prepare(
                    'INSERT INTO ' . Database::table('link_reactions') .
                    ' (link_id, reaction_type, ip_hash) VALUES (:lid, :rt, :ip)'
                );
                $ins->execute([':lid' => $linkId, ':rt' => $reactionType, ':ip' => $ipHash]);
            }

            return ['ok' => true, 'my_reaction' => $reactionType, 'counts' => $this->getCounts($linkId)];
        } finally {
            $this->pdo->prepare('SELECT RELEASE_LOCK(?)')->execute([$lockName]);
        }
    }

    /** Liczniki reakcji dla jednego posta. */
    public function getCounts(int $linkId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT reaction_type, COUNT(*) AS cnt FROM ' . Database::table('link_reactions') .
            ' WHERE link_id = :lid GROUP BY reaction_type'
        );
        $stmt->execute([':lid' => $linkId]);

        $counts = array_fill_keys(self::VALID_TYPES, 0);
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $r) {
            $counts[$r['reaction_type']] = (int)$r['cnt'];
        }
        return $counts;
    }

    /** Bieżąca reakcja danego użytkownika (w oknie 24h). */
    public function getCurrentVote(int $linkId, string $ipHash): ?string
    {
        $stmt = $this->pdo->prepare(
            'SELECT reaction_type FROM ' . Database::table('link_reactions') .
            ' WHERE link_id = :lid AND ip_hash = :ip AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)'
        );
        $stmt->execute([':lid' => $linkId, ':ip' => $ipHash]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? (string)$row['reaction_type'] : null;
    }

    /**
     * Zbiorcze liczniki dla listy postów (używane w panelu admina).
     * Zwraca: [ link_id => [ reaction_type => count, ... ], ... ]
     */
    public function getCountsForLinks(array $linkIds): array
    {
        if (empty($linkIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($linkIds), '?'));
        $stmt = $this->pdo->prepare(
            'SELECT link_id, reaction_type, COUNT(*) AS cnt FROM ' . Database::table('link_reactions') .
            ' WHERE link_id IN (' . $placeholders . ') GROUP BY link_id, reaction_type'
        );
        $stmt->execute(array_values($linkIds));

        $result = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $r) {
            $lid = (int)$r['link_id'];
            if (!isset($result[$lid])) {
                $result[$lid] = array_fill_keys(self::VALID_TYPES, 0);
            }
            $result[$lid][$r['reaction_type']] = (int)$r['cnt'];
        }
        return $result;
    }

    /** Łączna liczba głosów dla jednego posta. */
    public function getTotalVotes(int $linkId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM ' . Database::table('link_reactions') . ' WHERE link_id = :lid'
        );
        $stmt->execute([':lid' => $linkId]);
        return (int)$stmt->fetchColumn();
    }

}
