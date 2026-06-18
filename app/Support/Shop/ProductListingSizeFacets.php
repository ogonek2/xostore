<?php

namespace App\Support\Shop;

use App\Enums\SizeGridPresetKind;
use App\Models\SizeGrid;
use App\Models\SizeGridValue;
use Illuminate\Database\Eloquent\Builder;

final class ProductListingSizeFacets
{
    /**
     * @return list<array{
     *     id: int,
     *     label: string,
     *     sizes: list<array{key: string, label: string}>
     * }>
     */
    public static function build(Builder $productScope, string $locale): array
    {
        $values = SizeGridValue::query()
            ->with(['sizeGrid.translates'])
            ->whereHas('variants', fn (Builder $q) => $q
                ->where('is_active', true)
                ->whereIn('product_id', $productScope))
            ->orderBy('sort_order')
            ->get();

        if ($values->isEmpty()) {
            return [];
        }

        /** @var array<int, array{id: int, label: string, sort: int, sizes: array<string, array{key: string, label: string, sort: int}>}> $groups */
        $groups = [];

        foreach ($values as $value) {
            $grid = $value->sizeGrid;

            if (! $grid) {
                continue;
            }

            $gridId = (int) $grid->id;
            $normalized = static::normalizeValue($value->value);
            $label = trim((string) ($value->display_value ?: $value->value));

            if ($normalized === '' || $label === '') {
                continue;
            }

            if (! isset($groups[$gridId])) {
                $groups[$gridId] = [
                    'id' => $gridId,
                    'label' => static::gridLabel($grid, $locale),
                    'sort' => (int) $value->sort_order,
                    'sizes' => [],
                ];
            }

            $groups[$gridId]['sort'] = min($groups[$gridId]['sort'], (int) $value->sort_order);

            if (! isset($groups[$gridId]['sizes'][$normalized])) {
                $groups[$gridId]['sizes'][$normalized] = [
                    'key' => static::filterKey($gridId, $normalized),
                    'label' => $label,
                    'sort' => (int) $value->sort_order,
                ];
            }
        }

        return collect($groups)
            ->sortBy('sort')
            ->values()
            ->map(function (array $group): array {
                $sizes = collect($group['sizes'])
                    ->sortBy('sort')
                    ->values()
                    ->map(fn (array $size) => [
                        'key' => $size['key'],
                        'label' => $size['label'],
                    ])
                    ->all();

                return [
                    'id' => $group['id'],
                    'label' => $group['label'],
                    'sizes' => $sizes,
                ];
            })
            ->filter(fn (array $group) => $group['sizes'] !== [])
            ->values()
            ->all();
    }

    public static function normalizeValue(string $value): string
    {
        return strtolower(trim($value));
    }

    public static function filterKey(int $gridId, string $normalizedValue): string
    {
        return $gridId.':'.static::normalizeValue($normalizedValue);
    }

    /**
     * @param  list<int|string>  $sizeFilters
     */
    public static function applyToVariantsQuery(Builder $variantsQuery, array $sizeFilters): void
    {
        if ($sizeFilters === []) {
            return;
        }

        $variantsQuery->where(function (Builder $outer) use ($sizeFilters): void {
            foreach ($sizeFilters as $filter) {
                if (is_numeric($filter)) {
                    $outer->orWhere('size_grid_value_id', (int) $filter);

                    continue;
                }

                if (! is_string($filter) || ! str_contains($filter, ':')) {
                    continue;
                }

                [$gridId, $normalized] = explode(':', $filter, 2);
                $gridId = (int) $gridId;
                $normalized = static::normalizeValue($normalized);

                if ($gridId <= 0 || $normalized === '') {
                    continue;
                }

                $outer->orWhereHas('sizeGridValue', function (Builder $sizeValue) use ($gridId, $normalized): void {
                    $sizeValue
                        ->where('size_grid_id', $gridId)
                        ->where(function (Builder $match) use ($normalized): void {
                            $match->whereRaw('LOWER(TRIM(value)) = ?', [$normalized])
                                ->orWhereRaw('LOWER(TRIM(COALESCE(display_value, value))) = ?', [$normalized]);
                        });
                });
            }
        });
    }

    protected static function gridLabel(SizeGrid $grid, string $locale): string
    {
        $translated = $grid->translate('name', $locale);

        if (filled($translated)) {
            return (string) $translated;
        }

        $preset = SizeGridPresetKind::tryFrom((string) $grid->preset_kind);

        if ($preset) {
            return __('shop.size_grid_labels.'.$preset->value);
        }

        return $grid->code;
    }
}
