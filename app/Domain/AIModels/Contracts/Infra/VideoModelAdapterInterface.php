<?php

namespace App\Domain\AIModels\Contracts\Infra;

use App\Domain\AIProviders\DTO\CreateVideoFromImageRequestDTO;
use App\Domain\AIProviders\DTO\ModelCreateCommandDTO;
use App\Domain\AIProviders\DTO\ModelGetResultDTO;

interface VideoModelAdapterInterface
{
    public function providerSlug(): string;

    public function modelSlug(): string;

    public function buildCreateCommand(CreateVideoFromImageRequestDTO $request): ModelCreateCommandDTO;

    public function mapGetResult(array $providerResponse): ModelGetResultDTO;
}
