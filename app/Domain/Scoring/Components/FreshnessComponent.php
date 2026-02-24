<?php

namespace App\Domain\Scoring\Components;

use App\Domain\Product\Models\Product;

class FreshnessComponent
{
    public function compute(Product $product): float
    {
        $updated = $product->score_updated_at ?? $product->updated_at;
        if (! $updated) {
            return 0.0;
        }
        $hours = now()->diffInHours($updated, false);
        if ($hours <= 0) {
            return 100.0;
        }
        $decay = 48;
        return max(0.0, 100.0 * exp(-$hours / $decay));
    }
}
