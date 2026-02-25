<?php

namespace App\Domain\Payments\DTO;

class WebhookPayloadDTO
{
    public function __construct(
        public readonly string $provider,
        public readonly string $eventId,
        public readonly string $externalId,
        public readonly string $status,
        public readonly ?string $failureCode,
        public readonly ?string $failureMessage,
        public readonly array $raw,
    ) {}

    public static function fromArray(string $provider, array $payload): self
    {
        return new self(
            provider: $provider,
            eventId: (string) ($payload['event_id'] ?? ''),
            externalId: (string) ($payload['external_id'] ?? ''),
            status: strtolower((string) ($payload['status'] ?? 'pending')),
            failureCode: isset($payload['failure_code']) ? (string) $payload['failure_code'] : null,
            failureMessage: isset($payload['failure_message']) ? (string) $payload['failure_message'] : null,
            raw: $payload,
        );
    }
}
