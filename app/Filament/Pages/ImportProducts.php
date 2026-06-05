<?php

namespace App\Filament\Pages;

use App\Services\Import\ProductExcelImporter;
use App\Services\Import\ProductExcelTemplateBuilder;
use App\Services\Import\ProductImportPreviewer;
use App\Support\Import\ProductImportSpreadsheetLoader;
use App\Support\Import\ProductImportUploadedFileResolver;
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
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportProducts extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static ?string $navigationLabel = 'Импорт товаров';

    protected static ?string $title = 'Импорт товаров из Excel / CSV';

    protected static string|\UnitEnum|null $navigationGroup = 'Каталог';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.import-products';

    /** @var array<string, mixed> */
    public array $data = [];

    /** @var array<string, mixed>|null */
    public ?array $importResult = null;

    /** @var array<string, mixed>|null */
    public ?array $importPreview = null;

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Загрузка файла')
                    ->icon(Heroicon::OutlinedDocumentArrowUp)
                    ->description('Поддерживаются .xlsx и .csv. После загрузки ниже появится предпросмотр данных.')
                    ->schema([
                        FileUpload::make('file')
                            ->label('Файл Excel (.xlsx) или CSV (.csv)')
                            ->acceptedFileTypes(ProductImportSpreadsheetLoader::ACCEPTED_MIME_TYPES)
                            ->required()
                            ->disk('local')
                            ->directory('product-imports')
                            ->maxSize(10240)
                            ->live()
                            ->afterStateUpdated(fn () => $this->loadImportPreview()),
                    ])
                    ->statePath('data'),
                Actions::make([
                    Action::make('downloadTemplate')
                        ->label('Скачать шаблон Excel')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->action(fn (): StreamedResponse => app(ProductExcelTemplateBuilder::class)->download()),
                    Action::make('refreshPreview')
                        ->label('Обновить предпросмотр')
                        ->icon('heroicon-o-eye')
                        ->color('gray')
                        ->action(fn () => $this->loadImportPreview()),
                    Action::make('runImport')
                        ->label('Импортировать')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->requiresConfirmation()
                        ->modalDescription('Существующие товары с тем же SKU будут обновлены. Новые — созданы.')
                        ->action(fn () => $this->runImport()),
                ]),
                Section::make('Предпросмотр данных')
                    ->icon(Heroicon::OutlinedTableCells)
                    ->description('Как будут импортированы товары: slug, категории, каталоги и другие связи.')
                    ->schema([
                        View::make('filament.pages.partials.product-import-preview')
                            ->viewData(fn (): array => ['preview' => $this->importPreview]),
                    ])
                    ->visible(fn (): bool => $this->importPreview !== null),
                Section::make('Результат импорта')
                    ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                    ->schema([
                        View::make('filament.pages.partials.product-import-result')
                            ->viewData(fn (): array => ['result' => $this->importResult]),
                    ])
                    ->visible(fn (): bool => $this->importResult !== null),
                Section::make('Документация по импорту')
                    ->icon(Heroicon::OutlinedBookOpen)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        View::make('filament.pages.partials.product-import-documentation'),
                    ]),
            ]);
    }

    public function loadImportPreview(): void
    {
        $file = $this->resolveUploadedFile();

        if (! $file) {
            $this->importPreview = null;

            return;
        }

        $this->importPreview = app(ProductImportPreviewer::class)->build($file);
    }

    public function runImport(): void
    {
        $this->importResult = null;

        $file = $this->resolveUploadedFile();

        if (! $file) {
            Notification::make()
                ->title('Файл не выбран')
                ->body('Дождитесь окончания загрузки файла или выберите его снова.')
                ->danger()
                ->send();

            return;
        }

        $result = app(ProductExcelImporter::class)->import($file);
        $this->importResult = $result;
        $this->loadImportPreview();

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
    }

    protected function resolveUploadedFile(): ?UploadedFile
    {
        $uploaded = $this->data['file'] ?? null;

        if (blank($uploaded)) {
            try {
                $snapshot = $this->getSchema('content')->getStateSnapshot();
                $uploaded = data_get($snapshot, 'data.file');
            } catch (\Throwable) {
                // ignore
            }
        }

        return ProductImportUploadedFileResolver::from($uploaded);
    }
}
