<?php

require_once __DIR__ . '/Database.php';

/**
 * PDO wrapper that automatically applies table prefix to all queries
 */
class PrefixedPDO
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Prepare a statement with table prefix applied
     */
    public function prepare(string $sql, array $options = []): PDOStatement|false
    {
        $sql = Database::applyPrefix($sql);
        return $this->pdo->prepare($sql, $options);
    }

    /**
     * Execute a query with table prefix applied
     */
    public function query(string $sql, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false
    {
        $sql = Database::applyPrefix($sql);
        if ($fetchMode !== null) {
            return $this->pdo->query($sql, $fetchMode, ...$fetchModeArgs);
        }
        return $this->pdo->query($sql);
    }

    /**
     * Execute a statement with table prefix applied
     */
    public function exec(string $sql): int|false
    {
        $sql = Database::applyPrefix($sql);
        return $this->pdo->exec($sql);
    }

    /**
     * Forward all other method calls to the underlying PDO
     */
    public function __call(string $method, array $args): mixed
    {
        return $this->pdo->$method(...$args);
    }

    /**
     * Get the underlying PDO instance (for cases where raw PDO is needed)
     */
    public function getRawPdo(): PDO
    {
        return $this->pdo;
    }

    // Explicit forwarding for commonly used methods
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    public function lastInsertId(?string $name = null): string|false
    {
        return $this->pdo->lastInsertId($name);
    }

    public function quote(string $string, int $type = PDO::PARAM_STR): string|false
    {
        return $this->pdo->quote($string, $type);
    }

    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }

    public function errorCode(): ?string
    {
        return $this->pdo->errorCode();
    }

    public function errorInfo(): array
    {
        return $this->pdo->errorInfo();
    }

    public function getAttribute(int $attribute): mixed
    {
        return $this->pdo->getAttribute($attribute);
    }

    public function setAttribute(int $attribute, mixed $value): bool
    {
        return $this->pdo->setAttribute($attribute, $value);
    }
}
