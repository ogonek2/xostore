<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Concerns\HandlesTranslations;
use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    use HandlesTranslations;

    protected static string $resource = ProductResource::class;

    protected function getTranslationConfigKey(): string
    {
        return 'product';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
