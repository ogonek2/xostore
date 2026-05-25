<?php

namespace App\View\Components\Shop;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class CategoriesShowcase extends Component
{
    public function __construct(
        public Collection $cards,
    ) {}

    public function render(): View
    {
        return view('components.shop.categories-showcase');
    }
}
