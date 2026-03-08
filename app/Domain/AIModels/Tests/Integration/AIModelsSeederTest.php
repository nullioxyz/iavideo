<?php

namespace App\Domain\AIModels\Tests\Integration;

use App\Domain\AIModels\Models\Model as AIModel;
use Database\Seeders\AIModelsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AIModelsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_default_models_and_is_idempotent(): void
    {
        $seeder = app(AIModelsSeeder::class);
        $seeder->run();
        $seeder->run();

        $this->assertSame(9, AIModel::query()->count());

        $this->assertDatabaseHas('models', [
            'provider_model_key' => 'google/veo-3',
            'cost_per_second_usd' => '0.4000',
            'credits_per_second' => '1.1429',
            'active' => true,
            'public_visible' => true,
        ]);

        $this->assertDatabaseHas('models', [
            'provider_model_key' => 'kwaivgi/kling-v2.5-turbo-pro',
            'cost_per_second_usd' => '0.0700',
            'credits_per_second' => '0.2000',
            'active' => true,
            'public_visible' => true,
        ]);

        $this->assertDatabaseHas('models', [
            'provider_model_key' => 'wan-video/wan-2.2-i2v-fast',
            'cost_per_second_usd' => null,
            'credits_per_second' => null,
            'active' => false,
            'public_visible' => false,
        ]);
    }
}
