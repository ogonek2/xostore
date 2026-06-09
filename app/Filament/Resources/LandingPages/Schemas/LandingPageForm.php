<?php

namespace App\Filament\Resources\LandingPages\Schemas;

use App\Filament\Forms\TranslationTabs;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LandingPageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Страница')
                    ->schema([
                        TextInput::make('code')
                            ->label('Код (внутренний)')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(64)
                            ->alphaDash()
                            ->helperText('Латиница, например summer-sale'),
                        TextInput::make('sort_order')
                            ->label('Порядок')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('Опубликована')
                            ->default(true),
                        DateTimePicker::make('published_at')
                            ->label('Дата публикации')
                            ->seconds(false),
                        Toggle::make('show_header')
                            ->label('Шапка сайта')
                            ->default(true),
                        Toggle::make('show_footer')
                            ->label('Подвал сайта')
                            ->default(true),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                TranslationTabs::make('landing_page'),
            ]);
    }
}
