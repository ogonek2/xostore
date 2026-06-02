<?php

namespace App\Filament\Resources\NavMenus\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NavMenusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label('Код')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('items_count')
                    ->label('Пунктов')
                    ->counts('allItems'),
                IconColumn::make('is_active')
                    ->label('Активно')
                    ->boolean(),
            ])
            ->defaultSort('name');
    }
}
