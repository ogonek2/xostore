<?php

namespace App\Http\Controllers;

use App\Support\Shop\HomepageCategoryShowcase;
use App\Support\Shop\ShopLayoutData;
use App\Support\Shop\HomepagePromotions;
use App\Support\Shop\NewArrivalsProducts;
use App\Support\Shop\TrendingProducts;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(?string $locale = null): View
    {
        $locale = app()->getLocale();

        return view('home', [
            ...ShopLayoutData::shared(),
            'cartCount' => app(\App\Services\Cart\CartService::class)->count(),
            'categoryCards' => HomepageCategoryShowcase::cards($locale),
            'trendingProducts' => TrendingProducts::forHomepage($locale),
            'promotions' => HomepagePromotions::forHomepage($locale),
            'newArrivals' => NewArrivalsProducts::forHomepage($locale),
        ]);
    }
}
