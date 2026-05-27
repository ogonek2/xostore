<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SizeChartRowsRelationManager extends RelationManager
{
    protected static string $relationship = 'sizeChartRows';

    protected static ?string $title = 'Размерная сетка';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('size')
                ->label('Размер')
                ->maxLength(32),
            TextInput::make('chest')
                ->label('Грудь')
                ->maxLength(32),
            TextInput::make('waist')
                ->label('Талия')
                ->maxLength(32),
            TextInput::make('hips')
                ->label('Бёдра')
                ->maxLength(32),
            TextInput::make('inseam')
                ->label('Шов')
                ->maxLength(32),
            TextInput::make('sort_order')
                ->label('Сорт.')
                ->numeric()
                ->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('size')->label('Размер'),
                TextColumn::make('chest')->label('Грудь'),
                TextColumn::make('waist')->label('Талия'),
                TextColumn::make('hips')->label('Бёдра'),
                TextColumn::make('inseam')->label('Шов'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->headerActions([
                CreateAction::make()
                    ->label('Новая строка'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('Нет строк размерной сетки')
            ->emptyStateDescription('Добавьте мерки для таблицы размеров на странице товара.');
    }
}
