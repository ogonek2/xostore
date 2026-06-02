<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NavPanelLink extends Model
{
    protected $fillable = [
        'nav_panel_id',
        'labels',
        'url',
        'open_in_new_tab',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'labels' => 'array',
            'open_in_new_tab' => 'boolean',
        ];
    }

    public function panel(): BelongsTo
    {
        return $this->belongsTo(NavPanel::class, 'nav_panel_id');
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
        static::saving(function (NavPanelLink $link): void {
            if (! is_array($link->labels)) {
                return;
            }

            $link->labels = collect($link->labels)
                ->map(fn ($value) => is_string($value) ? trim($value) : $value)
                ->filter(fn ($value) => is_string($value) && $value !== '')
                ->all();
        });
    }
}
