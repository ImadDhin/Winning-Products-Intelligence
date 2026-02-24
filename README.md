# Winning Products Intelligence

Laravel 11 (PHP 8.3) monolith for dropshipping/ecommerce "Winning Products" intelligence: multi-source ingestion, deduplication, scoring, and Redis-backed leaderboards.

## Requirements
- PHP 8.3
- MySQL 8 or Postgres, Redis 6+
- Composer

## Install
```bash
composer install
cp .env.example .env
php artisan key:generate
# Set DB_* and REDIS_* in .env
php artisan migrate
```

## Queues (Redis)
Run workers per queue: `connectors`, `ingestion`, `scoring`, `leaderboard`, `notifications`.
See `deploy/supervisor.conf`.

## Scheduler
```bash
* * * * * php /path/to/artisan schedule:run
```

## API
- Auth: `POST /api/register`, `POST /api/login`, `POST /api/logout` (Bearer token)
- Winning list: `GET /api/winning?category_id=&window=24h|7d|30d&page=&per_page=`
- Product: `GET /api/products/{id}`
- Watchlist: `GET|POST|DELETE /api/watchlist`
- Alerts: `GET|POST|PATCH|DELETE /api/alerts`
- Health: `GET /api/health`

## Admin (auth + role:admin)
- `GET|PATCH /admin/connectors`, `GET /admin/connectors/{id}`
- `GET /admin/jobs-audit`
- `GET|PATCH /admin/scoring-weights`
- `GET /admin/backtest`, `POST /admin/backtest/run`
- `GET /admin/compliance`

## Deployment
See `deploy/DEPLOYMENT.md` and `deploy/nginx.conf`, `deploy/supervisor.conf`.
