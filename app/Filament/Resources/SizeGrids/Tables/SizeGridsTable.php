<?php

namespace App\Filament\Resources\SizeGrids\Tables;

use App\Filament\Support\AdminTableColumns;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SizeGridsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->label('Код')->searchable()->sortable(),
            AdminTableColumns::plTranslation(),
            TextColumn::make('unit')->label('Единица'),
            TextColumn::make('values_count')->label('Размеров')->counts('values'),
            IconColumn::make('is_active')->label('Активна')->boolean(),
        ]);
    }
}
