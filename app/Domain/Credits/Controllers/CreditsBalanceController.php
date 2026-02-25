<?php

namespace App\Domain\Credits\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Credits\Resources\CreditBalanceResource;
use App\Domain\Credits\UseCases\GetCreditBalanceUseCase;
use App\Http\Controllers\Controller;

class CreditsBalanceController extends Controller
{
    public function __construct(
        private readonly GetCreditBalanceUseCase $useCase,
    ) {}

    public function __invoke(): CreditBalanceResource
    {
        $user = auth('api')->user();
        if (! $user instanceof User) {
            abort(401);
        }

        $balance = $this->useCase->execute($user);

        return new CreditBalanceResource($user, $balance);
    }
}
