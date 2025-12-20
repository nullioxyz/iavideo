<?php

namespace App\Domain\AIModels\UseCases;

use App\Domain\AIModels\Contracts\Repositories\PresetRepositoryInterface;
use App\Domain\AIModels\Requests\PresetsRequest;
use Illuminate\Pagination\LengthAwarePaginator;

final class PresetsListUseCase
{
    public function __construct(
        private readonly PresetRepositoryInterface $presetRepository
    ) {}

    public function execute(PresetsRequest $request, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->presetRepository->paginate(
            (int) $request->route('model'),
            $perPage, $page
        );
    }
}
