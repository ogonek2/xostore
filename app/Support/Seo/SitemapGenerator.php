<?php

namespace App\Support\Seo;

use App\Models\Catalog;
use App\Models\Category;
use App\Models\Language;
use App\Models\Product;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

final class SitemapGenerator
{
    /**
     * @return list<array{loc: string, lastmod: ?string, changefreq: string, priority: string}>
     */
    public function entries(): array
    {
        $entries = [];
        $locales = Language::query()->where('is_active', true)->orderBy('sort_order')->pluck('code');

        foreach ($locales as $locale) {
            $entries[] = $this->entry(
                route('home', ['locale' => $locale]),
                null,
                'daily',
                '1.0',
            );
            $entries[] = $this->entry(
                route('products.index', ['locale' => $locale]),
                null,
                'daily',
                '0.9',
            );
        }

        foreach ($locales as $locale) {
            Category::query()
                ->where('is_active', true)
                ->with('translates')
                ->orderBy('id')
                ->chunk(100, function ($categories) use (&$entries, $locale) {
                    foreach ($categories as $category) {
                        $slug = $category->translate('slug', $locale);

                        if (! $slug) {
                            continue;
                        }

                        $entries[] = $this->entry(
                            route('category.show', ['locale' => $locale, 'category' => $slug]),
                            $category->updated_at,
                            'weekly',
                            '0.8',
                        );
                    }
                });

            Catalog::query()
                ->where('is_active', true)
                ->with('translates')
                ->orderBy('id')
                ->chunk(50, function ($catalogs) use (&$entries, $locale) {
                    foreach ($catalogs as $catalog) {
                        $slug = $catalog->translate('slug', $locale);

                        if (! $slug) {
                            continue;
                        }

                        $entries[] = $this->entry(
                            route('catalog.show', ['locale' => $locale, 'catalog' => $slug]),
                            $catalog->updated_at,
                            'weekly',
                            '0.7',
                        );
                    }
                });

            Product::query()
                ->published()
                ->with('translates')
                ->orderBy('id')
                ->chunk(100, function ($products) use (&$entries, $locale) {
                    foreach ($products as $product) {
                        $slug = $product->translate('slug', $locale) ?? $product->sku;

                        $entries[] = $this->entry(
                            route('product.show', ['locale' => $locale, 'product' => $slug]),
                            $product->updated_at,
                            'weekly',
                            '0.6',
                        );
                    }
                });
        }

        return $this->uniqueByLoc($entries);
    }

    public function toXml(): string
    {
        $entries = $this->entries();
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($entries as $entry) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.htmlspecialchars($entry['loc'], ENT_XML1)."</loc>\n";

            if ($entry['lastmod']) {
                $xml .= '    <lastmod>'.$entry['lastmod']."</lastmod>\n";
            }

            $xml .= '    <changefreq>'.$entry['changefreq']."</changefreq>\n";
            $xml .= '    <priority>'.$entry['priority']."</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * @param  list<array{loc: string, lastmod: ?string, changefreq: string, priority: string}>  $entries
     * @return list<array{loc: string, lastmod: ?string, changefreq: string, priority: string}>
     */
    protected function uniqueByLoc(array $entries): array
    {
        return Collection::make($entries)
            ->unique('loc')
            ->values()
            ->all();
    }

    /**
     * @return array{loc: string, lastmod: ?string, changefreq: string, priority: string}
     */
    protected function entry(string $loc, ?CarbonInterface $lastmod, string $changefreq, string $priority): array
    {
        return [
            'loc' => $loc,
            'lastmod' => $lastmod?->toAtomString(),
            'changefreq' => $changefreq,
            'priority' => $priority,
        ];
    }
}
