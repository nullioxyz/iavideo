<?php

namespace App\Domain\Invites\DTO;

final class InviteRedeemDTO
{
    public function __construct(
        public readonly string $token,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            token: (string) $data['token'],
        );
    }
}
