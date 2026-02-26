<?php

namespace App\Domain\Videos\Tests\Stress;

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

class InputCreationStressTest extends TestCase
{
    use AuthenticatesWithJwt;
    use RefreshDatabase;

    public function test_burst_input_creation_keeps_credit_and_ledger_consistent(): void
    {
        Event::fake([InputCreated::class]);
        Setting::query()->updateOrCreate(
            ['key' => 'max_daily_inputs'],
            ['value' => '500']
        );

        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'credit_balance' => 80,
        ]);

        $token = $this->loginAndGetToken($user);

        $activeModel = AIModel::factory()->create(['active' => true]);
        $preset = Preset::factory()->create([
            'default_model_id' => $activeModel->getKey(),
            'active' => true,
        ]);

        $attempts = 120;
        $created = 0;
        $rejected = 0;

        for ($i = 1; $i <= $attempts; $i++) {
            $response = $this->withJwt($token)->postJson('/api/input/create', [
                'preset_id' => $preset->getKey(),
                'title' => "stress-{$i}",
                'image' => UploadedFile::fake()->image("stress-{$i}.png", 1024, 1024)->size(400),
            ]);

            if ($response->status() === 201) {
                $created++;
                continue;
            }

            $response->assertStatus(422);
            $rejected++;
        }

        $this->assertSame(80, $created);
        $this->assertSame(40, $rejected);
        $this->assertSame(0, (int) $user->fresh()->credit_balance);

        $this->assertDatabaseCount('inputs', $created);
        $this->assertSame(
            $created,
            $user->creditLedger()->where('reference_type', 'input_creation')->count()
        );

        Event::assertDispatchedTimes(InputCreated::class, $created);
    }

    public function test_burst_input_creation_is_isolated_per_user(): void
    {
        Event::fake([InputCreated::class]);

        $userA = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'credit_balance' => 10,
        ]);

        $userB = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'credit_balance' => 7,
        ]);

        $tokenA = $this->loginAndGetToken($userA);
        $tokenB = $this->loginAndGetToken($userB);

        $activeModel = AIModel::factory()->create(['active' => true]);
        $preset = Preset::factory()->create([
            'default_model_id' => $activeModel->getKey(),
            'active' => true,
        ]);

        $totalRounds = 15;
        $createdA = 0;
        $createdB = 0;

        for ($i = 1; $i <= $totalRounds; $i++) {
            $responseA = $this->withJwt($tokenA)->postJson('/api/input/create', [
                'preset_id' => $preset->getKey(),
                'title' => "A-{$i}",
                'image' => UploadedFile::fake()->image("a-{$i}.png", 900, 900)->size(300),
            ]);

            if ($responseA->status() === 201) {
                $createdA++;
            } else {
                $responseA->assertStatus(422);
            }

            $responseB = $this->withJwt($tokenB)->postJson('/api/input/create', [
                'preset_id' => $preset->getKey(),
                'title' => "B-{$i}",
                'image' => UploadedFile::fake()->image("b-{$i}.png", 900, 900)->size(300),
            ]);

            if ($responseB->status() === 201) {
                $createdB++;
            } else {
                $responseB->assertStatus(422);
            }
        }

        $this->assertSame(10, $createdA);
        $this->assertSame(7, $createdB);
        $this->assertSame(0, (int) $userA->fresh()->credit_balance);
        $this->assertSame(0, (int) $userB->fresh()->credit_balance);

        $this->assertSame(10, $userA->creditLedger()->where('reference_type', 'input_creation')->count());
        $this->assertSame(7, $userB->creditLedger()->where('reference_type', 'input_creation')->count());
    }
}
