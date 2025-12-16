<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->header('Accept-Language');

        if (is_string($locale)) {
            $primary = strtolower(trim(explode(',', $locale)[0]));

            $map = [
                'pt-br' => 'pt_BR',
                'pt' => 'pt_BR',
                'en' => 'en',
            ];

            $chosen = $map[$primary] ?? null;

            if ($chosen && in_array($chosen, ['en', 'pt_BR'], true)) {
                App::setLocale($chosen);
            }
        }

        return $next($request);
    }
}
