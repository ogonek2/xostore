<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasTranslations;

    protected static function booted(): void
    {
        static::saving(function (Category $category) {
            if ($category->parent_id) {
                $parent = Category::query()->find($category->parent_id);
                $category->depth = ($parent?->depth ?? 0) + 1;
                $category->path = $parent?->path
                    ? "{$parent->path}/{$category->code}"
                    : $category->code;
            } else {
                $category->depth = 0;
                $category->path = $category->code;
            }
        });
    }

    protected $fillable = [
        'parent_id',
        'code',
        'type',
        'image_path',
        'depth',
        'path',
        'is_active',
        'show_in_menu',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'show_in_menu' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withPivot('is_primary');
    }

    public function sizeGrids(): BelongsToMany
    {
        return $this->belongsToMany(SizeGrid::class, 'category_size_grid');
    }

    public function catalogs(): BelongsToMany
    {
        return $this->belongsToMany(Catalog::class, 'catalog_category');
    }

    public static function idsIncludingDescendants(int $categoryId): array
    {
        $categories = static::query()
            ->where('is_active', true)
            ->get(['id', 'parent_id']);

        $byParent = $categories->groupBy('parent_id');
        $ids = [$categoryId];
        $queue = [$categoryId];

        while ($queue !== []) {
            $current = array_shift($queue);
            foreach ($byParent->get($current, collect()) as $child) {
                $ids[] = $child->id;
                $queue[] = $child->id;
            }
        }

        return array_values(array_unique($ids));
    }
}
