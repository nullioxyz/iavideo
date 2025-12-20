<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Domain\Auth\Providers\AuthServiceProvider::class,
    App\Domain\Auth\Providers\AuthRouteServiceProvider::class,
    App\Domain\AIModels\Providers\IAModelsServiceProvider::class,
    App\Domain\AIModels\Providers\IAModelsRouteServiceProvider::class,
    App\Domain\Videos\Providers\VideosServiceProvider::class,
    App\Domain\Videos\Providers\VideosRouteServiceProvider::class,
    App\Domain\Videos\Providers\EventServiceProvider::class
];
