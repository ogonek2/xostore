<?php

namespace App\Filament\Resources\HeroBanners\Pages;

use App\Filament\Resources\HeroBanners\HeroBannerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHeroBanner extends EditRecord
{
    protected static string $resource = HeroBannerResource::class;

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabLabel(): ?string
    {
        return 'Секция';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
