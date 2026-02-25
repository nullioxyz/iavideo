<?php

namespace App\Domain\Payments\Enums;

enum CreditPurchaseStatus: string
{
    case CREATED = 'created';
    case PENDING = 'pending';
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';
    case CANCELED = 'canceled';

    public function isTerminal(): bool
    {
        return in_array($this, [self::SUCCEEDED, self::FAILED, self::CANCELED], true);
    }
}
