<?php

namespace App\Filament\Resources\LandingPages\Pages;

use App\Filament\Concerns\HandlesTranslations;
use App\Filament\Resources\LandingPages\LandingPageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLandingPage extends CreateRecord
{
    use HandlesTranslations;

    protected static string $resource = LandingPageResource::class;

    protected function getTranslationConfigKey(): string
    {
        return 'landing_page';
    }

    protected function getRedirectUrl(): string
    {
        return LandingPageResource::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
