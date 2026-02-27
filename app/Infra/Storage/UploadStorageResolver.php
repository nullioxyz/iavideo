<?php

namespace App\Infra\Storage;

class UploadStorageResolver
{
    public static function provider(): string
    {
        return (string) config(
            'uploads.provider',
            app()->environment('production') ? 's3' : 'local'
        );
    }

    public static function tempDisk(): string
    {
        return (string) config('uploads.temp_disk', 'local');
    }

    public static function mediaDisk(): string
    {
        if (self::provider() === 's3') {
            return (string) config('uploads.s3.media_disk', 'spaces');
        }

        return (string) config('uploads.local.media_disk', 'public');
    }

    public static function mediaPrefix(): string
    {
        $configured = self::provider() === 's3'
            ? (string) config('uploads.s3.media_prefix', 'inkai')
            : (string) config('uploads.local.media_prefix', '');

        $prefix = trim($configured, '/');

        if (self::provider() === 's3' && app()->environment('production') && $prefix === '') {
            return 'inkai';
        }

        return $prefix;
    }

    public static function prefixedDirectory(string $directory): string
    {
        $cleanDirectory = trim($directory, '/');
        $prefix = self::mediaPrefix();

        if ($prefix === '') {
            return $cleanDirectory;
        }

        return $cleanDirectory === '' ? $prefix : "{$prefix}/{$cleanDirectory}";
    }
}
