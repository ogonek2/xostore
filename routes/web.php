<?php

use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ProductListingController;
use App\Http\Controllers\Api\ShopAnalyticsController;
use App\Http\Controllers\CatalogShowController;
use App\Http\Controllers\CategoryShowController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ConsultationController;
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
        Route::post('/api/analytics', [ShopAnalyticsController::class, 'store'])->name('api.analytics.store');

        Route::get('/api/koszyk', [CartController::class, 'show'])->name('api.cart.show');
        Route::post('/api/koszyk', [CartController::class, 'store'])->name('api.cart.store');
        Route::patch('/api/koszyk/{item}', [CartController::class, 'update'])->name('api.cart.update');
        Route::delete('/api/koszyk/{item}', [CartController::class, 'destroy'])->name('api.cart.destroy');

        Route::get('/produkty', ProductIndexController::class)->name('products.index');

        Route::get('/katalog/{catalog}', CatalogShowController::class)->name('catalog.show');

        Route::get('/c/{category}', CategoryShowController::class)->name('category.show');

        Route::get('/p/{product}', ProductShowController::class)->name('product.show');

        Route::get('/koszyk', fn (string $locale) => redirect()->route('checkout.show', ['locale' => $locale]))
            ->name('cart.show');

        Route::get('/zamowienie', [CheckoutController::class, 'show'])->name('checkout.show');
        Route::post('/zamowienie', [CheckoutController::class, 'store'])->name('checkout.store');
        Route::get('/zamowienie/dziekujemy/{order}', [CheckoutController::class, 'thankyou'])->name('checkout.thankyou');

        Route::get('/konsultacja/{product?}', [ConsultationController::class, 'show'])->name('consultation.show');
        Route::post('/konsultacja', [ConsultationController::class, 'store'])->name('consultation.store');

        Route::get('/promocje', fn (string $locale) => redirect()->route('catalog.show', [
            'locale' => $locale,
            'catalog' => $locale === 'en' ? 'promotions' : 'promocje',
        ]))->name('promotions.index');

        Route::get('/nowynki', fn (string $locale) => redirect()->route('catalog.show', [
            'locale' => $locale,
            'catalog' => $locale === 'en' ? 'new-in' : 'nowynki',
        ]))->name('new-arrivals.index');
    });
