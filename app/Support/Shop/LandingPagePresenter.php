<?php

namespace App\Support\Shop;

use App\Enums\LandingPageBlockType;
use App\Models\Catalog;
use App\Models\Category;
use App\Models\LandingPage;
use App\Models\LandingPageBlock;
use App\Models\Product;
use App\Support\Media\MediaUrl;
use Illuminate\Support\Collection;

final class LandingPagePresenter
{
    public static function resolveBySlug(string $slug, string $locale): ?LandingPage
    {
        return LandingPage::query()
            ->published()
            ->with(['translates', 'activeBlocks.translates'])
            ->whereHas('translates', function ($query) use ($slug, $locale): void {
                $query->where('field', 'slug')->where('value', $slug);
            })
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    public static function page(LandingPage $page, string $locale): array
    {
        return [
            'id' => $page->id,
            'code' => $page->code,
            'name' => $page->translate('name', $locale) ?? $page->code,
            'slug' => $page->translate('slug', $locale) ?? $page->code,
            'show_header' => $page->show_header,
            'show_footer' => $page->show_footer,
            'blocks' => static::blocks($page, $locale),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function blocks(LandingPage $page, string $locale): array
    {
        return $page->activeBlocks
            ->map(fn (LandingPageBlock $block) => static::block($block, $locale))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function block(LandingPageBlock $block, string $locale): array
    {
        $type = $block->typeEnum();
        $settings = $block->settings ?? [];

        $data = [
            'id' => $block->id,
            'type' => $type->value,
            'title' => $block->translate('title', $locale),
            'subtitle' => $block->translate('subtitle', $locale),
            'content_html' => LandingRichTextRenderer::render($block->translate('content', $locale)),
            'button_label' => $block->translate('button_label', $locale),
            'link_url' => static::resolveUrl($block->translate('link_url', $locale), $locale),
            'caption' => $block->translate('caption', $locale),
            'settings' => static::normalizeSettings($settings, $locale),
            'items' => static::localizedItems($settings['items'] ?? [], $locale, $type),
        ];

        if ($type === LandingPageBlockType::Products) {
            $data['products'] = static::productsForBlock($settings, $locale);
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    protected static function normalizeSettings(array $settings, string $locale): array
    {
        $normalized = $settings;

        $imagePath = static::resolveImagePath($settings['image_path'] ?? null);

        if ($imagePath !== null) {
            $normalized['image_url'] = MediaUrl::fromPath($imagePath);
        }

        return $normalized;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    protected static function localizedItems(array $items, string $locale, LandingPageBlockType $type): array
    {
        return collect($items)
            ->map(function (array $item) use ($locale, $type): array {
                $row = [
                    'title' => $item["title_{$locale}"] ?? $item['title_pl'] ?? $item['title_en'] ?? null,
                    'subtitle' => $item["subtitle_{$locale}"] ?? $item['subtitle_pl'] ?? null,
                    'content_html' => LandingRichTextRenderer::render(
                        $item["content_{$locale}"] ?? $item['content_pl'] ?? null,
                    ),
                    'caption' => $item["caption_{$locale}"] ?? $item['caption_pl'] ?? null,
                    'icon' => $item['icon'] ?? null,
                    'link_url' => static::resolveUrl($item['link_url'] ?? null, $locale),
                ];

                $itemImagePath = static::resolveImagePath($item['image_path'] ?? null);

                if ($itemImagePath !== null) {
                    $row['image_url'] = MediaUrl::fromPath($itemImagePath);
                }

                return $row;
            })
            ->filter(fn (array $row) => collect($row)->filter(fn ($v) => filled($v) && $v !== '')->isNotEmpty())
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return list<array<string, mixed>>
     */
    protected static function productsForBlock(array $settings, string $locale): array
    {
        $limit = max(1, min(24, (int) ($settings['limit'] ?? 8)));
        $source = (string) ($settings['source'] ?? 'trending');

        $products = match ($source) {
            'new_arrivals' => collect(NewArrivalsProducts::forHomepage($locale, $limit)['products'] ?? []),
            'catalog' => static::productsFromCatalog((string) ($settings['catalog_code'] ?? ''), $locale, $limit),
            'category' => static::productsFromCategory((string) ($settings['category_code'] ?? ''), $locale, $limit),
            default => TrendingProducts::forHomepage($locale, $limit),
        };

        if ($products->isEmpty()) {
            return [];
        }

        if (! $products->first() instanceof Product) {
            return $products->values()->all();
        }

        return $products
            ->map(fn (Product $product) => ProductCardPresenter::fromProduct($product, $locale, compact: true))
            ->values()
            ->all();
    }

    protected static function productsFromCatalog(string $code, string $locale, int $limit): Collection
    {
        if ($code === '') {
            return collect();
        }

        $catalog = Catalog::query()->where('code', $code)->where('is_active', true)->first();

        if (! $catalog) {
            return collect();
        }

        $catalog->load(['categories', 'products']);

        $query = Product::query()->published()->with(static::productEagerLoads());

        $categoryIds = $catalog->categories->pluck('id');
        $manualIds = $catalog->products->pluck('id');

        if ($manualIds->isNotEmpty() || $categoryIds->isNotEmpty()) {
            $query->where(function ($q) use ($categoryIds, $manualIds): void {
                if ($manualIds->isNotEmpty()) {
                    $q->whereIn('products.id', $manualIds);
                }
                if ($categoryIds->isNotEmpty()) {
                    $q->orWhereIn('primary_category_id', $categoryIds);
                }
            });
        }

        return $query->limit($limit)->get();
    }

    protected static function productsFromCategory(string $code, string $locale, int $limit): Collection
    {
        if ($code === '') {
            return collect();
        }

        $category = Category::query()->where('code', $code)->where('is_active', true)->first();

        if (! $category) {
            return collect();
        }

        $ids = Category::idsIncludingDescendants($category->id);

        return Product::query()
            ->published()
            ->with(static::productEagerLoads())
            ->whereIn('primary_category_id', $ids)
            ->limit($limit)
            ->get();
    }

    protected static function resolveImagePath(mixed $path): ?string
    {
        if (! is_string($path)) {
            return null;
        }

        $path = trim($path);

        return $path === '' ? null : $path;
    }

    protected static function resolveUrl(?string $url, string $locale): ?string
    {
        if (! filled($url)) {
            return null;
        }

        $url = trim($url);

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '//')) {
            return $url;
        }

        $path = ltrim($url, '/');

        if (str_starts_with($path, $locale.'/')) {
            return url('/'.$path);
        }

        return url('/'.$locale.'/'.$path);
    }

    /**
     * @return list<string>
     */
    protected static function productEagerLoads(): array
    {
        return [
            'brand.translates',
            'primaryCategory.translates',
            'images',
            'variants.attributeValues',
        ];
    }
}
