<?php

namespace App\Filament\Resources\OrderStatuses\Pages;

use App\Filament\Resources\OrderStatuses\OrderStatusResource;
use App\Models\OrderStatus;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrderStatus extends EditRecord
{
    protected static string $resource = OrderStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function afterSave(): void
    {
        if ($this->record->is_default) {
            OrderStatus::query()
                ->whereKeyNot($this->record->id)
                ->update(['is_default' => false]);
        }
    }
}
