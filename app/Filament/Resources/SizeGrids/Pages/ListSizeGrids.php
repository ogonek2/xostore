<?php

namespace App\Filament\Resources\SizeGrids\Pages;

use App\Filament\Resources\SizeGrids\SizeGridResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSizeGrids extends ListRecords
{
    protected static string $resource = SizeGridResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
