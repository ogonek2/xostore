<?php

namespace App\Support\Shop;

use App\Enums\ProductStatus;
use App\Models\Catalog;
use App\Models\Product;
use Illuminate\Support\Collection;

class TrendingProducts
{
    public static function forHomepage(string $locale, int $limit = 12): Collection
    {
        $catalog = Catalog::query()
            ->where('code', 'trendy')
            ->where('is_active', true)
            ->first();

        $query = Product::query()
            ->with([
                'brand.translates',
                'primaryCategory.translates',
                'images',
                'variants.attributeValues',
            ])
            ->published()
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at');

        if ($catalog) {
            $catalog->load('categories', 'products');

            $categoryIds = $catalog->categories->pluck('id');
            $manualIds = $catalog->products->pluck('id');

            if ($manualIds->isNotEmpty() || $categoryIds->isNotEmpty()) {
                $query->where(function ($q) use ($categoryIds, $manualIds) {
                    if ($manualIds->isNotEmpty()) {
                        $q->whereIn('products.id', $manualIds);
                    }
                    if ($categoryIds->isNotEmpty()) {
                        $q->orWhereHas(
                            'categories',
                            fn ($c) => $c->whereIn('categories.id', $categoryIds)
                        );
                    }
                });
            }
        }

        $products = $query->limit($limit)->get();

        if ($products->isEmpty()) {
            $products = Product::query()
                ->with([
                    'brand.translates',
                    'primaryCategory.translates',
                    'images',
                    'variants.attributeValues',
                ])
                ->where('status', ProductStatus::Published->value)
                ->orderByDesc('is_featured')
                ->orderByDesc('published_at')
                ->limit($limit)
                ->get();
        }

        return ProductCardPresenter::collection($products, $locale);
    }
}
