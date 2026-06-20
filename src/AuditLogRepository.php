<?php
declare(strict_types=1);

class AuditLogRepository
{
    public function __construct(private $pdo) {}

    /**
     * Zapisz wpis do logu audytu
     */
    public function log(
        string $actionType,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $entityName = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): int {
        $stmt = $this->pdo->prepare('
            INSERT INTO audit_logs (action_type, entity_type, entity_id, entity_name, old_values, new_values, ip_address, user_agent)
            VALUES (:action, :entity_type, :entity_id, :entity_name, :old_values, :new_values, :ip, :ua)
        ');

        $stmt->execute([
            ':action' => $actionType,
            ':entity_type' => $entityType,
            ':entity_id' => $entityId,
            ':entity_name' => $entityName,
            ':old_values' => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
            ':new_values' => $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
            ':ip' => $ipAddress,
            ':ua' => $userAgent ? mb_substr($userAgent, 0, 500) : null,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Pobierz listę logów z filtrowaniem i paginacją
     */
    public function list(
        int $limit = 50,
        int $offset = 0,
        ?string $actionType = null,
        ?string $entityType = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $searchQuery = null
    ): array {
        $where = [];
        $params = [];

        if ($actionType) {
            $where[] = 'action_type = :action';
            $params[':action'] = $actionType;
        }
        if ($entityType) {
            $where[] = 'entity_type = :entity';
            $params[':entity'] = $entityType;
        }
        if ($dateFrom) {
            $where[] = 'created_at >= :from';
            $params[':from'] = $dateFrom . ' 00:00:00';
        }
        if ($dateTo) {
            $where[] = 'created_at <= :to';
            $params[':to'] = $dateTo . ' 23:59:59';
        }
        if ($searchQuery) {
            $where[] = '(entity_name LIKE :search OR ip_address LIKE :search2)';
            $params[':search'] = '%' . $searchQuery . '%';
            $params[':search2'] = '%' . $searchQuery . '%';
        }

        $sql = 'SELECT * FROM audit_logs';
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY created_at DESC';

        $stmt = $this->pdo->prepare($sql . ' LIMIT :limit OFFSET :offset');
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Policz logi z tymi samymi filtrami
     */
    public function count(
        ?string $actionType = null,
        ?string $entityType = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $searchQuery = null
    ): int {
        $where = [];
        $params = [];

        if ($actionType) {
            $where[] = 'action_type = :action';
            $params[':action'] = $actionType;
        }
        if ($entityType) {
            $where[] = 'entity_type = :entity';
            $params[':entity'] = $entityType;
        }
        if ($dateFrom) {
            $where[] = 'created_at >= :from';
            $params[':from'] = $dateFrom . ' 00:00:00';
        }
        if ($dateTo) {
            $where[] = 'created_at <= :to';
            $params[':to'] = $dateTo . ' 23:59:59';
        }
        if ($searchQuery) {
            $where[] = '(entity_name LIKE :search OR ip_address LIKE :search2)';
            $params[':search'] = '%' . $searchQuery . '%';
            $params[':search2'] = '%' . $searchQuery . '%';
        }

        $sql = 'SELECT COUNT(*) FROM audit_logs';
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int)$stmt->fetchColumn();
    }

    /**
     * Pobierz unikalne typy akcji do filtra
     */
    public function getActionTypes(): array
    {
        $stmt = $this->pdo->query('SELECT DISTINCT action_type FROM audit_logs ORDER BY action_type');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Pobierz unikalne typy encji do filtra
     */
    public function getEntityTypes(): array
    {
        $stmt = $this->pdo->query('SELECT DISTINCT entity_type FROM audit_logs WHERE entity_type IS NOT NULL ORDER BY entity_type');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Pobierz pojedynczy wpis
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM audit_logs WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Wyczyść stare logi (starsze niż N dni)
     */
    public function cleanOldLogs(int $days = 90): int
    {
        $stmt = $this->pdo->prepare('DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)');
        $stmt->execute([':days' => $days]);
        return $stmt->rowCount();
    }

    /**
     * Pobierz ostatnie logi dla konkretnej encji
     */
    public function getForEntity(string $entityType, int $entityId, int $limit = 20): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM audit_logs
            WHERE entity_type = :type AND entity_id = :id
            ORDER BY created_at DESC
            LIMIT :limit
        ');
        $stmt->bindValue(':type', $entityType);
        $stmt->bindValue(':id', $entityId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
