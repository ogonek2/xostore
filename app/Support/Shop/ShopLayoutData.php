<?php

namespace App\Support\Shop;

use App\Models\Category;
use App\Models\Language;
use Illuminate\Support\Collection;

class ShopLayoutData
{
    public static function menuRoots(): Collection
    {
        return Category::query()
            ->with([
                'translates',
                'children' => fn ($q) => $q
                    ->where('is_active', true)
                    ->where('show_in_menu', true)
                    ->orderBy('sort_order')
                    ->with('translates'),
            ])
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->where('show_in_menu', true)
            ->orderBy('sort_order')
            ->get()
            ->keyBy('type');
    }

    public static function languages(): Collection
    {
        return Language::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public static function shared(): array
    {
        return [
            'menuRoots' => static::menuRoots(),
            'languages' => static::languages(),
            'navigation' => ShopNavigation::tree('header'),
        ];
    }
}
