<?php

namespace App\Domain\Videos\Repositories;

use App\Domain\Videos\Contracts\Repositories\PredictionWebhookRepositoryInterface;
use App\Domain\Videos\Models\Prediction;
use App\Domain\Videos\Models\PredictionOutput;

class PredictionWebhookRepository implements PredictionWebhookRepositoryInterface
{
    public function __construct(
        private readonly Prediction $predictionModel,
        private readonly PredictionOutput $predictionOutputModel,
    ) {}

    public function findByExternalId(string $externalId): ?Prediction
    {
        return $this->predictionModel->newQuery()
            ->where('external_id', $externalId)
            ->with('input.user')
            ->first();
    }

    public function updatePrediction(Prediction $prediction, array $data): Prediction
    {
        $prediction->fill($data);
        $prediction->save();

        return $prediction->refresh();
    }

    public function createOutput(Prediction $prediction, string $path, string $kind = 'video'): void
    {
        $this->predictionOutputModel->newQuery()->firstOrCreate(
            [
                'prediction_id' => $prediction->getKey(),
                'kind' => $kind,
                'path' => $path,
            ],
            []
        );
    }

    public function updateInputStatus(Prediction $prediction, string $status): void
    {
        $prediction->input()->update([
            'status' => $status,
        ]);
    }
}
