<?php

namespace App\Domain\Auth\DTO;

final class CredentialsDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            email: (string) $data['email'],
            password: (string) $data['password'],
        );
    }
}
