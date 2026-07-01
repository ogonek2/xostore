<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Support\Shop\ProductColorService;
use App\Support\Shop\ProductColorVariantSync;
use Illuminate\Console\Command;

class SyncProductColorsCommand extends Command
{
    protected $signature = 'shop:sync-product-colors';

    protected $description = 'Sync product color_hex from catalog and variant attribute values for filters';

    public function handle(): int
    {
        $count = 0;

        Product::query()
            ->where(function ($query): void {
                $query->whereNotNull('color_id')
                    ->orWhere(function ($query): void {
                        $query->whereNotNull('color_hex')->where('color_hex', '!=', '');
                    });
            })
            ->with('variants')
            ->chunkById(100, function ($products) use (&$count): void {
                foreach ($products as $product) {
                    ProductColorService::syncProduct($product);
                    ProductColorVariantSync::syncProduct($product);
                    $count++;
                }
            });

        $this->info("Synced colors for {$count} products.");

        return self::SUCCESS;
    }
}
