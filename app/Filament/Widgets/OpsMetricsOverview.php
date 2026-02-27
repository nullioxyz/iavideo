<?php

namespace App\Filament\Widgets;

use App\Domain\Analytics\UseCases\GetOpsMetricsUseCase;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OpsMetricsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Operations';

    protected function getStats(): array
    {
        $metrics = app(GetOpsMetricsUseCase::class)->execute();

        return [
            Stat::make('Queue pending jobs', (string) (int) $metrics['queue_pending_jobs'])
                ->description('Current queue backlog'),
            Stat::make('Failed jobs (24h)', (string) (int) $metrics['queue_failed_jobs_last_24h'])
                ->description('Queue failures in last 24 hours'),
            Stat::make('Prediction failures (24h)', (string) (int) $metrics['prediction_failures_last_24h'])
                ->description('Failed predictions in last 24 hours'),
            Stat::make('AI latency avg (24h)', number_format((float) $metrics['ai_latency_avg_ms_last_24h'], 2).' ms')
                ->description('Average provider processing latency'),
            Stat::make('AI latency p95 (24h)', number_format((float) $metrics['ai_latency_p95_ms_last_24h'], 2).' ms')
                ->description('95th percentile latency'),
        ];
    }

    public static function canView(): bool
    {
        return auth()->check();
    }
}
