<?php

namespace App\Filament\Resources\NewsletterGroups\Pages;

use App\Filament\Resources\NewsletterGroups\NewsletterGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNewsletterGroups extends ListRecords
{
    protected static string $resource = NewsletterGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
