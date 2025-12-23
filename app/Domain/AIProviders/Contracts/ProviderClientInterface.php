<?php

namespace App\Domain\AIProviders\Contracts;

use App\Domain\AIProviders\DTO\ProviderCreateResultDTO;
use App\Domain\AIProviders\DTO\ProviderGetResultDTO;

interface ProviderClientInterface
{
    public function providerSlug(): string;

    /** Cria prediction para um modelSlug específico (o client só envia) */
    public function create(string $modelSlug, array $payload, array $headers = []): ProviderCreateResultDTO;

    /** Consulta prediction pelo external_id */
    public function get(string $externalId): ProviderGetResultDTO;
}
