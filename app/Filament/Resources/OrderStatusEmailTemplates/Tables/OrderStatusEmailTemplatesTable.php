<?php

namespace App\Filament\Resources\OrderStatusEmailTemplates\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderStatusEmailTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Название')->searchable(),
                TextColumn::make('orderStatus.labels.pl')
                    ->label('Статус')
                    ->description(fn ($record) => $record->orderStatus?->code),
                TextColumn::make('subject')->label('Тема')->limit(50),
                TextColumn::make('message')
                    ->label('Текст')
                    ->limit(60)
                    ->wrap(),
                IconColumn::make('is_active')->label('Активен')->boolean(),
            ])
            ->defaultSort('name');
    }
}
