<?php

namespace App\Support\Shop;

use App\Models\Category;

final class ListingCategoryNav
{
    /**
     * @return list<array{label: string, url: string}>
     */
    public static function rootPills(string $locale): array
    {
        return static::mapCategories(
            Category::query()
                ->with('translates')
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(),
            $locale,
        );
    }

    /**
     * Sidebar / filter navigation for the current listing context.
     *
     * @return array{
     *     all_products: array{label: string, url: string},
     *     parent: ?array{label: string, url: string},
     *     links: list<array{label: string, url: string}>
     * }
     */
    public static function forFilters(string $locale, ?Category $category, string $listingType): array
    {
        $allProducts = [
            'label' => __('shop.listing.all_products', locale: $locale),
            'url' => route('products.index', ['locale' => $locale]),
        ];

        if ($listingType === 'catalog') {
            return [
                'all_products' => $allProducts,
                'parent' => null,
                'links' => [],
            ];
        }

        if ($category === null) {
            return [
                'all_products' => $allProducts,
                'parent' => null,
                'links' => static::rootPills($locale),
            ];
        }

        $category->loadMissing(['parent.translates', 'translates']);

        $parent = null;
        if ($category->parent) {
            $parentSlug = $category->parent->translate('slug', $locale) ?? $category->parent->code;
            $parent = [
                'label' => $category->parent->translate('name', $locale) ?? $category->parent->code,
                'url' => route('category.show', ['locale' => $locale, 'category' => $parentSlug]),
            ];
        }

        $children = $category->children()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->with('translates')
            ->get();

        if ($children->isNotEmpty()) {
            return [
                'all_products' => $allProducts,
                'parent' => $parent,
                'links' => static::mapCategories($children, $locale),
            ];
        }

        if ($category->parent_id) {
            $siblings = Category::query()
                ->with('translates')
                ->where('parent_id', $category->parent_id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            return [
                'all_products' => $allProducts,
                'parent' => $parent,
                'links' => static::mapCategories($siblings, $locale),
            ];
        }

        return [
            'all_products' => $allProducts,
            'parent' => null,
            'links' => static::rootPills($locale),
        ];
    }

    /**
     * @param  iterable<Category>  $categories
     * @return list<array{label: string, url: string}>
     */
    protected static function mapCategories(iterable $categories, string $locale): array
    {
        $links = [];

        foreach ($categories as $category) {
            $slug = $category->translate('slug', $locale) ?? $category->code;

            $links[] = [
                'label' => $category->translate('name', $locale) ?? $category->code,
                'url' => route('category.show', ['locale' => $locale, 'category' => $slug]),
            ];
        }

        return $links;
    }
}
