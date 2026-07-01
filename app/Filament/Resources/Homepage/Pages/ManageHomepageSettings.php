<?php

namespace App\Filament\Resources\Homepage\Pages;

use App\Filament\Resources\Homepage\HomepageSettingsResource;
use App\Models\HomepageSettings;
use Filament\Resources\Pages\EditRecord;

class ManageHomepageSettings extends EditRecord
{
    protected static string $resource = HomepageSettingsResource::class;

    protected static ?string $title = 'Главная страница';

    public function mount(int|string|null $record = null): void
    {
        parent::mount(HomepageSettings::instance()->getKey());
    }
}
