<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Concerns\HandlesTranslations;
use App\Filament\Resources\Categories\CategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    use HandlesTranslations;

    protected static string $resource = CategoryResource::class;

    protected function getTranslationConfigKey(): string
    {
        return 'category';
    }
}
