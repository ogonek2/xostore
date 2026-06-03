<?php

namespace App\Filament\Resources\SizeGrids;

use App\Filament\Resources\SizeGrids\Pages\CreateSizeGrid;
use App\Filament\Resources\SizeGrids\Pages\EditSizeGrid;
use App\Filament\Resources\SizeGrids\Pages\ListSizeGrids;
use App\Filament\Resources\SizeGrids\Schemas\SizeGridForm;
use App\Filament\Resources\SizeGrids\Tables\SizeGridsTable;
use App\Models\SizeGrid;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SizeGridResource extends Resource
{
    protected static ?string $model = SizeGrid::class;

    protected static ?string $navigationLabel = 'Размеры (кнопки)';

    protected static ?string $modelLabel = 'пресет кнопок размера';

    protected static ?string $pluralModelLabel = 'Пресеты кнопок размера';

    protected static string|\UnitEnum|null $navigationGroup = 'Каталог';

    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    public static function form(Schema $schema): Schema
    {
        return SizeGridForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SizeGridsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('translates');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSizeGrids::route('/'),
            'create' => CreateSizeGrid::route('/create'),
            'edit' => EditSizeGrid::route('/{record}/edit'),
        ];
    }
}
