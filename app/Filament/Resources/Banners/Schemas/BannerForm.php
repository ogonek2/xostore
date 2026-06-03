<?php

namespace App\Filament\Resources\Banners\Schemas;

use App\Filament\Forms\TranslationTabs;
use App\Filament\Support\FilamentMedia;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TranslationTabs::make('banner', 'Тексты баннера'),
            Section::make('Данные баннера')
                ->schema([
                    FilamentMedia::image('image_path', 'banners')
                        ->label('Изображение')
                        ->required()
                        ->columnSpanFull(),
                    TextInput::make('sort_order')
                        ->label('Порядок')
                        ->numeric()
                        ->default(0),
                    Toggle::make('is_active')
                        ->label('Активен')
                        ->default(true),
                ])
                ->columns(2),
        ]);
    }
}
