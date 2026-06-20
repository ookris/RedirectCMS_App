<?php

require_once __DIR__ . '/QRCodeLib.php';

/**
 * Generator kodów QR z cache'owaniem
 * Używa biblioteki psyon/php-qrcode (MIT license)
 */
class QRCodeGenerator
{
    private static string $cacheDir = '';

    /**
     * Inicjalizuje katalog cache
     */
    private static function initCacheDir(): void
    {
        if (self::$cacheDir === '') {
            self::$cacheDir = __DIR__ . '/../storage/cache/qr';
            if (!is_dir(self::$cacheDir)) {
                @mkdir(self::$cacheDir, 0755, true);
            }
        }
    }

    /**
     * Generuje klucz cache na podstawie danych i rozmiaru
     */
    private static function getCacheKey(string $data, int $size): string
    {
        return md5($data . '_' . $size);
    }

    /**
     * Pobiera ścieżkę do pliku cache
     */
    private static function getCachePath(string $cacheKey): string
    {
        self::initCacheDir();
        return self::$cacheDir . '/' . $cacheKey . '.png';
    }

    /**
     * Sprawdza czy cache istnieje i jest aktualny
     */
    private static function getCached(string $cacheKey): ?string
    {
        $path = self::getCachePath($cacheKey);
        if (file_exists($path)) {
            // Cache ważny przez 30 dni
            if (filemtime($path) > time() - 30 * 24 * 3600) {
                $content = file_get_contents($path);
                return $content !== false ? $content : null;
            }
            // Stary cache - usuń
            @unlink($path);
        }
        return null;
    }

    /**
     * Zapisuje do cache
     */
    private static function saveToCache(string $cacheKey, string $data): void
    {
        $path = self::getCachePath($cacheKey);
        @file_put_contents($path, $data);
    }

    /**
     * Wygeneruj QR code jako PNG (binary data)
     */
    public static function generatePNG(string $data, int $size = 300): string
    {
        if (empty($data)) {
            return '';
        }

        $cacheKey = self::getCacheKey($data, $size);

        // Sprawdź cache
        $cached = self::getCached($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            // Oblicz skalę - biblioteka używa modułów ~10px
            $scale = max(1, (int)round($size / 33));

            $options = [
                's' => 'qrl',     // QR Low error correction
                'sf' => $scale,   // Scale factor
            ];

            $qr = new QRCode($data, $options);
            $image = $qr->render_image();

            if (!$image) {
                return '';
            }

            // Przeskaluj do żądanego rozmiaru
            $currentWidth = imagesx($image);
            $currentHeight = imagesy($image);

            if ($currentWidth !== $size || $currentHeight !== $size) {
                $resized = imagecreatetruecolor($size, $size);
                $white = imagecolorallocate($resized, 255, 255, 255);
                imagefill($resized, 0, 0, $white);

                // Zachowaj proporcje, wycentruj
                $ratio = min($size / $currentWidth, $size / $currentHeight);
                $newWidth = (int)($currentWidth * $ratio);
                $newHeight = (int)($currentHeight * $ratio);
                $x = (int)(($size - $newWidth) / 2);
                $y = (int)(($size - $newHeight) / 2);

                imagecopyresampled(
                    $resized, $image,
                    $x, $y, 0, 0,
                    $newWidth, $newHeight,
                    $currentWidth, $currentHeight
                );

                $image = $resized;
            }

            // Konwertuj do PNG string
            ob_start();
            imagepng($image, null, 9);
            $pngData = ob_get_clean();

            if ($pngData === false || $pngData === '') {
                return '';
            }

            // Zapisz do cache
            self::saveToCache($cacheKey, $pngData);

            return $pngData;

        } catch (Throwable $e) {
            error_log('QR Code generation error: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Wygeneruj QR code jako Data URI (do wstawienia w <img src>)
     */
    public static function generateDataURI(string $data, int $size = 300): string
    {
        $png = self::generatePNG($data, $size);

        if ($png === '') {
            return '';
        }

        return 'data:image/png;base64,' . base64_encode($png);
    }

    /**
     * Wygeneruj QR code i zwróć ścieżkę względną do pliku cache
     * @return string|null Ścieżka względna (np. "storage/cache/qr/abc123.png") lub null w przypadku błędu
     */
    public static function generateFile(string $data, int $size = 300): ?string
    {
        if (empty($data)) {
            return null;
        }

        $cacheKey = self::getCacheKey($data, $size);
        $cachePath = self::getCachePath($cacheKey);

        // Sprawdź czy plik już istnieje i jest aktualny
        if (file_exists($cachePath) && filemtime($cachePath) > time() - 30 * 24 * 3600) {
            return 'storage/cache/qr/' . $cacheKey . '.png';
        }

        // Wygeneruj plik
        $pngData = self::generatePNG($data, $size);
        if ($pngData === '') {
            return null;
        }

        return 'storage/cache/qr/' . $cacheKey . '.png';
    }

    /**
     * Wyczyść stary cache QR kodów
     * @param int $maxAgeDays Maksymalny wiek plików w dniach
     * @return int Liczba usuniętych plików
     */
    public static function cleanCache(int $maxAgeDays = 30): int
    {
        self::initCacheDir();

        $deleted = 0;
        $threshold = time() - $maxAgeDays * 24 * 3600;

        $files = glob(self::$cacheDir . '/*.png');
        if ($files === false) {
            return 0;
        }

        foreach ($files as $file) {
            if (filemtime($file) < $threshold) {
                if (@unlink($file)) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }

    /**
     * Pobiera ścieżkę do katalogu cache
     */
    public static function getCacheDirectory(): string
    {
        self::initCacheDir();
        return self::$cacheDir;
    }
}
