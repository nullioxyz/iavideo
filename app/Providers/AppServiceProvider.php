<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('payments-create', function (Request $request) {
            $identifier = (string) ($request->user('api')?->getAuthIdentifier() ?? $request->ip());

            return [
                Limit::perMinute(10)->by($identifier),
            ];
        });

        RateLimiter::for('payments-webhook', function (Request $request) {
            return [
                Limit::perMinute(120)->by((string) $request->ip()),
            ];
        });

        RateLimiter::for('auth-login', function (Request $request) {
            $email = mb_strtolower(trim((string) $request->input('email', '')));
            $ip = (string) $request->ip();

            return [
                Limit::perMinute(8)->by($ip.'|'.$email),
                Limit::perMinute(20)->by($ip),
            ];
        });

        RateLimiter::for('auth-user-main', function (Request $request) {
            $identifier = (string) ($request->user('api')?->getAuthIdentifier() ?? $request->ip());

            return [
                Limit::perMinute(120)->by($identifier),
            ];
        });
    }
}
