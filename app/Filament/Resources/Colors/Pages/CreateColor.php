<?php

namespace App\Filament\Resources\Colors\Pages;

use App\Filament\Concerns\HandlesTranslations;
use App\Filament\Resources\Colors\ColorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateColor extends CreateRecord
{
    use HandlesTranslations;

    protected static string $resource = ColorResource::class;

    protected function getTranslationConfigKey(): string
    {
        return 'color';
    }
}
