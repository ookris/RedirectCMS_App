<?php
declare(strict_types=1);

class ImageOptimizerDetector
{
    private const ALLOWED_BINARIES = ['jpegoptim', 'pngquant', 'optipng'];

    /**
     * Detects available image optimization capabilities on this server.
     *
     * @return array<string, array{label: string, description: string, available: bool, formats: array, binaries?: array}>
     */
    public static function detect(): array
    {
        $execOk   = self::execEnabled();
        $hasJpeg  = $execOk && self::hasBinary('jpegoptim');
        $hasPngQ  = $execOk && self::hasBinary('pngquant');
        $hasPngO  = $execOk && self::hasBinary('optipng');
        $hasPng   = $hasPngQ || $hasPngO;

        return [
            'none' => [
                'label'       => 'Brak optymalizacji',
                'description' => 'Pliki zapisywane przez GD ze standardową jakością (JPEG 88, PNG 8). Brak dodatkowej obróbki.',
                'available'   => true,
                'formats'     => [],
            ],
            'gd' => [
                'label'       => 'GD — zmniejszona jakość',
                'description' => 'Zapisuje JPEG z jakością 78 zamiast 88 i PNG z poziomem 9. Mniejszy rozmiar pliku kosztem drobnej utraty jakości. Nie wymaga żadnych dodatkowych rozszerzeń.',
                'available'   => true,
                'formats'     => ['jpg', 'png'],
            ],
            'imagick' => [
                'label'       => 'Imagick',
                'description' => 'Usuwa metadane EXIF, generuje progresywne JPEG, lepsza kompresja PNG. Wymaga rozszerzenia PHP Imagick zainstalowanego na serwerze.',
                'available'   => extension_loaded('imagick'),
                'formats'     => ['jpg', 'png', 'webp'],
            ],
            'exec_tools' => [
                'label'       => 'Narzędzia systemowe',
                'description' => 'Optymalizacja po cropowaniu przez jpegoptim (JPEG) i/lub pngquant/optipng (PNG). Wymaga exec() oraz zainstalowanych narzędzi na serwerze.',
                'available'   => $execOk && ($hasJpeg || $hasPng),
                'formats'     => array_values(array_filter([
                    $hasJpeg ? 'jpg' : null,
                    $hasPng  ? 'png' : null,
                ])),
                'binaries'    => [
                    'jpegoptim' => $hasJpeg,
                    'pngquant'  => $hasPngQ,
                    'optipng'   => $hasPngO,
                ],
            ],
        ];
    }

    /**
     * Returns the key of the best available driver (excluding 'none').
     */
    public static function getBest(array $capabilities): string
    {
        foreach (['imagick', 'exec_tools', 'gd'] as $key) {
            if (!empty($capabilities[$key]['available'])) {
                return $key;
            }
        }
        return 'none';
    }

    /**
     * Checks whether exec() is callable (not disabled by PHP config).
     */
    private static function execEnabled(): bool
    {
        if (!function_exists('exec')) {
            return false;
        }
        $disabled = array_map('trim', explode(',', (string)ini_get('disable_functions')));
        return !in_array('exec', $disabled, true);
    }

    /**
     * Checks whether a whitelisted system binary exists on PATH.
     */
    private static function hasBinary(string $bin): bool
    {
        if (!in_array($bin, self::ALLOWED_BINARIES, true)) {
            return false;
        }
        $out = [];
        @exec('which ' . escapeshellarg($bin) . ' 2>/dev/null', $out);
        return isset($out[0]) && str_starts_with((string)$out[0], '/');
    }
}
