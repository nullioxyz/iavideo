<?php

namespace App\Domain\Videos\Services;

use App\Domain\Credits\Services\GenerationBillingService;
use App\Domain\Videos\Contracts\PredictionWebhookEffectsInterface;
use App\Domain\Videos\Jobs\DownloadPredictionOutputsJob;
use App\Domain\Videos\Models\Input;
use App\Domain\Videos\Models\Prediction;
use Illuminate\Support\Facades\DB;

class PredictionWebhookEffects implements PredictionWebhookEffectsInterface
{
    public function __construct(
        private readonly GenerationBillingService $billingService,
    ) {}

    public function dispatchDownloadOutputs(Prediction $prediction): void
    {
        DownloadPredictionOutputsJob::dispatch($prediction->id)->onQueue('downloads');
    }

    public function refundUnsuccessfulGenerationIfCharged(Prediction $prediction, string $reason, array $metadata = []): void
    {
        DB::transaction(function () use ($prediction, $reason, $metadata): void {
            $input = Input::query()
                ->whereKey($prediction->input_id)
                ->lockForUpdate()
                ->with('user')
                ->first();

            if (! $input) {
                return;
            }

            $this->billingService->refundInput($input, $reason, array_merge([
                'prediction_id' => $prediction->getKey(),
                'prediction_status' => $prediction->status,
            ], $metadata));
        });
    }
}
