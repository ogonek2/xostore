<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersProductListing;
use App\Http\Requests\ProductListingRequest;
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
            breadcrumbs: [
                ['label' => __('shop.nav.shop'), 'url' => route('products.index', ['locale' => $locale])],
            ],
        );
    }
}
