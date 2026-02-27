<?php

namespace App\Infra\Uploads;

use App\Infra\Contracts\InputImageIngestionInterface;
use App\Infra\Storage\InputImageStorageService;
use Illuminate\Http\UploadedFile;

class InputImageIngestionService implements InputImageIngestionInterface
{
    public function __construct(
        private readonly InputImageStorageService $imageStorage,
    ) {}

    public function ingest(int $inputId, UploadedFile $file): string
    {
        return $this->imageStorage->ingestTemporaryInput($inputId, $file);
    }
}
