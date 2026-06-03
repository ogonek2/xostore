<?php

namespace App\Filament\Resources\NewsletterCampaigns\Pages;

use App\Filament\Resources\NewsletterCampaigns\NewsletterCampaignResource;
use App\Services\Newsletter\NewsletterCampaignSender;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditNewsletterCampaign extends EditRecord
{
    protected static string $resource = NewsletterCampaignResource::class;

    public function canSave(): bool
    {
        return ! $this->record->isLocked();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send')
                ->label('Отправить')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Отправить рассылку?')
                ->modalDescription('Письма будут отправлены сразу всем подписчикам выбранной группы (или всем активным подписчикам).')
                ->visible(fn () => $this->record->canSend())
                ->action(function (): void {
                    try {
                        $stats = app(NewsletterCampaignSender::class)->sendNow($this->record->fresh());

                        $this->refreshFormData(['status']);

                        Notification::make()
                            ->title('Рассылка отправлена')
                            ->body(sprintf(
                                'Получателей: %d. Успешно: %d. Ошибок: %d. Пропущено: %d.',
                                $stats['recipients'],
                                $stats['sent'],
                                $stats['failed'],
                                $stats['skipped'],
                            ))
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Ошибка отправки')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            DeleteAction::make()
                ->visible(fn () => ! $this->record->isLocked()),
        ];
    }
}
