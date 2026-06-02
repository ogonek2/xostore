<?php

namespace App\Support\Shop;

use App\Models\Banner;
use App\Support\Media\MediaUrl;
use Illuminate\Support\Collection;

class HomepageBanners
{
    public static function items(): Collection
    {
        return Banner::query()
            ->active()
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Banner $banner) => [
                'title' => $banner->title,
                'image' => MediaUrl::fromPath($banner->image_path),
                'url' => $banner->link_url ?: '#',
            ]);
    }
}
