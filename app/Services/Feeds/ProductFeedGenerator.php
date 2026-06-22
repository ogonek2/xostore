<?php

namespace App\Services\Feeds;

use App\Models\FeedSettings;
use App\Support\Feeds\FacebookCatalogFeedBuilder;
use App\Support\Feeds\GoogleMerchantFeedBuilder;
use App\Support\Feeds\ProductFeedItemCollector;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

final class ProductFeedGenerator
{
    public function regenerateAll(): FeedSettings
    {
        if (! config('shop.feeds.enabled', true)) {
            return FeedSettings::instance();
        }

        $settings = FeedSettings::instance();
        $startedAt = microtime(true);

        try {
            $items = (new ProductFeedItemCollector($settings))->collect();
            $disk = Storage::disk($settings->storageDisk());
            $directory = $settings->storageDirectory();

            if (! $disk->exists($directory)) {
                $disk->makeDirectory($directory);
            }

            if ($settings->google_enabled) {
                $this->writeGoogleFeed($settings, $items);
            }

            if ($settings->facebook_enabled) {
                $this->writeFacebookFeed($settings, $items);
            }

            $settings->forceFill([
                'last_duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                'last_error' => null,
            ])->save();

            return $settings->refresh();
        } catch (\Throwable $exception) {
            $settings->forceFill([
                'last_error' => $exception->getMessage(),
                'last_duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            ])->save();

            Log::error('Product feed regeneration failed', [
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function ensureGoogleFeed(): string
    {
        $settings = FeedSettings::instance();

        if (! $settings->google_enabled) {
            abort(404);
        }

        if (! $settings->googleFileExists()) {
            $this->regenerateAll();
        }

        return Storage::disk($settings->storageDisk())->get($settings->googleStoragePath()) ?? '';
    }

    public function ensureFacebookFeed(): string
    {
        $settings = FeedSettings::instance();

        if (! $settings->facebook_enabled) {
            abort(404);
        }

        if (! $settings->facebookFileExists()) {
            $this->regenerateAll();
        }

        return Storage::disk($settings->storageDisk())->get($settings->facebookStoragePath()) ?? '';
    }

    /**
     * @param  list<\App\Support\Feeds\ProductFeedItem>  $items
     */
    private function writeGoogleFeed(FeedSettings $settings, array $items): void
    {
        $builder = new GoogleMerchantFeedBuilder;
        $xml = $builder->build(
            items: $items,
            shopName: (string) config('shop.name', 'XOStore'),
            shopUrl: url('/'),
        );

        Storage::disk($settings->storageDisk())->put($settings->googleStoragePath(), $xml);

        $settings->forceFill([
            'google_last_generated_at' => now(),
            'google_item_count' => count($items),
            'google_file_size' => strlen($xml),
        ])->save();
    }

    /**
     * @param  list<\App\Support\Feeds\ProductFeedItem>  $items
     */
    private function writeFacebookFeed(FeedSettings $settings, array $items): void
    {
        $builder = new FacebookCatalogFeedBuilder;
        $csv = $builder->build($items);

        Storage::disk($settings->storageDisk())->put($settings->facebookStoragePath(), $csv);

        $settings->forceFill([
            'facebook_last_generated_at' => now(),
            'facebook_item_count' => count($items),
            'facebook_file_size' => strlen($csv),
        ])->save();
    }
}
