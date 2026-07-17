<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BrokerPaymentIntent extends Model
{
    protected $fillable = [
        'broker_payment_id', 'source_payment_id', 'source_order_number',
        'amount_minor', 'currency', 'locale', 'return_url', 'return_token',
        'customer_ip', 'buyer', 'products', 'payu_order_id', 'redirect_uri',
        'status', 'idempotency_key', 'paid_at', 'cancelled_at', 'last_event_at',
        'callback_delivery_attempts', 'callback_delivery_error', 'callback_delivered_at',
    ];

    protected $hidden = ['buyer', 'customer_ip', 'return_token'];

    protected function casts(): array
    {
        return [
            'buyer' => 'encrypted:array',
            'products' => 'array',
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'last_event_at' => 'datetime',
            'callback_delivered_at' => 'datetime',
        ];
    }

    public function events(): HasMany
    {
        return $this->hasMany(BrokerPaymentEvent::class);
    }
}
