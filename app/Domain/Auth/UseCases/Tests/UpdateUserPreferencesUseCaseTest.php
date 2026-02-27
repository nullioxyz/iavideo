<?php

namespace App\Domain\Auth\UseCases\Tests;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\UseCases\UpdateUserPreferencesUseCase;
use App\Domain\Languages\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateUserPreferencesUseCaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_user_language_and_theme_preferences(): void
    {
        $language = Language::query()->create([
            'title' => 'Italiano',
            'slug' => 'it',
            'is_default' => false,
            'active' => true,
        ]);

        $user = User::factory()->create([
            'theme_preference' => 'system',
        ]);

        $useCase = new UpdateUserPreferencesUseCase;

        $updated = $useCase->execute($user, [
            'language_id' => $language->getKey(),
            'theme_preference' => 'dark',
        ]);

        $this->assertSame((int) $language->getKey(), (int) $updated->language_id);
        $this->assertSame('dark', (string) $updated->theme_preference);
    }

    public function test_it_keeps_existing_values_when_payload_is_empty(): void
    {
        $user = User::factory()->create([
            'theme_preference' => 'light',
        ]);

        $useCase = new UpdateUserPreferencesUseCase;

        $updated = $useCase->execute($user, []);

        $this->assertSame((string) $user->theme_preference, (string) $updated->theme_preference);
        $this->assertSame((int) $user->language_id, (int) $updated->language_id);
    }
}
