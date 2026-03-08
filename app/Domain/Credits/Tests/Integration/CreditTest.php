<?php

namespace App\Domain\Credits\Tests\Integration;

use App\Domain\AIModels\Models\Model;
use App\Domain\AIModels\Models\Model as AIModel;
use App\Domain\AIModels\Models\Preset;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Tests\Traits\AuthenticatesWithJwt;
use App\Domain\Credits\Models\CreditLedger;
use App\Domain\Videos\Events\InputCreated;
use App\Domain\Videos\Models\Input;
use App\Domain\Videos\Models\Prediction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CreditTest extends TestCase
{
    use AuthenticatesWithJwt;
    use RefreshDatabase;

    public function test_create_input_and_credit_ledger_and_charge_credit(): void
    {
        Event::fake([InputCreated::class]);

        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'credit_balance' => 30,
        ]);

        $token = $this->loginAndGetToken($user);

        $activeModel = AIModel::factory()->create([
            'active' => true,
            'public_visible' => true,
            'cost_per_second_usd' => '0.0700',
            'credits_per_second' => '5.0000',
        ]);

        $preset = Preset::factory()->create([
            'default_model_id' => $activeModel->getKey(),
            'active' => true,
        ]);

        $image = UploadedFile::fake()->image('tattoo.png', 900, 1600)->size(500);

        $response = $this->withJwt($token)->postJson('/api/input/create', [
            'model_id' => $activeModel->getKey(),
            'preset_id' => $preset->getKey(),
            'duration_seconds' => 5,
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
            'original_filename' => 'tattoo.png',
            'mime_type' => 'image/png',
            'duration_seconds' => 5,
            'estimated_cost_usd' => '0.3500',
            'model_cost_per_second_usd' => '0.0700',
            'model_credits_per_second' => '5.0000',
            'credits_charged' => 25,
            'billing_status' => 'charged',
        ]);

        $this->assertDatabaseHas('credit_ledger', [
            'user_id' => $user->getKey(),
            'reference_id' => $inputId,
            'reference_type' => 'input_generation',
            'operation_type' => 'generation_debit',
            'delta' => -25,
            'balance_after' => 5,
        ]);

        $ledger = CreditLedger::query()
            ->where('user_id', $user->getKey())
            ->where('reference_id', $inputId)
            ->where('operation_type', 'generation_debit')
            ->first();

        $this->assertNotNull($ledger);
        $this->assertSame('0.0700', $ledger->metadata['cost_per_second_usd'] ?? null);
        $this->assertSame('5.0000', $ledger->metadata['credits_per_second'] ?? null);

        $this->assertEquals(5, $user->fresh()->credit_balance);

        $input = Input::query()->findOrFail($inputId);

        Event::assertDispatched(InputCreated::class, function (InputCreated $event) use ($inputId) {
            return $event->inputId === $inputId
                && str_contains($event->tempPath, 'tmp/inputs');
        });
    }

    public function test_receive_replicate_prediction_failed_video_generation_and_refund_credit(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'credit_balance' => 30,
        ]);

        $activeModel = Model::factory()->create([
            'active' => true,
            'public_visible' => true,
            'cost_per_second_usd' => '0.0700',
            'credits_per_second' => '5.0000',
        ]);

        $preset = Preset::factory()->create([
            'default_model_id' => $activeModel->getKey(),
            'active' => true,
        ]);

        $input = Input::factory()->create([
            'user_id' => $user->getKey(),
            'model_id' => $activeModel->getKey(),
            'preset_id' => $preset->getKey(),
            'duration_seconds' => 5,
            'estimated_cost_usd' => '0.3500',
            'model_cost_per_second_usd' => '0.0700',
            'model_credits_per_second' => '5.0000',
            'credits_charged' => 25,
            'billing_status' => 'charged',
            'status' => 'created',
        ]);

        $prediction = Prediction::factory()->create([
            'input_id' => $input->id,
            'model_id' => $activeModel->id,
            'external_id' => 'ufawqhfynnddngldkgtslldrkq',
            'status' => 'submitting',
            'source' => 'web',
        ]);

        $creditLedger = CreditLedger::factory()->create([
            'user_id' => $user->getKey(),
            'delta' => -25,
            'balance_before' => 30,
            'balance_after' => 5,
            'reason' => 'Video generation charge',
            'operation_type' => 'generation_debit',
            'reference_type' => 'input_generation',
            'reference_id' => $input->getKey(),
            'model_id' => $activeModel->getKey(),
            'preset_id' => $preset->getKey(),
            'duration_seconds' => 5,
            'generation_cost_usd' => '0.3500',
            'metadata' => [
                'cost_per_second_usd' => '0.0700',
                'credits_per_second' => '5.0000',
            ],
        ]);

        $input->credit_debited = true;
        $input->credit_ledger_id = $creditLedger->getKey();
        $input->save();
        $user->credit_balance = 5;
        $user->save();

        $response = $this->post('/api/webhook/replicate', [
            'id' => 'ufawqhfynnddngldkgtslldrkq',
            'version' => '5c7d5dc6dd8bf75c1acaa8565735e7986bc5b66206b55cca93cb72c9bf15ccaa',
            'created_at' => '2022-04-26T22:13:06.224088Z',
            'started_at' => null,
            'completed_at' => null,
            'status' => 'failed',
            'input' => [
                'text' => 'Alice',
            ],
            'output' => null,
            'error' => null,
            'logs' => null,
            'metrics' => [],
        ]);

        $response->assertNoContent();

        $prediction->refresh();

        $this->assertEquals('failed', $prediction->status);
        $this->assertCount(0, $prediction->outputs);

        $this->assertDatabaseHas('inputs', [
            'id' => $input->getKey(),
            'user_id' => $user->getKey(),
            'preset_id' => $preset->getKey(),
            'status' => 'failed',
            'credit_debited' => false,
        ]);

        $this->assertDatabaseHas('credit_ledger', [
            'user_id' => $user->getKey(),
            'reference_id' => $input->getKey(),
            'reference_type' => 'input_generation',
            'operation_type' => 'generation_debit',
            'delta' => -25,
            'balance_after' => 5,
        ]);

        $this->assertDatabaseHas('credit_ledger', [
            'user_id' => $user->getKey(),
            'reference_id' => $input->getKey(),
            'reference_type' => 'input_generation',
            'operation_type' => 'generation_refund',
            'delta' => 25,
            'balance_after' => 30,
        ]);

        $refundLedger = CreditLedger::query()
            ->where('user_id', $user->getKey())
            ->where('reference_id', $input->getKey())
            ->where('operation_type', 'generation_refund')
            ->first();

        $this->assertNotNull($refundLedger);
        $this->assertSame('0.0700', $refundLedger->metadata['cost_per_second_usd'] ?? null);
        $this->assertSame('5.0000', $refundLedger->metadata['credits_per_second'] ?? null);
        $this->assertSame(30, (int) $user->fresh()->credit_balance);

        $prediction->refresh();
        $this->assertEquals('failed', $prediction->status);
    }
}
