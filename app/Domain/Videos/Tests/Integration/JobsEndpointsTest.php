<?php

namespace App\Domain\Videos\Tests\Integration;

use App\Domain\AIModels\Models\Model as AIModel;
use App\Domain\AIModels\Models\Preset;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Tests\Traits\AuthenticatesWithJwt;
use App\Domain\Platforms\Models\Platform;
use App\Domain\Videos\Models\Input;
use App\Domain\Videos\Models\Prediction;
use App\Domain\Videos\Models\PredictionOutput;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JobsEndpointsTest extends TestCase
{
    use AuthenticatesWithJwt;
    use RefreshDatabase;

    public function test_list_jobs_returns_only_authenticated_user_jobs_with_prediction_and_outputs(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'credit_balance' => 5,
        ]);

        $otherUser = User::factory()->create();
        $token = $this->loginAndGetToken($user);

        $platform = Platform::query()->create([
            'name' => 'Replicate',
            'slug' => 'replicate',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $model = AIModel::query()->create([
            'platform_id' => $platform->id,
            'name' => 'Model',
            'slug' => 'model',
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $preset = Preset::query()->create([
            'name' => 'Preset',
            'prompt' => 'Prompt',
            'negative_prompt' => null,
            'aspect_ratio' => '9:16',
            'duration_seconds' => 5,
            'default_model_id' => $model->id,
            'cost_estimate_usd' => 0.3500,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $myInput = Input::factory()->create([
            'user_id' => $user->getKey(),
            'preset_id' => $preset->getKey(),
            'title' => 'Titulo do input',
            'status' => Input::PROCESSING,
        ]);

        $myPrediction = Prediction::query()->create([
            'input_id' => $myInput->getKey(),
            'model_id' => $model->getKey(),
            'external_id' => 'pred-123',
            'status' => Prediction::PROCESSING,
            'source' => 'web',
            'attempt' => 1,
            'queued_at' => now(),
            'request_payload' => ['foo' => 'bar'],
            'response_payload' => ['baz' => 'qux'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        PredictionOutput::factory()->create([
            'prediction_id' => $myPrediction->getKey(),
            'kind' => 'video',
            'path' => '/tmp/video.mp4',
        ]);

        Input::factory()->create([
            'user_id' => $otherUser->getKey(),
            'preset_id' => $preset->getKey(),
        ]);

        $response = $this->withJwt($token)->getJson('/api/jobs');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'status',
                    'title',
                    'prediction' => [
                        'id',
                        'status',
                        'outputs',
                    ],
                ],
            ],
            'meta',
            'links',
        ]);

        $response->assertJsonPath('data.0.id', $myInput->getKey());
        $response->assertJsonPath('data.0.title', 'Titulo do input');
    }

    public function test_job_detail_returns_404_for_job_from_another_user(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'credit_balance' => 5,
        ]);

        $otherUser = User::factory()->create();
        $token = $this->loginAndGetToken($user);

        $input = Input::factory()->create([
            'user_id' => $otherUser->getKey(),
        ]);

        $response = $this->withJwt($token)->getJson('/api/jobs/'.$input->getKey());

        $response->assertNotFound();
    }

    public function test_job_detail_returns_title_for_authenticated_user_job(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'credit_balance' => 5,
        ]);

        $token = $this->loginAndGetToken($user);

        $input = Input::factory()->create([
            'user_id' => $user->getKey(),
            'title' => 'Titulo detalhe',
        ]);

        $response = $this->withJwt($token)->getJson('/api/jobs/'.$input->getKey());

        $response->assertOk();
        $response->assertJsonPath('data.id', $input->getKey());
        $response->assertJsonPath('data.title', 'Titulo detalhe');
    }

    public function test_rename_job_title_updates_authenticated_user_input(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'credit_balance' => 5,
        ]);

        $token = $this->loginAndGetToken($user);

        $input = Input::factory()->create([
            'user_id' => $user->getKey(),
            'title' => 'Titulo antigo',
        ]);

        $response = $this->withJwt($token)->patchJson('/api/jobs/'.$input->getKey().'/title', [
            'title' => 'Titulo novo',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.id', $input->getKey());
        $response->assertJsonPath('data.title', 'Titulo novo');

        $this->assertDatabaseHas('inputs', [
            'id' => $input->getKey(),
            'title' => 'Titulo novo',
        ]);
    }

    public function test_rename_job_title_uses_original_filename_when_title_is_empty(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'credit_balance' => 5,
        ]);

        $token = $this->loginAndGetToken($user);

        $input = Input::factory()->create([
            'user_id' => $user->getKey(),
            'title' => 'Titulo antigo',
            'original_filename' => 'meu-arquivo.png',
        ]);

        $response = $this->withJwt($token)->patchJson('/api/jobs/'.$input->getKey().'/title', [
            'title' => '  ',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.title', 'meu-arquivo.png');

        $this->assertDatabaseHas('inputs', [
            'id' => $input->getKey(),
            'title' => 'meu-arquivo.png',
        ]);
    }

    public function test_rename_job_title_returns_404_for_input_from_another_user(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'credit_balance' => 5,
        ]);

        $otherUser = User::factory()->create();
        $token = $this->loginAndGetToken($user);

        $input = Input::factory()->create([
            'user_id' => $otherUser->getKey(),
        ]);

        $response = $this->withJwt($token)->patchJson('/api/jobs/'.$input->getKey().'/title', [
            'title' => 'Sem permissao',
        ]);

        $response->assertNotFound();
    }

    public function test_download_job_video_returns_file_for_authenticated_owner(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'credit_balance' => 5,
        ]);

        $token = $this->loginAndGetToken($user);

        $input = Input::factory()->create([
            'user_id' => $user->getKey(),
            'title' => 'Meu Video Final',
        ]);

        $prediction = Prediction::factory()->create([
            'input_id' => $input->getKey(),
            'status' => Prediction::SUCCEEDED,
        ]);

        $output = PredictionOutput::factory()->create([
            'prediction_id' => $prediction->getKey(),
            'kind' => 'video',
            'mime_type' => 'video/mp4',
        ]);

        $output->addMediaFromString('fake-video-bytes')
            ->usingFileName('output.mp4')
            ->toMediaCollection('file', 'public');

        $response = $this->withJwt($token)->get('/api/jobs/'.$input->getKey().'/download');

        $response->assertOk();
        $this->assertStringContainsString('attachment;', (string) $response->headers->get('content-disposition'));
        $this->assertStringContainsString('.mp4', (string) $response->headers->get('content-disposition'));
    }

    public function test_download_job_video_returns_404_for_input_from_another_user(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'credit_balance' => 5,
        ]);

        $otherUser = User::factory()->create();
        $token = $this->loginAndGetToken($user);

        $input = Input::factory()->create([
            'user_id' => $otherUser->getKey(),
        ]);

        $response = $this->withJwt($token)->get('/api/jobs/'.$input->getKey().'/download');

        $response->assertNotFound();
    }

    public function test_download_job_video_returns_404_when_video_is_not_ready(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'credit_balance' => 5,
        ]);

        $token = $this->loginAndGetToken($user);

        $input = Input::factory()->create([
            'user_id' => $user->getKey(),
        ]);

        Prediction::factory()->create([
            'input_id' => $input->getKey(),
            'status' => Prediction::SUCCEEDED,
        ]);

        $response = $this->withJwt($token)->get('/api/jobs/'.$input->getKey().'/download');

        $response->assertNotFound();
    }
}
