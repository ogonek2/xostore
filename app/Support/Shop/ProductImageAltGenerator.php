<?php

namespace App\Support\Shop;

use App\Models\Product;

final class ProductImageAltGenerator
{
    public static function generate(Product $product, int $sequence): string
    {
        $locale = (string) config('shop.default_language', 'pl');
        $name = trim((string) ($product->translate('name', $locale) ?? $product->sku));

        if ($name === '') {
            $name = $product->sku;
        }

        if ($sequence <= 1) {
            return $name;
        }

        return "{$name} ({$sequence})";
    }
}
