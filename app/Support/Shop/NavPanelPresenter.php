<?php

namespace App\Support\Shop;

use App\Enums\NavPanelType;
use App\Models\Brand;
use App\Models\Category;
use App\Models\NavPanel;
use App\Models\Product;
use App\Services\Promotion\PromotionDiscountService;
use Illuminate\Support\Collection;

class NavPanelPresenter
{
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
            'products' => $payload['products'] ?? [],
        ];
    }

    protected static function resolveTitle(NavPanel $panel, string $locale): ?string
    {
        $custom = $panel->title($locale);

        if ($custom) {
            return $custom;
        }

        if ($panel->category_id) {
            $panel->loadMissing('category.translates');

            return $panel->category?->translate('name', $locale);
        }

        if ($panel->catalog_id) {
            $panel->loadMissing('catalog.translates');

            return $panel->catalog?->translate('name', $locale);
        }

        return null;
    }

    /**
     * @return array{links?: list<array<string, mixed>>, products?: list<array<string, mixed>>}|null
     */
    protected static function presentCategory(NavPanel $panel, string $locale): ?array
    {
        if (! $panel->category_id) {
            return null;
        }

        $result = [];

        if ($panel->show_subcategories) {
            $links = static::categorySubcategoryLinks($panel, $locale);

            if ($links !== []) {
                $result['links'] = $links;
            }
        }

        if ($panel->show_products) {
            $products = static::categoryProducts($panel);

            if ($products->isNotEmpty()) {
                $cards = static::presentProductCards($products, $locale);

                if ($cards !== null) {
                    $result['products'] = $cards['products'];
                }
            }
        }

        return $result === [] ? null : $result;
    }

    /**
     * @return list<array{label: string, url: string, open_in_new_tab: bool}>
     */
    protected static function categorySubcategoryLinks(NavPanel $panel, string $locale): array
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
            ->find($panel->category_id);

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
    protected static function categoryProducts(NavPanel $panel): Collection
    {
        $categoryIds = Category::idsIncludingDescendants((int) $panel->category_id);

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
            ->limit($panel->item_limit)
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

        return static::presentProductCards($panel->products, $locale);
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
     * @return array{links: list<array{label: string, url: string, open_in_new_tab: bool}>}|null
     */
    protected static function presentBrands(NavPanel $panel, string $locale): ?array
    {
        $brands = Brand::query()
            ->where('is_active', true)
            ->with('translates')
            ->orderBy('sort_order')
            ->limit($panel->item_limit)
            ->get();

        $links = $brands
            ->map(function (Brand $brand) use ($locale) {
                $name = $brand->translate('name', $locale) ?? $brand->code;

                return [
                    'label' => '#'.$name,
                    'url' => route('products.index', [
                        'locale' => $locale,
                        'brands' => [$brand->id],
                    ]),
                    'open_in_new_tab' => false,
                ];
            })
            ->values()
            ->all();

        return $links === [] ? null : ['links' => $links];
    }

    /**
     * @return array{products: list<array<string, mixed>>}|null
     */
    protected static function presentCatalogProducts(NavPanel $panel, string $locale): ?array
    {
        if (! $panel->catalog_id) {
            return null;
        }

        $catalog = $panel->catalog()->where('is_active', true)->first();

        if (! $catalog) {
            return null;
        }

        $slug = $catalog->translate('slug', $locale) ?? $catalog->code;
        $name = $catalog->translate('name', $locale) ?? $catalog->code;

        $products = (new ProductListingQuery(catalog: $catalog))
            ->baseQuery()
            ->limit($panel->item_limit)
            ->get();

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
            ->limit($panel->item_limit)
            ->get();

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
