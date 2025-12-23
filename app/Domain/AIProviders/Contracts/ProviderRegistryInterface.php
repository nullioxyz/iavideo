<?php

namespace App\Domain\AIProviders\Contracts;

interface ProviderRegistryInterface
{
    public function get(string $providerSlug): ProviderClientInterface;
}
