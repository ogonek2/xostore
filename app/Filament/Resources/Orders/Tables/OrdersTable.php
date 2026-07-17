<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\OrderStatus;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->label('Номер')->searchable()->sortable()->copyable(),
                TextColumn::make('orderStatus.labels.pl')
                    ->label('Статус')
                    ->badge()
                    ->color(fn ($record) => $record->orderStatus?->color ?: 'gray'),
                TextColumn::make('paymentMethod.labels.pl')->label('Оплата')->placeholder('—'),
                TextColumn::make('latestPayment.status')
                    ->label('Статус платежа')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->value ?? '—')
                    ->placeholder('—'),
                TextColumn::make('customer_name')->label('Клиент')->searchable(),
                TextColumn::make('email')->label('E-mail')->searchable(),
                TextColumn::make('total')->label('Сумма')->money('PLN')->sortable(),
                TextColumn::make('placed_at')->label('Дата')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->defaultSort('placed_at', 'desc')
            ->filters([
                SelectFilter::make('order_status_id')
                    ->label('Статус')
                    ->relationship('orderStatus', 'code')
                    ->getOptionLabelFromRecordUsing(
                        fn (OrderStatus $record) => $record->labels['pl'] ?? $record->code
                    ),
            ]);
    }
}
