<?php

namespace App\Filament\Resources\SizeChartPresets;

use App\Filament\Resources\SizeChartPresets\Pages\CreateSizeChartPreset;
use App\Filament\Resources\SizeChartPresets\Pages\EditSizeChartPreset;
use App\Filament\Resources\SizeChartPresets\Pages\ListSizeChartPresets;
use App\Filament\Resources\SizeChartPresets\Schemas\SizeChartPresetForm;
use App\Filament\Resources\SizeChartPresets\Tables\SizeChartPresetsTable;
use App\Models\SizeChartPreset;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SizeChartPresetResource extends Resource
{
    protected static ?string $model = SizeChartPreset::class;

    protected static ?string $navigationLabel = 'Таблицы мерок (см)';

    protected static ?string $modelLabel = 'пресет таблицы мерок';

    protected static ?string $pluralModelLabel = 'Пресеты таблицы мерок';

    protected static string|\UnitEnum|null $navigationGroup = 'Каталог';

    protected static ?int $navigationSort = 6;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    public static function form(Schema $schema): Schema
    {
        return SizeChartPresetForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SizeChartPresetsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('translates');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSizeChartPresets::route('/'),
            'create' => CreateSizeChartPreset::route('/create'),
            'edit' => EditSizeChartPreset::route('/{record}/edit'),
        ];
    }
}
