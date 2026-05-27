<?php

namespace App\Enums;

enum ProductRelationType: string
{
    case ColorVariant = 'color_variant';
    case Alternative = 'alternative';
    case Similar = 'similar';

    public function label(): string
    {
        return match ($this) {
            self::ColorVariant => 'Другой цвет (товар)',
            self::Alternative => 'Альтернатива',
            self::Similar => 'Похожий товар',
        };
    }
}
