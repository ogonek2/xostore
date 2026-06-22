<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\Feeds\ProductFeedRegenerator;
use App\Support\Shop\ProductColorVariantSync;

class ProductObserver
{
    public function saved(Product $product): void
    {
        if ($product->color_hex) {
            if ($product->wasRecentlyCreated || $product->wasChanged(['color_hex', 'color_label', 'color_slug'])) {
                ProductColorVariantSync::syncProduct($product, config('shop.default_language', 'pl'));
            }
        }

        ProductFeedRegenerator::markDirty();
    }

    public function deleted(Product $product): void
    {
        ProductFeedRegenerator::markDirty();
    }
}
