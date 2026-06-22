<?php

namespace App\Observers;

use App\Models\ProductVariant;
use App\Services\Feeds\ProductFeedRegenerator;

class ProductVariantObserver
{
    public function saved(ProductVariant $variant): void
    {
        ProductFeedRegenerator::markDirty();
    }

    public function deleted(ProductVariant $variant): void
    {
        ProductFeedRegenerator::markDirty();
    }
}
