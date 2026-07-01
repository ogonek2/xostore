<?php

namespace App\Models;

use App\Enums\CatalogHomepageSection;
use App\Enums\CatalogType;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Catalog extends Model
{
    use HasTranslations;

    protected $fillable = [
        'code',
        'type',
        'image_path',
        'is_active',
        'show_on_homepage',
        'homepage_section',
        'sort_order',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => CatalogType::class,
            'is_active' => 'boolean',
            'show_on_homepage' => 'boolean',
            'homepage_section' => CatalogHomepageSection::class,
            'published_at' => 'datetime',
        ];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'catalog_category');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'catalog_product')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }
}
