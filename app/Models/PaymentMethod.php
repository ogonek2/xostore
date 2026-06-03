<?php

namespace App\Models;

use App\Enums\PaymentMethodType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    protected $fillable = [
        'code',
        'type',
        'labels',
        'instructions',
        'is_active',
        'sort_order',
        'shipping_enabled',
        'shipping_cost',
        'free_shipping_enabled',
        'free_shipping_from',
        'redirect_url',
        'bank_recipient',
        'bank_name',
        'bank_account',
        'payment_note_template',
    ];

    protected function casts(): array
    {
        return [
            'type' => PaymentMethodType::class,
            'labels' => 'array',
            'instructions' => 'array',
            'is_active' => 'boolean',
            'shipping_enabled' => 'boolean',
            'shipping_cost' => 'decimal:2',
            'free_shipping_enabled' => 'boolean',
            'free_shipping_from' => 'decimal:2',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function label(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $labels = $this->labels ?? [];
        $default = (string) config('shop.default_language', 'pl');

        return $labels[$locale] ?? $labels[$default] ?? reset($labels) ?: null;
    }

    public function instructionsText(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $items = $this->instructions ?? [];
        $default = (string) config('shop.default_language', 'pl');

        return $items[$locale] ?? $items[$default] ?? reset($items) ?: null;
    }

    public function isGateway(): bool
    {
        return $this->type === PaymentMethodType::PaymentGateway;
    }
}
