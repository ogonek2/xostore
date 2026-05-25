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
            self::Manual => 'Ręczny wybór produktów',
            self::Categories => 'Z kategorii',
            self::Mixed => 'Kategorie + produkty',
        };
    }
}
