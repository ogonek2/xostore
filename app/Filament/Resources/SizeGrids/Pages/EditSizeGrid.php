<?php

namespace App\Filament\Resources\SizeGrids\Pages;

use App\Filament\Concerns\HandlesTranslations;
use App\Filament\Resources\SizeGrids\SizeGridResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSizeGrid extends EditRecord
{
    use HandlesTranslations;

    protected static string $resource = SizeGridResource::class;

    protected function getTranslationConfigKey(): string
    {
        return 'size_grid';
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
