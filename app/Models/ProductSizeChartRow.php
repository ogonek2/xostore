<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSizeChartRow extends Model
{
    protected $fillable = [
        'product_id',
        'size',
        'chest',
        'waist',
        'hips',
        'inseam',
        'sort_order',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
