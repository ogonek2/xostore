<?php

namespace App\Support\Shop;

use App\Enums\CatalogHomepageSection;
use App\Models\Catalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class CatalogHomepageProducts
{
    /**
     * @return array{products: Collection, view_all_url: string, catalog: ?Catalog}
     */
    public static function forSection(
        CatalogHomepageSection $section,
        string $locale,
        int $limit = 12,
    ): array {
        $catalog = CatalogHomepageResolver::forSection($section);

        $query = static::orderedQuery($section);

        if ($catalog) {
            $catalog->loadMissing('categories', 'products');

            if ($catalog->products->isEmpty() && $catalog->categories->isEmpty()) {
                $products = collect();
            } else {
                CatalogProductScope::apply($query, $catalog);
                static::applyManualSort($query, $catalog);
                $products = $query->limit($limit)->get();
            }
        } else {
            $products = $query->limit($limit)->get();
        }

        return [
            'products' => $products,
            'view_all_url' => static::viewAllUrl($catalog, $section, $locale),
            'catalog' => $catalog,
        ];
    }

    protected static function orderedQuery(CatalogHomepageSection $section): Builder
    {
        $query = CatalogProductScope::baseQuery();

        return match ($section) {
            CatalogHomepageSection::NewArrivals => $query
                ->orderByDesc('published_at')
                ->orderByDesc('id'),
            CatalogHomepageSection::Trending => $query
                ->orderByDesc('is_featured')
                ->orderByDesc('published_at')
                ->orderByDesc('id'),
        };
    }

    protected static function applyManualSort(Builder $query, Catalog $catalog): void
    {
        $catalog->loadMissing('products');

        if ($catalog->products->isEmpty()) {
            return;
        }

        $cases = $catalog->products
            ->map(fn ($product) => sprintf(
                'WHEN %d THEN %d',
                $product->id,
                (int) ($product->pivot->sort_order ?? 0),
            ))
            ->implode(' ');

        $query->orderByRaw("CASE products.id {$cases} ELSE 9999 END");
    }

    protected static function viewAllUrl(?Catalog $catalog, CatalogHomepageSection $section, string $locale): string
    {
        $slug = $catalog?->translate('slug', $locale) ?? $section->defaultCatalogCode();

        return route('catalog.show', [
            'locale' => $locale,
            'catalog' => $slug,
        ]);
    }
}
