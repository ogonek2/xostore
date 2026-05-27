<?php

namespace App\Enums;

enum CatalogType: string
{
    case Manual = 'manual';
    case Categories = 'categories';
    case Mixed = 'mixed';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Ручной выбор товаров',
            self::Categories => 'Из категорий',
            self::Mixed => 'Категории + товары',
        };
    }
}
