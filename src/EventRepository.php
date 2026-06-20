<?php

class EventRepository
{
    public function __construct(private $pdo) {}

    public function log(int $linkId, string $type, ?string $ip, ?string $ua, ?string $referer): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO events(link_id, event_type, ip, user_agent, referer) VALUES(:lid, :t, :ip, :ua, :ref)');
        $stmt->execute([':lid' => $linkId, ':t' => $type, ':ip' => $ip, ':ua' => $ua, ':ref' => $referer]);
    }

    public function countsForLink(int $linkId): array
    {
        $stmt = $this->pdo->prepare('SELECT event_type, COUNT(*) as cnt FROM events WHERE link_id = :lid GROUP BY event_type');
        $stmt->execute([':lid' => $linkId]);
        $rows = $stmt->fetchAll();
        $out = ['click' => 0, 'redirect' => 0];
        foreach ($rows as $r) {
            $out[$r['event_type']] = (int)$r['cnt'];
        }
        return $out;
    }
}
