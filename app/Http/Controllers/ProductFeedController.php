<?php

namespace App\Http\Controllers;

use App\Models\FeedSettings;
use App\Services\Feeds\ProductFeedGenerator;
use Illuminate\Http\Response;

class ProductFeedController extends Controller
{
    public function __invoke(string $slug, ProductFeedGenerator $generator): Response
    {
        if (! config('shop.feeds.enabled', true)) {
            abort(404);
        }

        $settings = FeedSettings::instance();

        if ($slug === $settings->google_slug && $settings->google_enabled) {
            return response($generator->ensureGoogleFeed(), 200, [
                'Content-Type' => 'application/xml; charset=UTF-8',
                'Cache-Control' => 'public, max-age=3600',
            ]);
        }

        if ($slug === $settings->facebook_slug && $settings->facebook_enabled) {
            return response($generator->ensureFacebookFeed(), 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Cache-Control' => 'public, max-age=3600',
            ]);
        }

        abort(404);
    }
}
