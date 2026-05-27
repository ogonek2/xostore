<?php

namespace App\Filament\Resources\Languages\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LanguageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('code')->label('Код')->disabled(),
                TextInput::make('name')->label('Название')->required(),
                TextInput::make('locale')->label('Locale')->required(),
                TextInput::make('flag')->label('Флаг (emoji)'),
                Toggle::make('is_default')->label('По умолчанию')->disabled(),
                Toggle::make('is_active')->label('Активен'),
                Toggle::make('auto_translate_on_create')
                    ->label('Автоперевод при создании записей')
                    ->helperText('При сохранении товара/категории на PL — EN заполняется автоматически (MyMemory API)'),
                TextInput::make('sort_order')->label('Порядок')->numeric(),
            ])->columns(2),
        ]);
    }
}
