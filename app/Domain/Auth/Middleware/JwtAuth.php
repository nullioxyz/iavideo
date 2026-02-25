<?php

namespace App\Domain\Auth\Middleware;

use App\Domain\Auth\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth as iJWTAuth;

final class JwtAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->hasBearerToken($request)) {
            return $this->unauthorized('missing_bearer_token');
        }

        try {
            $user = iJWTAuth::parseToken()->authenticate();

            if (! $user) {
                return $this->unauthorized('user_not_found');
            }

            if (! $user instanceof User) {
                $identifier = method_exists($user, 'getAuthIdentifier')
                    ? $user->getAuthIdentifier()
                    : null;

                $user = $identifier !== null
                    ? User::query()->whereKey($identifier)->first()
                    : null;

                if (! $user instanceof User) {
                    return $this->unauthorized('user_not_found');
                }
            }

            auth('api')->setUser($user);
            $request->setUserResolver(
                static fn () => auth('api')->user()
            );
        } catch (TokenExpiredException) {
            return $this->unauthorized('token_expired');
        } catch (TokenInvalidException) {
            return $this->unauthorized('token_invalid');
        } catch (JWTException) {
            return $this->unauthorized('invalid_or_expired_token');
        }

        return $next($request);
    }

    private function hasBearerToken(Request $request): bool
    {
        $header = (string) $request->header('Authorization', '');

        return Str::startsWith($header, 'Bearer ') && trim(Str::after($header, 'Bearer ')) !== '';
    }

    private function unauthorized(string $error): Response
    {
        return response()->json([
            'message' => 'Unauthorized',
            'error' => $error,
        ], 401);
    }
}
