<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasTranslations;
    use SoftDeletes;

    protected $fillable = [
        'brand_id',
        'primary_category_id',
        'size_grid_id',
        'size_chart_preset_id',
        'sku',
        'model_slug',
        'color_id',
        'color_label',
        'color_slug',
        'color_hex',
        'status',
        'type',
        'base_price',
        'compare_at_price',
        'weight_grams',
        'is_featured',
        'is_new',
        'sort_order',
        'track_inventory',
        'is_ready_to_ship',
        'custom_tailoring_enabled',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_new' => 'boolean',
            'custom_tailoring_enabled' => 'boolean',
            'track_inventory' => 'boolean',
            'is_ready_to_ship' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    public function primaryCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'primary_category_id');
    }

    public function sizeGrid(): BelongsTo
    {
        return $this->belongsTo(SizeGrid::class);
    }

    public function sizeChartPreset(): BelongsTo
    {
        return $this->belongsTo(SizeChartPreset::class);
    }

    public function productRelations(): HasMany
    {
        return $this->hasMany(ProductRelation::class)->orderBy('sort_order');
    }

    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_relations', 'product_id', 'related_product_id')
            ->withPivot(['type', 'sort_order'])
            ->orderByPivot('sort_order');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withPivot('is_primary');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function catalogs(): BelongsToMany
    {
        return $this->belongsToMany(Catalog::class, 'catalog_product')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function detailItems(): HasMany
    {
        return $this->hasMany(ProductDetailItem::class)->orderBy('sort_order');
    }

    public function sizeChartRows(): HasMany
    {
        return $this->hasMany(ProductSizeChartRow::class)->orderBy('sort_order');
    }

    public function scopePublished($query)
    {
        return $query
            ->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function scopeReadyToShip($query)
    {
        return $query->where('is_ready_to_ship', true);
    }
}
