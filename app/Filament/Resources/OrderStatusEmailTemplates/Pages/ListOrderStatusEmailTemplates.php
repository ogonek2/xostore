<?php

namespace App\Filament\Resources\OrderStatusEmailTemplates\Pages;

use App\Filament\Resources\OrderStatusEmailTemplates\OrderStatusEmailTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrderStatusEmailTemplates extends ListRecords
{
    protected static string $resource = OrderStatusEmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
