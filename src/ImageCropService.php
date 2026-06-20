<?php
declare(strict_types=1);

/**
 * ImageCropService — crop/resize images to theme-defined sizes using GD.
 * Cropped variants are stored in uploads/cropped/{sizeKey}/ preserving original subdirs.
 */
class ImageCropService
{
    private const GD_JPEG_QUALITY       = 78;
    private const GD_PNG_COMPRESSION    = 9;
    private const IMAGICK_JPEG_QUALITY  = 82;
    private const IMAGICK_PNG_QUALITY   = 85;
    private const IMAGICK_WEBP_QUALITY  = 82;
    private const JPEGOPTIM_MAX_QUALITY = 82;
    private const PNGQUANT_QUALITY_MIN  = 65;
    private const PNGQUANT_QUALITY_MAX  = 85;
    private const OPTIPNG_LEVEL         = 2;

    /**
     * Returns the default quality settings for each driver.
     * This is the single source of truth — SettingsController references this instead of duplicating the values.
     */
    public static function defaultSettings(): array
    {
        return [
            'gd' => [
                'jpeg_quality'    => self::GD_JPEG_QUALITY,
                'png_compression' => self::GD_PNG_COMPRESSION,
            ],
            'imagick' => [
                'jpeg_quality' => self::IMAGICK_JPEG_QUALITY,
                'png_quality'  => self::IMAGICK_PNG_QUALITY,
                'webp_quality' => self::IMAGICK_WEBP_QUALITY,
            ],
            'exec_tools' => [
                'jpegoptim_max' => self::JPEGOPTIM_MAX_QUALITY,
                'pngquant_min'  => self::PNGQUANT_QUALITY_MIN,
                'pngquant_max'  => self::PNGQUANT_QUALITY_MAX,
                'optipng_level' => self::OPTIPNG_LEVEL,
            ],
        ];
    }

    private string $uploadsRoot;
    private int $jpegQuality    = 88;
    private int $pngCompression = 8;
    /** Cached result of pngquant binary detection (null = not yet checked). */
    private ?bool $hasPngquant  = null;

    public function __construct(string $uploadsRoot)
    {
        $this->uploadsRoot = rtrim($uploadsRoot, '/');
    }

    /**
     * Apply GD quality settings from the settings array.
     * Falls back to recommended defaults if keys are missing.
     */
    public function applyGdSettings(array $settings): void
    {
        $this->jpegQuality    = max(1, min(100, (int)($settings['jpeg_quality']    ?? self::GD_JPEG_QUALITY)));
        $this->pngCompression = max(0, min(9,   (int)($settings['png_compression'] ?? self::GD_PNG_COMPRESSION)));
    }

    /**
     * Returns the absolute path where the cropped variant for a given size key would be stored.
     * E.g. uploads/2024/01/photo.jpg → uploads/cropped/featured/2024/01/photo.jpg
     */
    public function getCroppedPath(string $originalRelPath, string $sizeKey): string
    {
        $appRoot = dirname($this->uploadsRoot);
        // originalRelPath is relative to app root, e.g. "uploads/2024/01/photo.jpg"
        $withoutUploads = preg_replace('#^uploads/#', '', $originalRelPath);
        return $this->uploadsRoot . '/cropped/' . $sizeKey . '/' . $withoutUploads;
    }

    /**
     * Returns the relative path (from app root) for the cropped variant.
     */
    public function getCroppedRelPath(string $originalRelPath, string $sizeKey): string
    {
        $withoutUploads = preg_replace('#^uploads/#', '', $originalRelPath);
        return 'uploads/cropped/' . $sizeKey . '/' . $withoutUploads;
    }

    /**
     * Crop/resize a single image to the given dimensions.
     * If $crop = true: scale to cover target, then center-crop.
     * If $crop = false: scale to fit (letterbox), no crop.
     * Returns true on success, false on failure.
     */
    public function cropImage(string $srcAbsPath, string $dstAbsPath, int $targetW, int $targetH, bool $crop): bool
    {
        if (!file_exists($srcAbsPath)) {
            return false;
        }

        $imageInfo = @getimagesize($srcAbsPath);
        if ($imageInfo === false) {
            return false;
        }

        [$srcW, $srcH, $typeConst] = $imageInfo;
        $mimeType = $imageInfo['mime'];

        $srcImage = match ($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($srcAbsPath),
            'image/png'  => @imagecreatefrompng($srcAbsPath),
            'image/gif'  => @imagecreatefromgif($srcAbsPath),
            'image/webp' => @imagecreatefromwebp($srcAbsPath),
            default      => false,
        };

        if ($srcImage === false) {
            return false;
        }

        // Create output canvas
        $dstImage = imagecreatetruecolor($targetW, $targetH);
        if ($dstImage === false) {
            imagedestroy($srcImage);
            return false;
        }

        // Transparency for PNG/GIF
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagealphablending($dstImage, false);
            imagesavealpha($dstImage, true);
            $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
            imagefilledrectangle($dstImage, 0, 0, $targetW, $targetH, $transparent);
        } else {
            // Fill white background for JPEG
            $white = imagecolorallocate($dstImage, 255, 255, 255);
            imagefilledrectangle($dstImage, 0, 0, $targetW, $targetH, $white);
        }

