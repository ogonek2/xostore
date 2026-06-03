<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['total'] = (float) ($data['subtotal'] ?? $this->record->subtotal) + (float) ($data['shipping'] ?? 0);

        return $data;
    }
}
