<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopCommerceSettings extends Model
{
    protected $fillable = [
        'delivery_enabled',
        'payment_enabled',
        'delivery',
        'payment',
    ];

    protected function casts(): array
    {
        return [
            'delivery_enabled' => 'boolean',
            'payment_enabled' => 'boolean',
            'delivery' => 'array',
            'payment' => 'array',
        ];
    }

    public static function instance(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'delivery_enabled' => true,
                'payment_enabled' => true,
                'delivery' => [
                    'pl' => [
                        'title' => 'Dostawa',
                        'summary' => 'Kurier i paczkomaty InPost na terenie Polski.',
                        'details' => "Czas realizacji: 1–3 dni robocze.\nWysyłka: 1–2 dni robocze po potwierdzeniu zamówienia.",
                    ],
                    'en' => [
                        'title' => 'Delivery',
                        'summary' => 'Courier and InPost parcel lockers across Poland.',
                        'details' => "Processing time: 1–3 business days.\nShipping: 1–2 business days after order confirmation.",
                    ],
                ],
                'payment' => [
                    'pl' => [
                        'title' => 'Płatność',
                        'summary' => 'Bezpieczne metody płatności dostępne przy składaniu zamówienia.',
                    ],
                    'en' => [
                        'title' => 'Payment',
                        'summary' => 'Secure payment methods available at checkout.',
                    ],
                ],
            ],
        );
    }

    public function text(string $group, string $key, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $default = (string) config('shop.default_language', 'pl');
        $data = $this->{$group} ?? [];

        if (! is_array($data)) {
            return null;
        }

        $value = $data[$locale][$key] ?? $data[$default][$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }
}
