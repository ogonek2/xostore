<?php

namespace App\Filament\Resources\Catalogs\Tables;

use App\Enums\CatalogType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CatalogsTable
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
                    ->getStateUsing(fn ($record) => $record->translate('name', 'pl')),
                TextColumn::make('type')
                    ->label('Typ')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof CatalogType ? $state->label() : $state),
                TextColumn::make('categories_count')
                    ->label('Kategorie')
                    ->counts('categories'),
                TextColumn::make('products_count')
                    ->label('Produkty')
                    ->counts('products'),
                TextColumn::make('sort_order')
                    ->label('Kolejność')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktywny')
                    ->boolean(),
                IconColumn::make('show_on_homepage')
                    ->label('Home')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                TernaryFilter::make('is_active')->label('Aktywny'),
                SelectFilter::make('type')
                    ->label('Typ')
                    ->options(collect(CatalogType::cases())->mapWithKeys(
                        fn (CatalogType $type) => [$type->value => $type->label()]
                    )),
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
