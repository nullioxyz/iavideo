<?php

namespace App\Domain\Videos\UseCases;

use App\Domain\Videos\Contracts\Repositories\InputRepositoryInterface;
use App\Domain\Videos\DTO\InputCreateDTO;
use App\Domain\Videos\Events\InputCreated;
use App\Domain\Videos\Models\Input;
use App\Infra\Contracts\InputImageIngestionInterface;
use Illuminate\Http\UploadedFile;

final class CreateInputUseCase
{
    public function __construct(
        private readonly InputRepositoryInterface $inputRepository,
        private readonly InputImageIngestionInterface $ingestion,

    ) {}

    public function execute(int $userId, InputCreateDTO $dto, UploadedFile $file): Input
    {
        $input = $this->inputRepository->create(
            $dto->toArray($userId)
        );

        $tempPath = $this->ingestion->ingest($input->getKey(), $file);
        InputCreated::dispatch($input->getKey(), $tempPath);

        return $input;
    }
}
