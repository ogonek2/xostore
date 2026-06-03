<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SizeChartPreset extends Model
{
    use HasTranslations;

    protected $fillable = [
        'code',
        'unit',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function rows(): HasMany
    {
        return $this->hasMany(SizeChartPresetRow::class)->orderBy('sort_order');
    }
}
