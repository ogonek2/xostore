<?php

namespace App\Http\Controllers;

use App\Services\Cart\CartService;
use App\Support\Seo\SeoBuilder;
use App\Support\Shop\HomepageBanners;
use App\Support\Shop\HomepageCategoryShowcase;
use App\Support\Shop\HomepageHeroBanners;
use App\Support\Shop\HomepagePromotions;
use App\Support\Shop\NewArrivalsProducts;
use App\Support\Shop\ShopLayoutData;
use App\Support\Shop\TrendingProducts;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(?string $locale = null): View
    {
        $locale = app()->getLocale();

        return view('home', [
            ...ShopLayoutData::shared(),
            'seo' => SeoBuilder::forHome($locale),
            'cartCount' => app(CartService::class)->count(),
            'heroBannerSections' => HomepageHeroBanners::sections(),
            'bannersEnabled' => (bool) config('shop.homepage_banners.enabled', true),
            'banners' => HomepageBanners::items(),
            'categoryCards' => HomepageCategoryShowcase::cards($locale),
            'trendingProducts' => TrendingProducts::forHomepage($locale),
            'promotions' => HomepagePromotions::forHomepage($locale),
            'newArrivals' => NewArrivalsProducts::forHomepage($locale),
        ]);
    }
}
