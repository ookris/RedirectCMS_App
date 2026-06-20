<?php
declare(strict_types=1);

require_once __DIR__ . '/Database.php';

class SidebarService
{
    public function __construct(
        private readonly PrefixedPDO $pdo,
        private readonly string $basePath = ''
    ) {}

    /**
     * Returns rendered widget data for all configured widgets.
     * Each element: ['type' => string, 'title' => string, 'data' => mixed]
     */
    public function getWidgetsData(array $widgets, array $socialLinks = []): array
    {
        $result = [];
        foreach ($widgets as $widget) {
            $type = $widget['type'] ?? '';
            try {
                $data = match ($type) {
                    'popular_posts' => $this->getPopularPosts(
                        (int)($widget['count'] ?? 5),
                        (string)($widget['period'] ?? 'all'),
                        (int)($widget['category_id'] ?? 0)
                    ),
                    'random_posts' => $this->getRandomPosts((int)($widget['count'] ?? 5)),
                    'tag_cloud'    => $this->getTagCloud((int)($widget['max_tags'] ?? 30)),
                    'categories'   => $this->getCategories(!empty($widget['show_count'])),
                    'social_links' => $socialLinks,
                    'custom_html'  => (string)($widget['html'] ?? ''),
                    default        => null,
                };
            } catch (\Throwable $e) {
                error_log('SidebarService widget error (' . $type . '): ' . $e->getMessage());
                $data = null;
            }
            if ($data === null) {
                continue;
            }
            // Skip empty social links or empty tag/category lists
            if (is_array($data) && empty($data) && in_array($type, ['popular_posts', 'random_posts', 'tag_cloud', 'categories', 'social_links'], true)) {
                continue;
            }
            $result[] = [
                'type'  => $type,
                'title' => (string)($widget['title'] ?? ''),
                'data'  => $data,
            ];
        }
        return $result;
    }

    private function getPopularPosts(int $count, string $period, int $categoryId): array
    {
        $where = [
            "l.status = 'published'",
            "(l.publish_at IS NULL OR l.publish_at <= NOW())",
            "(l.expires_at IS NULL OR l.expires_at > NOW())",
            "l.page_title IS NOT NULL",
            "l.page_title != ''",
        ];
        $params = [];

        if ($period !== 'all') {
            $days = match ($period) {
                'today' => 1,
                '7d'    => 7,
                '30d'   => 30,
                default => 0,
            };
            if ($days > 0) {
                $where[] = "ls.visited_at >= DATE_SUB(NOW(), INTERVAL :days DAY)";
                $params[':days'] = $days;
            }
        }

        if ($categoryId > 0) {
            $where[] = "l.category_id = :category_id";
            $params[':category_id'] = $categoryId;
        }

        $whereStr = implode(' AND ', $where);
        $limit = max(1, min(10, $count));

        $stmt = $this->pdo->prepare(
            "SELECT l.slug, l.page_title, l.og_image, l.created_at, COUNT(ls.id) AS click_count
             FROM " . Database::table('links') . " l
             LEFT JOIN " . Database::table('link_stats') . " ls ON ls.link_id = l.id
             WHERE $whereStr
             GROUP BY l.id, l.slug, l.page_title, l.og_image, l.created_at
             ORDER BY click_count DESC, l.created_at DESC
             LIMIT :limit"
        );
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val, is_int($val) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getRandomPosts(int $count): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT slug, page_title, og_image, created_at
             FROM " . Database::table('links') . "
             WHERE status = 'published'
               AND (publish_at IS NULL OR publish_at <= NOW())
               AND (expires_at IS NULL OR expires_at > NOW())
               AND page_title IS NOT NULL AND page_title != ''
             ORDER BY RAND()
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', max(1, min(10, $count)), \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getTagCloud(int $maxTags): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT t.name, t.slug, COUNT(lt.link_id) AS post_count
             FROM " . Database::table('tags') . " t
             JOIN " . Database::table('link_tags') . " lt ON t.id = lt.tag_id
             JOIN " . Database::table('links') . " l ON lt.link_id = l.id
               AND l.status = 'published'
               AND (l.publish_at IS NULL OR l.publish_at <= NOW())
               AND (l.expires_at IS NULL OR l.expires_at > NOW())
             GROUP BY t.id, t.name, t.slug
             ORDER BY post_count DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', max(5, min(100, $maxTags)), \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getCategories(bool $showCount): array
    {
        if ($showCount) {
            $stmt = $this->pdo->query(
                "SELECT c.name, c.slug, c.color, COUNT(l.id) AS post_count
                 FROM " . Database::table('categories') . " c
                 LEFT JOIN " . Database::table('links') . " l ON c.id = l.category_id
                   AND l.status = 'published'
                   AND (l.publish_at IS NULL OR l.publish_at <= NOW())
                   AND (l.expires_at IS NULL OR l.expires_at > NOW())
                 GROUP BY c.id, c.name, c.slug, c.color
                 ORDER BY c.name ASC"
            );
        } else {
            $stmt = $this->pdo->query(
                "SELECT c.name, c.slug, c.color, 0 AS post_count
                 FROM " . Database::table('categories') . " c
                 ORDER BY c.name ASC"
            );
        }
        return $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];
    }
}
