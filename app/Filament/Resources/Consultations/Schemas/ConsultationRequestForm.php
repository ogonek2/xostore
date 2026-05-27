<?php

namespace App\Filament\Resources\Consultations\Schemas;

use App\Enums\ConsultationStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ConsultationRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                Select::make('status')->label('Статус')->options(collect(ConsultationStatus::cases())->mapWithKeys(
                    fn (ConsultationStatus $s) => [$s->value => $s->label()]
                )),
                TextInput::make('name')->label('Имя')->disabled(),
                TextInput::make('email')->label('E-mail')->disabled(),
                TextInput::make('phone')->label('Телефон')->disabled(),
                Textarea::make('message')->label('Сообщение')->disabled()->columnSpanFull(),
                Textarea::make('admin_notes')->label('Внутренние заметки')->rows(4)->columnSpanFull(),
            ])->columns(2),
        ]);
    }
}
