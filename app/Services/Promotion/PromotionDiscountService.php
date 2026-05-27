<?php

namespace App\Services\Promotion;

use App\Enums\PromotionProductTargetType;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Support\Collection;

class PromotionDiscountService
{
    /** @var Collection<int, Promotion>|null */
    protected static ?Collection $cachedPromotions = null;

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
            default => false,
        };
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
    }
}
