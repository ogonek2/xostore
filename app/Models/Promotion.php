<?php

namespace App\Models;

use App\Enums\PromotionLayout;
use App\Enums\PromotionProductTargetType;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Promotion extends Model
{
    use HasTranslations;

    protected $fillable = [
        'code',
        'layout',
        'image_path',
        'link_url',
        'category_id',
        'product_target_type',
        'catalog_id',
        'brand_id',
        'discount_percent',
        'starts_at',
        'expires_at',
        'is_active',
        'show_on_homepage',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'layout' => PromotionLayout::class,
            'product_target_type' => PromotionProductTargetType::class,
            'discount_percent' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
            'show_on_homepage' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(Catalog::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'promotion_product')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOnHomepage(Builder $query): Builder
    {
        return $query->where('show_on_homepage', true);
    }

    public function scopeCurrentlyRunning(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
            });
    }
}
