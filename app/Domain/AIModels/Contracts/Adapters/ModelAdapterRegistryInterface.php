<?php

namespace App\Domain\AIModels\Contracts\Adapters;

use App\Domain\AIModels\Contracts\Infra\VideoModelAdapterInterface;

interface ModelAdapterRegistryInterface
{
    public function video(string $providerSlug, string $modelSlug): VideoModelAdapterInterface;
}
