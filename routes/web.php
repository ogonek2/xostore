<?php

use App\Http\Controllers\Api\ProductListingController;
use App\Http\Controllers\CatalogShowController;
use App\Http\Controllers\CategoryShowController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductIndexController;
use App\Http\Controllers\ProductShowController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('home', ['locale' => config('shop.default_language')]);
});

Route::get('/{locale?}', HomeController::class)
    ->where(['locale' => 'pl|en'])
    ->name('home');

Route::prefix('{locale}')
    ->where(['locale' => 'pl|en'])
    ->group(function () {
        Route::get('/api/produkty', ProductListingController::class)->name('api.products.index');

        Route::get('/produkty', ProductIndexController::class)->name('products.index');

        Route::get('/katalog/{catalog}', CatalogShowController::class)->name('catalog.show');

        Route::get('/c/{category}', CategoryShowController::class)->name('category.show');

        Route::get('/p/{product}', ProductShowController::class)->name('product.show');

        Route::get('/promocje', fn (string $locale) => redirect()->route('catalog.show', [
            'locale' => $locale,
            'catalog' => $locale === 'en' ? 'promotions' : 'promocje',
        ]))->name('promotions.index');

        Route::get('/nowynki', fn (string $locale) => redirect()->route('catalog.show', [
            'locale' => $locale,
            'catalog' => $locale === 'en' ? 'new-in' : 'nowynki',
        ]))->name('new-arrivals.index');
    });
