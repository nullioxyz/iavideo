<?php

namespace App\Domain\Broadcasting\Support;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use InvalidArgumentException;

class ChannelRegistry
{
    /**
     * @return array<string, mixed>
     */
    public function def(string $key): array
    {
        $def = config("broadcasting_channels.$key");

        if (! $def) {
            throw new InvalidArgumentException("Channel key [{$key}] not found.");
        }

        return $def;
    }

    /**
     * @param  array<string, int|string>  $params
     */
    public function name(string $key, array $params = []): string
    {
        $pattern = (string) ($this->def($key)['pattern'] ?? '');

        foreach ($params as $paramKey => $value) {
            $pattern = str_replace('{'.$paramKey.'}', (string) $value, $pattern);
        }

        return $pattern;
    }

    /**
     * @param  array<string, int|string>  $params
     */
    public function make(string $key, array $params = []): Channel|PrivateChannel|PresenceChannel
    {
        $def = $this->def($key);
        $name = $this->name($key, $params);

        return match ($def['type'] ?? 'public') {
            'private' => new PrivateChannel($name),
            'presence' => new PresenceChannel($name),
            default => new Channel($name),
        };
    }
}
