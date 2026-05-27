<?php

namespace App\Enums;

enum PromotionProductTargetType: string
{
    case Category = 'category';
    case Catalog = 'catalog';
    case Products = 'products';

    public function label(): string
    {
        return match ($this) {
            self::Category => 'Категория',
            self::Catalog => 'Каталог (коллекция)',
            self::Products => 'Выбранные товары',
        };
    }
}
