<?php

namespace App\Enums;

enum CategoryType: string
{
    case Women = 'women';
    case Men = 'men';
    case Accessories = 'accessories';
    case Unisex = 'unisex';

    public function label(): string
    {
        return match ($this) {
            self::Women => 'Женское',
            self::Men => 'Мужское',
            self::Accessories => 'Аксессуары',
            self::Unisex => 'Унисекс',
        };
    }
}
