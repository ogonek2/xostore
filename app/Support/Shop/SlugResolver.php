<?php

namespace App\Support\Shop;

use App\Models\Catalog;
use App\Models\Category;
use App\Models\Language;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

class SlugResolver
{
    public static function category(string $slug, string $locale): ?Category
    {
        return static::resolveBySlug(Category::class, $slug, $locale, activeColumn: 'is_active');
    }

    public static function catalog(string $slug, string $locale): ?Catalog
    {
        return static::resolveBySlug(Catalog::class, $slug, $locale, activeColumn: 'is_active');
    }

    public static function product(string $slug, string $locale): ?Product
    {
        $product = static::resolveBySlug(Product::class, $slug, $locale);

        if ($product) {
            return $product;
        }

        return Product::query()
            ->where('sku', $slug)
            ->published()
            ->first();
    }

    protected static function resolveBySlug(
        string $modelClass,
        string $slug,
        string $locale,
        ?string $activeColumn = null,
    ): ?Model {
        $languageId = Language::query()->where('code', $locale)->where('is_active', true)->value('id');

        if (! $languageId) {
            return null;
        }

        $query = $modelClass::query()
            ->whereHas('translates', function ($q) use ($languageId, $slug) {
                $q->where('language_id', $languageId)
                    ->where('field', 'slug')
                    ->where('value', $slug);
            });

        if ($activeColumn) {
            $query->where($activeColumn, true);
        }

        if ($modelClass === Product::class) {
            $query->published();
        }

        return $query->first();
    }
}
