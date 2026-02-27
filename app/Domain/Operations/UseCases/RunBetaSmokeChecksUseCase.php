<?php

namespace App\Domain\Operations\UseCases;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class RunBetaSmokeChecksUseCase
{
    /**
     * @return array{passed:bool,checks:array<string,array{ok:bool,detail:string}>}
     */
    public function execute(bool $strict = false): array
    {
        $checks = [];

        $checks['db_connection'] = $this->safeCheck(function (): string {
            DB::connection()->getPdo();

            return 'Database connection is healthy.';
        });

        $checks['jobs_table'] = [
            'ok' => Schema::hasTable('jobs'),
            'detail' => Schema::hasTable('jobs')
                ? 'jobs table exists.'
                : 'jobs table missing.',
        ];

        $checks['failed_jobs_table'] = [
            'ok' => Schema::hasTable('failed_jobs'),
            'detail' => Schema::hasTable('failed_jobs')
                ? 'failed_jobs table exists.'
                : 'failed_jobs table missing.',
        ];

        $checks['required_routes'] = $this->checkRequiredRoutes();

        $requireRedis = $strict || (bool) config('ops_smoke.require_redis', false);
        $checks['redis_connection'] = $requireRedis
            ? $this->safeCheck(function (): string {
                $pong = Redis::connection()->ping();

                return 'Redis ping returned: '.(string) $pong;
            })
            : [
                'ok' => true,
                'detail' => 'Redis check skipped (non-strict mode).',
            ];

        $checks['queue_driver'] = [
            'ok' => config('queue.default') !== 'sync',
            'detail' => 'QUEUE_CONNECTION='.(string) config('queue.default'),
        ];

        $passed = collect($checks)->every(fn (array $check): bool => (bool) ($check['ok'] ?? false));

        return [
            'passed' => $passed,
            'checks' => $checks,
        ];
    }

    /**
     * @return array{ok:bool,detail:string}
     */
    private function checkRequiredRoutes(): array
    {
        $required = [
            'auth.login',
            'jobs.list',
            'jobs.quota',
            'jobs.download',
            'analytics.mvp-kpis',
            'analytics.ops-metrics',
            'institutional.list',
            'seo.show',
            'contacts.create',
        ];

        $missing = array_values(array_filter($required, fn (string $name): bool => ! Route::has($name)));

        return [
            'ok' => $missing === [],
            'detail' => $missing === []
                ? 'All required routes are registered.'
                : 'Missing routes: '.implode(', ', $missing),
        ];
    }

    /**
     * @param  callable():string  $callback
     * @return array{ok:bool,detail:string}
     */
    private function safeCheck(callable $callback): array
    {
        try {
            return [
                'ok' => true,
                'detail' => $callback(),
            ];
        } catch (\Throwable $exception) {
            return [
                'ok' => false,
                'detail' => $exception->getMessage(),
            ];
        }
    }
}
