# Deployment Notes

## Stack
- PHP 8.3 (PHP-FPM)
- Nginx
- MySQL 8 or Postgres 15
- Redis 6+

## PHP-FPM
- `pm.max_children` tuned to available memory
- `request_terminate_timeout` 30s
- No long-running work in HTTP request

## Nginx
- Proxy to PHP-FPM (see `deploy/nginx.conf`)
- Static assets, gzip, buffer timeouts aligned with FPM

## Redis
- Persistent (RDB or AOF)
- Used for: queue, cache, rate limit, leaderboards

## Queue workers
Run separate Supervisor programs per queue (see `deploy/supervisor.conf`):
- connectors, ingestion, scoring, leaderboard, notifications

## Scheduler
Single cron on one node:
```
* * * * * cd /var/www/winning-products && php artisan schedule:run >> /dev/null 2>&1
```

## Horizontal scaling
- Stateless app servers
- Multiple queue workers (same or different servers)
- Single Redis and single DB (or read replica for reads)
- Session driver `redis` or `database`

## Secrets
- All in `.env`; never commit
- Rotate: APP_KEY, DB password, Redis password, OAuth client secrets

## Health
- `GET /api/health` returns 200 when DB and Redis are OK (for load balancer)
