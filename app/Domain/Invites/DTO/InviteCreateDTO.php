<?php

namespace App\Domain\Invites\DTO;

use Carbon\CarbonInterface;

final class InviteCreateDTO
{
    public function __construct(
        public readonly string $email,
        public readonly int $creditsGranted = 3,
        public readonly ?CarbonInterface $expiresAt = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            email: (string) $data['email'],
            creditsGranted: (int) ($data['credits_granted'] ?? 3),
            expiresAt: isset($data['expires_at']) ? now()->parse((string) $data['expires_at']) : now()->addDays(7),
        );
    }

    public function toArray(int $invitedByUserId, string $token): array
    {
        return [
            'email' => strtolower($this->email),
            'token' => $token,
            'credits_granted' => $this->creditsGranted,
            'invited_by_user_id' => $invitedByUserId,
            'used_at' => null,
            'expires_at' => $this->expiresAt,
        ];
    }
}
