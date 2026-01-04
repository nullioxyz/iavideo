<?php

namespace App\Domain\Videos\Jobs;

use App\Domain\Videos\Models\Prediction;
use App\Domain\Videos\Models\PredictionOutput;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DownloadPredictionOutputsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly int $predictionId) {}

    public function handle(): void
    {
        $prediction = Prediction::query()->findOrFail($this->predictionId);

        $output = PredictionOutput::query()
            ->where('prediction_id', $prediction->id)
            ->where('kind', 'video')
            ->first();

        $url = $output->path;
        $res = Http::timeout(120)->get($url);

        $bytes = $res->body();

        $path = (string) parse_url($url, PHP_URL_PATH);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $allowed = ['mp4', 'mov', 'webm', 'gif'];
        if (! in_array($ext, $allowed, true)) {
            $ext = 'mp4';
        }

        $filename = Str::uuid()->toString().'.'.$ext;

        $output
            ->addMediaFromUrl($url)
            ->usingName('prediction_output')
            ->usingFileName($filename)
            ->withCustomProperties([
                'mime_type' => $res->header('Content-type')
            ])
            ->toMediaCollection('file');

        $output->getMediaFile()->update([
            'mime_type' => $res->header('Content-type')
        ]);

        $output->update([
            'mime_type' => $res->header('Content-type'),
            'size_bytes' => strlen($bytes),
        ]);
    }
}
