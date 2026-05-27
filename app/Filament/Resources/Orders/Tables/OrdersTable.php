<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->label('Номер')->searchable()->sortable(),
                TextColumn::make('status')->label('Статус')->badge()->formatStateUsing(fn ($s) => $s instanceof OrderStatus ? $s->label() : $s),
                TextColumn::make('email')->label('E-mail')->searchable(),
                TextColumn::make('total')->label('Сумма')->money('PLN')->sortable(),
                TextColumn::make('placed_at')->label('Дата')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->defaultSort('placed_at', 'desc')
            ->filters([
                SelectFilter::make('status')->label('Статус')->options(collect(OrderStatus::cases())->mapWithKeys(
                    fn (OrderStatus $s) => [$s->value => $s->label()]
                )),
            ]);
    }
}
