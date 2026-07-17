<?php

namespace App\Support\Telegram;

final class TelegramAdminLinks
{
    public static function resolve(string $path): ?string
    {
        $configuredBase = rtrim((string) config('shop.telegram.admin_url'), '/');

        if (preg_match('#^https?://#i', $path)) {
            if ($configuredBase === '') {
                return self::isValidButtonUrl($path) ? $path : null;
            }

            $query = parse_url($path, PHP_URL_QUERY);
            $fragment = parse_url($path, PHP_URL_FRAGMENT);
            $path = (string) (parse_url($path, PHP_URL_PATH) ?: '/');
            $path .= is_string($query) && $query !== '' ? '?'.$query : '';
            $path .= is_string($fragment) && $fragment !== '' ? '#'.$fragment : '';
        }

        $base = $configuredBase !== ''
            ? $configuredBase
            : rtrim((string) config('app.url'), '/');

        if ($base === '') {
            return null;
        }

        $url = $base.'/'.ltrim($path, '/');

        return self::isValidButtonUrl($url) ? $url : null;
    }

    public static function isValidButtonUrl(?string $url): bool
    {
        if (! is_string($url) || $url === '') {
            return false;
        }

        if (! preg_match('#^https?://#i', $url)) {
            return false;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return ! in_array($host, ['localhost', '127.0.0.1', '0.0.0.0'], true);
    }
}
