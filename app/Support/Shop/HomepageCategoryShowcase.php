<?php

namespace App\Support\Shop;

use App\Models\Category;
use Illuminate\Support\Collection;

class HomepageCategoryShowcase
{
    public static function cards(string $locale): Collection
    {
        $items = config('shop.homepage_showcase', []);

        $codes = collect($items)->pluck('category_code')->filter()->all();

        $categories = Category::query()
            ->with('translates')
            ->whereIn('code', $codes)
            ->get()
            ->keyBy('code');

        return collect($items)->map(function (array $item) use ($categories, $locale) {
            $category = $categories->get($item['category_code'] ?? '');

            return [
                'url' => $category
                    ? route('category.show', ['locale' => $locale, 'category' => $category->translate('slug', $locale) ?? $category->code])
                    : '#',
                'label' => __("shop.categories.labels.{$item['label']}"),
                'sublabel' => isset($item['sublabel'])
                    ? __("shop.categories.sublabels.{$item['sublabel']}")
                    : null,
                'image' => asset($item['image']),
                'alt' => $category?->translate('name', $locale) ?? $item['label'],
            ];
        });
    }
}
