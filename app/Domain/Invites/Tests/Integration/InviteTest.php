<?php

namespace App\Domain\Invites\Tests\Integration;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Tests\Traits\AuthenticatesWithJwt;
use App\Domain\Credits\Models\CreditLegder;
use App\Domain\Invites\Models\Invite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InviteTest extends TestCase
{
    use AuthenticatesWithJwt;
    use RefreshDatabase;

    public function test_validate_invite_returns_true_for_valid_hash(): void
    {
        Invite::factory()->create([
            'token' => 'valid-hash-123',
            'used_at' => null,
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->postJson('/api/invites/validate', [
            'hash' => 'valid-hash-123',
        ]);

        $response->assertOk();
        $this->assertSame('true', trim((string) $response->getContent()));
    }

    public function test_validate_invite_returns_false_for_invalid_or_expired_or_used_hash(): void
    {
        Invite::factory()->create([
            'token' => 'used-hash-123',
            'used_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        Invite::factory()->create([
            'token' => 'expired-hash-123',
            'used_at' => null,
            'expires_at' => now()->subDay(),
        ]);

        $notFound = $this->postJson('/api/invites/validate', [
            'hash' => 'unknown-hash-123',
        ]);

        $notFound->assertOk();
        $this->assertSame('false', trim((string) $notFound->getContent()));

        $used = $this->postJson('/api/invites/validate', [
            'hash' => 'used-hash-123',
        ]);

        $used->assertOk();
        $this->assertSame('false', trim((string) $used->getContent()));

        $expired = $this->postJson('/api/invites/validate', [
            'hash' => 'expired-hash-123',
        ]);

        $expired->assertOk();
        $this->assertSame('false', trim((string) $expired->getContent()));
    }

    public function test_redeem_invite_adds_credits_and_marks_used(): void
    {
        $inviter = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'credit_balance' => 3,
        ]);

        $invitedUser = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'email' => 'guest@example.com',
            'credit_balance' => 1,
            'invited_by_user_id' => null,
        ]);

        $invite = Invite::factory()->create([
            'email' => 'guest@example.com',
            'token' => 'invite-token-123',
            'credits_granted' => 4,
            'invited_by_user_id' => $inviter->getKey(),
            'used_at' => null,
            'expires_at' => now()->addDay(),
        ]);

        $token = $this->loginAndGetToken($invitedUser);

        $response = $this->withJwt($token)->postJson('/api/invites/redeem', [
            'token' => 'invite-token-123',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('invites', [
            'id' => $invite->getKey(),
            'token' => 'invite-token-123',
        ]);

        $this->assertNotNull($invite->fresh()->used_at);

        $invitedUser->refresh();
        $this->assertSame(5, (int) $invitedUser->credit_balance);
        $this->assertSame($inviter->getKey(), (int) $invitedUser->invited_by_user_id);

        $ledger = CreditLegder::query()
            ->where('user_id', $invitedUser->getKey())
            ->where('reference_type', 'invite_redemption')
            ->where('reference_id', $invite->getKey())
            ->first();

        $this->assertNotNull($ledger);
        $this->assertSame(4, (int) $ledger->delta);
        $this->assertSame(5, (int) $ledger->balance_after);
    }
}
