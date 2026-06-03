<?php

namespace App\Filament\Resources\OrderStatuses\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderStatusesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Код')->searchable()->sortable(),
                TextColumn::make('labels.pl')->label('Название (PL)'),
                TextColumn::make('emailTemplate.name')->label('E-mail шаблон')->placeholder('—'),
                IconColumn::make('is_default')->label('По умолчанию')->boolean(),
                IconColumn::make('is_active')->label('Активен')->boolean(),
                TextColumn::make('orders_count')->label('Заказов')->counts('orders'),
                TextColumn::make('sort_order')->label('Порядок')->sortable(),
            ])
            ->defaultSort('sort_order');
    }
}
