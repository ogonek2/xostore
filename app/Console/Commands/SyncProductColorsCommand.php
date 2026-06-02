<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Support\Shop\ProductColorVariantSync;
use Illuminate\Console\Command;

class SyncProductColorsCommand extends Command
{
    protected $signature = 'shop:sync-product-colors';

    protected $description = 'Sync product color_hex to variant attribute values for catalog filters';

    public function handle(): int
    {
        $count = 0;

        Product::query()
            ->whereNotNull('color_hex')
            ->where('color_hex', '!=', '')
            ->with('variants')
            ->chunkById(100, function ($products) use (&$count) {
                foreach ($products as $product) {
                    ProductColorVariantSync::syncProduct($product);
                    $count++;
                }
            });

        $this->info("Synced colors for {$count} products.");

        return self::SUCCESS;
    }
}
