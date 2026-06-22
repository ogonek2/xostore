<?php

namespace App\Support\Media;

use Illuminate\Support\Facades\Storage;

final class MediaUrl
{
    public static function fromPath(?string $path, ?string $disk = null): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $path = trim($path);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        if (str_starts_with($path, '/')) {
            return asset(ltrim($path, '/'));
        }

        $disk ??= Media::disk();

        if ($disk === 'public') {
            return asset('storage/'.$path);
        }

        try {
            return Storage::disk($disk)->url($path);
        } catch (\Throwable) {
            return asset('storage/'.$path);
        }
    }

    public static function orPlaceholder(?string $path, ?string $disk, string $placeholder): string
    {
        return static::fromPath($path, $disk) ?? asset($placeholder);
    }

    public static function sized(?string $path, ?string $disk, int $width, ?int $quality = null): ?string
    {
        $url = static::fromPath($path, $disk);

        if ($url === null || $width <= 0) {
            return $url;
        }

        if (str_starts_with($url, asset('images/'))) {
            return $url;
        }

        if (! config('shop.media.cdn_resize', true)) {
            return $url;
        }

        $quality ??= (int) config('shop.media.jpeg_quality', 82);

        if (str_contains($url, '?')) {
            return $url;
        }

        return $url.'?width='.$width.'&quality='.$quality;
    }

    public static function orPlaceholderSized(
        ?string $path,
        ?string $disk,
        string $placeholder,
        int $width,
        ?int $quality = null,
    ): string {
        return static::sized($path, $disk, $width, $quality)
            ?? asset($placeholder);
    }
}
