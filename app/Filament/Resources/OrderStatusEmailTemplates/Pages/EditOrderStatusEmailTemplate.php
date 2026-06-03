<?php

namespace App\Filament\Resources\OrderStatusEmailTemplates\Pages;

use App\Filament\Resources\OrderStatusEmailTemplates\OrderStatusEmailTemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrderStatusEmailTemplate extends EditRecord
{
    protected static string $resource = OrderStatusEmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
