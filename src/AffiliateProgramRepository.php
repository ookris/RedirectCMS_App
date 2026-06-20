<?php

class AffiliateProgramRepository
{
    public function __construct(private $pdo) {}

    public function listAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM affiliate_programs ORDER BY name ASC');
        return $stmt->fetchAll();
    }

    public function count(): int
    {
        return (int)$this->pdo->query('SELECT COUNT(*) FROM affiliate_programs')->fetchColumn();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM affiliate_programs WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(string $name, string $color = '#4CAF50'): int
    {
        $name = trim($name);
        if ($name === '') {
            throw new InvalidArgumentException('Nazwa programu jest wymagana');
        }
        if ($this->existsByName($name)) {
            throw new InvalidArgumentException('Program o takiej nazwie już istnieje');
        }
        $color = $this->normalizeColor($color);
        $stmt = $this->pdo->prepare('INSERT INTO affiliate_programs(name, color) VALUES(:n, :c)');
        $stmt->execute([':n' => $name, ':c' => $color]);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, string $name, string $color = '#4CAF50'): void
    {
        $name = trim($name);
        if ($name === '') {
            throw new InvalidArgumentException('Nazwa programu jest wymagana');
        }
        if ($this->existsByName($name, $id)) {
            throw new InvalidArgumentException('Program o takiej nazwie już istnieje');
        }
        $color = $this->normalizeColor($color);
        $stmt = $this->pdo->prepare('UPDATE affiliate_programs SET name = :n, color = :c WHERE id = :id');
        $stmt->execute([':n' => $name, ':c' => $color, ':id' => $id]);
    }

    private function normalizeColor(string $color): string
    {
        $color = trim($color);
        if ($color === '') {
            return '#4CAF50';
        }
        if (!str_starts_with($color, '#')) {
            $color = '#' . $color;
        }
        return strtoupper($color);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM affiliate_programs WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public function existsByName(string $name, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $stmt = $this->pdo->prepare('SELECT 1 FROM affiliate_programs WHERE name = :n AND id != :id');
            $stmt->execute([':n' => $name, ':id' => $excludeId]);
        } else {
            $stmt = $this->pdo->prepare('SELECT 1 FROM affiliate_programs WHERE name = :n');
            $stmt->execute([':n' => $name]);
        }
        return (bool)$stmt->fetchColumn();
    }
}
