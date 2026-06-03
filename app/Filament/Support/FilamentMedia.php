<?php

namespace App\Filament\Support;

use App\Support\Media\AdminMediaUpload;
use App\Support\Media\Media;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\View;
use Illuminate\Database\Eloquent\Model;

final class FilamentMedia
{
    public static function image(string $field, string $directory): FileUpload
    {
        $upload = FileUpload::make($field)
            ->disk(Media::disk())
            ->directory($directory)
            ->visibility('public')
            ->image()
            ->maxFiles(1)
            ->imagePreviewHeight('11.25rem')
            ->panelLayout('integrated')
            ->panelAspectRatio('16:9')
            ->deletable()
            ->openable()
            ->downloadable(false)
            ->placeholder('Нажмите или перетащите изображение')
            ->helperText(
                Media::usesBunny()
                    ? 'Текущее фото — блок выше. Замена: корзина слева от имени файла, затем новая загрузка.'
                    : 'Чтобы заменить: удалите файл (корзина) и загрузите новый.'
            );

        return static::configureCloudDisk($upload);
    }

    public static function gallery(string $field, string $directory, int $maxFiles = 50): FileUpload
    {
        $upload = FileUpload::make($field)
            ->disk(Media::disk())
            ->directory($directory)
            ->visibility('public')
            ->image()
            ->multiple()
            ->maxFiles($maxFiles)
            ->maxParallelUploads(5)
            ->reorderable()
            ->imagePreviewHeight('8rem')
            ->panelLayout('compact')
            ->deletable()
            ->downloadable(false)
            ->placeholder('Выберите несколько файлов или перетащите сюда')
            ->helperText('Alt для каждого фото подставится из названия товара (PL): «Название», «Название (2)»…')
            ->columnSpanFull();

        return static::configureCloudDisk($upload);
    }

    protected static function configureCloudDisk(FileUpload $upload): FileUpload
    {
        if (! Media::usesBunny()) {
            return $upload;
        }

        return $upload
            ->fetchFileInformation(false)
            ->saveUploadedFileUsing(
                fn (BaseFileUpload $component, $file): ?string => AdminMediaUpload::storeUpload($component, $file),
            )
            ->deleteUploadedFileUsing(function (BaseFileUpload $component, string $file): void {
                AdminMediaUpload::deleteUpload($file);
            })
            ->getUploadedFileUsing(
                fn (BaseFileUpload $component, string $file, string|array|null $storedFileNames): ?array => AdminMediaUpload::uploadedFilePayload(
                    $component,
                    $file,
                    $storedFileNames,
                ),
            )
            ->getOpenableFileUrlUsing(
                fn (BaseFileUpload $component, string $file): ?string => AdminMediaUpload::previewUrl($file),
            );
    }

    public static function currentImagePreview(string $attribute = 'image_path'): View
    {
        return View::make('filament.forms.admin-media-preview')
            ->visible(fn (?Model $record): bool => filled($record?->getAttribute($attribute)))
            ->viewData(fn (?Model $record): array => [
                'previewUrl' => AdminMediaUpload::previewUrl($record?->getAttribute($attribute)),
                'fileName' => basename((string) $record?->getAttribute($attribute)),
            ])
            ->columnSpanFull();
    }
}
