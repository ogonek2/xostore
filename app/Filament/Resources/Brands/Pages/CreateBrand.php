<?php

namespace App\Filament\Resources\Brands\Pages;

use App\Filament\Concerns\HandlesTranslations;
use App\Filament\Resources\Brands\BrandResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBrand extends CreateRecord
{
    use HandlesTranslations;

    protected static string $resource = BrandResource::class;

    protected function getTranslationConfigKey(): string
    {
        return 'brand';
    }
}
