<?php

namespace App\Filament\Resources\NewsletterSubscribers\Pages;

use App\Filament\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
use App\Services\Newsletter\NewsletterSubscriberCsv;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ListNewsletterSubscribers extends ListRecords
{
    protected static string $resource = NewsletterSubscriberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Экспорт CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => app(NewsletterSubscriberCsv::class)->export()),
            Action::make('import')
                ->label('Импорт CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('file')
                        ->label('Файл CSV')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'])
                        ->required()
                        ->disk('local')
                        ->directory('newsletter-imports'),
                ])
                ->action(function (array $data): void {
                    $uploaded = $data['file'] ?? null;

                    if ($uploaded instanceof UploadedFile) {
                        $file = $uploaded;
                    } elseif (is_string($uploaded) && Storage::disk('local')->exists($uploaded)) {
                        $path = Storage::disk('local')->path($uploaded);
                        $file = new UploadedFile($path, basename($path), null, null, true);
                    } else {
                        Notification::make()
                            ->title('Файл не выбран')
                            ->danger()
                            ->send();

                        return;
                    }

                    $result = app(NewsletterSubscriberCsv::class)->import($file);

                    Notification::make()
                        ->title('Импорт завершён')
                        ->body(sprintf(
                            'Добавлено: %d, обновлено: %d, пропущено: %d',
                            $result['imported'],
                            $result['updated'],
                            $result['skipped'],
                        ))
                        ->success()
                        ->send();

                    if ($result['errors'] !== []) {
                        Notification::make()
                            ->title('Ошибки импорта')
                            ->body(implode("\n", array_slice($result['errors'], 0, 5)))
                            ->warning()
                            ->send();
                    }
                }),
            CreateAction::make(),
        ];
    }
}
