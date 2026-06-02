<?php

namespace App\Filament\Resources\Promotions\Tables;

use App\Enums\PromotionLayout;
use App\Enums\PromotionProductTargetType;
use App\Filament\Support\AdminTableColumns;
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
                    ->label('Код')
                    ->searchable()
                    ->sortable(),
                AdminTableColumns::plTitle(),
                TextColumn::make('layout')
                    ->label('Макет')
                    ->formatStateUsing(fn ($state) => $state instanceof PromotionLayout ? $state->label() : $state),
                TextColumn::make('product_target_type')
                    ->label('Область')
                    ->formatStateUsing(
                        fn ($state) => $state instanceof PromotionProductTargetType
                            ? $state->label()
                            : ($state ?: '—')
                    ),
                TextColumn::make('discount_percent')
                    ->label('Скидка')
                    ->suffix('%'),
                TextColumn::make('expires_at')
                    ->label('Окончание')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Активна')
                    ->boolean(),
                IconColumn::make('show_on_homepage')
                    ->label('На главной')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                TernaryFilter::make('is_active')->label('Активна'),
                SelectFilter::make('layout')
                    ->label('Макет')
                    ->options(collect(PromotionLayout::cases())->mapWithKeys(
                        fn (PromotionLayout $layout) => [$layout->value => $layout->label()]
                    )),
            ]);
    }
}
