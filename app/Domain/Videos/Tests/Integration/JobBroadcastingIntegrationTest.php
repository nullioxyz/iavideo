<?php

namespace App\Domain\Videos\Tests\Integration;

use App\Domain\AIModels\Models\Model as AIModel;
use App\Domain\AIModels\Models\Preset;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Tests\Traits\AuthenticatesWithJwt;
use App\Domain\Broadcasting\Events\UserJobUpdatedBroadcast;
use App\Domain\Videos\Events\InputCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class JobBroadcastingIntegrationTest extends TestCase
{
    use AuthenticatesWithJwt;
    use RefreshDatabase;

    public function test_create_input_dispatches_user_job_updated_broadcast_event(): void
    {
        Event::fake([InputCreated::class, UserJobUpdatedBroadcast::class]);

        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'credit_balance' => 5,
        ]);

        $token = $this->loginAndGetToken($user);

        $activeModel = AIModel::factory()->create(['active' => true]);

        $preset = Preset::factory()->create([
            'default_model_id' => $activeModel->getKey(),
            'active' => true,
        ]);

        $image = UploadedFile::fake()->image('tattoo.png', 900, 1600)->size(500);

        $response = $this->withJwt($token)->postJson('/api/input/create', [
            'model_id' => $activeModel->getKey(),
            'preset_id' => $preset->getKey(),
            'image' => $image,
        ]);

        $response->assertCreated();

        Event::assertDispatched(UserJobUpdatedBroadcast::class);
    }
}
