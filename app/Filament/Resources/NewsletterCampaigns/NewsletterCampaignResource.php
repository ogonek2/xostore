<?php

namespace App\Filament\Resources\NewsletterCampaigns;

use App\Filament\Resources\NewsletterCampaigns\Pages\CreateNewsletterCampaign;
use App\Filament\Resources\NewsletterCampaigns\Pages\EditNewsletterCampaign;
use App\Filament\Resources\NewsletterCampaigns\Pages\ListNewsletterCampaigns;
use App\Filament\Resources\NewsletterCampaigns\RelationManagers\SendsRelationManager;
use App\Filament\Resources\NewsletterCampaigns\Schemas\NewsletterCampaignForm;
use App\Filament\Resources\NewsletterCampaigns\Tables\NewsletterCampaignsTable;
use App\Models\NewsletterCampaign;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NewsletterCampaignResource extends Resource
{
    protected static ?string $model = NewsletterCampaign::class;

    protected static ?string $navigationLabel = 'Рассылки';

    protected static ?string $modelLabel = 'рассылка';

    protected static ?string $pluralModelLabel = 'Рассылки';

    protected static string|\UnitEnum|null $navigationGroup = 'Маркетинг';

    protected static ?int $navigationSort = 22;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    public static function form(Schema $schema): Schema
    {
        return NewsletterCampaignForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NewsletterCampaignsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('group');
    }

    public static function getRelations(): array
    {
        return [
            SendsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNewsletterCampaigns::route('/'),
            'create' => CreateNewsletterCampaign::route('/create'),
            'edit' => EditNewsletterCampaign::route('/{record}/edit'),
        ];
    }
}
