<?php

namespace App\Domain\Scoring\Components;

use App\Domain\Product\Models\Product;

class CompetitionComponent
{
    public function compute(Product $product): float
    {
        $snapshots = $product->sourceSnapshots()->where('fetched_at', '>', now()->subDays(30))->get();
        $proxy = 0.0;
        $n = 0;
        foreach ($snapshots as $s) {
            $m = $s->metrics_snapshot['seller_count_proxy'] ?? $s->metrics_snapshot['seller_count'] ?? null;
            if ($m !== null) {
                $proxy += (float) $m;
                $n++;
            }
        }
        $avg = $n > 0 ? $proxy / $n : 0;
        $factor = 5.0;
        return max(0.0, 100.0 - $avg * $factor);
    }
}
