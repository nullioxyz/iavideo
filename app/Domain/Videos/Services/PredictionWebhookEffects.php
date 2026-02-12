<?php

namespace App\Domain\Videos\Services;

use App\Domain\Credits\UseCases\RefundCreditUseCase;
use App\Domain\Videos\Contracts\PredictionWebhookEffectsInterface;
use App\Domain\Videos\Jobs\DownloadPredictionOutputsJob;
use App\Domain\Videos\Models\Prediction;

class PredictionWebhookEffects implements PredictionWebhookEffectsInterface
{
    public function __construct(
        private readonly RefundCreditUseCase $refundCreditUseCase,
    ) {}

    public function dispatchDownloadOutputs(Prediction $prediction): void
    {
        DownloadPredictionOutputsJob::dispatch($prediction->id)->onQueue('downloads');
    }

    public function refundFailedGenerationIfDebited(Prediction $prediction): void
    {
        $prediction->loadMissing('input.user');

        if (! $prediction->input?->credit_debited) {
            return;
        }

        $this->refundCreditUseCase->execute($prediction->input->user, [
            'reference_type' => 'input_video_generation_failed',
            'reason' => 'Failed video generation',
            'reference_id' => $prediction->input->id,
        ]);
    }
}
