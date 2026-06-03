<?php

namespace App\Filament\Resources\OrderStatuses\Pages;

use App\Filament\Resources\OrderStatuses\OrderStatusResource;
use App\Models\OrderStatus;
use Filament\Resources\Pages\CreateRecord;

class CreateOrderStatus extends CreateRecord
{
    protected static string $resource = OrderStatusResource::class;

    protected function afterCreate(): void
    {
        if ($this->record->is_default) {
            OrderStatus::query()
                ->whereKeyNot($this->record->id)
                ->update(['is_default' => false]);
        }
    }
}
