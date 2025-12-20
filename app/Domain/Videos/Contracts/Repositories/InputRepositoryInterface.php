<?php

namespace App\Domain\Videos\Contracts\Repositories;

use App\Domain\Videos\Models\Input;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InputRepositoryInterface
{
    public function paginate(int $userId, int $perPage = 15, int $page = 1): LengthAwarePaginator;

    public function findById(int $id): ?Input;

    public function create(array $data): Input;

    public function update(Input $input, array $data): Input;

    public function delete(Input $input): bool;
}
