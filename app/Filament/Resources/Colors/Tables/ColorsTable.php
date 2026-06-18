<?php

namespace App\Filament\Resources\Colors\Tables;

use App\Filament\Support\AdminTableColumns;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ColorsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            ColorColumn::make('hex')->label('Цвет'),
            TextColumn::make('code')->searchable()->sortable(),
            AdminTableColumns::plTranslation(),
            IconColumn::make('is_active')->label('Активен')->boolean(),
            TextColumn::make('sort_order')->label('Порядок')->sortable(),
        ]);
    }
}
