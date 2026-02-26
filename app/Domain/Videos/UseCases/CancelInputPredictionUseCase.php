<?php

namespace App\Domain\Videos\UseCases;

use App\Domain\AIModels\Models\Model as AIModel;
use App\Domain\AIModels\Models\Preset;
use App\Domain\AIProviders\Infra\ProviderRegistry;
use App\Domain\Auth\Models\User;
use App\Domain\Credits\UseCases\RefundCreditUseCase;
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
        private readonly RefundCreditUseCase $refundCreditUseCase,
    ) {}

    public function execute(int $userId, int $inputId): Prediction
    {
        /** @var Input $input */
        $input = Input::query()
            ->with(['preset', 'preset.model', 'preset.model.platform', 'prediction'])
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

        $preset = $input->preset;
        if (! $preset instanceof Preset) {
            throw new RuntimeException("Preset not found for input {$inputId}");
        }

        $model = $preset->model;
        if (! $model instanceof AIModel || ! $model->platform instanceof Platform) {
            throw new RuntimeException("Model/platform not configured for preset {$preset->id}");
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
            $lockedInput->prediction()->update(['status' => Prediction::CANCELLED]);

            if (! $lockedInput->credit_debited) {
                return;
            }

            if (! $lockedInput->user instanceof User) {
                throw new RuntimeException("User not found for input {$inputId}");
            }

            $this->refundCreditUseCase->execute($lockedInput->user, [
                'reference_type' => 'input_video_generation_canceled',
                'reason' => 'Canceled video generation',
                'reference_id' => $lockedInput->id,
            ]);

            $lockedInput->update([
                'credit_debited' => false,
            ]);
        });

        $updatedPrediction = $input->prediction()->first();
        if (! $updatedPrediction instanceof Prediction) {
            throw new RuntimeException("Prediction not found for input {$inputId}");
        }

        return $updatedPrediction->refresh();
    }
}
