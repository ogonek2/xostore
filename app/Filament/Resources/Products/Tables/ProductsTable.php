<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\ProductStatus;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(fn (Product $record): string => ProductResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('display_name')
                    ->label('Название')
                    ->searchable(query: function ($query, string $search): void {
                        $query->where(function ($q) use ($search) {
                            $q->where('sku', 'like', "%{$search}%")
                                ->orWhereHas('translates', fn ($t) => $t
                                    ->where('field', 'name')
                                    ->where('value', 'like', "%{$search}%"));
                        });
                    })
                    ->getStateUsing(function (Product $record): string {
                        $record->loadMissing('translates');

                        return $record->translate('name', 'pl')
                            ?? $record->translate('name', 'en')
                            ?? $record->sku;
                    })
                    ->description(fn (Product $record): string => $record->sku)
                    ->wrap(),
                TextColumn::make('slug_pl')
                    ->label('Slug (PL)')
                    ->toggleable()
                    ->getStateUsing(fn (Product $record): ?string => $record->translate('slug', 'pl'))
                    ->placeholder('—'),
                TextColumn::make('model_slug')
                    ->label('Модель')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('color_label')
                    ->label('Цвет')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('brand.code')
                    ->label('Бренд')
                    ->placeholder('—'),
                TextColumn::make('primaryCategory.code')
                    ->label('Категория')
                    ->placeholder('—'),
                TextColumn::make('base_price')
                    ->label('Цена')
                    ->money('PLN')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof ProductStatus ? $state->label() : $state)
                    ->color(fn ($state) => match ($state instanceof ProductStatus ? $state->value : $state) {
                        ProductStatus::Published->value => 'success',
                        ProductStatus::Draft->value => 'gray',
                        ProductStatus::Archived->value => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('variants_count')
                    ->label('Размеры')
                    ->counts('variants'),
                IconColumn::make('is_ready_to_ship')
                    ->label('В наличии')
                    ->boolean()
                    ->tooltip('Показывается во вкладке «Товары в наличии» на сайте'),
                TextColumn::make('images_count')
                    ->label('Фото')
                    ->counts('images'),
                TextColumn::make('catalogs_count')
                    ->label('Каталоги')
                    ->counts('catalogs'),
                IconColumn::make('is_featured')
                    ->label('★')
                    ->boolean(),
                IconColumn::make('is_new')
                    ->label('Новинка')
                    ->boolean(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                TernaryFilter::make('is_ready_to_ship')
                    ->label('Товары в наличии'),
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(collect(ProductStatus::cases())->mapWithKeys(
                        fn (ProductStatus $status) => [$status->value => $status->label()]
                    )),
            ]);
    }
}
