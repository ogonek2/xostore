<?php

namespace App\Filament\Resources\OrderStatusEmailTemplates\Schemas;

use App\Models\OrderStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderStatusEmailTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('E-mail при смене статуса')
                ->schema([
                    TextInput::make('name')
                        ->label('Название (для админки)')
                        ->required()
                        ->maxLength(120),
                    Select::make('order_status_id')
                        ->label('Статус заказа')
                        ->relationship(
                            'orderStatus',
                            'code',
                            fn ($query) => $query->where('is_active', true)->orderBy('sort_order')
                        )
                        ->getOptionLabelFromRecordUsing(
                            fn (OrderStatus $record) => ($record->labels['pl'] ?? $record->code).' ('.$record->code.')'
                        )
                        ->required()
                        ->searchable()
                        ->preload()
                        ->unique(ignoreRecord: true)
                        ->native(false)
                        ->helperText('Один шаблон на статус. Письмо уходит клиенту при смене статуса заказа на этот.'),
                    TextInput::make('subject')
                        ->label('Тема письма')
                        ->required()
                        ->maxLength(255)
                        ->default('Zamówienie {{order_number}} — {{status}}')
                        ->columnSpanFull(),
                    Textarea::make('message')
                        ->label('Текст письма')
                        ->required()
                        ->rows(8)
                        ->helperText('Плейсхолдеры: {{order_number}}, {{customer_name}}, {{email}}, {{phone}}, {{total}}, {{status}}, {{city}}')
                        ->columnSpanFull(),
                    Toggle::make('is_active')
                        ->label('Активен')
                        ->default(true),
                ])
                ->columns(2),
        ]);
    }
}
