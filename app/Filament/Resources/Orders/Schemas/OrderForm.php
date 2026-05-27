<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Заказ')->schema([
                TextInput::make('number')->label('Номер')->disabled(),
                Select::make('status')->label('Статус')->options(collect(OrderStatus::cases())->mapWithKeys(
                    fn (OrderStatus $s) => [$s->value => $s->label()]
                )),
                TextInput::make('total')->label('Сумма')->disabled(),
                TextInput::make('email')->label('E-mail')->disabled(),
                TextInput::make('phone')->label('Телефон')->disabled(),
                Textarea::make('notes')->label('Комментарий клиента')->disabled()->columnSpanFull(),
            ])->columns(2),
        ]);
    }
}
