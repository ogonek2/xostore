<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderStatus extends Model
{
    protected $fillable = [
        'code',
        'labels',
        'color',
        'is_active',
        'is_default',
        'counts_towards_revenue',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'labels' => 'array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'counts_towards_revenue' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function emailTemplate(): HasOne
    {
        return $this->hasOne(OrderStatusEmailTemplate::class);
    }

    public function label(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $labels = $this->labels ?? [];
        $default = (string) config('shop.default_language', 'pl');

        return $labels[$locale] ?? $labels[$default] ?? reset($labels) ?: $this->code;
    }

    public static function default(): ?self
    {
        return static::query()
            ->where('is_active', true)
            ->where('is_default', true)
            ->first()
            ?? static::query()->where('is_active', true)->orderBy('sort_order')->first();
    }

    public static function forCode(string $code): ?self
    {
        return static::query()->where('code', $code)->first();
    }
}
