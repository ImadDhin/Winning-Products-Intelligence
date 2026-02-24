<?php

namespace App\Domain\Scoring\Components;

use App\Domain\Product\Models\Product;

class MarginPotentialComponent
{
    public function compute(Product $product): float
    {
        $variants = $product->variants;
        if ($variants->isEmpty()) {
            return 50.0;
        }
        $min = $variants->min('price_min');
        $max = $variants->max('price_max');
        $spread = $max - $min;
        if ($min <= 0) {
            return 50.0;
        }
        $marginPct = $spread / $min * 100;
        return min(100.0, $marginPct * 2);
    }
}
