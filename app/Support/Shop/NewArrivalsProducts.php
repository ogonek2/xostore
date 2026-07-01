<?php

namespace App\Support\Shop;

use App\Enums\CatalogHomepageSection;
use Illuminate\Support\Collection;

class NewArrivalsProducts
{
    public static function forHomepage(string $locale, int $limit = 12): array
    {
        $result = CatalogHomepageProducts::forSection(
            CatalogHomepageSection::NewArrivals,
            $locale,
            $limit,
        );

        return [
            'products' => ProductCardPresenter::collection($result['products'], $locale, compact: true),
            'view_all_url' => $result['view_all_url'],
        ];
    }
}
