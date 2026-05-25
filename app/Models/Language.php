<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Language extends Model
{
    protected $fillable = [
        'code',
        'name',
        'locale',
        'flag',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function translates(): HasMany
    {
        return $this->hasMany(Translate::class);
    }

    public static function default(): ?self
    {
        return static::query()
            ->where('is_active', true)
            ->where('is_default', true)
            ->first()
            ?? static::query()->where('is_active', true)->orderBy('sort_order')->first();
    }
}
