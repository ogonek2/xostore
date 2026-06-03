<?php

namespace App\Filament\Resources\OrderStatusEmailTemplates\Pages;

use App\Filament\Resources\OrderStatusEmailTemplates\OrderStatusEmailTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrderStatusEmailTemplate extends CreateRecord
{
    protected static string $resource = OrderStatusEmailTemplateResource::class;
}
