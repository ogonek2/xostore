<?php

namespace App\Filament\Resources\SizeChartPresets\Tables;

use App\Filament\Support\AdminTableColumns;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SizeChartPresetsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->label('Код')->searchable()->sortable(),
            AdminTableColumns::plTranslation(),
            TextColumn::make('unit')->label('Единица'),
            TextColumn::make('rows_count')->label('Строк')->counts('rows'),
            IconColumn::make('is_active')->label('Активен')->boolean(),
        ]);
    }
}
