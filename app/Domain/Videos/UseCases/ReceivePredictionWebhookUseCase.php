<?php

namespace App\Domain\Videos\UseCases;

use App\Domain\Credits\UseCases\RefundCreditUseCase;
use App\Domain\Videos\DTO\PredictionWebhookDTO;
use App\Domain\Videos\Jobs\DownloadPredictionOutputsJob;
use App\Domain\Videos\Models\Prediction;
use App\Domain\Videos\Models\PredictionOutput;
use Illuminate\Support\Carbon;

final class ReceivePredictionWebhookUseCase
{
    public function __construct(
        private readonly RefundCreditUseCase $refundCreditUseCase,
    ) {}

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

        $isFailed = false;
        $isCanceled = false;

        if (in_array($status, ['succeeded', 'failed', 'canceled'], true)) {
            $update['finished_at'] = Carbon::now();
            $isFailed = $status === 'failed';
            $isCanceled = $status === 'canceled';

            if ($isFailed) {
                $update['failed_at'] = Carbon::now();
                $update['error_message'] = $payload['error'] ?? null;
                $update['status'] = 'failed';
            }

            if ($isCanceled) {
                $update['canceled_at'] = Carbon::now();
                $update['status'] = 'canceled';
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
                'status' => 'done',
            ]);

            DownloadPredictionOutputsJob::dispatch($prediction->id)->onQueue('downloads');
        }

        if ($isFailed) {
            $prediction->input()->update([
                'status' => 'failed',
            ]);

            $prediction->update([
                'status' => 'failed',
            ]);

            $wasDebited = $prediction->input->credit_debited;

            if ($wasDebited) {
                $this->refundCreditUseCase->execute($prediction->input->user, [
                    'reference_type' => 'input_video_generation_failed',
                    'reason' => 'Failed video generation',
                    'reference_id' => $prediction->input->id,
                ]);
            }
        }

        return $prediction->refresh();
    }
}
