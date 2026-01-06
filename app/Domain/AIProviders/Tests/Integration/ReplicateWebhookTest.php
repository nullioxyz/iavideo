<?php

namespace App\Domain\AIProviders\Tests\Integration;

use App\Domain\AIModels\Models\Model;
use App\Domain\AIModels\Models\Preset;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Tests\Traits\AuthenticatesWithJwt;
use App\Domain\Videos\Jobs\DownloadPredictionOutputsJob;
use App\Domain\Videos\Models\Input;
use App\Domain\Videos\Models\Prediction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ReplicateWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_receive_replicate_prediction_starting_data(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
        ]);

        $activeModel = Model::factory()->create(['active' => true]);

        $preset = Preset::factory()->create([
            'default_model_id' => $activeModel->getKey(),
            'active' => true,
        ]);

        $input = Input::factory()->create([
            'user_id' => $user->getKey(),
            'preset_id' => $preset->getKey(),
            'status' => 'created',
        ]);

        $prediction = Prediction::factory()->create([
            'input_id' => $input->id,
            'model_id' => $activeModel->id,
            'external_id' => 'ufawqhfynnddngldkgtslldrkq',
            'status' => 'submitting',
            'source' => 'web',
        ]);
        
        $response = $this->post('/api/webhook/replicate', [
            "id" => "ufawqhfynnddngldkgtslldrkq",
            "version" => "5c7d5dc6dd8bf75c1acaa8565735e7986bc5b66206b55cca93cb72c9bf15ccaa",
            "created_at" => "2022-04-26T22:13:06.224088Z",
            "started_at" => null,
            "completed_at" => null,
            "status" => "starting",
            "input" => [
                "text" => "Alice"
            ],
            "output" => null,
            "error" => null,
            "logs" => null,
            "metrics" => []
        ]);

        $response->assertNoContent();

        $prediction->refresh();

        $this->assertEquals('starting', $prediction->status);
        $this->assertCount(0, $prediction->outputs);
    }

    public function test_receive_replicate_prediction_completed_data(): void
    {
        Queue::fake();
        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
        ]);

        $activeModel = Model::factory()->create(['active' => true]);

        $preset = Preset::factory()->create([
            'default_model_id' => $activeModel->getKey(),
            'active' => true,
        ]);

        $input = Input::factory()->create([
            'user_id' => $user->getKey(),
            'preset_id' => $preset->getKey(),
            'status' => 'created',
        ]);

        $prediction = Prediction::factory()->create([
            'input_id' => $input->id,
            'model_id' => $activeModel->id,
            'external_id' => 'rqgf4j40xxrmt0cvcqnrf0329m',
            'status' => 'starting',
            'source' => 'web',
        ]);
        
        $response = $this->post('/api/webhook/replicate', [
            "completed_at" => "2025-12-28T17:07:02.050986Z",
            "created_at" => "2025-12-28T17:04:51.439000Z",
            "data_removed" => true,
            "error" => null,
            "id" => "rqgf4j40xxrmt0cvcqnrf0329m",
            "input" => [],
            "metrics" => [
                "predict_time" => 130.600248305,
                "total_time" => 130.611986008
            ],
            "model" => "kwaivgi/kling-v2.5-turbo-pro",
            "output" => 'https://cdn.replicate.com/fake/video.mp4',
            "source" => "api",
            "started_at" => "2025-12-28T17:04:51.450737Z",
            "status" => "succeeded",
            "urls" => [
                "stream" => "https://stream.replicate.com/v1/files/jbxs-c4b5dqladlrvzlybu5awscrjpskhciw2fhrpn3tdl35v76ogjpxq",
                "get" => "https://api.replicate.com/v1/predictions/rqgf4j40xxrmt0cvcqnrf0329m",
                "cancel" => "https://api.replicate.com/v1/predictions/rqgf4j40xxrmt0cvcqnrf0329m/cancel",
                "web" => "https://replicate.com/p/rqgf4j40xxrmt0cvcqnrf0329m"
            ],
            "version" => "hidden"
        ]);

        $response->assertNoContent();

        $prediction->refresh();

        Queue::assertPushed(DownloadPredictionOutputsJob::class, function ($job) use ($prediction) {
            return $job->predictionId === $prediction->id;
        });


        $this->assertEquals('succeeded', $prediction->status);
        $this->assertCount(1, $prediction->outputs);
    }

    public function test_receive_replicate_prediction_completed_dispatches_download_job_and_creates_output_row(): void
    {
        Queue::fake();

        $user = User::factory()->create(['active' => true, 'password' => bcrypt('password')]);
        $activeModel = Model::factory()->create(['active' => true]);

        $preset = Preset::factory()->create([
            'default_model_id' => $activeModel->getKey(),
            'active' => true,
        ]);

        $input = Input::factory()->create([
            'user_id' => $user->getKey(),
            'preset_id' => $preset->getKey(),
            'status' => 'created',
        ]);

        $prediction = Prediction::factory()->create([
            'input_id' => $input->id,
            'model_id' => $activeModel->id,
            'external_id' => 'rqgf4j40xxrmt0cvcqnrf0329m',
            'status' => 'starting',
            'source' => 'web',
        ]);

        $videoUrl = 'https://cdn.replicate.com/fake/video.mp4';

        $response = $this->postJson('/api/webhook/replicate', [
            "completed_at" => "2025-12-28T17:07:02.050986Z",
            "created_at" => "2025-12-28T17:04:51.439000Z",
            "error" => null,
            "id" => "rqgf4j40xxrmt0cvcqnrf0329m",
            "input" => [],
            "metrics" => [
                "predict_time" => 130.600248305,
                "total_time" => 130.611986008
            ],
            "model" => "kwaivgi/kling-v2.5-turbo-pro",
            "output" => $videoUrl,
            "started_at" => "2025-12-28T17:04:51.450737Z",
            "status" => "succeeded",
            "urls" => [
                "get" => "https://api.replicate.com/v1/predictions/rqgf4j40xxrmt0cvcqnrf0329m",
                "web" => "https://replicate.com/p/rqgf4j40xxrmt0cvcqnrf0329m"
            ],
            "version" => "hidden"
        ]);

        $response->assertNoContent();

        $prediction->refresh();
        $this->assertSame('succeeded', $prediction->status);

        $this->assertCount(1, $prediction->outputs);

        $output = $prediction->outputs()->first();
        $this->assertNotNull($output);

        $this->assertSame($videoUrl, $output->provider_url ?? $output->path);

        Queue::assertPushed(DownloadPredictionOutputsJob::class, function ($job) use ($prediction) {
            return $job->predictionId === $prediction->id;
        });
    }

    
}
