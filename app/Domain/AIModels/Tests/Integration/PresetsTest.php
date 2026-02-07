<?php

namespace App\Domain\AIModels\Tests\Integration;

use App\Domain\AIModels\Models\Model as AIModel;
use App\Domain\AIModels\Models\Preset;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Tests\Traits\AuthenticatesWithJwt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PresetsTest extends TestCase
{
    use AuthenticatesWithJwt;
    use RefreshDatabase;

    public function test_fetch_presets_by_model_pagination(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
        ])->first();

        $activeModel = AIModel::factory()->create([
            'active' => true,
        ]);

        $preset = Preset::factory()->create([
            'default_model_id' => $activeModel->getKey(),
        ]);

        $inactivePreset = Preset::factory()->create([
            'default_model_id' => $activeModel->getKey(),
            'active' => false,
        ]);

        $token = $this->loginAndGetToken($user);

        $response = $this->withJwt($token)
            ->getJson("/api/models/{$activeModel->getKey()}/presets");

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'default_model_id',
                    'name',
                    'prompt',
                    'negative_prompt',
                    'duration_seconds',
                    'preview_video_url',
                    'aspect_ratio',
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

        $response->assertJsonPath('data.0.id', $preset->id);

        $returnedIds = collect($response->json('data'))->pluck('id')->all();
        $this->assertNotContains($inactivePreset->id, $returnedIds);
    }
}
