<?php

namespace App\Filament\Resources\Catalogs\Tables;

use App\Enums\CatalogType;
use App\Filament\Support\AdminTableColumns;
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
                    ->label('Код')
                    ->searchable()
                    ->sortable(),
                AdminTableColumns::plTranslation(),
                TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof CatalogType ? $state->label() : $state),
                TextColumn::make('categories_count')
                    ->label('Категории')
                    ->counts('categories'),
                TextColumn::make('products_count')
                    ->label('Товары')
                    ->counts('products'),
                TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),
                IconColumn::make('show_on_homepage')
                    ->label('На главной')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                TernaryFilter::make('is_active')->label('Активен'),
                SelectFilter::make('type')
                    ->label('Тип')
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
