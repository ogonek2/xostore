<?php

namespace App\Enums;

enum CatalogHomepageSection: string
{
    case Trending = 'trending';
    case NewArrivals = 'new_arrivals';

    public function label(): string
    {
        return match ($this) {
            self::Trending => 'Тренды',
            self::NewArrivals => 'Новинки',
        };
    }

    public function defaultCatalogCode(): string
    {
        return match ($this) {
            self::Trending => 'trendy',
            self::NewArrivals => 'nowynki',
        };
    }
}
