<?php

namespace App\Domain\Analytics\Tests\Integration;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Tests\Traits\AuthenticatesWithJwt;
use App\Domain\Videos\Models\Prediction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OpsMetricsEndpointTest extends TestCase
{
    use AuthenticatesWithJwt;
    use RefreshDatabase;

    public function test_admin_can_access_ops_metrics_endpoint(): void
    {
        Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);

        $admin = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        Prediction::factory()->create([
            'status' => Prediction::FAILED,
            'total_ms' => 1200,
            'created_at' => now(),
        ]);

        $token = $this->loginAndGetToken($admin);

        $response = $this->withJwt($token)->getJson('/api/analytics/ops-metrics');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'queue_pending_jobs',
                'queue_failed_jobs_last_24h',
                'prediction_failures_last_24h',
                'ai_latency_avg_ms_last_24h',
                'ai_latency_p95_ms_last_24h',
            ],
        ]);
    }

    public function test_non_admin_cannot_access_ops_metrics_endpoint(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
        ]);

        $token = $this->loginAndGetToken($user);

        $this->withJwt($token)
            ->getJson('/api/analytics/ops-metrics')
            ->assertForbidden();
    }
}
