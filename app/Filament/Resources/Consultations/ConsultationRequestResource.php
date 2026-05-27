<?php

namespace App\Filament\Resources\Consultations;

use App\Filament\Resources\Consultations\Pages\EditConsultationRequest;
use App\Filament\Resources\Consultations\Pages\ListConsultationRequests;
use App\Filament\Resources\Consultations\Schemas\ConsultationRequestForm;
use App\Filament\Resources\Consultations\Tables\ConsultationRequestsTable;
use App\Models\ConsultationRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ConsultationRequestResource extends Resource
{
    protected static ?string $model = ConsultationRequest::class;

    protected static ?string $navigationLabel = 'Консультации';

    protected static ?string $modelLabel = 'заявка';

    protected static ?string $pluralModelLabel = 'Консультации';

    protected static string|\UnitEnum|null $navigationGroup = 'Продажи';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return ConsultationRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConsultationRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListConsultationRequests::route('/'),
            'edit' => EditConsultationRequest::route('/{record}/edit'),
        ];
    }
}
