<?php

namespace App\Filament\Support;

use App\Models\SizeGrid;
use Illuminate\Database\Eloquent\Builder;

final class ProductSizeGridOptions
{
    /**
     * @return array<int, string>
     */
    public static function presets(?int $categoryId = null): array
    {
        return static::presetsQuery($categoryId)
            ->get()
            ->mapWithKeys(fn (SizeGrid $grid) => [
                $grid->id => static::label($grid),
            ])
            ->all();
    }

    public static function presetsQuery(?int $categoryId = null): Builder
    {
        $query = SizeGrid::query()
            ->where('is_active', true)
            ->with('values')
            ->orderBy('code');

        if ($categoryId) {
            $query->where(function (Builder $q) use ($categoryId): void {
                $q->whereHas('categories', fn (Builder $c) => $c->where('categories.id', $categoryId))
                    ->orWhereDoesntHave('categories');
            });
        }

        return $query;
    }

    public static function label(SizeGrid $grid): string
    {
        $name = $grid->translate('name', 'pl') ?? $grid->code;
        $count = $grid->values->count();
        $unit = filled($grid->unit) ? " ({$grid->unit})" : '';

        return $count > 0
            ? "{$name}{$unit} — {$count} разм."
            : "{$name}{$unit}";
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
}
