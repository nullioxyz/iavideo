<?php

namespace App\Domain\Broadcasting\Tests\Integration;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Tests\Traits\AuthenticatesWithJwt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BroadcastingAuthTest extends TestCase
{
    use AuthenticatesWithJwt;
    use RefreshDatabase;

    public function test_allows_authenticated_user_to_join_own_private_channel(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
        ]);

        $token = $this->loginAndGetToken($user);

        $response = $this->withJwt($token)->postJson('/api/broadcasting/auth', [
            'channel_name' => 'private-user.'.$user->getKey(),
            'socket_id' => '1234.5678',
        ]);

        $response->assertOk();
    }

    public function test_denies_authenticated_user_from_joining_other_private_channel(): void
    {
        [$me, $other] = User::factory()->count(2)->create([
            'active' => true,
            'password' => bcrypt('password'),
        ]);

        $token = $this->loginAndGetToken($me);

        $response = $this->withJwt($token)->postJson('/api/broadcasting/auth', [
            'channel_name' => 'private-user.'.$other->getKey(),
            'socket_id' => '1234.5678',
        ]);

        $response->assertForbidden();
    }
}
