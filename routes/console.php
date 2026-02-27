<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Domain\Analytics\UseCases\GetOpsMetricsUseCase;
use App\Domain\Operations\UseCases\RunBetaSmokeChecksUseCase;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('ops:smoke-beta {--strict} {--json}', function (): int {
    /** @var RunBetaSmokeChecksUseCase $useCase */
    $useCase = app(RunBetaSmokeChecksUseCase::class);

    $result = $useCase->execute((bool) $this->option('strict'));

    if ((bool) $this->option('json')) {
        $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    } else {
        foreach ($result['checks'] as $name => $check) {
            $label = $check['ok'] ? 'OK' : 'FAIL';
            $this->line("[{$label}] {$name}: {$check['detail']}");
        }
    }

    return $result['passed'] ? 0 : 1;
})->purpose('Run baseline smoke checks for closed beta readiness.');

Artisan::command('ops:monitor-health {--json}', function (): int {
    /** @var GetOpsMetricsUseCase $useCase */
    $useCase = app(GetOpsMetricsUseCase::class);

    $metrics = $useCase->execute();
    $thresholds = (array) config('ops_monitor.thresholds', []);
    $alerts = [];

    foreach ($thresholds as $metric => $threshold) {
        $value = (float) ($metrics[$metric] ?? 0);
        if ($value > (float) $threshold) {
            $alerts[] = [
                'metric' => (string) $metric,
                'value' => $value,
                'threshold' => (float) $threshold,
            ];
        }
    }

    if ((bool) $this->option('json')) {
        $this->line(json_encode([
            'metrics' => $metrics,
            'alerts' => $alerts,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    if ($alerts !== []) {
        Log::warning('ops.alert.threshold_exceeded', [
            'alerts' => $alerts,
            'metrics' => $metrics,
        ]);
        $this->warn('Operational thresholds exceeded.');

        return 1;
    }

    Log::info('ops.alert.healthy', ['metrics' => $metrics]);
    $this->info('Operational metrics are healthy.');

    return 0;
})->purpose('Evaluate ops metrics against thresholds and raise structured alerts.');
