<?php

namespace App\Domain\Scoring\Services;

use App\Domain\Product\Models\Product;
use App\Domain\Connector\Models\JobAudit;

class ConfidenceCalculator
{
    public function compute(Product $product, array $components): float
    {
        $sourcesCount = $product->sourceSnapshots()->distinct('source_id')->count('source_id');
        $sourcesFactor = min(1.0, $sourcesCount / (float) config('winning.sources_for_full_confidence', 3));
        $latest = $product->sourceSnapshots()->max('fetched_at');
        $hoursSince = $latest ? now()->diffInHours($latest, false) : 999;
        $decay = config('winning.confidence_decay_hours', 24);
        $recencyFactor = exp(-$decay * max(0, $hoursSince) / 24);
        $volatilityPenalty = $this->volatilityPenalty($product);
        $reliability = $this->connectorReliability($product);
        $confidence = $sourcesFactor * $recencyFactor * (1.0 - $volatilityPenalty) * $reliability;
        return min(1.0, max(0.0, round($confidence, 2)));
    }

    private function volatilityPenalty(Product $product): float
    {
        $old = \App\Domain\Scoring\Models\Score::where('product_id', $product->id)
            ->where('computed_at', '<', now()->subDay())->orderByDesc('computed_at')->first();
        if (! $old || $product->current_score <= 0) {
            return 0.0;
        }
        $pct = abs($product->current_score - (float) $old->score) / (float) $product->current_score * 100;
        $threshold = config('winning.volatility_threshold_pct', 20);
        return $pct > $threshold ? 0.2 : 0.0;
    }

    private function connectorReliability(Product $product): float
    {
        $sourceIds = $product->sourceSnapshots()->pluck('source_id')->unique();
        if ($sourceIds->isEmpty()) {
            return 0.5;
        }
        $total = JobAudit::whereIn('source_id', $sourceIds)->where('started_at', '>', now()->subDays(7))->count();
        $success = JobAudit::whereIn('source_id', $sourceIds)->where('started_at', '>', now()->subDays(7))->where('status', 'success')->count();
        if ($total === 0) {
            return 0.8;
        }
        return round($success / $total, 2);
    }
}
