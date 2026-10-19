<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Filament\Support\FilamentMedia;
use App\Models\Product;
use App\Support\Media\Media;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReviewImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'reviewImages';

    protected static ?string $title = 'Отзывы (изображения)';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            FilamentMedia::image('path', 'product-reviews')
                ->label('Скриншот / фото отзыва')
                ->required()
                ->columnSpanFull(),
            TextInput::make('alt')
                ->label('Alt')
                ->maxLength(255),
            TextInput::make('sort_order')
                ->label('Сорт.')
                ->numeric()
                ->default(0),
            Toggle::make('is_active')
                ->label('Активен')
                ->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('path')
                    ->label('Фото')
                    ->disk(fn ($record) => $record->disk ?? Media::disk()),
                TextColumn::make('alt')
                    ->label('Alt')
                    ->placeholder('—')
                    ->wrap(),
                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Сорт.')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->headerActions([
                CreateAction::make()
                    ->label('Добавить отзыв')
                    ->mutateFormDataUsing(function (array $data): array {
                        if (filled($data['alt'] ?? null)) {
                            return $data;
                        }

                        /** @var Product $product */
                        $product = $this->getOwnerRecord();
                        $name = $product->translate('name') ?? $product->sku;
                        $data['alt'] = 'Review — '.$name;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
