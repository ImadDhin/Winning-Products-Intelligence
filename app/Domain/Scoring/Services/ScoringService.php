<?php

namespace App\Domain\Scoring\Services;

use App\Domain\Product\Models\Product;
use App\Domain\Scoring\Models\Score;
use App\Domain\Scoring\DTOs\ScoreResult;

class ScoringService
{
    public function __construct(
        private ConfidenceCalculator $confidenceCalculator,
    ) {}

    public function compute(int $productId): ScoreResult
    {
        $product = Product::with([
            'sourceSnapshots' => fn ($q) => $q->where('fetched_at', '>', now()->subDays(30))->orderByDesc('fetched_at'),
            'metricsTimeSeries',
            'variants',
        ])->findOrFail($productId);

        $components = [
            'demand_growth' => $this->demandGrowthComponent()->compute($product),
            'competition' => $this->competitionComponent()->compute($product),
            'margin_potential' => $this->marginPotentialComponent()->compute($product),
            'stability' => $this->stabilityComponent()->compute($product),
            'freshness' => $this->freshnessComponent()->compute($product),
            'seasonality' => $this->seasonalityComponent()->compute($product),
        ];

        $weights = config('winning.weights', []);
        $score = $this->weightedSum($components, $weights);
        $confidence = $this->confidenceCalculator->compute($product, $components);
        $this->persistScore($productId, $score, $confidence, $components);
        return new ScoreResult($score, $confidence, $components);
    }

    private function demandGrowthComponent(): \App\Domain\Scoring\Components\DemandGrowthComponent
    {
        return app(\App\Domain\Scoring\Components\DemandGrowthComponent::class);
    }

    private function competitionComponent(): \App\Domain\Scoring\Components\CompetitionComponent
    {
        return app(\App\Domain\Scoring\Components\CompetitionComponent::class);
    }

    private function marginPotentialComponent(): \App\Domain\Scoring\Components\MarginPotentialComponent
    {
        return app(\App\Domain\Scoring\Components\MarginPotentialComponent::class);
    }

    private function stabilityComponent(): \App\Domain\Scoring\Components\StabilityComponent
    {
        return app(\App\Domain\Scoring\Components\StabilityComponent::class);
    }

    private function freshnessComponent(): \App\Domain\Scoring\Components\FreshnessComponent
    {
        return app(\App\Domain\Scoring\Components\FreshnessComponent::class);
    }

    private function seasonalityComponent(): \App\Domain\Scoring\Components\SeasonalityComponent
    {
        return app(\App\Domain\Scoring\Components\SeasonalityComponent::class);
    }

    private function weightedSum(array $components, array $weights): float
    {
        $sum = 0.0;
        foreach ($components as $key => $value) {
            $w = $weights[$key] ?? 0;
            $sum += (float) $value * $w;
        }
        return min(100.0, max(0.0, round($sum, 2)));
    }

    private function persistScore(int $productId, float $score, float $confidence, array $components): void
    {
        Score::updateOrCreate(
            ['product_id' => $productId],
            [
                'score' => $score,
                'confidence' => $confidence,
                'components' => $components,
                'computed_at' => now(),
            ]
        );
        Product::where('id', $productId)->update([
            'current_score' => $score,
            'score_confidence' => $confidence,
            'score_updated_at' => now(),
        ]);
        \Illuminate\Support\Facades\Cache::forget('product:card:' . $productId);
    }
}
