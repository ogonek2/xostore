<?php

namespace App\Support\Shop;

use App\Models\Product;
use Illuminate\Support\Str;

final class ProductSkuGenerator
{
    public static function generate(?string $seed = null): string
    {
        $base = static::normalizeBase($seed);

        if (! static::exists($base)) {
            return $base;
        }

        for ($suffix = 2; $suffix <= 999; $suffix++) {
            $candidate = static::limit("{$base}-{$suffix}");

            if (! static::exists($candidate)) {
                return $candidate;
            }
        }

        return static::limit($base.'-'.Str::upper(Str::ulid()));
    }

    public static function isDraftSku(?string $sku): bool
    {
        return is_string($sku) && str_starts_with($sku, 'DRAFT-');
    }

    protected static function normalizeBase(?string $seed): string
    {
        if ($seed === null || trim($seed) === '') {
            return 'XO-'.Str::upper(Str::ulid());
        }

        $slug = Str::upper(Str::slug(trim($seed), '-'));
        $slug = preg_replace('/[^A-Z0-9\-]/', '', $slug) ?? '';
        $slug = trim((string) $slug, '-');

        if ($slug === '') {
            return 'XO-'.Str::upper(Str::ulid());
        }

        return static::limit($slug);
    }

    protected static function limit(string $value): string
    {
        return Str::limit($value, 64, '');
    }

    protected static function exists(string $sku): bool
    {
        return Product::withTrashed()->where('sku', $sku)->exists();
    }
}
