<?php

namespace App\Support\Media;

use GdImage;

final class ProductImageOptimizer
{
    /**
     * Resize and compress a product photo. Returns a path to a temp JPEG file, or null if skipped.
     */
    public static function optimize(string $sourcePath): ?string
    {
        if (! extension_loaded('gd')) {
            return null;
        }

        $info = @getimagesize($sourcePath);

        if ($info === false) {
            return null;
        }

        [$width, $height, $type] = $info;

        if (! in_array($type, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF], true)) {
            return null;
        }

        $image = static::loadImage($sourcePath, $type);

        if (! $image instanceof GdImage) {
            return null;
        }

        if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF) {
            $image = static::flattenAlpha($image, $width, $height);
        }

        $maxWidth = (int) config('shop.media.max_width', 2000);
        $maxHeight = (int) config('shop.media.max_height', 2500);
        $maxBytes = (int) config('shop.media.max_upload_bytes', 1_048_576);

        $image = static::resizeIfNeeded($image, $width, $height, $maxWidth, $maxHeight);

        $temp = tempnam(sys_get_temp_dir(), 'xostore_img_');

        if ($temp === false) {
            imagedestroy($image);

            return null;
        }

        $output = static::compressToLimit($image, $temp, $maxBytes);
        imagedestroy($image);

        if ($output === null) {
            @unlink($temp);

            return null;
        }

        return $output;
    }

    private static function loadImage(string $path, int $type): GdImage|false
    {
        return match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            IMAGETYPE_GIF => @imagecreatefromgif($path),
            default => false,
        };
    }

    private static function flattenAlpha(GdImage $image, int $width, int $height): GdImage
    {
        $canvas = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);
        imagecopy($canvas, $image, 0, 0, 0, 0, $width, $height);
        imagedestroy($image);

        return $canvas;
    }

    private static function resizeIfNeeded(
        GdImage $image,
        int $width,
        int $height,
        int $maxWidth,
        int $maxHeight,
    ): GdImage {
        if ($width <= $maxWidth && $height <= $maxHeight) {
            return $image;
        }

        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = max(1, (int) round($width * $ratio));
        $newHeight = max(1, (int) round($height * $ratio));

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($image);

        return $resized;
    }

    private static function compressToLimit(GdImage $image, string $tempPath, int $maxBytes): ?string
    {
        $startQuality = (int) config('shop.media.jpeg_quality', 85);
        $quality = $startQuality;

        while ($quality >= 50) {
            imagejpeg($image, $tempPath, $quality);
            $size = filesize($tempPath);

            if ($size !== false && $size <= $maxBytes) {
                return $tempPath;
            }

            $quality -= 5;
        }

        $currentWidth = imagesx($image);
        $currentHeight = imagesy($image);
        $scale = 0.85;

        while ($scale >= 0.45) {
            $newWidth = max(1, (int) round($currentWidth * $scale));
            $newHeight = max(1, (int) round($currentHeight * $scale));
            $smaller = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($smaller, $image, 0, 0, 0, 0, $newWidth, $newHeight, $currentWidth, $currentHeight);

            for ($quality = $startQuality; $quality >= 50; $quality -= 5) {
                imagejpeg($smaller, $tempPath, $quality);
                $size = filesize($tempPath);

                if ($size !== false && $size <= $maxBytes) {
                    imagedestroy($smaller);

                    return $tempPath;
                }
            }

            imagedestroy($smaller);
            $scale -= 0.1;
        }

        imagejpeg($image, $tempPath, 50);
        $size = filesize($tempPath);

        return ($size !== false && $size <= (int) ($maxBytes * 1.05)) ? $tempPath : null;
    }
}
