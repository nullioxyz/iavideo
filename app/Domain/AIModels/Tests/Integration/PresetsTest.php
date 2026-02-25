<?php

namespace App\Domain\AIModels\Tests\Integration;

use App\Domain\AIModels\Models\Model as AIModel;
use App\Domain\AIModels\Models\Preset;
use App\Domain\AIModels\Models\PresetTag;
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
                    'preview_image_url',
                    'preview_video_url',
                    'aspect_ratio',
                    'tags',
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

    public function test_fetch_presets_can_filter_by_aspect_ratio_and_tag(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
        ]);

        $activeModel = AIModel::factory()->create([
            'active' => true,
        ]);

        $tagAnime = PresetTag::factory()->create([
            'name' => 'Anime',
            'slug' => 'anime',
        ]);

        $tagCinematic = PresetTag::factory()->create([
            'name' => 'Cinematic',
            'slug' => 'cinematic',
        ]);

        $presetWanted = Preset::factory()->create([
            'default_model_id' => $activeModel->getKey(),
            'aspect_ratio' => '9:16',
            'active' => true,
        ]);
        $presetWanted->tags()->attach([$tagAnime->getKey()]);

        $presetOtherAspect = Preset::factory()->create([
            'default_model_id' => $activeModel->getKey(),
            'aspect_ratio' => '16:9',
            'active' => true,
        ]);
        $presetOtherAspect->tags()->attach([$tagAnime->getKey()]);

        $presetOtherTag = Preset::factory()->create([
            'default_model_id' => $activeModel->getKey(),
            'aspect_ratio' => '9:16',
            'active' => true,
        ]);
        $presetOtherTag->tags()->attach([$tagCinematic->getKey()]);

        $token = $this->loginAndGetToken($user);

        $response = $this->withJwt($token)
            ->getJson("/api/models/{$activeModel->getKey()}/presets?aspect_ratio=9:16&tag=anime");

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $presetWanted->getKey());
        $response->assertJsonPath('data.0.aspect_ratio', '9:16');
        $response->assertJsonPath('data.0.tags.0.slug', 'anime');
    }

    public function test_fetch_preset_filter_options_returns_available_aspect_ratios_and_tags(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
        ]);

        $activeModel = AIModel::factory()->create([
            'active' => true,
        ]);

        $tagAnime = PresetTag::factory()->create([
            'name' => 'Anime',
            'slug' => 'anime',
        ]);

        $tagRealistic = PresetTag::factory()->create([
            'name' => 'Realistic',
            'slug' => 'realistic',
        ]);

        $presetOne = Preset::factory()->create([
            'default_model_id' => $activeModel->getKey(),
            'aspect_ratio' => '16:9',
            'active' => true,
        ]);
        $presetOne->tags()->attach([$tagAnime->getKey()]);

        $presetTwo = Preset::factory()->create([
            'default_model_id' => $activeModel->getKey(),
            'aspect_ratio' => '1:1',
            'active' => true,
        ]);
        $presetTwo->tags()->attach([$tagRealistic->getKey()]);

        $token = $this->loginAndGetToken($user);

        $response = $this->withJwt($token)
            ->getJson("/api/models/{$activeModel->getKey()}/presets/filters");

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'aspect_ratios',
                'tags' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                    ],
                ],
            ],
        ]);

        $this->assertContains('16:9', $response->json('data.aspect_ratios'));
        $this->assertContains('1:1', $response->json('data.aspect_ratios'));

        $tagSlugs = collect($response->json('data.tags'))->pluck('slug')->all();
        $this->assertContains('anime', $tagSlugs);
        $this->assertContains('realistic', $tagSlugs);
    }

    public function test_fetch_presets_returns_preview_image_and_video_urls_from_media(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
        ]);

        $activeModel = AIModel::factory()->create([
            'active' => true,
        ]);

        $preset = Preset::factory()->create([
            'default_model_id' => $activeModel->getKey(),
            'active' => true,
        ]);

        $preset->addMediaFromString('image-content')
            ->usingFileName('preview.jpg')
            ->toMediaCollection('preview_image', 'public');

        $preset->addMediaFromString('video-content')
            ->usingFileName('preview.mp4')
            ->toMediaCollection('preview_video', 'public');

        $token = $this->loginAndGetToken($user);

        $response = $this->withJwt($token)
            ->getJson("/api/models/{$activeModel->getKey()}/presets");

        $response->assertOk();
        $response->assertJsonPath('data.0.id', $preset->getKey());
        $this->assertNotNull($response->json('data.0.preview_image_url'));
        $this->assertNotNull($response->json('data.0.preview_video_url'));
    }
}
