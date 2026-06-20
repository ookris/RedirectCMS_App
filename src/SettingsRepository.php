<?php

require_once __DIR__ . '/Database.php';

class SettingsRepository
{
    public function __construct(private $pdo) {}

    private function t(string $name): string
    {
        return Database::table($name);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $t = $this->t('settings');
        $stmt = $this->pdo->prepare("SELECT value FROM {$t} WHERE `key` = :k");
        $stmt->execute([':k' => $key]);
        $row = $stmt->fetch();
        return $row ? $this->decode($row['value']) : $default;
    }

    public function set(string $key, mixed $value): void
    {
        $t = $this->t('settings');
        $stmt = $this->pdo->prepare("REPLACE INTO {$t}(`key`, `value`) VALUES (:k, :v)");
        $stmt->execute([':k' => $key, ':v' => $this->encode($value)]);
    }

    public function delete(string $key): void
    {
        $t = $this->t('settings');
        $stmt = $this->pdo->prepare("DELETE FROM {$t} WHERE `key` = :k");
        $stmt->execute([':k' => $key]);
    }

    private function encode(mixed $value): string
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        return (string)$value;
    }

    private function decode(string $value): mixed
    {
        $trim = trim($value);
        if ($trim === '') return '';
        $json = json_decode($value, true);
        return (json_last_error() === JSON_ERROR_NONE) ? $json : $value;
    }
}
