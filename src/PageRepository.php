<?php
declare(strict_types=1);

class PageRepository
{
    // Zarezerwowane slugi — obsługiwane przez dedykowany routing
    private const RESERVED_SLUGS = ['contact', 'blog', 'rss', 'feed', 'sitemap', 'cron', 'admin'];

    public function __construct(private readonly PrefixedPDO $pdo) {}

    public function ensureTable(): void
    {
        $this->pdo->exec('
            CREATE TABLE IF NOT EXISTS custom_pages (
                id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
                title       VARCHAR(255) NOT NULL,
                slug        VARCHAR(191) NOT NULL,
                content_html MEDIUMTEXT,
                content_js  TEXT,
                meta_title  VARCHAR(255),
                meta_description VARCHAR(500),
                status      ENUM(\'published\',\'draft\') NOT NULL DEFAULT \'draft\',
                show_in_nav TINYINT(1) NOT NULL DEFAULT 0,
                use_theme   TINYINT(1) NOT NULL DEFAULT 1,
                created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY slug (slug)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    public function listAll(bool $publishedOnly = false): array
    {
        $sql = 'SELECT * FROM custom_pages';
        if ($publishedOnly) {
            $sql .= " WHERE status = 'published'";
        }
        $sql .= ' ORDER BY title ASC';
        return $this->pdo->query($sql)->fetchAll();
    }

    public function count(): int
    {
        return (int)$this->pdo->query('SELECT COUNT(*) FROM custom_pages')->fetchColumn();
    }

    public function getNavPages(): array
    {
        $stmt = $this->pdo->query("SELECT id, title, slug FROM custom_pages WHERE status = 'published' AND show_in_nav = 1 ORDER BY title ASC");
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM custom_pages WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM custom_pages WHERE slug = :slug AND status = 'published'");
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function slugExists(string $slug, int $excludeId = 0): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM custom_pages WHERE slug = :slug AND id != :id');
        $stmt->execute([':slug' => $slug, ':id' => $excludeId]);
        return (bool)$stmt->fetchColumn();
    }

    public function isReservedSlug(string $slug): bool
    {
        return in_array(strtolower($slug), self::RESERVED_SLUGS, true);
    }

    public function create(
        string $title,
        string $slug,
        string $contentHtml = '',
        string $contentJs = '',
        ?string $metaTitle = null,
        ?string $metaDescription = null,
        string $status = 'draft',
        bool $showInNav = false,
        bool $useTheme = true
    ): int {
        $stmt = $this->pdo->prepare('
            INSERT INTO custom_pages (title, slug, content_html, content_js, meta_title, meta_description, status, show_in_nav, use_theme)
            VALUES (:title, :slug, :html, :js, :mt, :md, :status, :nav, :theme)
        ');
        $stmt->execute([
            ':title'  => $title,
            ':slug'   => $slug,
            ':html'   => $contentHtml,
            ':js'     => $contentJs,
            ':mt'     => $metaTitle,
            ':md'     => $metaDescription,
            ':status' => $status,
            ':nav'    => $showInNav ? 1 : 0,
            ':theme'  => $useTheme ? 1 : 0,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(
        int $id,
        string $title,
        string $slug,
        string $contentHtml = '',
        string $contentJs = '',
        ?string $metaTitle = null,
        ?string $metaDescription = null,
        string $status = 'draft',
        bool $showInNav = false,
        bool $useTheme = true
    ): void {
        $stmt = $this->pdo->prepare('
            UPDATE custom_pages
            SET title = :title, slug = :slug, content_html = :html, content_js = :js,
                meta_title = :mt, meta_description = :md, status = :status,
                show_in_nav = :nav, use_theme = :theme
            WHERE id = :id
        ');
        $stmt->execute([
            ':title'  => $title,
            ':slug'   => $slug,
            ':html'   => $contentHtml,
            ':js'     => $contentJs,
            ':mt'     => $metaTitle,
            ':md'     => $metaDescription,
            ':status' => $status,
            ':nav'    => $showInNav ? 1 : 0,
            ':theme'  => $useTheme ? 1 : 0,
            ':id'     => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM custom_pages WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
}
