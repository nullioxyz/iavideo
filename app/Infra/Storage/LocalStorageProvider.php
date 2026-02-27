<?php

namespace App\Infra\Storage;

use App\Infra\Storage\Contracts\StorageProviderInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LocalStorageProvider implements StorageProviderInterface
{
    public function __construct(
        private readonly string $tempDisk = 'local',
        private readonly string $mediaDisk = 'public',
        private readonly string $mediaPrefix = '',
    ) {}

    public function ingestTemporaryInput(int $inputId, UploadedFile $file): string
    {
        $uuid = (string) Str::uuid();
        $ext = $file->getClientOriginalExtension() ?: 'jpg';
        $tempPath = "tmp/inputs/{$inputId}/{$uuid}.{$ext}";

        Storage::disk($this->tempDisk)->putFileAs(
            dirname($tempPath),
            $file,
            basename($tempPath)
        );

        return $tempPath;
    }

    public function tempDisk(): string
    {
        return $this->tempDisk;
    }

    public function mediaDisk(): string
    {
        return $this->mediaDisk;
    }

    public function mediaPrefix(): string
    {
        return trim($this->mediaPrefix, '/');
    }
}
