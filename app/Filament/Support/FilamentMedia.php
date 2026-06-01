<?php

namespace App\Filament\Support;

use App\Support\Media\Media;
use Filament\Forms\Components\FileUpload;

final class FilamentMedia
{
    public static function image(string $field, string $directory): FileUpload
    {
        return FileUpload::make($field)
            ->disk(Media::disk())
            ->directory($directory)
            ->visibility('public')
            ->image();
    }
}
