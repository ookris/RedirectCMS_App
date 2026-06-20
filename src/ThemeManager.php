<?php

class ThemeManager
{
    private string $themesDir;
    private string $defaultThemeName = 'default';
    private array $legacyThemeAliases = [
        // Legacy aliases keep existing blog_theme values working after template removals.
        'SpotBuzz' => 'SampaiSimple',
        'RoyalUI' => 'Hyde',
        'MagSpot' => 'SampaiSimple',
        'LantroUI' => 'Lanyon',
        'JetThemeNewspaper' => 'SampaiSimple',
        'Aulian' => 'Hyde',
    ];

    public function __construct(?string $projectRoot = null)
    {
        if ($projectRoot === null) {
            $projectRoot = dirname(dirname(__FILE__));
        }
        $this->themesDir = $projectRoot . '/templates';
    }

    /**
     * Pobierz listę dostępnych szablonów
     */
    public function getAvailableThemes(): array
    {
        $themes = [];

        if (!is_dir($this->themesDir)) {
            return [];
        }

        $dirs = @scandir($this->themesDir);
        if ($dirs === false) {
            return [];
        }

        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }

            $themePath = $this->themesDir . '/' . $dir;
            if (!is_dir($themePath)) {
                continue;
            }

            $themeInfo = $this->getThemeInfo($dir);
            if ($themeInfo !== null) {
                $themes[$dir] = $themeInfo;
            }
        }

        return $themes;
    }

    /**
     * Pobierz informacje o konkretnym szablonie
     */
    public function getThemeInfo(string $themeName): ?array
    {
        $themeJsonPath = $this->themesDir . '/' . $themeName . '/theme.json';

        if (!file_exists($themeJsonPath)) {
            return null;
        }

        $json = @file_get_contents($themeJsonPath);
        if ($json === false) {
            return null;
        }

        $data = json_decode($json, true);
        if (!is_array($data)) {
            return null;
        }

        // Waliduj wymagane pola
        if (empty($data['name'])) {
            return null;
        }

        // Normalizuj colors — tablica [{key, label, default}]
        $colors = [];
        if (!empty($data['colors']) && is_array($data['colors'])) {
            foreach ($data['colors'] as $color) {
                if (!empty($color['key']) && !empty($color['default'])) {
                    $colors[] = [
                        'key'     => preg_replace('/[^a-z0-9_]/', '', strtolower($color['key'])),
                        'label'   => (string)($color['label'] ?? $color['key']),
                        'default' => (string)$color['default'],
                    ];
                }
            }
        }

        // Normalizuj image_sizes — {name: {width, height, crop, label}}
        $imageSizes = [];
        if (!empty($data['image_sizes']) && is_array($data['image_sizes'])) {
            foreach ($data['image_sizes'] as $sizeKey => $sizeData) {
                if (!is_array($sizeData)) {
                    continue;
                }
                $key = preg_replace('/[^a-z0-9_]/', '', strtolower($sizeKey));
                if ($key === '') {
                    continue;
                }
                $imageSizes[$key] = [
                    'width'  => max(1, (int)($sizeData['width']  ?? 800)),
                    'height' => max(1, (int)($sizeData['height'] ?? 600)),
                    'crop'   => (bool)($sizeData['crop'] ?? true),
                    'label'  => (string)($sizeData['label'] ?? $key),
                ];
            }
        }

        // Normalizuj supports — tablica stringów
        $supports = [];
        if (!empty($data['supports']) && is_array($data['supports'])) {
            $allowed = ['sidebar', 'contact_page', 'gallery', 'lightbox'];
            foreach ($data['supports'] as $feature) {
                if (is_string($feature) && in_array($feature, $allowed, true)) {
                    $supports[] = $feature;
                }
            }
        }

        return [
            'id'           => $themeName,
            'name'         => (string)$data['name'],
            'author'       => (string)($data['author'] ?? 'Nieznany autor'),
            'version'      => (string)($data['version'] ?? '0.0.0'),
            'description'  => (string)($data['description'] ?? ''),
            'published_date' => (string)($data['published_date'] ?? ''),
            'email'        => (string)($data['email'] ?? ''),
            'supports'     => $supports,
            'colors'       => $colors,
            'image_sizes'  => $imageSizes,
            'has_home'     => $this->hasTemplateFile($themeName, 'home'),
            'has_post'     => $this->hasTemplateFile($themeName, 'post'),
            'has_page'     => $this->hasTemplateFile($themeName, 'page'),
            'has_contact'  => $this->hasTemplateFile($themeName, 'contact'),
        ];
    }

    /**
     * Sprawdź czy szablon obsługuje daną funkcję
     */
    public function supports(string $themeName, string $feature): bool
    {
        $info = $this->getThemeInfo($themeName);
        if ($info === null) {
            return false;
        }
        return in_array($feature, $info['supports'], true);
    }

    /**
     * Pobierz definicje kolorów szablonu
     */
    public function getThemeColors(string $themeName): array
    {
        $info = $this->getThemeInfo($themeName);
        return $info['colors'] ?? [];
    }

    /**
     * Pobierz rozmiary obrazków zdefiniowane przez szablon
     */
    public function getImageSizes(string $themeName): array
    {
        $info = $this->getThemeInfo($themeName);
        return $info['image_sizes'] ?? [];
    }

    /**
     * Wygeneruj blok <style> z CSS custom properties na podstawie kolorów
     * $savedColors to tablica [key => value] z bazy danych (nadpisuje defaulty)
     */
    public function generateColorCss(string $themeName, array $savedColors = []): string
    {
        $colorDefs = $this->getThemeColors($themeName);
        if (empty($colorDefs)) {
            return '';
        }

        $vars = [];
        foreach ($colorDefs as $color) {
            $key   = $color['key'];
            $value = $savedColors[$key] ?? $color['default'];
            // Akceptuj tylko wartości hex, rgb(), rgba(), hsl() lub nazwane kolory CSS
            if (!$this->isValidCssColor($value)) {
                $value = $color['default'];
            }
            $vars[] = '    --theme-' . $key . ': ' . $value . ';';
        }

        return "<style>\n:root {\n" . implode("\n", $vars) . "\n}\n</style>\n";
    }

    /**
     * Zwróć tablicę domyślnych kolorów [key => default] dla szablonu
     */
    public function getDefaultColors(string $themeName): array
    {
        $defaults = [];
        foreach ($this->getThemeColors($themeName) as $color) {
            $defaults[$color['key']] = $color['default'];
        }
        return $defaults;
    }

    /**
     * Sprawdź czy szablon ma plik (home.php, post.php, page.php, contact.php itp.)
     */
    private function hasTemplateFile(string $themeName, string $fileName): bool
    {
        $filePath = $this->themesDir . '/' . $themeName . '/' . $fileName . '.php';
        return file_exists($filePath);
    }

    /**
     * Sprawdź czy szablon jest kompletny (ma wymagane pliki)
     */
    public function isThemeComplete(string $themeName): bool
    {
        return $this->hasTemplateFile($themeName, 'home') && $this->hasTemplateFile($themeName, 'post');
    }

    /**
     * Pobierz ścieżkę do pliku szablonu
     */
    public function getTemplateFilePath(string $themeName, string $templateName): ?string
    {
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $themeName) || !preg_match('/^[a-zA-Z0-9_-]+$/', $templateName)) {
            return null;
        }

        $filePath = $this->themesDir . '/' . $themeName . '/' . $templateName . '.php';

        if (!file_exists($filePath)) {
            return null;
        }

        return $filePath;
    }

    /**
     * Załaduj szablon (ze fallbackiem na domyślny)
     */
    public function loadTemplate(string $themeName, string $templateName): ?string
    {
        $filePath = $this->getTemplateFilePath($themeName, $templateName);

        if ($filePath === null && $themeName !== $this->defaultThemeName) {
            $filePath = $this->getTemplateFilePath($this->defaultThemeName, $templateName);
        }

        return $filePath;
    }

    /**
     * Pobierz domyślny szablon
     */
    public function getDefaultThemeName(): string
    {
        return $this->defaultThemeName;
    }

    /**
     * Sprawdź czy szablon istnieje i czy jest kompletny
     */
    public function validateTheme(string $themeName): bool
    {
        $info = $this->getThemeInfo($themeName);
        return $info !== null && $this->isThemeComplete($themeName);
    }

    /**
     * Pobierz bezpieczną nazwę szablonu (fallback na domyślny jeśli invalid)
     */
    public function getSafeThemeName(string $themeName): string
    {
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $themeName)) {
            return $this->defaultThemeName;
        }

        $themeName = $this->legacyThemeAliases[$themeName] ?? $themeName;

        if (!$this->validateTheme($themeName)) {
            return $this->defaultThemeName;
        }

        return $themeName;
    }

    /**
     * Prosta walidacja wartości koloru CSS (hex, rgb, rgba, hsl, hsla, named)
     */
    private function isValidCssColor(string $value): bool
    {
        $value = trim($value);
        // hex: #abc, #aabbcc, #aabbccdd
        if (preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $value)) {
            return true;
        }
        // rgb() / rgba() / hsl() / hsla()
        if (preg_match('/^(rgb|rgba|hsl|hsla)\([^)]{1,80}\)$/', $value)) {
            return true;
        }
        // named colors (tylko litery)
        if (preg_match('/^[a-zA-Z]{2,30}$/', $value)) {
            return true;
        }
        return false;
    }
}
