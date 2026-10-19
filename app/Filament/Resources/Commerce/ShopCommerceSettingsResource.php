<?php

namespace App\Filament\Resources\Commerce;

use App\Filament\Resources\Commerce\Pages\ManageShopCommerceSettings;
use App\Filament\Resources\Commerce\Schemas\ShopCommerceSettingsForm;
use App\Models\ShopCommerceSettings;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ShopCommerceSettingsResource extends Resource
{
    protected static ?string $model = ShopCommerceSettings::class;

    protected static ?string $navigationLabel = 'Доставка и оплата';

    protected static ?string $modelLabel = 'доставка и оплата';

    protected static ?string $pluralModelLabel = 'Доставка и оплата';

    protected static string|\UnitEnum|null $navigationGroup = 'Настройки';

    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    public static function form(Schema $schema): Schema
    {
        return ShopCommerceSettingsForm::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageShopCommerceSettings::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
