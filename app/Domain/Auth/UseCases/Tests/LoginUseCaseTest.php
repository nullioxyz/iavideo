<?php

namespace App\Domain\Auth\UseCases\Tests;

use App\Domain\Auth\Contracts\Infra\JwtAuthGatewayInterface;
use App\Domain\Auth\DTO\CredentialsDTO;
use App\Domain\Auth\DTO\TokenDTO;
use App\Domain\Auth\Exceptions\InvalidCredentialsException;
use App\Domain\Auth\Models\User;
use App\Domain\Observability\Support\StructuredActivityLogger;
use App\Domain\Auth\UseCases\LoginUseCase;
use Tests\TestCase;

class LoginUseCaseTest extends TestCase
{
    public function test_returns_token_dto_when_credentials_are_valid_and_user_is_active(): void
    {
        $logger = $this->createMock(StructuredActivityLogger::class);
        $logger->expects($this->once())
            ->method('log')
            ->with(
                'login_success',
                $this->isInstanceOf(User::class),
                $this->arrayHasKey('email')
            );

        $gateway = new class implements JwtAuthGatewayInterface
        {
            public function attempt(string $email, string $password): string
            {
                return 'token123';
            }

            public function user(): User
            {
                $u = new User;
                $u->active = true;

                return $u;
            }

            public function logout(): void
            {
                // noop
            }

            public function tokenTtlSeconds(): int
            {
                return 3600;
            }
        };

        $useCase = new LoginUseCase($gateway, $logger);

        $dto = new CredentialsDTO('a@b.com', 'password');
        $result = $useCase->execute($dto);

        $this->assertInstanceOf(TokenDTO::class, $result);
        $this->assertSame('token123', $result->accessToken);
        $this->assertSame(3600, $result->expiresInSeconds);
    }

    public function test_throws_validation_exception_when_credentials_are_invalid(): void
    {
        $this->expectException(InvalidCredentialsException::class);

        $logger = $this->createMock(StructuredActivityLogger::class);
        $logger->expects($this->once())
            ->method('log')
            ->with(
                'login_failed',
                null,
                $this->arrayHasKey('reason')
            );

        $gateway = new class implements JwtAuthGatewayInterface
        {
            public function attempt(string $email, string $password): ?string
            {
                return null;
            }

            public function user(): ?User
            {
                return null;
            }

            public function logout(): void
            {
                // noop
            }

            public function tokenTtlSeconds(): int
            {
                return 3600;
            }
        };

        $useCase = new LoginUseCase($gateway, $logger);

        $dto = new CredentialsDTO('a@b.com', 'wrong');

        $useCase->execute($dto);
    }
}
