<?php

namespace App\Filament\Resources\Promotions\Schemas;

use App\Enums\PromotionLayout;
use App\Filament\Forms\TranslationTabs;
use App\Models\Category;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PromotionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Ustawienia promocji')
                    ->schema([
                        TextInput::make('code')
                            ->label('Kod')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(64)
                            ->alphaDash(),
                        Select::make('layout')
                            ->label('Układ karty')
                            ->options(collect(PromotionLayout::cases())->mapWithKeys(
                                fn (PromotionLayout $layout) => [$layout->value => $layout->label()]
                            ))
                            ->required()
                            ->native(false),
                        TextInput::make('discount_percent')
                            ->label('Rabat (%)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(99),
                        TextInput::make('sort_order')
                            ->label('Kolejność')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('Aktywna')
                            ->default(true),
                        Toggle::make('show_on_homepage')
                            ->label('Na stronie głównej')
                            ->default(true),
                        DateTimePicker::make('starts_at')
                            ->label('Start')
                            ->seconds(false),
                        DateTimePicker::make('expires_at')
                            ->label('Koniec')
                            ->seconds(false),
                        FileUpload::make('image_path')
                            ->label('Zdjęcie')
                            ->image()
                            ->directory('promotions')
                            ->columnSpanFull(),
                        TextInput::make('link_url')
                            ->label('Link (opcjonalny)')
                            ->url()
                            ->maxLength(512)
                            ->placeholder('https://… lub puste — użyje kategorii')
                            ->columnSpanFull(),
                        Select::make('category_id')
                            ->label('Kategoria docelowa')
                            ->relationship('category', 'code')
                            ->getOptionLabelFromRecordUsing(
                                fn (Category $record) => $record->translate('name', 'pl') ?? $record->code
                            )
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                TranslationTabs::make('promotion'),
            ]);
    }
}
