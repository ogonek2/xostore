<?php

namespace App\View\Composers;

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
    }
}
