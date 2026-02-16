<?php

namespace App\Domain\Videos\Listeners;

use App\Domain\Videos\Events\CancelPredictionInput;
use App\Domain\Videos\UseCases\CancelInputPredictionUseCase;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CancelPredictionInputListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(CancelPredictionInput $event): void
    {
        /** @var CancelInputPredictionUseCase $useCase */
        $useCase = app()->make(CancelInputPredictionUseCase::class);
        $useCase->execute($event->inputId);
    }
}
