<?php

namespace App\Filament\Support;

use App\Infra\Storage\UploadStorageResolver;

class FilamentUpload
{
    public static function disk(): string
    {
        return UploadStorageResolver::tempDisk();
    }

    public static function mediaDisk(): string
    {
        return UploadStorageResolver::mediaDisk();
    }

    public static function directory(string $directory): string
    {
        return UploadStorageResolver::prefixedDirectory($directory);
    }
}
