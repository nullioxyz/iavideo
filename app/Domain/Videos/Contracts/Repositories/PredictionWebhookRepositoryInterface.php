<?php

namespace App\Domain\Videos\Contracts\Repositories;

use App\Domain\Videos\Models\Prediction;

interface PredictionWebhookRepositoryInterface
{
    public function findByExternalId(string $externalId): ?Prediction;

    public function updatePrediction(Prediction $prediction, array $data): Prediction;

    public function createOutput(Prediction $prediction, string $path, string $kind = 'video'): void;

    public function updateInputStatus(Prediction $prediction, string $status): void;
}
