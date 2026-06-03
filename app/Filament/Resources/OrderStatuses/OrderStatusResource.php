<?php

namespace App\Filament\Resources\OrderStatuses;

use App\Filament\Resources\OrderStatuses\Pages\CreateOrderStatus;
use App\Filament\Resources\OrderStatuses\Pages\EditOrderStatus;
use App\Filament\Resources\OrderStatuses\Pages\ListOrderStatuses;
use App\Filament\Resources\OrderStatuses\Schemas\OrderStatusForm;
use App\Filament\Resources\OrderStatuses\Tables\OrderStatusesTable;
use App\Models\OrderStatus;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OrderStatusResource extends Resource
{
    protected static ?string $model = OrderStatus::class;

    protected static ?string $navigationLabel = 'Статусы заказов';

    protected static ?string $modelLabel = 'статус';

    protected static ?string $pluralModelLabel = 'Статусы заказов';

    protected static string|\UnitEnum|null $navigationGroup = 'Продажи';

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    public static function form(Schema $schema): Schema
    {
        return OrderStatusForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrderStatusesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrderStatuses::route('/'),
            'create' => CreateOrderStatus::route('/create'),
            'edit' => EditOrderStatus::route('/{record}/edit'),
        ];
    }
}
