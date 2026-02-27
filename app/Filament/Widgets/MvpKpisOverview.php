<?php

namespace App\Filament\Widgets;

use App\Domain\Analytics\UseCases\GetMvpKpisUseCase;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MvpKpisOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'MVP KPIs';

    protected function getStats(): array
    {
        $metrics = app(GetMvpKpisUseCase::class)->execute();

        return [
            Stat::make('Activation rate', number_format((float) $metrics['activation_rate_percent'], 2).' %')
                ->description('% users that generated first video'),
            Stat::make('Avg time to ready', number_format((float) $metrics['avg_time_to_ready_seconds'], 2).' s')
                ->description('Average time until video is ready'),
            Stat::make('Prediction failure rate', number_format((float) $metrics['prediction_failure_rate_percent'], 2).' %')
                ->description('Failed predictions over total'),
            Stat::make('Retry success rate', number_format((float) $metrics['retry_success_rate_percent'], 2).' %')
                ->description('Recovered failures after retry'),
            Stat::make('Credits / completed video', number_format((float) $metrics['credits_per_completed_video'], 2))
                ->description('Average credits consumed per completed video'),
            Stat::make('Refund rate', number_format((float) $metrics['refund_rate_percent'], 2).' %')
                ->description('Refunded credits over debited credits'),
            Stat::make('Contacts (30d)', (string) (int) $metrics['contacts_volume_last_30d'])
                ->description('Support/contact demand in last 30 days'),
        ];
    }

    public static function canView(): bool
    {
        return auth()->check();
    }
}
