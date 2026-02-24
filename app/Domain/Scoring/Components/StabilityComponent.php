<?php

namespace App\Domain\Scoring\Components;

use App\Domain\Product\Models\Product;

class StabilityComponent
{
    public function compute(Product $product): float
    {
        $negative = (float) $product->metricsTimeSeries()
            ->where('metric_key', 'negative_review_trend')
            ->where('ts_bucket', '>=', now()->subDays(30))
            ->avg('value');
        return max(0.0, 100.0 - $negative * 10);
    }
}
