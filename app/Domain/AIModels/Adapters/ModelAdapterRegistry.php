<?php

namespace App\Domain\AIModels\Adapters;

use App\Domain\AIModels\Contracts\Adapters\ModelAdapterRegistryInterface;
use App\Domain\AIModels\Contracts\Infra\VideoModelAdapterInterface;
use InvalidArgumentException;

final class ModelAdapterRegistry implements ModelAdapterRegistryInterface
{
    /** @param VideoModelAdapterInterface[] $videoAdapters */
    public function __construct(private readonly array $videoAdapters) {}

    public function video(string $providerSlug, string $modelSlug): VideoModelAdapterInterface
    {
        $fallback = null;

        foreach ($this->videoAdapters as $adapter) {
            if ($adapter->providerSlug() === $providerSlug && $adapter->modelSlug() === $modelSlug) {
                return $adapter;
            }

            if ($adapter->providerSlug() === $providerSlug && $adapter->modelSlug() === '*') {
                $fallback = $adapter;
            }
        }

        if ($fallback instanceof VideoModelAdapterInterface) {
            return $fallback;
        }

        throw new InvalidArgumentException("Video adapter not found for {$providerSlug}:{$modelSlug}");
    }
}
