<?php

namespace App\Domain\Broadcasting\Events;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserSessionLoggedOutBroadcast extends BroadcastAbstractEvent implements ShouldQueue
{
    use Queueable;
    public function __construct(
        private readonly int $userId,
        private readonly string $reason = 'manual_logout',
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
            'type' => 'session_logged_out',
            'reason' => $this->reason,
            'logged_out_at' => now()->toISOString(),
        ];
    }

    public function eventName(): string
    {
        return 'session-logged-out';
    }
}
