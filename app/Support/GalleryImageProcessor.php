<?php

namespace App\Support;

use InvalidArgumentException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use RuntimeException;

class GalleryImageProcessor
{
    private const MAX_WIDTH = 1600;

    private const QUALITY = 80;

    private const DIRECTORY = 'gallery';

    /**
     * Return the public URL for an uploaded file path stored by process().
     *
     * Local / default: delegates to Storage::disk('public')->url() — requires the
     * public/storage symlink (php artisan storage:link).
     *
     * Production override: when both UPLOAD_PUBLIC_ROOT and UPLOAD_PUBLIC_URL are
     * set in .env, the URL base is taken from UPLOAD_PUBLIC_URL.
     *
     * Throws InvalidArgumentException if exactly one of the pair is set (see
     * resolveUploadConfig() for the validation rule).
     *
     * Returns null for null / empty paths so callers can use @if checks as before.
     */
    public static function url(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $config = static::resolveUploadConfig();

        if ($config !== null) {
            return rtrim($config['url_base'], '/') . '/' . ltrim($path, '/');
        }

        return Storage::disk('public')->url($path);
    }

    /**
     * Process an uploaded image and write it to the configured upload destination.
     *
     * The returned value is always a relative path (e.g. "services/uuid.webp") which
     * is safe to store in the database regardless of environment. Use url() to turn
     * it back into a public URL.
     *
     * Local / default: writes to Storage::disk('public') — requires the
     * public/storage symlink (php artisan storage:link).
     *
     * Production override: when both UPLOAD_PUBLIC_ROOT and UPLOAD_PUBLIC_URL are
     * set in .env, writes directly to UPLOAD_PUBLIC_ROOT so no symlink is needed.
     * The subdirectory is created automatically if it does not exist.
     *
     * Throws InvalidArgumentException if exactly one of the config pair is set (see
     * resolveUploadConfig() for the validation rule).
     * Throws RuntimeException if the directory cannot be created or the write fails.
     *
     * @param  string  $directory  Relative sub-folder (e.g. "gallery", "blog").
     */
    public function process(UploadedFile $file, string $directory = self::DIRECTORY): string
    {
        $manager = $this->createImageManager();

        $path = $file->getRealPath() ?: $file->getPathname();

        $image = $manager->read($path);

        if ($image->width() > self::MAX_WIDTH) {
            $image->scaleDown(self::MAX_WIDTH);
        }

        $encoded = $image->toWebp(self::QUALITY);

        $relativePath = trim($directory, '/') . '/' . Str::uuid()->toString() . '.webp';

        $config = static::resolveUploadConfig();

        if ($config !== null) {
            $fullPath = rtrim($config['root'], '/') . '/' . $relativePath;
            $dir = dirname($fullPath);

            if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
                throw new RuntimeException(
                    "Failed to create upload directory: {$dir}"
                );
            }

            $written = file_put_contents($fullPath, $encoded->toString());

            if ($written === false) {
                throw new RuntimeException(
                    "Failed to write uploaded image to: {$fullPath}"
                );
            }
        } else {
            Storage::disk('public')->put($relativePath, $encoded->toString());
        }

        return $relativePath;
    }

    /**
     * Read and validate the upload config pair.
     *
     * Rules:
     *   both empty            → returns null   (local / public-disk mode)
     *   both set              → returns array  (production direct-write mode)
     *   exactly one set       → throws         (misconfiguration — fail fast)
     *
     * @return array{root: string, url_base: string}|null
     *
     * @throws InvalidArgumentException
     */
    private static function resolveUploadConfig(): ?array
    {
        $root    = config('filesystems.upload_public_root');
        $urlBase = config('filesystems.upload_public_url');

        $hasRoot    = $root    !== null && $root    !== '';
        $hasUrlBase = $urlBase !== null && $urlBase !== '';

        if ($hasRoot && $hasUrlBase) {
            return ['root' => (string) $root, 'url_base' => (string) $urlBase];
        }

        if (! $hasRoot && ! $hasUrlBase) {
            return null;
        }

        $present = $hasRoot ? 'UPLOAD_PUBLIC_ROOT' : 'UPLOAD_PUBLIC_URL';
        $missing = $hasRoot ? 'UPLOAD_PUBLIC_URL'  : 'UPLOAD_PUBLIC_ROOT';

        throw new InvalidArgumentException(
            "{$present} is set but {$missing} is missing. "
            . 'Both UPLOAD_PUBLIC_ROOT and UPLOAD_PUBLIC_URL must be set together, '
            . 'or both must be left empty to use local/storage mode.'
        );
    }

    private function createImageManager(): ImageManager
    {
        if (extension_loaded('gd') && function_exists('imagewebp')) {
            return ImageManager::gd();
        }

        if (extension_loaded('imagick')) {
            return ImageManager::imagick();
        }

        throw new RuntimeException(
            'Gallery image processing requires PHP GD with WebP support (imagewebp) or the Imagick extension. Neither is available.'
        );
    }
}
