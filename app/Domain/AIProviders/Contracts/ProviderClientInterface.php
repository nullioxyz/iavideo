<?php

namespace App\Domain\AIProviders\Contracts;

use App\Domain\AIProviders\DTO\ProviderCreateResultDTO;
use App\Domain\AIProviders\DTO\ProviderGetResultDTO;

interface ProviderClientInterface
{
    public function providerSlug(): string;

    public function create(string $modelSlug, array $payload, array $headers = []): ProviderCreateResultDTO;

    public function get(string $externalId): ProviderGetResultDTO;

    public function cancel(string $externalId): ProviderGetResultDTO;
}
