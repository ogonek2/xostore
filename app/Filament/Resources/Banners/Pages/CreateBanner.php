<?php

namespace App\Filament\Resources\Banners\Pages;

use App\Filament\Concerns\HandlesTranslations;
use App\Filament\Resources\Banners\BannerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBanner extends CreateRecord
{
    use HandlesTranslations;

    protected static string $resource = BannerResource::class;

    protected function getTranslationConfigKey(): string
    {
        return 'banner';
    }
}
