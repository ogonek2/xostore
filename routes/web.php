<?php

use App\Http\Controllers\Admin\MediaPreviewController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CheckoutQuoteController;
use App\Http\Controllers\Api\NewsletterSubscriptionController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\Api\ProductListingController;
use App\Http\Controllers\Api\ShopAnalyticsController;
use App\Http\Controllers\NewsletterUnsubscribeController;
use App\Http\Controllers\CatalogShowController;
use App\Http\Controllers\CategoryShowController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\ProductIndexController;
use App\Http\Controllers\ProductShowController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', RobotsController::class)->name('robots');

Route::middleware(['web', 'auth'])
    ->prefix('admin')
    ->group(function () {
        Route::get('media/preview', MediaPreviewController::class)->name('admin.media.preview');
    });

Route::get('/', function () {
    return redirect()->route('home', ['locale' => config('shop.default_language')]);
});

Route::get('/newsletter/unsubscribe/{token}', NewsletterUnsubscribeController::class)
    ->name('newsletter.unsubscribe');

Route::get('/{locale?}', HomeController::class)
    ->where(['locale' => 'pl|en'])
    ->name('home');

Route::prefix('{locale}')
    ->where(['locale' => 'pl|en'])
    ->group(function () {
        Route::get('/api/produkty', ProductListingController::class)->name('api.products.index');
        Route::post('/api/analytics', [ShopAnalyticsController::class, 'store'])->name('api.analytics.store');
        Route::post('/api/newsletter', [NewsletterSubscriptionController::class, 'store'])->name('api.newsletter.subscribe');
        Route::get('/api/zamowienie/wycena', CheckoutQuoteController::class)->name('api.checkout.quote');

        Route::get('/api/koszyk', [CartController::class, 'show'])->name('api.cart.show');
        Route::post('/api/koszyk', [CartController::class, 'store'])->name('api.cart.store');
        Route::patch('/api/koszyk/{item}', [CartController::class, 'update'])->name('api.cart.update');
        Route::delete('/api/koszyk/{item}', [CartController::class, 'destroy'])->name('api.cart.destroy');

        Route::get('/produkty', ProductIndexController::class)->name('products.index');

        Route::get('/katalog/{catalog}', CatalogShowController::class)->name('catalog.show');

        Route::get('/c/{category}', CategoryShowController::class)->name('category.show');

        Route::get('/p/{product}', ProductShowController::class)->name('product.show');

        Route::get('/l/{landing}', LandingPageController::class)->name('landing.show');

        Route::get('/koszyk', fn (string $locale) => redirect()->route('checkout.show', ['locale' => $locale]))
            ->name('cart.show');

        Route::get('/zamowienie', [CheckoutController::class, 'show'])->name('checkout.show');
        Route::post('/zamowienie', [CheckoutController::class, 'store'])->name('checkout.store');
        Route::get('/zamowienie/dziekujemy/{order}', [CheckoutController::class, 'thankyou'])->name('checkout.thankyou');

        Route::get('/zamowienie/sledzenie', [OrderTrackingController::class, 'show'])->name('order.track');
        Route::post('/zamowienie/sledzenie', [OrderTrackingController::class, 'lookup'])->name('order.track.lookup');

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
