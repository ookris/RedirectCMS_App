<?php

class CategoryRepository
{
    public function __construct(private $pdo) {}

    /**
     * Pobierz wszystkie kategorie
     */
    public function list(int $limit = 100, int $offset = 0): array
    {
        $limit = max(1, (int)$limit);
        $offset = max(0, (int)$offset);
        $sql = "SELECT c.*, COUNT(l.id) as link_count 
                FROM categories c 
                LEFT JOIN links l ON l.category_id = c.id 
                GROUP BY c.id 
                ORDER BY c.name ASC 
                LIMIT $limit OFFSET $offset";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Pobierz kategorię po ID
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM categories WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Pobierz kategorię po slug
     */
    public function getBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM categories WHERE slug = :slug');
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Utwórz nową kategorię
     */
    public function create(string $name, string $slug, ?string $description = null, string $color = '#3A3F45', ?string $iconImage = null, ?string $iconImageThumb = null): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO categories(name, slug, description, color, icon_image, icon_image_thumb) VALUES(:n, :s, :d, :c, :icon, :thumb)');
        $stmt->execute([
            ':n' => $name,
            ':s' => $slug,
            ':d' => $description,
            ':c' => $color,
            ':icon' => $iconImage,
            ':thumb' => $iconImageThumb
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Zaktualizuj kategorię
     */
    public function update(int $id, string $name, string $slug, ?string $description = null, string $color = '#3A3F45', ?string $iconImage = null, ?string $iconImageThumb = null): void
    {
        $stmt = $this->pdo->prepare('UPDATE categories SET name = :n, slug = :s, description = :d, color = :c, icon_image = :icon, icon_image_thumb = :thumb WHERE id = :id');
        $stmt->execute([
            ':n' => $name,
            ':s' => $slug,
            ':d' => $description,
            ':c' => $color,
            ':icon' => $iconImage,
            ':thumb' => $iconImageThumb,
            ':id' => $id
        ]);
    }

    /**
     * Usuń kategorię
     */
    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM categories WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    /**
     * Sprawdź czy slug już istnieje
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $stmt = $this->pdo->prepare('SELECT 1 FROM categories WHERE slug = :s AND id != :id');
            $stmt->execute([':s' => $slug, ':id' => $excludeId]);
        } else {
            $stmt = $this->pdo->prepare('SELECT 1 FROM categories WHERE slug = :s');
            $stmt->execute([':s' => $slug]);
        }
        return (bool)$stmt->fetchColumn();
    }
}
