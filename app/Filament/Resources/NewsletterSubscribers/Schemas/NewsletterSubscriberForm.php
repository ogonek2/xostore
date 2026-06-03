<?php

namespace App\Filament\Resources\NewsletterSubscribers\Schemas;

use App\Enums\NewsletterSubscriberStatus;
use App\Models\Language;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NewsletterSubscriberForm
{
    public static function configure(Schema $schema): Schema
    {
        $locales = Language::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('code', 'code');

        return $schema
            ->columns(2)
            ->components([
                Section::make('Подписчик')
                    ->schema([
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('name')
                            ->label('Имя')
                            ->maxLength(120),
                        Select::make('locale')
                            ->label('Язык')
                            ->options($locales)
                            ->native(false),
                        Select::make('status')
                            ->label('Статус')
                            ->options(collect(NewsletterSubscriberStatus::cases())->mapWithKeys(
                                fn (NewsletterSubscriberStatus $status) => [$status->value => $status->label()]
                            ))
                            ->required()
                            ->native(false),
                        TextInput::make('source')
                            ->label('Источник')
                            ->maxLength(64),
                        Select::make('groups')
                            ->label('Группы рассылки')
                            ->relationship('groups', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                        DateTimePicker::make('subscribed_at')
                            ->label('Дата подписки'),
                        DateTimePicker::make('unsubscribed_at')
                            ->label('Дата отписки'),
                    ])
                    ->columns(2),
            ]);
    }
}
