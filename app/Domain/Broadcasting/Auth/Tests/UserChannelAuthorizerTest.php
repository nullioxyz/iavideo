<?php

namespace App\Domain\Broadcasting\Auth\Tests;

use App\Domain\Auth\Models\User;
use App\Domain\Broadcasting\Auth\UserChannelAuthorizer;
use PHPUnit\Framework\TestCase;

class UserChannelAuthorizerTest extends TestCase
{
    public function test_it_allows_user_to_join_own_channel(): void
    {
        $authorizer = new UserChannelAuthorizer;
        $user = new User;
        $user->id = 7;

        $this->assertTrue($authorizer->join($user, 7));
    }

    public function test_it_denies_user_to_join_other_user_channel(): void
    {
        $authorizer = new UserChannelAuthorizer;
        $user = new User;
        $user->id = 7;

        $this->assertFalse($authorizer->join($user, 8));
    }
}
