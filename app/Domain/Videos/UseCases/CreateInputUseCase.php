<?php

namespace App\Domain\Videos\UseCases;

use App\Domain\Auth\Models\User;
use App\Domain\Credits\UseCases\ReserveCreditUseCase;
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
        private readonly ReserveCreditUseCase $reserveCreditUseCase,

    ) {}

    public function execute(User $user, InputCreateDTO $dto, UploadedFile $file): Input
    {
        if ($this->reserveCreditUseCase->canCharge($user) === false) {
            throw new \Exception('Insufficient balance');
        }

        $input = $this->inputRepository->create(
            $dto->toArray($user->getKey())
        );

        $this->reserveCreditUseCase->execute($user, [
            'reason' => 'Charge for input creation',
            'reference_type' => 'input_creation',
            'reference_id' => $input->getKey(),
        ]);

        $tempPath = $this->ingestion->ingest($input->getKey(), $file);
        InputCreated::dispatch($input->getKey(), $tempPath);

        return $input;
    }
}
