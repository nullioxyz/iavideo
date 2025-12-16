<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\DTO\CredentialsDTO;
use App\Domain\Auth\Exceptions\InvalidCredentialsException;
use App\Domain\Auth\Requests\AuthRequest;
use App\Domain\Auth\Resources\TokenResource;
use App\Domain\Auth\UseCases\LoginUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

final class AuthController extends Controller
{
    public function __construct(
        private readonly LoginUseCase $login
    ) {}

    public function __invoke(AuthRequest $request)
    {
        try {
            $dto = CredentialsDTO::fromArray($request->validated());
            $tokenDto = $this->login->execute($dto);

        } catch (InvalidCredentialsException $e) {
            throw ValidationException::withMessages([
                'email' => [__('validation.invalid_credentials')],
            ]);
        }

        return new TokenResource($tokenDto);
    }
}
