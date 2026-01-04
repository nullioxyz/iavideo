<?php

namespace App\Domain\Videos\UseCases;

use App\Domain\AIModels\Contracts\Adapters\ModelAdapterRegistryInterface;
use App\Domain\AIProviders\Contracts\ProviderClientInterface;
use App\Domain\AIProviders\DTO\CreateVideoFromImageRequestDTO;
use App\Domain\AIProviders\Infra\ProviderRegistry;
use App\Domain\Videos\Models\Input;
use App\Domain\Videos\Models\Prediction;
use Illuminate\Support\Carbon;
use RuntimeException;

final class CreatePredictionForInputUseCase
{
    public function __construct(
        private readonly ProviderRegistry $providerClients,
        private readonly ModelAdapterRegistryInterface $adapters,
    ) {}

    public function execute(int $inputId): Prediction
    {
        /** @var Input $input */
        $input = Input::query()
            ->with(['preset', 'preset.model', 'preset.model.platform'])
            ->findOrFail($inputId);

        $preset = $input->preset;
        if (!$preset) {
            throw new RuntimeException("Preset not found for input {$inputId}");
        }

        $model = $preset->model;
        if (!$model || !$model->platform) {
            throw new RuntimeException("Model/platform not configured for preset {$preset->id}");
        }

        $providerSlug = (string) $model->platform->slug;
        $modelSlug = (string) $model->slug;
        $modelVersion = $model->version;
        
        $imageUrl = app()->environment('local', 'testing') ? 'https://solztt.com/lang/images?uuid=e5a4c343-b7cb-4d02-bf7a-9b23c09e44a8&size=lg&format=avif' : $input->getFirstMediaUrl();

        $normalized = new CreateVideoFromImageRequestDTO(
            modelSlug: $modelSlug,
            modelVersion: $modelVersion,
            imageUrl: $imageUrl,
            prompt: (string) $preset->prompt,
            negativePrompt: $preset->negative_prompt ?: null,
            aspectRatio: (string) ($preset->aspect_ratio ?? '9:16'),
            durationSeconds: (int) ($preset->duration_seconds ?? 5),
            extra: [
                'webhook' => route('webhook.replicate')
            ]
        );

        $adapter = $this->adapters->video($providerSlug, $modelSlug);
        $command = $adapter->buildCreateCommand($normalized);

        $client = $this->providerClients->get($providerSlug);
        $result = $client->create($modelSlug, $command->payload, $command->headers);

        if ($result->externalId === '') {
            throw new RuntimeException('Provider did not return external prediction id.');
        }

        /** @var Prediction $prediction */
        $prediction = Prediction::query()->create([
            'input_id' => $input->id,
            'model_id' => $model->id,
            'external_id' => $result->externalId,
            'status' => $result->status ?: 'submitting',
            'source' => 'web',
            'attempt' => 1,

            'queued_at' => Carbon::now(),

            'duration_seconds' => (int) ($preset->duration_seconds ?? 5),
            'cost_estimate_usd' => $preset->cost_estimate_usd,

            'request_payload' => $command->payload,
            'response_payload' => $result->responsePayload,
        ]);

        $input->update(['status' => 'processing']);

        return $prediction->refresh();
    }
}
