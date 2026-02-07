<?php

namespace App\Domain\Videos\Repositories;

use App\Domain\Videos\Contracts\Repositories\InputRepositoryInterface;
use App\Domain\Videos\Models\Input;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InputRepository implements InputRepositoryInterface
{
    public function __construct(
        private readonly Input $model
    ) {}

    public function paginate(int $userId, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        $perPage = max(1, min($perPage, 100));
        $page = max(1, $page);

        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->paginate(perPage: $perPage, page: $page);
    }

    public function findById(int $id): ?Input
    {
        return $this->model->newQuery()
            ->whereKey($id)
            ->first();
    }

    public function create(array $data): Input
    {
        $dataToCreate = array_merge(
            $data,
            [
                'status' => 'created',
            ]
        );
        /** @var Input $input */
        $input = $this->model->newQuery()->create($dataToCreate);

        return $input->refresh();
    }

    public function update(Input $input, array $data): Input
    {
        $input->fill($data);
        $input->save();

        return $input->refresh();
    }

    public function delete(Input $input): bool
    {
        return (bool) $input->delete();
    }
}
