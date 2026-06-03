<?php

namespace App\Filament\Resources\Footer;

use App\Filament\Resources\Footer\Pages\ManageFooterSettings;
use App\Filament\Resources\Footer\Schemas\FooterSettingsForm;
use App\Models\FooterSettings;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class FooterSettingsResource extends Resource
{
    protected static ?string $model = FooterSettings::class;

    protected static ?string $navigationLabel = 'Футер — настройки';

    protected static ?string $modelLabel = 'настройки футера';

    protected static ?string $pluralModelLabel = 'Футер';

    protected static string|\UnitEnum|null $navigationGroup = 'Настройки';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return FooterSettingsForm::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageFooterSettings::route('/'),
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
