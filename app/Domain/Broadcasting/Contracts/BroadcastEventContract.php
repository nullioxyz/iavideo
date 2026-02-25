<?php

namespace App\Domain\Broadcasting\Contracts;

interface BroadcastEventContract
{
    public function channelKey(): string;

    /**
     * @return array<string, int|string>
     */
    public function params(): array;

    /**
     * @return array<string, mixed>
     */
    public function payload(): array;

    public function eventName(): string;
}
