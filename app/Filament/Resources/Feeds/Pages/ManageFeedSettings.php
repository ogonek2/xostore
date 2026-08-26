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

    /** @var string|null */
    private ?string $pendingGoogleFeed = null;

    /** @var string|null */
    private ?string $pendingFacebookFeed = null;

    public function mount(int|string|null $record = null): void
    {
        parent::mount(FeedSettings::instance()->getKey());
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $settings = $this->getSettings();

        $data['google_feed_content'] = $settings->readGoogleFeedContent();
        $data['facebook_feed_content'] = $settings->readFacebookFeedContent();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->pendingGoogleFeed = array_key_exists('google_feed_content', $data)
            ? (string) $data['google_feed_content']
            : null;
        $this->pendingFacebookFeed = array_key_exists('facebook_feed_content', $data)
            ? (string) $data['facebook_feed_content']
            : null;

        unset($data['google_feed_content'], $data['facebook_feed_content']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        $settings = $this->getSettings();

        return [
            Action::make('saveFeeds')
                ->label('Сохранить содержимое фидов')
                ->icon(Heroicon::OutlinedDocumentCheck)
                ->color('gray')
                ->action(function (): void {
                    try {
                        $this->persistEditorFeedContents(lockManual: true);
                        $this->record = FeedSettings::instance()->refresh();
                        $this->fillForm();

                        Notification::make()
                            ->title('Содержимое фидов сохранено')
                            ->body('Включена блокировка перезаписи ручных правок.')
                            ->success()
                            ->send();
                    } catch (\Throwable $exception) {
                        Notification::make()
                            ->title('Не удалось сохранить фиды')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('regenerate')
                ->label('Обновить фиды сейчас')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('primary')
                ->requiresConfirmation(fn (): bool => $this->getSettings()->manual_feeds_locked)
                ->modalHeading('Перезаписать ручные правки?')
                ->modalDescription('Сейчас включена блокировка ручных правок. Генерация из каталога перезапишет XML/CSV. Продолжить?')
                ->action(function (): void {
                    try {
                        ProductFeedRegenerator::regenerateNow(force: true);
                        $settings = FeedSettings::instance();
                        $settings->forceFill(['manual_feeds_locked' => false])->save();

                        $this->record = $settings->refresh();
                        $this->fillForm();

                        Notification::make()
                            ->title('Фиды успешно обновлены')
                            ->body('Блокировка ручных правок снята.')
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
            $settings = FeedSettings::instance()->refresh();

            if ($settings->manual_feeds_locked) {
                $this->writePendingFeedsToDisk();
                $this->record = FeedSettings::instance()->refresh();
                $this->fillForm();

                Notification::make()
                    ->title('Настройки и ручные фиды сохранены')
                    ->success()
                    ->send();

                return;
            }

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
        } finally {
            $this->pendingGoogleFeed = null;
            $this->pendingFacebookFeed = null;
        }
    }

    private function persistEditorFeedContents(bool $lockManual): void
    {
        $state = $this->form->getRawState();

        $this->pendingGoogleFeed = array_key_exists('google_feed_content', $state)
            ? (string) $state['google_feed_content']
            : null;
        $this->pendingFacebookFeed = array_key_exists('facebook_feed_content', $state)
            ? (string) $state['facebook_feed_content']
            : null;

        $this->writePendingFeedsToDisk();

        if ($lockManual) {
            FeedSettings::instance()->forceFill(['manual_feeds_locked' => true])->save();
        }
    }

    private function writePendingFeedsToDisk(): void
    {
        $settings = FeedSettings::instance();

        if ($this->pendingGoogleFeed !== null && $settings->google_enabled) {
            $settings->writeGoogleFeedContent($this->pendingGoogleFeed);
        }

        if ($this->pendingFacebookFeed !== null && $settings->facebook_enabled) {
            $settings->writeFacebookFeedContent($this->pendingFacebookFeed);
        }
    }

    private function getSettings(): FeedSettings
    {
        return $this->record instanceof FeedSettings
            ? $this->record
            : FeedSettings::instance();
    }
}
