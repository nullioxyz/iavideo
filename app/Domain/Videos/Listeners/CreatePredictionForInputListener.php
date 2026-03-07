<?php

namespace App\Domain\Videos\Listeners;

use App\Domain\Broadcasting\Events\UserJobUpdatedBroadcast;
use App\Domain\Credits\Services\GenerationBillingService;
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
        private readonly GenerationBillingService $billingService,
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

        DB::transaction(function () use ($event, $exception): void {
            $input = Input::query()
                ->whereKey($event->inputId)
                ->lockForUpdate()
                ->with('user')
                ->first();

            if (! $input instanceof Input) {
                return;
            }

            $input->update([
                'status' => Input::FAILED,
            ]);

            $this->billingService->refundInput($input, 'Prediction creation failed after retries', [
                'refund_reason' => 'prediction_creation_failed',
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            event(UserJobUpdatedBroadcast::fromInput($input->refresh()));
        });
    }
}
