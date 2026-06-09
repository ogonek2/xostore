<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LandingPage extends Model
{
    use HasTranslations;

    protected $fillable = [
        'code',
        'is_active',
        'show_header',
        'show_footer',
        'sort_order',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'show_header' => 'boolean',
            'show_footer' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(LandingPageBlock::class)->orderBy('sort_order');
    }

    public function activeBlocks(): HasMany
    {
        return $this->blocks()->where('is_active', true);
    }

    public function scopePublished($query)
    {
        return $query
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }
}
