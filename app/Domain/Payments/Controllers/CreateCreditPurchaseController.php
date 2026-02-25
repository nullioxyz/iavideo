<?php

namespace App\Domain\Payments\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Payments\Requests\CreateCreditPurchaseRequest;
use App\Domain\Payments\Resources\CreditPurchaseOrderResource;
use App\Domain\Payments\UseCases\CreateCreditPurchaseUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CreateCreditPurchaseController extends Controller
{
    public function __construct(private readonly CreateCreditPurchaseUseCase $useCase) {}

    public function __invoke(CreateCreditPurchaseRequest $request): CreditPurchaseOrderResource|JsonResponse
    {
        $user = auth('api')->user();
        if (! $user instanceof User) {
            abort(401);
        }

        $idempotencyKey = $request->header('Idempotency-Key');

        try {
            $order = $this->useCase->execute(
                $user,
                (int) $request->integer('credits'),
                is_string($idempotencyKey) && $idempotencyKey !== '' ? $idempotencyKey : null,
            );
        } catch (\DomainException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return new CreditPurchaseOrderResource($order);
    }
}
