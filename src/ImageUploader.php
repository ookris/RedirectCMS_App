<?php

class ImageUploader
{
    private const DEFAULT_MAX_FILE_SIZE = 5_242_880; // 5 MB

    private string $uploadDir;
    private int $maxFileSize = self::DEFAULT_MAX_FILE_SIZE;
    private array $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private int $minWidth = 200;
    private int $minHeight = 200;
    
    public function __construct(
        string $uploadDir = 'uploads',
        ?int $maxFileSize = null,
        ?array $allowedTypes = null,
        ?int $minWidth = null,
        ?int $minHeight = null
    ) {
        $this->uploadDir = rtrim($uploadDir, '/');
        if ($maxFileSize !== null) {
            $this->maxFileSize = $maxFileSize;
        }
        if ($allowedTypes !== null) {
            $this->allowedTypes = $allowedTypes;
        }
        if ($minWidth !== null) {
            $this->minWidth = $minWidth;
        }
        if ($minHeight !== null) {
            $this->minHeight = $minHeight;
        }
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Upload image and return relative path (from project root)
     * @throws InvalidArgumentException
     */
    public function upload(array $file): string
    {
        $result = $this->processUpload($file, false);
        return $result['path'];
    }

    /**
     * Upload image together with thumbnail; returns array with paths.
     * @throws InvalidArgumentException
     */
    public function uploadWithThumbnail(array $file, int $thumbMaxWidth = 400, int $thumbMaxHeight = 400): array
    {
        return $this->processUpload($file, true, $thumbMaxWidth, $thumbMaxHeight);
    }
    
    /**
     * Delete uploaded image
     */
    public function delete(string $filepath): bool
    {
        // Jeśli to względna ścieżka, zamień na bezwzględną
        if (strpos($filepath, 'uploads/') === 0) {
            $filepath = __DIR__ . '/../' . $filepath;
        }

        if (file_exists($filepath) && strpos(realpath($filepath), realpath($this->uploadDir)) === 0) {
            return unlink($filepath);
        }
        return false;
    }

    /**
     * Delete category icon with its thumbnail
     * @param string|null $iconPath Path to icon (800x800)
     * @param string|null $thumbPath Path to thumbnail (400x400)
     * @return bool True if at least one file was deleted
     */
    public function deleteCategoryIcon(?string $iconPath, ?string $thumbPath): bool
    {
        $deletedAny = false;

        if ($iconPath !== null && $iconPath !== '') {
            $fullIconPath = $iconPath;
            if (strpos($iconPath, 'uploads/') === 0) {
                $fullIconPath = __DIR__ . '/../' . $iconPath;
            }
            if (file_exists($fullIconPath)) {
                if (unlink($fullIconPath)) {
                    $deletedAny = true;
                }
            }
        }

        if ($thumbPath !== null && $thumbPath !== '') {
            $fullThumbPath = $thumbPath;
            if (strpos($thumbPath, 'uploads/') === 0) {
                $fullThumbPath = __DIR__ . '/../' . $thumbPath;
            }
            if (file_exists($fullThumbPath)) {
                if (unlink($fullThumbPath)) {
                    $deletedAny = true;
                }
            }
        }

        return $deletedAny;
    }
    
    /**
     * Sanitize filename - remove Polish characters, spaces, special chars
     */
    private function sanitizeFilename(string $filename): string
    {
        // Zamień polskie znaki na ASCII
        $map = [
            'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n',
            'ó' => 'o', 'ś' => 's', 'ź' => 'z', 'ż' => 'z',
            'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'E', 'Ł' => 'L', 'Ń' => 'N',
            'Ó' => 'O', 'Ś' => 'S', 'Ź' => 'Z', 'Ż' => 'Z'
        ];
        
        $filename = strtr($filename, $map);
        
        // Zamień spacje i inne znaki specjalne na podkreślniki
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
        
        // Usuń wielokrotne podkreślniki
        $filename = preg_replace('/_+/', '_', $filename);
        
        // Usuń podkreślniki z początku i końca
        $filename = trim($filename, '_');
        
        // Jeśli nazwa jest pusta po sanityzacji, użyj generycznej nazwy
        if ($filename === '') {
            $filename = 'file';
        }
        
        // Ogranicz długość do 50 znaków
        if (strlen($filename) > 50) {
            $filename = substr($filename, 0, 50);
        }
        
        return $filename;
    }
    
    /**
     * Resize and save image
     */
    private function resizeAndSave(
        string $sourcePath,
        string $targetPath,
        string $mimeType,
        int $width,
        int $height,
        int $maxWidth = 1200,
        int $maxHeight = 1200
    ): void
    {
        // Jeśli obrazek jest mniejszy lub równy maksymalnym wymiarom, skopiuj bez zmian
        if ($width <= $maxWidth && $height <= $maxHeight) {
            if (!copy($sourcePath, $targetPath)) {
                throw new InvalidArgumentException('Nie udało się zapisać pliku');
            }
            return;
        }
        
        // Oblicz nowe wymiary zachowując proporcje
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = (int)round($width * $ratio);
        $newHeight = (int)round($height * $ratio);
        
        // Wczytaj obrazek źródłowy
        $sourceImage = match($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($sourcePath),
            'image/png' => imagecreatefrompng($sourcePath),
            'image/gif' => imagecreatefromgif($sourcePath),
            'image/webp' => imagecreatefromwebp($sourcePath),
            default => throw new InvalidArgumentException('Nieobsługiwany typ obrazka')
        };
        
        if ($sourceImage === false) {
            throw new InvalidArgumentException('Nie można wczytać obrazka');
        }
        
        // Utwórz nowy obrazek
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Zachowaj przezroczystość dla PNG i GIF
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // Przeskaluj
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Zapisz
        $success = match($mimeType) {
            'image/jpeg' => imagejpeg($newImage, $targetPath, 90),
            'image/png' => imagepng($newImage, $targetPath, 8),
            'image/gif' => imagegif($newImage, $targetPath),
            'image/webp' => imagewebp($newImage, $targetPath, 90),
            default => false
        };
        
        // Zwolnij pamięć
        imagedestroy($sourceImage);
        imagedestroy($newImage);
        
        if (!$success) {
            throw new InvalidArgumentException('Nie udało się zapisać przeskalowanego obrazka');
        }
    }
    
    private function getUploadErrorMessage(int $errorCode): string
    {
        return match($errorCode) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Plik jest za duży',
            UPLOAD_ERR_PARTIAL => 'Plik został przesłany tylko częściowo',
            UPLOAD_ERR_NO_FILE => 'Nie przesłano pliku',
            UPLOAD_ERR_NO_TMP_DIR => 'Brak katalogu tymczasowego',
            UPLOAD_ERR_CANT_WRITE => 'Nie udało się zapisać pliku',
            UPLOAD_ERR_EXTENSION => 'Rozszerzenie PHP zablokowało upload',
            default => 'Nieznany błąd uploadu'
        };
    }
    
