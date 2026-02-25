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

    public function __construct(
        private readonly RefundCreditUseCase $refundCreditUseCase,
    ) {}

    public function handle(CreatePredictionForInput $event): void
    {
        /** @var CreatePredictionForInputUseCase $useCase */
        $useCase = app()->make(CreatePredictionForInputUseCase::class);

        try {
            $useCase->execute($event->inputId);
        } catch (\Throwable $exception) {
            $this->handleFailure($event->inputId, $exception);
            throw $exception;
        }
    }

    private function handleFailure(int $inputId, \Throwable $exception): void
    {
        Log::error('videos.prediction_creation.failed', [
            'input_id' => $inputId,
            'exception' => $exception::class,
            'message' => $exception->getMessage(),
        ]);

        DB::transaction(function () use ($inputId): void {
            $input = Input::query()
                ->whereKey($inputId)
                ->lockForUpdate()
                ->with('user')
                ->first();

            if (! $input instanceof Input) {
                return;
            }

            $input->update([
                'status' => Input::FAILED,
            ]);

            if ($input->credit_debited && $input->user instanceof User) {
                $this->refundCreditUseCase->execute($input->user, [
                    'reference_type' => 'input_prediction_creation_failed',
                    'reason' => 'Prediction creation failed',
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
