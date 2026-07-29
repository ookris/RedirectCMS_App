<?php

class TagRepository
{
    public function __construct(private $pdo) {}

    /**
     * Pobierz wszystkie tagi
     */
    public function list(int $limit = 100, int $offset = 0): array
    {
        $limit = max(1, (int)$limit);
        $offset = max(0, (int)$offset);
        $sql = "SELECT t.*, COUNT(lt.link_id) as link_count 
                FROM tags t 
                LEFT JOIN link_tags lt ON lt.tag_id = t.id 
                GROUP BY t.id 
                ORDER BY t.name ASC 
                LIMIT $limit OFFSET $offset";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Pobierz tag po ID
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tags WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Pobierz tag po slug
     */
    public function getBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tags WHERE slug = :slug');
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Utwórz nowy tag
     */
    public function create(string $name, string $slug): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO tags(name, slug) VALUES(:n, :s)');
        $stmt->execute([':n' => $name, ':s' => $slug]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Zaktualizuj tag
     */
    public function update(int $id, string $name, string $slug): void
    {
        $stmt = $this->pdo->prepare('UPDATE tags SET name = :n, slug = :s WHERE id = :id');
        $stmt->execute([':n' => $name, ':s' => $slug, ':id' => $id]);
    }

    /**
     * Usuń tag
     */
    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM tags WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    /**
     * Sprawdź czy slug już istnieje
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $stmt = $this->pdo->prepare('SELECT 1 FROM tags WHERE slug = :s AND id != :id');
            $stmt->execute([':s' => $slug, ':id' => $excludeId]);
        } else {
            $stmt = $this->pdo->prepare('SELECT 1 FROM tags WHERE slug = :s');
            $stmt->execute([':s' => $slug]);
        }
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
     * Przypisz tagi do linku
     */
    public function setTagsForLink(int $linkId, array $tagIds): void
    {
        $this->pdo->beginTransaction();
        try {
            // Usuń stare powiązania
            $stmt = $this->pdo->prepare('DELETE FROM link_tags WHERE link_id = :lid');
            $stmt->execute([':lid' => $linkId]);

            // Dodaj nowe
            if (!empty($tagIds)) {
                $stmt = $this->pdo->prepare('INSERT INTO link_tags(link_id, tag_id) VALUES(:lid, :tid)');
                foreach ($tagIds as $tagId) {
                    $stmt->execute([':lid' => $linkId, ':tid' => (int)$tagId]);
                }
            }

            $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Znajdź lub utwórz tag po nazwie
     */
    public function findOrCreate(string $name): int
    {
        // Usuń znaczniki HTML i ogranicz długość — nazwa tagu może pochodzić
        // z niezaufanego źródła (import CSV), a jest renderowana w panelu admina.
        $name = trim(strip_tags($name));
        $name = mb_substr($name, 0, 100);
        if ($name === '') {
            $name = 'tag';
        }

        $slug = Utils::sanitizeSlug($name);
        
        // Sprawdź czy tag istnieje
        $existing = $this->getBySlug($slug);
        if ($existing) {
            return (int)$existing['id'];
        }

        // Utwórz nowy
        return $this->create($name, $slug);
    }

    /**
     * Pobierz najpopularniejsze tagi (z liczbą użyć)
     */
    public function getTopTags(int $limit = 50): array
    {
        $limit = max(1, (int)$limit);
        $sql = "SELECT t.*, COUNT(lt.link_id) as usage_count 
                FROM tags t 
                INNER JOIN link_tags lt ON lt.tag_id = t.id 
                GROUP BY t.id 
                HAVING usage_count > 0
                ORDER BY usage_count DESC, t.name ASC 
                LIMIT $limit";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Wyszukaj tagi po nazwie (dla autocomplete)
     */
    public function searchTags(string $query, int $limit = 20): array
    {
        $limit = max(1, (int)$limit);
        $stmt = $this->pdo->prepare(
            "SELECT t.*, COUNT(lt.link_id) as usage_count 
             FROM tags t 
             LEFT JOIN link_tags lt ON lt.tag_id = t.id 
             WHERE t.name LIKE :query 
             GROUP BY t.id 
             ORDER BY usage_count DESC, t.name ASC 
             LIMIT $limit"
        );
        $stmt->execute([':query' => '%' . $query . '%']);
        return $stmt->fetchAll();
    }
}
