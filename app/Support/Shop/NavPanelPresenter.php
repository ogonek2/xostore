<?php

namespace App\Support\Shop;

use App\Enums\NavPanelType;
use App\Models\Brand;
use App\Models\Catalog;
use App\Models\Category;
use App\Models\NavPanel;
use App\Models\Product;
use App\Services\Promotion\PromotionDiscountService;
use App\Support\Media\MediaUrl;
use Illuminate\Support\Collection;

class NavPanelPresenter
{
    protected static function megaProductLimit(NavPanel $panel): int
    {
        $cap = max(1, (int) config('shop.mega_menu.product_limit', 4));

        return min($cap, max(1, (int) $panel->item_limit));
    }

    /**
     * @param  Collection<int, Product>  $products
     * @return Collection<int, Product>
     */
    protected static function uniqueProductsForMega(Collection $products, NavPanel $panel): Collection
    {
        return $products
            ->unique(fn (Product $product) => filled($product->model_slug)
                ? 'model:'.$product->model_slug
                : 'id:'.$product->id)
            ->take(static::megaProductLimit($panel))
            ->values();
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function present(NavPanel $panel, string $locale): ?array
    {
        if (! $panel->is_active) {
            return null;
        }

        $type = $panel->type instanceof NavPanelType
            ? $panel->type
            : NavPanelType::tryFrom((string) $panel->type);

        if (! $type) {
            return null;
        }

        $payload = match ($type) {
            NavPanelType::Category => static::presentCategory($panel, $locale),
            NavPanelType::SelectedProducts => static::presentSelectedProducts($panel, $locale),
            NavPanelType::Links => static::presentLinks($panel, $locale),
            NavPanelType::Brands => static::presentBrands($panel, $locale),
            NavPanelType::CatalogProducts => static::presentCatalogProducts($panel, $locale),
            NavPanelType::PromotionProducts => static::presentPromotionProducts($panel, $locale),
        };

        if ($payload === null) {
            return null;
        }

        return [
            'type' => $type->value,
            'title' => static::resolveTitle($panel, $locale),
            'columns' => max(1, min(2, (int) $panel->columns)),
            'links' => $payload['links'] ?? [],
            'brands' => $payload['brands'] ?? [],
            'products' => $payload['products'] ?? [],
            'view_all_url' => $payload['view_all_url'] ?? null,
            'view_all_label' => $payload['view_all_label'] ?? null,
        ];
    }

    protected static function resolveTitle(NavPanel $panel, string $locale): ?string
    {
        $custom = $panel->title($locale);

        if ($custom) {
            return $custom;
        }

        $categories = static::resolveCategories($panel);
        $catalogs = static::resolveCatalogs($panel);

        if ($categories->count() === 1) {
            $category = $categories->first();

            return $category->translate('name', $locale) ?? $category->code;
        }

        if ($catalogs->count() === 1) {
            $catalog = $catalogs->first();

            return $catalog->translate('name', $locale) ?? $catalog->code;
        }

        if ($panel->category_id && $categories->isEmpty()) {
            $panel->loadMissing('category.translates');

            return $panel->category?->translate('name', $locale);
        }

        if ($panel->catalog_id && $catalogs->isEmpty()) {
            $panel->loadMissing('catalog.translates');

            return $panel->catalog?->translate('name', $locale);
        }

        return null;
    }

    /**
     * @return Collection<int, Category>
     */
    protected static function resolveCategories(NavPanel $panel): Collection
    {
        if ($panel->relationLoaded('categories') && $panel->categories->isNotEmpty()) {
            return $panel->categories;
        }

        $categories = $panel->categories()
            ->where('is_active', true)
            ->with('translates')
            ->orderByPivot('sort_order')
            ->get();

        if ($categories->isNotEmpty()) {
            return $categories;
        }

        if ($panel->category_id) {
            $category = Category::query()
                ->where('is_active', true)
                ->with('translates')
                ->find($panel->category_id);

            return $category ? collect([$category]) : collect();
        }

        return collect();
    }

    /**
     * @return Collection<int, Catalog>
     */
    protected static function resolveCatalogs(NavPanel $panel): Collection
    {
        if ($panel->relationLoaded('catalogs') && $panel->catalogs->isNotEmpty()) {
            return $panel->catalogs;
        }

        $catalogs = $panel->catalogs()
            ->where('is_active', true)
            ->with('translates')
            ->orderByPivot('sort_order')
            ->get();

        if ($catalogs->isNotEmpty()) {
            return $catalogs;
        }

        if ($panel->catalog_id) {
            $catalog = Catalog::query()
                ->where('is_active', true)
                ->with('translates')
                ->find($panel->catalog_id);

            return $catalog ? collect([$catalog]) : collect();
        }

        return collect();
    }

    /**
     * @return array{links?: list<array<string, mixed>>, products?: list<array<string, mixed>>}|null
     */
    protected static function presentCategory(NavPanel $panel, string $locale): ?array
    {
        $categories = static::resolveCategories($panel);

        if ($categories->isEmpty()) {
            return null;
        }

        $result = [];

        if ($panel->show_subcategories && $categories->count() === 1) {
            $links = static::categorySubcategoryLinks((int) $categories->first()->id, $panel, $locale);

            if ($links !== []) {
                $result['links'] = $links;
            }
        } else {
            $links = static::categoryDirectLinks($categories, $locale, (int) $panel->item_limit);

            if ($links !== []) {
                $result['links'] = $links;
            }
        }

        if ($panel->show_products && $categories->count() === 1) {
            $category = $categories->first();
            $products = static::uniqueProductsForMega(
                static::categoryProducts((int) $category->id, $panel),
                $panel,
            );

            if ($products->isNotEmpty()) {
                $cards = static::presentProductCards($products, $locale);

                if ($cards !== null) {
                    $result['products'] = $cards['products'];
                }
            }

            $slug = $category->translate('slug', $locale) ?? $category->code;
            $name = $category->translate('name', $locale) ?? $category->code;
            $result['view_all_url'] = route('category.show', ['locale' => $locale, 'category' => $slug]);
            $result['view_all_label'] = __('shop.nav.all_in_category', ['name' => $name]);
        }

        return $result === [] ? null : $result;
    }

    /**
     * @return list<array{label: string, url: string, open_in_new_tab: bool}>
     */
    protected static function categoryDirectLinks(Collection $categories, string $locale, int $limit): array
    {
        return $categories
            ->take(max(1, $limit))
            ->map(function (Category $category) use ($locale) {
                $slug = $category->translate('slug', $locale) ?? $category->code;
                $label = $category->translate('name', $locale) ?? $category->code;

                return [
                    'label' => $label,
                    'url' => route('category.show', ['locale' => $locale, 'category' => $slug]),
                    'open_in_new_tab' => false,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array{label: string, url: string, open_in_new_tab: bool}>
     */
    protected static function categorySubcategoryLinks(int $categoryId, NavPanel $panel, string $locale): array
    {
        $category = Category::query()
            ->with([
                'translates',
                'children' => fn ($q) => $q
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->with('translates'),
            ])
            ->where('is_active', true)
            ->find($categoryId);

        if (! $category) {
            return [];
        }

        $links = [];

        $parentSlug = $category->translate('slug', $locale) ?? $category->code;
        $parentLabel = $category->translate('name', $locale) ?? $category->code;

        $links[] = [
            'label' => __('shop.nav.all_in_category', ['name' => $parentLabel]),
            'url' => route('category.show', ['locale' => $locale, 'category' => $parentSlug]),
            'open_in_new_tab' => false,
        ];

        foreach ($category->children->take(max(0, $panel->item_limit - 1)) as $child) {
            $slug = $child->translate('slug', $locale) ?? $child->code;
            $label = $child->translate('name', $locale) ?? $child->code;

            $links[] = [
                'label' => $label,
                'url' => route('category.show', ['locale' => $locale, 'category' => $slug]),
                'open_in_new_tab' => false,
            ];
        }

        return $links;
    }

    /**
     * @return Collection<int, Product>
     */
    protected static function categoryProducts(int $categoryId, NavPanel $panel): Collection
    {
        $categoryIds = Category::idsIncludingDescendants($categoryId);

        return Product::query()
            ->published()
            ->with([
                'brand.translates',
                'primaryCategory.translates',
                'images',
                'variants' => fn ($q) => $q->where('is_active', true),
            ])
            ->where(function ($query) use ($categoryIds) {
                $query->whereIn('primary_category_id', $categoryIds)
                    ->orWhereHas('categories', fn ($q) => $q->whereIn('categories.id', $categoryIds));
            })
            ->orderByDesc('published_at')
            ->limit(max(static::megaProductLimit($panel) * 3, 12))
            ->get();
    }

    /**
     * @return array{products: list<array<string, mixed>>}|null
     */
    protected static function presentSelectedProducts(NavPanel $panel, string $locale): ?array
    {
        $panel->loadMissing([
            'products' => fn ($q) => $q
                ->published()
                ->with([
                    'brand.translates',
                    'primaryCategory.translates',
                    'images',
                    'variants' => fn ($v) => $v->where('is_active', true),
                ]),
        ]);

        if ($panel->products->isEmpty()) {
            return null;
        }

        $products = static::uniqueProductsForMega($panel->products, $panel);

        return static::presentProductCards($products, $locale);
    }

    /**
     * @return array{links: list<array{label: string, url: string, open_in_new_tab: bool}>}|null
     */
    protected static function presentLinks(NavPanel $panel, string $locale): ?array
    {
        $panel->loadMissing('links');

        $links = $panel->links
            ->map(function ($link) use ($locale) {
                $label = $link->label($locale);
                $url = ShopNavigation::resolveUrl($link->url, $locale);

                if (! $label || ! $url) {
                    return null;
                }

                return [
                    'label' => $label,
                    'url' => $url,
                    'open_in_new_tab' => (bool) $link->open_in_new_tab,
                ];
            })
            ->filter()
            ->values()
            ->all();

        return $links === [] ? null : ['links' => $links];
    }

    /**
     * @return array{brands: list<array{name: string, url: string, logo: ?string}>, view_all_url: string, view_all_label: string}|null
     */
    protected static function presentBrands(NavPanel $panel, string $locale): ?array
    {
        $brands = Brand::query()
            ->where('is_active', true)
            ->with('translates')
            ->orderBy('sort_order')
            ->limit(max(1, (int) $panel->item_limit))
            ->get();

        $items = $brands
            ->map(function (Brand $brand) use ($locale) {
                $name = $brand->translate('name', $locale) ?? $brand->code;

                return [
                    'name' => $name,
                    'url' => route('products.index', [
                        'locale' => $locale,
                        'brands' => [$brand->id],
                    ]),
                    'logo' => MediaUrl::fromPath($brand->logo_path),
                ];
            })
            ->values()
            ->all();

        if ($items === []) {
            return null;
        }

        return [
            'brands' => $items,
            'view_all_url' => route('products.index', ['locale' => $locale]),
            'view_all_label' => __('shop.nav.all_brands'),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected static function presentCatalogProducts(NavPanel $panel, string $locale): ?array
    {
        $catalogs = static::resolveCatalogs($panel);

        if ($catalogs->isEmpty()) {
            return null;
        }

        if ($catalogs->count() > 1) {
            $links = static::catalogDirectLinks($catalogs, $locale, (int) $panel->item_limit);

            return $links === [] ? null : ['links' => $links];
        }

        $catalog = $catalogs->first();
        $slug = $catalog->translate('slug', $locale) ?? $catalog->code;
        $name = $catalog->translate('name', $locale) ?? $catalog->code;

        $products = (new ProductListingQuery(catalog: $catalog))
            ->baseQuery()
            ->limit(max(static::megaProductLimit($panel) * 3, 12))
            ->get();

        $products = static::uniqueProductsForMega($products, $panel);

        $cards = static::presentProductCards($products, $locale);

        if ($cards === null) {
            return [
                'view_all_url' => route('catalog.show', ['locale' => $locale, 'catalog' => $slug]),
                'view_all_label' => __('shop.nav.all_in_category', ['name' => $name]),
            ];
        }

        return array_merge($cards, [
            'view_all_url' => route('catalog.show', ['locale' => $locale, 'catalog' => $slug]),
            'view_all_label' => __('shop.nav.all_in_category', ['name' => $name]),
        ]);
    }

    /**
     * @return list<array{label: string, url: string, open_in_new_tab: bool}>
     */
    protected static function catalogDirectLinks(Collection $catalogs, string $locale, int $limit): array
    {
        return $catalogs
            ->take(max(1, $limit))
            ->map(function (Catalog $catalog) use ($locale) {
                $slug = $catalog->translate('slug', $locale) ?? $catalog->code;
                $label = $catalog->translate('name', $locale) ?? $catalog->code;

                return [
                    'label' => $label,
                    'url' => route('catalog.show', ['locale' => $locale, 'catalog' => $slug]),
                    'open_in_new_tab' => false,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array{products: list<array<string, mixed>>}|null
     */
    protected static function presentPromotionProducts(NavPanel $panel, string $locale): ?array
    {
        $ids = app(PromotionDiscountService::class)
            ->discountedProductIds()
            ->take($panel->item_limit);

        if ($ids->isEmpty()) {
            return null;
        }

        $products = Product::query()
            ->published()
            ->with([
                'brand.translates',
                'primaryCategory.translates',
                'images',
                'variants' => fn ($q) => $q->where('is_active', true),
            ])
            ->whereIn('id', $ids)
            ->get();

        $products = static::uniqueProductsForMega($products, $panel);

        return static::presentProductCards($products, $locale);
    }

    /**
     * @return array{products: list<array<string, mixed>>}|null
     */
    protected static function presentProductCards(Collection $products, string $locale): ?array
    {
        $cards = $products
            ->map(function (Product $product) use ($locale) {
                $card = ProductCardPresenter::fromProduct($product, $locale, compact: true);

                return [
                    'url' => $card['url'],
                    'name' => $card['name'],
                    'image' => $card['image'],
                    'price_formatted' => $card['price_formatted'],
                    'compare_at_formatted' => $card['compare_at_formatted'],
                    'on_sale' => ! empty($card['compare_at_formatted']),
                ];
            })
            ->values()
            ->all();

        return $cards === [] ? null : ['products' => $cards];
    }
}
