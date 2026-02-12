<?php

namespace App\Domain\Invites\Providers;

use App\Domain\Invites\Contracts\Repositories\InviteRepositoryInterface;
use App\Domain\Invites\Repositories\InviteRepository;
use Illuminate\Support\ServiceProvider;

class InvitesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InviteRepositoryInterface::class, InviteRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
