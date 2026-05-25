<?php

namespace App\Support\Shop;

use App\Models\Catalog;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductListingQuery
{
    public function __construct(
        protected ?Category $category = null,
        protected ?Catalog $catalog = null,
        protected array $filters = [],
    ) {}

    public function baseQuery(): Builder
    {
        $query = Product::query()
            ->with([
                'brand.translates',
                'primaryCategory.translates',
                'images',
                'variants' => fn ($q) => $q->where('is_active', true),
                'variants.attributeValues',
            ])
            ->published();

        if ($this->catalog) {
            $this->applyCatalogScope($query, $this->catalog);
        }

        if ($this->category) {
            $ids = Category::idsIncludingDescendants($this->category->id);
            $query->whereHas('categories', fn (Builder $q) => $q->whereIn('categories.id', $ids));
        }

        return $query;
    }

    public function filteredQuery(): Builder
    {
        $query = $this->baseQuery();
        $this->applyFilters($query);
        $this->applySort($query);

        return $query;
    }

    public function paginate(int $page, int $perPage): LengthAwarePaginator
    {
        return $this->filteredQuery()->paginate(
            perPage: $perPage,
            page: $page,
        );
    }

    public function applyFilters(Builder $query): void
    {
        if (! empty($this->filters['brands'])) {
            $query->whereIn('brand_id', $this->filters['brands']);
        }

        if (! empty($this->filters['colors'])) {
            $query->whereHas('variants', function (Builder $q) {
                $q->where('is_active', true)
                    ->whereHas('attributeValues', fn (Builder $av) => $av->whereIn('attribute_values.id', $this->filters['colors']));
            });
        }

        if ($this->filters['price_min'] !== null) {
            $query->whereHas('variants', fn (Builder $q) => $q
                ->where('is_active', true)
                ->where('price', '>=', $this->filters['price_min']));
        }

        if ($this->filters['price_max'] !== null) {
            $query->whereHas('variants', fn (Builder $q) => $q
                ->where('is_active', true)
                ->where('price', '<=', $this->filters['price_max']));
        }

        if (! empty($this->filters['new'])) {
            $query->where('is_new', true);
        }

        if (! empty($this->filters['sale'])) {
            $query->where(function (Builder $q) {
                $q->where(function (Builder $inner) {
                    $inner->whereNotNull('compare_at_price')
                        ->whereColumn('compare_at_price', '>', 'base_price');
                })->orWhereHas('variants', function (Builder $v) {
                    $v->where('is_active', true)
                        ->whereNotNull('compare_at_price')
                        ->whereColumn('compare_at_price', '>', 'price');
                });
            });
        }

        if (! empty($this->filters['q'])) {
            $term = '%'.$this->filters['q'].'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('sku', 'like', $term)
                    ->orWhereHas('translates', fn (Builder $t) => $t
                        ->whereIn('field', ['name', 'short_description'])
                        ->where('value', 'like', $term));
            });
        }
    }

    public function applySort(Builder $query): void
    {
        $sort = $this->filters['sort'] ?? 'newest';

        match ($sort) {
            'price_asc' => $query->orderByRaw(
                '(SELECT MIN(price) FROM product_variants WHERE product_variants.product_id = products.id AND is_active = 1) ASC'
            ),
            'price_desc' => $query->orderByRaw(
                '(SELECT MIN(price) FROM product_variants WHERE product_variants.product_id = products.id AND is_active = 1) DESC'
            ),
            'featured' => $query->orderByDesc('is_featured')->orderByDesc('published_at'),
            default => $query->orderByDesc('published_at')->orderByDesc('id'),
        };
    }

    protected function applyCatalogScope(Builder $query, Catalog $catalog): void
    {
        $catalog->loadMissing('categories', 'products');

        $manualIds = $catalog->products->pluck('id');
        $categoryIds = $catalog->categories->pluck('id');

        if ($manualIds->isEmpty() && $categoryIds->isEmpty()) {
            return;
        }

        $query->where(function (Builder $q) use ($manualIds, $categoryIds) {
            if ($manualIds->isNotEmpty()) {
                $q->whereIn('products.id', $manualIds);
            }
            if ($categoryIds->isNotEmpty()) {
                $allCategoryIds = $categoryIds
                    ->flatMap(fn ($id) => Category::idsIncludingDescendants($id))
                    ->unique()
                    ->values();

                $q->orWhereHas('categories', fn (Builder $c) => $c->whereIn('categories.id', $allCategoryIds));
            }
        });
    }
}
