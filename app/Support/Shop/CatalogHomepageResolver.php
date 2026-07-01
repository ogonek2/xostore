<?php

namespace App\Support\Shop;

use App\Enums\CatalogHomepageSection;
use App\Models\Catalog;

final class CatalogHomepageResolver
{
    public static function forSection(CatalogHomepageSection $section): ?Catalog
    {
        $catalog = Catalog::query()
            ->where('is_active', true)
            ->where('show_on_homepage', true)
            ->where('homepage_section', $section->value)
            ->orderBy('sort_order')
            ->first();

        if ($catalog) {
            return $catalog;
        }

        return Catalog::query()
            ->where('is_active', true)
            ->where('code', $section->defaultCatalogCode())
            ->first();
    }
}
