<?php

namespace App\Support\Telegram;

final class TelegramAdminLinks
{
    public static function resolve(string $path): ?string
    {
        $base = rtrim((string) (config('shop.telegram.admin_url') ?: config('app.url')), '/');

        if ($base === '') {
            return null;
        }

        $url = $base.'/'.ltrim($path, '/');

        return static::isValidButtonUrl($url) ? $url : null;
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
