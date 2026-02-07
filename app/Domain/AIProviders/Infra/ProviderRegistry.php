<?php

namespace App\Domain\AIProviders\Infra;

use App\Domain\AIProviders\Contracts\ProviderClientInterface;
use App\Domain\AIProviders\Contracts\ProviderRegistryInterface;
use InvalidArgumentException;

final class ProviderRegistry implements ProviderRegistryInterface
{
    /** @param array<string, ProviderClientInterface> $videoFromImageProviders */
    public function __construct(
        private readonly array $videoFromImageProviders
    ) {}

    public function get(string $providerSlug): ProviderClientInterface
    {
        $providerSlug = strtolower($providerSlug);
        if (! isset($this->videoFromImageProviders[$providerSlug])) {
            throw new InvalidArgumentException("Provider not registered: {$providerSlug}");
        }

        return $this->videoFromImageProviders[$providerSlug];
    }
}
