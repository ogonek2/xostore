<?php

namespace App\Filament\Pages;

use App\Services\Import\ProductExcelImporter;
use App\Services\Import\ProductExcelTemplateBuilder;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportProducts extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static ?string $navigationLabel = 'Импорт товаров';

    protected static ?string $title = 'Импорт товаров из Excel';

    protected static string|\UnitEnum|null $navigationGroup = 'Каталог';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.import-products';

    /** @var array<string, mixed> */
    public array $data = [];

    /** @var array<string, mixed>|null */
    public ?array $importResult = null;

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                View::make('filament.pages.partials.product-import-documentation'),
                Section::make('Загрузка файла')
                    ->description('Используйте шаблон Excel. Лист «Товары» — данные, лист «Справка» — краткая подсказка.')
                    ->schema([
                        FileUpload::make('file')
                            ->label('Файл Excel (.xlsx)')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                            ])
                            ->required()
                            ->disk('local')
                            ->directory('product-imports')
                            ->maxSize(10240),
                    ])
                    ->statePath('data'),
                Actions::make([
                    Action::make('downloadTemplate')
                        ->label('Скачать шаблон Excel')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->action(fn (): StreamedResponse => app(ProductExcelTemplateBuilder::class)->download()),
                    Action::make('runImport')
                        ->label('Импортировать')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->requiresConfirmation()
                        ->modalDescription('Существующие товары с тем же SKU будут обновлены. Новые — созданы.')
                        ->action(fn () => $this->runImport()),
                ]),
                View::make('filament.pages.partials.product-import-result')
                    ->viewData(fn (): array => ['result' => $this->importResult])
                    ->visible(fn (): bool => $this->importResult !== null),
            ]);
    }

    public function runImport(): void
    {
        $this->importResult = null;

        $this->validate([
            'data.file' => ['required'],
        ]);

        $file = $this->resolveUploadedFile($this->data['file'] ?? null);

        if (! $file) {
            Notification::make()
                ->title('Файл не выбран')
                ->danger()
                ->send();

            return;
        }

        $result = app(ProductExcelImporter::class)->import($file);
        $this->importResult = $result;

        Notification::make()
            ->title('Импорт завершён')
            ->body(sprintf(
                'Создано: %d, обновлено: %d, вариантов +%d/~%d, пропущено: %d',
                $result['created'],
                $result['updated'],
                $result['variants_created'],
                $result['variants_updated'],
                $result['skipped'],
            ))
            ->success()
            ->send();

        if ($result['warnings'] !== []) {
            Notification::make()
                ->title('Предупреждения')
                ->body(implode("\n", array_slice($result['warnings'], 0, 5)))
                ->warning()
                ->send();
        }

        if ($result['errors'] !== []) {
            Notification::make()
                ->title('Ошибки')
                ->body(implode("\n", array_slice($result['errors'], 0, 5)))
                ->danger()
                ->send();
        }

        $this->data = [];
    }

    protected function resolveUploadedFile(mixed $uploaded): ?UploadedFile
    {
        if ($uploaded instanceof UploadedFile) {
            return $uploaded;
        }

        if (is_string($uploaded) && Storage::disk('local')->exists($uploaded)) {
            $path = Storage::disk('local')->path($uploaded);

            return new UploadedFile($path, basename($path), null, null, true);
        }

        return null;
    }
}
