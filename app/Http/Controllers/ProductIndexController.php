<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersProductListing;
use App\Http\Requests\ProductListingRequest;
use App\Support\Seo\SeoBuilder;
use Illuminate\View\View;

class ProductIndexController extends Controller
{
    use RendersProductListing;

    public function __invoke(ProductListingRequest $request, string $locale): View
    {
        return $this->listingView(
            request: $request,
            locale: $locale,
            pageTitle: __('shop.listing.all_products'),
            seo: SeoBuilder::forListing(
                title: __('shop.listing.all_products'),
                canonical: route('products.index', ['locale' => $locale]),
                fallbackDescription: __('seo.products_index_description', locale: $locale),
            ),
            breadcrumbs: [
                ['label' => __('shop.nav.shop'), 'url' => route('products.index', ['locale' => $locale])],
            ],
        );
    }
}
