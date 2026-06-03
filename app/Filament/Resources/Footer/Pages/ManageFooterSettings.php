<?php

namespace App\Filament\Resources\Footer\Pages;

use App\Filament\Resources\Footer\FooterSettingsResource;
use App\Models\FooterSettings;
use Filament\Resources\Pages\EditRecord;

class ManageFooterSettings extends EditRecord
{
    protected static string $resource = FooterSettingsResource::class;

    protected static ?string $title = 'Настройки футера';

    public function mount(int|string|null $record = null): void
    {
        parent::mount(FooterSettings::instance()->getKey());
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
