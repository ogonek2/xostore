<?php

namespace App\Support\Import;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
final class ProductImportUploadedFileResolver
{
    public static function from(mixed $uploaded): ?UploadedFile
    {
        $uploaded = static::normalize($uploaded);

        if ($uploaded === null) {
            return null;
        }

        if ($uploaded instanceof UploadedFile) {
            return $uploaded;
        }

        if (! is_string($uploaded)) {
            return null;
        }

        $uploaded = trim($uploaded);

        if ($uploaded === '') {
            return null;
        }

        if (Storage::disk('local')->exists($uploaded)) {
            return static::makeUploadedFile(Storage::disk('local')->path($uploaded), $uploaded);
        }

        if (is_file($uploaded)) {
            return static::makeUploadedFile($uploaded, basename($uploaded));
        }

        foreach (['local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($uploaded)) {
                return static::makeUploadedFile(Storage::disk($disk)->path($uploaded), $uploaded);
            }
        }

        return null;
    }

    public static function normalize(mixed $uploaded): mixed
    {
        if ($uploaded instanceof UploadedFile) {
            return $uploaded;
        }

        if (! is_array($uploaded)) {
            return $uploaded;
        }

        if ($uploaded === []) {
            return null;
        }

        if (Arr::isAssoc($uploaded)) {
            $first = Arr::first($uploaded);

            return static::normalize($first);
        }

        foreach ($uploaded as $item) {
            $normalized = static::normalize($item);

            if ($normalized !== null && $normalized !== '') {
                return $normalized;
            }
        }

        return null;
    }

    protected static function makeUploadedFile(string $path, string $originalName): UploadedFile
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $mime = match ($extension) {
            'csv' => 'text/csv',
            'txt' => 'text/plain',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls' => 'application/vnd.ms-excel',
            default => null,
        };

        return new UploadedFile($path, basename($originalName), $mime, null, true);
    }
}
