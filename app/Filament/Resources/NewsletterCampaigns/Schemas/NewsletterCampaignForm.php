<?php

namespace App\Filament\Resources\NewsletterCampaigns\Schemas;

use App\Models\NewsletterCampaign;
use App\Models\NewsletterGroup;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NewsletterCampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Рассылка')
                    ->schema([
                        Placeholder::make('status_label')
                            ->label('Статус')
                            ->content(fn (?NewsletterCampaign $record) => $record?->status?->label() ?? 'Черновик')
                            ->visible(fn (?NewsletterCampaign $record) => $record !== null),
                        TextInput::make('name')
                            ->label('Внутреннее название')
                            ->required()
                            ->maxLength(255)
                            ->disabled(fn (?NewsletterCampaign $record) => $record?->isLocked() ?? false),
                        Select::make('newsletter_group_id')
                            ->label('Группа получателей')
                            ->options(
                                NewsletterGroup::query()
                                    ->where('is_active', true)
                                    ->orderBy('sort_order')
                                    ->pluck('name', 'id')
                            )
                            ->placeholder('Все подписанные')
                            ->native(false)
                            ->searchable()
                            ->disabled(fn (?NewsletterCampaign $record) => $record?->isLocked() ?? false),
                        TextInput::make('subject')
                            ->label('Тема письма')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->disabled(fn (?NewsletterCampaign $record) => $record?->isLocked() ?? false),
                        Textarea::make('body_html')
                            ->label('HTML-содержимое')
                            ->required()
                            ->rows(14)
                            ->helperText('Плейсхолдеры: {{name}}, {{email}}, {{unsubscribe_url}}. Отправка — кнопка «Отправить» вверху страницы.')
                            ->columnSpanFull()
                            ->disabled(fn (?NewsletterCampaign $record) => $record?->isLocked() ?? false),
                        Textarea::make('body_text')
                            ->label('Текстовая версия (опционально)')
                            ->rows(6)
                            ->columnSpanFull()
                            ->disabled(fn (?NewsletterCampaign $record) => $record?->isLocked() ?? false),
                    ])
                    ->columns(2),
            ]);
    }
}
