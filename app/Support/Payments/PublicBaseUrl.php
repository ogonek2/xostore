<?php

namespace App\Support\Payments;

use RuntimeException;

final class PublicBaseUrl
{
    /**
     * Build an absolute https URL from a configured public base and path.
     */
    public static function join(string $base, string $path): string
    {
        $base = rtrim(trim($base), '/');
        $path = '/'.ltrim($path, '/');

        if ($base === '' || ! str_starts_with(strtolower($base), 'https://')) {
            throw new RuntimeException('Public base URL must be an absolute https URL.');
        }

        return $base.$path;
    }

    public static function host(string $url): ?string
    {
        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) ? strtolower($host) : null;
    }
}
