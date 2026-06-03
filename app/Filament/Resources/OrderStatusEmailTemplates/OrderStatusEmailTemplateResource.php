<?php

namespace App\Filament\Resources\OrderStatusEmailTemplates;

use App\Filament\Resources\OrderStatusEmailTemplates\Pages\CreateOrderStatusEmailTemplate;
use App\Filament\Resources\OrderStatusEmailTemplates\Pages\EditOrderStatusEmailTemplate;
use App\Filament\Resources\OrderStatusEmailTemplates\Pages\ListOrderStatusEmailTemplates;
use App\Filament\Resources\OrderStatusEmailTemplates\Schemas\OrderStatusEmailTemplateForm;
use App\Filament\Resources\OrderStatusEmailTemplates\Tables\OrderStatusEmailTemplatesTable;
use App\Models\OrderStatusEmailTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderStatusEmailTemplateResource extends Resource
{
    protected static ?string $model = OrderStatusEmailTemplate::class;

    protected static ?string $navigationLabel = 'E-mail шаблоны статусов';

    protected static ?string $modelLabel = 'E-mail шаблон';

    protected static ?string $pluralModelLabel = 'E-mail шаблоны статусов';

    protected static string|\UnitEnum|null $navigationGroup = 'Продажи';

    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    public static function form(Schema $schema): Schema
    {
        return OrderStatusEmailTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrderStatusEmailTemplatesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('orderStatus');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrderStatusEmailTemplates::route('/'),
            'create' => CreateOrderStatusEmailTemplate::route('/create'),
            'edit' => EditOrderStatusEmailTemplate::route('/{record}/edit'),
        ];
    }
}
