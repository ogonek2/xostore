<?php

namespace App\View\Components\Shop;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Hero extends Component
{
    public function __construct(
        public string $image = '/images/home/hero.jpg',
        public ?string $ctaUrl = null,
    ) {}

    public function render(): View
    {
        return view('components.shop.hero');
    }
}
