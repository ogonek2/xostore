<?php

namespace App\Support\Seo;

use Illuminate\Database\Eloquent\Model;

final class SeoBuilder
{
    public static function defaults(?string $locale = null): PageSeo
    {
        $locale ??= app()->getLocale();

        return new PageSeo(
            title: (string) (config('shop.seo.default_title') ?: config('shop.name')),
            description: (string) (config('shop.seo.default_description') ?: __('seo.default_description', locale: $locale)),
            canonical: route('home', ['locale' => $locale]),
        );
    }

    public static function forHome(string $locale): PageSeo
    {
        return new PageSeo(
            title: (string) (config('shop.seo.default_title') ?: config('shop.name')),
            description: (string) (config('shop.seo.default_description') ?: __('seo.home_description', locale: $locale)),
            canonical: route('home', ['locale' => $locale]),
        );
    }

    /**
     * @param  array<string, mixed>  $product
     */
    public static function forProduct(array $product, string $locale): PageSeo
    {
        $title = $product['meta_title'] ?? $product['display_name'] ?? $product['name'] ?? config('shop.name');
        $description = $product['meta_description']
            ?? $product['short_description']
            ?? $product['description']
            ?? null;

        return new PageSeo(
            title: (string) $title,
            description: static::truncate((string) $description),
            canonical: route('product.show', [
                'locale' => $locale,
                'product' => $product['slug'],
            ]),
            ogImage: $product['images'][0]['url'] ?? null,
            ogType: 'product',
        );
    }

    public static function forListing(
        string $title,
        string $canonical,
        ?string $metaTitle = null,
        ?string $metaDescription = null,
        ?string $fallbackDescription = null,
    ): PageSeo {
        $description = $metaDescription ?? $fallbackDescription;

        return new PageSeo(
            title: $metaTitle ?: $title,
            description: static::truncate($description),
            canonical: $canonical,
        );
    }

    public static function fromTranslatable(
        Model $model,
        string $locale,
        string $fallbackTitle,
        string $canonical,
        ?string $fallbackDescription = null,
    ): PageSeo {
        return static::forListing(
            title: $fallbackTitle,
            canonical: $canonical,
            metaTitle: $model->translate('meta_title', $locale),
            metaDescription: $model->translate('meta_description', $locale),
            fallbackDescription: $fallbackDescription ?? $model->translate('description', $locale),
        );
    }

    public static function privatePage(string $title, ?string $description = null): PageSeo
    {
        return new PageSeo(
            title: $title,
            description: static::truncate($description),
            canonical: url()->current(),
            robotsIndex: false,
        );
    }

    public static function truncate(?string $text, int $max = 160): ?string
    {
        if ($text === null || $text === '') {
            return null;
        }

        $text = preg_replace('/\s+/u', ' ', trim(strip_tags($text))) ?? '';

        if ($text === '') {
            return null;
        }

        if (mb_strlen($text) <= $max) {
            return $text;
        }

        return mb_substr($text, 0, $max - 1).'…';
    }
}
