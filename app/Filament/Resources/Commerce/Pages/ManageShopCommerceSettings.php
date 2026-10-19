<?php

namespace App\Filament\Resources\Commerce\Pages;

use App\Filament\Resources\Commerce\ShopCommerceSettingsResource;
use App\Models\ShopCommerceSettings;
use App\Support\Shop\ShopCommerceInfo;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class ManageShopCommerceSettings extends EditRecord
{
    protected static string $resource = ShopCommerceSettingsResource::class;

    protected static ?string $title = 'Доставка и оплата';

    public function mount(int|string|null $record = null): void
    {
        parent::mount(ShopCommerceSettings::instance()->getKey());
    }

    protected function afterSave(): void
    {
        ShopCommerceInfo::forgetCache();

        Notification::make()
            ->title('Настройки сохранены')
            ->success()
            ->send();
    }
}
