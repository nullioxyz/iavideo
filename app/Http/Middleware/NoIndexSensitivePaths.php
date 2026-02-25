<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoIndexSensitivePaths
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if ($this->isSensitivePath($request)) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet');
        }

        return $response;
    }

    private function isSensitivePath(Request $request): bool
    {
        return $request->is('api/*')
            || $request->is('admin')
            || $request->is('admin/*');
    }
}

