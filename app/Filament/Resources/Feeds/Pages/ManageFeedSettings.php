<?php

namespace App\Filament\Resources\Feeds\Pages;

use App\Filament\Resources\Feeds\FeedSettingsResource;
use App\Models\FeedSettings;
use App\Services\Feeds\ProductFeedGenerator;
use App\Services\Feeds\ProductFeedRegenerator;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class ManageFeedSettings extends EditRecord
{
    protected static string $resource = FeedSettingsResource::class;

    protected static ?string $title = 'Товарные фиды';

    public function mount(int|string|null $record = null): void
    {
        parent::mount(FeedSettings::instance()->getKey());
    }

    protected function getHeaderActions(): array
    {
        $settings = FeedSettings::instance();

        return [
            Action::make('regenerate')
                ->label('Обновить фиды сейчас')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('primary')
                ->action(function (): void {
                    try {
                        ProductFeedRegenerator::regenerateNow();
                        $this->record = FeedSettings::instance()->refresh();
                        $this->fillForm();

                        Notification::make()
                            ->title('Фиды успешно обновлены')
                            ->success()
                            ->send();
                    } catch (\Throwable $exception) {
                        Notification::make()
                            ->title('Не удалось обновить фиды')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('openGoogle')
                ->label('Открыть Google фид')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url($settings->googlePublicUrl(), shouldOpenInNewTab: true)
                ->visible($settings->google_enabled),
            Action::make('openFacebook')
                ->label('Открыть Facebook фид')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url($settings->facebookPublicUrl(), shouldOpenInNewTab: true)
                ->visible($settings->facebook_enabled),
        ];
    }

    protected function afterSave(): void
    {
        try {
            app(ProductFeedGenerator::class)->regenerateAll();
            $this->record = FeedSettings::instance()->refresh();
            $this->fillForm();

            Notification::make()
                ->title('Настройки сохранены, фиды обновлены')
                ->success()
                ->send();
        } catch (\Throwable $exception) {
            Notification::make()
                ->title('Настройки сохранены')
                ->body('Фиды не удалось обновить: '.$exception->getMessage())
                ->warning()
                ->send();
        }
    }
}
