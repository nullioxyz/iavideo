<?php

namespace App\Domain\Videos\Tests\Integration;

use App\Domain\AIModels\Models\Model as AIModel;
use App\Domain\AIModels\Models\Preset;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Tests\Traits\AuthenticatesWithJwt;
use App\Domain\Videos\Events\InputCreated;
use App\Domain\Videos\Models\Input;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class InputCreationTest extends TestCase
{
    use AuthenticatesWithJwt;
    use RefreshDatabase;

    public function test_create_input_dispatches_event_and_persists_input(): void
    {
        Event::fake([InputCreated::class]);

        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'credit_balance' => 5,
        ]);

        $token = $this->loginAndGetToken($user);

        $activeModel = AIModel::factory()->create(['active' => true]);

        $preset = Preset::factory()->create([
            'default_model_id' => $activeModel->getKey(),
            'aspect_ratio' => '9:16',
            'active' => true,
        ]);

        $image = UploadedFile::fake()->image('tattoo.png', 900, 1600)->size(500);

        $response = $this->withJwt($token)->postJson('/api/input/create', [
            'model_id' => $activeModel->getKey(),
            'preset_id' => $preset->getKey(),
            'title' => 'Meu video de teste',
            'image' => $image,
        ]);

        $response->assertCreated();

        $response->assertJsonStructure([
            'data' => [
                'id',
                'model_id',
                'preset_id',
                'user_id',
                'status',
                'title',
                'original_filename',
                'mime_type',
                'size_bytes',
                'duration_seconds',
                'estimated_cost_usd',
                'credits_charged',
                'billing_status',
            ],
        ]);

        $inputId = (int) $response->json('data.id');

        $this->assertDatabaseHas('inputs', [
            'id' => $inputId,
            'user_id' => $user->getKey(),
            'model_id' => $activeModel->getKey(),
            'preset_id' => $preset->getKey(),
            'status' => 'created',
            'title' => 'Meu video de teste',
            'original_filename' => 'tattoo.png',
            'mime_type' => 'image/png',
            'credits_charged' => 1,
            'billing_status' => 'charged',
        ]);

        $input = Input::query()->findOrFail($inputId);

        $this->assertNotNull($input->size_bytes);
        $this->assertGreaterThan(0, (int) $input->size_bytes);

        Event::assertDispatched(InputCreated::class, function (InputCreated $event) use ($inputId) {
            return $event->inputId === $inputId
                && str_contains($event->tempPath, 'tmp/inputs');
        });
    }

    public function test_create_input_uses_uploaded_filename_as_title_when_title_is_empty(): void
    {
        Event::fake([InputCreated::class]);

        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'credit_balance' => 5,
        ]);

        $token = $this->loginAndGetToken($user);

        $activeModel = AIModel::factory()->create(['active' => true]);

        $preset = Preset::factory()->create([
            'default_model_id' => $activeModel->getKey(),
            'aspect_ratio' => '9:16',
            'active' => true,
        ]);

        $image = UploadedFile::fake()->image('arquivo-original.png', 900, 1600)->size(500);

        $response = $this->withJwt($token)->postJson('/api/input/create', [
            'model_id' => $activeModel->getKey(),
            'preset_id' => $preset->getKey(),
            'title' => '   ',
            'image' => $image,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.title', 'arquivo-original.png');

        $inputId = (int) $response->json('data.id');

        $this->assertDatabaseHas('inputs', [
            'id' => $inputId,
            'title' => 'arquivo-original.png',
            'original_filename' => 'arquivo-original.png',
        ]);
    }

    public function test_create_input_accepts_image_matching_preset_aspect_ratio_16_9(): void
    {
        Event::fake([InputCreated::class]);

        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'credit_balance' => 5,
        ]);

        $token = $this->loginAndGetToken($user);

        $activeModel = AIModel::factory()->create(['active' => true]);

        $preset = Preset::factory()->create([
            'default_model_id' => $activeModel->getKey(),
            'aspect_ratio' => '16:9',
            'active' => true,
        ]);

        $image = UploadedFile::fake()->image('landscape.png', 1600, 900)->size(500);

        $response = $this->withJwt($token)->postJson('/api/input/create', [
            'model_id' => $activeModel->getKey(),
            'preset_id' => $preset->getKey(),
            'image' => $image,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.model_id', $activeModel->getKey());
        $response->assertJsonPath('data.preset_id', $preset->getKey());
    }
}
