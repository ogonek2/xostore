<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersProductListing;
use App\Http\Requests\ProductListingRequest;
use App\Support\Shop\SlugResolver;
use Illuminate\View\View;

class CategoryShowController extends Controller
{
    use RendersProductListing;

    public function __invoke(ProductListingRequest $request, string $locale, string $category): View
    {
        $record = SlugResolver::category($category, $locale);

        if (! $record) {
            abort(404);
        }

        $record->load(['parent.translates', 'children' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->with('translates')]);

        $name = $record->translate('name', $locale) ?? $record->code;

        $breadcrumbs = [
            ['label' => __('shop.nav.shop'), 'url' => route('products.index', ['locale' => $locale])],
        ];

        if ($record->parent) {
            $parentSlug = $record->parent->translate('slug', $locale);
            if ($parentSlug) {
                $breadcrumbs[] = [
                    'label' => $record->parent->translate('name', $locale),
                    'url' => route('category.show', ['locale' => $locale, 'category' => $parentSlug]),
                ];
            }
        }

        $breadcrumbs[] = ['label' => $name, 'url' => null];

        return $this->listingView(
            request: $request,
            locale: $locale,
            pageTitle: $name,
            metaDescription: $record->translate('description', $locale),
            breadcrumbs: $breadcrumbs,
            category: $record,
        );
    }
}
