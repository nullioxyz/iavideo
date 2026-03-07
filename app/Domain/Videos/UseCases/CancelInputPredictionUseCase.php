<?php

namespace App\Domain\Videos\UseCases;

use App\Domain\AIModels\Models\Model as AIModel;
use App\Domain\AIModels\Models\Preset;
use App\Domain\AIProviders\Infra\ProviderRegistry;
use App\Domain\Credits\Services\GenerationBillingService;
use App\Domain\Platforms\Models\Platform;
use App\Domain\Videos\Models\Input;
use App\Domain\Videos\Models\Prediction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class CancelInputPredictionUseCase
{
    public function __construct(
        private readonly ProviderRegistry $providerClients,
        private readonly GenerationBillingService $billingService,
    ) {}

    public function execute(int $userId, int $inputId): Prediction
    {
        /** @var Input $input */
        $input = Input::query()
            ->with(['preset', 'model.platform', 'prediction', 'user'])
            ->whereKey($inputId)
            ->where('user_id', $userId)
            ->first();

        if (! $input instanceof Input) {
            throw (new ModelNotFoundException())->setModel(Input::class, [$inputId]);
        }

        $prediction = $input->prediction;
        if (! $prediction instanceof Prediction || ! $prediction->external_id) {
            throw new RuntimeException("External ID not found for input {$inputId}");
        }

        $model = $input->model;
        if (! $model instanceof AIModel || ! $model->platform instanceof Platform) {
            throw new RuntimeException("Model/platform not configured for input {$inputId}");
        }

        $providerSlug = (string) $model->platform->slug;

        $client = $this->providerClients->get($providerSlug);
        $result = $client->cancel($prediction->external_id);

        if ($result->statusCode !== 200) {
            throw new RuntimeException('Failed to cancel prediction.');
        }

        DB::transaction(function () use ($inputId, $userId): void {
            /** @var Input|null $lockedInput */
            $lockedInput = Input::query()
                ->whereKey($inputId)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->with(['prediction', 'user'])
                ->first();

            if (! $lockedInput instanceof Input) {
                throw (new ModelNotFoundException())->setModel(Input::class, [$inputId]);
            }

            if ($lockedInput->status === Input::CANCELLED) {
                return;
            }

            $lockedInput->update(['status' => Input::CANCELLED]);
            $lockedInput->prediction()->update([
                'status' => Prediction::CANCELLED,
                'canceled_at' => now(),
            ]);

            $this->billingService->refundInput($lockedInput, 'Canceled video generation', [
                'refund_reason' => 'cancelled',
                'initiated_by' => 'user',
            ]);
        });

        $updatedPrediction = $input->prediction()->first();
        if (! $updatedPrediction instanceof Prediction) {
            throw new RuntimeException("Prediction not found for input {$inputId}");
        }

        return $updatedPrediction->refresh();
    }
}
