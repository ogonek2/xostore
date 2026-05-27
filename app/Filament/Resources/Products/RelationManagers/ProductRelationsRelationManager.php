<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Enums\ProductRelationType;
use App\Filament\Support\ProductAdminOptions;
use App\Models\Product;
use App\Models\ProductRelation;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductRelationsRelationManager extends RelationManager
{
    protected static string $relationship = 'productRelations';

    protected static ?string $title = 'Связанные товары';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('related_product_id')
                ->label('Товар')
                ->required()
                ->searchable()
                ->options(fn () => ProductAdminOptions::productPicker($this->getOwnerRecord()->id)),
            Select::make('type')
                ->label('Тип связи')
                ->options(collect(ProductRelationType::cases())->mapWithKeys(
                    fn (ProductRelationType $type) => [$type->value => $type->label()]
                ))
                ->required()
                ->native(false),
            TextInput::make('sort_order')
                ->label('Сортировка')
                ->numeric()
                ->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('relatedProduct.sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('related_name')
                    ->label('Товар')
                    ->state(fn (ProductRelation $record) => $record->relatedProduct?->translate('name', 'pl') ?? '—'),
                TextColumn::make('type')
                    ->label('Тип')
                    ->formatStateUsing(fn ($state) => $state instanceof ProductRelationType ? $state->label() : $state),
                TextColumn::make('sort_order')
                    ->label('Сорт.'),
            ])
            ->defaultSort('sort_order')
            ->headerActions([
                CreateAction::make()
                    ->label('Новая связь'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('Нет связанных товаров')
            ->emptyStateDescription('Добавьте альтернативы, другие цвета или похожие модели.');
    }
}
