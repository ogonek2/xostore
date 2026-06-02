<?php

namespace App\Models;

use App\Enums\NavPanelType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NavPanel extends Model
{
    protected $fillable = [
        'nav_item_id',
        'type',
        'title_labels',
        'category_id',
        'catalog_id',
        'show_subcategories',
        'show_products',
        'columns',
        'item_limit',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => NavPanelType::class,
            'title_labels' => 'array',
            'columns' => 'integer',
            'item_limit' => 'integer',
            'show_subcategories' => 'boolean',
            'show_products' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(NavItem::class, 'nav_item_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(Catalog::class);
    }

    public function links(): HasMany
    {
        return $this->hasMany(NavPanelLink::class)->orderBy('sort_order');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'nav_panel_product')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    public function title(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $labels = $this->title_labels ?? [];

        if (! empty($labels[$locale])) {
            return $labels[$locale];
        }

        $default = (string) config('shop.default_language', 'pl');

        return $labels[$default] ?? reset($labels) ?: null;
    }

    protected static function booted(): void
    {
        static::saving(function (NavPanel $panel): void {
            if (! is_array($panel->title_labels)) {
                return;
            }

            $panel->title_labels = collect($panel->title_labels)
                ->map(fn ($value) => is_string($value) ? trim($value) : $value)
                ->filter(fn ($value) => is_string($value) && $value !== '')
                ->all();
        });
    }
}
