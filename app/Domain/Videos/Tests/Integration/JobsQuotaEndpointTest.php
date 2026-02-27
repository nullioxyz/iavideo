<?php

namespace App\Domain\Videos\Tests\Integration;

use App\Domain\AIModels\Models\Model as AIModel;
use App\Domain\AIModels\Models\Preset;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Tests\Traits\AuthenticatesWithJwt;
use App\Domain\Settings\Models\Setting;
use App\Domain\Videos\Models\Input;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobsQuotaEndpointTest extends TestCase
{
    use AuthenticatesWithJwt;
    use RefreshDatabase;

    public function test_quota_endpoint_returns_current_daily_limit_state(): void
    {
        Setting::query()->updateOrCreate(['key' => 'max_daily_inputs'], ['value' => '3']);
        Setting::query()->updateOrCreate(['key' => 'daily_input_limit_warning_threshold'], ['value' => '1']);

        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
        ]);

        $token = $this->loginAndGetToken($user);

        $model = AIModel::factory()->create(['active' => true]);
        $preset = Preset::factory()->create([
            'default_model_id' => $model->getKey(),
            'active' => true,
        ]);

        Input::factory()->count(2)->create([
            'user_id' => $user->getKey(),
            'preset_id' => $preset->getKey(),
        ]);

        $response = $this->withJwt($token)->getJson('/api/jobs/quota');

        $response->assertOk();
        $response->assertJsonPath('data.daily_limit', 3);
        $response->assertJsonPath('data.used_today', 2);
        $response->assertJsonPath('data.remaining_today', 1);
        $response->assertJsonPath('data.near_limit', true);
        $response->assertJsonPath('data.limit_reached', false);
    }
}
