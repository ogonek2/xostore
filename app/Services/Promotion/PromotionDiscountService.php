<?php

namespace App\Services\Promotion;

use App\Enums\PromotionProductTargetType;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Support\Collection;

class PromotionDiscountService
{
    /** @var Collection<int, Promotion>|null */
    protected static ?Collection $cachedPromotions = null;

    /** @var Collection<int, int>|null */
    protected static ?Collection $cachedDiscountedProductIds = null;

    public function discountPercentForProduct(Product $product): ?int
    {
        $product->loadMissing(['categories', 'catalogs']);

        $best = null;

        foreach ($this->activePromotions() as $promotion) {
            if (! $this->promotionAppliesToProduct($promotion, $product)) {
                continue;
            }

            $percent = (int) ($promotion->discount_percent ?? 0);

            if ($percent < 1) {
                continue;
            }

            if ($best === null || $percent > $best) {
                $best = $percent;
            }
        }

        return $best;
    }

    public function applyDiscount(float $price, Product $product): float
    {
        $percent = $this->discountPercentForProduct($product);

        if (! $percent) {
            return $price;
        }

        return round($price * (1 - $percent / 100), 2);
    }

    /**
     * @return Collection<int, int>
     */
    public function discountedProductIds(): Collection
    {
        if (static::$cachedDiscountedProductIds !== null) {
            return static::$cachedDiscountedProductIds;
        }

        $ids = collect();

        foreach ($this->activePromotions() as $promotion) {
            $ids = $ids->merge($this->productIdsForPromotion($promotion));
        }

        static::$cachedDiscountedProductIds = $ids->unique()->values();

        return static::$cachedDiscountedProductIds;
    }

    protected function promotionAppliesToProduct(Promotion $promotion, Product $product): bool
    {
        $target = $promotion->product_target_type;

        if (! $target) {
            return $promotion->category_id
                && $product->categories->contains('id', $promotion->category_id);
        }

        return match ($target) {
            PromotionProductTargetType::Category => $promotion->category_id
                && (
                    $product->primary_category_id === $promotion->category_id
                    || $product->categories->contains('id', $promotion->category_id)
                ),
            PromotionProductTargetType::Catalog => $promotion->catalog_id
                && $product->catalogs->contains('id', $promotion->catalog_id),
            PromotionProductTargetType::Products => $promotion->relationLoaded('products')
                ? $promotion->products->contains('id', $product->id)
                : $promotion->products()->where('products.id', $product->id)->exists(),
            PromotionProductTargetType::Brand => $promotion->brand_id
                && $product->brand_id === $promotion->brand_id,
            default => false,
        };
    }

    /**
     * @return Collection<int, int>
     */
    protected function productIdsForPromotion(Promotion $promotion): Collection
    {
        $target = $promotion->product_target_type;

        if (! $target && $promotion->category_id) {
            return $this->productIdsForCategory($promotion->category_id);
        }

        return match ($target) {
            PromotionProductTargetType::Category => $promotion->category_id
                ? $this->productIdsForCategory($promotion->category_id)
                : collect(),
            PromotionProductTargetType::Catalog => $promotion->catalog_id
                ? Product::query()
                    ->published()
                    ->whereHas('catalogs', fn ($q) => $q->where('catalogs.id', $promotion->catalog_id))
                    ->pluck('id')
                : collect(),
            PromotionProductTargetType::Products => $promotion->products()->pluck('products.id'),
            PromotionProductTargetType::Brand => $promotion->brand_id
                ? Product::query()
                    ->published()
                    ->where('brand_id', $promotion->brand_id)
                    ->pluck('id')
                : collect(),
            default => collect(),
        };
    }

    /**
     * @return Collection<int, int>
     */
    protected function productIdsForCategory(int $categoryId): Collection
    {
        $categoryIds = Category::idsIncludingDescendants($categoryId);

        return Product::query()
            ->published()
            ->where(function ($q) use ($categoryId, $categoryIds) {
                $q->where('primary_category_id', $categoryId)
                    ->orWhereHas('categories', fn ($c) => $c->whereIn('categories.id', $categoryIds));
            })
            ->pluck('id');
    }

    /**
     * @return Collection<int, Promotion>
     */
    protected function activePromotions(): Collection
    {
        if (static::$cachedPromotions !== null) {
            return static::$cachedPromotions;
        }

        static::$cachedPromotions = Promotion::query()
            ->active()
            ->currentlyRunning()
            ->whereNotNull('discount_percent')
            ->where('discount_percent', '>', 0)
            ->with(['products:id'])
            ->get();

        return static::$cachedPromotions;
    }

    public static function clearCache(): void
    {
        static::$cachedPromotions = null;
        static::$cachedDiscountedProductIds = null;
    }
}