        if ($crop) {
            // Scale to cover target size, then center-crop
            $scaleW = $targetW / $srcW;
            $scaleH = $targetH / $srcH;
            $scale  = max($scaleW, $scaleH);

            $scaledW = (int)round($srcW * $scale);
            $scaledH = (int)round($srcH * $scale);

            // Source crop offsets (in original image coordinates)
            $srcOffsetX = (int)round(($scaledW - $targetW) / 2 / $scale);
            $srcOffsetY = (int)round(($scaledH - $targetH) / 2 / $scale);
            $srcCropW   = (int)round($targetW / $scale);
            $srcCropH   = (int)round($targetH / $scale);

            imagecopyresampled(
                $dstImage, $srcImage,
                0, 0,
                $srcOffsetX, $srcOffsetY,
                $targetW, $targetH,
                $srcCropW, $srcCropH
            );
        } else {
            // Scale to fit inside target, center on canvas
            $scaleW = $targetW / $srcW;
            $scaleH = $targetH / $srcH;
            $scale  = min($scaleW, $scaleH, 1.0); // never upscale

            $newW = (int)round($srcW * $scale);
            $newH = (int)round($srcH * $scale);
            $dstX = (int)round(($targetW - $newW) / 2);
            $dstY = (int)round(($targetH - $newH) / 2);

            imagecopyresampled(
                $dstImage, $srcImage,
                $dstX, $dstY,
                0, 0,
                $newW, $newH,
                $srcW, $srcH
            );
        }

        // Ensure destination directory exists
        $dstDir = dirname($dstAbsPath);
        if (!is_dir($dstDir)) {
            if (!@mkdir($dstDir, 0755, true) && !is_dir($dstDir)) {
                imagedestroy($srcImage);
                imagedestroy($dstImage);
                return false;
            }
        }

        // Save (quality adjusted by $this->jpegQuality / $this->pngCompression)
        $ext = strtolower(pathinfo($srcAbsPath, PATHINFO_EXTENSION));
        $success = match ($ext) {
            'jpg', 'jpeg' => imagejpeg($dstImage, $dstAbsPath, $this->jpegQuality),
            'png'         => imagepng($dstImage, $dstAbsPath, $this->pngCompression),
            'gif'         => imagegif($dstImage, $dstAbsPath),
            'webp'        => imagewebp($dstImage, $dstAbsPath, $this->jpegQuality),
            default       => imagejpeg($dstImage, $dstAbsPath, $this->jpegQuality),
        };

        imagedestroy($srcImage);
        imagedestroy($dstImage);

