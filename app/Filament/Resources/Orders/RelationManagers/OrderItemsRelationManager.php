<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Товары в заказе';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product_name')
                    ->label('Товар')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('variant_label')
                    ->label('Вариант')
                    ->placeholder('—'),
                TextColumn::make('variant_sku')
                    ->label('SKU')
                    ->copyable(),
                TextColumn::make('quantity')
                    ->label('Кол-во')
                    ->alignCenter(),
                TextColumn::make('unit_price')
                    ->label('Цена')
                    ->money('PLN'),
                TextColumn::make('total_price')
                    ->label('Сумма')
                    ->money('PLN'),
            ])
            ->defaultSort('id')
            ->paginated(false);
    }
}
