<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\PaymentMethod;
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
                Select::make('order_status_id')
                    ->label('Статус')
                    ->relationship('orderStatus', 'code', fn ($query) => $query->orderBy('sort_order'))
                    ->getOptionLabelFromRecordUsing(
                        fn ($record) => ($record->labels['pl'] ?? $record->code).' ('.$record->code.')'
                    )
                    ->required()
                    ->searchable()
                    ->preload()
                    ->native(false),
                Select::make('payment_method_id')
                    ->label('Способ оплаты')
                    ->relationship('paymentMethod', 'code')
                    ->getOptionLabelFromRecordUsing(fn (PaymentMethod $record) => $record->label('pl').' ('.$record->code.')')
                    ->searchable()
                    ->preload(),
                TextInput::make('shipping')
                    ->label('Доставка')
                    ->numeric()
                    ->minValue(0)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                        $set('total', (float) $get('subtotal') + (float) $state);
                    }),
                TextInput::make('subtotal')->label('Подытог')->disabled(),
                TextInput::make('total')->label('Итого')->numeric()->minValue(0),
                TextInput::make('email')->label('E-mail'),
                TextInput::make('phone')->label('Телефон'),
                TextInput::make('customer_name')->label('Имя'),
                Select::make('delivery_method')
                    ->label('Способ доставки')
                    ->options([
                        'courier' => 'Kurier',
                        'paczkomat' => 'Paczkomat',
                    ])
                    ->native(false),
                TextInput::make('city')->label('Город'),
                TextInput::make('postal_code')->label('Индекс'),
                TextInput::make('street')->label('Улица и номер')->columnSpanFull(),
                Textarea::make('delivery_address')->label('Адрес (старое поле)')->rows(2)->columnSpanFull()->disabled(),
                Textarea::make('notes')->label('Комментарий клиента')->disabled()->columnSpanFull(),
            ])->columns(2),
        ]);
    }
}
