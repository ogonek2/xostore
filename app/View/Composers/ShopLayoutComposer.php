<?php

namespace App\View\Composers;

use App\Services\Cart\CartService;
use App\Support\Shop\ShopFooter;
use App\Support\Shop\ShopLayoutData;
use Illuminate\View\View;

class ShopLayoutComposer
{
    public function compose(View $view): void
    {
        if (! $view->offsetExists('menuRoots')) {
            $view->with('menuRoots', ShopLayoutData::menuRoots());
        }

        if (! $view->offsetExists('languages')) {
            $view->with('languages', ShopLayoutData::languages());
        }

        if (! $view->offsetExists('cartCount')) {
            $view->with('cartCount', app(CartService::class)->count());
        }

        if (! $view->offsetExists('footer')) {
            $view->with('footer', ShopFooter::data());
        }
    }
}
