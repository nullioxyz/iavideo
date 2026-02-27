<?php

namespace App\Domain\Broadcasting\Events;

class UserGenerationLimitAlertBroadcast extends BroadcastAbstractEvent
{
    public function __construct(
        private readonly int $userId,
        private readonly int $dailyLimit,
        private readonly int $usedToday,
        private readonly int $remainingToday,
        private readonly bool $nearLimit,
        private readonly bool $limitReached,
    ) {}

    public function channelKey(): string
    {
        return 'user';
    }

    /**
     * @return array<string, int|string>
     */
    public function params(): array
    {
        return [
            'userId' => $this->userId,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return [
            'quota' => [
                'daily_limit' => $this->dailyLimit,
                'used_today' => $this->usedToday,
                'remaining_today' => $this->remainingToday,
                'near_limit' => $this->nearLimit,
                'limit_reached' => $this->limitReached,
            ],
        ];
    }
}

