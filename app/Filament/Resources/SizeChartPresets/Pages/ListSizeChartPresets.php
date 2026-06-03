<?php

namespace App\Filament\Resources\SizeChartPresets\Pages;

use App\Filament\Resources\SizeChartPresets\SizeChartPresetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSizeChartPresets extends ListRecords
{
    protected static string $resource = SizeChartPresetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
