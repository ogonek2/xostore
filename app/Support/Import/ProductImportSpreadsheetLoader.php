<?php

namespace App\Support\Import;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

final class ProductImportSpreadsheetLoader
{
    /** @var list<string> */
    public const ACCEPTED_MIME_TYPES = [
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-excel',
        'text/csv',
        'text/plain',
        'application/csv',
        'text/comma-separated-values',
    ];

    public static function load(UploadedFile $file): Spreadsheet
    {
        $path = $file->getRealPath() ?: $file->getPathname();

        if (static::isCsvFile($file)) {
            $reader = IOFactory::createReader(IOFactory::READER_CSV);
            $reader->setReadDataOnly(true);

            return $reader->load($path);
        }

        return IOFactory::load($path);
    }

    public static function isCsvFile(UploadedFile $file): bool
    {
        $extension = Str::lower($file->getClientOriginalExtension());

        if (in_array($extension, ['csv', 'txt'], true)) {
            return true;
        }

        $mime = Str::lower((string) $file->getMimeType());

        return in_array($mime, [
            'text/csv',
            'text/plain',
            'application/csv',
            'text/comma-separated-values',
        ], true);
    }
}
