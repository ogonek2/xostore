<?php

namespace App\Enums;

enum PromotionProductTargetType: string
{
    case Products = 'products';
    case Category = 'category';
    case Catalog = 'catalog';
    case Brand = 'brand';

    public function label(): string
    {
        return match ($this) {
            self::Products => 'Товар (выбранные)',
            self::Category => 'Категория',
            self::Catalog => 'Отдельная группа (каталог)',
            self::Brand => 'Бренд',
        };
    }
}
