<?php

namespace App\Filament\Resources\Colors\Schemas;

use App\Filament\Forms\TranslationTabs;
use App\Support\Import\ImportUniqueCode;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ColorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Основное')
                ->schema([
                    TextInput::make('code')
                        ->label('Код')
                        ->required()
                        ->maxLength(64)
                        ->unique(ignoreRecord: true)
                        ->helperText('Латиница, без пробелов. Используется в импорте и фильтрах.')
                        ->extraInputAttributes(['autocomplete' => 'off'])
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (?string $state, callable $set, Get $get): void {
                            if (filled($state)) {
                                return;
                            }

                            $defaultLocale = (string) config('shop.default_language', 'pl');
                            $name = $get("trans_{$defaultLocale}_name");

                            if (! is_string($name) || trim($name) === '') {
                                return;
                            }

                            $set('code', ImportUniqueCode::slugBase($name));
                        }),
                    ColorPicker::make('hex')
                        ->label('HEX')
                        ->required()
                        ->default('#CCCCCC'),
                    Toggle::make('is_active')->label('Активен')->default(true),
                    TextInput::make('sort_order')
                        ->label('Порядок')
                        ->numeric()
                        ->default(0),
                ])
                ->columns(2),
            TranslationTabs::make('color', 'Переводы', ['name']),
        ]);
    }
}
