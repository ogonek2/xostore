<?php

namespace App\Filament\Resources\Promotions\Tables;

use App\Enums\PromotionLayout;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PromotionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kod')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('display_title')
                    ->label('Tytuł (PL)')
                    ->getStateUsing(fn ($record) => $record->translate('title', 'pl')),
                TextColumn::make('layout')
                    ->label('Układ')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof PromotionLayout ? $state->label() : $state),
                TextColumn::make('discount_percent')
                    ->label('Rabat')
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->label('Koniec')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Kolejność')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktywna')
                    ->boolean(),
                IconColumn::make('show_on_homepage')
                    ->label('Home')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                TernaryFilter::make('is_active')->label('Aktywna'),
                SelectFilter::make('layout')
                    ->label('Układ')
                    ->options(collect(PromotionLayout::cases())->mapWithKeys(
                        fn (PromotionLayout $layout) => [$layout->value => $layout->label()]
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
