<?php

namespace App\Support\Import;

use Illuminate\Support\Str;

final class ImportUniqueCode
{
    public static function fromLabel(string $label, callable $exists): string
    {
        $slug = static::slugBase($label);

        if (! $exists($slug)) {
            return $slug;
        }

        $hash = substr(hash('crc32b', $label), 0, 8);
        $candidate = "{$slug}-{$hash}";

        if (! $exists($candidate)) {
            return $candidate;
        }

        do {
            $candidate = "{$slug}-".Str::lower(Str::random(6));
        } while ($exists($candidate));

        return $candidate;
    }

    public static function slugBase(string $label): string
    {
        $slug = Str::slug(trim($label));

        if ($slug === '') {
            $slug = 'item';
        }

        return rtrim(substr($slug, 0, 48), '-');
    }
}
