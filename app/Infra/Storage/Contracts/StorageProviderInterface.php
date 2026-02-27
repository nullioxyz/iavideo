<?php

namespace App\Infra\Storage\Contracts;

use Illuminate\Http\UploadedFile;

interface StorageProviderInterface
{
    public function ingestTemporaryInput(int $inputId, UploadedFile $file): string;

    public function tempDisk(): string;

    public function mediaDisk(): string;

    public function mediaPrefix(): string;
}
