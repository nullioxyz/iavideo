<?php

namespace App\Domain\Videos\Tests\Integration;

use App\Domain\AIModels\Models\Model as AIModel;
use App\Domain\AIModels\Models\Preset;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Tests\Traits\AuthenticatesWithJwt;
use App\Domain\Platforms\Models\Platform;
use App\Domain\Videos\Models\Input;
use App\Domain\Videos\Models\Prediction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CancelPredictionTest extends TestCase
{
    use AuthenticatesWithJwt;
    use RefreshDatabase;

    public function test_cancel_input_prediction_returns_cancelled_status_immediately(): void
    {
        $this->fakeReplicateCancel();

        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'credit_balance' => 5,
        ]);

        $token = $this->loginAndGetToken($user);

        $platform = Platform::query()->create([
            'name' => 'Replicate',
            'slug' => 'replicate',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $model = AIModel::query()->create([
            'platform_id' => $platform->id,
            'name' => 'Kling v2.5 Turbo Pro',
            'slug' => 'kwaivgi/kling-v2.5-turbo-pro',
            'version' => null,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $preset = Preset::query()->create([
            'name' => 'Preset 9:16',
            'prompt' => 'Go until to the start of the universe. Go to the Big Bang.',
            'negative_prompt' => null,
            'aspect_ratio' => '9:16',
            'duration_seconds' => 5,
            'default_model_id' => $model->id,
            'cost_estimate_usd' => 0.3500,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $input = Input::query()->create([
            'user_id' => $user->getKey(),
            'model_id' => $model->getKey(),
            'preset_id' => $preset->id,
            'start_image_path' => null,
            'original_filename' => 'tattoo.png',
            'mime_type' => 'image/png',
            'size_bytes' => 12345,
            'credit_debited' => false,
            'status' => 'created',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $prediction = Prediction::factory()->create([
            'input_id' => $input->getKey(),
            'model_id' => $model->getKey(),
            'external_id' => '2wbzrawha9rmw0cv9h5ajeyyn4',
            'status' => 'starting',
            'source' => 'web',
            'attempt' => 1,
            'queued_at' => Carbon::now(),
        ]);

        $response = $this->withJwt($token)->postJson('/api/prediction/cancel', [
            'input_id' => $input->getKey(),
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.id', $input->getKey());
        $response->assertJsonPath('data.status', Input::CANCELLED);
        $response->assertJsonPath('data.prediction.id', $prediction->getKey());
        $response->assertJsonPath('data.prediction.status', Prediction::CANCELLED);

        $this->assertDatabaseHas('inputs', [
            'id' => $input->getKey(),
            'status' => Input::CANCELLED,
        ]);

        $this->assertDatabaseHas('predictions', [
            'id' => $prediction->getKey(),
            'status' => Prediction::CANCELLED,
        ]);
    }

    public function test_cancel_job_endpoint_returns_cancelled_status_immediately(): void
    {
        $this->fakeReplicateCancel();

        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'credit_balance' => 5,
        ]);

        $token = $this->loginAndGetToken($user);

        $platform = Platform::query()->create([
            'name' => 'Replicate',
            'slug' => 'replicate',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $model = AIModel::query()->create([
            'platform_id' => $platform->id,
            'name' => 'Kling v2.5 Turbo Pro',
            'slug' => 'kwaivgi/kling-v2.5-turbo-pro',
            'version' => null,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $preset = Preset::query()->create([
            'name' => 'Preset 9:16',
            'prompt' => 'Go until to the start of the universe. Go to the Big Bang.',
            'negative_prompt' => null,
            'aspect_ratio' => '9:16',
            'duration_seconds' => 5,
            'default_model_id' => $model->id,
            'cost_estimate_usd' => 0.3500,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $input = Input::query()->create([
            'user_id' => $user->getKey(),
            'model_id' => $model->getKey(),
            'preset_id' => $preset->id,
            'start_image_path' => null,
            'original_filename' => 'tattoo.png',
            'mime_type' => 'image/png',
            'size_bytes' => 12345,
            'credit_debited' => false,
            'status' => 'created',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $prediction = Prediction::factory()->create([
            'input_id' => $input->getKey(),
            'model_id' => $model->getKey(),
            'external_id' => '2wbzrawha9rmw0cv9h5ajeyyn4',
            'status' => 'starting',
            'source' => 'web',
            'attempt' => 1,
            'queued_at' => Carbon::now(),
        ]);

        $response = $this->withJwt($token)->postJson('/api/jobs/'.$input->getKey().'/cancel');

        $response->assertOk();
        $response->assertJsonPath('data.id', $input->getKey());
        $response->assertJsonPath('data.status', Input::CANCELLED);
        $response->assertJsonPath('data.prediction.id', $prediction->getKey());
        $response->assertJsonPath('data.prediction.status', Prediction::CANCELLED);

        $this->assertDatabaseHas('inputs', [
            'id' => $input->getKey(),
            'status' => Input::CANCELLED,
        ]);

        $this->assertDatabaseHas('predictions', [
            'id' => $prediction->getKey(),
            'status' => Prediction::CANCELLED,
        ]);
    }

    public function test_cancel_job_endpoint_cancels_locally_when_prediction_has_no_external_id(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'credit_balance' => 5,
        ]);

        $token = $this->loginAndGetToken($user);

        $platform = Platform::query()->create([
            'name' => 'Replicate',
            'slug' => 'replicate',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $model = AIModel::query()->create([
            'platform_id' => $platform->id,
            'name' => 'Kling v2.5 Turbo Pro',
            'slug' => 'kwaivgi/kling-v2.5-turbo-pro',
            'provider_model_key' => 'kwaivgi/kling-v2.5-turbo-pro',
            'active' => true,
            'public_visible' => true,
            'cost_per_second_usd' => '0.0700',
            'credits_per_second' => '5.0000',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $preset = Preset::query()->create([
            'name' => 'Preset 9:16',
            'prompt' => 'Go until to the start of the universe. Go to the Big Bang.',
            'negative_prompt' => null,
            'aspect_ratio' => '9:16',
            'duration_seconds' => 5,
            'default_model_id' => $model->id,
            'cost_estimate_usd' => 0.3500,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $input = Input::query()->create([
            'user_id' => $user->getKey(),
            'model_id' => $model->getKey(),
            'preset_id' => $preset->id,
            'duration_seconds' => 5,
            'estimated_cost_usd' => '0.3500',
            'model_cost_per_second_usd' => '0.0700',
            'model_credits_per_second' => '5.0000',
            'credits_charged' => 25,
            'billing_status' => 'charged',
            'credit_debited' => true,
            'status' => Input::PROCESSING,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $prediction = Prediction::factory()->create([
            'input_id' => $input->getKey(),
            'model_id' => $model->getKey(),
            'external_id' => null,
            'status' => Prediction::STARTING,
            'source' => 'web',
            'attempt' => 1,
            'queued_at' => Carbon::now(),
        ]);

        $response = $this->withJwt($token)->postJson('/api/jobs/'.$input->getKey().'/cancel');

        $response->assertOk();
        $response->assertJsonPath('data.id', $input->getKey());
        $response->assertJsonPath('data.status', Input::CANCELLED);
        $response->assertJsonPath('data.prediction.id', $prediction->getKey());
        $response->assertJsonPath('data.prediction.status', Prediction::CANCELLED);

        $this->assertDatabaseHas('inputs', [
            'id' => $input->getKey(),
            'status' => Input::CANCELLED,
            'credit_debited' => false,
        ]);

        $this->assertDatabaseHas('predictions', [
            'id' => $prediction->getKey(),
            'status' => Prediction::CANCELLED,
        ]);
    }

    public function test_user_cannot_cancel_input_of_another_user(): void
    {
        $owner = User::factory()->create(['active' => true]);
        $attacker = User::factory()->create(['active' => true]);

        $token = $this->loginAndGetToken($attacker);

        $platform = Platform::query()->create([
            'name' => 'Replicate',
            'slug' => 'replicate',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $model = AIModel::query()->create([
            'platform_id' => $platform->id,
            'name' => 'Kling v2.5 Turbo Pro',
            'slug' => 'kwaivgi/kling-v2.5-turbo-pro',
            'version' => null,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $preset = Preset::query()->create([
            'name' => 'Preset 9:16',
            'prompt' => 'Go until to the start of the universe. Go to the Big Bang.',
            'negative_prompt' => null,
            'aspect_ratio' => '9:16',
            'duration_seconds' => 5,
            'default_model_id' => $model->id,
            'cost_estimate_usd' => 0.3500,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $input = Input::query()->create([
            'user_id' => $owner->getKey(),
            'preset_id' => $preset->id,
            'status' => 'created',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Prediction::factory()->create([
            'input_id' => $input->getKey(),
            'model_id' => $model->getKey(),
            'external_id' => 'ext-owner',
            'status' => 'starting',
            'source' => 'web',
            'attempt' => 1,
            'queued_at' => Carbon::now(),
        ]);

        $response = $this->withJwt($token)->postJson('/api/prediction/cancel', [
            'input_id' => $input->getKey(),
        ]);

        $response->assertUnprocessable();
    }

    public function test_user_cannot_cancel_job_of_another_user(): void
    {
        $owner = User::factory()->create(['active' => true]);
        $attacker = User::factory()->create(['active' => true]);

        $token = $this->loginAndGetToken($attacker);

        $platform = Platform::query()->create([
            'name' => 'Replicate',
            'slug' => 'replicate',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $model = AIModel::query()->create([
            'platform_id' => $platform->id,
            'name' => 'Kling v2.5 Turbo Pro',
            'slug' => 'kwaivgi/kling-v2.5-turbo-pro',
            'version' => null,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $preset = Preset::query()->create([
            'name' => 'Preset 9:16',
            'prompt' => 'Go until to the start of the universe. Go to the Big Bang.',
            'negative_prompt' => null,
            'aspect_ratio' => '9:16',
            'duration_seconds' => 5,
            'default_model_id' => $model->id,
            'cost_estimate_usd' => 0.3500,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $input = Input::query()->create([
            'user_id' => $owner->getKey(),
            'preset_id' => $preset->id,
            'status' => 'created',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Prediction::factory()->create([
            'input_id' => $input->getKey(),
            'model_id' => $model->getKey(),
            'external_id' => 'ext-owner',
            'status' => 'starting',
            'source' => 'web',
            'attempt' => 1,
            'queued_at' => Carbon::now(),
        ]);

        $response = $this->withJwt($token)->postJson('/api/jobs/'.$input->getKey().'/cancel');

        $response->assertUnprocessable();
    }

    private function fakeReplicateCancel(): void
    {
        Config::set('services.replicate.token', 'test-token');

        Http::fake([
            'https://api.replicate.com/v1/predictions/*/cancel' => Http::response([
                'id' => '2wbzrawha9rmw0cv9h5ajeyyn4',
                'status' => 'cancelled',
            ], 200),
        ]);
    }
}
