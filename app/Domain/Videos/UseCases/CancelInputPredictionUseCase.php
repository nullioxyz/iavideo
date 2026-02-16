<?php

namespace App\Domain\Videos\UseCases;

use App\Domain\AIProviders\Infra\ProviderRegistry;
use App\Domain\Credits\UseCases\RefundCreditUseCase;
use App\Domain\Videos\Models\Input;
use App\Domain\Videos\Models\Prediction;
use RuntimeException;

final class CancelInputPredictionUseCase
{
    public function __construct(
        private readonly ProviderRegistry $providerClients,
        private readonly RefundCreditUseCase $refundCreditUseCase,
    ) {}

    public function execute(int $inputId): Prediction
    {
        /** @var Input $input */
        $input = Input::query()
            ->with(['preset', 'preset.model', 'preset.model.platform', 'prediction'])
            ->findOrFail($inputId);

        if (! $input->prediction?->external_id) {
            throw new RuntimeException("External ID not found for input {$inputId}");
        }

        $preset = $input->preset;
        if (! $preset) {
            throw new RuntimeException("Preset not found for input {$inputId}");
        }

        $model = $preset->model;
        if (! $model || ! $model->platform) {
            throw new RuntimeException("Model/platform not configured for preset {$preset->id}");
        }

        $providerSlug = (string) $model->platform->slug;

        $client = $this->providerClients->get($providerSlug);
        $result = $client->cancel($input->prediction->external_id);

        if ($result->statusCode !== 200) {
            throw new RuntimeException('Failed to cancel prediction.');
        }

        $input->update(['status' => Input::CANCELLED]);
        $input->prediction()->update(['status' => Prediction::CANCELLED]);

        if ($input->credit_debited) {
            $this->refundCreditUseCase->execute($input->user, [
                'reference_type' => 'input_video_generation_canceled',
                'reason' => 'Canceled video generation',
                'reference_id' => $input->id,
            ]);

            $input->update([
                'credit_debited' => false,
            ]);
        }

        return $input->prediction->refresh();
    }
}
