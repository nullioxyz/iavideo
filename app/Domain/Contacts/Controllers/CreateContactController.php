<?php

namespace App\Domain\Contacts\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Contacts\Requests\CreateContactRequest;
use App\Domain\Contacts\UseCases\CreateContactUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CreateContactController extends Controller
{
    public function __construct(
        private readonly CreateContactUseCase $useCase,
    ) {}

    public function __invoke(CreateContactRequest $request): JsonResponse
    {
        $user = auth('api')->user();
        $isUser = $user instanceof User;

        $contact = $this->useCase->execute($request->validated(), $isUser);

        return response()->json([
            'data' => [
                'id' => $contact->getKey(),
            ],
        ], 201);
    }
}

