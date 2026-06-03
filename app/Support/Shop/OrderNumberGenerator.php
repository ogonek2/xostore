<?php

namespace App\Support\Shop;

use App\Models\Order;
use Illuminate\Support\Str;

final class OrderNumberGenerator
{
    /** 6 цифр + 2 буквы, например 482719XK */
    public static function generate(): string
    {
        do {
            $number = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT)
                .strtoupper(Str::random(2));
        } while (Order::query()->where('number', $number)->exists());

        return $number;
    }
}
