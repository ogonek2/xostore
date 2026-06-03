<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersProductListing;
use App\Http\Requests\ProductListingRequest;
use App\Support\Seo\SeoBuilder;
use App\Support\Shop\SlugResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CatalogShowController extends Controller
{
    use RendersProductListing;

    public function __invoke(ProductListingRequest $request, string $locale, string $catalog): View|RedirectResponse
    {
        $record = SlugResolver::catalog($catalog, $locale);

        if (! $record) {
            abort(404);
        }

        $name = $record->translate('name', $locale) ?? $record->code;

        $slug = $record->translate('slug', $locale) ?? $record->code;

        return $this->listingView(
            request: $request,
            locale: $locale,
            pageTitle: $name,
            seo: SeoBuilder::fromTranslatable(
                $record,
                $locale,
                $name,
                route('catalog.show', ['locale' => $locale, 'catalog' => $slug]),
            ),
            breadcrumbs: [
                ['label' => __('shop.nav.shop'), 'url' => route('products.index', ['locale' => $locale])],
                ['label' => $name, 'url' => null],
            ],
            catalog: $record,
        );
    }
}
