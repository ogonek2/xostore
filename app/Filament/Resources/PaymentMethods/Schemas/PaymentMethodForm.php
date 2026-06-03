<?php

namespace App\Filament\Resources\PaymentMethods\Schemas;

use App\Enums\PaymentMethodType;
use App\Filament\Forms\NavItemLabelFields;
use App\Models\Language;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PaymentMethodForm
{
    public static function configure(Schema $schema): Schema
    {
        $instructionFields = Language::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($lang) => Textarea::make("instructions.{$lang->code}")
                ->label('Подсказка на сайте ('.strtoupper($lang->code).')')
                ->rows(2)
                ->columnSpanFull())
            ->all();

        return $schema->components([
            Section::make('Основное')
                ->schema([
                    TextInput::make('code')
                        ->label('Код')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(64)
                        ->alphaDash(),
                    Select::make('type')
                        ->label('Тип')
                        ->options(collect(PaymentMethodType::cases())->mapWithKeys(
                            fn (PaymentMethodType $type) => [$type->value => $type->label()]
                        ))
                        ->required()
                        ->native(false)
                        ->live(),
                    ...NavItemLabelFields::make('labels'),
                    ...$instructionFields,
                    TextInput::make('sort_order')->label('Порядок')->numeric()->default(0),
                    Toggle::make('is_active')->label('Активен')->default(true),
                ])
                ->columns(2),

            Section::make('Доставка для этого способа')
                ->schema([
                    Toggle::make('shipping_enabled')
                        ->label('Включить стоимость доставки')
                        ->default(true)
                        ->live(),
                    TextInput::make('shipping_cost')
                        ->label('Стоимость доставки')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->visible(fn (Get $get) => (bool) $get('shipping_enabled')),
                    Toggle::make('free_shipping_enabled')
                        ->label('Бесплатная доставка от суммы')
                        ->default(false)
                        ->live(),
                    TextInput::make('free_shipping_from')
                        ->label('Бесплатно от суммы заказа')
                        ->numeric()
                        ->minValue(0)
                        ->visible(fn (Get $get) => (bool) $get('free_shipping_enabled')),
                ])
                ->columns(2),

            Section::make('Перевод на счёт')
                ->schema([
                    TextInput::make('bank_recipient')->label('Получатель')->maxLength(255),
                    TextInput::make('bank_name')->label('Банк')->maxLength(255),
                    TextInput::make('bank_account')->label('Номер счёта')->maxLength(64),
                    TextInput::make('payment_note_template')
                        ->label('Шаблон назначения платежа')
                        ->placeholder('Zamówienie {{order_number}}')
                        ->helperText('Плейсхолдер: {{order_number}}')
                        ->maxLength(500),
                ])
                ->columns(2)
                ->visible(fn (Get $get) => $get('type') === PaymentMethodType::BankTransfer->value),

            Section::make('PayU / платёжная система')
                ->schema([
                    TextInput::make('redirect_url')
                        ->label('URL редиректа')
                        ->url()
                        ->maxLength(2000)
                        ->placeholder('https://secure.payu.com/...?extOrderId={{order_number}}&amount={{total_minor}}')
                        ->helperText('Плейсхолдеры: {{order_number}}, {{total}}, {{total_minor}}, {{currency}}, {{email}}, {{phone}}')
                        ->columnSpanFull(),
                ])
                ->visible(fn (Get $get) => $get('type') === PaymentMethodType::PaymentGateway->value),
        ]);
    }
}
