<?php

namespace App\Domain\Auth\Tests\Integration;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Tests\Traits\AuthenticatesWithJwt;
use App\Domain\Languages\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPreferencesTest extends TestCase
{
    use AuthenticatesWithJwt;
    use RefreshDatabase;

    public function test_authenticated_user_can_update_language_and_theme_preferences(): void
    {
        $language = Language::query()->create([
            'title' => 'Italiano',
            'slug' => 'it',
            'is_default' => false,
            'active' => true,
        ]);

        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
        ]);

        $token = $this->loginAndGetToken($user);

        $response = $this->withJwt($token)->patchJson('/api/auth/preferences', [
            'language_id' => $language->getKey(),
            'theme_preference' => 'dark',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.language.id', $language->getKey());
        $response->assertJsonPath('data.theme_preference', 'dark');

        $this->assertDatabaseHas('users', [
            'id' => $user->getKey(),
            'language_id' => $language->getKey(),
            'theme_preference' => 'dark',
        ]);
    }
}