        return $success !== false;
    }

    /**
     * Apply post-crop optimization to a file using the given driver.
     * 'none' and 'gd' are no-ops here (quality is set during cropImage).
     * $settings overrides the class constants for that driver.
     */
    public function optimize(string $filePath, string $driver, array $settings = []): bool
    {
        if ($driver === 'none' || $driver === 'gd' || !file_exists($filePath)) {
            return true;
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($driver === 'imagick') {
            if (!extension_loaded('imagick')) {
                return false;
            }
            $jpegQ = max(1, min(100, (int)($settings['jpeg_quality'] ?? self::IMAGICK_JPEG_QUALITY)));
            $pngQ  = max(1, min(100, (int)($settings['png_quality']  ?? self::IMAGICK_PNG_QUALITY)));
            $webpQ = max(1, min(100, (int)($settings['webp_quality'] ?? self::IMAGICK_WEBP_QUALITY)));
            try {
                $img = new \Imagick($filePath);
                $img->stripImage();
                if (in_array($ext, ['jpg', 'jpeg'], true)) {
                    $img->setInterlaceScheme(\Imagick::INTERLACE_PLANE);
                    $img->setImageCompressionQuality($jpegQ);
                } elseif ($ext === 'png') {
                    $img->setImageCompressionQuality($pngQ);
                } elseif ($ext === 'webp') {
                    $img->setImageCompressionQuality($webpQ);
                }
                $result = $img->writeImage($filePath);
                $img->destroy();
                return $result;
            } catch (\Throwable $e) {
                error_log(__CLASS__ . ' imagick error: ' . $e->getMessage());
                return false;
            }
        }

        if ($driver === 'exec_tools') {
            $safe      = escapeshellarg($filePath);
            $exitCode  = 0;
            $jpegMax   = max(1,  min(100, (int)($settings['jpegoptim_max'] ?? self::JPEGOPTIM_MAX_QUALITY)));
            $pngMin    = max(0,  min(100, (int)($settings['pngquant_min']  ?? self::PNGQUANT_QUALITY_MIN)));
            $pngMax    = max(0,  min(100, (int)($settings['pngquant_max']  ?? self::PNGQUANT_QUALITY_MAX)));
            $optiLevel = max(0,  min(7,   (int)($settings['optipng_level'] ?? self::OPTIPNG_LEVEL)));

            if (in_array($ext, ['jpg', 'jpeg'], true)) {
                @exec('jpegoptim --max=' . (int)$jpegMax . ' --strip-all ' . $safe . ' 2>/dev/null', $_, $exitCode);
                return $exitCode === 0;
            } elseif ($ext === 'png') {
                // Detect pngquant once per service instance; fall back to optipng (lossless)
                if ($this->hasPngquant === null) {
                    $which = [];
                    @exec('which pngquant 2>/dev/null', $which);
                    $this->hasPngquant = !empty($which[0]) && str_starts_with((string)$which[0], '/');
                }
                if ($this->hasPngquant) {
                    @exec(
                        'pngquant --force --quality=' . (int)$pngMin . '-' . (int)$pngMax
                        . ' --output ' . $safe . ' ' . $safe . ' 2>/dev/null',
                        $_,
                        $exitCode
                    );
                } else {
                    @exec('optipng -o' . (int)$optiLevel . ' -quiet ' . $safe . ' 2>/dev/null', $_, $exitCode);
                }
                return $exitCode === 0;
            }
            return true;
        }

        return true;
    }

    /**
     * Process all images from the links table (og_image + gallery images).
     * $force: when true, re-process even if the cropped file is newer than the source.
     * $sizeFilter: when non-empty, only process the listed size keys.
     * Returns ['processed' => int, 'skipped' => int, 'failed' => int]
     */
    public function regenerateForLinks(PrefixedPDO $pdo, array $imageSizes, string $driver = 'none', array $driverSettings = [], bool $force = false, array $sizeFilter = []): array
    {
        $appRoot = dirname($this->uploadsRoot);
        $stats = ['processed' => 0, 'skipped' => 0, 'failed' => 0];

        if ($driver === 'gd') {
            $this->applyGdSettings($driverSettings['gd'] ?? []);
        }

        // Collect all image paths from links
        $paths = [];

        // og_image column
        $stmt = $pdo->query("SELECT og_image FROM " . Database::table('links') . " WHERE og_image IS NOT NULL AND og_image != '' AND status != 'trashed'");
        if ($stmt) {
            foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $p) {
                $paths[$p] = true;
            }
        }

        // Gallery images
        $stmt = $pdo->query("SELECT path FROM " . Database::table('link_images') . " WHERE path IS NOT NULL AND path != ''");
        if ($stmt) {
            foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $p) {
                $paths[$p] = true;
            }
        }

        foreach (array_keys($paths) as $relPath) {
            $absPath = $appRoot . '/' . ltrim($relPath, '/');
            if (!file_exists($absPath)) {
                $stats['skipped']++;
                continue;
            }

            foreach ($imageSizes as $sizeKey => $size) {
                // Apply size filter if specified
                if (!empty($sizeFilter) && !in_array($sizeKey, $sizeFilter, true)) {
                    continue;
                }

                $dstAbs = $this->getCroppedPath($relPath, $sizeKey);
                // Skip if already up to date (dst newer than src) — unless force is requested
                if (!$force && file_exists($dstAbs) && filemtime($dstAbs) >= filemtime($absPath)) {
                    $stats['skipped']++;
                    continue;
                }
                $ok = $this->cropImage($absPath, $dstAbs, (int)$size['width'], (int)$size['height'], !empty($size['crop']));
                if ($ok) {
                    $this->optimize($dstAbs, $driver, $driverSettings[$driver] ?? []);
                    $stats['processed']++;
                } else {
                    $stats['failed']++;
                }
            }
        }

        return $stats;
    }
}
