<?php

namespace App\Domain\Operations\Tests\Integration;

use App\Domain\Analytics\UseCases\GetOpsMetricsUseCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class MonitorOpsHealthCommandTest extends TestCase
{
    public function test_monitor_health_returns_success_when_metrics_are_healthy(): void
    {
        config()->set('ops_monitor.thresholds', [
            'queue_pending_jobs' => 200,
        ]);

        $stub = new class extends GetOpsMetricsUseCase {
            public function execute(): array
            {
                return [
                    'queue_pending_jobs' => 10,
                ];
            }
        };

        Log::spy();
        $this->app->instance(GetOpsMetricsUseCase::class, $stub);

        $exitCode = Artisan::call('ops:monitor-health', ['--json' => true]);

        $this->assertSame(0, $exitCode);
        Log::shouldHaveReceived('info')->once();
    }

    public function test_monitor_health_returns_failure_when_threshold_is_exceeded(): void
    {
        config()->set('ops_monitor.thresholds', [
            'queue_pending_jobs' => 50,
        ]);

        $stub = new class extends GetOpsMetricsUseCase {
            public function execute(): array
            {
                return [
                    'queue_pending_jobs' => 120,
                ];
            }
        };

        Log::spy();
        $this->app->instance(GetOpsMetricsUseCase::class, $stub);

        $exitCode = Artisan::call('ops:monitor-health', ['--json' => true]);

        $this->assertSame(1, $exitCode);
        Log::shouldHaveReceived('warning')->once();
    }
}
