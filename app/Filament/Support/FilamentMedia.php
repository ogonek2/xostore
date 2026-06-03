<?php

namespace App\Filament\Support;

use App\Support\Media\Media;
use App\Support\Media\MediaUrl;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;

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
            ->imagePreviewHeight('12rem')
            ->panelLayout('integrated')
            ->deletable()
            ->openable()
            ->removeUploadedFileButtonPosition('right bottom')
            ->uploadButtonPosition('left bottom')
            ->uploadProgressIndicatorPosition('center bottom')
            ->placeholder('Нажмите или перетащите изображение')
            ->helperText(
                Media::usesBunny()
                    ? 'Чтобы заменить картинку: нажмите «Удалить» (корзина на превью), затем загрузите новый файл.'
                    : 'Чтобы заменить: удалите текущий файл и загрузите новый.'
            );

        if (Media::usesBunny()) {
            $upload
                ->fetchFileInformation(false)
                ->getUploadedFileUsing(static function (
                    BaseFileUpload $component,
                    string $file,
                    string|array|null $storedFileNames,
                ): ?array {
                    $url = MediaUrl::fromPath($file, Media::disk());

                    if ($url === null) {
                        return null;
                    }

                    $name = $component->isMultiple()
                        ? (($storedFileNames[$file] ?? null) ?? basename($file))
                        : ($storedFileNames ?? basename($file));

                    return [
                        'name' => is_string($name) ? $name : basename($file),
                        'size' => 0,
                        'type' => 'image/*',
                        'url' => $url,
                    ];
                })
                ->getOpenableFileUrlUsing(
                    fn (BaseFileUpload $component, string $file): ?string => MediaUrl::fromPath($file, Media::disk()),
                );
        }

        return $upload;
    }
}
