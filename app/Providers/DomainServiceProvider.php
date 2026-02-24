<?php

namespace App\Providers;

use App\Domain\Connector\Contracts\ConnectorInterface;
use App\Domain\Connector\Contracts\NormalizerInterface;
use App\Domain\Connector\Normalizers\ExampleTrendingNormalizer;
use App\Domain\Connector\Connectors\ExampleTrendingConnector;
use App\Domain\Ingestion\Deduplication\DeduplicationService;
use App\Domain\Scoring\Services\ConfidenceCalculator;
use App\Domain\Scoring\Services\ScoringService;
use App\Domain\Leaderboard\Services\LeaderboardService;
use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ConnectorInterface::class, function ($app, $params) {
            $source = $params['source'] ?? null;
            if ($source && $source->connector_class) {
                return $app->make($source->connector_class);
            }
            return $app->make(ExampleTrendingConnector::class);
        });

        $this->app->bind(NormalizerInterface::class, ExampleTrendingNormalizer::class);
        $this->app->singleton(DeduplicationService::class);
        $this->app->singleton(ScoringService::class);
        $this->app->singleton(ConfidenceCalculator::class);
        $this->app->singleton(LeaderboardService::class);

    }

    public function boot(): void
    {
        //
    }
}
