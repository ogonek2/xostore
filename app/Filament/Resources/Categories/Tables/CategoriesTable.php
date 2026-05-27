<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Enums\CategoryType;
use App\Filament\Support\AdminTableColumns;
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
                    ->label('Код')
                    ->searchable()
                    ->sortable(),
                AdminTableColumns::plTranslation()->searchable(),
                TextColumn::make('parent.code')
                    ->label('Родитель')
                    ->placeholder('—'),
                TextColumn::make('type')
                    ->label('Тип')
                    ->formatStateUsing(fn ($state) => $state instanceof CategoryType ? $state->label() : $state),
                TextColumn::make('products_count')
                    ->label('Товары')
                    ->counts('products'),
                TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Активна')
                    ->boolean(),
                IconColumn::make('show_in_menu')
                    ->label('Меню')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                TernaryFilter::make('is_active')->label('Активна'),
                SelectFilter::make('type')
                    ->label('Тип')
                    ->options(collect(CategoryType::cases())->mapWithKeys(
                        fn (CategoryType $type) => [$type->value => $type->label()]
                    )->all()),
            ]);
    }
}
