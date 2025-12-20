<?php

namespace App\Infra\Contracts;

use Illuminate\Http\UploadedFile;

interface InputImageIngestionInterface
{
    public function ingest(int $inputId, UploadedFile $file): string;
}
