<?php

namespace App\Domain\Credits\Controllers;

use App\Domain\Credits\Requests\ListCreditStatementRequest;
use App\Domain\Credits\Resources\CreditStatementEntryResource;
use App\Domain\Credits\UseCases\ListCreditStatementUseCase;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaginatedResource;

class CreditsStatementController extends Controller
{
    public function __construct(
        private readonly ListCreditStatementUseCase $useCase,
    ) {}

    public function __invoke(ListCreditStatementRequest $request): PaginatedResource
    {
        $userId = (int) auth('api')->id();
        $perPage = (int) $request->integer('per_page', 15);
        $page = (int) $request->integer('page', 1);

        $paginator = $this->useCase->execute($userId, $perPage, $page);

        return new PaginatedResource($paginator, CreditStatementEntryResource::class);
    }
}
