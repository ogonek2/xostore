<?php

namespace App\Filament\Resources\NewsletterGroups\Pages;

use App\Filament\Resources\NewsletterGroups\NewsletterGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNewsletterGroup extends EditRecord
{
    protected static string $resource = NewsletterGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
