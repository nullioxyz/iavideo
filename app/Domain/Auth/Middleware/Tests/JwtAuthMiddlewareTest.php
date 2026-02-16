<?php

namespace App\Domain\Auth\Middleware\Tests;

use App\Domain\Auth\Middleware\JwtAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth as IJWTAuth;

class JwtAuthMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_401_when_missing_bearer_token(): void
    {
        $middleware = new JwtAuth;

        $request = Request::create('/anything', 'GET'); // sem Authorization

        $response = $middleware->handle($request, fn () => response('ok', 200));
        $data = json_decode((string) $response->getContent(), true);

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame(
            ['message' => 'Unauthorized', 'error' => 'missing_bearer_token'],
            $data
        );
    }

    public function test_returns_401_when_token_is_invalid(): void
    {
        $middleware = new JwtAuth;

        $request = Request::create('/anything', 'GET', server: [
            'HTTP_AUTHORIZATION' => 'Bearer invalid-token',
        ]);

        IJWTAuth::shouldReceive('parseToken->authenticate')
            ->once()
            ->andThrow(new TokenInvalidException('Token invalid'));

        $response = $middleware->handle($request, fn () => response('ok', 200));
        $data = json_decode((string) $response->getContent(), true);

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame(
            ['message' => 'Unauthorized', 'error' => 'token_invalid'],
            $data
        );
    }

    public function test_allows_request_when_token_is_valid(): void
    {
        $middleware = new JwtAuth;

        $request = Request::create('/anything', 'GET', server: [
            'HTTP_AUTHORIZATION' => 'Bearer valid-token',
        ]);

        // Não precisa criar User real: basta retornar um objeto truthy
        IJWTAuth::shouldReceive('parseToken->authenticate')
            ->once()
            ->andReturn((object) ['id' => 1]);

        $response = $middleware->handle($request, fn () => response()->json(['ok' => true], 200));
        $data = json_decode((string) $response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['ok' => true], $data);
    }
}
