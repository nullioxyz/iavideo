<?php

namespace App\Domain\Videos\Services;

use App\Domain\Auth\Models\User;
use App\Domain\Credits\UseCases\RefundCreditUseCase;
use App\Domain\Videos\Contracts\PredictionWebhookEffectsInterface;
use App\Domain\Videos\Jobs\DownloadPredictionOutputsJob;
use App\Domain\Videos\Models\Input;
use App\Domain\Videos\Models\Prediction;
use Illuminate\Support\Facades\DB;

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
        DB::transaction(function () use ($prediction): void {
            $input = Input::query()
                ->whereKey($prediction->input_id)
                ->lockForUpdate()
                ->with('user')
                ->first();

            if (! $input || ! $input->credit_debited) {
                return;
            }

            if (! $input->user instanceof User) {
                return;
            }

            $input->update([
                'credit_debited' => false,
            ]);

            $this->refundCreditUseCase->execute($input->user, [
                'reference_type' => 'input_video_generation_failed',
                'reason' => 'Failed video generation',
                'reference_id' => $input->id,
            ]);
        });
    }
}
