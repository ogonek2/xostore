<?php

namespace App\Filament\Resources\Catalogs;

use App\Filament\Resources\Catalogs\Pages\CreateCatalog;
use App\Filament\Resources\Catalogs\Pages\EditCatalog;
use App\Filament\Resources\Catalogs\Pages\ListCatalogs;
use App\Filament\Resources\Catalogs\Schemas\CatalogForm;
use App\Filament\Resources\Catalogs\Tables\CatalogsTable;
use App\Models\Catalog;
use BackedEnum;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CatalogResource extends Resource
{
    protected static ?string $model = Catalog::class;

    protected static ?string $navigationLabel = 'Каталоги';

    protected static ?string $modelLabel = 'каталог';

    protected static ?string $pluralModelLabel = 'Каталоги';

    protected static string|\UnitEnum|null $navigationGroup = 'Каталог';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    public static function form(Schema $schema): Schema
    {
        return CatalogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CatalogsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('translates');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCatalogs::route('/'),
            'create' => CreateCatalog::route('/create'),
            'edit' => EditCatalog::route('/{record}/edit'),
        ];
    }
}
