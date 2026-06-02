<?php

namespace App\Filament\Resources\HeroBanners\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class HeroBannersTable
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
                    ->searchable(),
                TextColumn::make('layout')
                    ->label('Сетка'),
                TextColumn::make('items_count')
                    ->label('Карточек')
                    ->counts('items'),
                TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                TernaryFilter::make('is_active')->label('Активен'),
            ]);
    }
}
