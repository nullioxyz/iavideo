<?php

namespace App\Domain\Videos\Tests\Integration;

use App\Domain\AIModels\Models\Model as AIModel;
use App\Domain\AIModels\Models\Preset;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Tests\Traits\AuthenticatesWithJwt;
use App\Domain\Platforms\Models\Platform;
use App\Domain\Videos\Events\CancelPredictionInput;
use App\Domain\Videos\Models\Input;
use App\Domain\Videos\Models\Prediction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CancelPredictionTest extends TestCase
{
    use AuthenticatesWithJwt;
    use RefreshDatabase;

    public function test_cancel_input_prediction_dispatches_event_and_persists_input(): void
    {
        Event::fake([CancelPredictionInput::class]);

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

        $response->assertNoContent();

        Event::assertDispatched(CancelPredictionInput::class, function (CancelPredictionInput $event) use ($input) {
            return $event->inputId === $input->getKey();
        });
    }
}
