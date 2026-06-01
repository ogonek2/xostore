<?php

namespace App\Support\Media;

final class Media
{
    public static function disk(): string
    {
        return (string) config('filesystems.media_disk', 'public');
    }

    public static function usesBunny(): bool
    {
        return static::disk() === 'bunny';
    }
}
