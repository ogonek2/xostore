<?php

namespace App\Support\Import;

use App\Enums\SizeGridPresetKind;
use App\Models\SizeChartPreset;
use App\Models\SizeGrid;
use App\Support\Shop\SizeGridTemplates;
use Illuminate\Support\Str;

final class ImportSizePresetCatalog
{
    /**
     * @return array<string, string>
     */
    public static function sizeGridAliases(): array
    {
        return [
            'clothing_standard' => 'clothing_letter_unisex',
            'eu_footwear' => 'footwear_eu',
            'bags_cm_25_35' => 'bags_cm',
            'bags_one_size' => 'accessories_one_size',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function sizeChartPresetAliases(): array
    {
        return [
            'dresses_women' => 'women_dresses_cm',
            'dress_women' => 'women_dresses_cm',
            'women_dresses' => 'women_dresses_cm',
            'tops_women' => 'women_tops_cm',
            'men_shirts' => 'men_tops_cm',
            'men_trousers' => 'men_trousers_cm',
            'outerwear' => 'outerwear_cm',
        ];
    }

    public static function resolveSizeGridCode(string $input): string
    {
        $normalized = Str::lower(trim($input));

        return static::sizeGridAliases()[$normalized] ?? $normalized;
    }

    public static function resolveSizeChartPresetCode(string $input): string
    {
        $normalized = Str::lower(trim($input));

        return static::sizeChartPresetAliases()[$normalized] ?? $normalized;
    }

    public static function findSizeGrid(string $input): ?SizeGrid
    {
        $input = trim($input);

        if ($input === '') {
            return null;
        }

        foreach (static::sizeGridCodeCandidates($input) as $code) {
            $grid = SizeGrid::query()
                ->whereRaw('LOWER(code) = ?', [Str::lower($code)])
                ->first();

            if ($grid) {
                return $grid;
            }
        }

        return static::findByTranslatedName(SizeGrid::class, $input);
    }

    public static function findSizeChartPreset(string $input): ?SizeChartPreset
    {
        $input = trim($input);

        if ($input === '') {
            return null;
        }

        foreach (static::sizeChartPresetCodeCandidates($input) as $code) {
            $preset = SizeChartPreset::query()
                ->whereRaw('LOWER(code) = ?', [Str::lower($code)])
                ->first();

            if ($preset) {
                return $preset;
            }
        }

        return static::findByTranslatedName(SizeChartPreset::class, $input);
    }

    /**
     * @return list<string>
     */
    public static function sizeGridCodeCandidates(string $input): array
    {
        $normalized = Str::lower(trim($input));
        $resolved = static::resolveSizeGridCode($input);

        return array_values(array_unique(array_filter([
            $input,
            $normalized,
            $resolved,
            Str::slug($input),
            Str::slug($resolved),
        ])));
    }

    /**
     * @return list<string>
     */
    public static function sizeChartPresetCodeCandidates(string $input): array
    {
        $normalized = Str::lower(trim($input));
        $resolved = static::resolveSizeChartPresetCode($input);

        return array_values(array_unique(array_filter([
            $input,
            $normalized,
            $resolved,
            Str::slug($input),
            Str::slug($resolved),
        ])));
    }

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelClass
     */
    protected static function findByTranslatedName(string $modelClass, string $input): ?\Illuminate\Database\Eloquent\Model
    {
        $needle = Str::lower(trim($input));

        return $modelClass::query()
            ->whereHas('translates', function ($query) use ($needle): void {
                $query->where('field', 'name')
                    ->whereRaw('LOWER(value) = ?', [$needle]);
            })
            ->first();
    }

    /**
     * @return list<array{value: string, display_value: string, sort_order: int}>
     */
    public static function templateValuesForCode(string $code): array
    {
        $code = static::resolveSizeGridCode($code);

        $kind = SizeGridPresetKind::tryFrom($code);

        if ($kind) {
            return SizeGridTemplates::valuesFor($kind);
        }

        return match ($code) {
            'clothing_letter_women', 'clothing_letter_men', 'clothing_letter_unisex',
            'outerwear_letter', 'knitwear_letter', 'lingerie_letter' => SizeGridTemplates::valuesFor(SizeGridPresetKind::ClothingLetters),
            'clothing_eu_numeric', 'denim_waist' => SizeGridTemplates::valuesFor(SizeGridPresetKind::ClothingNumeric),
            'footwear_eu' => SizeGridTemplates::valuesFor(SizeGridPresetKind::Footwear),
            'bags_sml' => SizeGridTemplates::valuesFor(SizeGridPresetKind::Bags),
            'bags_cm' => SizeGridTemplates::bagAlternativeValues('bags_cm_25_35'),
            'belts_cm' => SizeGridTemplates::valuesFor(SizeGridPresetKind::Belts),
            'accessories_one_size' => SizeGridTemplates::valuesFor(SizeGridPresetKind::OneSize),
            default => [],
        };
    }
}
