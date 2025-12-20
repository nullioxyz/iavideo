<?php

namespace App\Domain\Videos\Providers;

use App\Domain\Videos\Events\InputCreated;
use App\Domain\Videos\Listeners\UploadInputImage;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        InputCreated::class => [
            UploadInputImage::class,
        ],
    ];
}
