<?php

namespace App\Domain\Auth\Tests\Traits;

use App\Domain\Auth\Models\User;

trait AuthenticatesWithJwt
{
    protected function loginAndGetToken(?User $user, string $password = 'password'): string
    {
        $user = $user ?? User::factory()->create([
            'password' => bcrypt($password),
            'active' => true,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertOk();

        return $response->json('data.access_token');
    }

    protected function withJwt(string $token): self
    {
        return $this->withHeader('Authorization', "Bearer {$token}");
    }
}
