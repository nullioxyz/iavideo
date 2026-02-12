<?php

return [
    App\Domain\AIModels\Providers\IAModelsRouteServiceProvider::class,
    App\Domain\AIModels\Providers\IAModelsServiceProvider::class,
    App\Domain\AIProviders\Providers\AIProvidersRouteServiceProvider::class,
    App\Domain\Invites\Providers\InvitesRouteServiceProvider::class,
    App\Domain\Invites\Providers\InvitesServiceProvider::class,
    App\Domain\Auth\Providers\AuthRouteServiceProvider::class,
    App\Domain\Auth\Providers\AuthServiceProvider::class,
    App\Domain\Videos\Providers\EventServiceProvider::class,
    App\Domain\Videos\Providers\VideosRouteServiceProvider::class,
    App\Domain\Videos\Providers\VideosServiceProvider::class,
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Domain\Credits\Providers\CreditServiceProvider::class,
];
