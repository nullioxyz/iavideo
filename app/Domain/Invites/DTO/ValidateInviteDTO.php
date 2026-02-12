<?php

namespace App\Domain\Invites\DTO;

final class ValidateInviteDTO
{
    public function __construct(
        public readonly string $hash,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            hash: (string) $data['hash'],
        );
    }
}
