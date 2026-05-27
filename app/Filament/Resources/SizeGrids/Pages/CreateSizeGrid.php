<?php

namespace App\Filament\Resources\SizeGrids\Pages;

use App\Filament\Concerns\HandlesTranslations;
use App\Filament\Resources\SizeGrids\SizeGridResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSizeGrid extends CreateRecord
{
    use HandlesTranslations;

    protected static string $resource = SizeGridResource::class;

    protected function getTranslationConfigKey(): string
    {
        return 'size_grid';
    }
}
