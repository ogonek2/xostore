<?php

namespace App\Filament\Resources\OrderStatuses\Schemas;

use App\Filament\Forms\NavItemLabelFields;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class OrderStatusForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Статус заказа')
                ->schema([
                    TextInput::make('code')
                        ->label('Код')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(64)
                        ->alphaDash()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, callable $set) => $set('code', Str::slug($state))),
                    ...NavItemLabelFields::make('labels'),
                    TextInput::make('color')
                        ->label('Цвет бейджа')
                        ->placeholder('#c9a962')
                        ->maxLength(16),
                    TextInput::make('sort_order')->label('Порядок')->numeric()->default(0),
                    Toggle::make('is_active')->label('Активен')->default(true),
                    Toggle::make('is_default')
                        ->label('Статус для новых заказов')
                        ->helperText('Только один статус может быть по умолчанию'),
                    Toggle::make('counts_towards_revenue')
                        ->label('Учитывать в выручке')
                        ->default(true),
                ])
                ->columns(2),
        ]);
    }
}
