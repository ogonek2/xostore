<?php

namespace App\Filament\Resources\NewsletterGroups;

use App\Filament\Resources\NewsletterGroups\Pages\CreateNewsletterGroup;
use App\Filament\Resources\NewsletterGroups\Pages\EditNewsletterGroup;
use App\Filament\Resources\NewsletterGroups\Pages\ListNewsletterGroups;
use App\Filament\Resources\NewsletterGroups\Schemas\NewsletterGroupForm;
use App\Filament\Resources\NewsletterGroups\Tables\NewsletterGroupsTable;
use App\Models\NewsletterGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NewsletterGroupResource extends Resource
{
    protected static ?string $model = NewsletterGroup::class;

    protected static ?string $navigationLabel = 'Группы рассылки';

    protected static ?string $modelLabel = 'группа';

    protected static ?string $pluralModelLabel = 'Группы рассылки';

    protected static string|\UnitEnum|null $navigationGroup = 'Маркетинг';

    protected static ?int $navigationSort = 21;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return NewsletterGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NewsletterGroupsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNewsletterGroups::route('/'),
            'create' => CreateNewsletterGroup::route('/create'),
            'edit' => EditNewsletterGroup::route('/{record}/edit'),
        ];
    }
}
