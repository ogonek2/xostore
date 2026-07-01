<?php

namespace App\Support\Shop;

use App\Models\Catalog;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

final class CatalogProductScope
{
    public static function apply(Builder $query, Catalog $catalog): void
    {
        if ($catalog->code === 'ready_to_ship') {
            $query->where('is_ready_to_ship', true);

            return;
        }

        $catalog->loadMissing('categories', 'products');

        $manualIds = $catalog->products->pluck('id');
        $categoryIds = $catalog->categories->pluck('id');

        if ($manualIds->isEmpty() && $categoryIds->isEmpty()) {
            return;
        }

        $query->where(function (Builder $inner) use ($manualIds, $categoryIds): void {
            if ($manualIds->isNotEmpty()) {
                $inner->whereIn('products.id', $manualIds);
            }

            if ($categoryIds->isNotEmpty()) {
                $allCategoryIds = $categoryIds
                    ->flatMap(fn (int $id) => Category::idsIncludingDescendants($id))
                    ->unique()
                    ->values();

                $inner->orWhereHas('categories', fn (Builder $category) => $category->whereIn('categories.id', $allCategoryIds));
            }
        });
    }

    /**
     * @return list<int>
     */
    public static function manualProductIds(Catalog $catalog): array
    {
        $catalog->loadMissing('products');

        return $catalog->products->pluck('id')->all();
    }

    public static function baseQuery(): Builder
    {
        return Product::query()
            ->with([
                'brand.translates',
                'primaryCategory.translates',
                'images',
                'variants.attributeValues',
                'translates',
            ])
            ->published();
    }
}
