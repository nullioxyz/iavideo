<?php

namespace App\Domain\Videos\UseCases;

use App\Domain\Broadcasting\Events\UserJobUpdatedBroadcast;
use App\Domain\Videos\Contracts\Repositories\InputRepositoryInterface;
use App\Domain\Videos\Models\Input;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class RenameInputTitleUseCase
{
    public function __construct(
        private readonly InputRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, int $inputId, ?string $title): Input
    {
        $input = $this->repository->findOwnedById($userId, $inputId);

        if (! $input instanceof Input) {
            throw (new ModelNotFoundException())->setModel(Input::class, [$inputId]);
        }

        $resolvedTitle = $title ?? $input->original_filename;

        $updated = $this->repository->update($input, [
            'title' => $resolvedTitle,
        ]);

        event(UserJobUpdatedBroadcast::fromInput($updated));

        return $updated;
    }
}
