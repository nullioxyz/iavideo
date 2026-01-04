<?php

namespace App\Domain\Videos\Listeners;

use App\Domain\Videos\Events\CreatePredictionForInput;
use App\Domain\Videos\Models\Input;
use App\Domain\Videos\UseCases\CreatePredictionForInputUseCase;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreatePredictionForInputListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(CreatePredictionForInput $event): void
    {
        /** @var Input $input */
        $useCase = app()->make(CreatePredictionForInputUseCase::class);
        $useCase->execute($event->inputId);
    }
}
