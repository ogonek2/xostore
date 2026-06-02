<?php

namespace App\Support\Shop;

use App\Models\HeroBannerSection;
use App\Support\Media\MediaUrl;

class HomepageHeroBanners
{
    public static function sections(): array
    {
        $locale = app()->getLocale();

        return HeroBannerSection::query()
            ->active()
            ->with([
                'items' => fn ($q) => $q
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->with('translates'),
            ])
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($section) => [
                'name' => $section->name,
                'layout' => $section->layout,
                'items' => $section->items
                    ->map(fn ($item) => [
                        'title' => $item->translate('title', $locale),
                        'subtitle' => $item->translate('subtitle', $locale),
                        'image' => MediaUrl::fromPath($item->image_path),
                        'link_url' => $item->link_url,
                        'button_label' => $item->translate('button_label', $locale),
                        'button_url' => $item->button_url,
                        'text_position' => $item->text_position,
                        'text_color' => $item->text_color,
                        'overlay_opacity' => max(0, min(90, (int) $item->overlay_opacity)),
                    ])
                    ->values()
                    ->all(),
            ])
            ->filter(fn (array $section) => collect($section['items'])->contains(fn ($item) => ! empty($item['image'])))
            ->values()
            ->all();
    }
}
