<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\ProductStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('display_name')
                    ->label('Nazwa (PL)')
                    ->getStateUsing(fn ($record) => $record->translate('name', 'pl'))
                    ->limit(40),
                TextColumn::make('brand.code')
                    ->label('Marka')
                    ->placeholder('—'),
                TextColumn::make('primaryCategory.code')
                    ->label('Kategoria')
                    ->placeholder('—'),
                TextColumn::make('base_price')
                    ->label('Cena')
                    ->money('PLN')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        ProductStatus::Published->value => 'success',
                        ProductStatus::Draft->value => 'gray',
                        ProductStatus::Archived->value => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('variants_count')
                    ->label('Warianty')
                    ->counts('variants'),
                TextColumn::make('catalogs_count')
                    ->label('Katalogi')
                    ->counts('catalogs'),
                IconColumn::make('is_featured')
                    ->label('★')
                    ->boolean(),
                IconColumn::make('is_new')
                    ->label('Nowość')
                    ->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('status')
                    ->options(collect(ProductStatus::cases())->mapWithKeys(
                        fn (ProductStatus $status) => [$status->value => ucfirst($status->value)]
                    )),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
