<?php

namespace App\Enums;

enum SizeGridPresetKind: string
{
    case ClothingLetters = 'clothing_letters';
    case ClothingNumeric = 'clothing_numeric';
    case Footwear = 'footwear';
    case Bags = 'bags';
    case Belts = 'belts';
    case OneSize = 'one_size';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::ClothingLetters => 'Одежда (буквы S, M, L…)',
            self::ClothingNumeric => 'Одежда (числа EU 32, 34…)',
            self::Footwear => 'Обувь (EU)',
            self::Bags => 'Сумки и аксессуары',
            self::Belts => 'Ремни (см)',
            self::OneSize => 'Без размера (one size)',
            self::Custom => 'Свой набор',
        };
    }

    public function hint(): string
    {
        return match ($this) {
            self::ClothingLetters => 'Для платьев, курток, свитеров — кнопки XXS–XXL на карточке товара.',
            self::ClothingNumeric => 'Для одежды с европейской нумерацией (32, 34, 36…).',
            self::Footwear => 'Размеры обуви EU 35–42 (можно изменить вручную).',
            self::Bags => 'Маленькая / средняя / большая сумка или размеры в см (25, 30…).',
            self::Belts => 'Длина ремня в сантиметрах.',
            self::OneSize => 'Один размер: шарфы, клатчи без выбора размера.',
            self::Custom => 'Заполните список размеров вручную ниже.',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $kind) => [$kind->value => $kind->label()])
            ->all();
    }
}
