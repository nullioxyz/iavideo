<?php

namespace App\Domain\Broadcasting\Support\Tests;

use App\Domain\Broadcasting\Support\ChannelRegistry;
use Illuminate\Broadcasting\PrivateChannel;
use InvalidArgumentException;
use Tests\TestCase;

class ChannelRegistryTest extends TestCase
{
    public function test_it_builds_channel_name_from_pattern_and_params(): void
    {
        config()->set('broadcasting_channels.user', [
            'pattern' => 'user.{userId}',
            'type' => 'private',
        ]);

        $registry = new ChannelRegistry;

        $name = $registry->name('user', ['userId' => 10]);

        $this->assertSame('user.10', $name);
    }

    public function test_it_makes_private_channel_instance(): void
    {
        config()->set('broadcasting_channels.user', [
            'pattern' => 'user.{userId}',
            'type' => 'private',
        ]);

        $registry = new ChannelRegistry;

        $channel = $registry->make('user', ['userId' => 10]);

        $this->assertInstanceOf(PrivateChannel::class, $channel);
    }

    public function test_it_throws_when_channel_key_is_not_defined(): void
    {
        config()->set('broadcasting_channels', []);

        $registry = new ChannelRegistry;

        $this->expectException(InvalidArgumentException::class);

        $registry->def('missing');
    }
}
