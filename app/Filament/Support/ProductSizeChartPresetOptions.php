<?php

namespace App\Filament\Support;

use App\Models\SizeChartPreset;
use App\Models\SizeChartPresetRow;
use Illuminate\Database\Eloquent\Builder;

final class ProductSizeChartPresetOptions
{
    /**
     * @return array<string, string>
     */
    public static function presets(?int $alwaysIncludePresetId = null): array
    {
        $presets = static::query()->get();

        if ($alwaysIncludePresetId && ! $presets->contains('id', $alwaysIncludePresetId)) {
            $extra = SizeChartPreset::query()
                ->with('rows')
                ->whereKey($alwaysIncludePresetId)
                ->first();

            if ($extra) {
                $presets = $presets->prepend($extra);
            }
        }

        return $presets
            ->unique('id')
            ->mapWithKeys(fn (SizeChartPreset $preset) => [
                (string) $preset->id => static::label($preset),
            ])
            ->all();
    }

    public static function query(): Builder
    {
        return SizeChartPreset::query()
            ->where('is_active', true)
            ->with('rows')
            ->orderBy('code');
    }

    public static function label(SizeChartPreset $preset): string
    {
        $name = $preset->translate('name', 'pl') ?? $preset->code;
        $count = $preset->rows->count();
        $unit = filled($preset->unit) ? " · {$preset->unit}" : '';

        return $count > 0
            ? "{$name}{$unit} — {$count} rozmiarów"
            : "{$name}{$unit}";
    }

    /**
     * @return list<array{size: string, chest: ?string, waist: ?string, hips: ?string, inseam: ?string, sort_order: int}>
     */
    public static function rowsForProductCopy(int $presetId): array
    {
        $preset = SizeChartPreset::query()
            ->with(['rows' => fn ($q) => $q->orderBy('sort_order')])
            ->find($presetId);

        if (! $preset) {
            return [];
        }

        return $preset->rows
            ->map(fn (SizeChartPresetRow $row, int $index) => [
                'size' => $row->size,
                'chest' => static::formatCm($row->chest_cm),
                'waist' => static::formatCm($row->waist_cm),
                'hips' => static::formatCm($row->hips_cm),
                'inseam' => static::formatCm($row->inseam_cm),
                'sort_order' => $row->sort_order ?: $index + 1,
            ])
            ->values()
            ->all();
    }

    public static function formatCm(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $number = is_numeric($value) ? (float) $value : null;

        if ($number === null) {
            return null;
        }

        $formatted = fmod($number, 1.0) === 0.0
            ? (string) (int) $number
            : rtrim(rtrim(number_format($number, 1, '.', ''), '0'), '.');

        return "{$formatted} cm";
    }
}
