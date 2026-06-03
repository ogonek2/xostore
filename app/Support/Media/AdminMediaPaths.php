<?php

namespace App\Support\Media;

final class AdminMediaPaths
{
    /** @var list<string> */
    private const ALLOWED_PREFIXES = [
        'banners/',
        'hero-banners/',
        'products/',
        'catalogs/',
        'categories/',
        'promotions/',
    ];

    public static function isAllowed(string $path): bool
    {
        $path = trim($path);

        if ($path === '' || str_contains($path, '..')) {
            return false;
        }

        foreach (self::ALLOWED_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
