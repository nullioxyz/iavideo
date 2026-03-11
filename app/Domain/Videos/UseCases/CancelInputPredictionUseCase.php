<?php

namespace App\Domain\Videos\UseCases;

use App\Domain\AIModels\Models\Model as AIModel;
use App\Domain\AIModels\Models\Preset;
use App\Domain\AIProviders\Infra\ProviderRegistry;
use App\Domain\Broadcasting\Events\UserJobUpdatedBroadcast;
use App\Domain\Credits\Services\GenerationBillingService;
use App\Domain\Platforms\Models\Platform;
use App\Domain\Videos\Models\Input;
use App\Domain\Videos\Models\Prediction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class CancelInputPredictionUseCase
{
    public function __construct(
        private readonly ProviderRegistry $providerClients,
        private readonly GenerationBillingService $billingService,
    ) {}

    public function execute(int $userId, int $inputId): Input
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
            return $this->cancelLocally($userId, $inputId);
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

        return $this->cancelLocally($userId, $inputId);
    }

    private function cancelLocally(int $userId, int $inputId): Input
    {
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

            $prediction = $lockedInput->prediction;
            if ($prediction instanceof Prediction) {
                $prediction->update([
                    'status' => Prediction::CANCELLED,
                    'canceled_at' => $prediction->canceled_at ?? Carbon::now(),
                ]);
            }

            $this->billingService->refundInput($lockedInput, 'Canceled video generation', [
                'refund_reason' => 'cancelled',
                'initiated_by' => 'user',
            ]);
        });

        /** @var Input|null $updatedInput */
        $updatedInput = Input::query()
            ->with([
                'model.platform',
                'preset.model.platform',
                'prediction.outputs',
                'user',
            ])
            ->whereKey($inputId)
            ->where('user_id', $userId)
            ->first();

        if (! $updatedInput instanceof Input) {
            throw new RuntimeException("Input not found after cancellation {$inputId}");
        }

        try {
            event(UserJobUpdatedBroadcast::fromInput($updatedInput));
        } catch (\Throwable) {
            // Broadcasting is a non-critical side-effect.
        }

        return $updatedInput;
    }
}
