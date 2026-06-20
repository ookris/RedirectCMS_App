<?php
declare(strict_types=1);

/**
 * Serwis do logowania akcji administratora
 */
class AuditService
{
    private AuditLogRepository $repo;

    public function __construct($pdo)
    {
        $this->repo = new AuditLogRepository($pdo);
    }

    /**
     * Zaloguj akcję administratora
     */
    public function log(
        string $actionType,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $entityName = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;

        $this->repo->log($actionType, $entityType, $entityId, $entityName, $oldValues, $newValues, $ip, $ua);
    }

    /**
     * Wyodrębnij kluczowe pola z linku do logowania
     */
    public function extractLinkFields(array $link): array
    {
        return [
            'slug' => $link['slug'] ?? null,
            'target_url' => $link['target_url'] ?? null,
            'status' => $link['status'] ?? null,
            'category_id' => $link['category_id'] ?? null,
            'affiliate_program_id' => $link['affiliate_program_id'] ?? null,
            'publish_at' => $link['publish_at'] ?? null,
            'expires_at' => $link['expires_at'] ?? null,
            'delay_seconds' => $link['delay_seconds'] ?? null,
        ];
    }

    /**
     * Wyodrębnij pola z kategorii do logowania
     */
    public function extractCategoryFields(array $cat): array
    {
        return [
            'name' => $cat['name'] ?? null,
            'slug' => $cat['slug'] ?? null,
            'color' => $cat['color'] ?? null,
        ];
    }

    /**
     * Wyodrębnij pola z tagu do logowania
     */
    public function extractTagFields(array $tag): array
    {
        return [
            'name' => $tag['name'] ?? null,
            'slug' => $tag['slug'] ?? null,
        ];
    }

    /**
     * Wyodrębnij pola z programu partnerskiego do logowania
     */
    public function extractAffiliateProgramFields(array $prog): array
    {
        return [
            'name' => $prog['name'] ?? null,
            'color' => $prog['color'] ?? null,
        ];
    }

    /**
     * Porównaj dwie tablice i zwróć tylko zmienione pola
     */
    public function getChangedFields(array $old, array $new): array
    {
        $changed = [];
        foreach ($new as $key => $value) {
            if (!array_key_exists($key, $old) || $old[$key] !== $value) {
                $changed[$key] = $value;
            }
        }
        return $changed;
    }

    /**
     * Zaloguj login użytkownika
     */
    public function logLogin(string $username): void
    {
        $this->log('login', null, null, $username);
    }

    /**
     * Zaloguj nieudaną próbę logowania
     */
    public function logFailedLogin(string $username): void
    {
        $this->log('login_failed', null, null, $username);
    }

    /**
     * Zaloguj wylogowanie
     */
    public function logLogout(): void
    {
        $this->log('logout');
    }

    /**
     * Zaloguj utworzenie encji
     */
    public function logCreate(string $entityType, int $entityId, ?string $entityName = null, ?array $values = null): void
    {
        $this->log('create', $entityType, $entityId, $entityName, null, $values);
    }

    /**
     * Zaloguj aktualizację encji
     */
    public function logUpdate(string $entityType, int $entityId, ?string $entityName = null, ?array $oldValues = null, ?array $newValues = null): void
    {
        $this->log('update', $entityType, $entityId, $entityName, $oldValues, $newValues);
    }

    /**
     * Zaloguj usunięcie encji (soft delete)
     */
    public function logDelete(string $entityType, int $entityId, ?string $entityName = null, ?array $values = null): void
    {
        $this->log('delete', $entityType, $entityId, $entityName, $values, null);
    }

    /**
     * Zaloguj trwałe usunięcie encji
     */
    public function logPermanentDelete(string $entityType, int $entityId, ?string $entityName = null): void
    {
        $this->log('permanent_delete', $entityType, $entityId, $entityName);
    }

    /**
     * Zaloguj przywrócenie encji
     */
    public function logRestore(string $entityType, int $entityId, ?string $entityName = null): void
    {
        $this->log('restore', $entityType, $entityId, $entityName);
    }

    /**
     * Zaloguj duplikację encji
     */
    public function logDuplicate(string $entityType, int $newId, ?string $newName = null, int $sourceId = 0): void
    {
        $this->log('duplicate', $entityType, $newId, $newName, ['source_id' => $sourceId], null);
    }

    /**
     * Zaloguj zmianę ustawień
     */
    public function logSettingsChange(array $changedSettings): void
    {
        $this->log('settings_change', 'settings', null, null, null, $changedSettings);
    }

    /**
     * Zaloguj operację masową
     */
    public function logBulkAction(string $action, array $ids, ?string $details = null): void
    {
        $this->log('bulk_action', 'link', null, $details, [
            'action' => $action,
            'count' => count($ids),
            'ids' => $ids,
        ], null);
    }

    /**
     * Zaloguj akcję na zadaniu cron
     */
    public function logCronAction(string $action, int $jobId, string $jobName): void
    {
        $this->log($action, 'cron_job', $jobId, $jobName);
    }
}
