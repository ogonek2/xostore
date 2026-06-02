<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NavItem extends Model
{
    protected $fillable = [
        'nav_menu_id',
        'parent_id',
        'labels',
        'url',
        'open_in_new_tab',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'labels' => 'array',
            'open_in_new_tab' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(NavMenu::class, 'nav_menu_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(NavItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(NavItem::class, 'parent_id')->orderBy('sort_order');
    }

    public function panels(): HasMany
    {
        return $this->hasMany(NavPanel::class)->orderBy('sort_order');
    }

    public function label(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $labels = $this->labels ?? [];

        if (! empty($labels[$locale])) {
            return $labels[$locale];
        }

        $default = (string) config('shop.default_language', 'pl');

        return $labels[$default] ?? reset($labels) ?: null;
    }

    protected static function booted(): void
    {
        static::creating(function (NavItem $item): void {
            if ($item->nav_menu_id || ! $item->parent_id) {
                return;
            }

            $item->nav_menu_id = NavItem::query()
                ->whereKey($item->parent_id)
                ->value('nav_menu_id');
        });

        static::saving(function (NavItem $item): void {
            if (! is_array($item->labels)) {
                return;
            }

            $item->labels = collect($item->labels)
                ->map(fn ($value) => is_string($value) ? trim($value) : $value)
                ->filter(fn ($value) => is_string($value) && $value !== '')
                ->all();
        });
    }
}
