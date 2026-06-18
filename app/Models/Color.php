<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Color extends Model
{
    use HasTranslations;

    protected $fillable = [
        'code',
        'hex',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function label(?string $locale = null): ?string
    {
        $locale ??= (string) config('shop.default_language', 'pl');

        return $this->translate('name', $locale) ?? $this->code;
    }
}
