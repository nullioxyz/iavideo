<?php

namespace App\Domain\Videos\Providers;

use App\Domain\Videos\Events\CreatePredictionForInput;
use App\Domain\Videos\Events\InputCreated;
use App\Domain\Videos\Listeners\CreatePredictionForInputListener;
use App\Domain\Videos\Listeners\UploadInputImageListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        InputCreated::class => [
            UploadInputImageListener::class,
        ],
        CreatePredictionForInput::class => [
            CreatePredictionForInputListener::class,
        ],
    ];
}
