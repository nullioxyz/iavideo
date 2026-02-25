<?php

namespace App\Domain\Broadcasting\Providers;

use App\Domain\Auth\Models\User;
use App\Domain\Broadcasting\Contracts\ChannelAuthorizerContract;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class BroadcastingChannelsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        foreach (config('broadcasting_channels', []) as $def) {
            $pattern = (string) ($def['pattern'] ?? '');
            $authorizer = $def['authorizer'] ?? null;

            Broadcast::channel($pattern, function () use ($authorizer) {
                if (! $authorizer) {
                    return false;
                }

                $args = func_get_args();
                $authUser = auth('api')->user();

                if (! $authUser instanceof User) {
                    return false;
                }

                $instance = app($authorizer);
                if (! $instance instanceof ChannelAuthorizerContract) {
                    throw new InvalidArgumentException(
                        sprintf('%s should implement %s', $authorizer, ChannelAuthorizerContract::class)
                    );
                }

                // Signature from Broadcast::channel callback: (User $user, ...$params)
                $params = array_slice($args, 1);

                return $instance->join($authUser, ...$params);
            });
        }
    }
}
