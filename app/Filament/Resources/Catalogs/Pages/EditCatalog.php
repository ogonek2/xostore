<?php

namespace App\Filament\Resources\Catalogs\Pages;

use App\Filament\Concerns\HandlesTranslations;
use App\Filament\Resources\Catalogs\CatalogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCatalog extends EditRecord
{
    use HandlesTranslations;

    protected static string $resource = CatalogResource::class;

    protected function getTranslationConfigKey(): string
    {
        return 'catalog';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
