<?php

namespace App\Support\Shop;

use App\Models\SizeGridValue;
use Illuminate\Database\Eloquent\Builder;

final class ProductListingSizeFacets
{
    /**
     * Unique in-stock sizes available across the current catalog scope (fashion-style flat list).
     *
     * @return list<array{
     *     id: int,
     *     label: string,
     *     sizes: list<array{key: string, label: string}>
     * }>
     */
    public static function build(Builder $productScope, string $locale): array
    {
        $values = SizeGridValue::query()
            ->whereHas('variants', fn (Builder $q) => $q
                ->where('is_active', true)
                ->whereNotNull('size_grid_value_id')
                ->whereIn('product_id', $productScope))
            ->orderBy('sort_order')
            ->get();

        if ($values->isEmpty()) {
            return [];
        }

        /** @var array<string, array{key: string, label: string, sort: int}> $unique */
        $unique = [];

        foreach ($values as $value) {
            $label = trim((string) ($value->display_value ?: $value->value));
            $normalized = static::normalizeValue($label);

            if ($normalized === '' || $label === '') {
                continue;
            }

            if (! isset($unique[$normalized])) {
                $unique[$normalized] = [
                    'key' => static::filterKey($normalized),
                    'label' => $label,
                    'sort' => static::sortWeight($label, (int) $value->sort_order),
                ];

                continue;
            }

            $unique[$normalized]['sort'] = min($unique[$normalized]['sort'], static::sortWeight($label, (int) $value->sort_order));
        }

        $sizes = collect($unique)
            ->sortBy('sort')
            ->values()
            ->map(fn (array $size) => [
                'key' => $size['key'],
                'label' => $size['label'],
            ])
            ->all();

        if ($sizes === []) {
            return [];
        }

        return [[
            'id' => 0,
            'label' => '',
            'sizes' => $sizes,
        ]];
    }

    public static function normalizeValue(string $value): string
    {
        return strtolower(trim($value));
    }

    public static function filterKey(string $normalizedValue): string
    {
        return static::normalizeValue($normalizedValue);
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

                if (! is_string($filter) || trim($filter) === '') {
                    continue;
                }

                if (str_contains($filter, ':')) {
                    [$gridId, $normalized] = explode(':', $filter, 2);
                    $gridId = (int) $gridId;
                    $normalized = static::normalizeValue($normalized);

                    if ($gridId <= 0 || $normalized === '') {
                        continue;
                    }

                    $outer->orWhereHas('sizeGridValue', function (Builder $sizeValue) use ($gridId, $normalized): void {
                        static::applyNormalizedMatch($sizeValue->where('size_grid_id', $gridId), $normalized);
                    });

                    continue;
                }

                $normalized = static::normalizeValue($filter);

                if ($normalized === '') {
                    continue;
                }

                $outer->orWhereHas('sizeGridValue', function (Builder $sizeValue) use ($normalized): void {
                    static::applyNormalizedMatch($sizeValue, $normalized);
                });
            }
        });
    }

    protected static function applyNormalizedMatch(Builder $sizeValue, string $normalized): void
    {
        $sizeValue->where(function (Builder $match) use ($normalized): void {
            $match->whereRaw('LOWER(TRIM(value)) = ?', [$normalized])
                ->orWhereRaw('LOWER(TRIM(COALESCE(display_value, value))) = ?', [$normalized]);
        });
    }

    protected static function sortWeight(string $label, int $gridSortOrder): int
    {
        static $letterOrder = [
            'xxxs' => 10,
            'xxs' => 20,
            'xs' => 30,
            's' => 40,
            'm' => 50,
            'l' => 60,
            'xl' => 70,
            'xxl' => 80,
            '2xl' => 85,
            '3xl' => 90,
            '4xl' => 95,
            '5xl' => 100,
        ];

        $key = strtolower(trim($label));

        if (isset($letterOrder[$key])) {
            return $letterOrder[$key];
        }

        if (is_numeric($key)) {
            return 500 + (int) $key;
        }

        return 1000 + $gridSortOrder;
    }
}
