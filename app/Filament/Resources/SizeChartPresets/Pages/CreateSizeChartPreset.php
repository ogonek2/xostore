<?php

namespace App\Filament\Resources\SizeChartPresets\Pages;

use App\Filament\Concerns\HandlesTranslations;
use App\Filament\Resources\SizeChartPresets\SizeChartPresetResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSizeChartPreset extends CreateRecord
{
    use HandlesTranslations;

    protected static string $resource = SizeChartPresetResource::class;

    protected function getTranslationConfigKey(): string
    {
        return 'size_chart_preset';
    }
}
