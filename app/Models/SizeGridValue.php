<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SizeGridValue extends Model
{
    protected $fillable = [
        'size_grid_id',
        'value',
        'display_value',
        'sort_order',
    ];

    public function sizeGrid(): BelongsTo
    {
        return $this->belongsTo(SizeGrid::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
