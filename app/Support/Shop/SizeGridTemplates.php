<?php

namespace App\Support\Shop;

use App\Enums\SizeGridPresetKind;

final class SizeGridTemplates
{
    /**
     * @return list<array{value: string, display_value: string, sort_order: int}>
     */
    public static function valuesFor(SizeGridPresetKind|string|null $kind): array
    {
        $kind = is_string($kind) ? SizeGridPresetKind::tryFrom($kind) : $kind;

        if (! $kind instanceof SizeGridPresetKind) {
            return [];
        }

        return match ($kind) {
            SizeGridPresetKind::ClothingLetters => static::letters(['XXS', 'XS', 'S', 'M', 'L', 'XL', 'XXL']),
            SizeGridPresetKind::ClothingNumeric => static::numeric(32, 48, 2),
            SizeGridPresetKind::Footwear => static::numeric(35, 42),
            SizeGridPresetKind::Bags => static::letters(['S', 'M', 'L']),
            SizeGridPresetKind::Belts => static::numeric(80, 110, 5),
            SizeGridPresetKind::OneSize => [
                ['value' => 'one_size', 'display_value' => 'One size', 'sort_order' => 0],
            ],
            SizeGridPresetKind::Custom => [],
        };
    }

    /**
     * @return array<string, string>
     */
    public static function bagSizeAlternatives(): array
    {
        return [
            'bags_sml' => 'Маленькая / Средняя / Большая (S, M, L)',
            'bags_cm_25_35' => 'Ширина в см (25, 30, 35)',
            'bags_one_size' => 'Без размера (One size)',
        ];
    }

    /**
     * @return list<array{value: string, display_value: string, sort_order: int}>
     */
    public static function bagAlternativeValues(string $key): array
    {
        return match ($key) {
            'bags_cm_25_35' => static::numeric(25, 35, 5),
            'bags_one_size' => [
                ['value' => 'one_size', 'display_value' => 'One size', 'sort_order' => 0],
            ],
            default => static::letters(['S', 'M', 'L']),
        };
    }

    /**
     * @param  list<string>  $codes
     * @return list<array{value: string, display_value: string, sort_order: int}>
     */
    protected static function letters(array $codes): array
    {
        $rows = [];

        foreach ($codes as $index => $code) {
            $rows[] = [
                'value' => strtolower($code),
                'display_value' => strtoupper($code),
                'sort_order' => $index,
            ];
        }

        return $rows;
    }

    /**
     * @return list<array{value: string, display_value: string, sort_order: int}>
     */
    protected static function numeric(int $from, int $to, int $step = 1): array
    {
        $rows = [];
        $index = 0;

        foreach (range($from, $to, $step) as $number) {
            $rows[] = [
                'value' => (string) $number,
                'display_value' => (string) $number,
                'sort_order' => $index++,
            ];
        }

        return $rows;
    }
}
