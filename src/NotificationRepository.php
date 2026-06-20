<?php
declare(strict_types=1);

class NotificationRepository
{
    public function __construct(private $pdo) {}

    /**
     * Utwórz nowe powiadomienie
     */
    public function create(
        string $component,
        string $title,
        ?string $message = null,
        string $severity = 'error',
        ?array $context = null
    ): int {
        $stmt = $this->pdo->prepare('
            INSERT INTO notifications (component, severity, title, message, context)
            VALUES (:component, :severity, :title, :message, :context)
        ');
        $stmt->execute([
            ':component' => $component,
            ':severity' => $severity,
            ':title' => $title,
            ':message' => $message,
            ':context' => $context ? json_encode($context, JSON_UNESCAPED_UNICODE) : null,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Policz nieprzeczytane powiadomienia (dla badge)
     */
    public function countUnread(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM notifications WHERE is_read = 0');
        return (int)$stmt->fetchColumn();
    }

    /**
     * Pobierz listę powiadomień z filtrowaniem i paginacją
     */
    public function list(
        int $limit = 50,
        int $offset = 0,
        ?string $component = null,
        ?string $severity = null,
        ?string $readStatus = null
    ): array {
        $where = [];
        $params = [];

        if ($component) {
            $where[] = 'component = :component';
            $params[':component'] = $component;
        }
        if ($severity) {
            $where[] = 'severity = :severity';
            $params[':severity'] = $severity;
        }
        if ($readStatus === 'unread') {
            $where[] = 'is_read = 0';
        } elseif ($readStatus === 'read') {
            $where[] = 'is_read = 1';
        }

        $sql = 'SELECT * FROM notifications';
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
     * Policz powiadomienia z tymi samymi filtrami (dla paginacji)
     */
    public function count(
        ?string $component = null,
        ?string $severity = null,
        ?string $readStatus = null
    ): int {
        $where = [];
        $params = [];

        if ($component) {
            $where[] = 'component = :component';
            $params[':component'] = $component;
        }
        if ($severity) {
            $where[] = 'severity = :severity';
            $params[':severity'] = $severity;
        }
        if ($readStatus === 'unread') {
            $where[] = 'is_read = 0';
        } elseif ($readStatus === 'read') {
            $where[] = 'is_read = 1';
        }

        $sql = 'SELECT COUNT(*) FROM notifications';
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Oznacz powiadomienie jako przeczytane
     */
    public function markAsRead(int $id): void
    {
        $stmt = $this->pdo->prepare('UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    /**
     * Oznacz wszystkie nieprzeczytane jako przeczytane
     */
    public function markAllAsRead(): void
    {
        $this->pdo->exec('UPDATE notifications SET is_read = 1, read_at = NOW() WHERE is_read = 0');
    }

    /**
     * Usuń stare powiadomienia (starsze niż N dni)
     */
    public function cleanOldNotifications(int $days = 90): int
    {
        $stmt = $this->pdo->prepare('DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)');
        $stmt->execute([':days' => $days]);
        return $stmt->rowCount();
    }

    /**
     * Pobierz unikalne nazwy komponentów (dla filtra)
     */
    public function getComponents(): array
    {
        $stmt = $this->pdo->query('SELECT DISTINCT component FROM notifications ORDER BY component');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
