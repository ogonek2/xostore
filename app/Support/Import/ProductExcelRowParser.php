<?php

namespace App\Support\Import;

use Carbon\Carbon;
use Illuminate\Support\Str;

final class ProductExcelRowParser
{
    /**
     * @param  list<string|null>  $headerRow
     * @return array<string, string>
     */
    public static function mapHeader(array $headerRow): array
    {
        $map = [];
        $aliases = ProductImportColumns::aliases();

        foreach ($headerRow as $index => $cell) {
            $key = Str::lower(trim((string) $cell));
            $key = str_replace([' ', '-'], '_', $key);

            if ($key === '') {
                continue;
            }

            foreach ($aliases as $canonical => $options) {
                if (in_array($key, $options, true) || $key === $canonical) {
                    $map[$index] = $canonical;

                    continue 2;
                }
            }

            if (in_array($key, ProductImportColumns::keys(), true)) {
                $map[$index] = $key;
            }
        }

        return $map;
    }

    /**
     * @param  array<int, string>  $headerMap
     * @param  list<mixed>  $row
     * @return array<string, string>
     */
    public static function mapRow(array $headerMap, array $row): array
    {
        $data = [];

        foreach ($headerMap as $index => $key) {
            $value = trim((string) ($row[$index] ?? ''));

            if ($value !== '') {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    public static function rowIsEmpty(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    public static function parseBool(?string $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = Str::lower(trim($value));

        return in_array($normalized, ['1', 'true', 'yes', 'tak', 'да', 'y'], true);
    }

    public static function parseFloat(?string $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = str_replace([' ', ','], ['', '.'], trim($value));

        return is_numeric($normalized) ? (float) $normalized : null;
    }

    public static function parseInt(?string $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    public static function parseDate(?string $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param  list<string>  $codes
     * @return list<string>
     */
    public static function parseCodeList(?string $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        return collect(explode(',', $value))
            ->map(fn (string $code) => trim($code))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
