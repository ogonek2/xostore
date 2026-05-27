<?php

namespace App\Filament\Resources\Products\Pages;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Facades\FilamentView;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    public function mount(): void
    {
        $this->authorizeAccess();

        $product = Product::query()->create([
            'sku' => ProductResource::generateDraftSku(),
            'status' => ProductStatus::Draft->value,
            'type' => ProductType::Variable->value,
            'sort_order' => 0,
        ]);

        $url = ProductResource::getUrl('edit', ['record' => $product]);

        $this->redirect($url, navigate: FilamentView::hasSpaMode($url));
    }
}
