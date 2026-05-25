<?php

namespace App\View\Components\Shop;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Header extends Component
{
    public function __construct(
        public Collection $menuRoots,
        public Collection $languages,
        public int $cartCount = 0,
    ) {}

    public function render(): View
    {
        return view('components.shop.header');
    }
}
