<?php

namespace App\Domain\Credits\UseCases;

use App\Domain\Credits\Models\CreditLegder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListCreditStatementUseCase
{
    public function execute(int $userId, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        $perPage = max(1, min($perPage, 100));
        $page = max(1, $page);

        return CreditLegder::query()
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->paginate(perPage: $perPage, page: $page);
    }
}
