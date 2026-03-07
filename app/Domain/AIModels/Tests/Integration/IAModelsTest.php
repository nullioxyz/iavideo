<?php

namespace App\Domain\AIModels\Tests\Integration;

use App\Domain\AIModels\Models\Model as AIModel;
use App\Domain\AIModels\Models\Preset;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Tests\Traits\AuthenticatesWithJwt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IAModelsTest extends TestCase
{
    use AuthenticatesWithJwt;
    use RefreshDatabase;

    public function test_fetch_ia_models_pagination_returns_only_models_with_active_presets(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
        ])->first();

        $activeModel = AIModel::factory()->create([
            'active' => true,
        ]);

        $inactiveModel = AIModel::factory()->create([
            'active' => false,
        ]);

        $modelWithoutPreset = AIModel::factory()->create([
            'active' => true,
        ]);

        $modelWithInactivePreset = AIModel::factory()->create([
            'active' => true,
        ]);

        Preset::factory()->create([
            'default_model_id' => $activeModel->getKey(),
            'active' => true,
        ]);

        Preset::factory()->create([
            'default_model_id' => $modelWithInactivePreset->getKey(),
            'active' => false,
        ]);

        $token = $this->loginAndGetToken($user);

        $response = $this->withJwt($token)
            ->getJson('/api/models?per_page=15&page=1');

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'platform_id',
                    'name',
                    'slug',
                    'provider_model_key',
                    'version',
                    'active',
                    'public_visible',
                    'available_for_generation',
                    'cost_per_second_usd',
                    'sort_order',
                    'created_at',
                    'updated_at',
                ],
            ],
            'meta' => [
                'current_page',
                'from',
                'to',
                'per_page',
                'total',
                'last_page',
            ],
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
        ]);

        $this->assertCount(1, $response->json('data'));

        $response->assertJsonPath('data.0.id', $activeModel->id);
        $response->assertJsonPath('data.0.active', true);
        $response->assertJsonPath('data.0.public_visible', true);
        $response->assertJsonPath('data.0.available_for_generation', true);

        $returnedIds = collect($response->json('data'))->pluck('id')->all();
        $this->assertNotContains($inactiveModel->id, $returnedIds);
        $this->assertNotContains($modelWithoutPreset->id, $returnedIds);
        $this->assertNotContains($modelWithInactivePreset->id, $returnedIds);
    }
}
