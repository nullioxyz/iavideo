<?php

return [
    'thresholds' => [
        'queue_pending_jobs' => (int) env('OPS_ALERT_QUEUE_PENDING_JOBS', 200),
        'queue_failed_jobs_last_24h' => (int) env('OPS_ALERT_FAILED_JOBS_24H', 30),
        'prediction_failures_last_24h' => (int) env('OPS_ALERT_PREDICTION_FAILURES_24H', 60),
        'ai_latency_p95_ms_last_24h' => (int) env('OPS_ALERT_AI_LATENCY_P95_MS_24H', 120000),
    ],
];
