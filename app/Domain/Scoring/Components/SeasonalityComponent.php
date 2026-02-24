<?php

namespace App\Domain\Scoring\Components;

use App\Domain\Product\Models\Product;

class SeasonalityComponent
{
    public function compute(Product $product): float
    {
        return 50.0;
    }
}
