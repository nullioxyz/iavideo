<?php

namespace App\Domain\Videos\Tests\Integration;

use App\Domain\AIModels\Models\Model as AIModel;
use App\Domain\AIModels\Models\Preset;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Tests\Traits\AuthenticatesWithJwt;
use App\Domain\Settings\Models\Setting;
use App\Domain\Videos\Events\InputCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class InputEstimateTest extends TestCase
{
    use AuthenticatesWithJwt;
    use RefreshDatabase;

    public function test_estimate_endpoint_returns_credits_and_costs(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'credit_unit_value_usd'],
            ['value' => '0.35']
        );

        $user = User::factory()->create(['active' => true, 'password' => bcrypt('password')]);
        $token = $this->loginAndGetToken($user);

        $model = AIModel::factory()->create([
            'cost_per_second_usd' => '0.1500',
            'active' => true,
            'public_visible' => true,
        ]);

        $preset = Preset::factory()->create([
            'default_model_id' => $model->getKey(),
            'duration_seconds' => 5,
            'active' => true,
        ]);

        $response = $this->withJwt($token)->postJson('/api/input/estimate', [
            'model_id' => $model->getKey(),
            'preset_id' => $preset->getKey(),
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.model_id', $model->getKey());
        $response->assertJsonPath('data.preset_id', $preset->getKey());
        $response->assertJsonPath('data.duration_seconds', 5);
        $response->assertJsonPath('data.credits_required', 3);
        $response->assertJsonPath('data.model_cost_per_second_usd', '0.1500');
        $response->assertJsonPath('data.estimated_generation_cost_usd', '0.7500');
    }

    public function test_create_input_recalculates_server_side_and_ignores_frontend_credit_fields(): void
    {
        Event::fake([InputCreated::class]);

        Setting::query()->updateOrCreate(
            ['key' => 'credit_unit_value_usd'],
            ['value' => '0.35']
        );

        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'credit_balance' => 5,
        ]);

        $token = $this->loginAndGetToken($user);

        $model = AIModel::factory()->create([
            'cost_per_second_usd' => '0.0700',
            'active' => true,
            'public_visible' => true,
        ]);

        $preset = Preset::factory()->create([
            'default_model_id' => $model->getKey(),
            'duration_seconds' => 5,
            'active' => true,
        ]);

        $response = $this->withJwt($token)->postJson('/api/input/create', [
            'model_id' => $model->getKey(),
            'preset_id' => $preset->getKey(),
            'credits_required' => 999,
            'estimated_generation_cost_usd' => '999.9999',
            'image' => UploadedFile::fake()->image('input.png', 900, 1600)->size(500),
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.credits_charged', 1);

        $this->assertSame(4, (int) $user->fresh()->credit_balance);
    }

    public function test_create_input_fails_when_balance_is_insufficient_for_estimated_credits(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'credit_unit_value_usd'],
            ['value' => '0.35']
        );

        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'credit_balance' => 2,
        ]);

        $token = $this->loginAndGetToken($user);

        $model = AIModel::factory()->create([
            'cost_per_second_usd' => '0.1500',
            'active' => true,
            'public_visible' => true,
        ]);

        $preset = Preset::factory()->create([
            'default_model_id' => $model->getKey(),
            'duration_seconds' => 5,
            'active' => true,
        ]);

        $response = $this->withJwt($token)->postJson('/api/input/create', [
            'model_id' => $model->getKey(),
            'preset_id' => $preset->getKey(),
            'image' => UploadedFile::fake()->image('input.png', 900, 1600)->size(500),
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Insufficient balance');
        $this->assertDatabaseCount('inputs', 0);
    }

    public function test_estimate_fails_when_model_cost_is_missing(): void
    {
        $user = User::factory()->create(['active' => true, 'password' => bcrypt('password')]);
        $token = $this->loginAndGetToken($user);

        $model = AIModel::factory()->create([
            'cost_per_second_usd' => null,
            'active' => true,
            'public_visible' => true,
        ]);

        $preset = Preset::factory()->create([
            'default_model_id' => $model->getKey(),
            'active' => true,
        ]);

        $response = $this->withJwt($token)->postJson('/api/input/estimate', [
            'model_id' => $model->getKey(),
            'preset_id' => $preset->getKey(),
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Selected model does not have a defined generation cost.');
    }
}
