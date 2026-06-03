<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SizeChartPresetRow extends Model
{
    protected $fillable = [
        'size_chart_preset_id',
        'size',
        'chest_cm',
        'waist_cm',
        'hips_cm',
        'inseam_cm',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'chest_cm' => 'decimal:1',
            'waist_cm' => 'decimal:1',
            'hips_cm' => 'decimal:1',
            'inseam_cm' => 'decimal:1',
        ];
    }

    public function preset(): BelongsTo
    {
        return $this->belongsTo(SizeChartPreset::class, 'size_chart_preset_id');
    }
}
