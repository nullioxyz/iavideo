<?php

namespace App\Domain\Videos\UseCases;

use App\Domain\Videos\DTO\PredictionWebhookDTO;
use App\Domain\Videos\Models\Prediction;
use App\Domain\Videos\Models\PredictionOutput;
use Illuminate\Support\Carbon;
use App\Domain\Videos\Jobs\DownloadPredictionOutputsJob;

final class ReceivePredictionWebhookUseCase
{
    public function execute(PredictionWebhookDTO $dto): Prediction
    {
        $prediction = Prediction::where('external_id', $dto->getId())->first();
        $payload = $dto->toArray();

        $status = (string) ($payload['status'] ?? 'processing');

        if (in_array($prediction->status, ['succeeded', 'failed', 'canceled', 'refunded'], true)) {
            return $prediction;
        }

        $update = $dto->prepareToSave(
            $prediction->input_id,
            $prediction->model_id,
            $prediction->source,
            $prediction->attempt,
            $dto->getOutput()
        );
        
        if (in_array($status, ['processing', 'starting']) && ! $prediction->started_at) {
            $update['started_at'] = Carbon::now();
        }

        if (in_array($status, ['succeeded', 'failed', 'canceled'], true)) {
            $update['finished_at'] = Carbon::now();

            if ($status === 'failed') {
                $update['failed_at'] = Carbon::now();
                $update['error_message'] = $payload['error'] ?? null;
            }
        }

        $prediction->update($update);

        if ($status === 'succeeded') {
            PredictionOutput::create([
                'prediction_id' => $prediction->getKey(),
                'kind' => 'video',
                'path' => $dto->getOutput() ?? 'empty-path',
            ]);

            $prediction->input()->update([
                'status' => 'done'
            ]);

            DownloadPredictionOutputsJob::dispatch($prediction->id)->onQueue('downloads');

        }

        return $prediction->refresh();
    }
}
