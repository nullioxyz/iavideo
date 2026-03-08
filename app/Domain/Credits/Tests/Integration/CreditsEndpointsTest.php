<?php

namespace App\Domain\Credits\Tests\Integration;

use App\Domain\AIModels\Models\Model as AIModel;
use App\Domain\AIModels\Models\Preset;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Tests\Traits\AuthenticatesWithJwt;
use App\Domain\Videos\Models\Input;
use App\Domain\Videos\Models\Prediction;
use App\Domain\Videos\Models\PredictionOutput;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditsEndpointsTest extends TestCase
{
    use AuthenticatesWithJwt;
    use RefreshDatabase;

    public function test_balance_returns_authenticated_user_balance(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'credit_balance' => 12,
        ]);

        $token = $this->loginAndGetToken($user);

        $response = $this->withJwt($token)->getJson('/api/credits/balance');

        $response->assertOk();
        $response->assertJsonPath('data.user_id', $user->getKey());
        $response->assertJsonPath('data.credit_balance', 12);
    }

    public function test_statement_returns_only_authenticated_user_entries(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'credit_balance' => 3,
        ]);

        $otherUser = User::factory()->create();
        $token = $this->loginAndGetToken($user);

        $user->creditLedger()->create([
            'delta' => -1,
            'balance_before' => 3,
            'balance_after' => 2,
            'reason' => 'Video generation charge',
            'operation_type' => 'generation_debit',
            'reference_type' => 'input_generation',
            'reference_id' => 1,
            'metadata' => [
                'cost_per_second_usd' => '0.0700',
                'credits_per_second' => '5.0000',
            ],
        ]);

        $otherUser->creditLedger()->create([
            'delta' => -1,
            'balance_before' => 2,
            'balance_after' => 1,
            'reason' => 'Other user',
            'operation_type' => 'generation_debit',
            'reference_type' => 'input_generation',
            'reference_id' => 2,
        ]);

        $response = $this->withJwt($token)->getJson('/api/credits/statement');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.reason', 'Video generation charge');
        $response->assertJsonPath('data.0.operation_type', 'generation_debit');
        $response->assertJsonPath('data.0.cost_per_second_usd', '0.0700');
        $response->assertJsonPath('data.0.credits_per_second', '5.0000');
    }

    public function test_video_generations_returns_only_authenticated_user_history_with_credit_usage(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'credit_balance' => 3,
        ]);

        $otherUser = User::factory()->create();
        $token = $this->loginAndGetToken($user);

        $model = AIModel::factory()->create(['active' => true]);
        $preset = Preset::factory()->create([
            'default_model_id' => $model->getKey(),
            'active' => true,
        ]);

        $input = Input::factory()->create([
            'user_id' => $user->getKey(),
            'model_id' => $model->getKey(),
            'preset_id' => $preset->getKey(),
            'title' => 'Video teste',
            'status' => Input::DONE,
        ]);

        $prediction = Prediction::factory()->create([
            'input_id' => $input->getKey(),
            'model_id' => $model->getKey(),
            'status' => Prediction::SUCCEEDED,
        ]);

        PredictionOutput::factory()->create([
            'prediction_id' => $prediction->getKey(),
            'kind' => 'video',
            'path' => 'https://cdn.example.com/video.mp4',
        ]);

        $user->creditLedger()->create([
            'delta' => -1,
            'balance_before' => 3,
            'balance_after' => 2,
            'reason' => 'Video generation charge',
            'operation_type' => 'generation_debit',
            'reference_type' => 'input_generation',
            'reference_id' => $input->getKey(),
            'model_id' => $model->getKey(),
            'preset_id' => $preset->getKey(),
            'duration_seconds' => 5,
            'generation_cost_usd' => '0.3500',
        ]);

        $user->creditLedger()->create([
            'delta' => 1,
            'balance_before' => 2,
            'balance_after' => 3,
            'reason' => 'Failed video generation',
            'operation_type' => 'generation_refund',
            'reference_type' => 'input_generation',
            'reference_id' => $input->getKey(),
            'metadata' => ['refund_reason' => 'provider_failed'],
        ]);

        $otherInput = Input::factory()->create([
            'user_id' => $otherUser->getKey(),
            'preset_id' => $preset->getKey(),
        ]);

        $otherUser->creditLedger()->create([
            'delta' => -1,
            'balance_before' => 1,
            'balance_after' => 0,
            'reason' => 'Other user',
            'operation_type' => 'generation_debit',
            'reference_type' => 'input_generation',
            'reference_id' => $otherInput->getKey(),
        ]);

        $response = $this->withJwt($token)->getJson('/api/credits/video-generations');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.input_id', $input->getKey());
        $response->assertJsonPath('data.0.title', 'Video teste');
        $response->assertJsonPath('data.0.model.id', $model->getKey());
        $response->assertJsonPath('data.0.preset.id', $preset->getKey());
        $response->assertJsonPath('data.0.prediction.id', $prediction->getKey());
        $response->assertJsonPath('data.0.prediction.output_video_url', 'https://cdn.example.com/video.mp4');
        $response->assertJsonPath('data.0.credits_debited', 1);
        $response->assertJsonPath('data.0.credits_refunded', 1);
        $response->assertJsonPath('data.0.credits_used', 0);
        $response->assertJsonPath('data.0.is_refunded', true);
        $response->assertJsonPath('data.0.failure.reason', 'Failed video generation');
        $response->assertJsonPath('data.0.ledger_entries_count', 2);
        $response->assertJsonPath('data.0.ledger_entries.0.operation', 'debit');
        $response->assertJsonPath('data.0.ledger_entries.1.operation', 'refund');
        $response->assertJsonPath('data.0.ledger_entries.0.operation_type', 'generation_debit');
        $response->assertJsonPath('data.0.ledger_entries.1.operation_type', 'generation_refund');
        $response->assertJsonPath('data.0.credit_events.0.type', 'debit');
        $response->assertJsonPath('data.0.credit_events.1.type', 'refund');
    }

    public function test_video_generations_exposes_cancellation_and_refund_details(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'credit_balance' => 3,
        ]);

        $token = $this->loginAndGetToken($user);

        $model = AIModel::factory()->create(['active' => true]);
        $preset = Preset::factory()->create([
            'default_model_id' => $model->getKey(),
            'active' => true,
        ]);

        $input = Input::factory()->create([
            'user_id' => $user->getKey(),
            'model_id' => $model->getKey(),
            'preset_id' => $preset->getKey(),
            'title' => 'Video cancelado',
            'status' => Input::CANCELLED,
        ]);

        Prediction::factory()->create([
            'input_id' => $input->getKey(),
            'model_id' => $model->getKey(),
            'status' => Prediction::CANCELLED,
        ]);

        $user->creditLedger()->create([
            'delta' => -1,
            'balance_before' => 3,
            'balance_after' => 2,
            'reason' => 'Video generation charge',
            'operation_type' => 'generation_debit',
            'reference_type' => 'input_generation',
            'reference_id' => $input->getKey(),
        ]);

        $user->creditLedger()->create([
            'delta' => 1,
            'balance_before' => 2,
            'balance_after' => 3,
            'reason' => 'Canceled video generation',
            'operation_type' => 'generation_refund',
            'reference_type' => 'input_generation',
            'reference_id' => $input->getKey(),
            'metadata' => ['refund_reason' => 'cancelled'],
        ]);

        $response = $this->withJwt($token)->getJson('/api/credits/video-generations');

        $response->assertOk();
        $response->assertJsonPath('data.0.input_id', $input->getKey());
        $response->assertJsonPath('data.0.is_canceled', true);
        $response->assertJsonPath('data.0.is_refunded', true);
        $response->assertJsonPath('data.0.cancellation.reason', 'Canceled video generation');
        $response->assertJsonPath('data.0.ledger_entries_count', 2);
    }
}
