<?php

namespace App\Providers;

use App\Models\Color;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Observers\ColorObserver;
use App\Observers\OrderObserver;
use App\Observers\ProductImageObserver;
use App\Observers\ProductObserver;
use App\Observers\ProductVariantObserver;
use App\Services\Locale\CurrentLanguage;
use App\Support\Analytics\ShopAnalytics;
use App\View\Composers\ShopLayoutComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CurrentLanguage::class);
        $this->app->singleton(ShopAnalytics::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('payment-bridge', fn (Request $request) => Limit::perMinute(120)->by($request->ip()));
        View::composer('layouts.shop', ShopLayoutComposer::class);
        Order::observe(OrderObserver::class);
        Color::observe(ColorObserver::class);
        Product::observe(ProductObserver::class);
        ProductVariant::observe(ProductVariantObserver::class);
        ProductImage::observe(ProductImageObserver::class);
    }
}
