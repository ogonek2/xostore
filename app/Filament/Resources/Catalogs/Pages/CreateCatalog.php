<?php

namespace App\Filament\Resources\Catalogs\Pages;

use App\Filament\Concerns\HandlesTranslations;
use App\Filament\Resources\Catalogs\CatalogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCatalog extends CreateRecord
{
    use HandlesTranslations;

    protected static string $resource = CatalogResource::class;

    protected function getTranslationConfigKey(): string
    {
        return 'catalog';
    }
}
