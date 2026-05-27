<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Translate extends Model
{
    protected $fillable = [
        'language_id',
        'translatable_type',
        'translatable_id',
        'field',
        'value',
        'is_machine_translated',
    ];

    protected function casts(): array
    {
        return [
            'is_machine_translated' => 'boolean',
        ];
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function translatable(): MorphTo
    {
        return $this->morphTo();
    }
}
