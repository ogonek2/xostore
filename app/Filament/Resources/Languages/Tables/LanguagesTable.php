<?php

namespace App\Filament\Resources\Languages\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LanguagesTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('flag')->label(''),
            TextColumn::make('code')->label('Код'),
            TextColumn::make('name')->label('Название'),
            TextColumn::make('locale')->label('Locale'),
            IconColumn::make('is_default')->label('По умолчанию')->boolean(),
            IconColumn::make('is_active')->label('Активен')->boolean(),
            IconColumn::make('auto_translate_on_create')->label('Автоперевод')->boolean(),
            TextColumn::make('sort_order')->label('Порядок')->sortable(),
        ]);
    }
}
