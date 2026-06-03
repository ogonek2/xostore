<?php

namespace App\Filament\Resources\SizeChartPresets\Pages;

use App\Filament\Concerns\HandlesTranslations;
use App\Filament\Resources\SizeChartPresets\SizeChartPresetResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSizeChartPreset extends EditRecord
{
    use HandlesTranslations;

    protected static string $resource = SizeChartPresetResource::class;

    protected function getTranslationConfigKey(): string
    {
        return 'size_chart_preset';
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
