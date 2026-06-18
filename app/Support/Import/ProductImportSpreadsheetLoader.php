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
            $reader->setInputEncoding(static::detectCsvEncoding($file));
            $reader->setDelimiter(static::detectCsvDelimiter($file));

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

    protected static function detectCsvEncoding(UploadedFile $file): string
    {
        $path = $file->getRealPath() ?: $file->getPathname();
        $sample = @file_get_contents($path, false, null, 0, 8192) ?: '';

        if (str_starts_with($sample, "\xEF\xBB\xBF")) {
            return 'UTF-8';
        }

        if ($sample !== '' && mb_check_encoding($sample, 'UTF-8')) {
            return 'UTF-8';
        }

        if ($sample !== '' && mb_check_encoding($sample, 'Windows-1251')) {
            return 'CP1251';
        }

        return 'UTF-8';
    }

    protected static function detectCsvDelimiter(UploadedFile $file): string
    {
        $path = $file->getRealPath() ?: $file->getPathname();
        $line = @file_get_contents($path, false, null, 0, 2048) ?: '';

        if (str_starts_with($line, "\xEF\xBB\xBF")) {
            $line = substr($line, 3);
        }

        $firstLine = strtok($line, "\r\n") ?: '';

        $counts = [
            ';' => substr_count($firstLine, ';'),
            ',' => substr_count($firstLine, ','),
            "\t" => substr_count($firstLine, "\t"),
        ];

        arsort($counts);

        $delimiter = (string) array_key_first($counts);

        return $counts[$delimiter] > 0 ? $delimiter : ',';
    }
}
