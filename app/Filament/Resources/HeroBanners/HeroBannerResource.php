<?php

namespace App\Filament\Resources\HeroBanners;

use App\Filament\Resources\HeroBanners\Pages\CreateHeroBanner;
use App\Filament\Resources\HeroBanners\Pages\EditHeroBanner;
use App\Filament\Resources\HeroBanners\Pages\ListHeroBanners;
use App\Filament\Resources\HeroBanners\RelationManagers\HeroBannerItemsRelationManager;
use App\Filament\Resources\HeroBanners\Schemas\HeroBannerForm;
use App\Filament\Resources\HeroBanners\Tables\HeroBannersTable;
use App\Models\HeroBannerSection;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HeroBannerResource extends Resource
{
    protected static ?string $model = HeroBannerSection::class;

    protected static ?string $navigationLabel = 'Конструктор баннеров';

    protected static ?string $modelLabel = 'hero-баннер';

    protected static ?string $pluralModelLabel = 'Конструктор баннеров';

    protected static string|\UnitEnum|null $navigationGroup = 'Маркетинг';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    public static function form(Schema $schema): Schema
    {
        return HeroBannerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HeroBannersTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['items.translates']);
    }

    public static function getRelations(): array
    {
        return [
            HeroBannerItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHeroBanners::route('/'),
            'create' => CreateHeroBanner::route('/create'),
            'edit' => EditHeroBanner::route('/{record}/edit'),
        ];
    }
}
