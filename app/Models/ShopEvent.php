<?php

namespace App\Models;

use App\Enums\ShopEventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'shop_visitor_session_id',
        'event_type',
        'path',
        'product_id',
        'category_id',
        'product_variant_id',
        'payload',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => ShopEventType::class,
            'payload' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ShopVisitorSession::class, 'shop_visitor_session_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
