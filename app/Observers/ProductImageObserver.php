<?php

namespace App\Observers;

use App\Models\ProductImage;
use App\Services\Feeds\ProductFeedRegenerator;

class ProductImageObserver
{
    public function saved(ProductImage $image): void
    {
        ProductFeedRegenerator::markDirty();
    }

    public function deleted(ProductImage $image): void
    {
        ProductFeedRegenerator::markDirty();
    }
}
