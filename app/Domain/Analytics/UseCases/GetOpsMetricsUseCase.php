<?php

namespace App\Domain\Analytics\UseCases;

use App\Domain\Videos\Models\Prediction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GetOpsMetricsUseCase
{
    /**
     * @return array<string, int|float>
     */
    public function execute(): array
    {
        $pendingJobs = Schema::hasTable('jobs')
            ? (int) DB::table('jobs')->count()
            : 0;

        $failedJobs24h = Schema::hasTable('failed_jobs')
            ? (int) DB::table('failed_jobs')
                ->where('failed_at', '>=', now()->subDay())
                ->count()
            : 0;

        $avgAiLatencyMs = (float) Prediction::query()
            ->whereNotNull('total_ms')
            ->where('created_at', '>=', now()->subDay())
            ->avg('total_ms');

        $p95AiLatencyMs = (float) Prediction::query()
            ->whereNotNull('total_ms')
            ->where('created_at', '>=', now()->subDay())
            ->orderBy('total_ms')
            ->pluck('total_ms')
            ->whenEmpty(fn ($collection) => collect([0]))
            ->values()
            ->pipe(function ($values) {
                $count = $values->count();
                $index = (int) ceil($count * 0.95) - 1;

                return (float) ($values->get(max(0, $index)) ?? 0);
            });

        $predictionFailures24h = (int) Prediction::query()
            ->where('status', Prediction::FAILED)
            ->where('created_at', '>=', now()->subDay())
            ->count();

        return [
            'queue_pending_jobs' => $pendingJobs,
            'queue_failed_jobs_last_24h' => $failedJobs24h,
            'prediction_failures_last_24h' => $predictionFailures24h,
            'ai_latency_avg_ms_last_24h' => round(max(0.0, $avgAiLatencyMs), 2),
            'ai_latency_p95_ms_last_24h' => round(max(0.0, $p95AiLatencyMs), 2),
        ];
    }
}
