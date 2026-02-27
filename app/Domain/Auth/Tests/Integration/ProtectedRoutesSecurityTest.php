<?php

namespace App\Domain\Auth\Tests\Integration;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProtectedRoutesSecurityTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('protectedRoutesProvider')]
    public function test_protected_routes_require_authentication(string $method, string $uri, array $payload = []): void
    {
        $response = match (strtoupper($method)) {
            'GET' => $this->getJson($uri),
            'POST' => $this->postJson($uri, $payload),
            'PATCH' => $this->patchJson($uri, $payload),
            default => throw new \RuntimeException("Unsupported method: {$method}"),
        };

        $response->assertUnauthorized();
        $response->assertJsonPath('message', 'Unauthorized');
    }

    /**
     * @return array<string, array{0:string, 1:string, 2?:array<string,mixed>}>
     */
    public static function protectedRoutesProvider(): array
    {
        return [
            // Auth
            'auth.me' => ['GET', '/api/auth/me'],
            'auth.first_login_reset' => ['POST', '/api/auth/first-login/reset-password', [
                'current_password' => 'password123',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ]],
            'auth.impersonation_exchange' => ['POST', '/api/auth/impersonation/exchange', [
                'hash' => str_repeat('a', 64),
            ]],
            'auth.preferences_update' => ['PATCH', '/api/auth/preferences', [
                'theme_preference' => 'dark',
            ]],

            // Credits
            'credits.balance' => ['GET', '/api/credits/balance'],
            'credits.statement' => ['GET', '/api/credits/statement'],
            'credits.video_generations' => ['GET', '/api/credits/video-generations'],

            // Videos / Jobs
            'input.create' => ['POST', '/api/input/create', [
                'preset_id' => 1,
            ]],
            'prediction.cancel' => ['POST', '/api/prediction/cancel', [
                'input_id' => 1,
            ]],
            'jobs.list' => ['GET', '/api/jobs'],
            'jobs.quota' => ['GET', '/api/jobs/quota'],
            'jobs.detail' => ['GET', '/api/jobs/1'],
            'jobs.download' => ['GET', '/api/jobs/1/download'],
            'jobs.cancel' => ['POST', '/api/jobs/1/cancel'],
            'jobs.rename' => ['PATCH', '/api/jobs/1/title', [
                'title' => 'Novo título',
            ]],

            // Analytics
            'analytics.mvp_kpis' => ['GET', '/api/analytics/mvp-kpis'],
            'analytics.ops_metrics' => ['GET', '/api/analytics/ops-metrics'],

            // AI Models / Presets
            'models.list' => ['GET', '/api/models'],
            'models.presets' => ['GET', '/api/models/1/presets'],
            'models.presets.filters' => ['GET', '/api/models/1/presets/filters'],

            // Payments
            'payments.purchase_credits' => ['POST', '/api/payments/credits/purchase', [
                'credits' => 10,
            ]],

            // Invites (protected endpoint)
            'invites.redeem' => ['POST', '/api/invites/redeem', [
                'code' => 'ABCDE12345',
            ]],
        ];
    }
}
