<?php

namespace App\Infra\Uploads;

use App\Infra\Contracts\InputImageIngestionInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InputImageIngestionService implements InputImageIngestionInterface
{
    public function ingest(int $inputId, UploadedFile $file): string
    {
        $uuid = (string) Str::uuid();
        $ext = $file->getClientOriginalExtension() ?: 'jpg';

        $tempPath = "tmp/inputs/{$inputId}/{$uuid}.{$ext}";

        Storage::disk('local')->putFileAs(
            dirname($tempPath),
            $file,
            basename($tempPath)
        );

        return $tempPath;
    }
}
