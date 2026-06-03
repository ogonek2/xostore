<?php

namespace App\Support\Shop;

use App\Models\Banner;
use App\Support\Media\MediaUrl;
use Illuminate\Support\Collection;

class HomepageBanners
{
    public static function items(): Collection
    {
        $locale = app()->getLocale();

        return Banner::query()
            ->active()
            ->with('translates')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get()
            ->map(function (Banner $banner) use ($locale) {
                $url = ShopNavigation::resolveUrl($banner->translate('link_url', $locale), $locale);

                return [
                    'title' => $banner->translate('title', $locale),
                    'image' => MediaUrl::fromPath($banner->image_path),
                    'url' => $url ?: '#',
                ];
            });
    }
}
