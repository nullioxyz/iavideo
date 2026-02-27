# Closed Beta Runbook

## 1. Deploy Gate
- Run migrations: `php artisan migrate --force`
- Run seeders: `php artisan db:seed --force`
- Run smoke checks:
  - `php artisan ops:smoke-beta --strict --json`
  - Must return exit code `0`

## 2. Worker Operations
- Start queue workers (recommended):
  - `php artisan queue:work --queue=default --tries=3 --backoff=120,300`
- Check failed jobs:
  - `php artisan queue:failed`
- Retry failed jobs in controlled batches:
  - `php artisan queue:retry all`

## 3. Health Monitoring
- Evaluate metrics + alerts:
  - `php artisan ops:monitor-health --json`
- If command exits with `1`, investigate:
  - queue backlog
  - failed jobs in 24h
  - prediction failures in 24h
  - latency p95

## 4. Common Incident Playbooks
### A. Queue backlog spikes
1. Scale workers horizontally.
2. Pause new input creation if backlog keeps growing.
3. Track `/api/analytics/ops-metrics` until stabilized.

### B. Repeated prediction failures
1. Verify provider availability.
2. Confirm retries are working (2min, 5min).
3. Confirm final cancellation and credit refund audit entries.

### C. Websocket instability
1. Verify Socket.IO service and Redis connectivity.
2. Frontend must fallback to polling (`/api/jobs`, `/api/jobs/quota`).

## 5. Rollback Guidelines
- If critical regression:
1. stop workers,
2. rollback release,
3. run smoke again,
4. resume workers.

## 6. Go / No-Go
Go only if:
- smoke checks pass,
- workers healthy,
- monitor-health reports no threshold alerts,
- core user journey works in staging.
