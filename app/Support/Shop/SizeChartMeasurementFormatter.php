<?php

namespace App\Support\Shop;

final class SizeChartMeasurementFormatter
{
    public static function format(mixed $value, string $unit = 'cm'): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value) && ! is_numeric($value)) {
            return $value;
        }

        $number = (float) $value;
        $formatted = fmod($number, 1.0) === 0.0
            ? (string) (int) $number
            : rtrim(rtrim(number_format($number, 1, '.', ''), '0'), '.');

        return $unit !== '' ? "{$formatted} {$unit}" : $formatted;
    }
}
