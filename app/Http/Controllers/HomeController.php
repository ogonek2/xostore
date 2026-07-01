<?php

namespace App\Http\Controllers;

use App\Models\HomepageSettings;
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
        $homepage = HomepageSettings::instance();

        return view('home', [
            ...ShopLayoutData::shared(),
            'seo' => SeoBuilder::forHome($locale),
            'cartCount' => app(CartService::class)->count(),
            'heroBannerSections' => HomepageHeroBanners::sections(),
            'homepage' => $homepage,
            'bannersEnabled' => $homepage->show_banners_section && (bool) config('shop.homepage_banners.enabled', true),
            'banners' => HomepageBanners::items(),
            'categoryCards' => $homepage->show_category_showcase
                ? HomepageCategoryShowcase::cards($locale)
                : collect(),
            'trendingProducts' => $homepage->show_trending_section
                ? TrendingProducts::forHomepage($locale)
                : collect(),
            'promotions' => $homepage->show_promotions_section
                ? HomepagePromotions::forHomepage($locale)
                : ['featured' => null, 'compact' => collect(), 'view_all_url' => '#'],
            'newArrivals' => $homepage->show_new_arrivals_section
                ? NewArrivalsProducts::forHomepage($locale)
                : ['products' => collect(), 'view_all_url' => '#'],
        ]);
    }
}
