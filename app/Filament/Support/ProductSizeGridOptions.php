<?php

namespace App\Filament\Support;

use App\Models\SizeGrid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class ProductSizeGridOptions
{
    /**
     * Все активные пресеты для выбора в товаре (без фильтра по категории — иначе текущий пресет может «пропасть» и валидация падает).
     *
     * @return array<string, string>
     */
    public static function presets(?int $categoryId = null, ?int $alwaysIncludeGridId = null): array
    {
        $grids = static::presetsQuery($categoryId)->get();

        if ($alwaysIncludeGridId && ! $grids->contains('id', $alwaysIncludeGridId)) {
            $extra = SizeGrid::query()
                ->with('values')
                ->whereKey($alwaysIncludeGridId)
                ->first();

            if ($extra) {
                $grids = $grids->prepend($extra);
            }
        }

        return $grids
            ->unique('id')
            ->mapWithKeys(fn (SizeGrid $grid) => [
                (string) $grid->id => static::label($grid),
            ])
            ->all();
    }

    public static function presetsQuery(?int $categoryId = null): Builder
    {
        return SizeGrid::query()
            ->where('is_active', true)
            ->with('values')
            ->orderBy('code');
    }

    public static function label(SizeGrid $grid): string
    {
        $name = $grid->translate('name', 'pl') ?? $grid->code;
        $count = $grid->values->count();
        $unit = filled($grid->unit) ? " · {$grid->unit}" : '';
        $sizes = $count > 0
            ? ' — '.static::previewSizes($grid->values)
            : '';

        return "{$name}{$unit}{$sizes}";
    }

    protected static function previewSizes(Collection $values): string
    {
        $labels = $values
            ->sortBy('sort_order')
            ->map(fn ($value) => $value->display_value ?? $value->value)
            ->values();

        if ($labels->count() <= 8) {
            return $labels->implode(', ');
        }

        return $labels->take(6)->implode(', ').'… +'.($labels->count() - 6);
    }

    /**
     * @return list<string>
     */
    public static function sizeLabels(?int $sizeGridId): array
    {
        if (! $sizeGridId) {
            return [];
        }

        $grid = SizeGrid::query()
            ->with(['values' => fn ($q) => $q->orderBy('sort_order')])
            ->find($sizeGridId);

        if (! $grid) {
            return [];
        }

        return $grid->values
            ->map(fn ($value) => $value->display_value ?? $value->value)
            ->values()
            ->all();
    }

    /**
     * @return list<array{size: string, chest: null, waist: null, hips: null, inseam: null, sort_order: int}>
     */
    public static function emptyChartRows(?int $sizeGridId): array
    {
        $labels = static::sizeLabels($sizeGridId);

        return array_map(
            fn (string $size, int $index) => [
                'size' => $size,
                'chest' => null,
                'waist' => null,
                'hips' => null,
                'inseam' => null,
                'sort_order' => $index + 1,
            ],
            $labels,
            array_keys($labels),
        );
    }
}
