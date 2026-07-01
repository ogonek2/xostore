<?php

namespace App\Support\Shop;

use App\Enums\CatalogHomepageSection;
use Illuminate\Support\Collection;

class TrendingProducts
{
    public static function forHomepage(string $locale, int $limit = 12): Collection
    {
        $result = CatalogHomepageProducts::forSection(
            CatalogHomepageSection::Trending,
            $locale,
            $limit,
        );

        return ProductCardPresenter::collection($result['products'], $locale);
    }
}
