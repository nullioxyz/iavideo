<?php

namespace App\Domain\Videos\Enums;

use Illuminate\Support\Carbon;

enum PredictionStatus: string
{
    case QUEUED = 'queued';
    case STARTING = 'starting';
    case SUBMITTING = 'submitting';
    case PROCESSING = 'processing';
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public static function fromWebhook(?string $status): self
    {
        $normalized = strtolower((string) ($status ?? self::PROCESSING->value));
        $normalized = $normalized === 'canceled' ? self::CANCELLED->value : $normalized;

        return self::tryFrom($normalized) ?? self::PROCESSING;
    }

    public function isTerminal(): bool
    {
        return match ($this) {
            self::SUCCEEDED, self::FAILED, self::CANCELLED, self::REFUNDED => true,
            default => false,
        };
    }

    public function startsProcessingWindow(): bool
    {
        return match ($this) {
            self::PROCESSING, self::STARTING => true,
            default => false,
        };
    }

    public function shouldMarkFinished(): bool
    {
        return match ($this) {
            self::SUCCEEDED, self::FAILED, self::CANCELLED => true,
            default => false,
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function outcomeUpdate(Carbon $now, ?string $errorMessage): array
    {
        return match ($this) {
            self::FAILED => [
                'failed_at' => $now,
                'error_message' => $errorMessage,
            ],
            self::CANCELLED => [
                'canceled_at' => $now,
            ],
            default => [],
        };
    }
}
