<?php

namespace App\Services\Feeds;

use App\Models\FeedSettings;

final class ProductFeedRegenerator
{
    private static bool $pending = false;

    private static bool $registered = false;

    public static function markDirty(): void
    {
        if (! config('shop.feeds.enabled', true)) {
            return;
        }

        $settings = FeedSettings::instance();

        if (! $settings->auto_regenerate) {
            return;
        }

        static::$pending = true;
        static::registerTerminator();
    }

    public static function regenerateNow(): void
    {
        if (! config('shop.feeds.enabled', true)) {
            return;
        }

        app(ProductFeedGenerator::class)->regenerateAll();
        static::$pending = false;
    }

    private static function registerTerminator(): void
    {
        if (static::$registered) {
            return;
        }

        static::$registered = true;

        app()->terminating(function (): void {
            if (! static::$pending) {
                return;
            }

            static::$pending = false;

            try {
                app(ProductFeedGenerator::class)->regenerateAll();
            } catch (\Throwable) {
                // Errors are stored on feed_settings.last_error
            }
        });
    }
}
