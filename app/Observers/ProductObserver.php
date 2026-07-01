<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\Feeds\ProductFeedRegenerator;
use App\Support\Shop\ProductColorService;
use App\Support\Shop\ProductColorVariantSync;

class ProductObserver
{
    public function saving(Product $product): void
    {
        if ($product->color_id) {
            ProductColorService::applyColorFromCatalog($product);
        }
    }

    public function saved(Product $product): void
    {
        if ($product->color_hex) {
            if ($product->wasRecentlyCreated || $product->wasChanged(['color_hex', 'color_label', 'color_slug', 'color_id'])) {
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
