<?php

namespace App\Domain\Videos\Listeners\Tests;

use App\Domain\Auth\Models\User;
use App\Domain\Broadcasting\Events\UserJobUpdatedBroadcast;
use App\Domain\Credits\Models\CreditLedger;
use App\Domain\Videos\Events\CreatePredictionForInput;
use App\Domain\Videos\Listeners\CreatePredictionForInputListener;
use App\Domain\Videos\Models\Input;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CreatePredictionForInputListenerTest extends TestCase
{
    use RefreshDatabase;

    public function test_listener_has_expected_retry_configuration(): void
    {
        $listener = app(CreatePredictionForInputListener::class);

        $this->assertSame(3, $listener->tries);
        $this->assertSame([120, 300], $listener->backoff());
    }

    public function test_listener_final_failure_marks_input_failed_and_refunds_credit(): void
    {
        Event::fake([UserJobUpdatedBroadcast::class]);

        $user = User::factory()->create([
            'active' => true,
            'credit_balance' => 0,
        ]);

        $input = Input::factory()->create([
            'user_id' => $user->getKey(),
            'model_cost_per_second_usd' => '0.0700',
            'model_credits_per_second' => '5.0000',
            'credits_charged' => 1,
            'billing_status' => 'charged',
            'credit_debited' => true,
            'status' => Input::PROCESSING,
        ]);

        $listener = app(CreatePredictionForInputListener::class);
        $listener->failed(
            new CreatePredictionForInput((int) $input->getKey()),
            new \RuntimeException('provider unavailable')
        );

        $this->assertSame(Input::FAILED, (string) $input->fresh()->status);
        $this->assertFalse((bool) $input->fresh()->credit_debited);

        $ledger = CreditLedger::query()
            ->where('user_id', $user->getKey())
            ->where('reference_type', 'input_generation')
            ->where('operation_type', 'generation_refund')
            ->where('reference_id', $input->getKey())
            ->latest('id')
            ->first();

        $this->assertNotNull($ledger);
        $this->assertSame('0.0700', $ledger->metadata['cost_per_second_usd'] ?? null);
        $this->assertSame('5.0000', $ledger->metadata['credits_per_second'] ?? null);
        $this->assertSame(1, (int) $user->fresh()->credit_balance);

        Event::assertDispatched(UserJobUpdatedBroadcast::class);
    }
}
