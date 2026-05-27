<?php

namespace App\Filament\Resources\Products\Concerns;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Str;

trait ManagesProductRecord
{
    protected ?string $pendingPrimaryImage = null;

    protected function persistProductTranslations(Product $product): void
    {
        if (empty($this->pendingTranslations)) {
            return;
        }

        $this->saveTranslations($product, $this->pendingTranslations);
    }

    protected function normalizeProductSlugs(array $data): array
    {
        $defaultLocale = (string) config('shop.default_language', 'pl');
        $defaultName = $data["trans_{$defaultLocale}_name"] ?? null;

        if (
            (! isset($data['model_slug']) || trim((string) $data['model_slug']) === '') &&
            is_string($defaultName) &&
            trim($defaultName) !== ''
        ) {
            $data['model_slug'] = Str::slug($defaultName);
        }

        if (
            (! isset($data['color_slug']) || trim((string) $data['color_slug']) === '') &&
            isset($data['color_label']) &&
            is_string($data['color_label']) &&
            trim($data['color_label']) !== ''
        ) {
            $data['color_slug'] = Str::slug($data['color_label']);
        }

        return $data;
    }

    protected function syncPrimaryImage(Product $product): void
    {
        if ($this->pendingPrimaryImage === null || $this->pendingPrimaryImage === '') {
            return;
        }

        $product->images()->update(['is_primary' => false]);

        /** @var ProductImage|null $existing */
        $existing = $product->images()->where('path', $this->pendingPrimaryImage)->first();

        if ($existing) {
            $existing->update(['is_primary' => true]);

            return;
        }

        $product->images()->create([
            'path' => $this->pendingPrimaryImage,
            'is_primary' => true,
            'sort_order' => 0,
        ]);
    }

    protected function isDraftProduct(Product $product): bool
    {
        return str_starts_with($product->sku, 'DRAFT-');
    }

    public static function generateDraftSku(): string
    {
        return 'DRAFT-'.now()->format('YmdHis');
    }
}
