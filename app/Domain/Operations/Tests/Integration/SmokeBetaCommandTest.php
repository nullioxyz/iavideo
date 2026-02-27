<?php

namespace App\Domain\Operations\Tests\Integration;

use App\Domain\Operations\UseCases\RunBetaSmokeChecksUseCase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SmokeBetaCommandTest extends TestCase
{
    public function test_smoke_beta_command_returns_success_when_all_checks_pass(): void
    {
        $stub = new class extends RunBetaSmokeChecksUseCase {
            public function execute(bool $strict = false): array
            {
                return [
                    'passed' => true,
                    'checks' => [
                        'db_connection' => ['ok' => true, 'detail' => 'ok'],
                    ],
                ];
            }
        };

        $this->app->instance(RunBetaSmokeChecksUseCase::class, $stub);

        $exitCode = Artisan::call('ops:smoke-beta', ['--json' => true]);

        $this->assertSame(0, $exitCode);
    }

    public function test_smoke_beta_command_returns_failure_when_any_check_fails(): void
    {
        $stub = new class extends RunBetaSmokeChecksUseCase {
            public function execute(bool $strict = false): array
            {
                return [
                    'passed' => false,
                    'checks' => [
                        'db_connection' => ['ok' => false, 'detail' => 'failed'],
                    ],
                ];
            }
        };

        $this->app->instance(RunBetaSmokeChecksUseCase::class, $stub);

        $exitCode = Artisan::call('ops:smoke-beta', ['--json' => true]);

        $this->assertSame(1, $exitCode);
    }
}
