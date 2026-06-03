<?php

namespace App\Filament\Resources\Footer;

use App\Filament\Resources\Footer\Pages\ManageFooterMenu;
use App\Filament\Resources\Footer\Schemas\FooterMenuForm;
use App\Models\NavMenu;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class FooterMenuResource extends Resource
{
    protected static ?string $model = NavMenu::class;

    protected static ?string $navigationLabel = 'Футер — колонки';

    protected static ?string $modelLabel = 'колонки футера';

    protected static ?string $pluralModelLabel = 'Колонки футера';

    protected static string|\UnitEnum|null $navigationGroup = 'Настройки';

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('code', 'footer');
    }

    public static function form(Schema $schema): Schema
    {
        return FooterMenuForm::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageFooterMenu::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
