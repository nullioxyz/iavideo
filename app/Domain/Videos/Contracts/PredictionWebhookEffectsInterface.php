<?php

namespace App\Domain\Videos\Contracts;

use App\Domain\Videos\Models\Prediction;

interface PredictionWebhookEffectsInterface
{
    public function dispatchDownloadOutputs(Prediction $prediction): void;

    public function refundFailedGenerationIfDebited(Prediction $prediction): void;
}
