# Redis, Queues & Horizon

This dashboard uses Redis for cache, sessions, queues, and (in production) Laravel Horizon.

## Local development (Windows)

Horizon requires `pcntl` and does **not** run on Windows. Use a standard queue worker:

```powershell
php artisan queue:work redis --queue=critical,payments,webhooks,billing,telemetry,emails,pdf,integrations,default,low --tries=3
```

Or use the bundled dev script:

```powershell
composer dev
```

Verify Redis and queues:

```powershell
php artisan redis:health
php artisan queue:monitor redis:critical,redis:payments,redis:webhooks,redis:billing,redis:telemetry,redis:emails,redis:pdf,redis:integrations,redis:default,redis:low
```

## Production (Linux / WHM / cPanel)

### Environment

```env
QUEUE_CONNECTION=redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your-strong-password
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
HORIZON_NAME=pradydashboard
```

### Supervisor — Horizon (recommended)

```ini
[program:pradydashboard-horizon]
process_name=%(program_name)s
command=php /home/user/pradydashboard/artisan horizon
autostart=true
autorestart=true
user=user
redirect_stderr=true
stdout_logfile=/home/user/pradydashboard/storage/logs/horizon.log
stopwaitsecs=3600
```

### Supervisor — fallback queue worker

If Horizon is unavailable:

```ini
[program:pradydashboard-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /home/user/pradydashboard/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --queue=critical,payments,webhooks,billing,telemetry,emails,pdf,integrations,default,low
autostart=true
autorestart=true
numprocs=2
user=user
redirect_stderr=true
stdout_logfile=/home/user/pradydashboard/storage/logs/queue.log
stopwaitsecs=3600
```

### Scheduler (cron)

```cron
* * * * * cd /home/user/pradydashboard && php artisan schedule:run >> /dev/null 2>&1
```

## Queue responsibilities

| Queue | Purpose |
|-------|---------|
| `critical` | Urgent operational tasks |
| `payments` | Payment reconciliation jobs |
| `webhooks` | Payments Gateway webhook redispatch |
| `billing` | Recurring/overdue billing, reminders |
| `telemetry` | Server sync jobs |
| `emails` | Financial documents, payment reminders |
| `pdf` | PDF generation |
| `integrations` | Tenant system API polling |
| `default` | General async work |
| `low` | Non-urgent summaries |

## Monitoring

- Admin → **Monitoring → Redis & Queues** — pending counts, failed jobs, retry/forget
- **Horizon** — `/horizon` (requires login + `monitoring.view` permission)
- CLI — `php artisan redis:health`

## Safety notes

- Financial truth (invoice balances, payment allocation, license enforcement) is **not** cached in Redis.
- Jobs use Redis locks to prevent duplicate invoices, payments, webhooks, and sync runs.
- Failed jobs are stored in the `failed_jobs` table with sanitized summaries in the admin UI (no full stack traces for normal users).
