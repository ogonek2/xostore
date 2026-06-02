<?php

namespace App\Observers;

use App\Models\Product;
use App\Support\Shop\ProductColorVariantSync;

class ProductObserver
{
    public function saved(Product $product): void
    {
        if (! $product->color_hex) {
            return;
        }

        if (! $product->wasRecentlyCreated && ! $product->wasChanged(['color_hex', 'color_label', 'color_slug'])) {
            return;
        }

        ProductColorVariantSync::syncProduct($product, config('shop.default_language', 'pl'));
    }
}
