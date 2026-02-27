<?php

namespace App\Infra\Storage;

use App\Domain\Videos\Models\Input;
use App\Infra\Storage\Contracts\StorageProviderInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class InputImageStorageService
{
    public function __construct(
        private readonly StorageProviderInterface $storageProvider,
    ) {}

    public function ingestTemporaryInput(int $inputId, UploadedFile $file): string
    {
        $uuid = (string) Str::uuid();
        $ext = $file->getClientOriginalExtension() ?: 'jpg';
        $tempPath = "tmp/inputs/{$inputId}/{$uuid}.{$ext}";

        Storage::disk($this->storageProvider->tempDisk())->putFileAs(
            dirname($tempPath),
            $file,
            basename($tempPath)
        );

        return $tempPath;
    }

    public function tempFileExists(string $tempPath): bool
    {
        return file_exists($this->absoluteTempPath($tempPath));
    }

    public function attachFromTemp(Input $input, string $tempPath): Media
    {
        $absolutePath = $this->absoluteTempPath($tempPath);
        Config::set('media-library.prefix', $this->storageProvider->mediaPrefix());

        $media = $input
            ->addMedia($absolutePath)
            ->usingName('start_image')
            ->usingFileName(basename($absolutePath))
            ->toMediaCollection('start_image', $this->storageProvider->mediaDisk());

        Storage::disk($this->storageProvider->tempDisk())->delete($tempPath);

        return $media;
    }

    private function absoluteTempPath(string $tempPath): string
    {
        return Storage::disk($this->storageProvider->tempDisk())->path($tempPath);
    }
}
