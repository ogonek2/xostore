<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasTranslations;

    protected $fillable = [
        'image_path',
        'sort_order',
        'is_active',
    ];

    public function translatableFields(): array
    {
        return config('shop.banner.translatable_fields', [
            'title',
            'link_url',
        ]);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
