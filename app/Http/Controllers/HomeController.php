<?php

namespace App\Http\Controllers;

use App\Services\Cart\CartService;
use App\Support\Seo\SeoBuilder;
use App\Support\Shop\HomepagePageBuilder;
use App\Support\Shop\ShopLayoutData;
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
            'homepageBlocks' => HomepagePageBuilder::resolve($locale),
        ]);
    }
}
