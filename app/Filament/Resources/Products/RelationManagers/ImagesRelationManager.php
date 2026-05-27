<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $title = 'Галерея';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            FileUpload::make('path')
                ->label('Фото')
                ->image()
                ->directory('products')
                ->required()
                ->columnSpanFull(),
            TextInput::make('alt')
                ->label('Alt')
                ->maxLength(255),
            TextInput::make('sort_order')
                ->label('Сорт.')
                ->numeric()
                ->default(0),
            Toggle::make('is_primary')
                ->label('Главное'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('path')
                    ->label('Фото')
                    ->disk('public'),
                TextColumn::make('alt')
                    ->label('Alt')
                    ->placeholder('—'),
                IconColumn::make('is_primary')
                    ->label('Главное')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Сорт.')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->headerActions([
                CreateAction::make()
                    ->label('Добавить фото'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('Нет фотографий')
            ->emptyStateDescription('Добавьте фото в галерею товара.');
    }
}
