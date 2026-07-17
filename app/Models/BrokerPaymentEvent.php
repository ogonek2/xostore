<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrokerPaymentEvent extends Model
{
    protected $fillable = [
        'broker_payment_intent_id', 'event_id', 'fingerprint',
        'status', 'payload', 'occurred_at',
    ];

    protected function casts(): array
    {
        return ['payload' => 'array', 'occurred_at' => 'datetime'];
    }

    public function intent(): BelongsTo
    {
        return $this->belongsTo(BrokerPaymentIntent::class, 'broker_payment_intent_id');
    }
}
