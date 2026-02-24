<?php

namespace App\Domain\Scoring\Components;

use App\Domain\Product\Models\Product;

class DemandGrowthComponent
{
    public function compute(Product $product): float
    {
        $velocity7d = (float) $product->metricsTimeSeries()
            ->where('metric_key', 'engagement_velocity')
            ->where('ts_bucket', '>=', now()->subDays(7))
            ->avg('value');
        $velocity30d = (float) $product->metricsTimeSeries()
            ->where('metric_key', 'engagement_velocity')
            ->where('ts_bucket', '>=', now()->subDays(30))
            ->avg('value');
        if ($velocity30d <= 0) {
            return $velocity7d > 0 ? 50.0 : 0.0;
        }
        return min(100.0, 50.0 * ($velocity7d / $velocity30d));
    }
}
