<?php

class LinkRepository
{
    public function __construct(private $pdo) {}

    public function create(
        string|array $slugOrData,
        ?string $targetUrl = null,
        ?int $delaySeconds = null,
        ?string $pageTitle = null,
        ?string $pageDescription = null,
        ?string $ogImage = null,
        ?int $categoryId = null,
        ?int $affiliateProgramId = null,
        ?string $publishAt = null,
        ?string $expiresAt = null,
        ?string $passwordHash = null,
        ?string $passwordHint = null,
        ?string $notes = null,
        ?string $status = 'published',
        ?string $fallbackUrl = null
    ): int
    {
        // Support both array and individual parameters
        if (is_array($slugOrData)) {
            $data = $slugOrData;
            $slug = $data['slug'];
            $targetUrl = $data['target_url'];
            $delaySeconds = $data['delay_seconds'] ?? 5;
            $pageTitle = $data['page_title'] ?? null;
            $pageDescription = $data['page_description'] ?? null;
            $ogImage = $data['og_image'] ?? null;
            $categoryId = $data['category_id'] ?? null;
            $affiliateProgramId = $data['affiliate_program_id'] ?? null;
            $publishAt = $data['publish_at'] ?? null;
            $expiresAt = $data['expires_at'] ?? null;
            $passwordHash = $data['password_hash'] ?? null;
            $passwordHint = $data['password_hint'] ?? null;
            $notes = $data['notes'] ?? null;
            $status = $data['status'] ?? 'published';
            $fallbackUrl = $data['fallback_url'] ?? null;
        } else {
            $slug = $slugOrData;
        }

        $stmt = $this->pdo->prepare('INSERT INTO links(slug, target_url, delay_seconds, page_title, page_description, og_image, category_id, affiliate_program_id, publish_at, expires_at, fallback_url, password_hash, password_hint, notes, status) VALUES(:s, :u, :d, :t, :desc, :img, :cid, :apid, :publish_at, :expires_at, :fallback_url, :pass_hash, :pass_hint, :notes, :status)');
        $stmt->execute([
            ':s' => $slug,
            ':u' => $targetUrl,
            ':d' => $delaySeconds,
            ':t' => $pageTitle,
            ':desc' => $pageDescription,
            ':img' => $ogImage,
            ':cid' => $categoryId,
            ':apid' => $affiliateProgramId,
            ':publish_at' => $publishAt,
            ':expires_at' => $expiresAt,
            ':fallback_url' => $fallbackUrl,
            ':pass_hash' => $passwordHash,
            ':pass_hint' => $passwordHint,
            ':notes' => $notes,
            ':status' => $status
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function getBySlug(string $slug, bool $includeNonPublished = false): ?array
    {
        $sql = 'SELECT * FROM links WHERE slug = :s';
        if (!$includeNonPublished) {
            $sql .= " AND status = 'published'";
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':s' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getBySlugWithRelations(string $slug, bool $includeNonPublished = false): ?array
    {
        $sql = 'SELECT * FROM links WHERE slug = :s';
        if (!$includeNonPublished) {
            $sql .= " AND status = 'published'";
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':s' => $slug]);
        $link = $stmt->fetch();
        if (!$link) {
            return null;
        }

        $linkId = (int)$link['id'];
        $link['tags'] = $this->getTagsForLink($linkId);
        $link['custom_fields'] = $this->getCustomFields($linkId);
        $link['images'] = $this->getImages($linkId);

        if (!empty($link['category_id'])) {
            $stmt = $this->pdo->prepare('SELECT * FROM categories WHERE id = :id');
            $stmt->execute([':id' => $link['category_id']]);
            $link['category'] = $stmt->fetch() ?: null;
        } else {
            $link['category'] = null;
        }

        if (!empty($link['affiliate_program_id'])) {
            $stmt = $this->pdo->prepare('SELECT * FROM affiliate_programs WHERE id = :id');
            $stmt->execute([':id' => $link['affiliate_program_id']]);
            $link['affiliate_program'] = $stmt->fetch() ?: null;
        } else {
            $link['affiliate_program'] = null;
        }

        return $link;
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM links WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function count(
        ?int $categoryId = null,
        ?int $tagId = null,
        string $searchQuery = '',
        bool $onlyActive = false,
        ?int $affiliateProgramId = null,
        ?string $statusFilter = null
    ): int {
        $sql = 'SELECT COUNT(DISTINCT l.id) FROM links l LEFT JOIN categories c ON l.category_id = c.id LEFT JOIN affiliate_programs ap ON l.affiliate_program_id = ap.id';
        $where = [];
        $params = [];

        if ($tagId !== null) {
            $sql .= ' INNER JOIN link_tags lt ON l.id = lt.link_id';
            $where[] = 'lt.tag_id = :tid';
            $params[':tid'] = $tagId;
        }

        if ($categoryId !== null) {
            $where[] = 'l.category_id = :cid';
            $params[':cid'] = $categoryId;
        }

        if ($affiliateProgramId !== null) {
            $where[] = 'l.affiliate_program_id = :apid';
            $params[':apid'] = $affiliateProgramId;
        }

        if ($searchQuery !== '') {
            $where[] = '(l.slug LIKE :search OR l.target_url LIKE :search OR c.name LIKE :search OR ap.name LIKE :search)';
            $params[':search'] = '%' . $searchQuery . '%';
        }

        // Status filtering
        if ($statusFilter !== null && $statusFilter !== 'all') {
            switch ($statusFilter) {
                case 'published':
                    $where[] = "l.status = 'published'";
                    $where[] = '(l.publish_at IS NULL OR l.publish_at <= NOW())';
                    $where[] = '(l.expires_at IS NULL OR l.expires_at > NOW())';
                    break;
                case 'draft':
                    $where[] = "l.status = 'draft'";
                    break;
                case 'trashed':
                    $where[] = "l.status = 'trashed'";
                    break;
                case 'scheduled':
                    $where[] = "l.status = 'published'";
                    $where[] = 'l.publish_at > NOW()';
                    break;
                case 'expired':
                    $where[] = "l.status = 'published'";
                    $where[] = 'l.expires_at IS NOT NULL';
                    $where[] = 'l.expires_at <= NOW()';
                    break;
            }
        } elseif ($onlyActive) {
            $where[] = "l.status = 'published'";
            $where[] = '(l.publish_at IS NULL OR l.publish_at <= NOW())';
            $where[] = '(l.expires_at IS NULL OR l.expires_at > NOW())';
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function list(
        int $limit = 100,
        int $offset = 0,
        ?int $categoryId = null,
        ?int $tagId = null,
        string $sortBy = 'created_at',
        string $sortOrder = 'DESC',
        string $searchQuery = '',
        bool $onlyActive = false,
        bool $includeCustomFields = false,
        ?int $affiliateProgramId = null,
        ?string $statusFilter = null
    ): array
    {
        $limit = max(1, (int)$limit);
        $offset = max(0, (int)$offset);

        // Whitelist dozwolonych kolumn do sortowania (zabezpieczenie przed SQL injection)
        $allowedSortColumns = ['id', 'slug', 'target_url', 'created_at', 'delay_seconds', 'publish_at', 'expires_at', 'category_name', 'affiliate_program_name', 'status', 'deleted_at'];
        $sortBy = in_array($sortBy, $allowedSortColumns, true) ? $sortBy : 'created_at';

        // Whitelist kierunku sortowania
        $sortOrder = in_array(strtoupper($sortOrder), ['ASC', 'DESC'], true) ? strtoupper($sortOrder) : 'DESC';

        // Mapa kolumn do sortowania (dla kolumn z JOINowanych tabel)
        $sortColumnMap = [
            'category_name' => 'c.name',
            'affiliate_program_name' => 'ap.name',
        ];
        $sortColumn = $sortColumnMap[$sortBy] ?? "l.$sortBy";

        $sql = "SELECT l.*, c.name as category_name, c.slug as category_slug, c.color as category_color, ap.name as affiliate_program_name, ap.color as affiliate_program_color
            FROM links l
            LEFT JOIN categories c ON l.category_id = c.id
            LEFT JOIN affiliate_programs ap ON l.affiliate_program_id = ap.id";

        $where = [];
        $params = [];

        if ($categoryId !== null) {
            $where[] = 'l.category_id = :cid';
            $params[':cid'] = $categoryId;
        }

        if ($affiliateProgramId !== null) {
            $where[] = 'l.affiliate_program_id = :apid';
            $params[':apid'] = $affiliateProgramId;
        }

        if ($tagId !== null) {
            $sql .= " INNER JOIN link_tags lt ON l.id = lt.link_id";
            $where[] = 'lt.tag_id = :tid';
            $params[':tid'] = $tagId;
        }

        // Wyszukiwanie po page_title, page_description, slug, target_url, nazwie kategorii i nazwie programu afiliacyjnego
        if ($searchQuery !== '') {
            $where[] = '(l.page_title LIKE :search OR l.page_description LIKE :search OR l.slug LIKE :search OR l.target_url LIKE :search OR c.name LIKE :search OR ap.name LIKE :search)';
            $params[':search'] = '%' . $searchQuery . '%';
        }

        // Status filtering
        if ($statusFilter !== null && $statusFilter !== 'all') {
            switch ($statusFilter) {
                case 'published':
                    $where[] = "l.status = 'published'";
                    $where[] = '(l.publish_at IS NULL OR l.publish_at <= NOW())';
                    $where[] = '(l.expires_at IS NULL OR l.expires_at > NOW())';
                    break;
                case 'draft':
                    $where[] = "l.status = 'draft'";
                    break;
                case 'trashed':
                    $where[] = "l.status = 'trashed'";
                    break;
                case 'scheduled':
                    $where[] = "l.status = 'published'";
                    $where[] = 'l.publish_at > NOW()';
                    break;
                case 'expired':
                    $where[] = "l.status = 'published'";
                    $where[] = 'l.expires_at IS NOT NULL';
                    $where[] = 'l.expires_at <= NOW()';
                    break;
            }
        } elseif ($onlyActive) {
            $where[] = "l.status = 'published'";
            $where[] = '(l.publish_at IS NULL OR l.publish_at <= NOW())';
            $where[] = '(l.expires_at IS NULL OR l.expires_at > NOW())';
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        // Dodaj sortowanie (bezpieczne, bo używamy whitelisty)
        $sql .= " ORDER BY $sortColumn $sortOrder LIMIT $limit OFFSET $offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        if ($includeCustomFields && !empty($rows)) {
            $ids = array_map(fn($row) => (int)$row['id'], $rows);
            $fields = $this->getCustomFieldsForLinks($ids);

            foreach ($rows as &$row) {
                $rowId = (int)$row['id'];
                $row['custom_fields'] = $fields[$rowId] ?? [];
            }
        }

        return $rows;
    }

    /**
     * Zlicz aktywne wpisy bloga z opcjonalnym filtrem kategorii/tagu.
     * Warunki muszą być identyczne jak w list() z onlyActive=true.
     */
    public function countBlogPosts(?int $categoryId = null, ?int $tagId = null, string $searchQuery = ''): int
    {
        $sql    = 'SELECT COUNT(*) FROM links l';
        $where  = [
            "l.status = 'published'",
            '(l.publish_at IS NULL OR l.publish_at <= NOW())',
            '(l.expires_at IS NULL OR l.expires_at > NOW())',
        ];
        $params = [];

        if ($tagId !== null) {
            $sql   .= ' INNER JOIN link_tags lt ON l.id = lt.link_id';
            $where[] = 'lt.tag_id = :tid';
            $params[':tid'] = $tagId;
        }

        if ($categoryId !== null) {
            $where[] = 'l.category_id = :cid';
            $params[':cid'] = $categoryId;
        }

        if ($searchQuery !== '') {
            $where[] = '(l.page_title LIKE :search OR l.page_description LIKE :search OR l.slug LIKE :search)';
            $params[':search'] = '%' . $searchQuery . '%';
        }

        $sql .= ' WHERE ' . implode(' AND ', $where);

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Pobierz wpisy do slidera (newest/random/popular).
     * $type: 'newest' | 'random' | 'popular'
     * $days: 0 = cały czas, >0 = ostatnie N dni (tylko dla popular)
     */
    public function getSliderPosts(int $count, string $type = 'newest', int $days = 0): array
    {
        $count = max(1, min(5, $count));
        $baseWhere = "status = 'published'
              AND (publish_at IS NULL OR publish_at <= NOW())
              AND (expires_at IS NULL OR expires_at > NOW())
              AND page_title IS NOT NULL AND page_title != ''
              AND og_image IS NOT NULL AND og_image != ''";

        if ($type === 'random') {
            // Avoid ORDER BY RAND() — use count + random offset instead
            $cntStmt = $this->pdo->query("SELECT COUNT(*) FROM links l WHERE $baseWhere");
            $total   = (int)($cntStmt ? $cntStmt->fetchColumn() : 0);
            $offset  = ($total > $count) ? random_int(0, $total - $count) : 0;
            $stmt = $this->pdo->prepare(
                "SELECT l.slug, l.page_title, l.page_description, l.og_image, l.created_at,
                        c.name AS category_name, c.color AS category_color
                 FROM links l
                 LEFT JOIN categories c ON l.category_id = c.id
                 WHERE $baseWhere
                 ORDER BY l.id
                 LIMIT :lim OFFSET :off"
            );
            $stmt->bindValue(':lim', $count, \PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        if ($type === 'popular') {
            $statsWhere = '';
            $params = [];
            if ($days > 0) {
                $statsWhere = 'AND ls.visited_at >= DATE_SUB(NOW(), INTERVAL :days DAY)';
                $params[':days'] = $days;
            }
            $stmt = $this->pdo->prepare(
                "SELECT l.slug, l.page_title, l.page_description, l.og_image, l.created_at,
                        c.name AS category_name, c.color AS category_color,
                        COUNT(ls.id) AS click_count
                 FROM links l
                 LEFT JOIN categories c ON l.category_id = c.id
                 LEFT JOIN link_stats ls ON ls.link_id = l.id $statsWhere
                 WHERE $baseWhere
                 GROUP BY l.id, l.slug, l.page_title, l.page_description, l.og_image, l.created_at,
                          c.name, c.color
                 ORDER BY click_count DESC, l.created_at DESC
                 LIMIT :lim"
            );
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val, is_int($val) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
            }
            $stmt->bindValue(':lim', $count, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        // default: newest
        $stmt = $this->pdo->prepare(
            "SELECT l.slug, l.page_title, l.page_description, l.og_image, l.created_at,
                    c.name AS category_name, c.color AS category_color
             FROM links l
             LEFT JOIN categories c ON l.category_id = c.id
             WHERE $baseWhere
             ORDER BY l.created_at DESC
             LIMIT :lim"
        );
        $stmt->bindValue(':lim', $count, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Pobierz ostatnie N wpisów do stopki (każda podstrona).
     */
    public function getRecentPosts(int $count = 4): array
    {
        $count = max(1, min(20, $count));
        $stmt = $this->pdo->prepare(
            "SELECT l.slug, l.page_title, l.og_image, l.created_at
             FROM links l
             WHERE l.status = 'published'
               AND (l.publish_at IS NULL OR l.publish_at <= NOW())
               AND (l.expires_at IS NULL OR l.expires_at > NOW())
               AND l.page_title IS NOT NULL AND l.page_title != ''
             ORDER BY l.created_at DESC
             LIMIT :lim"
        );
        $stmt->bindValue(':lim', $count, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Pobierz opublikowane wpisy (aktywny status, okno publikacji) do RSS/sitemap
     */
    public function getActiveBlogPosts(int $limit = 5000, int $offset = 0): array
    {
        $limit = max(1, min((int)$limit, 5000));
        $offset = max(0, (int)$offset);

        $sql = "
            SELECT
                l.id,
                l.slug,
                l.page_title,
                l.page_description,
                l.target_url,
                l.og_image,
                l.publish_at,
                l.expires_at,
                l.created_at,
                c.slug AS category_slug,
                c.name AS category_name,
                c.color AS category_color
            FROM links l
            LEFT JOIN categories c ON l.category_id = c.id
            WHERE l.status = 'published'
              AND (l.publish_at IS NULL OR l.publish_at <= NOW())
              AND (l.expires_at IS NULL OR l.expires_at > NOW())
            ORDER BY COALESCE(l.publish_at, l.created_at) DESC, l.id DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function update(
        int $id,
        string|array $slugOrData,
        ?string $targetUrl = null,
        ?int $delaySeconds = null,
        ?string $pageTitle = null,
        ?string $pageDescription = null,
        ?string $ogImage = null,
        ?int $categoryId = null,
        ?int $affiliateProgramId = null,
        ?string $publishAt = null,
        ?string $expiresAt = null,
        ?string $passwordHash = null,
        ?string $passwordHint = null,
        ?string $notes = null,
        ?string $fallbackUrl = null
    ): void
    {
        // Support both array and individual parameters
        if (is_array($slugOrData)) {
            $data = $slugOrData;
            $slug = $data['slug'] ?? null;
            $targetUrl = $data['target_url'] ?? null;
            $delaySeconds = $data['delay_seconds'] ?? null;
            $pageTitle = $data['page_title'] ?? null;
            $pageDescription = $data['page_description'] ?? null;
            $ogImage = $data['og_image'] ?? null;
            $categoryId = $data['category_id'] ?? null;
            $affiliateProgramId = $data['affiliate_program_id'] ?? null;
            $publishAt = $data['publish_at'] ?? null;
            $expiresAt = $data['expires_at'] ?? null;
            $passwordHash = $data['password_hash'] ?? null;
            $passwordHint = $data['password_hint'] ?? null;
            $notes = $data['notes'] ?? null;

            // Build dynamic UPDATE query based on provided fields
            $fields = [];
            $params = [':id' => $id];

            if ($slug !== null) {
                $fields[] = 'slug = :s';
                $params[':s'] = $slug;
            }
            if ($targetUrl !== null) {
                $fields[] = 'target_url = :u';
                $params[':u'] = $targetUrl;
            }
            if ($delaySeconds !== null) {
                $fields[] = 'delay_seconds = :d';
                $params[':d'] = $delaySeconds;
            }
            if ($pageTitle !== null) {
                $fields[] = 'page_title = :t';
                $params[':t'] = $pageTitle;
            }
            if ($pageDescription !== null) {
                $fields[] = 'page_description = :desc';
                $params[':desc'] = $pageDescription;
            }
            if ($ogImage !== null) {
                $fields[] = 'og_image = :img';
                $params[':img'] = $ogImage;
            }
            if (array_key_exists('category_id', $data)) {
                $fields[] = 'category_id = :cid';
                $params[':cid'] = $categoryId;
            }
            if (array_key_exists('affiliate_program_id', $data)) {
                $fields[] = 'affiliate_program_id = :apid';
                $params[':apid'] = $affiliateProgramId;
            }
            if (isset($data['publish_at'])) {
                $fields[] = 'publish_at = :publish_at';
                $params[':publish_at'] = $publishAt;
            }
            if (isset($data['expires_at'])) {
                $fields[] = 'expires_at = :expires_at';
                $params[':expires_at'] = $expiresAt;
            }
            if (array_key_exists('fallback_url', $data)) {
                $fields[] = 'fallback_url = :fallback_url';
                $params[':fallback_url'] = $data['fallback_url'];
            }
            if (isset($data['password_hash'])) {
                $fields[] = 'password_hash = :pass_hash';
                $params[':pass_hash'] = $passwordHash;
            }
            if (isset($data['password_hint'])) {
                $fields[] = 'password_hint = :pass_hint';
                $params[':pass_hint'] = $passwordHint;
            }
            if (isset($data['notes'])) {
                $fields[] = 'notes = :notes';
                $params[':notes'] = $notes;
            }
            if (isset($data['status'])) {
                $fields[] = 'status = :status';
                $params[':status'] = $data['status'];
            }
            if (array_key_exists('deleted_at', $data)) {
                $fields[] = 'deleted_at = :deleted_at';
                $params[':deleted_at'] = $data['deleted_at'];
            }

            if (empty($fields)) {
                return; // Nothing to update
            }

            $sql = 'UPDATE links SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
        } else {
            $slug = $slugOrData;
            $stmt = $this->pdo->prepare('UPDATE links SET slug = :s, target_url = :u, delay_seconds = :d, page_title = :t, page_description = :desc, og_image = :img, category_id = :cid, affiliate_program_id = :apid, publish_at = :publish_at, expires_at = :expires_at, fallback_url = :fallback_url, password_hash = :pass_hash, password_hint = :pass_hint, notes = :notes WHERE id = :id');
            $stmt->execute([
                ':s' => $slug,
                ':u' => $targetUrl,
                ':d' => $delaySeconds,
                ':t' => $pageTitle,
                ':desc' => $pageDescription,
                ':img' => $ogImage,
                ':cid' => $categoryId,
                ':apid' => $affiliateProgramId,
                ':publish_at' => $publishAt,
                ':expires_at' => $expiresAt,
                ':fallback_url' => $fallbackUrl,
                ':pass_hash' => $passwordHash,
                ':pass_hint' => $passwordHint,
                ':notes' => $notes,
                ':id' => $id
            ]);
        }
    }

    /**
     * Soft delete - przeniesienie do kosza
     */
    public function delete(int $id, bool $permanent = false): void
    {
        if ($permanent) {
            $stmt = $this->pdo->prepare('DELETE FROM links WHERE id = :id');
            $stmt->execute([':id' => $id]);
        } else {
            $stmt = $this->pdo->prepare("UPDATE links SET status = 'trashed', deleted_at = NOW() WHERE id = :id");
            $stmt->execute([':id' => $id]);
        }
    }

    /**
     * Przywraca link z kosza
     */
    public function restore(int $id): void
    {
        $stmt = $this->pdo->prepare("UPDATE links SET status = 'published', deleted_at = NULL WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    /**
     * Trwale usuwa link z bazy danych
     */
    public function permanentDelete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM links WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    /**
     * Opróżnia cały kosz
     */
    public function emptyTrash(): int
    {
        $stmt = $this->pdo->prepare("DELETE FROM links WHERE status = 'trashed'");
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Pobiera linki z kosza starsze niz podana liczba dni
     */
    /**
     * Ustawia deleted_at = NOW() dla wierszy z status='trashed' gdzie deleted_at IS NULL (legacy dane).
     * Uzycie NOW() zamiast created_at zapobiega natychmiastowemu usunieciu starych linkow.
     */
    public function backfillTrashedDeletedAt(): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE links SET deleted_at = NOW()
            WHERE status = 'trashed' AND deleted_at IS NULL
        ");
        $stmt->execute();
    }

    public function getTrashedOlderThan(int $days): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM links
            WHERE status = 'trashed'
            AND deleted_at IS NOT NULL
            AND deleted_at < DATE_SUB(NOW(), INTERVAL :days DAY)
        ");
        $stmt->execute([':days' => $days]);
        return $stmt->fetchAll();
    }

    public function slugExists(string $slug): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM links WHERE slug = :s');
        $stmt->execute([':s' => $slug]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Pobierz tagi dla linku
     */
    public function getTagsForLink(int $linkId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT t.* 
            FROM tags t
            INNER JOIN link_tags lt ON lt.tag_id = t.id
            WHERE lt.link_id = :lid
            ORDER BY t.name ASC
        ');
        $stmt->execute([':lid' => $linkId]);
        return $stmt->fetchAll();
    }

    /**
     * Pobierz linki z kategoriami i tagami (rozszerzona wersja)
     */
    public function getByIdWithRelations(int $id): ?array
    {
        $link = $this->getById($id);
        if (!$link) {
            return null;
        }
        
        // Dodaj tagi
        $link['tags'] = $this->getTagsForLink($id);
        $link['custom_fields'] = $this->getCustomFields($id);
        $link['images'] = $this->getImages($id);
        
        // Dodaj kategorię jeśli istnieje
        if (!empty($link['category_id'])) {
            $stmt = $this->pdo->prepare('SELECT * FROM categories WHERE id = :id');
            $stmt->execute([':id' => $link['category_id']]);
            $link['category'] = $stmt->fetch() ?: null;
        } else {
            $link['category'] = null;
        }
        
        return $link;
    }

    /**
     * Pobierz powiązane wpisy (po kategorii lub tagach)
     */
    public function getRelatedPosts(int $linkId, int $limit = 5): array
    {
        $currentLink = $this->getByIdWithRelations($linkId);
        if (!$currentLink) {
            return [];
        }

        $relatedIds = [];
        
        // 1. Dodaj wpisy z tej samej kategorii
        if (!empty($currentLink['category_id'])) {
            $limit = max(1, (int)$limit);
            $stmt = $this->pdo->prepare("
                SELECT l.id
                FROM links l
                WHERE l.category_id = ?
                AND l.id != ?
                AND l.status = 'published'
                AND (l.publish_at IS NULL OR l.publish_at <= NOW())
                AND (l.expires_at IS NULL OR l.expires_at > NOW())
                ORDER BY l.created_at DESC
                LIMIT " . $limit . "
            ");
            $stmt->bindValue(1, $currentLink['category_id'], PDO::PARAM_INT);
            $stmt->bindValue(2, $linkId, PDO::PARAM_INT);
            $stmt->execute();
            foreach ($stmt->fetchAll() as $row) {
                $relatedIds[(int)$row['id']] = 1;
            }
        }
        
        // 2. Dodaj wpisy ze wspólnymi tagami
        if (!empty($currentLink['tags'])) {
            $tagIds = array_column($currentLink['tags'], 'id');
            $placeholders = implode(',', array_fill(0, count($tagIds), '?'));
            $limit = max(1, (int)$limit);
            $sql = "
                SELECT DISTINCT l.id
                FROM links l
                INNER JOIN link_tags lt ON l.id = lt.link_id
                WHERE lt.tag_id IN ($placeholders)
                AND l.id != ?
                AND l.status = 'published'
                AND (l.publish_at IS NULL OR l.publish_at <= NOW())
                AND (l.expires_at IS NULL OR l.expires_at > NOW())
                ORDER BY l.created_at DESC
                LIMIT $limit
            ";
            $stmt = $this->pdo->prepare($sql);
            $params = array_merge($tagIds, [$linkId]);
            $stmt->execute($params);
            foreach ($stmt->fetchAll() as $row) {
                $relatedIds[(int)$row['id']] = 1;
            }
        }

        // Pobierz pełne dane powiązanych wpisów
        if (empty($relatedIds)) {
            return [];
        }

        $relatedIdsList = implode(',', array_keys($relatedIds));
        $limit = max(1, (int)$limit);
        $stmt = $this->pdo->prepare("
            SELECT l.*, c.name as category_name, c.color as category_color
            FROM links l
            LEFT JOIN categories c ON l.category_id = c.id
            WHERE l.id IN ($relatedIdsList)
            ORDER BY l.created_at DESC
            LIMIT $limit
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getCustomFields(int $linkId): array
    {
        $stmt = $this->pdo->prepare('SELECT field_key, field_label, field_value FROM link_custom_fields WHERE link_id = :lid ORDER BY field_key ASC');
        $stmt->execute([':lid' => $linkId]);
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[(string)$row['field_key']] = [
                'key' => (string)$row['field_key'],
                'label' => (string)$row['field_label'],
                'value' => (string)($row['field_value'] ?? ''),
            ];
        }
        return $result;
    }

    public function setCustomFields(int $linkId, array $values, array $definitions): void
    {
        $this->pdo->beginTransaction();
        try {
            $deleteStmt = $this->pdo->prepare('DELETE FROM link_custom_fields WHERE link_id = :lid');
            $deleteStmt->execute([':lid' => $linkId]);

            if (!empty($definitions)) {
                $insertStmt = $this->pdo->prepare('INSERT INTO link_custom_fields(link_id, field_key, field_label, field_value) VALUES(:lid, :fkey, :flabel, :fvalue)');
                foreach ($definitions as $definition) {
                    $key = (string)($definition['key'] ?? '');
                    if ($key === '' || !array_key_exists($key, $values)) {
                        continue;
                    }

                    $value = $values[$key];
                    if ($value === '' || $value === null) {
                        continue;
                    }

                    $insertStmt->execute([
                        ':lid' => $linkId,
                        ':fkey' => $key,
                        ':flabel' => (string)($definition['label'] ?? $key),
                        ':fvalue' => (string)$value,
                    ]);
                }
            }

            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function getCustomFieldsForLinks(array $linkIds): array
    {
        if (empty($linkIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($linkIds), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT link_id, field_key, field_label, field_value FROM link_custom_fields WHERE link_id IN ($placeholders) ORDER BY field_key ASC"
        );
        $stmt->execute($linkIds);

        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $lid = (int)$row['link_id'];
            if (!isset($result[$lid])) {
                $result[$lid] = [];
            }
            $result[$lid][(string)$row['field_key']] = [
                'key' => (string)$row['field_key'],
                'label' => (string)$row['field_label'],
                'value' => (string)($row['field_value'] ?? ''),
            ];
        }

        return $result;
    }

    public function getImages(int $linkId): array
    {
        $stmt = $this->pdo->prepare('SELECT id, path, thumb_path, is_primary, created_at FROM link_images WHERE link_id = :lid ORDER BY is_primary DESC, created_at DESC, id DESC');
        $stmt->execute([':lid' => $linkId]);
        $rows = $stmt->fetchAll();

        return array_map(fn($row) => [
            'id' => (int)$row['id'],
            'path' => (string)$row['path'],
            'thumb_path' => (string)($row['thumb_path'] ?? ''),
            'is_primary' => (int)$row['is_primary'] === 1,
            'created_at' => (string)$row['created_at'],
        ], $rows);
    }

    public function addImage(int $linkId, string $path, ?string $thumbPath = null, bool $isPrimary = false): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO link_images(link_id, path, thumb_path, is_primary) VALUES(:lid, :path, :thumb, :primary)');
        $stmt->execute([
            ':lid' => $linkId,
            ':path' => $path,
            ':thumb' => $thumbPath,
            ':primary' => $isPrimary ? 1 : 0,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function setPrimaryImage(int $linkId, ?int $imageId = null): void
    {
        $this->pdo->beginTransaction();
        try {
            $primaryPath = null;
            $primaryThumbPath = null;

            // Wyłącz obecne flagi
            $resetStmt = $this->pdo->prepare('UPDATE link_images SET is_primary = 0 WHERE link_id = :lid');
            $resetStmt->execute([':lid' => $linkId]);

            // Ustaw wskazany obrazek jako główny, jeśli istnieje
            if ($imageId !== null) {
                $selectStmt = $this->pdo->prepare('SELECT id, path, thumb_path FROM link_images WHERE id = :id AND link_id = :lid');
                $selectStmt->execute([':id' => $imageId, ':lid' => $linkId]);
                $selected = $selectStmt->fetch();
                if ($selected) {
                    $primaryPath = (string)$selected['path'];
                    $primaryThumbPath = !empty($selected['thumb_path']) ? (string)$selected['thumb_path'] : null;
                    $setStmt = $this->pdo->prepare('UPDATE link_images SET is_primary = 1 WHERE id = :id AND link_id = :lid');
                    $setStmt->execute([':id' => $imageId, ':lid' => $linkId]);
                }
            }

            // Jeśli wskazany obrazek nie istnieje lub nie podano, wybierz najnowszy
            if ($primaryPath === null) {
                $fallbackStmt = $this->pdo->prepare('SELECT id, path, thumb_path FROM link_images WHERE link_id = :lid ORDER BY created_at DESC, id DESC LIMIT 1');
                $fallbackStmt->execute([':lid' => $linkId]);
                $fallback = $fallbackStmt->fetch();
                if ($fallback) {
                    $primaryPath = (string)$fallback['path'];
                    $primaryThumbPath = !empty($fallback['thumb_path']) ? (string)$fallback['thumb_path'] : null;
                    $fallbackId = (int)$fallback['id'];
                    $markStmt = $this->pdo->prepare('UPDATE link_images SET is_primary = 1 WHERE id = :id AND link_id = :lid');
                    $markStmt->execute([':id' => $fallbackId, ':lid' => $linkId]);
                }
            }

            $updateLink = $this->pdo->prepare('UPDATE links SET og_image = :img, og_image_thumb = :thumb WHERE id = :lid');
            $updateLink->execute([
                ':img' => $primaryPath,
                ':thumb' => $primaryThumbPath,
                ':lid' => $linkId,
            ]);

            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function deleteImages(int $linkId, array $imageIds): array
    {
        $ids = array_values(array_filter(array_map('intval', $imageIds), fn($id) => $id > 0));
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $params = array_merge([$linkId], $ids);

        $selectStmt = $this->pdo->prepare("SELECT id, path, thumb_path, is_primary FROM link_images WHERE link_id = ? AND id IN ($placeholders)");
        $selectStmt->execute($params);
        $rows = $selectStmt->fetchAll();
        if (empty($rows)) {
            return [];
        }

        $deleteStmt = $this->pdo->prepare("DELETE FROM link_images WHERE link_id = ? AND id IN ($placeholders)");
        $deleteStmt->execute($params);

        return array_map(fn($row) => [
            'id' => (int)$row['id'],
            'path' => (string)$row['path'],
            'thumb_path' => (string)($row['thumb_path'] ?? ''),
            'is_primary' => (int)$row['is_primary'] === 1,
        ], $rows);
    }

    /**
     * Pobierz wszystkie kategorie z liczbą wpisów
     */
    public function getCategoriesWithCounts(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                c.*,
                COUNT(l.id) as post_count
            FROM categories c
            LEFT JOIN links l ON c.id = l.category_id
                AND l.status = 'published'
                AND (l.publish_at IS NULL OR l.publish_at <= NOW())
                AND (l.expires_at IS NULL OR l.expires_at > NOW())
            GROUP BY c.id
            ORDER BY c.name ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Pobierz wszystkie tagi z liczbą wpisów
     */
    public function getTagsWithCounts(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                t.*,
                COUNT(l.id) as post_count
            FROM tags t
            LEFT JOIN link_tags lt ON t.id = lt.tag_id
            LEFT JOIN links l ON lt.link_id = l.id
                AND l.status = 'published'
                AND (l.publish_at IS NULL OR l.publish_at <= NOW())
                AND (l.expires_at IS NULL OR l.expires_at > NOW())
            GROUP BY t.id
            ORDER BY t.name ASC
        ");
        return $stmt->fetchAll();
    }
}
