<?php

namespace App\Domain\Broadcasting\Events;

use App\Domain\Broadcasting\Contracts\BroadcastEventContract;
use App\Domain\Broadcasting\Support\ChannelRegistry;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

abstract class BroadcastAbstractEvent implements ShouldBroadcastNow, BroadcastEventContract
{
    use Dispatchable;
    use SerializesModels;

    public function broadcastOn(): Channel|PrivateChannel|PresenceChannel
    {
        return app(ChannelRegistry::class)->make($this->channelKey(), $this->params());
    }

    public function broadcastAs(): string
    {
        return $this->eventName();
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return $this->payload();
    }

    public function eventName(): string
    {
        return Str::kebab(class_basename(static::class));
    }
}
