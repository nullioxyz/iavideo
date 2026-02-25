<?php

namespace App\Domain\AIModels\UseCases;

use App\Domain\AIModels\Contracts\Repositories\PresetRepositoryInterface;

final class PresetFiltersUseCase
{
    public function __construct(
        private readonly PresetRepositoryInterface $presetRepository,
    ) {}

    /**
     * @return array{
     *     aspect_ratios:list<string>,
     *     tags:list<array{id:int,name:string,slug:string}>
     * }
     */
    public function execute(int $modelId): array
    {
        return $this->presetRepository->listFilterOptions($modelId);
    }
}
