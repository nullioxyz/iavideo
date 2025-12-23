<?php

namespace App\Domain\Videos\UseCases;

use App\Domain\AIModels\Contracts\Adapters\ModelAdapterRegistryInterface;
use App\Domain\AIProviders\Contracts\ProviderClientInterface;
use App\Domain\AIProviders\DTO\CreateVideoFromImageRequestDTO;
use App\Domain\AIProviders\Infra\ProviderRegistry;
use App\Domain\Videos\Models\Input;
use App\Domain\Videos\Models\Prediction;
use Illuminate\Support\Carbon;
use League\Uri\Http;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

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
