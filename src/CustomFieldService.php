<?php

require_once __DIR__ . '/SettingsRepository.php';

class CustomFieldService
{
    private const SETTINGS_KEY = 'custom_link_fields';

    public function __construct(private $pdo) {}

    public function getDefaultDefinitions(): array
    {
        return [
            ['key' => 'avg_rating', 'label' => 'Średnia ocena', 'type' => 'number', 'placeholder' => '4.8', 'step' => '0.1'],
            ['key' => 'sales', 'label' => 'Sprzedaż', 'type' => 'number', 'placeholder' => '1250'],
            ['key' => 'positive_reviews', 'label' => 'Pozytywne opinie', 'type' => 'number', 'placeholder' => '320'],
            ['key' => 'price', 'label' => 'Cena', 'type' => 'text', 'placeholder' => '199 PLN'],
        ];
    }

    public function getDefinitions(): array
    {
        $settingsRepo = new SettingsRepository($this->pdo);
        $raw = $settingsRepo->get(self::SETTINGS_KEY, '');
        $decoded = is_array($raw) ? $raw : json_decode((string)$raw, true);

        if (is_array($decoded) && !empty($decoded)) {
            return $this->normalizeDefinitions($decoded, false);
        }

        return $this->getDefaultDefinitions();
    }

    public function saveDefinitions(array $definitions): array
    {
        $normalized = $this->normalizeDefinitions($definitions, true);
        $settingsRepo = new SettingsRepository($this->pdo);
        $settingsRepo->set(self::SETTINGS_KEY, json_encode($normalized));
        return $normalized;
    }

    private function normalizeDefinitions(array $definitions, bool $strict = false): array
    {
        $allowedTypes = ['text', 'number'];
        $seenKeys = [];
        $result = [];

        foreach ($definitions as $def) {
            $key = strtolower(trim((string)($def['key'] ?? '')));
            $label = trim((string)($def['label'] ?? ''));
            $type = strtolower(trim((string)($def['type'] ?? 'text')));
            $placeholder = trim((string)($def['placeholder'] ?? ''));
            $step = trim((string)($def['step'] ?? ''));

            if ($key === '' || $label === '') {
                if ($strict) {
                    throw new InvalidArgumentException('Każde pole musi mieć klucz i etykietę.');
                }
                continue;
            }

            $key = preg_replace('/[^a-z0-9_-]/', '_', $key);
            if ($key === '' || isset($seenKeys[$key])) {
                if ($strict) {
                    throw new InvalidArgumentException('Klucze pól muszą być unikalne i zawierać tylko litery, cyfry, podkreślenia lub myślniki.');
                }
                continue;
            }
            $seenKeys[$key] = true;

            if (!in_array($type, $allowedTypes, true)) {
                $type = 'text';
            }

            $entry = [
                'key' => $key,
                'label' => $label,
                'type' => $type,
            ];

            if ($placeholder !== '') {
                $entry['placeholder'] = $placeholder;
            }

            if ($type === 'number' && $step !== '') {
                $entry['step'] = $step;
            }

            $result[] = $entry;
        }

        if (empty($result)) {
            return $this->getDefaultDefinitions();
        }

        return $result;
    }
}
