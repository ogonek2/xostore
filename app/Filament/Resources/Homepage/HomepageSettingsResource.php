<?php

namespace App\Filament\Resources\Homepage;

use App\Filament\Resources\Homepage\Pages\ManageHomepageSettings;
use App\Filament\Resources\Homepage\Schemas\HomepageSettingsForm;
use App\Models\HomepageSettings;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class HomepageSettingsResource extends Resource
{
    protected static ?string $model = HomepageSettings::class;

    protected static ?string $navigationLabel = 'Главная страница';

    protected static ?string $modelLabel = 'настройки главной';

    protected static ?string $pluralModelLabel = 'Главная страница';

    protected static string|\UnitEnum|null $navigationGroup = 'Настройки';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHomeModern;

    public static function form(Schema $schema): Schema
    {
        return HomepageSettingsForm::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageHomepageSettings::route('/'),
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
