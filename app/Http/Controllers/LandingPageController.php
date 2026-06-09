<?php

namespace App\Http\Controllers;

use App\Support\Seo\SeoBuilder;
use App\Support\Shop\LandingPagePresenter;
use App\Support\Shop\ShopLayoutData;
use App\Services\Cart\CartService;
use Illuminate\View\View;

class LandingPageController extends Controller
{
    public function __invoke(string $locale, string $landing): View
    {
        $record = LandingPagePresenter::resolveBySlug($landing, $locale);

        if (! $record) {
            abort(404);
        }

        $presented = LandingPagePresenter::page($record, $locale);

        return view('shop.landing', [
            ...ShopLayoutData::shared(),
            'showFooter' => $presented['show_footer'] ?? true,
            'cartCount' => app(CartService::class)->count(),
            'seo' => SeoBuilder::fromTranslatable(
                $record,
                $locale,
                $presented['name'],
                route('landing.show', ['locale' => $locale, 'landing' => $presented['slug']]),
                $record->translate('meta_description', $locale),
            ),
            'landing' => $presented,
        ]);
    }
}
