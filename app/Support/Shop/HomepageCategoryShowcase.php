<?php

namespace App\Support\Shop;

use App\Models\Category;
use App\Models\HomepageSettings;
use App\Models\Product;
use App\Support\Media\MediaUrl;
use Illuminate\Support\Collection;

class HomepageCategoryShowcase
{
    public static function cards(string $locale, ?array $items = null): Collection
    {
        $configuredItems = $items ?? static::configuredItems();

        if ($configuredItems === []) {
            return collect();
        }

        $categoryIds = collect($configuredItems)->pluck('category_id')->filter()->unique()->values();
        $categoryCodes = collect($configuredItems)->pluck('category_code')->filter()->unique()->values();

        $categories = Category::query()
            ->with('translates')
            ->when(
                $categoryIds->isNotEmpty(),
                fn ($query) => $query->whereIn('id', $categoryIds),
                fn ($query) => $query->whereIn('code', $categoryCodes),
            )
            ->get()
            ->keyBy(fn (Category $category) => $categoryIds->isNotEmpty() ? $category->id : $category->code);

        $productImages = static::productImagesForCategories(
            $categories->pluck('id')->all(),
        );

        return collect($configuredItems)->map(function (array $item) use ($categories, $productImages, $locale) {
            $category = isset($item['category_id'])
                ? $categories->get((int) $item['category_id'])
                : $categories->get($item['category_code'] ?? '');

            if (! $category) {
                return null;
            }

            $imagePath = filled($category->image_path)
                ? $category->image_path
                : ($productImages[$category->id] ?? null);

            $label = $category->translate('name', $locale) ?? $category->code;
            $sublabelKey = $item['sublabel_key'] ?? $item['sublabel'] ?? null;

            return [
                'url' => route('category.show', [
                    'locale' => $locale,
                    'category' => $category->translate('slug', $locale) ?? $category->code,
                ]),
                'label' => isset($item['label_key'])
                    ? __("shop.categories.labels.{$item['label_key']}")
                    : $label,
                'sublabel' => $sublabelKey
                    ? __("shop.categories.sublabels.{$sublabelKey}")
                    : null,
                'image' => MediaUrl::orPlaceholder(
                    $imagePath,
                    null,
                    'images/products/placeholder.jpg',
                ),
                'alt' => $label,
            ];
        })->filter()->values();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected static function configuredItems(): array
    {
        $stored = HomepageSettings::instance()->resolvedBlocks();

        foreach ($stored as $block) {
            if (($block['type'] ?? null) !== 'category_showcase') {
                continue;
            }

            $items = $block['settings']['items'] ?? null;

            if (is_array($items) && $items !== []) {
                return $items;
            }
        }

        return collect(config('shop.homepage_showcase', []))
            ->map(fn (array $item) => [
                'category_code' => $item['category_code'] ?? null,
                'label_key' => $item['label'] ?? null,
                'sublabel_key' => $item['sublabel'] ?? null,
            ])
            ->filter(fn (array $item) => filled($item['category_code']))
            ->values()
            ->all();
    }

    /**
     * @param  list<int>  $categoryIds
     * @return array<int, string>
     */
    protected static function productImagesForCategories(array $categoryIds): array
    {
        if ($categoryIds === []) {
            return [];
        }

        $products = Product::query()
            ->published()
            ->whereHas('categories', fn ($query) => $query->whereIn('categories.id', $categoryIds))
            ->whereHas('images')
            ->with(['images', 'categories:id'])
            ->orderByDesc('published_at')
            ->get();

        $images = [];

        foreach ($products as $product) {
            $path = $product->images->first()?->path;

            if (! $path) {
                continue;
            }

            foreach ($product->categories as $category) {
                if (! in_array($category->id, $categoryIds, true)) {
                    continue;
                }

                $images[$category->id] ??= $path;
            }
        }

        return $images;
    }
}
