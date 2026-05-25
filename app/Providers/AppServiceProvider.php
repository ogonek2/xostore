<?php

namespace App\Providers;

use App\Services\Locale\CurrentLanguage;
use App\View\Composers\ShopLayoutComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CurrentLanguage::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.shop', ShopLayoutComposer::class);
    }
}
