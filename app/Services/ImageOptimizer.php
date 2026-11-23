<?php

namespace App\Services;

/**
 * Converts uploaded raster images (JPG/PNG) in public/ to WebP using GD,
 * returning the updated relative path so asset($path) works seamlessly.
 */
class ImageOptimizer
{
    public static function toWebp(?string $relativePath, int $quality = 84): ?string
    {
        if (! $relativePath || ! function_exists('imagewebp')) {
            return $relativePath;
        }

        $ext = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));
        if ($ext === 'webp' || ! in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
            return $relativePath;
        }

        $fullPath = public_path($relativePath);
        if (! is_file($fullPath)) {
            return $relativePath;
        }

        $image = match ($ext) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($fullPath),
            'png' => @imagecreatefrompng($fullPath),
            default => false,
        };

        if (! $image) {
            return $relativePath;
        }

        if ($ext === 'png') {
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
        }

        $webpRelative = preg_replace('/\.(jpe?g|png)$/i', '.webp', $relativePath);
        $webpFull = public_path($webpRelative);

        if (@imagewebp($image, $webpFull, $quality)) {
            imagedestroy($image);
            @unlink($fullPath);

            return $webpRelative;
        }

        imagedestroy($image);

        return $relativePath;
    }
}
