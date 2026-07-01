<?php

namespace App\View\Components\Shop;

use App\Support\Shop\MobileNavUtilities;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Header extends Component
{
    /** @var list<array<string, mixed>> */
    public array $navigation = [];

    /** @var list<array<string, mixed>> */
    public array $megaItems = [];

    public Collection $simpleNavItems;

    /** @var list<array<string, mixed>> */
    public array $catalogMegaItems = [];

    /** @var list<array<string, mixed>> */
    public array $mobileUtilityLinks = [];

    /** @var array{quick_actions: list<array<string, mixed>>, collections: list<array<string, mixed>>, pages: list<array<string, mixed>>, dropdowns: list<array<string, mixed>>} */
    public array $mobileMenu = [
        'quick_actions' => [],
        'collections' => [],
        'pages' => [],
        'dropdowns' => [],
    ];

    /**
     * @param  list<array<string, mixed>>  $navigation
     */
    public function __construct(
        public Collection $languages,
        public int $cartCount = 0,
        array $navigation = [],
    ) {
        $this->prepareNavigation($navigation);
    }

    /**
     * @param  list<array<string, mixed>>  $navigation
     */
    protected function prepareNavigation(array $navigation): void
    {
        $megaIndex = 0;
        $prepared = [];
        $megaItems = [];

        foreach ($navigation as $item) {
            if (($item['type'] ?? '') === 'mega' && ! empty($item['panels'])) {
                $item['mega_index'] = $megaIndex;
                $megaItems[] = $item;
                $megaIndex++;
            }

            $prepared[] = $item;
        }

        $this->navigation = $prepared;
        $this->megaItems = $megaItems;
        $this->simpleNavItems = collect($prepared)->filter(
            fn (array $item) => ($item['type'] ?? '') !== 'mega' || empty($item['panels'])
        );
        $this->catalogMegaItems = MobileNavUtilities::catalogMegaItems($megaItems);
        $this->mobileMenu = MobileNavUtilities::menu(
            $this->cartCount,
            $this->simpleNavItems,
            $this->catalogMegaItems,
        );
        $this->mobileUtilityLinks = collect($this->mobileMenu['quick_actions'])
            ->merge($this->mobileMenu['collections'])
            ->merge($this->mobileMenu['pages'])
            ->values()
            ->all();
    }

    public function render(): View
    {
        return view('components.shop.header');
    }
}
