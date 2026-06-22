<?php

namespace App\Filament\Resources\Feeds;

use App\Filament\Resources\Feeds\Pages\ManageFeedSettings;
use App\Filament\Resources\Feeds\Schemas\FeedSettingsForm;
use App\Models\FeedSettings;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class FeedSettingsResource extends Resource
{
    protected static ?string $model = FeedSettings::class;

    protected static ?string $navigationLabel = 'Товарные фиды';

    protected static ?string $modelLabel = 'настройки фидов';

    protected static ?string $pluralModelLabel = 'Товарные фиды';

    protected static string|\UnitEnum|null $navigationGroup = 'Настройки';

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRss;

    public static function form(Schema $schema): Schema
    {
        return FeedSettingsForm::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageFeedSettings::route('/'),
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
