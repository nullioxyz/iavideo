<?php

namespace App\Domain\AIModels\Tests\Integration;

use App\Domain\AIModels\Models\Model as AIModel;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Tests\Traits\AuthenticatesWithJwt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IAModelsTest extends TestCase
{
    use RefreshDatabase;
    use AuthenticatesWithJwt;

    public function test_fetch_ia_models_pagination_returns_only_active_models(): void
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
                    'version',
                    'active',
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

        $returnedIds = collect($response->json('data'))->pluck('id')->all();
        $this->assertNotContains($inactiveModel->id, $returnedIds);
    }
}
