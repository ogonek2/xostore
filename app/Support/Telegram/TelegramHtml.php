<?php

namespace App\Support\Telegram;

final class TelegramHtml
{
    public static function escape(?string $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function line(string $label, ?string $value): string
    {
        return '<b>'.static::escape($label).':</b> '.static::escape($value);
    }
}
