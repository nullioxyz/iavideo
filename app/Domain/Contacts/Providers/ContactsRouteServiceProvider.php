<?php

namespace App\Domain\Contacts\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class ContactsRouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(
            base_path('app/Domain/Contacts/Routes/api.php')
        );

        parent::boot();
    }
}

