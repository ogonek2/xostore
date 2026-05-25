<?php

namespace App\Support\Shop;

use App\Enums\ProductStatus;
use App\Models\Catalog;
use App\Models\Product;
class NewArrivalsProducts
{
    public static function forHomepage(string $locale, int $limit = 12): array
    {
        $catalog = Catalog::query()
            ->where('code', 'nowynki')
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
            } else {
                $query->where('is_new', true);
            }
        } else {
            $query->where('is_new', true);
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
                ->where('is_new', true)
                ->orderByDesc('published_at')
                ->limit($limit)
                ->get();
        }

        return [
            'products' => ProductCardPresenter::collection($products, $locale, compact: true),
            'view_all_url' => route('catalog.show', [
                'locale' => $locale,
                'catalog' => $locale === 'en' ? 'new-in' : 'nowynki',
            ]),
        ];
    }
}
