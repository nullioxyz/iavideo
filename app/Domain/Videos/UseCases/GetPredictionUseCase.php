<?php

namespace App\Domain\Videos\UseCases;

use App\Domain\AIProviders\Infra\ProviderRegistry;
use App\Domain\Videos\Models\Prediction;
use RuntimeException;

final class GetPredictionUseCase
{
    public function __construct(
        private readonly ProviderRegistry $providerClients,
    ) {}

    public function execute(Prediction $prediction): Prediction
    {
        /** @var Prediction $prediction */
        $prediction = Prediction::query()
            ->with(['model.platform'])
            ->where('external_id', $prediction->external_id)
            ->firstOrFail();

        $model = $prediction->model;
        $providerSlug = (string) $model->platform->slug;

        $client = $this->providerClients->get($providerSlug);
        $result = $client->get($prediction->external_id);

        if ($result->payload['id'] == '') {
            throw new RuntimeException('Provider did not return external prediction id.');
        }

        /** @var Prediction $prediction */
        $prediction->update([
            'status' => $result->payload['status'],
        ]);

        return $prediction->refresh();
    }
}
