<?php

namespace App\Domain\Invites\Database\Factories;

use App\Domain\Auth\Models\User;
use App\Domain\Invites\Models\Invite;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InviteFactory extends Factory
{
    protected $model = Invite::class;

    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'token' => Str::uuid()->toString(),
            'credits_granted' => 3,
            'invited_by_user_id' => User::factory(),
            'used_at' => null,
            'expires_at' => now()->addDays(7),
        ];
    }
}
