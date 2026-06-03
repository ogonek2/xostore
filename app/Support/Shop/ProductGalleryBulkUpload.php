<?php

namespace App\Support\Shop;

use App\Models\Product;
use App\Support\Media\Media;
use Illuminate\Support\Arr;

final class ProductGalleryBulkUpload
{
    /**
     * @param  array<int, string>|string|null  $paths
     */
    public function store(Product $product, array|string|null $paths): int
    {
        $paths = array_values(array_filter(
            Arr::wrap($paths),
            fn (mixed $path): bool => is_string($path) && trim($path) !== '',
        ));

        if ($paths === []) {
            return 0;
        }

        $product->loadMissing('translates');

        $startOrder = (int) $product->images()->max('sort_order') + 1;
        $setPrimary = ! $product->images()->exists();
        $created = 0;

        foreach ($paths as $offset => $path) {
            $sequence = $startOrder + $offset + 1;

            $product->images()->create([
                'path' => $path,
                'disk' => Media::disk(),
                'alt' => ProductImageAltGenerator::generate($product, $sequence),
                'sort_order' => $startOrder + $offset,
                'is_primary' => $setPrimary && $offset === 0,
            ]);

            $created++;
        }

        return $created;
    }
}
