<?php

namespace App\Domain\Videos\Listeners;

use App\Domain\Broadcasting\Events\UserJobUpdatedBroadcast;
use App\Domain\Auth\Models\User;
use App\Domain\Credits\UseCases\RefundCreditUseCase;
use App\Domain\Videos\Events\CreatePredictionForInput;
use App\Domain\Videos\Models\Input;
use App\Domain\Videos\UseCases\CreatePredictionForInputUseCase;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreatePredictionForInputListener implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public function __construct(
        private readonly RefundCreditUseCase $refundCreditUseCase,
    ) {}

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [120, 300];
    }

    public function handle(CreatePredictionForInput $event): void
    {
        /** @var CreatePredictionForInputUseCase $useCase */
        $useCase = app()->make(CreatePredictionForInputUseCase::class);

        try {
            $useCase->execute($event->inputId);
        } catch (\Throwable $exception) {
            Log::warning('videos.prediction_creation.retryable_failure', [
                'input_id' => $event->inputId,
                'attempt' => $this->attempts(),
                'max_attempts' => $this->tries,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
            throw $exception;
        }
    }

    public function failed(CreatePredictionForInput $event, \Throwable $exception): void
    {
        Log::error('videos.prediction_creation.failed', [
            'input_id' => $event->inputId,
            'attempt' => $this->attempts(),
            'max_attempts' => $this->tries,
            'exception' => $exception::class,
            'message' => $exception->getMessage(),
        ]);

        DB::transaction(function () use ($event): void {
            $input = Input::query()
                ->whereKey($event->inputId)
                ->lockForUpdate()
                ->with('user')
                ->first();

            if (! $input instanceof Input) {
                return;
            }

            $input->update([
                'status' => Input::CANCELLED,
            ]);

            if ($input->credit_debited && $input->user instanceof User) {
                $this->refundCreditUseCase->execute($input->user, [
                    'reference_type' => 'input_prediction_creation_canceled',
                    'reason' => 'Prediction creation canceled after retries',
                    'reference_id' => $input->getKey(),
                ]);

                $input->update([
                    'credit_debited' => false,
                ]);
            }

            event(UserJobUpdatedBroadcast::fromInput($input->refresh()));
        });
    }
}
