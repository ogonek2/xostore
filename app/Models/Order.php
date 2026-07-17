<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'number',
        'access_token',
        'order_status_id',
        'payment_method_id',
        'locale',
        'currency',
        'email',
        'phone',
        'customer_name',
        'first_name',
        'last_name',
        'company',
        'street',
        'delivery_address',
        'city',
        'postal_code',
        'country',
        'notes',
        'subtotal',
        'shipping',
        'total',
        'placed_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping' => 'decimal:2',
            'total' => 'decimal:2',
            'placed_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function orderStatus(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class);
    }

    public function displayName(): string
    {
        return $this->customer_name
            ?: trim(($this->first_name ?? '').' '.($this->last_name ?? ''))
            ?: '—';
    }

    public function displayAddress(): string
    {
        return $this->delivery_address ?: (string) $this->street;
    }

    public function statusLabel(?string $locale = null): string
    {
        return $this->orderStatus?->label($locale) ?? '—';
    }
}
