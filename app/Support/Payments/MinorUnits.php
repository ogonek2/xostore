<?php

namespace App\Support\Payments;

use InvalidArgumentException;

final class MinorUnits
{
    public static function fromDecimal(int|float|string $amount): int
    {
        $value = trim((string) $amount);

        if (! preg_match('/^(\d+)(?:\.(\d{1,2}))?$/', $value, $matches)) {
            throw new InvalidArgumentException('Amount must be a non-negative decimal with at most two fraction digits.');
        }

        return ((int) $matches[1] * 100) + (int) str_pad($matches[2] ?? '', 2, '0');
    }
}
