<?php

namespace App\Filament\Resources\PaymentMethods\Tables;

use App\Enums\PaymentMethodType;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentMethodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Код')->searchable(),
                TextColumn::make('labels.pl')->label('Название (PL)')->limit(30),
                TextColumn::make('type')
                    ->label('Тип')
                    ->formatStateUsing(fn ($s) => $s instanceof PaymentMethodType ? $s->label() : $s),
                TextColumn::make('shipping_cost')->label('Доставка')->suffix(' zł'),
                IconColumn::make('free_shipping_enabled')->label('Бесплатно от')->boolean(),
                TextColumn::make('free_shipping_from')->label('От суммы')->placeholder('—'),
                IconColumn::make('is_active')->label('Активен')->boolean(),
                TextColumn::make('sort_order')->label('Порядок')->sortable(),
            ])
            ->defaultSort('sort_order');
    }
}
