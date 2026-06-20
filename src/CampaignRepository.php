<?php

class CampaignRepository
{
    public function __construct(private $pdo) {}

    public function listAll(): array
    {
        $stmt = $this->pdo->query('
            SELECT c.*,
                   COUNT(cl.link_id) AS link_count
            FROM campaigns c
            LEFT JOIN campaign_links cl ON cl.campaign_id = c.id
            GROUP BY c.id
            ORDER BY c.name ASC
        ');
        return $stmt->fetchAll();
    }

    public function count(): int
    {
        return (int)$this->pdo->query('SELECT COUNT(*) FROM campaigns')->fetchColumn();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM campaigns WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(string $name, string $color = '#2196F3', ?string $description = null): int
    {
        $name = trim($name);
        if ($name === '') {
            throw new InvalidArgumentException('Nazwa kampanii jest wymagana');
        }
        if ($this->existsByName($name)) {
            throw new InvalidArgumentException('Kampania o takiej nazwie już istnieje');
        }

        $stmt = $this->pdo->prepare('INSERT INTO campaigns (name, color, description) VALUES (:n, :c, :d)');
        $stmt->execute([':n' => $name, ':c' => $color, ':d' => $description]);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, string $name, string $color = '#2196F3', ?string $description = null): void
    {
        $name = trim($name);
        if ($name === '') {
            throw new InvalidArgumentException('Nazwa kampanii jest wymagana');
        }
        if ($this->existsByName($name, $id)) {
            throw new InvalidArgumentException('Kampania o takiej nazwie już istnieje');
        }

        $stmt = $this->pdo->prepare('UPDATE campaigns SET name = :n, color = :c, description = :d WHERE id = :id');
        $stmt->execute([':n' => $name, ':c' => $color, ':d' => $description, ':id' => $id]);
    }

    public function delete(int $id): void
    {
        // Remove link associations first
        $stmt = $this->pdo->prepare('DELETE FROM campaign_links WHERE campaign_id = :id');
        $stmt->execute([':id' => $id]);

        $stmt = $this->pdo->prepare('DELETE FROM campaigns WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public function existsByName(string $name, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $stmt = $this->pdo->prepare('SELECT 1 FROM campaigns WHERE name = :n AND id != :id');
            $stmt->execute([':n' => $name, ':id' => $excludeId]);
        } else {
            $stmt = $this->pdo->prepare('SELECT 1 FROM campaigns WHERE name = :n');
            $stmt->execute([':n' => $name]);
        }
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Get links belonging to a campaign with stats.
     */
    public function getLinksForCampaign(int $campaignId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT l.id, l.slug, l.page_title, l.target_url, l.status, l.created_at,
                   c.name AS category_name, c.color AS category_color
            FROM campaign_links cl
            INNER JOIN links l ON l.id = cl.link_id
            LEFT JOIN categories c ON c.id = l.category_id
            WHERE cl.campaign_id = :cid
            ORDER BY cl.id DESC
        ');
        $stmt->execute([':cid' => $campaignId]);
        return $stmt->fetchAll();
    }

    /**
     * Set links for a campaign (replace all).
     */
    public function setLinksForCampaign(int $campaignId, array $linkIds): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM campaign_links WHERE campaign_id = :cid');
        $stmt->execute([':cid' => $campaignId]);

        if (!empty($linkIds)) {
            $insert = $this->pdo->prepare('INSERT INTO campaign_links (campaign_id, link_id) VALUES (:cid, :lid)');
            foreach ($linkIds as $linkId) {
                $insert->execute([':cid' => $campaignId, ':lid' => (int)$linkId]);
            }
        }
    }

    /**
     * Add a link to a campaign.
     */
    public function addLinkToCampaign(int $campaignId, int $linkId): void
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM campaign_links WHERE campaign_id = :cid AND link_id = :lid');
        $stmt->execute([':cid' => $campaignId, ':lid' => $linkId]);
        if (!$stmt->fetchColumn()) {
            $stmt = $this->pdo->prepare('INSERT INTO campaign_links (campaign_id, link_id) VALUES (:cid, :lid)');
            $stmt->execute([':cid' => $campaignId, ':lid' => $linkId]);
        }
    }

    /**
     * Remove a link from a campaign.
     */
    public function removeLinkFromCampaign(int $campaignId, int $linkId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM campaign_links WHERE campaign_id = :cid AND link_id = :lid');
        $stmt->execute([':cid' => $campaignId, ':lid' => $linkId]);
    }

    /**
     * Get campaign IDs for a specific link.
     */
    public function getCampaignsForLink(int $linkId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT c.* FROM campaigns c
            INNER JOIN campaign_links cl ON cl.campaign_id = c.id
            WHERE cl.link_id = :lid
            ORDER BY c.name ASC
        ');
        $stmt->execute([':lid' => $linkId]);
        return $stmt->fetchAll();
    }

    /**
     * Get aggregated stats for a campaign.
     */
    public function getCampaignStats(int $campaignId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT
                COUNT(DISTINCT cl.link_id) AS total_links,
                COALESCE(SUM(s.total), 0) AS total_clicks,
                COALESCE(SUM(s.today), 0) AS today_clicks
            FROM campaign_links cl
            LEFT JOIN (
                SELECT link_id,
                       COUNT(*) AS total,
                       SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) AS today
                FROM events
                GROUP BY link_id
            ) s ON s.link_id = cl.link_id
            WHERE cl.campaign_id = :cid
        ');
        $stmt->execute([':cid' => $campaignId]);
        return $stmt->fetch() ?: ['total_links' => 0, 'total_clicks' => 0, 'today_clicks' => 0];
    }
}
