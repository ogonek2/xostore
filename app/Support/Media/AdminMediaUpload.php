<?php

namespace App\Support\Media;

use Filament\Forms\Components\BaseFileUpload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final class AdminMediaUpload
{
    public static function previewUrl(?string $path): ?string
    {
        if ($path === null || trim($path) === '' || ! AdminMediaPaths::isAllowed($path)) {
            return null;
        }

        return route('admin.media.preview', ['path' => $path]);
    }

    /**
     * @return array{name: string, size: int, type: string, url: string}|null
     */
    public static function uploadedFilePayload(
        BaseFileUpload $component,
        string $file,
        string|array|null $storedFileNames,
    ): ?array {
        $url = static::previewUrl($file);

        if ($url === null) {
            return null;
        }

        $name = $component->isMultiple()
            ? (($storedFileNames[$file] ?? null) ?? basename($file))
            : ($storedFileNames ?? basename($file));

        return [
            'name' => is_string($name) ? $name : basename($file),
            'size' => 204_800,
            'type' => static::mimeTypeForPath($file),
            'url' => $url,
        ];
    }

    public static function storeUpload(BaseFileUpload $component, TemporaryUploadedFile $file): ?string
    {
        $directory = trim((string) $component->getDirectory(), '/');

        if ($directory === '') {
            return null;
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $path = $directory.'/'.Str::ulid().'.'.$extension;

        if (! AdminMediaPaths::isAllowed($path)) {
            return null;
        }

        $realPath = $file->getRealPath();

        if (! is_string($realPath) || ! is_readable($realPath)) {
            return null;
        }

        $stream = fopen($realPath, 'rb');

        if ($stream === false) {
            return null;
        }

        try {
            Storage::disk(Media::disk())->writeStream($path, $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        return $path;
    }

    public static function deleteUpload(string $file): void
    {
        if (! AdminMediaPaths::isAllowed($file)) {
            return;
        }

        Storage::disk(Media::disk())->delete($file);
    }

    public static function mimeTypeForPath(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'avif' => 'image/avif',
            default => 'image/jpeg',
        };
    }
}
