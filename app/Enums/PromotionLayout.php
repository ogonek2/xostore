<?php

namespace App\Enums;

enum PromotionLayout: string
{
    case Featured = 'featured';
    case Compact = 'compact';

    public function label(): string
    {
        return match ($this) {
            self::Featured => 'Крупная карточка',
            self::Compact => 'Компактная',
        };
    }
}
