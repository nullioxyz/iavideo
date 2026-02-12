<?php

namespace App\Domain\Videos\Jobs;

use App\Domain\Videos\Models\Prediction;
use App\Domain\Videos\Models\PredictionOutput;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class DownloadPredictionOutputsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 180;

    public int $uniqueFor = 3600;

    public function __construct(public readonly int $predictionId) {}

    public function uniqueId(): string
    {
        return "prediction-output-download:{$this->predictionId}";
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(): void
    {
        $prediction = Prediction::query()->findOrFail($this->predictionId);

        $output = PredictionOutput::query()
            ->where('prediction_id', $prediction->id)
            ->where('kind', 'video')
            ->first();

        if (! $output) {
            Log::warning('prediction.output.download.skipped_output_not_found', [
                'prediction_id' => $prediction->id,
                'external_id' => $prediction->external_id,
            ]);

            return;
        }

        if ($output->getMediaFile()) {
            Log::info('prediction.output.download.skipped_already_attached', [
                'prediction_id' => $prediction->id,
                'prediction_output_id' => $output->id,
                'external_id' => $prediction->external_id,
            ]);

            return;
        }

        $url = $output->path;

        Log::info('prediction.output.download.started', [
            'prediction_id' => $prediction->id,
            'prediction_output_id' => $output->id,
            'external_id' => $prediction->external_id,
            'url' => $url,
        ]);

        $res = Http::timeout(120)->retry(3, 1000)->get($url);

        if ($res->failed()) {
            throw new \RuntimeException("Failed to download prediction output from {$url} (status {$res->status()})");
        }

        $bytes = $res->body();

        $path = (string) parse_url($url, PHP_URL_PATH);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $allowed = ['mp4', 'mov', 'webm', 'gif'];
        if (! in_array($ext, $allowed, true)) {
            $ext = 'mp4';
        }

        $filename = Str::uuid()->toString().'.'.$ext;

        $media = $output
            ->addMediaFromUrl($url)
            ->usingName('prediction_output')
            ->usingFileName($filename)
            ->withCustomProperties([
                'mime_type' => $res->header('Content-Type'),
            ])
            ->toMediaCollection('file');

        $media->update([
            'mime_type' => $res->header('Content-Type'),
        ]);

        $output->update([
            'mime_type' => $res->header('Content-Type'),
            'size_bytes' => strlen($bytes),
        ]);

        Log::info('prediction.output.download.completed', [
            'prediction_id' => $prediction->id,
            'prediction_output_id' => $output->id,
            'external_id' => $prediction->external_id,
            'mime_type' => $res->header('Content-Type'),
            'size_bytes' => strlen($bytes),
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('prediction.output.download.failed', [
            'prediction_id' => $this->predictionId,
            'exception' => $exception::class,
            'message' => $exception->getMessage(),
        ]);
    }
}