    private function getExtensionFromMime(string $mimeType): string
    {
        return match($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            default => 'jpg'
        };
    }

    /**
     * Upload category icon image (800x800) with thumbnail (400x400)
     * Images are saved in /uploads/category_icon directory without YYYY/MM subdirs
     * @throws InvalidArgumentException
     */
    public function uploadCategoryIcon(array $file): array
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new InvalidArgumentException('Nieprawidłowy plik');
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException($this->getUploadErrorMessage($file['error']));
        }

        if ($file['size'] > $this->maxFileSize) {
            throw new InvalidArgumentException('Plik jest za duży (max 5MB)');
        }

        $finfo = null;
        try {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo === false) {
                throw new RuntimeException('Nie można zainicjować detekcji typu MIME');
            }

            $mimeType = finfo_file($finfo, $file['tmp_name']);
            if ($mimeType === false) {
                throw new RuntimeException('Nie można odczytać typu MIME pliku');
            }
        } finally {
            if (is_resource($finfo)) {
                finfo_close($finfo);
            }
        }

        if (!in_array($mimeType, $this->allowedTypes, true)) {
            throw new InvalidArgumentException('Nieprawidłowy typ pliku (dozwolone: JPEG, PNG, GIF, WebP)');
        }

        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            throw new InvalidArgumentException('Nie można odczytać informacji o obrazku');
        }

        [$width, $height] = $imageInfo;
        if ($width < $this->minWidth || $height < $this->minHeight) {
            throw new InvalidArgumentException(
                sprintf('Obrazek jest za mały (minimum %dx%d px)', $this->minWidth, $this->minHeight)
            );
        }

        // Sprawdź czy obrazek nie przekracza maksymalnego rozmiaru 1200x1200
        if ($width > 1200 || $height > 1200) {
            throw new InvalidArgumentException('Obrazek jest za duży (maksymalnie 1200x1200 px)');
        }

        // Folder dla ikon kategorii (bez YYYY/MM) - używamy $this->uploadDir z konstruktora
        $targetDir = $this->uploadDir . '/category_icon';
        if (!is_dir($targetDir)) {
            if (!@mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
                throw new RuntimeException('Nie można utworzyć katalogu przesyłania obrazków.');
            }
        }

        // Generowanie nazwy pliku tak samo jak dla linków
        $originalName = pathinfo($file['name'], PATHINFO_FILENAME);
        $originalName = $this->sanitizeFilename($originalName);
        $extension = $this->getExtensionFromMime($mimeType);
        $randomSuffix = sprintf('%06d', random_int(0, 999999));
        $filenameBase = $originalName . '_' . $randomSuffix;

        // Zapisz ikonę (800x800)
        $iconFilename = $filenameBase . '.' . $extension;
        $iconPath = $targetDir . '/' . $iconFilename;
        $this->resizeAndSave($file['tmp_name'], $iconPath, $mimeType, $width, $height, 800, 800);

        // Zapisz miniaturę (400x400)
        $thumbFilename = $filenameBase . '_thumb.' . $extension;
        $thumbPath = $targetDir . '/' . $thumbFilename;
        $this->resizeAndSave($file['tmp_name'], $thumbPath, $mimeType, $width, $height, 400, 400);

        // Zwracamy względną ścieżkę (tak jak w processUpload)
        return [
            'icon_path' => 'uploads/category_icon/' . $iconFilename,
            'thumb_path' => 'uploads/category_icon/' . $thumbFilename,
        ];
    }

    /**
     * Wspólny handler uploadu (z opcjonalnym generowaniem miniatury)
     */
    private function processUpload(array $file, bool $generateThumb, int $thumbMaxWidth = 400, int $thumbMaxHeight = 400): array
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new InvalidArgumentException('Nieprawidłowy plik');
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException($this->getUploadErrorMessage($file['error']));
        }

        if ($file['size'] > $this->maxFileSize) {
            throw new InvalidArgumentException('Plik jest za duży (max 5MB)');
        }

        $finfo = null;
        try {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo === false) {
                throw new RuntimeException('Nie można zainicjować detekcji typu MIME');
            }

            $mimeType = finfo_file($finfo, $file['tmp_name']);
            if ($mimeType === false) {
                throw new RuntimeException('Nie można odczytać typu MIME pliku');
            }
        } finally {
            if (is_resource($finfo)) {
                finfo_close($finfo);
            }
        }

        if (!in_array($mimeType, $this->allowedTypes, true)) {
            throw new InvalidArgumentException('Nieprawidłowy typ pliku (dozwolone: JPEG, PNG, GIF, WebP)');
        }

        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            throw new InvalidArgumentException('Nie można odczytać informacji o obrazku');
        }

        [$width, $height] = $imageInfo;
        if ($width < $this->minWidth || $height < $this->minHeight) {
            throw new InvalidArgumentException(
                sprintf('Obrazek jest za mały (minimum %dx%d px)', $this->minWidth, $this->minHeight)
            );
        }

        $yearMonth = date('Y/m');
        $targetDir = $this->uploadDir . '/' . $yearMonth;
        if (!is_dir($targetDir)) {
            if (!@mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
                throw new RuntimeException('Nie można utworzyć katalogu przesyłania obrazków.');
            }
        }

        $originalName = pathinfo($file['name'], PATHINFO_FILENAME);
        $originalName = $this->sanitizeFilename($originalName);
        $extension = $this->getExtensionFromMime($mimeType);
        $randomSuffix = sprintf('%06d', random_int(0, 999999));
        $filenameBase = $originalName . '_' . $randomSuffix;

        $mainFilename = $filenameBase . '.' . $extension;
        $mainPath = $targetDir . '/' . $mainFilename;

        $this->resizeAndSave($file['tmp_name'], $mainPath, $mimeType, $width, $height);

        $thumbRelative = null;
        if ($generateThumb) {
            $thumbFilename = $filenameBase . '_thumb.' . $extension;
            $thumbPath = $targetDir . '/' . $thumbFilename;
            $this->resizeAndSave($file['tmp_name'], $thumbPath, $mimeType, $width, $height, $thumbMaxWidth, $thumbMaxHeight);
            $thumbRelative = 'uploads/' . $yearMonth . '/' . $thumbFilename;
        }

        return [
            'path' => 'uploads/' . $yearMonth . '/' . $mainFilename,
            'thumb_path' => $thumbRelative,
        ];
    }
}
