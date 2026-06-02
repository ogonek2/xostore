<?php

namespace App\Filament\Resources\Banners\Schemas;

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
            Section::make('Данные баннера')
                ->schema([
                    TextInput::make('title')
                        ->label('Название')
                        ->maxLength(160),
                    FilamentMedia::image('image_path', 'banners')
                        ->label('Изображение')
                        ->required()
                        ->columnSpanFull(),
                    TextInput::make('link_url')
                        ->label('Ссылка')
                        ->url()
                        ->maxLength(500)
                        ->placeholder('https://…'),
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
