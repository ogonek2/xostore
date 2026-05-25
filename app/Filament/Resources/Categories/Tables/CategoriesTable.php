<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kod')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('display_name')
                    ->label('Nazwa (PL)')
                    ->getStateUsing(fn ($record) => $record->translate('name', 'pl'))
                    ->searchable(false),
                TextColumn::make('parent.code')
                    ->label('Rodzic')
                    ->placeholder('—'),
                TextColumn::make('type')
                    ->label('Typ')
                    ->badge(),
                TextColumn::make('products_count')
                    ->label('Produkty')
                    ->counts('products')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Kolejność')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktywna')
                    ->boolean(),
                IconColumn::make('show_in_menu')
                    ->label('Menu')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                TernaryFilter::make('is_active')->label('Aktywna'),
                SelectFilter::make('type')
                    ->label('Typ')
                    ->options(config('shop.category.types', [])),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
