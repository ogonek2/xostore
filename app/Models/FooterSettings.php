<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FooterSettings extends Model
{
    protected $fillable = [
        'newsletter_enabled',
        'newsletter',
        'brand',
        'social_enabled',
        'social',
        'contact_enabled',
        'contact',
        'payments_enabled',
        'payments',
        'bottom',
    ];

    protected function casts(): array
    {
        return [
            'newsletter_enabled' => 'boolean',
            'newsletter' => 'array',
            'brand' => 'array',
            'social_enabled' => 'boolean',
            'social' => 'array',
            'contact_enabled' => 'boolean',
            'contact' => 'array',
            'payments_enabled' => 'boolean',
            'payments' => 'array',
            'bottom' => 'array',
        ];
    }

    public static function instance(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'newsletter_enabled' => false,
                'social_enabled' => false,
                'contact_enabled' => false,
                'payments_enabled' => false,
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
